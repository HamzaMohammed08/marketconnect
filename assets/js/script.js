/**
 * Main JavaScript file for MarketConnect
 * Handles all interactive features and UI enhancements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Custom navbar shadow on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 10) {
            navbar.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
        }
    });

    // Product card enhancements
    const productCards = document.querySelectorAll('.card');
    productCards.forEach(card => {
        // Add loading state to images
        const img = card.querySelector('img');
        if (img) {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
        }

        // Add click handler for the entire card
        const cardLink = card.querySelector('a[href*="product_detail.php"]');
        if (cardLink) {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('a') && !e.target.closest('button')) {
                    cardLink.click();
                }
            });
            card.style.cursor = 'pointer';
        }
    });

    // WhatsApp button click tracking
    const whatsappButtons = document.querySelectorAll('.whatsapp-cta');
    whatsappButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // You can add analytics tracking here
            console.log('WhatsApp button clicked');
        });
    });

    // Enhanced dropdown behavior
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            // Add hover effect for desktop
            if (window.innerWidth > 768) {
                dropdown.addEventListener('mouseenter', function() {
                    this.classList.add('show');
                    menu.classList.add('show');
                });

                dropdown.addEventListener('mouseleave', function() {
                    this.classList.remove('show');
                    menu.classList.remove('show');
                });
            }
        }
    });

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Add to cart button enhancement
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            this.disabled = true;

            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 1000);
        });
    });

    // Price range input with live update
    const priceRange = document.querySelector('input[type="range"]');
    const priceOutput = document.getElementById('price-output');
    if (priceRange && priceOutput) {
        priceRange.addEventListener('input', function() {
            priceOutput.textContent = this.value;
        });
    }

    // Image zoom effect on product details page
    const productImage = document.querySelector('.product-detail-img');
    if (productImage) {
        productImage.addEventListener('mousemove', function(e) {
            const x = e.clientX - e.target.offsetLeft;
            const y = e.clientY - e.target.offsetTop;
            
            this.style.transformOrigin = `${x}px ${y}px`;
            this.style.transform = 'scale(1.5)';
        });

        productImage.addEventListener('mouseleave', function() {
            this.style.transformOrigin = 'center center';
            this.style.transform = 'scale(1)';
        });
    }

    // Infinite scroll simulation for product grid
    let loading = false;
    window.addEventListener('scroll', function() {
        const productGrid = document.querySelector('.products-section .row');
        if (productGrid && !loading) {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
                loading = true;
                // Add loading indicator
                const loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'text-center my-4';
                loadingIndicator.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                productGrid.appendChild(loadingIndicator);

                // Simulate loading more products
                setTimeout(() => {
                    loadingIndicator.remove();
                    loading = false;
                }, 1500);
            }
        }
    });
});
