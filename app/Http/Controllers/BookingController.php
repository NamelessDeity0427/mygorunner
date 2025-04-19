<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
// REMOVED: use App\Models\TieUpPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Added for transaction
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use MatanYadaev\EloquentSpatial\Objects\Point; // Added for spatial data
use Exception; // Added for exception handling

class BookingController extends Controller
{
    /**
     * Display a listing of the customer's bookings.
     * Apply authorization middleware in routes.
     */
    public function index(): View | RedirectResponse
    {
        $user = Auth::user();
        // Ensure the user has a customer profile linked
        if (!$user->customer) {
            Log::warning("User attempted to view bookings without a customer profile.", ['user_id' => $user->id]);
            // Redirect to dashboard or profile creation page with an error
            return redirect()->route('customer.dashboard') // Adjust route as needed
                ->with('error', 'Please complete your customer profile to view bookings.');
        }

        $customerId = $user->customer->id;

        // Eager load necessary relationships for efficiency and security (prevents N+1)
        // REMOVED 'tieUpPartner' from with()
        $bookings = Booking::where('customer_id', $customerId)
            ->with(['rider.user:id,name', 'items']) // Load rider name and booking items
            ->orderBy('created_at', 'desc')
            ->paginate(15); // Use pagination

        return view('customer.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking.
     * Apply authorization middleware in routes.
     */
    public function create(): View | RedirectResponse
    {
        $user = Auth::user();
        // Ensure the user has a customer profile linked
        if (!$user->customer) {
             Log::warning("User attempted to create booking without a customer profile.", ['user_id' => $user->id]);
            return redirect()->route('customer.dashboard') // Adjust route as needed
                ->with('error', 'Please complete your customer profile to create a booking.');
        }

        // REMOVED: $tieUpPartners = TieUpPartner::where('is_active', true)->orderBy('name')->get();
        // REMOVED: No default location needed here based on previous changes

        // Pass only necessary data (e.g., service types for a dropdown)
        $serviceTypes = ['food_delivery', 'grocery', 'laundry', 'bills_payment', 'other']; // Example

        return view('customer.bookings.create', compact('serviceTypes'));
    }

    /**
     * Store a newly created booking in storage.
     * Apply authorization middleware in routes.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
             Log::warning("User attempted to store booking without a customer profile.", ['user_id' => $user->id]);
            return back()->with('error', 'Customer profile not found.')->withInput();
        }

        // --- Validation ---
        // Stricter validation rules. Added pickup/delivery fields. Removed tie-up fields.
        $validatedData = $request->validate([
            // REMOVED: 'booking_type' => ['required', 'in:tie_up,direct'],
            // REMOVED: 'tie_up_partner_id' => ['nullable', 'required_if:booking_type,tie_up', 'exists:tie_up_partners,id'],
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
            'items' => ['nullable', 'array'], // Ensure items is an array if present
            // Make item fields required only if the items array itself is present and not empty
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        // --- Data Processing ---
        $scheduled_at = null;
        if (!empty($validatedData['scheduled_at_date']) && !empty($validatedData['scheduled_at_time'])) {
            // Combine date and time, ensuring it's a valid Carbon instance
            try {
                 $scheduled_at = \Carbon\Carbon::parse($validatedData['scheduled_at_date'] . ' ' . $validatedData['scheduled_at_time']);
                 // Basic check: Ensure scheduled time is in the future (e.g., at least 30 mins from now)
                 if ($scheduled_at->isBefore(now()->addMinutes(30))) {
                     return back()->with('error', 'Scheduled time must be at least 30 minutes from now.')->withInput();
                 }
            } catch (Exception $e) {
                Log::error("Invalid schedule date/time format during booking creation.", ['data' => $validatedData, 'error' => $e->getMessage()]);
                return back()->with('error', 'Invalid scheduled date or time format.')->withInput();
            }
        }

        // Create Point objects from validated coordinates
        $pickup_location = new Point($validatedData['pickup_latitude'], $validatedData['pickup_longitude']);
        $delivery_location = new Point($validatedData['delivery_latitude'], $validatedData['delivery_longitude']);

        // Generate a more robust unique booking number
        $booking_number = 'MYGO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // --- Database Insertion (Use Transaction) ---
        DB::beginTransaction();
        try {
            $bookingData = [
                'booking_number' => $booking_number,
                'customer_id' => $customer->id, // Use the authenticated customer's ID
                'rider_id' => null, // Rider assigned later by admin/dispatcher
                // REMOVED: 'tie_up_partner_id' => null,
                // REMOVED: 'booking_type' => 'direct', // Assume direct
                'service_type' => $validatedData['service_type'],
                'pickup_address' => $validatedData['pickup_address'],
                'pickup_location' => $pickup_location,
                'delivery_address' => $validatedData['delivery_address'],
                'delivery_location' => $delivery_location,
                'special_instructions' => $validatedData['special_instructions'],
                'reference_number' => null, // Can be updated later if needed
                'scheduled_at' => $scheduled_at,
                // REMOVED: 'is_recurring', 'recurring_pattern'
                'status' => 'pending', // Initial status
                // Fees and calculated fields are set later by admin/system, not fillable
            ];

            // Create the booking using create() method which respects $fillable
            $booking = Booking::create($bookingData);

            // Save items if provided
            // REMOVED: Check for 'direct' booking type
            if (!empty($validatedData['items'])) {
                foreach ($validatedData['items'] as $itemData) {
                    // Ensure name is not empty before creating
                    if (!empty($itemData['name'])) {
                        // Use create() which respects $fillable on BookingItem model
                        $booking->items()->create([
                            'name' => $itemData['name'],
                            'quantity' => $itemData['quantity'] ?? 1,
                            'notes' => $itemData['notes'] ?? null,
                            // 'price' => null, // Price set later or not applicable for direct items?
                        ]);
                    }
                }
            }

            DB::commit(); // Commit if all operations succeed

            // TODO: Notify Admin/Dispatcher about the new booking (e.g., via event/notification)

            return redirect()->route('customer.bookings.index')->with('success', 'Booking #' . $booking_number . ' placed successfully!');

        } catch (Exception $e) {
            DB::rollBack(); // Rollback on any error
            Log::error("Booking creation failed", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Log stack trace for debugging
            ]);
            // Provide a generic error message to the user for security
            return back()->withInput()->with('error', 'An unexpected error occurred while placing your booking. Please try again later.');
        }
    }

    /**
     * Display the specified booking.
     * Apply authorization middleware in routes.
     * Route model binding implicitly handles finding the Booking by UUID.
     */
    public function show(Booking $booking): View | RedirectResponse
    {
        $user = Auth::user();
        // Authorization: Ensure the logged-in user owns this booking
        if (!$user->customer || $booking->customer_id !== $user->customer->id) {
             Log::warning("User attempted to view unauthorized booking.", ['user_id' => $user->id, 'booking_id' => $booking->id]);
            abort(403, 'Access Denied'); // Forbidden
        }

        // Eager load necessary data
        // REMOVED: 'tieUpPartner'
        $booking->load([
            'rider.user:id,name', // Get rider name
            'items', // Get booking items
            'statusHistory.creator:id,name', // Get status history with user who made the change
            'payments' // Load payments related to this booking
        ]);

        // Get rider's current location if assigned, otherwise null
        $riderLocation = $booking->rider?->current_location; // Spatial Point object or null

        return view('customer.bookings.show', compact('booking', 'riderLocation'));
    }

    /**
      * Cancel a booking (Customer action).
      * Example implementation - requires a route definition.
      */
    // public function cancel(Booking $booking): RedirectResponse
    // {
    //     $user = Auth::user();
    //     // Authorization: Ensure the logged-in user owns this booking
    //     if (!$user->customer || $booking->customer_id !== $user->customer->id) {
    //         Log::warning("User attempted to cancel unauthorized booking.", ['user_id' => $user->id, 'booking_id' => $booking->id]);
    //         abort(403, 'Access Denied');
    //     }

    //     // Business Logic: Check if the booking is cancellable (e.g., only if status is 'pending' or 'accepted')
    //     if (!in_array($booking->status, ['pending', 'accepted'])) {
    //          return back()->with('error', 'Booking cannot be cancelled at its current stage.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $booking->status = 'cancelled';
    //         $booking->cancelled_at = now();
    //         $booking->save();

    //         // Add to status history
    //         $booking->statusHistory()->create([
    //             'status' => 'cancelled',
    //             'notes' => 'Cancelled by customer.',
    //             'created_by' => $user->id,
    //         ]);

    //         // TODO: Notify Admin/Rider about cancellation (Event/Notification)

    //         DB::commit();
    //         return redirect()->route('customer.bookings.show', $booking)->with('success', 'Booking cancelled successfully.');

    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error("Booking cancellation failed", ['booking_id' => $booking->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
    //         return back()->with('error', 'Failed to cancel booking. Please try again.');
    //     }
    // }
}