<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Carbon\Carbon;
use Exception;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(): View | RedirectResponse
    {
        $user = Auth::user();
        if (!$user->customer) {
            Log::warning("User attempted to view bookings without a customer profile.", ["user_id" => $user->id]);
            return redirect()->route('customer.dashboard')->with('error', 'Please complete your customer profile to view bookings.');
        }

        $customerId = $user->customer->id;
        $bookings = Booking::where('customer_id', $customerId)
            ->with(['rider.user:id,name', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.bookings.index', compact('bookings'));
    }

    public function create(): View | RedirectResponse
    {
        $user = Auth::user();
        if (!$user->customer) {
            Log::warning("User attempted to create booking without a customer profile.", ['user_id' => $user->id]);
            return redirect()->route('customer.dashboard')->with('error', 'Please complete your customer profile to create a booking.');
        }

        $serviceTypes = ['food_delivery', 'grocery', 'laundry', 'bills_payment', 'other'];
        return view('customer.bookings.create', compact('serviceTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            Log::warning("User attempted to store booking without a customer profile.", ["user_id" => $user->id]);
            return back()->with('error', 'Customer profile not found.')->withInput();
        }

        $validatedData = $request->validate([
            'service_type' => ['required', 'string', 'in:food_delivery,grocery,laundry,bills_payment,other'],
            'pickup_address' => ['required', 'string', 'max:1000'],
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['required', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['required', 'numeric', 'between:-180,180'],
            'scheduled_at_date' => ['nullable', 'date', 'after_or_equal:today'],
            'scheduled_at_time' => ['nullable', 'required_with:scheduled_at_date', 'date_format:H:i'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $estimatedCost = $this->bookingService->calculateCost($validatedData);
            $booking = $this->bookingService->createBooking($validatedData, $customer);
            $booking->update(['estimated_cost' => $estimatedCost]);
            return redirect()->route('customer.bookings.index')->with('success', "Booking #{$booking->booking_number} placed successfully! Estimated cost: $$estimatedCost");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'An unexpected error occurred while placing your booking. Please try again later.');
        }
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,assigned,in_progress,completed,cancelled'],
        ]);

        try {
            if (Auth::user()->hasRole('rider') && $booking->rider_id !== Auth::user()->rider->id) {
                return back()->with('error', 'You are not authorized to update this booking.');
            }

            $booking->update(['status' => $validated['status']]);
            event(new \App\Events\BookingStatusUpdated($booking));
            return back()->with('success', "Booking #{$booking->booking_number} status updated to {$validated['status']}.");
        } catch (Exception $e) {
            Log::error("Booking status update failed", [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update booking status.');
        }
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $user = Auth::user();
        if ($booking->customer->user_id !== $user->id) {
            return back()->with('error', 'Unauthorized access to booking.');
        }

        $cancellationWindow = now()->subMinutes(5);
        if ($booking->created_at->lt($cancellationWindow) || $booking->status !== 'pending') {
            return back()->with('error', 'Booking cannot be cancelled at this stage.');
        }

        try {
            $booking->update(['status' => 'cancelled']);
            event(new \App\Events\BookingStatusUpdated($booking));
            return redirect()->route('customer.bookings.index')->with('success', "Booking #{$booking->booking_number} cancelled successfully.");
        } catch (Exception $e) {
            Log::error("Booking cancellation failed", [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to cancel booking.');
        }
    }
}