// Global variables
let currentUser = null;
let menuData = [];
let isotope = null;

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
    loadMenuData();
    initializeEventListeners();
    updateCurrentYear();
});

// Check login status
async function checkLoginStatus() {
    try {
        const response = await fetch('session_check.php');
        const result = await response.json();
        
        if (result.logged_in) {
            currentUser = result;
            updateNavigation(true, result.user_name);
        } else {
            updateNavigation(false);
        }
    } catch (error) {
        console.error('Error checking login status:', error);
        updateNavigation(false);
    }
}

// Update navigation based on login status
function updateNavigation(isLoggedIn, userName = '') {
    const navAuthContent = document.getElementById('navAuthContent');
    const orderHistoryLink = document.getElementById('orderHistoryLink');
    const loginButton = document.querySelector('.btn-outline-primary');
    
    if (isLoggedIn) {
        orderHistoryLink.href = 'order_history.php';
        loginButton.style.display = 'none';
        navAuthContent.innerHTML = `
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-1"></i>${userName}
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="logout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a></li>
                </ul>
            </div>
        `;
    } else {
        orderHistoryLink.href = '#';
        loginButton.style.display = 'block';
        navAuthContent.innerHTML = '';
    }
}

// Load menu data from database
async function loadMenuData() {
    try {
        const response = await fetch('get_products.php');
        const result = await response.json();
        
        if (result.success) {
            menuData = result.data;
            displayMenu(menuData);
            initializeIsotope();
        } else {
            console.error('Error loading menu:', result.message);
            showErrorMessage('Gagal memuat menu');
        }
    } catch (error) {
        console.error('Error fetching menu:', error);
        showErrorMessage('Terjadi kesalahan saat memuat menu');
    }
}

// Display menu items
function displayMenu(items) {
    const menuContainer = document.getElementById('menuContainer');
    
    if (items.length === 0) {
        menuContainer.innerHTML = `
            <div class="col-12 text-center">
                <p class="text-muted">Tidak ada menu yang tersedia saat ini.</p>
            </div>
        `;
        return;
    }
    
    const menuHTML = items.map(item => `
        <div class="col-md-6 mb-4 mix ${item.category_class}">
            <div class="card h-100 shadow-sm menu-item">
                <div class="row g-0 h-100">
                    <div class="col-5">
                        <img src="${item.image_url}" 
                             class="h-100 w-100 rounded-start menu-image" 
                             alt="${item.name}" 
                             onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'"
                             style="object-fit: cover;">
                    </div>
                    <div class="col-7">
                        <div class="card-body d-flex flex-column h-100">
                            <h5 class="card-title text-truncate">${item.name}</h5>
                            <p class="card-text text-muted small flex-grow-1">${item.description}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h6 text-primary mb-0">Rp ${item.price}</span>
                                    <span class="badge bg-${item.stock > 10 ? 'success' : 'warning'} rounded-pill">
                                        Stok: ${item.stock}
                                    </span>
                                </div>
                                <button class="btn btn-primary w-100" 
                                        onclick="orderItem(${item.id}, '${item.name}', ${item.raw_price}, ${item.stock})"
                                        ${item.stock < 1 ? 'disabled' : ''}>
                                    <i class="fas fa-cart-plus me-1"></i>
                                    ${item.stock < 1 ? 'Stok Habis' : 'Pesan'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    menuContainer.innerHTML = menuHTML;
}

// Initialize Isotope for filtering
function initializeIsotope() {
    const menuContainer = document.getElementById('menuContainer');
    
    // Initialize Isotope after images are loaded
    imagesLoaded(menuContainer, function() {
        isotope = new Isotope(menuContainer, {
            itemSelector: '.mix',
            layoutMode: 'fitRows'
        });
    });
}

// Initialize event listeners
function initializeEventListeners() {
    document.getElementById('orderHistoryLink').addEventListener('click', function(e) {
        e.preventDefault();
        if (!currentUser) {
            const loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            loginModal.show();
        } else {
            window.location.href = 'order_history.php';
        }
    });

    // Category filter buttons
    const filterButtons = document.querySelectorAll('#category-filter .btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Filter items
            const filterValue = this.getAttribute('data-filter');
            if (isotope) {
                isotope.arrange({ filter: filterValue });
            }
        });
    });
    
    // Dropdown filter buttons
    const dropdownButtons = document.querySelectorAll('.dropdown-item[data-filter]');
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const filterValue = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            const matchingButton = document.querySelector(`#category-filter .btn[data-filter="${filterValue}"]`);
            if (matchingButton) {
                matchingButton.classList.add('active');
            }
            
            // Filter items
            if (isotope) {
                isotope.arrange({ filter: filterValue });
            }
        });
    });
    
    // Quantity modal buttons
    document.getElementById('btnQtyMinus')?.addEventListener('click', function() {
        const qtyInput = document.getElementById('qtyInput');
        const currentValue = parseInt(qtyInput.value);
        if (currentValue > 1) {
            qtyInput.value = currentValue - 1;
        }
    });
    
    document.getElementById('btnQtyPlus')?.addEventListener('click', function() {
        const qtyInput = document.getElementById('qtyInput');
        const currentValue = parseInt(qtyInput.value);
        qtyInput.value = currentValue + 1;
    });
    
    // Order form submission
    document.getElementById('orderQtyForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        processOrder();
    });
    
    // Subscribe form
    const subscribeForm = document.querySelector('.hero-form');
    if (subscribeForm) {
        subscribeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSubscription();
        });
    }
}

// Order item function
function orderItem(itemId, itemName, itemPrice, stock) {
    if (!currentUser) {
        // Show login required modal
        const loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        loginModal.show();
        return;
    }

    if (stock < 1) {
        showErrorMessage('Maaf, stok produk ini telah habis');
        return;
    }
    
    // Set item details in quantity modal
    document.getElementById('qtyMenuName').textContent = itemName;
    const qtyInput = document.getElementById('qtyInput');
    qtyInput.value = 1;
    qtyInput.max = stock; // Set max quantity to available stock
    
    // Store item data for order processing
    document.getElementById('orderQtyForm').dataset.itemId = itemId;
    document.getElementById('orderQtyForm').dataset.itemName = itemName;
    document.getElementById('orderQtyForm').dataset.itemPrice = itemPrice;
    document.getElementById('orderQtyForm').dataset.stock = stock;
    
    // Show quantity modal
    const qtyModal = new bootstrap.Modal(document.getElementById('orderQtyModal'));
    qtyModal.show();
}

// Process order
async function processOrder() {
    const form = document.getElementById('orderQtyForm');
    const itemId = form.dataset.itemId;
    const itemName = form.dataset.itemName;
    const itemPrice = parseFloat(form.dataset.itemPrice);
    const quantity = parseInt(document.getElementById('qtyInput').value);
    const stock = parseInt(form.dataset.stock);
    
    if (quantity > stock) {
        showErrorMessage('Jumlah pesanan melebihi stok yang tersedia');
        return;
    }

    try {
        const response = await fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                item_id: itemId,
                quantity: quantity
            })
        });

        const result = await response.json();
        
        if (result.success) {
            const totalPrice = itemPrice * quantity;
            
            // Close quantity modal
            const qtyModal = bootstrap.Modal.getInstance(document.getElementById('orderQtyModal'));
            qtyModal.hide();
            
            // Show success modal
            showOrderSuccess(itemName, quantity, totalPrice);
            
            // Reload menu data to update stock
            loadMenuData();
        } else {
            showErrorMessage(result.message || 'Gagal memproses pesanan');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Terjadi kesalahan saat memproses pesanan');
    }
}

// Show order success modal
function showOrderSuccess(itemName, quantity, totalPrice) {
    const modalContainer = document.getElementById('orderSuccessModalContainer');
    modalContainer.innerHTML = `
        <div class="modal fade" id="orderSuccessModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>Pesanan Berhasil!
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Terima Kasih!</h4>
                        <p>Pesanan Anda telah berhasil diproses:</p>
                        <div class="bg-light p-3 rounded">
                            <strong>${itemName}</strong><br>
                            Jumlah: ${quantity}<br>
                            Total: Rp ${totalPrice.toLocaleString('id-ID')}
                        </div>
                        <p class="mt-3 text-muted">Pesanan Anda akan segera diproses.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
    successModal.show();
    
    // Remove modal from DOM after it's hidden
    document.getElementById('orderSuccessModal').addEventListener('hidden.bs.modal', function() {
        modalContainer.innerHTML = '';
    });
}

// Handle subscription
function handleSubscription() {
    const emailInput = document.querySelector('.hero-form input[type="email"]');
    const email = emailInput.value;
    
    if (email) {
        // Create and show subscribe modal
        const modalContainer = document.getElementById('subscribeModal');
        modalContainer.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-envelope me-2"></i>Berlangganan Newsletter
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Berhasil Berlangganan!</h4>
                        <p>Terima kasih telah berlangganan dengan email:</p>
                        <div class="bg-light p-2 rounded">
                            <strong>${email}</strong>
                        </div>
                        <p class="mt-3 text-muted">Anda akan mendapatkan promo dan update terbaru dari kami.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        `;
        
        emailInput.value = '';
    }
}

// Logout function
async function logout() {
    try {
        const response = await fetch('logout.php');
        currentUser = null;
        updateNavigation(false);
        
        // Show logout success message
        showSuccessMessage('Anda telah berhasil logout');
        
        // Reload page after a short delay
        setTimeout(() => {
            window.location.reload();
        }, 1500);
        
    } catch (error) {
        console.error('Error during logout:', error);
        showErrorMessage('Terjadi kesalahan saat logout');
    }
}

// Update current year in footer
function updateCurrentYear() {
    const currentYearElement = document.getElementById('currentYear');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
}

// Show success message
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Show error message
function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}