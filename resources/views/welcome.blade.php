<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sulogoon - Errand & Delivery Service in Tagum City</title>

        @vite([
            'resources/css/welcome.css'
        ])

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

</head>
<body>
    <!-- Mobile menu overlay -->
    <div class="overlay" id="overlay"></div>
    
    <nav id="navbar">
        <div class="nav-container">
            <a href="/" class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
                </svg>
                Sulogoon
            </a>

            <!-- Mobile menu toggle -->
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>

            @if (Route::has('login'))
                <ul class="nav-auth-links" id="navLinks">
                    @auth
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Log in</a></li>

                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="cta-button">Register</a></li>
                        @endif
                    @endauth
                </ul>
            @endif
        </div>
    </nav>

    <section class="stripe-section primary hero">
        <div class="animated-blob blob-1"></div>
        <div class="animated-blob blob-2"></div>
        
        <div class="hero-content">
            <div class="hero-badge animate-fadeIn" data-aos="fade-up">Quick & Reliable Delivery</div>
            <h1 data-aos="fade-up" data-aos-delay="100">Your <span class="highlight">Go-To Errand Service</span> in Tagum City</h1>
            <p class="animate-fadeInUp" data-aos="fade-up" data-aos-delay="200">From food delivery and groceries to bill payments and laundry, let Sulogoon handle your everyday tasks. <span class="tagalog-phrase typewriter">Pwede magpa-sugo?</span></p>
            
            <div class="cta-buttons" data-aos="fade-up" data-aos-delay="300">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="cta-button-main animate-pulse">Request a Service</a>
                    <a href="#how-it-works" class="cta-button-main btn-secondary">How It Works</a>
                @else
                    <a href="#services" class="cta-button-main animate-pulse">Explore Services</a>
                    <a href="#how-it-works" class="cta-button-main btn-secondary">How It Works</a>
                @endif
            </div>
        </div>
    </section>

    <div class="diagonal-divider"></div>

    <section id="services" class="stripe-section light features">
        <div class="section-header">
            <div class="section-badge" data-aos="fade-up">Our Services</div>
            <h2 class="section-title" data-aos="fade-up" data-aos-delay="100">Comprehensive Errand Solutions</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="200">We offer a wide range of delivery and errand options tailored for the Tagum community.</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 13V16M12 13C12.8284 13 13.5 12.3284 13.5 11.5C13.5 10.6716 12.8284 10 12 10C11.1716 10 10.5 10.6716 10.5 11.5C10.5 12.3284 11.1716 13 12 13ZM15 19H9C6.23858 19 4 16.7614 4 14C4 11.2386 6.23858 9 9 9H15C17.7614 9 20 11.2386 20 14C20 16.7614 17.7614 19 15 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Food Delivery</h3>
                <p>Craving local favorites? We'll pick up and deliver meals from your favorite restaurants right to your doorstep.</p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 3H4.5L5.5 11H19L17 16H7M7 16L5.5 11M7 16H17M5.5 11L4.5 3M10 21C10.5523 21 11 20.5523 11 20C11 19.4477 10.5523 19 10 19C9.44772 19 9 19.4477 9 20C9 20.5523 9.44772 21 10 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 21C17.5523 21 18 20.5523 18 20C18 19.4477 17.5523 19 17 19C16.4477 19 16 19.4477 16 20C16 20.5523 16.4477 21 17 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Grocery Errands</h3>
                <p>Short on time? Send us your shopping list, and we'll handle the shopping and delivery to your location.</p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 16V14C9 12.8954 9.89543 12 11 12H13C14.1046 12 15 12.8954 15 14V16M13 19H11M6 19H18M5 11L6 7H18L19 11H5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Laundry Errands</h3>
                <p>Convenient pick-up and drop-off service for your laundry needs, saving you time and hassle.</p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21C12 21 4.5 15.75 4.5 10.5C4.5 7.48071 6.98071 5 10 5C11.5128 5 12.9057 5.6444 13.8865 6.72791M12 21C12 21 19.5 15.75 19.5 10.5C19.5 7.48071 17.0193 5 14 5C12.4872 5 11.0943 5.6444 10.1135 6.72791" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Pet Care Assistance</h3>
                <p>Need pet food or supplies delivered? We help care for your furry friends with quick deliveries.</p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 3H16M8 21H16M3 8V16M21 8V16M7 12H17M12 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Household Cleaning</h3>
                <p>Assistance with basic household cleaning tasks, helping you maintain a clean and comfortable living space.</p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6H21M4 10H20M6 14H18M7 18H17M5 3H19C19.5523 3 20 3.44772 20 4V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Bill Payments</h3>
                <p>Avoid queues – we can securely handle your utility and service bill payments so you don't have to.</p>
            </div>
        </div>
    </section>

    <div class="diagonal-divider reverse"></div>

    <section id="how-it-works" class="stripe-section primary how-it-works">
        <div class="section-header">
            <div class="section-badge" data-aos="fade-up">Process</div>
            <h2 class="section-title" data-aos="fade-up" data-aos-delay="100">Getting Errands Done is Easy</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="200">Our streamlined process ensures your tasks are completed quickly and efficiently.</p>
        </div>
        
        <div class="steps">
            <div class="step" data-aos="fade-up" data-aos-delay="100">
                <div class="step-number">1</div>
                <h3>Request Service</h3>
                <p>Tell us what you need via our platform or contact details. We'll confirm availability right away.</p>
            </div>
            
            <div class="step" data-aos="fade-up" data-aos-delay="300">
                <div class="step-number">2</div>
                <h3>Rider Assigned</h3>
                <p>A friendly, registered Sulogoon rider accepts your task request and prepares for service.</p>
            </div>
            
            <div class="step" data-aos="fade-up" data-aos-delay="500">
                <div class="step-number">3</div>
                <h3>Task Complete</h3>
                <p>Your errand is handled efficiently and completed as requested, with real-time updates.</p>
            </div>
        </div>
    </section>

    <div class="diagonal-divider"></div>

    <section class="stripe-section light testimonials">
        <div class="section-header">
            <div class="section-badge" data-aos="fade-up">Testimonials</div>
            <h2 class="section-title" data-aos="fade-up" data-aos-delay="100">What Our Customers Say</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="200">Hear from our satisfied customers about their Sulogoon experience.</p>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-rating">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </div>
                <p class="testimonial-content">"Sulogoon has been a lifesaver for my busy schedule. Their riders are always on time and very professional. I use their service weekly for my grocery shopping!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">MA</div>
                    <div class="testimonial-info">
                        <h4>Maria Alonzo</h4>
                        <p>Tagum City Resident</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-rating">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </div>
                <p class="testimonial-content">"As a small business owner, I rely on Sulogoon for quick deliveries to my customers. Their service is reliable and has helped my business grow significantly."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">RD</div>
                    <div class="testimonial-info">
                        <h4>Ryan Dominguez</h4>
                        <p>Local Business Owner</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-rating">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </div>
                <p class="testimonial-content">"I use Sulogoon to pay my bills and it saves me so much time. The app is easy to use and their customer service is excellent. Highly recommended!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">JS</div>
                    <div class="testimonial-info">
                        <h4>Jenny Santos</h4>
                        <p>Working Professional</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="diagonal-divider dark"></div>

    <section class="stripe-section dark cta-section">
        <div class="cta-shape top-right"></div>
        <div class="cta-shape bottom-left"></div>
        
        <div class="cta-container" data-aos="fade-up">
            <h2 class="cta-title">Ready to Skip the Errands?</h2>
            <p class="cta-text">Let Sulogoon handle your daily tasks while you focus on what matters most to you.</p>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="cta-button-main animate-pulse">Get Started Today</a>
            @else
                <a href="#services" class="cta-button-main animate-pulse">Explore Our Services</a>
            @endif
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <a href="/" class="footer-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
                    </svg>
                    Sulogoon
                </a>
                <p>Your reliable food delivery and errand service partner in Tagum City. Providing convenient solutions since 2019.</p>
                <p>2nd Floor, RCC Building, Osmeña Extension, Tagum City.</p>
                
                <div class="footer-social">
                    <a href="#" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9.198 21.5h4v-8.01h3.604l.396-3.98h-4V7.5a1 1 0 0 1 1-1h3v-4h-3a5 5 0 0 0-5 5v2.01h-2l-.396 3.98h2.396v8.01Z"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 0 1 1.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 0 1-1.153 1.772 4.915 4.915 0 0 1-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 0 1-1.772-1.153 4.904 4.904 0 0 1-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 0 1 1.153-1.772A4.897 4.897 0 0 1 5.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 1.802c-2.67 0-2.986.01-4.04.059-.976.045-1.505.207-1.858.344-.466.182-.8.398-1.15.748-.35.35-.566.684-.748 1.15-.137.353-.3.882-.344 1.857-.048 1.055-.058 1.37-.058 4.041 0 2.67.01 2.986.058 4.04.045.977.207 1.505.344 1.858.182.466.399.8.748 1.15.35.35.684.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058 2.67 0 2.987-.01 4.04-.058.977-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.684.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041 0-2.67-.01-2.986-.058-4.04-.045-.977-.207-1.505-.344-1.858a3.097 3.097 0 0 0-.748-1.15 3.098 3.098 0 0 0-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.055-.048-1.37-.058-4.041-.058zm0 3.063a5.135 5.135 0 1 1 0 10.27 5.135 5.135 0 0 1 0-10.27zm0 8.468a3.333 3.333 0 1 0 0-6.666 3.333 3.333 0 0 0 0 6.666zm6.538-8.469a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0z"/></svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.8a8.49 8.49 0 0 1-2.36.64 4.13 4.13 0 0 0 1.81-2.27 8.21 8.21 0 0 1-2.61 1 4.1 4.1 0 0 0-7 3.74 11.64 11.64 0 0 1-8.45-4.29 4.16 4.16 0 0 0-.55 2.07 4.09 4.09 0 0 0 1.82 3.41 4.05 4.05 0 0 1-1.86-.51v.05a4.1 4.1 0 0 0 3.3 4 3.93 3.93 0 0 1-1.1.17 4.9 4.9 0 0 1-.77-.07 4.11 4.11 0 0 0 3.83 2.84A8.22 8.22 0 0 1 3 18.34a11.57 11.57 0 0 0 6.29 1.85A11.59 11.59 0 0 0 21 8.45v-.53a8.43 8.43 0 0 0 2-2.12Z"/></svg>
                    </a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>Services</h4>
                <ul>
                    <li><a href="#services">Food Delivery</a></li>
                    <li><a href="#services">Grocery Errands</a></li>
                    <li><a href="#services">Bill Payments</a></li>
                    <li><a href="#services">Laundry Errands</a></li>
                    <li><a href="#services">Pet Care Assistance</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>
                Company
            </h4>
            <ul>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="#careers">Careers</a></li>
                <li><a href="#privacy">Privacy Policy</a></li>
                <li><a href="#terms">Terms of Service</a></li>
            </ul>
        </div>
        
        <div class="footer-links">
            <h4>Support</h4>
            <ul>
                <li><a href="#faq">FAQs</a></li>
                <li><a href="#help">Help Center</a></li>
                <li><a href="#rider">Become a Rider</a></li>
                <li><a href="#feedback">Feedback</a></li>
            </ul>
        </div>
    </div>
    
    <div class="copyright">
        <p>&copy; 2025 Sulogoon. All rights reserved. Designed with <span style="color: var(--yellow-secondary);">♥</span> in Tagum City.</p>
    </div>
</footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>

        @vite([
            'resources/js/welcome.js'
        ])

    @stack('scripts')
</body>
</html>