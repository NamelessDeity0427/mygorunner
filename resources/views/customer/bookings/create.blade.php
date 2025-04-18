<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2>Place New Booking</h2>
        </div>
    </x-slot>

    <div class="py-6 animate-fadeInUp">
        <div class="max-w-4xl mx-auto">
            <div class="card">
                <!-- Error/Validation Display -->
                @if (session('error'))
                    <div class="mb-6 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-red-700">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-red-700">
                        <p class="font-medium mb-1">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.bookings.store') }}" id="booking-form">
                    @csrf

                    <div class="space-y-8">
                        <!-- Booking Details -->
                        <div class="space-y-4">
                            <h3 class="section-title">Booking Details</h3>
                            <div class="grid col-span-12 gap-6 lg:grid-cols-2">
                                <div class="form-group col-span-12 lg:col-span-6">
                                    <label for="booking_type" class="form-label">Booking Type</label>
                                    <select id="booking_type" name="booking_type" required onchange="toggleBookingFields()" class="form-select">
                                        <option value="" selected disabled>-- Select Type --</option>
                                        <option value="tie_up" {{ old('booking_type') == 'tie_up' ? 'selected' : '' }}>Tie-Up Partner</option>
                                        <option value="direct" {{ old('booking_type') == 'direct' ? 'selected' : '' }}>Direct Request</option>
                                    </select>
                                    @error('booking_type')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group col-span-12 lg:col-span-6">
                                    <label for="service_type" class="form-label">Service Type</label>
                                    <select id="service_type" name="service_type" required class="form-select">
                                        <option value="" selected disabled>-- Select Service --</option>
                                        <option value="food_delivery" {{ old('service_type') == 'food_delivery' ? 'selected' : '' }}>Food Delivery</option>
                                        <option value="grocery" {{ old('service_type') == 'grocery' ? 'selected' : '' }}>Grocery</option>
                                        <option value="laundry" {{ old('service_type') == 'laundry' ? 'selected' : '' }}>Laundry</option>
                                        <option value="bills_payment" {{ old('service_type') == 'bills_payment' ? 'selected' : '' }}>Bills Payment</option>
                                        <option value="other" {{ old('service_type') == 'other' ? 'selected' : '' }}>Other Errand</option>
                                    </select>
                                    @error('service_type')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tie-Up Partner -->
                        <div id="tie-up-partner-field" style="display: {{ old('booking_type') == 'tie_up' ? 'block' : 'none' }};" class="space-y-4">
                            <h3 class="section-title">Partner Selection</h3>
                            <div class="form-group">
                                <label for="tie_up_partner_id" class="form-label">Partner Shop</label>
                                <select id="tie_up_partner_id" name="tie_up_partner_id" class="form-select">
                                    <option value="" selected disabled>-- Select Partner --</option>
                                    @foreach($tieUpPartners as $partner)
                                        <option value="{{ $partner->id }}" {{ old('tie_up_partner_id') == $partner->id ? 'selected' : '' }}>
                                            {{ $partner->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tie_up_partner_id')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Direct Request Items -->
                        <div id="direct-request-items" style="display: {{ old('booking_type') == 'direct' ? 'block' : 'none' }};" class="space-y-4">
                            a<h3 class="section-title">Items / Tasks</h3>
                            <div id="items-container" class="space-y-3">
                                <div class="grid col-span-12 gap-4 text-sm font-medium text-gray-700 pb-2 border-b border-gray-200">
                                    <div class="col-span-5">Item / Task</div>
                                    <div class="col-span-3">Quantity</div>
                                    <div class="col-span-3">Notes</div>
                                    <div class="col-span-1"></div>
                                </div>
                                <div class="item-row grid col-span-12 gap-4 items-center" style="display: none;" id="item-template">
                                    <div class="col-span-5">
                                        <input type="text" name="items[__INDEX__][name]" class="form-input text-sm" placeholder="Item Name / Task Detail">
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" name="items[__INDEX__][quantity]" class="form-input text-sm text-center" placeholder="Qty" value="1" min="1">
                                    </div>
                                    <div class="col-span-3">
                                        <input type="text" name="items[__INDEX__][notes]" class="form-input text-sm" placeholder="Notes (optional)">
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" class="remove-item-btn btn btn-secondary w-8 h-8 p-0">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                @if(old('items'))
                                    @foreach(old('items') as $index => $item)
                                        <div class="item-row grid col-span-12 gap-4 items-center">
                                            <div class="col-span-5">
                                                <input type="text" name="items[{{ $index }}][name]" class="form-input text-sm @error('items.'.$index.'.name') border-red-500 @enderror" placeholder="Item Name / Task Detail" value="{{ $item['name'] ?? '' }}">
                                                @error('items.'.$index.'.name')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-span-3">
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-input text-sm text-center @error('items.'.$index.'.quantity') border-red-500 @enderror" placeholder="Qty" value="{{ $item['quantity'] ?? 1 }}" min="1">
                                                @error('items.'.$index.'.quantity')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-span-3">
                                                <input type="text" name="items[{{ $index }}][notes]" class="form-input text-sm @error('items.'.$index.'.notes') border-red-500 @enderror" placeholder="Notes (optional)" value="{{ $item['notes'] ?? '' }}">
                                                @error('items.'.$index.'.notes')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-span-1 flex justify-center">
                                                <button type="button" class="remove-item-btn btn btn-secondary w-8 h-8 p-0">
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="item-row grid col-span-12 gap-4 items-center">
                                        <div class="col-span-5">
                                            <input type="text" name="items[0][name]" class="form-input text-sm" placeholder="Item Name / Task Detail">
                                        </div>
                                        <div class="col-span-3">
                                            <input type="number" name="items[0][quantity]" class="form-input text-sm text-center" placeholder="Qty" value="1" min="1">
                                        </div>
                                        <div class="col-span-3">
                                            <input type="text" name="items[0][notes]" class="form-input text-sm" placeholder="Notes (optional)">
                                        </div>
                                        <div class="col-span-1 flex justify-center">
                                            <button type="button" class="remove-item-btn btn btn-secondary w-8 h-8 p-0">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-4">
                                <button type="button" id="add-item-btn" class="btn btn-secondary gap-2">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Item / Task
                                </button>
                            </div>
                        </div>

                        <!-- Scheduling -->
                        <div class="space-y-4">
                            <h3 class="section-title">Scheduling (Optional)</h3>
                            <div class="grid col-span-12 gap-6 lg:grid-cols-2">
                                <div class="form-group col-span-12 lg:col-span-6">
                                    <label for="scheduled_at_date" class="form-label">Date</label>
                                    <input type="date" id="scheduled_at_date" name="scheduled_at_date" value="{{ old('scheduled_at_date') }}" min="{{ date('Y-m-d') }}" class="form-input">
                                    @error('scheduled_at_date')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group col-span-12 lg:col-span-6">
                                    <label for="scheduled_at_time" class="form-label">Time</label>
                                    <input type="time" id="scheduled_at_time" name="scheduled_at_time" value="{{ old('scheduled_at_time') }}" class="form-input">
                                    @error('scheduled_at_time')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="space-y-4">
                            <h3 class="section-title">Additional Information</h3>
                            <div class="form-group">
                                <label for="special_instructions" class="form-label">Special Instructions</label>
                                <textarea id="special_instructions" name="special_instructions" rows="4" class="form-textarea" placeholder="Any special instructions or notes for your booking">{{ old('special_instructions') }}</textarea>
                                @error('special_instructions')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-4 flex-col-mobile">
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Place Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let itemIndex = {{ old('items') ? count(old('items')) : 1 }};
                const itemsContainer = document.getElementById('items-container');
                const template = document.getElementById('item-template');

                document.getElementById('add-item-btn')?.addEventListener('click', function() {
                    if (!template) return;
                    const clone = template.cloneNode(true);
                    clone.style.display = 'grid';
                    clone.id = '';
                    clone.querySelectorAll('[name]').forEach(input => {
                        input.name = input.name.replace('__INDEX__', itemIndex);
                        if (input.type === 'text' || input.type === 'number') input.value = input.type === 'number' ? '1' : '';
                    });
                    itemsContainer.appendChild(clone);
                    itemIndex++;
                    attachRemoveListeners();
                });

                function attachRemoveListeners() {
                    itemsContainer.querySelectorAll('.remove-item-btn').forEach(button => {
                        button.removeEventListener('click', handleRemoveItem);
                        button.addEventListener('click', handleRemoveItem);
                    });
                }

                function handleRemoveItem(event) {
                    const itemRows = itemsContainer.querySelectorAll('.item-row:not(#item-template)');
                    if (itemRows.length > 1) {
                        event.target.closest('.item-row').remove();
                    } else if (itemRows.length === 1) {
                        const lastRow = event.target.closest('.item-row');
                        lastRow.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => input.value = input.type === 'number' ? '1' : '');
                    }
                }

                attachRemoveListeners();

                window.toggleBookingFields = function() {
                    const bookingType = document.getElementById('booking_type').value;
                    const tieUpField = document.getElementById('tie-up-partner-field');
                    const directItemsField = document.getElementById('direct-request-items');
                    const tieUpSelect = tieUpField?.querySelector('select');

                    if (bookingType === 'tie_up') {
                        if (tieUpField) tieUpField.style.display = 'block';
                        if (directItemsField) directItemsField.style.display = 'none';
                        if (tieUpSelect) tieUpSelect.required = true;
                    } else if (bookingType === 'direct') {
                        if (tieUpField) tieUpField.style.display = 'none';
                        if (directItemsField) directItemsField.style.display = 'block';
                        if (tieUpSelect) {
                            tieUpSelect.required = false;
                            tieUpSelect.value = '';
                        }
                    } else {
                        if (tieUpField) tieUpField.style.display = 'none';
                        if (directItemsField) directItemsField.style.display = 'none';
                        if (tieUpSelect) tieUpSelect.required = false;
                    }
                };

                window.toggleBookingFields();
            });
        </script>
    @endpush
</x-app-layout>