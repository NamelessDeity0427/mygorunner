<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Rider') }}: {{ $rider->user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('error'))
                         <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                    @endif
                     @if ($errors->any())
                         <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded">
                            <div class="font-bold">Please fix the following errors:</div>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.riders.update', $rider) }}">
                        @csrf
                        @method('PUT') {{-- Important for updates --}}
                        <div class="space-y-6">

                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">User Account Details</h3>

                            <div class="form-group">
                                <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $rider->user->name) }}" required class="form-input">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $rider->user->email) }}" required class="form-input">
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number <span class="text-red-500">*</span></label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $rider->user->phone) }}" required class="form-input">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">New Password (Optional)</label>
                                    <input type="password" id="password" name="password" class="form-input" aria-describedby="passwordHelp">
                                     <p id="passwordHelp" class="text-xs text-gray-500 mt-1">Leave blank to keep current password.</p>
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
                                </div>
                            </div>

                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4 pt-4">Rider Profile Details</h3>

                             <div class="form-group">
                                <label for="address" class="form-label">Full Address <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" rows="3" required class="form-textarea">{{ old('address', $rider->address) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="vehicle_type" class="form-label">Vehicle Type <span class="text-red-500">*</span></label>
                                    <input type="text" id="vehicle_type" name="vehicle_type" value="{{ old('vehicle_type', $rider->vehicle_type) }}" placeholder="e.g., Motorcycle, E-Bike" required class="form-input">
                                </div>

                                <div class="form-group">
                                    <label for="plate_number" class="form-label">Plate Number / Vehicle ID <span class="text-red-500">*</span></label>
                                    <input type="text" id="plate_number" name="plate_number" value="{{ old('plate_number', $rider->plate_number) }}" required class="form-input">
                                </div>
                            </div>

                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div class="form-group">
                                    <label for="status" class="form-label">Current Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="offline" {{ old('status', $rider->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                                        <option value="available" {{ old('status', $rider->status) == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="on_task" {{ old('status', $rider->status) == 'on_task' ? 'selected' : '' }}>On Task</option>
                                        <option value="on_break" {{ old('status', $rider->status) == 'on_break' ? 'selected' : '' }}>On Break</option>
                                    </select>
                                </div>

                                 <div class="form-group flex items-end pb-2">
                                    <label for="is_active" class="flex items-center">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $rider->is_active) ? 'checked' : '' }}>
                                        <span class="ml-2">Account Active</span>
                                    </label>
                                </div>
                             </div>

                             {{-- Add fields for license upload etc. later --}}

                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-4">
                            <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Rider</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>