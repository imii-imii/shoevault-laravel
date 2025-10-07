document.addEventListener('DOMContentLoaded', function() {
            // Function to show a specific section
            function showSection(sectionId) {
                // Hide all sections first
                const allSections = ['slider', 'services', 'testimonials', 'about-us', 'contact'].map(id => document.getElementById(id));
                allSections.forEach(section => {
                    if (section) {
                        section.style.display = section.id === sectionId ? 'block' : 'none';
                    }
                });

                // Update navigation active states
                document.querySelectorAll('.navbar-right.menu a').forEach(link => {
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });

                // Special handling for slider section
                if (sectionId === 'slider') {
                    resetHomeSection();
                }
            }

            // === Shoe Showcase Auto-scroll ===
            let currentShoeIndex = 0;
            const shoes = document.querySelectorAll('.floating-shoe');
            
            function showNextShoe() {
                if (!shoes.length) return;
                
                // Hide current shoe
                shoes[currentShoeIndex].classList.remove('active');
                
                // Move to next shoe (loop back to 0 when reaching the end)
                currentShoeIndex = (currentShoeIndex + 1) % shoes.length;
                
                // Show next shoe
                shoes[currentShoeIndex].classList.add('active');
            }
            
            // Initialize shoe showcase if shoes exist
            if (shoes.length > 0) {
                // Ensure first shoe is active
                shoes[0].classList.add('active');
                
                // Auto-scroll every 3 seconds
                setInterval(showNextShoe, 3000);
            }

            // CTA Button functionality
            const ctaPrimary = document.querySelector('.cta-primary');
            const ctaSecondary = document.querySelector('.cta-secondary');
            
            if (ctaPrimary) {
                ctaPrimary.addEventListener('click', () => {
                    // Redirect to reservation portal page
                    window.location.href = '/portal';
                });
            }

            if (ctaSecondary) {
                ctaSecondary.addEventListener('click', () => {
                    // Scroll to contact section
                    showSection('contact');
                    const contactElement = document.getElementById('contact');
                    if (contactElement) {
                        contactElement.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }

            // Check if there's a stored scroll target
            const scrollTarget = localStorage.getItem('scrollTarget');
            if (scrollTarget) {
                // Clear the stored target
                localStorage.removeItem('scrollTarget');
                
                // Wait a brief moment for the page to fully load
                setTimeout(() => {
                    showSection(scrollTarget);
                    const targetElement = document.getElementById(scrollTarget);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 100);
            }

            // Add click handlers for navigation links
            document.querySelectorAll('.navbar-right.menu a').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sectionId = link.getAttribute('href').substring(1);
                    showSection(sectionId);
                    const targetElement = document.getElementById(sectionId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            let slideIndex = 0;
            let slider = document.getElementById('slider');
            let slides = slider ? slider.getElementsByClassName('slide') : [];

            // Slide control functionality
            let slideControl = document.getElementById('slide-control');
            let slideControlItems = slideControl ? slideControl.getElementsByClassName('slide-control-item') : [];

            // Function to reset home section
            function resetHomeSection() {
                if (slides.length > 0) {
                    // Reset slide index
                    slideIndex = 0;

                    // Hide all slides and set initial position
                    Array.from(slides).forEach(slide => {
                        slide.classList.remove('active');
                        slide.style.transform = 'translateX(100%)';
                    });

                    // Show first slide
                    setTimeout(() => {
                        slides[0].classList.add('active');
                        slides[0].style.transform = 'translateX(0%)';
                    }, 100);

                    // Reset slide control
                    if (slideControlItems.length > 0) {
                        Array.from(slideControlItems).forEach(item => {
                            item.classList.remove('active');
                        });
                        setTimeout(() => {
                            slideControlItems[0].classList.add('active');
                        }, 100);
                    }
                }
            }

            // Initialize first slide if it exists
            if (slides.length > 0) {
                // Set all slides to initial position
                Array.from(slides).forEach(slide => {
                    slide.classList.remove('active');
                    slide.style.transform = 'translateX(100%)';
                });

                // Show first slide after a delay
                setTimeout(() => {
                    slides[0].classList.add('active');
                    slides[0].style.transform = 'translateX(0%)';
                }, 500);
            }

            // Initialize first slide control as active
            if (slideControlItems.length > 0) {
                setTimeout(() => {
                    slideControlItems[0].classList.add('active');
                }, 500);
            }

            // Slide control click functionality
            Array.from(slideControlItems).forEach((el, index) => {
                el.onclick = function() {
                    if (index === slideIndex) return;

                    slideIndex = index;

                    // Remove active class from current slide control
                    let currSlideControl = slideControl.querySelector('.slide-control-item.active');
                    if (currSlideControl) {
                        currSlideControl.classList.remove('active');
                    }

                    // Hide current slide and show new slide with slide-right animation
                    Array.from(slides).forEach(slide => {
                        if (slide.classList.contains('active')) {
                            slide.style.transform = 'translateX(-100%)';
                            setTimeout(() => {
                                slide.classList.remove('active');
                            }, 600);
                        } else {
                            slide.classList.remove('active');
                            slide.style.transform = 'translateX(100%)';
                        }
                    });

                    // Show the selected slide with aesthetic timing
                    setTimeout(() => {
                        if (slides[slideIndex]) {
                            slides[slideIndex].classList.add('active');
                            slides[slideIndex].style.transform = 'translateX(0%)';
                        }
                    }, 150);

                    // Add active class to new slide control immediately
                    if (slideControlItems[slideIndex]) {
                        slideControlItems[slideIndex].classList.add('active');
                    }
                };
            });

            // Modal functionality
            let modal = document.getElementById('modal');
            let closeBtn = document.getElementById('modal-close');

            if (closeBtn) {
                closeBtn.onclick = () => {
                    if (modal) {
                        modal.style.display = 'none';
                    }
                };
            }

            // More images functionality
            let moreImages = document.getElementsByClassName('more-images-item');
            let previewImages = document.getElementsByClassName('img-preview');

            Array.from(moreImages).forEach((el) => {
                el.onclick = () => {
                    let imgItems = el.parentNode.getElementsByTagName('img');

                    Array.from(imgItems).forEach((item, index) => {
                        if (previewImages[index]) {
                            previewImages[index].src = item.src;
                        }
                    });

                    let img = el.querySelector('img');
                    if (modal && img) {
                        modal.style.display = 'block';

                        let modalContent = modal.querySelector('.modal-content');
                        if (modalContent) {
                            modalContent.src = img.src;

                            let temp = modalContent.cloneNode(true);
                            modalContent.parentNode.replaceChild(temp, modalContent);
                        }
                    }
                };
            });

            // Navigation functionality
            const navLinks = document.querySelectorAll('.navbar a[href^="#"]');
            const sections = document.querySelectorAll('section, #slider');

            // Hide all sections initially except slider
            sections.forEach(section => {
                if (section.id !== 'slider') {
                    section.style.display = 'none';
                } else {
                    section.style.display = 'block';
                }
            });

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    navLinks.forEach(navLink => navLink.classList.remove('active'));

                    // Add active class to clicked link
                    this.classList.add('active');

                    const targetId = this.getAttribute('href');
                    const targetSection = document.querySelector(targetId);

                    if (targetSection) {
                        // Hide all sections with smooth transition
                        sections.forEach(section => {
                            if (section !== targetSection) {
                                section.style.opacity = '0';
                                setTimeout(() => {
                                    section.style.display = 'none';
                                }, 300);
                            }
                        });

                        // Show target section with smooth transition
                        if (targetId === '#slider') {
                            targetSection.style.display = 'block';
                            document.body.classList.remove('section-view'); // Remove scroll class for slider
                        } else {
                            targetSection.style.display = 'flex';
                            document.body.classList.add('section-view'); // Add scroll class for other sections

                            // Special handling for services section
                            if (targetId === '#services') {
                                targetSection.style.overflowY = 'auto';
                                targetSection.style.height = 'auto';
                                targetSection.style.minHeight = '100vh';
                            }
                        }
                        setTimeout(() => {
                            targetSection.style.opacity = '1';
                        }, 50);

                        // If showing slider, ensure first slide is active
                        if (targetId === '#slider') {
                            resetHomeSection();
                        }
                    }
                });
            });

            // Add smooth transitions to sections
            sections.forEach(section => {
                section.style.transition = 'opacity 0.3s ease';
                section.style.opacity = '1';
            });

            // POS Style Reservation System Functionality
            const reservationModal = document.getElementById('reservation-modal');
            const navItems = document.querySelectorAll('.nav-item');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const productsGrid = document.getElementById('products-grid');
            const cartItems = document.getElementById('cart-items');
            const clearCart = document.getElementById('clear-cart');
            const searchInput = document.getElementById('search-input');
            const clearSearch = document.getElementById('clear-search');
            const cashReceived = document.getElementById('cash-received');
            const keypadButtons = document.querySelectorAll('.keypad-btn');
            const deleteBtn = document.getElementById('delete-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const confirmReservationBtn = document.getElementById('confirm-reservation-btn');
            const currentTimeDisplay = document.getElementById('current-time');
            const currentDateDisplay = document.getElementById('current-date');

            // ===== PRODUCT DATA =====
            const PRODUCTS = [
                // Men's Shoes
                {
                    id: 1,
                    name: "Nike Air Max 270",
                    brand: "Nike",
                    price: 8499,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 15,
                    image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop",
                    description: "Comfortable running shoes with Air Max technology"
                },
                {
                    id: 2,
                    name: "Adidas Ultraboost 22",
                    brand: "Adidas",
                    price: 12999,
                    category: "men",
                    sizes: ["8", "9", "10", "11", "12"],
                    stock: 12,
                    image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
                    description: "Premium running shoes with Boost technology"
                },
                {
                    id: 3,
                    name: "Jordan Air 1 Retro High",
                    brand: "Nike",
                    price: 15999,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11"],
                    stock: 8,
                    image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
                    description: "Classic basketball shoes with Air technology"
                },
                {
                    id: 4,
                    name: "Converse Chuck Taylor",
                    brand: "Converse",
                    price: 3999,
                    category: "accessories",
                    sizes: ["6", "7", "8", "9", "10", "11", "12"],
                    stock: 25,
                    image: "https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop",
                    description: "Timeless canvas sneakers"
                },
                {
                    id: 5,
                    name: "Vans Old Skool",
                    brand: "Vans",
                    price: 4499,
                    category: "accessories",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 18,
                    image: "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop",
                    description: "Classic skate shoes with side stripe"
                },

                // Women's Shoes
                {
                    id: 6,
                    name: "Nike Air Force 1 '07",
                    brand: "Nike",
                    price: 6999,
                    category: "women",
                    sizes: ["5", "6", "7", "8", "9", "10"],
                    stock: 20,
                    image: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop",
                    description: "Iconic lifestyle sneakers"
                },
                {
                    id: 7,
                    name: "Adidas Stan Smith",
                    brand: "Adidas",
                    price: 5499,
                    category: "women",
                    sizes: ["5", "6", "7", "8", "9", "10"],
                    stock: 16,
                    image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
                    description: "Classic tennis shoes"
                },
                {
                    id: 8,
                    name: "Puma RS-X",
                    brand: "Puma",
                    price: 6499,
                    category: "women",
                    sizes: ["6", "7", "8", "9", "10"],
                    stock: 14,
                    image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
                    description: "Retro-inspired running shoes"
                },
                {
                    id: 9,
                    name: "New Balance 574",
                    brand: "New Balance",
                    price: 5999,
                    category: "women",
                    sizes: ["5", "6", "7", "8", "9", "10"],
                    stock: 22,
                    image: "https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop",
                    description: "Comfortable lifestyle sneakers"
                },
                {
                    id: 10,
                    name: "Reebok Classic",
                    brand: "Reebok",
                    price: 3999,
                    category: "women",
                    sizes: ["6", "7", "8", "9", "10"],
                    stock: 19,
                    image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
                    description: "Timeless athletic shoes"
                },

                // Kids' Shoes
                {
                    id: 11,
                    name: "Nike Kids Revolution",
                    brand: "Nike",
                    price: 2999,
                    category: "women",
                    sizes: ["1", "2", "3", "4", "5", "6"],
                    stock: 30,
                    image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
                    description: "Comfortable kids running shoes"
                },
                {
                    id: 12,
                    name: "Adidas Kids Cloudfoam",
                    brand: "Adidas",
                    price: 3499,
                    category: "women",
                    sizes: ["1", "2", "3", "4", "5", "6"],
                    stock: 25,
                    image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
                    description: "Soft and comfortable kids shoes"
                },
                {
                    id: 13,
                    name: "Puma Kids Softride",
                    brand: "Puma",
                    price: 2799,
                    category: "men",
                    sizes: ["2", "3", "4", "5", "6"],
                    stock: 28,
                    image: "https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop",
                    description: "Lightweight kids athletic shoes"
                },

                // Sports Shoes
                {
                    id: 14,
                    name: "Nike ZoomX Vaporfly",
                    brand: "Nike",
                    price: 24999,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 6,
                    image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop",
                    description: "Elite racing shoes for marathon runners"
                },
                {
                    id: 15,
                    name: "Adidas Predator Edge",
                    brand: "Adidas",
                    price: 18999,
                    category: "men",
                    sizes: ["8", "9", "10", "11", "12"],
                    stock: 10,
                    image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
                    description: "Professional soccer cleats"
                },
                {
                    id: 16,
                    name: "Under Armour Curry 9",
                    brand: "Under Armour",
                    price: 12999,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 12,
                    image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
                    description: "Basketball shoes with superior traction"
                },

                // Casual Shoes
                {
                    id: 17,
                    name: "Sperry Top-Sider",
                    brand: "Sperry",
                    price: 4999,
                    category: "menl",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 35,
                    image: "https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop",
                    description: "Classic boat shoes"
                },
                {
                    id: 18,
                    name: "Crocs Classic Clog",
                    brand: "Crocs",
                    price: 2499,
                    category: "men",
                    sizes: ["6", "7", "8", "9", "10", "11", "12"],
                    stock: 50,
                    image: "https://images.unsplash.com/photo-1552346154-21d32810aba3?w=400&h=400&fit=crop",
                    description: "Comfortable and versatile clogs"
                },
                {
                    id: 19,
                    name: "Birkenstock Arizona",
                    brand: "Birkenstock",
                    price: 5999,
                    category: "women",
                    sizes: ["6", "7", "8", "9", "10", "11"],
                    stock: 22,
                    image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
                    description: "Premium comfort sandals"
                },
                {
                    id: 20,
                    name: "Nike Air Jordan 4",
                    brand: "Nike",
                    price: 18999,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 5,
                    image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
                    description: "Retro basketball sneakers"
                },
                {
                    id: 21,
                    name: "Adidas Yeezy Boost 350",
                    brand: "Adidas",
                    price: 29999,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 3,
                    image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
                    description: "Limited edition lifestyle sneakers"
                },
                {
                    id: 22,
                    name: "Nike Zoom Fly 4",
                    brand: "Nike",
                    price: 9999,
                    category: "sports",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 15,
                    image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop",
                    description: "Performance running shoes"
                },
                {
                    id: 23,
                    name: "Adidas Terrex Free Hiker",
                    brand: "Adidas",
                    price: 15999,
                    category: "men",
                    sizes: ["8", "9", "10", "11", "12"],
                    stock: 8,
                    image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
                    description: "Hiking shoes with Boost technology"
                },
                {
                    id: 24,
                    name: "Nike Kids Air Jordan 1",
                    brand: "Nike",
                    price: 5999,
                    category: "men",
                    sizes: ["1", "2", "3", "4", "5", "6"],
                    stock: 18,
                    image: "https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop",
                    description: "Kids basketball shoes"
                },
                {
                    id: 25,
                    name: "Adidas Kids Gazelle",
                    brand: "Adidas",
                    price: 3999,
                    category: "men",
                    sizes: ["1", "2", "3", "4", "5", "6"],
                    stock: 20,
                    image: "https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=400&fit=crop",
                    description: "Classic kids sneakers"
                },
                {
                    id: 26,
                    name: "Nike Air Max 90",
                    brand: "Nike",
                    price: 8999,
                    category: "women",
                    sizes: ["5", "6", "7", "8", "9", "10"],
                    stock: 12,
                    image: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop",
                    description: "Iconic lifestyle sneakers"
                },
                {
                    id: 27,
                    name: "Adidas NMD R1",
                    brand: "Adidas",
                    price: 11999,
                    category: "women",
                    sizes: ["5", "6", "7", "8", "9", "10"],
                    stock: 9,
                    image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
                    description: "Modern lifestyle sneakers"
                },
                {
                    id: 28,
                    name: "Puma Future Rider",
                    brand: "Puma",
                    price: 5499,
                    category: "men",
                    sizes: ["6", "7", "8", "9", "10", "11", "12"],
                    stock: 25,
                    image: "https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop",
                    description: "Retro-inspired lifestyle sneakers"
                },
                {
                    id: 29,
                    name: "Dr. Martens 1460",
                    brand: "Dr. Martens",
                    price: 8999,
                    category: "men",
                    sizes: ["7", "8", "9", "10", "11"],
                    stock: 15,
                    image: "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop",
                    description: "Iconic leather boots"
                },
                {
                    id: 30,
                    name: "Timberland 6-Inch",
                    brand: "Timberland",
                    price: 11999,
                    category: "men",
                    sizes: ["8", "9", "10", "11", "12"],
                    stock: 12,
                    image: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop",
                    description: "Premium waterproof boots"
                },
                {
                    id: 31,
                    name: "Clarks Desert Boot",
                    brand: "Clarks",
                    price: 6499,
                    category: "accessories",
                    sizes: ["7", "8", "9", "10", "11"],
                    stock: 20,
                    image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop",
                    description: "Classic suede desert boots"
                },
                {
                    id: 32,
                    name: "Leather Shoe Laces",
                    brand: "Premium",
                    price: 299,
                    category: "accessories",
                    sizes: ["Standard"],
                    stock: 50,
                    image: "https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop",
                    description: "High-quality leather shoe laces"
                },
                {
                    id: 33,
                    name: "Shoe Cleaning Kit",
                    brand: "CarePro",
                    price: 899,
                    category: "accessories",
                    sizes: ["One Size"],
                    stock: 30,
                    image: "https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=400&h=400&fit=crop",
                    description: "Complete shoe cleaning and care kit"
                },
                {
                    id: 34,
                    name: "Shoe Insoles",
                    brand: "ComfortPlus",
                    price: 599,
                    category: "accessories",
                    sizes: ["7", "8", "9", "10", "11", "12"],
                    stock: 40,
                    image: "https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop",
                    description: "Memory foam shoe insoles for comfort"
                },
                {
                    id: 35,
                    name: "Shoe Polish Set",
                    brand: "ShineMaster",
                    price: 399,
                    category: "accessories",
                    sizes: ["Standard"],
                    stock: 25,
                    image: "https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=400&h=400&fit=crop",
                    description: "Professional shoe polish and brush set"
                }
            ];

            window.cart = [];
            window.currentFilter = 'all';
            window.selectedSizes = {};
            let cashAmount = "0.00";

            // Open reservation modal when Reserve Now buttons are clicked
            document.querySelectorAll('.button button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    openReservationModal();
                });
            });

            // Close reservation modal
            reservationModal.addEventListener('click', function(e) {
                if (e.target === reservationModal) {
                    closeReservationModal();
                }
            });

            function openReservationModal() {
                reservationModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                updateDateTime();
                loadProducts();
                
                // Load cart data from localStorage if available
                const savedCartData = localStorage.getItem('reservationCart');
                if (savedCartData) {
                    try {
                        const data = JSON.parse(savedCartData);
                        if (data.items && data.items.length > 0) {
                            // Restore cart items from localStorage
                            cart.length = 0; // Clear current cart
                            data.items.forEach(item => {
                                cart.push(item);
                            });
                        }
                    } catch (error) {
                        console.error('Error loading cart data:', error);
                    }
                }
                
                loadCart();
                setInterval(updateDateTime, 1000);
                
                // Initialize logout functionality
                initializeLogout();
            }

            function closeReservationModal() {
                reservationModal.style.display = 'none';
                document.body.style.overflow = 'auto';
                resetReservationSystem();
            }

            function updateDateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour12: true,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                const dateString = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                currentTimeDisplay.textContent = timeString;
                currentDateDisplay.textContent = dateString;
            }

            // Navigation
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    const section = this.getAttribute('data-section');

                    // Update active nav item
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');

                    // Handle different sections
                    if (section === 'reservation') {
                        // Stay in reservation system (default behavior)
                        return;
                    } else if (section === 'conversion') {
                        // Redirect to shoe conversion page
                        window.location.href = 'shoe conversion.html';
                        return;
                    } else {
                        alert('This section is not available in the demo.');
                        return;
                    }
                });
            });

            // Filter buttons
            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.getAttribute('data-category');
                    currentFilter = filter;

                    // Update active button
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    loadProducts();
                });
            });

            function loadProducts() {
                productsGrid.innerHTML = '';

                let filteredProducts = PRODUCTS;

                if (currentFilter !== 'all') {
                    filteredProducts = PRODUCTS.filter(product => product.category === currentFilter);
                }

                // Apply search filter
                const searchTerm = searchInput.value.toLowerCase();
                if (searchTerm) {
                    filteredProducts = filteredProducts.filter(product =>
                        product.name.toLowerCase().includes(searchTerm) ||
                        product.brand.toLowerCase().includes(searchTerm)
                    );
                }

                filteredProducts.forEach(product => {
                            const productCard = document.createElement('div');
                            productCard.className = 'product-card';
                            
                            // Determine stock status and button state
                            const isOutOfStock = product.stock <= 0;
                            const stockClass = isOutOfStock ? 'out-of-stock' : 'in-stock';
                            const stockText = isOutOfStock ? 'Out of Stock' : `${product.stock} in stock`;
                            const buttonDisabled = isOutOfStock ? 'disabled' : '';
                            const buttonClass = isOutOfStock ? 'add-to-cart-btn disabled' : 'add-to-cart-btn';
                            
                            productCard.innerHTML = `
                <div class="product-image">
                    <div class="product-badge">${product.category.toUpperCase()}</div>
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="product-infos">
                    <div class="product-name">${product.name}</div>
                    <div class="product-brand">${product.brand}</div>
                    <div class="product-details">
                        <div class="product-price">₱${product.price.toLocaleString()}</div>
                        <div class="product-stock ${stockClass}">${stockText}</div>
                    </div>
                    <div class="product-sizes">
                        <span class="size-label">Sizes:</span>
                        ${product.sizes.map(size => `
                            <span class="size-badge" data-product="${product.id}" data-size="${size}">${size}</span>
                        `).join('')}
                    </div>
                    <button class="${buttonClass}" onclick="addToCart(${product.id})" ${buttonDisabled}>
                        <i class="bx bx-plus"></i>
                        ${isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                </div>
            `;
            productsGrid.appendChild(productCard);
        });
        
        // Add size selection functionality
        document.querySelectorAll('.size-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                const productId = this.getAttribute('data-product');
                const size = this.getAttribute('data-size');
                
                // Check if this size is already selected
                if (this.classList.contains('selected')) {
                    // Deselect this size
                    this.classList.remove('selected');
                    delete selectedSizes[productId];
                    
                    // Update add to cart button
                    const productCard = this.closest('.product-card');
                    const addToCartBtn = productCard.querySelector('.add-to-cart-btn');
                    addToCartBtn.classList.remove('ready');
                    addToCartBtn.innerHTML = '<i class="bx bx-plus"></i>Add to Cart';
                } else {
                    // Clear other selected sizes for this product
                    document.querySelectorAll(`[data-product="${productId}"]`).forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    selectedSizes[productId] = size;
                    
                    // Update add to cart button for this product
                    const productCard = this.closest('.product-card');
                    const addToCartBtn = productCard.querySelector('.add-to-cart-btn');
                    addToCartBtn.classList.add('ready');
                    addToCartBtn.innerHTML = '<i class="bx bx-plus"></i>Add to Cart';
                }
            });
        });
    }

    // Make functions globally accessible
    window.addToCart = function(productId) {
        const product = findProduct(productId);
        if (product) {
            const selectedSize = selectedSizes[productId];
            if (!selectedSize) {
                showNotification('Please select a size first!');
                return;
            }
            
            // Check stock availability
            if (product.stock <= 0) {
                showNotification('Sorry, this item is out of stock!');
                return;
            }
            
            const existingItem = cart.find(item => item.id === productId && item.size === selectedSize);
            if (existingItem) {
                if (existingItem.quantity >= 10) {
                    showNotification('Maximum quantity is 10 per item');
                    return;
                }
                if (existingItem.quantity >= product.stock) {
                    showNotification(`Only ${product.stock} items left in stock!`);
                    return;
                }
                existingItem.quantity += 1;
                // Decrease stock
                product.stock -= 1;
                showNotification(`${product.name} (Size ${selectedSize}) quantity increased`);
            } else {
                cart.push({
                    ...product,
                    size: selectedSize,
                    quantity: 1
                });
                // Decrease stock
                product.stock -= 1;
                showNotification(`${product.name} (Size ${selectedSize}) added to cart`);
            }
            
            // Update cart display and total
            loadCart();
            // Reload products to update stock display
            loadProducts();
            
            // Reset the add to cart button
            const productCard = document.querySelector(`[data-product="${productId}"]`).closest('.product-card');
            const addToCartBtn = productCard.querySelector('.add-to-cart-btn');
            addToCartBtn.classList.remove('ready');
            addToCartBtn.innerHTML = '<i class="bx bx-plus"></i>Add to Cart';
            
            // Clear size selection for this product
            document.querySelectorAll(`[data-product="${productId}"]`).forEach(b => b.classList.remove('selected'));
            delete selectedSizes[productId];
        }
    };

    window.findProduct = function(productId) {
        return PRODUCTS.find(p => p.id === productId);
    };

    window.loadCart = function() {
        cartItems.innerHTML = '';
        
        if (cart.length === 0) {
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <i class="bx bx-shopping-bag"></i>
                    <h4>Your cart is empty</h4>
                    <p>Add some shoes to get started!</p>
                </div>
            `;
            updateOrderSummary();
            return;
        }
        
        cart.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            
            cartItem.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-row">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₱${parseFloat(item.price).toLocaleString()}</div>
                    </div>
                    <div class="cart-item-row">
                        <div class="cart-item-size">Size: ${item.size}</div>
                        <div class="quantity-controls">
                            <button class="quantity-btn minus" onclick="updateQuantity(${item.id}, '${item.size}', -1)" ${item.quantity <= 1 ? 'disabled' : ''}>
                                <i class="bx bx-minus"></i>
                            </button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn plus" onclick="updateQuantity(${item.id}, '${item.size}', 1)">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            cartItems.appendChild(cartItem);
        });
        
        // Always update the order summary after loading cart
        updateOrderSummary();
    };

    window.updateOrderSummary = function() {
        let total = 0;
        
        cart.forEach(item => {
            // Handle different price formats
            let price = item.price;
            if (typeof price === 'string') {
                price = price.replace('₱', '').replace(/,/g, '');
            }
            total += parseFloat(price) * item.quantity;
        });
        
        // Format total with proper commas for thousands and millions
        const formattedTotal = total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        document.getElementById('total').textContent = `₱ ${formattedTotal}`;
    };

    window.updateQuantity = function(productId, size, change) {
        const item = cart.find(item => item.id === productId && item.size === size);
        if (item) {
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                removeFromCart(productId, size);
                showNotification('Item removed from cart');
            } else if (newQuantity > 10) {
                showNotification('Maximum quantity is 10 per item');
                return;
            } else {
                // Handle stock changes
                const product = findProduct(productId);
                if (change > 0) {
                    // Increasing quantity - check stock
                    if (product.stock <= 0) {
                        showNotification('Sorry, this item is out of stock!');
                        return;
                    }
                    product.stock -= 1;
                } else {
                    // Decreasing quantity - increase stock back
                    product.stock += 1;
                }
                
                item.quantity = newQuantity;
                // Update cart display and total
                loadCart();
                // Reload products to update stock display
                loadProducts();
                showNotification(`Quantity updated to ${newQuantity}`);
            }
        }
    };

    window.removeFromCart = function(productId, size) {
        const item = cart.find(item => item.id === productId && item.size === size);
        if (item) {
            // Restore stock
            const product = findProduct(productId);
            if (product) {
                product.stock += item.quantity;
            }
            
            cart = cart.filter(item => !(item.id === productId && item.size === size));
            // Update cart display and total
            loadCart();
            // Reload products to update stock display
            loadProducts();
            showNotification(`${item.name} (Size ${item.size}) removed from cart`);
        }
    };

    // Clear cart
    clearCart.addEventListener('click', function() {
        // Restore all stock
        cart.forEach(item => {
            const product = findProduct(item.id);
            if (product) {
                product.stock += item.quantity;
            }
        });
        
        cart = [];
        // Update cart display and total
        loadCart();
        // Reload products to update stock display
        loadProducts();
    });

    // Cancel order button
    const cancelOrderBtn = document.getElementById('cancel-order');
    cancelOrderBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            showNotification('Cart is already empty!');
            return;
        }
        
        if (confirm('Are you sure you want to cancel this order? All items will be returned to stock.')) {
            // Restore all stock
            cart.forEach(item => {
                const product = findProduct(item.id);
                if (product) {
                    product.stock += item.quantity;
                }
            });
            
            cart = [];
            loadCart();
            loadProducts();
            showNotification('Order cancelled successfully!');
        }
    });

    // Reserve order button
    const reserveOrderBtn = document.getElementById('reserve-order');
    reserveOrderBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            showNotification('Please add items to your cart first!');
            return;
        }
        
        // Store cart data in localStorage before redirecting
        const cartData = {
            items: cart,
            total: parseFloat(document.getElementById('total').textContent.replace('₱', '').replace(/,/g, ''))
        };
        
        // Save cart data to localStorage
        localStorage.setItem('reservationCart', JSON.stringify(cartData));
        
        // Redirect to reservation form
        window.location.href = 'reservation form.html';
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        loadProducts();
    });

    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        loadProducts();
    });

    // Numeric keypad functionality
    keypadButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            
            if (value === '.') {
                if (!cashAmount.includes('.')) {
                    cashAmount += '.';
                }
            } else {
                if (cashAmount === '0.00') {
                    cashAmount = value;
                } else {
                    cashAmount += value;
                }
            }
            
            cashReceived.value = cashAmount;
        });
    });

    deleteBtn.addEventListener('click', function() {
        if (cashAmount.length > 1) {
            cashAmount = cashAmount.slice(0, -1);
        } else {
            cashAmount = '0.00';
        }
        cashReceived.value = cashAmount;
    });

    // Action buttons
    cancelBtn.addEventListener('click', function() {
        closeReservationModal();
    });

    confirmReservationBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        
        const total = parseFloat(document.getElementById('total').textContent.replace('₱', '').trim());
        const cash = parseFloat(cashAmount);
        
        if (cash < total) {
            alert('Insufficient cash received!');
            return;
        }
        
        const change = cash - total;
        
        // Show success message with change
        alert(`Reservation confirmed!\n\nTotal: ₱${total.toFixed(2)}\nCash Received: ₱${cash.toFixed(2)}\nChange: ₱${change.toFixed(2)}\n\nYour reservation has been successfully processed!`);
        closeReservationModal();
    });

    function resetReservationSystem() {
        cart = [];
        selectedSizes = {};
        cashAmount = "0.00";
        cashReceived.value = cashAmount;
        searchInput.value = '';
        currentFilter = 'all';
        
        // Reset filter buttons
        filterButtons.forEach(b => b.classList.remove('active'));
        document.querySelector('[data-category="all"]').classList.add('active');
        
        // Reset nav items
        navItems.forEach(nav => nav.classList.remove('active'));
        document.querySelector('[data-section="reservation"]').classList.add('active');
    }

    // Logout functionality
    function initializeLogout() {
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            // Remove any existing event listeners
            logoutBtn.replaceWith(logoutBtn.cloneNode(true));
            const newLogoutBtn = document.querySelector('.logout-btn');
            
            newLogoutBtn.addEventListener('click', function() {
                // Show confirmation dialog
                if (confirm('Are you sure you want to logout? Your current reservation will be cleared.')) {
                    // Reset the reservation system
                    resetReservationSystem();
                    
                    // Clear any stored cart data
                    localStorage.removeItem('reservationCart');
                    
                    // Show logout notification
                    showNotification('Successfully logged out!');
                    
                    // Redirect to home page (landing page)
                    window.location.href = 'index.html';
                }
            });
        }
    }

    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-weight: 600;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Global logout event listener using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.logout-btn')) {
            e.preventDefault();
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to logout? Your current reservation will be cleared.')) {
                // Reset the reservation system
                resetReservationSystem();
                
                // Clear any stored cart data
                localStorage.removeItem('reservationCart');
                
                // Show logout notification
                showNotification('Successfully logged out!');
                
                // Redirect to home page (landing page)
                window.location.href = 'index.html';
            }
        }
    });
            // === Responsive Slider Enhancements (Arrows + Swipe) ===
            const prevBtn = document.querySelector('.slider-arrow.prev');
            const nextBtn = document.querySelector('.slider-arrow.next');

            function goToSlide(newIndex) {
                if (!slides.length) return;
                if (newIndex === slideIndex) return;
                const dir = newIndex > slideIndex ? 1 : -1;
                const oldIndex = slideIndex;
                slideIndex = (newIndex + slides.length) % slides.length;

                // Update control thumbnails
                if (slideControlItems.length) {
                    Array.from(slideControlItems).forEach(i => i.classList.remove('active'));
                }

                // Prepare slides
                Array.from(slides).forEach((s, idx) => {
                    if (idx === oldIndex) {
                        s.classList.add('active');
                        s.style.transform = `translateX(${dir === 1 ? '-100%' : '100%'})`;
                        setTimeout(() => s.classList.remove('active'), 600);
                    } else if (idx === slideIndex) {
                        s.style.transform = `translateX(${dir === 1 ? '100%' : '-100%'})`;
                        s.classList.add('active');
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                s.style.transform = 'translateX(0%)';
                            });
                        });
                    } else {
                        s.classList.remove('active');
                        s.style.transform = 'translateX(100%)';
                    }
                });

                if (slideControlItems[slideIndex]) {
                    slideControlItems[slideIndex].classList.add('active');
                }
            }

            if (prevBtn && nextBtn) {
                prevBtn.addEventListener('click', () => goToSlide(slideIndex - 1));
                nextBtn.addEventListener('click', () => goToSlide(slideIndex + 1));
                // Keyboard support when arrows focused
                [prevBtn, nextBtn].forEach(btn => {
                    btn.setAttribute('tabindex', '0');
                    btn.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
                        if (e.key === 'ArrowLeft' && btn === nextBtn) { prevBtn.click(); }
                        if (e.key === 'ArrowRight' && btn === prevBtn) { nextBtn.click(); }
                    });
                });
            }

            // Touch / swipe support
            let touchStartX = 0; let touchDeltaX = 0; let touchActive = false;
            const sliderArea = document.getElementById('slider');
            if (sliderArea) {
                sliderArea.addEventListener('touchstart', (e) => {
                    if (!e.touches.length) return;
                    touchActive = true;
                    touchStartX = e.touches[0].clientX;
                    touchDeltaX = 0;
                }, { passive: true });
                sliderArea.addEventListener('touchmove', (e) => {
                    if (!touchActive) return;
                    touchDeltaX = e.touches[0].clientX - touchStartX;
                }, { passive: true });
                sliderArea.addEventListener('touchend', () => {
                    if (!touchActive) return;
                    const threshold = 60; // px
                    if (touchDeltaX > threshold) {
                        goToSlide(slideIndex - 1);
                    } else if (touchDeltaX < -threshold) {
                        goToSlide(slideIndex + 1);
                    }
                    touchActive = false;
                });
            }

            // Update slide control click to reuse goToSlide
            Array.from(slideControlItems).forEach((el, index) => {
                el.onclick = function() { goToSlide(index); };
            });
            // === Mobile slide-control reposition below product info ===
            function relocateSlideControl() {
                const sc = document.getElementById('slide-control');
                if (!sc || !slides.length) return;
                const firstActive = Array.from(slides).find(s => s.classList.contains('active')) || slides[0];
                const infoBlock = firstActive ? firstActive.querySelector('.product-info') : null;
                const anchor = document.getElementById('slide-control-anchor');
                const isMobile = window.matchMedia('(max-width: 820px)').matches;
                if (isMobile) {
                    if (infoBlock && sc.parentElement !== infoBlock.parentElement) {
                        // Append after product-info within the active slide
                        infoBlock.parentElement.appendChild(sc);
                        sc.classList.add('mobile-relocated');
                    }
                } else {
                    // Return to original slider root (before anchor placeholder so DOM order stable)
                    if (anchor && sc.classList.contains('mobile-relocated')) {
                        anchor.before(sc);
                        sc.classList.remove('mobile-relocated');
                    }
                }
            }
            relocateSlideControl();
            window.addEventListener('resize', relocateSlideControl);

            // Also re-run after slide change to ensure placement follows active slide
            const originalGoToSlide = typeof goToSlide === 'function' ? goToSlide : null;
            // Monkey patch if goToSlide exists (defined later in enhancements block)
            // Defer to next tick to intercept after definition
            setTimeout(() => {
                if (typeof goToSlide === 'function' && !goToSlide.__patched) {
                    const base = goToSlide;
                    function wrapped(idx){ base(idx); relocateSlideControl(); }
                    wrapped.__patched = true;
                    window.goToSlide = wrapped; // expose globally to reuse
                }
            }, 0);
        });