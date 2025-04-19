<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Stripe;
use Exception;

class PaymentController extends Controller
{
    public function process(Request $request, Booking $booking): RedirectResponse
    {
        $user = Auth::user();
        if ($booking->customer->user_id !== $user->id) {
            return back()->with('error', 'Unauthorized access to booking.');
        }

        $validated = $request->validate([
            'stripeToken' => ['required', 'string'],
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $charge = Charge::create([
                'amount' => $booking->estimated_cost * 100,
                'currency' => 'usd',
                'source' => $validated['stripeToken'],
                'description' => "Payment for Booking #{$booking->booking_number}",
            ]);

            $booking->update([
                'payment_status' => 'paid',
                'payment_id' => $charge->id,
            ]);

            return redirect()->route('customer.bookings.index')->with('success', 'Payment processed successfully.');
        } catch (Exception $e) {
            Log::error("Payment processing failed", [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to process payment.');
        }
    }
}