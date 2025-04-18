<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2>Dashboard</h2>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">{{ date('F j, Y') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 animate-fadeInUp">
        <!-- Welcome Card -->
        <div class="card mb-6">
            <div class="flex items-center gap-4">
                <div class="avatar-large">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-semibold mb-1">{{ __("Welcome back, ") }} {{ Auth::user()->name }}!</h2>
                    <p class="text-gray-500">Manage your bookings and account information all in one place.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="section-title">Quick Actions</h3>
                    <span class="badge">Essential</span>
                </div>
                <div class="space-y-3">
                    <a href="{{ route('customer.bookings.create') }}" class="action-link">
                        <div class="action-icon bg-yellow-light">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 4.16669V15.8334" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4.16669 10H15.8334" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <span class="action-title">Place New Booking</span>
                            <span class="action-desc">Schedule a new service or appointment</span>
                        </div>
                        <svg class="action-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="{{ route('customer.bookings.index') }}" class="action-link">
                        <div class="action-icon bg-blue-50">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.6667 5H3.33333C2.8731 5 2.5 5.3731 2.5 5.83333V14.1667C2.5 14.6269 2.8731 15 3.33333 15H16.6667C17.1269 15 17.5 14.6269 17.5 14.1667V5.83333C17.5 5.3731 17.1269 5 16.6667 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2.5 8.33334H17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="action-content">
                            <span class="action-title">View Booking History</span>
                            <span class="action-desc">Check past and upcoming bookings</span>
                        </div>
                        <svg class="action-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="section-title">Account Overview</h3>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-yellow-dark hover:underline">Edit</a>
                </div>
                <div class="space-y-3">
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ Auth::user()->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ Auth::user()->created_at->format('F Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-large {
            width: 48px;
            height: 48px;
            background-color: var(--yellow-light);
            color: var(--yellow-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            letter-spacing: -0.025em;
        }

        .badge {
            background-color: var(--yellow-light);
            color: var(--yellow-dark);
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
        }

        .card {
            margin-bottom: 1.5rem;
        }

        .space-y-3 > * + * {
            margin-top: 0.75rem;
        }

        .action-link {
            display: flex;
            align-items: center;
            padding: 0.875rem;
            border-radius: 8px;
            border: 1px solid var(--gray-100);
            transition: all 0.15s ease;
            text-decoration: none;
            color: var(--gray-900);
        }

        .action-link:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-200);
            transform: translateY(-1px);
        }

        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--yellow-dark);
            margin-right: 0.875rem;
            flex-shrink: 0;
        }

        .action-content {
            flex: 1;
        }

        .action-title {
            display: block;
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--gray-900);
        }

        .action-desc {
            display: block;
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .action-arrow {
            color: var(--gray-400);
            flex-shrink: 0;
            transition: transform 0.15s ease;
        }

        .action-link:hover .action-arrow {
            transform: translateX(3px);
            color: var(--yellow-dark);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding: 0.875rem;
            border-radius: 8px;
            border: 1px solid var(--gray-100);
            background-color: var(--gray-50);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-500);
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 500;
            color: var(--gray-900);
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-1 {
            margin-bottom: 0.25rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-gray-500 {
            color: var(--gray-500);
        }

        .text-yellow-dark {
            color: var(--yellow-dark);
        }

        .hover\:underline:hover {
            text-decoration: underline;
        }

        .bg-yellow-light {
            background-color: var(--yellow-light);
        }

        .bg-blue-50 {
            background-color: rgba(59, 130, 246, 0.1);
        }

        @media (max-width: 768px) {
            .grid-cols-1 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }

        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</x-app-layout>