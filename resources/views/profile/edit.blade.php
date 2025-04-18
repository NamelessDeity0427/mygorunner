<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Booking Profile</h2>
            <span class="badge">{{ Auth::user()->name }}</span>
        </div>
    </x-slot>

    <!-- Profile Header -->
    <div class="booking-profile-header">
        <div class="booking-profile-container">
            <div class="booking-profile-avatar">
                <div class="avatar-circle">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
            </div>
            <div class="booking-profile-info">
                <h1>{{ Auth::user()->name }}</h1>
                <p>{{ Auth::user()->email }}</p>
                @if (Auth::user()->created_at)
                    <span class="member-since">Member since {{ Auth::user()->created_at->format('M Y') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="booking-profile-content">
        <div class="booking-sidebar">
            <div class="sidebar-menu">
                <a href="#profile-info" class="sidebar-menu-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="currentColor"/>
                    </svg>
                    <span>Account Details</span>
                </a>
                <a href="#payment-methods" class="sidebar-menu-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 4H4C2.89 4 2.01 4.89 2.01 6L2 18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8Z" fill="currentColor"/>
                    </svg>
                    <span>Payment Methods</span>
                </a>
                <a href="#security" class="sidebar-menu-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 8H17V6C17 3.24 14.76 1 12 1C9.24 1 7 3.24 7 6V8H6C4.9 8 4 8.9 4 10V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V10C20 8.9 19.1 8 18 8ZM12 17C10.9 17 10 16.1 10 15C10 13.9 10.9 13 12 13C13.1 13 14 13.9 14 15C14 16.1 13.1 17 12 17ZM15.1 8H8.9V6C8.9 4.29 10.29 2.9 12 2.9C13.71 2.9 15.1 4.29 15.1 6V8Z" fill="currentColor"/>
                    </svg>
                    <span>Security</span>
                </a>
                <a href="#booking-history" class="sidebar-menu-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 3C8.03 3 4 7.03 4 12H1L4.89 15.89L4.96 16.03L9 12H6C6 8.13 9.13 5 13 5C16.87 5 20 8.13 20 12C20 15.87 16.87 19 13 19C11.07 19 9.32 18.21 8.06 16.94L6.64 18.36C8.27 19.99 10.51 21 13 21C17.97 21 22 16.97 22 12C22 7.03 17.97 3 13 3ZM12 8V13L16.28 15.54L17 14.33L13.5 12.25V8H12Z" fill="currentColor"/>
                    </svg>
                    <span>Booking History</span>
                </a>
                <a href="#account-deletion" class="sidebar-menu-item danger">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" fill="currentColor"/>
                    </svg>
                    <span>Delete Account</span>
                </a>
            </div>
            
            <div class="booking-summary">
                <h3>Booking Summary</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-value">0</span>
                        <span class="summary-label">Active Bookings</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-value">0</span>
                        <span class="summary-label">Completed</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-value">0</span>
                        <span class="summary-label">Canceled</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="booking-main">
            <section class="content-section" id="profile-info">
                <div class="section-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="currentColor"/>
                        </svg>
                        Account Details
                    </h2>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <section class="content-section" id="payment-methods">
                <div class="section-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4C2.89 4 2.01 4.89 2.01 6L2 18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8Z" fill="currentColor"/>
                        </svg>
                        Payment Methods
                    </h2>
                </div>
                <div class="payment-methods-container">
                    <div class="payment-method-card">
                        <div class="payment-method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="24" height="24" rx="6" fill="#E7F1FF"/>
                                <path d="M16 7H8C6.89 7 6 7.89 6 9V15C6 16.11 6.89 17 8 17H16C17.11 17 18 16.11 18 15V9C18 7.89 17.11 7 16 7ZM16 11H8V9H16V11Z" fill="#3B82F6"/>
                            </svg>
                        </div>
                        <div class="payment-method-info">
                            <div class="payment-method-primary">
                                <span class="payment-method-name">Add Payment Method</span>
                            </div>
                            <div class="payment-method-action">
                                <button class="add-payment-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" fill="currentColor"/>
                                    </svg>
                                    Add New
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content-section" id="security">
                <div class="section-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8H17V6C17 3.24 14.76 1 12 1C9.24 1 7 3.24 7 6V8H6C4.9 8 4 8.9 4 10V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V10C20 8.9 19.1 8 18 8ZM12 17C10.9 17 10 16.1 10 15C10 13.9 10.9 13 12 13C13.1 13 14 13.9 14 15C14 16.1 13.1 17 12 17ZM15.1 8H8.9V6C8.9 4.29 10.29 2.9 12 2.9C13.71 2.9 15.1 4.29 15.1 6V8Z" fill="currentColor"/>
                        </svg>
                        Security
                    </h2>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            <section class="content-section" id="booking-history">
                <div class="section-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 3C8.03 3 4 7.03 4 12H1L4.89 15.89L4.96 16.03L9 12H6C6 8.13 9.13 5 13 5C16.87 5 20 8.13 20 12C20 15.87 16.87 19 13 19C11.07 19 9.32 18.21 8.06 16.94L6.64 18.36C8.27 19.99 10.51 21 13 21C17.97 21 22 16.97 22 12C22 7.03 17.97 3 13 3ZM12 8V13L16.28 15.54L17 14.33L13.5 12.25V8H12Z" fill="currentColor"/>
                        </svg>
                        Booking History
                    </h2>
                </div>
                <div class="booking-history-empty">
                    <div class="empty-state-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19ZM17 12H12V17H17V12ZM17 7H12V11H17V7ZM7 7H11V17H7V7Z" fill="#94A3B8"/>
                        </svg>
                    </div>
                    <h3>No bookings yet</h3>
                    <p>Your booking history will appear here once you make a reservation</p>
                    <button class="new-booking-btn">Make a Booking</button>
                </div>
            </section>

            <section class="content-section" id="account-deletion">
                <div class="section-header danger-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" fill="currentColor"/>
                        </svg>
                        Delete Account
                    </h2>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>
        </div>
    </div>
</x-app-layout>