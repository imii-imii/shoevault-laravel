<!DOCTYPE html>
<html>

<head>
    <title>NIKE PRODUCTS PAGE</title>
    <meta charset="utf-8">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/reservation-home.css') }}">
</head>

<body>

    <!-- TOP NAV -->
    <div class="navbar">
        <a href="#" class="logo">
            <img src="{{ asset('reservation-assets/logo.png') }}" alt="Nike">
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span class="nav-toggle-bar"></span>
        </button>
        <div class="navbar-right menu" id="mainNavMenu" aria-hidden="true">
            <a href="#slider" class="active">Home</a>
            <a href="#services">Services</a>
            <a href="#testimonials">Testimonials</a>
            <a href="#about-us">About Us</a>
            <a href="#contact">Contact Us</a>
        </div>
    </div>
    <!-- END TOP NAV -->

    <!-- MAIN -->
    <div id="slider" class="slider">
        <div class="slider-content">
            <!-- Left side - Welcome Info -->
            <div class="welcome-info">
                <div class="welcome-header">
                    <h1>Welcome to <span class="brand-name">Shoe Vault</span></h1>
                    <h2>Your Premier Footwear Destination in Batangas</h2>
                </div>
                <div class="welcome-description">
                    <p>Discover the finest collection of premium athletic footwear from world-renowned brands. Experience unmatched quality, authentic products, and exceptional service that has made us Batangas' most trusted shoe destination.</p>
                </div>
                <div class="welcome-features">
                    <div class="feature-item">
                        <i class="bx bx-check-circle"></i>
                        <span>100% Authentic Products</span>
                    </div>
                    <div class="feature-item">
                        <i class="bx bx-support"></i>
                        <span>Expert Fitting Service</span>
                    </div>
                    <div class="feature-item">
                        <i class="bx bx-award"></i>
                        <span>Premium Brand Selection</span>
                    </div>
                    <div class="feature-item">
                        <i class="bx bx-shield-check"></i>
                        <span>Quality Guarantee</span>
                    </div>
                </div>
                <div class="welcome-actions">
                    <a href="{{ route('reservation.portal') }}" class="cta-primary">Reserve Now</a>
                    <a href="{{ route('reservation.size-converter') }}" class="cta-secondary">Size Converter</a>
                </div>
            </div>
            
            <!-- Right side - Shoe Showcase -->
            <div class="shoe-showcase">
                <div class="showcase-container">
                    <div class="floating-shoe active" data-shoe="1">
                        <img src="{{ asset('reservation-assets/air-max-alpha-tr-3-mens-training-shoe-0C1CV7.png') }}" alt="Nike Air Max Alpha TR 3">
                        <div class="shoe-label">Air Max Alpha TR 3</div>
                    </div>
                    <div class="floating-shoe" data-shoe="2">
                        <img src="{{ asset('reservation-assets/air-zoom-superrep-mens-hiit-class-shoe-ZWLnJW (1).png') }}" alt="Nike Air Zoom SuperRep">
                        <div class="shoe-label">Air Zoom SuperRep</div>
                    </div>
                    <div class="floating-shoe" data-shoe="3">
                        <img src="{{ asset('reservation-assets/zoom-fly-3-mens-running-shoe-XhzpPH.png') }}" alt="Nike Zoom Fly 3">
                        <div class="shoe-label">Zoom Fly 3</div>
                    </div>
                    <div class="floating-shoe" data-shoe="4">
                        <img src="{{ asset('reservation-assets/zoomx-vaporfly-next-running-shoe-4Q5jfG.png') }}" alt="Nike ZoomX Vaporfly NEXT%">
                        <div class="shoe-label">ZoomX Vaporfly NEXT%</div>
                    </div>
                </div>
                <div class="showcase-bg-pattern"></div>
            </div>
        </div>
    </div>
    <!-- END MAIN -->

    <!-- SERVICES SECTION -->
    <section id="services" class="services-section">
        <div class="services-container">
            <div class="services-header">
                <h2>Our Services</h2>
                <p>Discover the premium services we offer at Shoe Vault Batangas</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bx bx-shopping-bag"></i>
                    </div>
                    <h3>Premium Footwear</h3>
                    <p>Exclusive collection of Nike, Adidas, and other top brands. Authentic products with the latest releases and classic favorites.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bx bx-customize"></i>
                    </div>
                    <h3>Custom Fitting</h3>
                    <p>Professional shoe fitting service to ensure perfect comfort and performance. Expert consultation for optimal fit.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bx bx-support"></i>
                    </div>
                    <h3>Expert Support</h3>
                    <p>Knowledgeable staff to help you choose the right footwear for your specific needs and activities.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bx bx-check-shield"></i>
                    </div>
                    <h3>Quality Guarantee</h3>
                    <p>100% authentic products with warranty protection. We stand behind every pair of shoes we sell.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bx bx-store"></i>
                    </div>
                    <h3>Local Store</h3>
                    <p>Visit our physical store in Batangas for a hands-on shopping experience with our friendly staff.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="bx bx-gift"></i>
                    </div>
                    <h3>Loyalty Rewards</h3>
                    <p>Enjoy exclusive discounts specially for our loyal customers.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- END SERVICES SECTION -->

    <!-- TESTIMONIALS SECTION -->
    <section id="testimonials" class="testimonials-section">
        <div class="testimonials-container">
            <div class="testimonials-header">
                <h2>Customer Testimonials</h2>
                <p>What our valued customers say about Shoe Vault Batangas</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-image">
                        <img src="{{ asset('reservation-assets/testimonial-1.jpg') }}" alt="Maria Santos" onerror="this.style.display='none'">
                    </div>
                    <div class="testimonial-content">
                        <p>"Amazing selection of Nike shoes! The staff was very helpful in finding the perfect fit for my running needs."</p>
                    </div>
                    <div class="testimonial-author">
                        <h4>Carlos Bugtong</h4>
                        <span>Regular Customer</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-image">
                        <img src="{{ asset('reservation-assets/testimonial-2.jpg') }}" alt="John Dela Cruz" onerror="this.style.display='none'">
                    </div>
                    <div class="testimonial-content">
                        <p>"Best shoe store in Batangas! Authentic products and excellent customer service. Highly recommended!"</p>
                    </div>
                    <div class="testimonial-author">
                        <h4>John Dela Cruz</h4>
                        <span>Sports Enthusiast</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-image">
                        <img src="{{ asset('reservation-assets/testimonial-3.jpg') }}" alt="Ana Rodriguez" onerror="this.style.display='none'">
                    </div>
                    <div class="testimonial-content">
                        <p>"Fast delivery and great prices. The quality of their shoes is outstanding. Will definitely shop here again!"</p>
                    </div>
                    <div class="testimonial-author">
                        <h4>Ana Rodriguez</h4>
                        <span>OFW</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END TESTIMONIALS SECTION -->

    <!-- ABOUT US SECTION -->
    <section id="about-us" class="about-section">
        <div class="about-container">
            <div class="about-header">
                <h2>About Shoe Vault Batangas</h2>
                <p>Your trusted destination for premium footwear in Batangas</p>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <h3>Our Story</h3>
                    <p>Shoe Vault Batangas has been serving the community since 2020, providing authentic premium footwear from top brands like Nike, Adidas, and more. We pride ourselves on offering exceptional customer service and expert fitting advice.</p>

                    <h3>Our Mission</h3>
                    <p>To provide our customers with the highest quality footwear, backed by professional service and genuine products that enhance their performance and lifestyle.</p>

                    <h3>Why Choose Us</h3>
                    <ul>
                        <li>100% Authentic Products</li>
                        <li>Expert Fitting Service</li>
                        <li>Wide Selection of Brands</li>
                        <li>Local Store Experience</li>
                        <li>Fast Delivery Service</li>
                    </ul>

                    <div class="about-stats">
                        <div class="stat-card">
                            <div class="stat-number">5+</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">5000+</div>
                            <div class="stat-label">Happy Customers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">100+</div>
                            <div class="stat-label">Shoes Available</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Customer Support</div>
                        </div>
                    </div>
                </div>

                <div class="about-image-section">
                    <div class="about-image-card">
                        <div class="about-image-placeholder">
                            <i class="bx bx-store"></i>
                        </div>
                        <h4>Our Store</h4>
                        <p>Visit our flagship store in Manghinao Proper Bauan Batangas for a premium shopping experience with expert staff ready to assist you.</p>
                    </div>

                    <div class="about-image-card">
                        <div class="about-image-placeholder">
                            <i class="bx bx-award"></i>
                        </div>
                        <h4>Quality Assured</h4>
                        <p>Every product in our collection is 100% authentic with manufacturer warranty and quality guarantee.</p>
                    </div>

                    <div class="about-image-card">
                        <div class="about-image-placeholder">
                            <i class="bx bx-heart"></i>
                        </div>
                        <h4>Customer First</h4>
                        <p>We prioritize customer satisfaction with personalized service and expert fitting consultations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END ABOUT US SECTION -->

    <!-- CONTACT SECTION -->
    <section id="contact" class="contact-section">
        <div class="contact-container">
            <div class="contact-header">
                <h2>Contact Us</h2>
                <p>Get in touch with Shoe Vault Batangas</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="bx bx-map"></i>
                        <div>
                            <h4>Address</h4>
                            <p>Manghinao Proper Bauan, Batangas</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bx bx-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>+63 912 345 6789</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bx bx-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>info@shoebaultbatangas.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bx bx-time"></i>
                        <div>
                            <h4>Business Hours</h4>
                            <p>Monday - Saturday: 9:00 AM - 8:00 PM<br>Sunday: 10:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>

                <div class="social-media-section">
                    <div class="social-media-header">
                        <h3>Follow Us</h3>
                        <p>Stay connected with us on social media for the latest updates, promotions, and new arrivals!</p>
                    </div>
                    <div class="social-media-grid">
                        <a href="https://www.facebook.com/ShoeVaultBatangas" target="_blank" class="social-media-card facebook">
                            <div class="social-media-icon">
                                <i class="bx bxl-facebook"></i>
                            </div>
                            <h4>Facebook</h4>
                            <p>Like & Follow</p>
                        </a>
                        <a href="https://instagram.com/shoevaultbatangas" target="_blank" class="social-media-card instagram">
                            <div class="social-media-icon">
                                <i class="bx bxl-instagram"></i>
                            </div>
                            <h4>Instagram</h4>
                            <p>Follow & Share</p>
                        </a>
                        <a href="https://www.tiktok.com/@shoevaultbtg" target="_blank" class="social-media-card tiktok">
                            <div class="social-media-icon">
                                <img src="{{ asset('reservation-assets/tiktok.png') }}" alt="TikTok" class="tiktok-icon">
                            </div>
                            <h4>TikTok</h4>
                            <p>Watch & Follow</p>
                        </a>
                        <a href="https://www.facebook.com/ShoeVaultBatangas" target="_blank" class="social-media-card messenger">
                            <div class="social-media-icon">
                                <i class="bx bxl-messenger"></i>
                            </div>
                            <h4>Messenger</h4>
                            <p>Chat Directly</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Removed contact form section per latest request -->
        </div>
    </section>
    <!-- END CONTACT SECTION -->

    <!-- MODAL -->
    <div id="modal" class="modal">
        <span id="modal-close" class="close">&times;</span>
        <img id="modal-content" class="modal-content">
        <div class="more-images">
            <div class="more-images-item">
                <img class="img-preview">
            </div>
            <div class="more-images-item">
                <img class="img-preview">
            </div>
            <div class="more-images-item">
                <img class="img-preview">
            </div>
            <div class="more-images-item">
                <img class="img-preview">
            </div>
        </div>
    </div>
    <!-- END MODAL -->
        <script type="text/javascript" src="{{ asset('js/reservation-home.js') }}"></script>
        <script>
            (function(){
                const toggle = document.getElementById('navToggle');
                const menu = document.getElementById('mainNavMenu');
                if(!toggle || !menu) return;
                function closeMenu(){
                    menu.classList.remove('show');
                    toggle.classList.remove('active');
                    toggle.setAttribute('aria-expanded','false');
                    menu.setAttribute('aria-hidden','true');
                }
                toggle.addEventListener('click', (e)=>{
                    e.stopPropagation();
                    const open = menu.classList.toggle('show');
                    toggle.classList.toggle('active', open);
                    toggle.setAttribute('aria-expanded', open?'true':'false');
                    menu.setAttribute('aria-hidden', open?'false':'true');
                });
                document.addEventListener('click', (e)=>{
                    if(menu.classList.contains('show') && !menu.contains(e.target) && e.target!==toggle){
                        closeMenu();
                    }
                });
                window.addEventListener('resize', ()=>{
                    if(window.innerWidth>780){
                        // reset inline/mobile states
                        menu.classList.remove('show');
                        toggle.classList.remove('active');
                        toggle.setAttribute('aria-expanded','false');
                        menu.setAttribute('aria-hidden','false');
                    } else {
                        menu.setAttribute('aria-hidden', menu.classList.contains('show')? 'false':'true');
                    }
                });
                // Close when a nav link clicked (optional scroll logic already in index.js)
                menu.querySelectorAll('a').forEach(a=> a.addEventListener('click', ()=>{ if(window.innerWidth<=780){ closeMenu(); }}));
            })();
        </script>
</body>

</html>
