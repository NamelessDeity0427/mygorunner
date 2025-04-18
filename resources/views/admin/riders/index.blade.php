<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Riders') }}
            </h2>
             <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">Register New Rider</a> {{-- Add this button --}}
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="alert alert-success mb-4">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Vehicle</th>
                                    <th>Plate No.</th>
                                    <th>Status</th>
                                    <th>Active</th>
                                    <th>Joined</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riders as $rider)
                                <tr>
                                    <td>{{ $rider->id }}</td>
                                    <td>{{ $rider->user->name ?? 'N/A' }}</td>
                                    <td>{{ $rider->user->email ?? 'N/A' }}</td>
                                    <td>{{ $rider->user->phone ?? '-' }}</td>
                                    <td>{{ $rider->vehicle_type ?? '-' }}</td>
                                    <td>{{ $rider->plate_number ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucwords($rider->status) }}</span> {{-- Add color logic later --}}
                                    </td>
                                     <td>
                                        <span class="badge {{ $rider->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $rider->is_active ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>{{ $rider->created_at->format('Y-m-d') }}</td>
                                    <td class="text-right whitespace-nowrap">
                                        {{-- <a href="#" class="btn btn-sm btn-info">View</a> --}} {{-- Maybe add a show route later if needed --}}
                                        <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('admin.riders.destroy', $rider) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this rider? This will also delete their user account.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No riders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $riders->links() }} {{-- Pagination links --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>