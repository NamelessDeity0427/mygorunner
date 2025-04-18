<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Bookings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                        <div class="alert alert-success mb-4">{{ session('success') }}</div>
                    @endif

                    <div class="mb-4 d-flex justify-content-end">
                         <a href="{{ route('customer.bookings.create') }}" class="btn btn-primary">Place New Booking</a>
                    </div>

                    <h3 class="mb-3">Booking History</h3>

                    @if($bookings->isEmpty())
                        <p>You haven't placed any bookings yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Service</th>
                                        <th>Pickup</th>
                                        <th>Delivery</th>
                                        <th>Status</th>
                                        <th>Rider</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->booking_number }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $booking->booking_type)) }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $booking->service_type)) }}</td>
                                        <td>{{ $booking->pickup_address }} {{ $booking->tieUpPartner ? '('.$booking->tieUpPartner->name.')' : '' }}</td>
                                        <td>{{ $booking->delivery_address }}</td>
                                        <td>
                                            <span class="badge bg-{{ \App\Helpers\StatusHelper::getBookingStatusClass($booking->status) ?? 'secondary' }}"> {{-- Requires a helper --}}
                                                {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $booking->rider->user->name ?? 'Not Assigned' }}</td>
                                        <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                             <a href="{{ route('customer.bookings.show', $booking) }}" class="btn btn-sm btn-info">Details</a>
                                             {{-- Add cancel button if status allows --}}
                                             @if(in_array($booking->status, ['pending', 'assigned']))
                                                {{-- <form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                </form> --}}
                                             @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                         {{ $bookings->links() }} {{-- Pagination Links --}}
                    @endif

                </div>
            </div>
        </div>
    </div>
     {{-- Helper function example (Create app/Helpers/StatusHelper.php or similar) --}}
     {{--
        namespace App\Helpers;
        class StatusHelper {
            public static function getBookingStatusClass($status) {
                return match ($status) {
                    'pending' => 'warning', 'assigned' => 'info', 'picked_up' => 'primary',
                    'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger',
                    default => 'secondary',
                };
            }
        }
     --}}
</x-app-layout>