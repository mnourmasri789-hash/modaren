<?php
// Include database connection (which starts session)
require_once 'db_connect.php';

// Handle native POST requests if AJAX is not active
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $size = isset($_POST['size']) ? trim($_POST['size']) : '';
    
    if ($product_id > 0 && !empty($size)) {
        $cart_key = $product_id . '_' . $size;
        
        if ($action === 'remove') {
            if (isset($_SESSION['cart'][$cart_key])) {
                unset($_SESSION['cart'][$cart_key]);
            }
        } elseif ($action === 'update') {
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            if (isset($_SESSION['cart'][$cart_key])) {
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$cart_key]);
                } else {
                    $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
                }
            }
        }
    }
    
    // Redirect to self to prevent form resubmission
    header("Location: cart.php");
    exit;
}

// Compute cart items and totals
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
$cart_badge = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $cart_badge += $item['quantity'];
}

// Shipping threshold: Free above 1500 TL, otherwise 99 TL
$shipping = ($subtotal > 1500 || $subtotal == 0) ? 0 : 99;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alışveriş Sepetim | MODA AREN</title>
    <meta name="description" content="Sepetinizdeki ürünleri görüntüleyin, adetlerini düzenleyin ve güvenle satın alın.">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- PROMO BAR -->
    <div class="promo-bar" id="promoBar">
        <div class="promo-slider">
            <div class="promo-slide active">1500 TL ÜZERİ ALIŞVERİŞLERDE ÜCRETSİZ KARGO</div>
            <div class="promo-slide">30 GÜN İÇİNDE KOŞULSUZ İADE GARANTİSİ</div>
        </div>
    </div>

    <!-- HEADER / NAVBAR -->
    <header class="header">
        <div class="container">
            <!-- Left Nav -->
            <div class="nav-left">
                <button class="menu-toggle-btn" aria-label="Menü">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <nav class="nav-links">
                    <a href="women.php" class="nav-link-item">BAYAN</a>
                    <a href="men.php" class="nav-link-item">BAY</a>
                    <a href="women.php?cat=KIDS" class="nav-link-item">KIDS</a>
                    <a href="men.php?cat=SPORTS" class="nav-link-item accent-link">SPORTS</a>
                </nav>
            </div>

            <!-- Center Logo -->
            <div class="nav-center">
                <a href="index.php" class="logo-link">
                    <span>MODA AREN</span>
                </a>
            </div>

            <!-- Right Icons -->
            <div class="nav-right">
                <button class="nav-icon-btn search-toggle-btn" aria-label="Arama Yap">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                
                <button class="nav-icon-btn profile-toggle-btn" aria-label="Kullanıcı Profili" onclick="document.querySelector('.profile-modal').classList.add('active'); document.querySelector('.overlay-backdrop').classList.add('active');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                
                <button class="nav-icon-btn cart-toggle-btn" aria-label="Sepeti Göster">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="cart-count-badge" style="<?php echo $cart_badge > 0 ? 'display:flex;' : 'display:none;'; ?>"><?php echo $cart_badge; ?></span>
                </button>
            </div>
        </div>
    </header>

    <!-- SHOPPING CART MAIN SECTION -->
    <main class="container section-padding">
        <h1 style="font-size: 2.25rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">ALIŞVERİŞ SEPETİM</h1>
        
        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 100px 0;">
                <div style="font-size: 4rem; color: var(--color-gray-border); margin-bottom: 25px;">
                    <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 15px;">Sepetinizde ürün bulunmamaktadır.</h2>
                <p style="color: var(--color-text-muted); margin-bottom: 35px;">MODA AREN koleksiyonlarını inceleyerek dilediğiniz ürünleri sepete ekleyebilirsiniz.</p>
                <a href="index.php" class="btn-primary">ALIŞVERİŞE BAŞLA</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Left Side: Cart Items List -->
                <div class="cart-items-list">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-page-item">
                            <img class="cart-page-img" src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            
                            <div class="cart-page-info">
                                <span class="cart-page-brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                                <h3 class="cart-page-title">
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                </h3>
                                
                                <div class="cart-page-meta">
                                    <span>Beden: <strong><?php echo htmlspecialchars($item['size']); ?></strong></span>
                                    <span>Birim Fiyat: <strong><?php echo number_format($item['price'], 2, ',', '.'); ?> TL</strong></span>
                                </div>
                                
                                <div class="cart-page-actions">
                                    <!-- Qty Adjustment Forms -->
                                    <div class="cart-item-qty" style="border: 1px solid var(--color-black);">
                                        <!-- Decrease Form -->
                                        <form action="cart.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                            <button type="submit" class="qty-btn" style="width:30px; height:30px; font-weight:700;">-</button>
                                        </form>
                                        
                                        <div class="qty-val" style="width:35px; text-align:center; font-weight:700; font-size:0.85rem;"><?php echo $item['quantity']; ?></div>
                                        
                                        <!-- Increase Form -->
                                        <form action="cart.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                            <button type="submit" class="qty-btn" style="width:30px; height:30px; font-weight:700;">+</button>
                                        </form>
                                    </div>
                                    
                                    <!-- Remove Form -->
                                    <form action="cart.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                        <button type="submit" class="cart-page-remove" style="background:none; border:none; text-decoration:underline; font-weight:700; cursor:pointer;">Kaldır</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="cart-page-price text-gold">
                                <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?> TL
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Right Side: Order Summary Panel -->
                <div class="cart-summary-panel">
                    <h2 class="summary-title">SİPARİŞ ÖZETİ</h2>

                    <!-- Promo Code Input -->
                    <div style="margin-bottom:20px;">
                        <div id="promo-input-wrap" style="display:flex;gap:8px;">
                            <input type="text" id="promo-code-input"
                                placeholder="Promosyon kodu"
                                style="flex:1;padding:10px 14px;background:rgba(255,255,255,0.05);border:1px solid var(--color-gray-border);border-radius:6px;color:#fff;font-family:var(--font-primary);font-size:0.85rem;letter-spacing:1px;text-transform:uppercase;outline:none;"
                                onfocus="this.style.borderColor='var(--color-gold)'"
                                onblur="this.style.borderColor='var(--color-gray-border)'">
                            <button onclick="applyPromoCode()" id="promo-apply-btn"
                                style="padding:10px 16px;background:var(--color-gold);color:#000;border:none;border-radius:6px;font-weight:800;font-size:0.75rem;letter-spacing:1px;text-transform:uppercase;cursor:pointer;white-space:nowrap;transition:background 0.2s;"
                                onmouseover="this.style.background='#f0c93c'" onmouseout="this.style.background='var(--color-gold)'">
                                UYGULA
                            </button>
                        </div>
                        <div id="promo-msg" style="font-size:0.78rem;font-weight:600;margin-top:8px;display:none;"></div>
                    </div>

                    <div class="summary-row">
                        <span>Ara Toplam</span>
                        <span id="cart-subtotal-val"><?php echo number_format($subtotal, 2, ',', '.'); ?> TL</span>
                    </div>

                    <div class="summary-row" id="cart-discount-row" style="display:none;color:#4ade80;">
                        <span id="cart-discount-label">İndirim</span>
                        <span id="cart-discount-val">- 0,00 TL</span>
                    </div>

                    <div class="summary-row">
                        <span>Kargo Ücreti</span>
                        <span><?php echo $shipping == 0 ? 'ÜCRETSİZ' : number_format($shipping, 2, ',', '.') . ' TL'; ?></span>
                    </div>

                    <?php if ($shipping > 0): ?>
                        <div style="font-size: 0.75rem; color: var(--color-gold-dark); font-weight: 700; margin-bottom: 25px; text-transform: uppercase;">
                            * Sepetinize <?php echo number_format(1500 - $subtotal, 2, ',', '.'); ?> TL değerinde ürün daha ekleyin, kargo ücretsiz olsun!
                        </div>
                    <?php endif; ?>

                    <div class="summary-row total">
                        <span>Toplam</span>
                        <span id="cart-total-val"><?php echo number_format($total, 2, ',', '.'); ?> TL</span>
                    </div>

                    <a href="checkout.php" id="checkout-btn" class="btn-primary cart-summary-btn" style="box-sizing:border-box; width:100%;">ÖDEMEYE GEÇ</a>
                    <a href="index.php" class="btn-secondary cart-summary-btn" style="margin-top: 15px; box-sizing:border-box; width:100%;">ALIŞVERİŞE DEVAM ET</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4 class="footer-col-title">PRODUCTS</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="men.php">Ayakkabı</a></li>
                        <li><a class="footer-link-item" href="women.php">Giyim</a></li>
                        <li><a class="footer-link-item" href="men.php">Aksesuar</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-col-title">SPORTS</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="men.php">Koşu</a></li>
                        <li><a class="footer-link-item" href="men.php">Antrenman</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-col-title">SUPPORT</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="#">Yardım & SSS</a></li>
                        <li><a class="footer-link-item" href="#">İade ve Değişim</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-col-title">COMPANY INFO</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="#">MODA AREN Hakkında</a></li>
                        <li><a class="footer-link-item" href="#">İletişim</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="copyright">&copy; <?php echo date('Y'); ?> MODA AREN. Tüm Hakları Saklıdır.</p>
            </div>
        </div>
    </footer>

    <!-- OVERLAY BACKDROP -->
    <div class="overlay-backdrop"></div>

    <!-- SIDE DRAWER: MOBILE NAV -->
    <div class="side-drawer mobile-nav-drawer">
        <div class="side-drawer-header">
            <span class="side-drawer-title">MENÜ</span>
            <button class="side-drawer-close">&times;</button>
        </div>
        <div class="mobile-nav-content">
            <div class="mobile-nav-menu">
                <a href="women.php" class="mobile-nav-link">BAYAN</a>
                <a href="men.php" class="mobile-nav-link">BAY</a>
                <a href="women.php?cat=KIDS" class="mobile-nav-link">KIDS</a>
                <a href="men.php?cat=SPORTS" class="mobile-nav-link accent">SPORTS</a>
            </div>
        </div>
    </div>

    <!-- SEARCH PANEL OVERLAY -->
    <div class="search-overlay-panel">
        <div class="container search-panel-container">
            <div class="search-panel-row">
                <input type="text" placeholder="Ürün adı, kategori veya marka arayın..." class="search-input-field">
                <button class="search-close-btn">&times;</button>
            </div>
            <div class="search-results-preview"></div>
        </div>
    </div>

    <!-- SIMULATED USER ACCOUNT MODAL -->
    <div class="custom-modal profile-modal">
        <button class="modal-close-btn" onclick="document.querySelector('.profile-modal').classList.remove('active'); document.querySelector('.overlay-backdrop').classList.remove('active');">&times;</button>
        <div class="profile-modal-header">
            <h3 class="profile-modal-title">HESABIM</h3>
        </div>
        <form class="profile-form" onsubmit="event.preventDefault(); alert('Giriş başarılı.'); document.querySelector('.profile-modal').classList.remove('active'); document.querySelector('.overlay-backdrop').classList.remove('active');">
            <div class="form-group">
                <label class="form-label">E-POSTA ADRESİ</label>
                <input type="email" class="form-input" value="ahmet@modaren.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">ŞİFRE</label>
                <input type="password" class="form-input" value="••••••••" required>
            </div>
            <button type="submit" class="btn-primary profile-submit-btn">GİRİŞ YAP</button>
        </form>
    </div>

    <!-- Global products inject -->
    <script>
        window.DB_PRODUCTS = <?php 
            $all_p = [];
            if ($db_connected && $conn) {
                try {
                    $q = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
                    $q->execute();
                    $all_p = $q->fetchAll();
                } catch(PDOException $e) {}
            }
            echo json_encode($all_p); 
        ?>;
    </script>
    <script src="script.js"></script>
    <script>
    // ── Promo Code Logic ──
    const CART_SUBTOTAL = <?php echo $subtotal; ?>;
    const CART_SHIPPING = <?php echo $shipping; ?>;
    let appliedDiscount = 0;
    let appliedPromoCode = '';

    function formatPrice(val) {
        return val.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' TL';
    }

    function applyPromoCode() {
        const code = document.getElementById('promo-code-input').value.trim();
        const msgEl = document.getElementById('promo-msg');
        const btn = document.getElementById('promo-apply-btn');
        if (!code) {
            showPromoMsg('error', 'Lütfen bir kod giriniz.');
            return;
        }
        btn.textContent = '...';
        btn.disabled = true;
        const fd = new FormData();
        fd.append('code', code);
        fd.append('subtotal', CART_SUBTOTAL);
        fetch('promo_action.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                btn.textContent = 'UYGULA';
                btn.disabled = false;
                if (data.status === 'success') {
                    appliedDiscount = parseFloat(data.discount_amount) || 0;
                    appliedPromoCode = data.code;
                    updateCartTotals();
                    showPromoMsg('success', data.message);
                    // Pass promo to checkout via URL
                    const co = document.getElementById('checkout-btn');
                    if (co) co.href = 'checkout.php?promo=' + encodeURIComponent(appliedPromoCode) + '&discount=' + appliedDiscount;
                } else {
                    showPromoMsg('error', data.message);
                    appliedDiscount = 0;
                    updateCartTotals();
                }
            })
            .catch(() => {
                btn.textContent = 'UYGULA';
                btn.disabled = false;
                showPromoMsg('error', 'Bağlantı hatası. Lütfen tekrar deneyin.');
            });
    }

    function showPromoMsg(type, msg) {
        const el = document.getElementById('promo-msg');
        el.style.display = 'block';
        el.style.color = type === 'success' ? '#4ade80' : '#f87171';
        el.textContent = msg;
    }

    function updateCartTotals() {
        const discountRow = document.getElementById('cart-discount-row');
        const discountVal  = document.getElementById('cart-discount-val');
        const totalVal     = document.getElementById('cart-total-val');
        if (appliedDiscount > 0) {
            discountRow.style.display = 'flex';
            discountVal.textContent = '- ' + formatPrice(appliedDiscount);
        } else {
            discountRow.style.display = 'none';
        }
        const newTotal = Math.max(0, CART_SUBTOTAL - appliedDiscount + CART_SHIPPING);
        totalVal.textContent = formatPrice(newTotal);
    }

    // Allow Enter key on promo input
    document.addEventListener('DOMContentLoaded', function() {
        const inp = document.getElementById('promo-code-input');
        if (inp) inp.addEventListener('keypress', function(e) { if (e.key === 'Enter') applyPromoCode(); });
    });
    </script>
</body>
</html>
