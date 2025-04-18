{{-- resources/views/layouts/navigation.blade.php --}}
<nav id="navbar" class="bg-white">
    <div class="nav-container">
        @auth {{-- Ensure user is logged in before checking roles --}}
            @php
                $user = Auth::user();
                $dashboardRoute = 'customer.dashboard'; // Default route
                $logoRoute = 'customer.dashboard'; // Default route

                if ($user->isAdmin() || $user->isStaff()) { // Assuming User model has isAdmin() and isStaff() helpers
                    $dashboardRoute = 'admin.dashboard';
                    $logoRoute = 'admin.dashboard';
                } elseif ($user->isRider()) { // Assuming User model has isRider() helper
                    $dashboardRoute = 'rider.dashboard';
                    $logoRoute = 'rider.dashboard';
                }
                // Customer is the default if none of the above match or only isCustomer() is true
            @endphp

            <a href="{{ route($logoRoute) }}" class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
                </svg>
                Sulogoon {{-- Or MyGoRunner --}}
            </a>

            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <ul class="nav-auth-links" id="navLinks">
                {{-- Common Dashboard Link --}}
                <li>
                    <a href="{{ route($dashboardRoute) }}" class="{{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">Dashboard</a>
                </li>

                {{-- Role-Specific Links --}}
                @if ($user->isAdmin() || $user->isStaff())
                    {{-- Admin/Staff Links --}}
                    <li><a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">Services</a></li>
                    <li><a href="{{ route('admin.riders.index') }}" class="{{ request()->routeIs('admin.riders.*') ? 'active' : '' }}">Riders</a></li>
                    {{-- Add other admin links: Dispatch, Reports, Redemptions etc. --}}

                @elseif ($user->isRider())
                    {{-- Rider Links --}}
                    {{-- <li><a href="{{ route('rider.tasks.index') }}" class="{{ request()->routeIs('rider.tasks.*') ? 'active' : '' }}">My Tasks</a></li> --}}
                    {{-- <li><a href="{{ route('rider.attendance.index') }}" class="{{ request()->routeIs('rider.attendance.*') ? 'active' : '' }}">Attendance</a></li> --}}
                    {{-- <li><a href="{{ route('rider.earnings.index') }}" class="{{ request()->routeIs('rider.earnings.*') ? 'active' : '' }}">Earnings</a></li> --}}
                     <li><a href="#">My Tasks</a></li> {{-- Placeholder --}}
                     <li><a href="#">Attendance</a></li> {{-- Placeholder --}}
                     <li><a href="#">Earnings</a></li> {{-- Placeholder --}}


                @else {{-- Customer Links --}}
                    <li><a href="{{ route('customer.bookings.index') }}" class="{{ request()->routeIs('customer.bookings.index', 'customer.bookings.show') ? 'active' : '' }}">My Bookings</a></li>
                    <li><a href="{{ route('customer.bookings.create') }}" class="{{ request()->routeIs('customer.bookings.create') ? 'active' : '' }}">New Booking</a></li>
                     {{-- Add link to browse services later --}}
                @endif


                {{-- User Dropdown (Common) --}}
                <li class="dropdown">
                    <button class="dropdown-toggle">
                        <span class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="name">{{ Auth::user()->name }}</span>
                        <svg class="chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('profile.edit') }}">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 8C9.65685 8 11 6.65685 11 5C11 3.34315 9.65685 2 8 2C6.34315 2 5 3.34315 5 5C5 6.65685 6.34315 8 8 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2.5 13.5C2.5 11.0147 4.51472 9 7 9H9C11.4853 9 13.5 11.0147 13.5 13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Profile
                        </a></li>
                        <li class="divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.5 2H12.5C13.0523 2 13.5 2.44772 13.5 3V13C13.5 13.5523 13.0523 14 12.5 14H10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M6.5 11.5L10.5 7.5L6.5 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10.5 7.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Log Out
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        @else {{-- Links for Guests (Not Logged In) --}}
            <a href="{{ route('login') }}" class="logo"> {{-- Or home route --}}
                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
                </svg>
                Sulogoon {{-- Or MyGoRunner --}}
            </a>
             <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-guest-links" id="navLinks">
                 <li><a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a></li>
                 @if (Route::has('register'))
                    <li><a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">Register</a></li>
                 @endif
            </ul>
        @endauth
    </div>
    <div class="overlay" id="overlay"></div>
</nav>