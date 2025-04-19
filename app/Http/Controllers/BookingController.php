<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Services\BookingService;
use App\Rules\ValidPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->middleware(['auth', 'role:customer'])->except('updateStatus');
        $this->middleware(['auth', 'role:rider'])->only('updateStatus');
        $this->bookingService = $bookingService;
    }

    public function index(): View
    {
        $customerId = Auth::user()->customer->id;
        $bookings = Booking::where('customer_id', $customerId)
            ->with(['rider.user' => function ($query) {
                $query->select('id', 'name')->whereNotNull('id');
            }, 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.bookings.index', compact('bookings'));
    }

    public function create(): View
    {
        $serviceTypes = Service::pluck('name')->toArray();
        return view('customer.bookings.create', compact('serviceTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'service_type' => ['required', 'string', 'exists:services,name'],
            'pickup_address' => ['required', 'string', 'max:1000'],
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['required', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['required', 'numeric', 'between:-180,180'],
            'pickup_point' => [new ValidPoint('pickup_latitude', 'pickup_longitude')],
            'delivery_point' => [new ValidPoint('delivery_latitude', 'delivery_longitude')],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            return DB::transaction(function () use ($validatedData, $request) {
                $customer = Auth::user()->customer;
                $validatedData['pickup_location'] = new Point($validatedData['pickup_latitude'], $validatedData['pickup_longitude']);
                $validatedData['delivery_location'] = new Point($validatedData['delivery_latitude'], $validatedData['delivery_longitude']);
                $booking = $this->bookingService->createBooking($validatedData, $customer);
                event(new \App\Events\BookingStatusUpdated($booking));
                return redirect()->route('customer.bookings.index')->with('success', "Booking #{$booking->id} placed successfully!");
            });
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validStatuses = ['pending', 'accepted', 'assigned', 'at_pickup', 'picked_up', 'on_the_way', 'at_delivery', 'completed', 'cancelled'];
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', $validStatuses)],
        ]);

        try {
            if (Auth::user()->isRider() && $booking->rider_id !== Auth::user()->rider->id) {
                return back()->with('error', 'You are not authorized to update this booking.');
            }

            // Prevent invalid status transitions
            $currentStatus = $booking->status;
            $allowedTransitions = [
                'pending' => ['accepted', 'cancelled'],
                'accepted' => ['assigned'],
                'assigned' => ['at_pickup'],
                'at_pickup' => ['picked_up'],
                'picked_up' => ['on_the_way'],
                'on_the_way' => ['at_delivery'],
                'at_delivery' => ['completed'],
                'completed' => [],
                'cancelled' => [],
            ];
            if (!in_array($validated['status'], $allowedTransitions[$currentStatus] ?? [])) {
                return back()->with('error', "Cannot transition from {$currentStatus} to {$validated['status']}.");
            }

            $booking->update(['status' => $validated['status']]);
            event(new \App\Events\BookingStatusUpdated($booking));
            return back()->with('success', "Booking #{$booking->id} status updated to {$validated['status']}.");
        } catch (\Exception $e) {
            Log::error('Booking status update failed', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update booking status.');
        }
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->customer->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized access to booking.');
        }

        $cancellationWindow = (int) \App\Models\SystemSetting::getValue('cancellation_window_minutes', 5);
        if ($booking->created_at->lt(now()->subMinutes($cancellationWindow)) || $booking->status !== 'pending') {
            return back()->with('error', 'Booking cannot be cancelled at this stage.');
        }

        try {
            $booking->update(['status' => 'cancelled']);
            event(new \App\Events\BookingStatusUpdated($booking));
            return redirect()->route('customer.bookings.index')->with('success', "Booking #{$booking->id} cancelled successfully.");
        } catch (\Exception $e) {
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to cancel booking.');
        }
    }

    public function track(Booking $booking): View
    {
        if ($booking->customer->user_id !== Auth::id() && (!$booking->rider_id || $booking->rider_id !== Auth::user()->rider?->id)) {
            abort(403, 'Unauthorized access to booking.');
        }

        $booking->load(['locationLogs' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'rider.user']);

        return view('customer.bookings.track', compact('booking'));
    }
}