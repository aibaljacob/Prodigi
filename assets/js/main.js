/**
 * PRODIGI - Main JavaScript
 * Handles interactivity, AJAX requests, and UI enhancements
 */

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
});

/**
 * Add to cart function
 */
function addToCart(productId) {
    fetch('api/cart-add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

/**
 * Remove from cart
 */
function removeFromCart(cartId) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch('api/cart-remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart_id: cartId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Failed to remove from cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

/**
 * Update cart count
 */
function updateCartCount() {
    fetch('api/cart-count.php')
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = data.count;
            if (data.count === 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'flex';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        max-width: 400px;
    `;
    
    if (type === 'success') {
        notification.style.background = '#D1FAE5';
        notification.style.color = '#065F46';
    } else if (type === 'error') {
        notification.style.background = '#FEE2E2';
        notification.style.color = '#991B1B';
    } else {
        notification.style.background = '#DBEAFE';
        notification.style.color = '#1E40AF';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Form validation
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

/**
 * File upload preview
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Search products
 */
function searchProducts(query) {
    if (query.length < 2) return;
    
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data.results);
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    const resultsContainer = document.getElementById('searchResults');
    if (!resultsContainer) return;
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p>No results found</p>';
        return;
    }
    
    let html = '<ul class="search-results-list">';
    results.forEach(product => {
        html += `
            <li>
                <a href="product.php?slug=${product.product_slug}">
                    <img src="${product.thumbnail_image || 'assets/images/placeholder.jpg'}" alt="${product.product_name}">
                    <div>
                        <h4>${product.product_name}</h4>
                        <p>₹${product.price}</p>
                    </div>
                </a>
            </li>
        `;
    });
    html += '</ul>';
    
    resultsContainer.innerHTML = html;
}

/**
 * Load more products (infinite scroll)
 */
let currentPage = 1;
let isLoading = false;

function loadMoreProducts() {
    if (isLoading) return;
    isLoading = true;
    
    currentPage++;
    
    fetch(`api/products.php?page=${currentPage}`)
    .then(response => response.json())
    .then(data => {
        if (data.products && data.products.length > 0) {
            appendProducts(data.products);
        }
        isLoading = false;
    })
    .catch(error => {
        console.error('Error:', error);
        isLoading = false;
    });
}

/**
 * Append products to grid
 */
function appendProducts(products) {
    const grid = document.querySelector('.products-grid');
    if (!grid) return;
    
    products.forEach(product => {
        const card = createProductCard(product);
        grid.appendChild(card);
    });
}

/**
 * Create product card element
 */
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.innerHTML = `
        <div class="product-image">
            <img src="${product.thumbnail_image || 'assets/images/placeholder.jpg'}" alt="${product.product_name}">
            ${product.discount_price ? '<span class="badge badge-sale">Sale</span>' : ''}
        </div>
        <div class="product-info">
            <p class="product-category">${product.category_name}</p>
            <h3 class="product-title">
                <a href="product.php?slug=${product.product_slug}">${product.product_name}</a>
            </h3>
            <div class="product-footer">
                <div class="product-price">
                    <span class="price-current">₹${product.discount_price || product.price}</span>
                </div>
                <button class="btn-cart" onclick="addToCart(${product.product_id})">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
        </div>
    `;
    return card;
}

/**
 * Infinite scroll detection
 */
window.addEventListener('scroll', function() {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
        loadMoreProducts();
    }
});

/**
 * Payment processing with Razorpay
 */
function processPayment(amount, transactionId, productName) {
    const options = {
        key: RAZORPAY_KEY_ID, // Set this in the page
        amount: amount * 100, // Amount in paise
        currency: 'INR',
        name: 'PRODIGI',
        description: productName,
        order_id: '', // Will be generated from backend
        handler: function(response) {
            // Verify payment
            verifyPayment(response.razorpay_payment_id, transactionId);
        },
        prefill: {
            name: '',
            email: '',
            contact: ''
        },
        theme: {
            color: '#4B6EF5'
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.open();
}

/**
 * Verify payment
 */
function verifyPayment(paymentId, transactionId) {
    fetch('api/verify-payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            payment_id: paymentId,
            transaction_id: transactionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'order-success.php?id=' + transactionId;
        } else {
            showNotification('Payment verification failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Confirm action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .error {
        border-color: #EF4444 !important;
    }
`;
document.head.appendChild(style);
