<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Charge;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer']);
    }

    public function process(Request $request, Booking $booking): \Illuminate\Http\RedirectResponse
    {
        if ($booking->customer->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized access to booking.');
        }

        if ($booking->payment && $booking->payment->status === 'paid') {
            return back()->with('error', 'This booking has already been paid.');
        }

        $validated = $request->validate([
            'stripeToken' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            return DB::transaction(function () use ($request, $booking, $validated) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $charge = Charge::create([
                    'amount' => $validated['amount'] * 100,
                    'currency' => 'usd',
                    'source' => $validated['stripeToken'],
                    'description' => "Payment for Booking #{$booking->id}",
                ]);

                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $validated['amount'],
                    'payment_method' => 'stripe',
                    'status' => 'paid',
                    'reference_number' => $charge->id,
                    'processed_by' => null,
                ]);

                $booking->update(['status' => 'paid']);

                return redirect()->route('customer.bookings.index')->with('success', 'Payment processed successfully.');
            });
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Payment processing failed (Card error)', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Payment declined: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }
}