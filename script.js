/**
 * MODA AREN E-commerce JavaScript
 * Handles all dynamic UI elements, search, quick view modals, AJAX cart operations, and fallback states.
 */

// Global mock products data in case database is offline
const MOCK_PRODUCTS = [
    {
        id: 1,
        category_id: 4,
        category_name: 'SPORTS',
        name: 'MODA AREN Chronos Gold Runner',
        brand: 'MODA AREN GOLD',
        price: 3299.00,
        original_price: 3899.00,
        rating: 4.90,
        reviews: 124,
        image_url: 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
        sizes: '39,40,41,42,43,44',
        is_gold: 1,
        description: 'Aerodinamik koşu tasarımı, ayağı çorap gibi saran Primeknit üst yüzey yapısı ve asil altın detaylarıyla antrenmanlarınızda ve sokakta maksimum konfor sunar.'
    },
    {
        id: 2,
        category_id: 1,
        category_name: 'BAYAN',
        name: 'Gold Stripe Kadın Antrenman Taytı',
        brand: 'MODA AREN ACTIVE',
        price: 1299.00,
        original_price: null,
        rating: 4.80,
        reviews: 89,
        image_url: 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1506152983158-b4a74a01c721?auto=format&fit=crop&w=600&q=80',
        sizes: 'XS,S,M,L,XL',
        is_gold: 1,
        description: 'Yüksek belli, toparlayıcı dikişsiz esnek kumaş. Bacak kenarlarındaki 3 ince metalik altın sarısı şerit ile antrenman şıklığını zirveye çıkarır.'
    },
    {
        id: 3,
        category_id: 2,
        category_name: 'BAY',
        name: 'Performance Pamuklu Kapüşonlu Sweatshirt',
        brand: 'MODA AREN CLIMA',
        price: 1899.00,
        original_price: null,
        rating: 4.60,
        reviews: 56,
        image_url: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1519985176271-adb1088fa94c?auto=format&fit=crop&w=600&q=80',
        sizes: 'S,M,L,XL,XXL',
        is_gold: 0,
        description: 'Geri dönüştürülmüş pamuk karışımlı kalın polar dokusu sayesinde soğuk havalarda vücut sıcaklığını koruyan rahat kesim spor kapüşonlu üst.'
    },
    {
        id: 4,
        category_id: 4,
        category_name: 'SPORTS',
        name: 'Primeknit Metalic Gold Krampon',
        brand: 'MODA AREN PRO',
        price: 4199.00,
        original_price: 4999.00,
        rating: 5.00,
        reviews: 42,
        image_url: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80',
        sizes: '40,41,42,43,44',
        is_gold: 1,
        description: 'Çim sahada yüksek çekiş gücü ve top kontrolü sağlayan esnek bilekli Primeknit dış yüzey. Topuk ve logo bölgesinde altın varak kaplamalar.'
    },
    {
        id: 5,
        category_id: 3,
        category_name: 'KIDS',
        name: 'Genç Çocuk Eşofman Takımı İkili',
        brand: 'MODA AREN KIDS',
        price: 1499.00,
        original_price: null,
        rating: 4.70,
        reviews: 74,
        image_url: 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1502904582529-0a1a2b9910d6?auto=format&fit=crop&w=600&q=80',
        sizes: '8-9 Yaş,10-11 Yaş,12-13 Yaş,14-15 Yaş',
        is_gold: 0,
        description: 'Çocukların gün boyu özgürce hareket edebileceği, yumuşak dokulu ve terletmeyen özel örgü kumaşlı ceket ve pantolon takımı.'
    },
    {
        id: 6,
        category_id: 4,
        category_name: 'SPORTS',
        name: 'Gold Reflection Su Geçirmez Rüzgarlık',
        brand: 'MODA AREN OUTDOOR',
        price: 2899.00,
        original_price: null,
        rating: 4.70,
        reviews: 31,
        image_url: 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&q=80',
        sizes: 'S,M,L,XL',
        is_gold: 1,
        description: 'Su itici ultra hafif kumaş, rüzgar kesen fermuar yapısı ve ayarlanabilir kordonlu kapüşon. Göğsünde şık yansımalı gold logo detayı bulunur.'
    },
    {
        id: 7,
        category_id: 1,
        category_name: 'BAYAN',
        name: 'Fitilli Kadın Crop Antrenman Üstü',
        brand: 'MODA AREN ACTIVE',
        price: 899.00,
        original_price: null,
        rating: 4.50,
        reviews: 112,
        image_url: 'https://images.unsplash.com/photo-1518622358385-8ea7d0794bf6?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1483721310020-03333e577078?auto=format&fit=crop&w=600&q=80',
        sizes: 'XS,S,M,L',
        is_gold: 0,
        description: 'Yumuşak örgü kumaştan üretilen ribanalı şık tasarım. Hem antrenman esnasında hem de günlük yaşamda sokak kombinleri için idealdir.'
    },
    {
        id: 8,
        category_id: 4,
        category_name: 'SPORTS',
        name: 'Premium Gold Metal Fermuarlı Spor Çantası',
        brand: 'MODA AREN ACCESSORIES',
        price: 1599.00,
        original_price: null,
        rating: 4.80,
        reviews: 65,
        image_url: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
        hover_image_url: 'https://images.unsplash.com/photo-1547949003-9792a18a2601?auto=format&fit=crop&w=600&q=80',
        sizes: 'Standart',
        is_gold: 1,
        description: 'Islak ve kuru eşya ayırıcı özel bölmeler, fermuarlı yan ayakkabı cebi ve asil altın sarısı metal fermuar başlıkları içeren spor seyahat çantası.'
    }
];

// Determine the active products array (database-driven or mock fallback)
const PRODUCTS = (window.DB_PRODUCTS && window.DB_PRODUCTS.length > 0) ? window.DB_PRODUCTS : MOCK_PRODUCTS;

document.addEventListener('DOMContentLoaded', () => {
    initPromoSlider();
    initMobileNav();
    initSearchOverlay();
    initCartDrawer();
    initQuickView();
    initProductDetailPage();
    initAccordions();
    initAuthModal();
    updateCartBadge();
});

/* ==========================================================================
   1. PROMO BAR SLIDER
   ========================================================================== */
function initPromoSlider() {
    const slides = document.querySelectorAll('.promo-slide');
    if (slides.length <= 1) return;
    
    let currentSlide = 0;
    
    setInterval(() => {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }, 4000);
}

/* ==========================================================================
   2. MOBILE NAV DRAWER
   ========================================================================== */
function initMobileNav() {
    const toggleBtn = document.querySelector('.menu-toggle-btn');
    const closeBtn = document.querySelector('.mobile-nav-drawer .side-drawer-close');
    const drawer = document.querySelector('.mobile-nav-drawer');
    const backdrop = document.querySelector('.overlay-backdrop');
    
    if (!toggleBtn || !drawer || !backdrop) return;
    
    const openMobileNav = () => {
        drawer.classList.add('active');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    
    const closeMobileNav = () => {
        drawer.classList.remove('active');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    };
    
    toggleBtn.addEventListener('click', openMobileNav);
    if (closeBtn) closeBtn.addEventListener('click', closeMobileNav);
    backdrop.addEventListener('click', closeMobileNav);
}

/* ==========================================================================
   3. SEARCH OVERLAY PANEL
   ========================================================================== */
function initSearchOverlay() {
    const openBtn = document.querySelector('.search-toggle-btn');
    const closeBtn = document.querySelector('.search-close-btn');
    const searchPanel = document.querySelector('.search-overlay-panel');
    const inputField = document.querySelector('.search-input-field');
    const resultsContainer = document.querySelector('.search-results-preview');
    
    if (!openBtn || !searchPanel || !inputField) return;
    
    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        searchPanel.classList.add('active');
        inputField.focus();
        document.body.style.overflow = 'hidden';
    });
    
    const closeSearch = () => {
        searchPanel.classList.remove('active');
        inputField.value = '';
        if (resultsContainer) resultsContainer.innerHTML = '';
        document.body.style.overflow = '';
    };
    
    if (closeBtn) closeBtn.addEventListener('click', closeSearch);
    
    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && searchPanel.classList.contains('active')) {
            closeSearch();
        }
    });

    // Realtime local search logic
    inputField.addEventListener('input', (e) => {
        const query = e.target.value.trim().toLowerCase();
        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            return;
        }
        
        const filtered = PRODUCTS.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.brand.toLowerCase().includes(query) ||
            p.description.toLowerCase().includes(query)
        );
        
        renderSearchResults(filtered);
    });
    
    function renderSearchResults(results) {
        if (!resultsContainer) return;
        
        if (results.length === 0) {
            resultsContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--color-text-muted); font-size: 0.9rem; font-weight: 600;">
                    Aradığınız kriterlere uygun ürün bulunamadı.
                </div>
            `;
            return;
        }
        
        resultsContainer.innerHTML = results.slice(0, 8).map(product => `
            <div class="search-item-card" onclick="window.location.href='product.php?id=${product.id}'">
                <img class="search-item-img" src="${product.image_url}" alt="${product.name}">
                <div class="search-item-info">
                    <span class="search-item-name">${product.name}</span>
                    <span class="search-item-price">${formatPrice(product.price)} TL</span>
                </div>
            </div>
        `).join('');
    }
}

/* ==========================================================================
   4. CART DRAWER & AJAX ACTIONS
   ========================================================================== */
function initCartDrawer() {
    const openBtn = document.querySelector('.cart-toggle-btn');
    const closeBtn = document.querySelector('.cart-drawer .side-drawer-close');
    const drawer = document.querySelector('.cart-drawer');
    const backdrop = document.querySelector('.overlay-backdrop');
    
    if (!openBtn || !drawer || !backdrop) return;
    
    const openCart = (e) => {
        if (e) e.preventDefault();
        drawer.classList.add('active');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
        fetchAndRenderCart();
    };
    
    const closeCart = () => {
        drawer.classList.remove('active');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    };
    
    openBtn.addEventListener('click', openCart);
    if (closeBtn) closeBtn.addEventListener('click', closeCart);
    backdrop.addEventListener('click', closeCart);
    
    // Bind all dynamic click events within the drawer (remove & qty adjust)
    drawer.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.cart-item-remove');
        if (removeBtn) {
            e.preventDefault();
            const itemId = removeBtn.dataset.productId;
            const itemSize = removeBtn.dataset.size;
            ajaxCartAction('remove', { product_id: itemId, size: itemSize });
            return;
        }
        
        const qtyBtn = e.target.closest('.qty-btn');
        if (qtyBtn) {
            const itemId = qtyBtn.dataset.productId;
            const itemSize = qtyBtn.dataset.size;
            const action = qtyBtn.dataset.action; // 'increase' or 'decrease'
            const input = qtyBtn.parentElement.querySelector('.qty-val');
            let qty = parseInt(input.textContent || input.value);
            
            if (action === 'increase') {
                qty++;
            } else if (action === 'decrease' && qty > 1) {
                qty--;
            }
            
            ajaxCartAction('update', { product_id: itemId, size: itemSize, quantity: qty });
        }
    });
    
    // Global function to trigger cart drawer opening after add to cart
    window.openCartDrawer = openCart;
}

// Fetch session cart items from PHP endpoint
function fetchAndRenderCart() {
    const drawerContent = document.querySelector('.cart-drawer-content');
    const subtotalText = document.querySelector('.cart-subtotal-val');
    const totalText = document.querySelector('.cart-total-val');
    
    if (!drawerContent) return;
    
    fetch('cart_action.php?action=get')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.cart && data.cart.length > 0) {
                let html = '';
                data.cart.forEach(item => {
                    html += `
                        <div class="cart-item">
                            <img class="cart-item-img" src="${item.image_url}" alt="${item.name}">
                            <div class="cart-item-info">
                                <span class="cart-item-title">${item.name}</span>
                                <div class="cart-item-meta">
                                    <span>Beden: <strong>${item.size}</strong></span>
                                </div>
                                <div class="cart-item-price-row">
                                    <div class="cart-item-qty">
                                        <button class="qty-btn" data-product-id="${item.product_id}" data-size="${item.size}" data-action="decrease">-</button>
                                        <div class="qty-val">${item.quantity}</div>
                                        <button class="qty-btn" data-product-id="${item.product_id}" data-size="${item.size}" data-action="increase">+</button>
                                    </div>
                                    <span class="cart-item-price">${formatPrice(item.price * item.quantity)} TL</span>
                                </div>
                            </div>
                            <button class="cart-item-remove" data-product-id="${item.product_id}" data-size="${item.size}">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                    `;
                });
                drawerContent.innerHTML = html;
                
                if (subtotalText) subtotalText.textContent = formatPrice(data.totals.subtotal) + ' TL';
                if (totalText) totalText.textContent = formatPrice(data.totals.total) + ' TL';
                
                // Show checkout button and hide empty state
                const footer = document.querySelector('.cart-drawer-footer');
                if (footer) footer.style.display = 'block';
            } else {
                renderCartEmptyState(drawerContent);
            }
            updateCartBadge(data.badge_count || 0);
        })
        .catch(() => {
            renderCartEmptyState(drawerContent);
        });
}

function renderCartEmptyState(container) {
    container.innerHTML = `
        <div class="cart-empty-state">
            <div class="cart-empty-icon">
                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <p class="cart-empty-text">Sepetiniz şu anda boş.</p>
        </div>
    `;
    const footer = document.querySelector('.cart-drawer-footer');
    if (footer) footer.style.display = 'none';
    updateCartBadge(0);
}

// Perform cart operations (add, remove, update) via AJAX
function ajaxCartAction(action, params) {
    const formData = new FormData();
    for (const key in params) {
        formData.append(key, params[key]);
    }
    
    fetch(`cart_action.php?action=${action}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            fetchAndRenderCart();
            updateCartBadge(data.badge_count);
            
            // If action was an addition, trigger the toast feedback
            if (action === 'add') {
                showToastNotification(params.product_name || 'Ürün');
                if (window.openCartDrawer) {
                    window.openCartDrawer();
                }
            }
            
            // If on the cart.php page, refresh the container
            if (window.location.pathname.includes('cart.php')) {
                window.location.reload();
            }
        } else {
            alert(data.message || 'Bir hata oluştu.');
        }
    })
    .catch(() => {
        alert('İşlem gerçekleştirilemedi. Lütfen bağlantınızı kontrol edin.');
    });
}

function updateCartBadge(count) {
    const badge = document.querySelector('.cart-count-badge');
    if (!badge) return;
    
    if (count !== undefined) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
        return;
    }
    
    // Fetch count if not provided
    fetch('cart_action.php?action=get')
        .then(res => res.json())
        .then(data => {
            const cnt = data.badge_count || 0;
            badge.textContent = cnt;
            badge.style.display = cnt > 0 ? 'flex' : 'none';
        });
}

function showToastNotification(productName) {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    
    toast.innerHTML = `
        <span class="toast-icon">✓</span>
        <span><strong>${productName}</strong> sepete eklendi!</span>
    `;
    
    toast.classList.add('active');
    setTimeout(() => {
        toast.classList.remove('active');
    }, 3500);
}

/* ==========================================================================
   5. QUICK VIEW MODAL
   ========================================================================== */
function initQuickView() {
    const backdrop = document.querySelector('.overlay-backdrop');
    const modal = document.querySelector('.custom-modal');
    const closeBtn = document.querySelector('.modal-close-btn');
    
    if (!modal) return;
    
    // Delegate product card quickview buttons
    document.body.addEventListener('click', (e) => {
        const quickviewBtn = e.target.closest('.action-btn-quickview');
        if (!quickviewBtn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const productId = parseInt(quickviewBtn.dataset.productId);
        const product = PRODUCTS.find(p => p.id === productId);
        
        if (product) {
            openQuickView(product);
        }
    });
    
    const closeModal = () => {
        modal.classList.remove('active');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    };
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    
    function openQuickView(product) {
        const gallery = modal.querySelector('.quickview-gallery');
        const brand = modal.querySelector('.quickview-brand');
        const title = modal.querySelector('.quickview-title');
        const priceRow = modal.querySelector('.quickview-price-row');
        const desc = modal.querySelector('.quickview-desc');
        const sizesContainer = modal.querySelector('.quickview-sizes');
        const addBtn = modal.querySelector('.quickview-add-btn');
        
        gallery.innerHTML = `<img class="quickview-img" src="${product.image_url}" alt="${product.name}">`;
        brand.textContent = product.brand;
        title.textContent = product.name;
        
        let priceHtml = `${formatPrice(product.price)} TL`;
        if (product.original_price) {
            priceHtml += ` <span class="quickview-price-original">${formatPrice(product.original_price)} TL</span>`;
        }
        priceRow.innerHTML = priceHtml;
        desc.textContent = product.description;
        
        // Render size selectors
        const sizesList = product.sizes.split(',');
        sizesContainer.innerHTML = sizesList.map((size, idx) => `
            <button class="size-select-btn" data-size="${size}">${size}</button>
        `).join('');
        
        // Size select handlers
        let selectedSize = '';
        const sizeButtons = sizesContainer.querySelectorAll('.size-select-btn');
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                sizeButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedSize = btn.dataset.size;
            });
        });
        
        // Reset and rebuild add to cart action
        addBtn.onclick = (e) => {
            e.preventDefault();
            if (!selectedSize) {
                alert('Lütfen bir beden seçiniz.');
                return;
            }
            closeModal();
            ajaxCartAction('add', {
                product_id: product.id,
                size: selectedSize,
                quantity: 1,
                product_name: product.name
            });
        };
        
        modal.classList.add('active');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/* ==========================================================================
   6. PRODUCT DETAILS PAGE GALLERY & CONTROLS
   ========================================================================== */
function initProductDetailPage() {
    // Thumbnail switching
    const thumbs = document.querySelectorAll('.product-gallery-thumb');
    const mainImg = document.querySelector('.product-gallery-main img');
    
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const thumbImg = thumb.querySelector('img');
            if (mainImg && thumbImg) {
                mainImg.src = thumbImg.src;
            }
        });
    });
    
    // Detailed page size box picker
    const sizeBoxes = document.querySelectorAll('.detail-sizes-grid .size-box');
    const sizeInput = document.getElementById('selected-size-input');
    
    sizeBoxes.forEach(box => {
        box.addEventListener('click', () => {
            sizeBoxes.forEach(b => b.classList.remove('active'));
            box.classList.add('active');
            if (sizeInput) {
                sizeInput.value = box.dataset.size;
            }
        });
    });
    
    // Quantity adjustments on detail page
    const qtyVal = document.querySelector('.detail-qty-val');
    const qtyInput = document.getElementById('qty-input');
    const qtyMinus = document.querySelector('.detail-qty-btn.minus');
    const qtyPlus = document.querySelector('.detail-qty-btn.plus');
    
    if (qtyVal && qtyMinus && qtyPlus) {
        let currentQty = 1;
        
        qtyMinus.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentQty > 1) {
                currentQty--;
                qtyVal.textContent = currentQty;
                if (qtyInput) qtyInput.value = currentQty;
            }
        });
        
        qtyPlus.addEventListener('click', (e) => {
            e.preventDefault();
            currentQty++;
            qtyVal.textContent = currentQty;
            if (qtyInput) qtyInput.value = currentQty;
        });
    }
    
    // Add to cart form interceptor (converts form submit to AJAX)
    const detailForm = document.getElementById('add-to-cart-form');
    if (detailForm) {
        detailForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const pId = detailForm.querySelector('[name="product_id"]').value;
            const size = sizeInput ? sizeInput.value : '';
            const qty = qtyInput ? qtyInput.value : 1;
            const name = detailForm.querySelector('[name="product_name"]').value;
            
            if (!size) {
                const warn = document.getElementById('size-warning');
                if (warn) {
                    warn.style.display = 'block';
                    setTimeout(() => warn.style.display = 'none', 3000);
                } else {
                    alert('Lütfen bir beden seçiniz.');
                }
                return;
            }
            
            ajaxCartAction('add', {
                product_id: pId,
                size: size,
                quantity: qty,
                product_name: name
            });
        });
    }
}

/* ==========================================================================
   7. ACCORDION PANELS
   ========================================================================== */
function initAccordions() {
    const headers = document.querySelectorAll('.detail-accordion-header');
    
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.accordion-icon');
            
            if (content) {
                content.classList.toggle('active');
                if (icon) {
                    icon.textContent = content.classList.contains('active') ? '−' : '+';
                }
            }
        });
    });
}

/* ==========================================================================
   UTILITY FUNCTIONS
   ========================================================================== */
function formatPrice(num) {
    return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&.');
}

/* ==========================================================================
   8. USER LOGIN & REGISTRATION (AJAX)
   ========================================================================== */
function initAuthModal() {
    const modal = document.querySelector('.profile-modal');
    if (!modal) return;
    
    // Tab Switching
    const tabs = modal.querySelectorAll('.profile-tab');
    const loginForm = modal.querySelector('.auth-login-form');
    const registerForm = modal.querySelector('.auth-register-form');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const target = tab.dataset.target;
            if (target === 'login') {
                if (loginForm) loginForm.style.display = 'flex';
                if (registerForm) registerForm.style.display = 'none';
            } else if (target === 'register') {
                if (loginForm) loginForm.style.display = 'none';
                if (registerForm) registerForm.style.display = 'flex';
            }
        });
    });
    
    // Handle Login Submit
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const errorBox = loginForm.querySelector('.auth-error-box');
            if (errorBox) errorBox.style.display = 'none';
            
            const formData = new FormData(loginForm);
            
            fetch('auth_action.php?action=login', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    if (errorBox) {
                        errorBox.textContent = '✕ ' + data.message;
                        errorBox.style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(() => {
                if (errorBox) {
                    errorBox.textContent = '✕ Bir bağlantı hatası oluştu.';
                    errorBox.style.display = 'block';
                }
            });
        });
    }
    
    // Handle Register Submit
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const errorBox = registerForm.querySelector('.auth-error-box');
            if (errorBox) errorBox.style.display = 'none';
            
            const formData = new FormData(registerForm);
            
            fetch('auth_action.php?action=register', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    if (errorBox) {
                        errorBox.textContent = '✕ ' + data.message;
                        errorBox.style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(() => {
                if (errorBox) {
                    errorBox.textContent = '✕ Bir bağlantı hatası oluştu.';
                    errorBox.style.display = 'block';
                }
            });
        });
    }
    
    // Handle Logout Click
    const logoutBtn = modal.querySelector('.auth-logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const formData = new FormData();
            fetch('auth_action.php?action=logout', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                }
            });
        });
    }
}

/* ==========================================================================
   9. LIVE AJAX SEARCH
   ========================================================================== */
function initLiveSearch() {
    const searchPanel = document.querySelector('.search-overlay-panel');
    if (!searchPanel) return;

    const input = searchPanel.querySelector('.search-input-field');
    const resultsContainer = searchPanel.querySelector('.search-results-preview');
    if (!input || !resultsContainer) return;

    let searchTimeout = null;

    input.addEventListener('input', () => {
        const query = input.value.trim();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            // Show skeleton loaders
            resultsContainer.innerHTML = `
                <div style="padding:16px; color:rgba(255,255,255,0.4); font-size:0.82rem; letter-spacing:1px;">Aranıyor...</div>
            `;
            resultsContainer.style.display = 'block';

            fetch(`search_action.php?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.results || data.results.length === 0) {
                        resultsContainer.innerHTML = `
                            <div style="padding:20px 16px; color:rgba(255,255,255,0.4); font-size:0.85rem;">
                                "${query}" için sonuç bulunamadı.
                            </div>`;
                        return;
                    }

                    resultsContainer.innerHTML = data.results.map(p => `
                        <a href="product.php?id=${p.id}" class="search-result-item" style="
                            display:flex; align-items:center; gap:14px;
                            padding:12px 16px;
                            border-bottom:1px solid rgba(255,255,255,0.05);
                            text-decoration:none; color:#fff;
                            transition:background 0.2s;
                        "
                        onmouseover="this.style.background='rgba(212,175,55,0.08)'"
                        onmouseout="this.style.background='transparent'">
                            <img src="${p.image_url}" alt="${p.name}" style="
                                width:52px; height:52px; object-fit:cover;
                                border-radius:6px; flex-shrink:0;
                                border:1px solid rgba(255,255,255,0.08);
                            ">
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:0.72rem; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:rgba(255,255,255,0.45); margin-bottom:2px;">${p.brand}</div>
                                <div style="font-size:0.88rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.name}</div>
                            </div>
                            <div style="color:#d4af37; font-weight:800; font-size:0.9rem; white-space:nowrap;">${parseFloat(p.price).toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</div>
                        </a>
                    `).join('') + `
                        <a href="index.php" style="
                            display:block; padding:12px 16px;
                            text-align:center; font-size:0.75rem; font-weight:700;
                            letter-spacing:1.5px; text-transform:uppercase;
                            color:rgba(212,175,55,0.7); text-decoration:none;
                        ">Tümünü Gör →</a>
                    `;
                })
                .catch(() => {
                    // Fallback: search mock products in-memory
                    const products = window.DB_PRODUCTS || window.MOCK_PRODUCTS || [];
                    const q = query.toLowerCase();
                    const matches = products.filter(p =>
                        p.name.toLowerCase().includes(q) || p.brand.toLowerCase().includes(q)
                    ).slice(0, 6);

                    if (matches.length === 0) {
                        resultsContainer.innerHTML = `<div style="padding:20px 16px; color:rgba(255,255,255,0.4); font-size:0.85rem;">Sonuç bulunamadı.</div>`;
                        return;
                    }

                    resultsContainer.innerHTML = matches.map(p => `
                        <a href="product.php?id=${p.id}" style="
                            display:flex; align-items:center; gap:14px;
                            padding:12px 16px;
                            border-bottom:1px solid rgba(255,255,255,0.05);
                            text-decoration:none; color:#fff;
                        ">
                            <img src="${p.image_url}" alt="${p.name}" style="width:52px;height:52px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:0.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,0.4);">${p.brand}</div>
                                <div style="font-size:0.88rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${p.name}</div>
                            </div>
                            <div style="color:#d4af37;font-weight:800;">${parseFloat(p.price).toLocaleString('tr-TR', {minimumFractionDigits:2})} TL</div>
                        </a>
                    `).join('');
                });
        }, 280); // 280ms debounce
    });

    // Close results when panel closes
    const closeBtn = searchPanel.querySelector('.search-close-btn');
    if (closeBtn) {
        const origClick = closeBtn.onclick;
        closeBtn.addEventListener('click', () => {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            input.value = '';
        });
    }
}

/* ==========================================================================
   10. WISHLIST HEART BUTTONS
   ========================================================================== */
function initWishlistButtons() {
    // Load current wishlist IDs and mark buttons
    fetch('wishlist_action.php?action=get_ids')
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.ids) {
                data.ids.forEach(id => markWishlistBtn(id, true));
            }
        })
        .catch(() => {}); // Silently fail if not logged in

    // Delegate click events
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('.wishlist-heart-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();

        const productId = btn.dataset.productId;
        if (!productId) return;

        // Animate the heart
        btn.classList.add('heartbeat');
        setTimeout(() => btn.classList.remove('heartbeat'), 400);

        const fd = new FormData();
        fd.append('product_id', productId);
        fetch('wishlist_action.php?action=toggle', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.requireLogin) {
                    // Prompt user to log in
                    const modal = document.querySelector('.profile-modal');
                    const backdrop = document.querySelector('.overlay-backdrop');
                    if (modal && backdrop) {
                        modal.classList.add('active');
                        backdrop.classList.add('active');
                    }
                    return;
                }
                if (data.status === 'success') {
                    markWishlistBtn(productId, data.wishlisted);
                    showToastNotification(data.wishlisted ? '❤️ İstek listesine eklendi' : 'İstek listesinden çıkarıldı');
                }
            })
            .catch(() => {});
    });
}

function markWishlistBtn(productId, isWishlisted) {
    document.querySelectorAll(`.wishlist-heart-btn[data-product-id="${productId}"]`).forEach(btn => {
        if (isWishlisted) {
            btn.classList.add('wishlisted');
            btn.setAttribute('aria-pressed', 'true');
            btn.title = 'İstek listesinden çıkar';
        } else {
            btn.classList.remove('wishlisted');
            btn.setAttribute('aria-pressed', 'false');
            btn.title = 'İstek listesine ekle';
        }
    });
}

/* ==========================================================================
   11. SMOOTH PAGE TRANSITIONS
   ========================================================================== */
function initPageTransitions() {
    // Inject transition overlay
    if (!document.getElementById('page-transition-overlay')) {
        const overlay = document.createElement('div');
        overlay.id = 'page-transition-overlay';
        overlay.style.cssText = `
            position:fixed; inset:0; background:#000;
            opacity:0; pointer-events:none;
            z-index:99999; transition:opacity 0.35s cubic-bezier(0.4,0,0.2,1);
        `;
        document.body.appendChild(overlay);
    }

    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        // Skip external, anchor-only, JS, and target=_blank links
        if (!href || href.startsWith('#') || href.startsWith('javascript') ||
            href.startsWith('mailto') || href.startsWith('tel') ||
            link.target === '_blank') return;

        // Skip if modifier key held
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        e.preventDefault();
        const overlay = document.getElementById('page-transition-overlay');
        overlay.style.pointerEvents = 'all';
        overlay.style.opacity = '1';

        setTimeout(() => {
            window.location.href = href;
        }, 340);
    });

    // Fade in on page load
    window.addEventListener('pageshow', () => {
        const overlay = document.getElementById('page-transition-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
        }
    });
}

/* ==========================================================================
   12. PRODUCT CARD MICRO-INTERACTIONS & ENTRANCE ANIMATIONS
   ========================================================================== */
function initCardMicroInteractions() {
    // Intersection Observer for entrance animations
    const cards = document.querySelectorAll('.product-card');
    if (!cards.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                const card = entry.target;
                const delay = parseFloat(card.dataset.animDelay || 0);
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, delay);
                observer.unobserve(card);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(32px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s cubic-bezier(0.34,1.56,0.64,1)';
        card.dataset.animDelay = (i % 4) * 70;
        observer.observe(card);
    });

    // Ripple effect on product card CTA buttons
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('.card-cta-btn, .btn-primary, .btn-secondary');
        if (!btn) return;

        const ripple = document.createElement('span');
        ripple.style.cssText = `
            position:absolute; border-radius:50%;
            background:rgba(255,255,255,0.25);
            transform:scale(0); animation:ripple-effect 0.6s linear;
            pointer-events:none;
        `;

        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top  = (e.clientY - rect.top  - size / 2) + 'px';

        if (getComputedStyle(btn).position === 'static') {
            btn.style.position = 'relative';
        }
        btn.style.overflow = 'hidden';
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 650);
    });

    // Inject ripple keyframe once
    if (!document.getElementById('ripple-style')) {
        const style = document.createElement('style');
        style.id = 'ripple-style';
        style.textContent = `
            @keyframes ripple-effect {
                to { transform: scale(4); opacity: 0; }
            }
            .wishlist-heart-btn {
                position:absolute; top:12px; right:12px;
                width:36px; height:36px;
                background:rgba(0,0,0,0.6);
                border:none; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                cursor:pointer; z-index:3;
                transition:background 0.2s, transform 0.2s;
                backdrop-filter: blur(4px);
                color: rgba(255,255,255,0.6);
                font-size:1rem;
            }
            .wishlist-heart-btn:hover {
                background:rgba(212,175,55,0.15);
                transform:scale(1.15);
                color:#d4af37;
            }
            .wishlist-heart-btn.wishlisted {
                color: #e53e3e;
                background: rgba(229, 62, 62, 0.15);
            }
            .wishlist-heart-btn.wishlisted:hover {
                background: rgba(229, 62, 62, 0.25);
            }
            @keyframes heartbeat {
                0%   { transform:scale(1); }
                30%  { transform:scale(1.35); }
                60%  { transform:scale(0.95); }
                100% { transform:scale(1); }
            }
            .wishlist-heart-btn.heartbeat {
                animation: heartbeat 0.4s ease;
            }
        `;
        document.head.appendChild(style);
    }
}

/* ==========================================================================
   INITIALIZE ALL NEW PREMIUM MODULES
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initWishlistButtons();
    initPageTransitions();
    initCardMicroInteractions();
});
