<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Service') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">

                            <div class="form-group">
                                <label for="name" class="form-label">Service Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-input @error('name') border-red-500 @enderror">
                                @error('name') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-textarea @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                @error('description') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div class="form-group">
                                    <label for="price" class="form-label">Price (PHP)</label>
                                    <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price') }}" class="form-input @error('price') border-red-500 @enderror">
                                    @error('price') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" id="category" name="category" value="{{ old('category') }}" placeholder="e.g., Food, Errand, Pabili" class="form-input @error('category') border-red-500 @enderror">
                                    @error('category') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" id="image" name="image" class="form-input-file @error('image') border-red-500 @enderror">
                                @error('image') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                             <div class="form-group">
                                <label for="is_active" class="flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" class="form-checkbox" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="ml-2">Active (visible to customers)</span>
                                </label>
                            </div>

                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-4">
                            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>