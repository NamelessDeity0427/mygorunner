<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Booking Details') }}: {{ $booking->booking_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h4 class="mb-3">Booking Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ \App\Helpers\StatusHelper::getBookingStatusClass($booking->status) ?? 'secondary' }}">
                                    {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </p>
                            <p><strong>Booking Type:</strong> {{ ucwords(str_replace('_', ' ', $booking->booking_type)) }}</p>
                            <p><strong>Service Type:</strong> {{ ucwords(str_replace('_', ' ', $booking->service_type)) }}</p>
                            @if($booking->tieUpPartner)
                            <p><strong>Partner:</strong> {{ $booking->tieUpPartner->name }}</p>
                            @endif
                            <p><strong>Pickup:</strong> {{ $booking->pickup_address }}</p>
                            <p><strong>Delivery:</strong> {{ $booking->delivery_address }}</p>
                        </div>
                        <div class="col-md-6">
                             <p><strong>Date Placed:</strong> {{ $booking->created_at->format('M d, Y H:i A') }}</p>
                             <p><strong>Scheduled For:</strong> {{ $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y H:i A') : 'Immediate' }}</p>
                             <p><strong>Assigned Rider:</strong> {{ $booking->rider->user->name ?? 'Not Yet Assigned' }}</p>
                             <p><strong>Special Instructions:</strong> {{ $booking->special_instructions ?? 'None' }}</p>
                             <p><strong>Total Amount:</strong> PHP {{ number_format($booking->total_amount, 2) }} (Details TBC)</p> {{-- Adjust based on payment logic --}}
                             <p><strong>Payment Status:</strong> {{-- Add logic based on Payments table --}} Pending</p>
                        </div>
                    </div>

                     @if($booking->booking_type == 'direct' && $booking->items->isNotEmpty())
                         <h5 class="mt-4">Items / Task Details</h5>
                         <ul>
                             @foreach($booking->items as $item)
                                <li>{{ $item->quantity }} x {{ $item->name }} {{ $item->notes ? '('.$item->notes.')' : '' }}</li>
                             @endforeach
                         </ul>
                     @endif
                </div>
            </div>

            {{-- Map Placeholder --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                 <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h4 class="mb-3">Live Tracking Map</h4>
                    <div id="tracking-map" style="height: 400px; background-color: #eee;">
                        Map will be integrated here using Leaflet.
                        <br>Pickup: {{ $mapData['pickup']?->latitude }}, {{ $mapData['pickup']?->longitude }}
                        <br>Delivery: {{ $mapData['delivery']?->latitude }}, {{ $mapData['delivery']?->longitude }}
                        <br>Rider: {{ $mapData['rider'] ? ($mapData['rider']->latitude . ', ' . $mapData['rider']->longitude) : 'N/A' }}
                    </div>
                 </div>
            </div>

             {{-- Status History --}}
             <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900 dark:text-gray-100">
                     <h4 class="mb-3">Status History</h4>
                     @if($booking->statusHistory->isEmpty())
                         <p>No status updates yet.</p>
                     @else
                         <ul>
                             @foreach($booking->statusHistory as $history)
                                <li>
                                    <strong>{{ ucwords(str_replace('_', ' ', $history->status)) }}</strong>
                                     - {{ $history->created_at->format('M d, Y H:i A') }}
                                     by {{ $history->creator->name ?? 'System' }}
                                     {{ $history->notes ? '('.$history->notes.')' : '' }}
                                 </li>
                             @endforeach
                         </ul>
                     @endif
                 </div>
             </div>

        </div>
    </div>
    {{-- Add Leaflet CSS/JS includes in your main layout or here --}}
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" ... /> --}}
    {{-- <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" ... ></script> --}}
    {{-- <script> // Basic Leaflet initialization script would go here </script> --}}
</x-app-layout>