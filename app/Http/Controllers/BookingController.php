<?php
// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\TieUpPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// REMOVED: Point object import
// use MatanYadaev\EloquentSpatial\Objects\Point;

class BookingController extends Controller
{
    // index() and show() methods remain largely the same,
    // but remove references to map data derived from booking locations in show()

    /**
     * Display a listing of the customer's bookings (history).
     */
    public function index(): View
    {
        // Ensure customer relationship exists on User model
        $customer = Auth::user()->customer;
        if (!$customer) {
            // Handle case where user might not have a customer profile yet
            // Maybe redirect or show an error
             abort(403, 'Customer profile not found.');
        }
        $bookings = Booking::where('customer_id', $customer->id)
                           ->with(['rider.user', 'tieUpPartner']) // Eager load related data
                           ->orderBy('created_at', 'desc')
                           ->paginate(15);

        return view('customer.bookings.index', compact('bookings'));
    }


    /**
     * Show the form for creating a new booking.
     */
    public function create(): View
    {
        $tieUpPartners = TieUpPartner::where('is_active', true)->orderBy('name')->get();
        // REMOVED: No need for default location on the form map anymore
        // $defaultLocation = Auth::user()->customer?->default_location;

        // Pass only tieUpPartners
        return view('customer.bookings.create', compact('tieUpPartners'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // --- Validation ---
        // Remove validation rules for address and lat/lng fields
        $validatedData = $request->validate([
            'booking_type' => ['required', 'in:tie_up,direct'],
            'service_type' => ['required', 'in:food_delivery,grocery,laundry,bills_payment,other'],
            'tie_up_partner_id' => ['nullable', 'required_if:booking_type,tie_up', 'exists:tie_up_partners,id'],
            // REMOVED: 'pickup_address', 'pickup_lat', 'pickup_lng' validation
            // REMOVED: 'delivery_address', 'delivery_lat', 'delivery_lng' validation
            'scheduled_at_date' => ['nullable', 'date'],
            'scheduled_at_time' => ['nullable', 'date_format:H:i'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'items.*.name' => ['nullable','required_if:booking_type,direct', 'string', 'max:255'],
            'items.*.quantity' => ['nullable','required_if:booking_type,direct', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        // --- Data Processing ---
        $customer = Auth::user()->customer;
        if (!$customer) {
             return back()->with('error', 'Customer profile not found.');
        }

        $scheduled_at = null;
        if (!empty($validatedData['scheduled_at_date']) && !empty($validatedData['scheduled_at_time'])) {
            $scheduled_at = $validatedData['scheduled_at_date'] . ' ' . $validatedData['scheduled_at_time'];
        }

        // REMOVED: Point object creation
        // $pickup_location = new Point($validatedData['pickup_lat'], $validatedData['pickup_lng']);
        // $delivery_location = new Point($validatedData['delivery_lat'], $validatedData['delivery_lng']);

        $booking_number = 'MYGO-' . strtoupper(uniqid());

        // --- Database Insertion ---
        try {
            $booking = Booking::create([
                'booking_number' => $booking_number,
                'customer_id' => $customer->id,
                'rider_id' => null,
                'tie_up_partner_id' => $validatedData['booking_type'] === 'tie_up' ? $validatedData['tie_up_partner_id'] : null,
                'booking_type' => $validatedData['booking_type'],
                'service_type' => $validatedData['service_type'],
                // REMOVED: Address/Location fields from create()
                // 'pickup_address' => $validatedData['pickup_address'],
                // 'pickup_location' => $pickup_location,
                // 'delivery_address' => $validatedData['delivery_address'],
                // 'delivery_location' => $delivery_location,
                'special_instructions' => $validatedData['special_instructions'],
                'reference_number' => null,
                'scheduled_at' => $scheduled_at,
                'is_recurring' => false,
                'recurring_pattern' => null,
                'status' => 'pending',
                'service_fee' => 0.00,
                'rider_fee' => 0.00,
                'total_amount' => 0.00,
            ]);

            // Save items for 'direct' booking (remains the same)
            if ($validatedData['booking_type'] === 'direct' && isset($validatedData['items'])) {
                foreach ($validatedData['items'] as $itemData) {
                    if (!empty($itemData['name'])) {
                        $booking->items()->create([
                            'name' => $itemData['name'],
                            'quantity' => $itemData['quantity'] ?? 1,
                            'notes' => $itemData['notes'],
                            'price' => null,
                        ]);
                    }
                }
            }

            return redirect()->route('customer.bookings.index')->with('success', 'Booking placed successfully!');

        } catch (\Exception $e) {
            Log::error("Booking creation failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to place booking. Please try again.');
        }
    }


     /**
     * Display the specified booking.
     */
    public function show(Booking $booking): View
    {
        // Ensure the logged-in user owns this booking
        $customer = Auth::user()->customer;
         if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403); // Forbidden
        }

        $booking->load(['rider.user', 'tieUpPartner', 'items', 'statusHistory.creator']);

        // REMOVED: mapData derived from booking locations
        // Map data will now likely focus *only* on the assigned rider's current location
        $riderLocation = $booking->rider?->current_location;

        return view('customer.bookings.show', compact('booking', 'riderLocation'));
    }

}