<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Delete Account</h2>
        <p class="mt-1 text-sm text-gray-600">Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>
    </header>

    <button class="btn btn-danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Delete Account</button>

    <div class="modal" x-data="{ show: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }" x-show="show" x-on:open-modal.window="show = true" x-on:close.window="show = false" x-cloak>
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-medium text-gray-900">Are you sure you want to delete your account?</h2>
                <p class="mt-1 text-sm text-gray-600">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>

                <div class="form-group mt-6">
                    <label for="password" class="form-label sr-only">Password</label>
                    <input id="password" name="password" type="password" class="form-input" placeholder="Password" />
                    @error('password', 'userDeletion')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <button type="button" class="btn btn-secondary" x-on:click="show = false">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 200;
        transition: opacity 0.3s ease;
    }

    .modal-content {
        background-color: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        max-width: 500px;
        width: 100%;
        margin: 1rem;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--red-accent) 0%, #B91C1C 100%);
        color: var(--white);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #B91C1C 0%, var(--red-accent) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
    }

    [x-cloak] {
        display: none;
    }
</style>