<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Services') }}
            </h2>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">Add New Service</a>
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
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                <tr>
                                    <td>
                                        @if($service->image_path)
                                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->name }}" width="50">
                                        @else
                                            (No image)
                                        @endif
                                    </td>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ $service->category ?? '-' }}</td>
                                    <td>{{ $service->price ? 'PHP ' . number_format($service->price, 2) : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $service->is_active ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No services found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $services->links() }} {{-- Pagination links --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>