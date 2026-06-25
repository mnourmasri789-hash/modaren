<?php
// Include database connection
require_once 'db_connect.php';

// Fetch products from database if connected
$products = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
        $stmt->execute();
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
}

// Separate out 4 trending items for the homepage
$trending_products = array_slice($products, 0, 4);
if (empty($trending_products)) {
    // If DB is offline, use mock array to render
    $trending_products = [
        [
            'id' => 1,
            'category_name' => 'SPORTS',
            'name' => 'MODA AREN Chronos Gold Runner',
            'brand' => 'MODA AREN GOLD',
            'price' => 3299.00,
            'image_url' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80',
            'sizes' => '39,40,41,42,43,44',
            'description' => 'Aerodinamik koşu tasarımı.'
        ],
        [
            'id' => 2,
            'category_name' => 'BAYAN',
            'name' => 'Gold Stripe Kadın Antrenman Taytı',
            'brand' => 'MODA AREN ACTIVE',
            'price' => 1299.00,
            'image_url' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=600&q=80',
            'sizes' => 'XS,S,M,L,XL',
            'description' => 'Yüksek belli tayt.'
        ],
        [
            'id' => 3,
            'category_name' => 'BAY',
            'name' => 'Performance Pamuklu Kapüşonlu Sweatshirt',
            'brand' => 'MODA AREN CLIMA',
            'price' => 1899.00,
            'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=600&q=80',
            'sizes' => 'S,M,L,XL,XXL',
            'description' => 'Pamuklu sweatshirt.'
        ],
        [
            'id' => 4,
            'category_name' => 'SPORTS',
            'name' => 'Primeknit Metalic Gold Krampon',
            'brand' => 'MODA AREN PRO',
            'price' => 4199.00,
            'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80',
            'sizes' => '40,41,42,43,44',
            'description' => 'Primeknit krampon.'
        ]
    ];
}

// Calculate cart item count
$cart_badge = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_badge += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MODA AREN | Spor & Atletik Giyim Resmi Mağazası</title>
    <meta name="description" content="MODA AREN premium spor giyim, ayakkabı ve aksesuar koleksiyonu. Adidas kalitesi, minimalist tarz ve gold detaylarla spor giyimin yeni yüzü.">
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- PROMO BAR -->
    <div class="promo-bar" id="promoBar">
        <div class="promo-slider">
            <div class="promo-slide active">1500 TL ÜZERİ ALIŞVERİŞLERDE ÜCRETSİZ KARGO</div>
            <div class="promo-slide">MODA AREN YENİ SEZON KOLEKSİYONU ŞİMDİ SATIŞTA</div>
            <div class="promo-slide">GOLD EDITION SERİSİNİ KEŞFEDİN - SINIRLI SAYIDA</div>
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

                <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="profile.php" class="nav-icon-btn" aria-label="Profilim" title="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" style="display:inline-flex;align-items:center;justify-content:center;color:var(--color-gold);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <?php else: ?>
                <button class="nav-icon-btn profile-toggle-btn" aria-label="Kullanıcı Profili" onclick="document.querySelector('.profile-modal').classList.add('active'); document.querySelector('.overlay-backdrop').classList.add('active');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <?php endif; ?>

                <button class="nav-icon-btn cart-toggle-btn" aria-label="Sepeti Göster">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="cart-count-badge" style="<?php echo $cart_badge > 0 ? 'display:flex;' : 'display:none;'; ?>"><?php echo $cart_badge; ?></span>
                </button>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero-section-new">
        <img class="hero-new-bg-img" src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1920&q=80" alt="MODA AREN Yeni Sezon">
        <div class="container">
            <div class="hero-new-content">
                <h1 class="hero-new-title">MODA AREN<br>YENİ SEZON</h1>
                <div class="hero-new-ctas">
                    <a href="men.php" class="btn-adidas">BAY GİYİM</a>
                    <a href="women.php" class="btn-adidas">BAYAN GİYİM</a>
                </div>
            </div>
        </div>
    </section>

    <!-- TRENDING NOW GALLERY -->
    <section class="section-padding container">
        <div class="section-header">
            <h2 class="section-title">TRENDING NOW</h2>
            <a href="men.php" class="category-card-link" style="color: var(--color-black); border-bottom: 2px solid var(--color-black); padding-bottom: 4px;">
                TÜMÜNÜ GÖR 
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
        
        <div class="trending-grid">
            <?php foreach ($trending_products as $product): ?>
            <div class="trending-card" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'" style="position:relative;">
                <!-- Wishlist Heart -->
                <button class="wishlist-heart-btn" data-product-id="<?php echo $product['id']; ?>" aria-label="İstek listesine ekle" aria-pressed="false" title="İstek listesine ekle" onclick="event.stopPropagation();">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
                <div class="trending-img-wrapper">
                    <img class="trending-img" src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="trending-info">
                    <span class="trending-product-name"><?php echo htmlspecialchars($product['name']); ?></span>
                    <span class="trending-category"><?php echo htmlspecialchars($product['brand']); ?></span>
                    <span class="trending-price"><?php echo number_format($product['price'], 2, ',', '.'); ?> TL</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CATEGORY HIGHLIGHT CARDS -->
    <section class="section-padding bg-gold" style="background-color: var(--color-light-bg) !important; border-top: 1px solid var(--color-gray-border);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title gold-bar">KATEGORİLER</h2>
            </div>
            
            <div class="categories-grid">
                <div class="category-card" onclick="window.location.href='women.php'">
                    <img class="category-card-img" src="https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=600&q=80" alt="Bayan Koleksiyonu">
                    <div class="category-card-info">
                        <h3 class="category-card-name">BAYAN</h3>
                        <a href="women.php" class="category-card-link">KEŞFET <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </div>
                </div>
                
                <div class="category-card" onclick="window.location.href='men.php'">
                    <img class="category-card-img" src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=600&q=80" alt="Bay Koleksiyonu">
                    <div class="category-card-info">
                        <h3 class="category-card-name">BAY</h3>
                        <a href="men.php" class="category-card-link">KEŞFET <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </div>
                </div>

                <div class="category-card" onclick="window.location.href='women.php'">
                    <img class="category-card-img" src="https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=600&q=80" alt="Çocuk Giyim">
                    <div class="category-card-info">
                        <h3 class="category-card-name">KIDS</h3>
                        <a href="women.php" class="category-card-link">KEŞFET <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </div>
                </div>

                <div class="category-card" onclick="window.location.href='men.php'">
                    <img class="category-card-img" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80" alt="Spor Ekipmanları">
                    <div class="category-card-info">
                        <h3 class="category-card-name">SPORTS</h3>
                        <a href="men.php" class="category-card-link">KEŞFET <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BRAND STATEMENT -->
    <section class="brand-statement-section">
        <div class="container">
            <h2 class="brand-statement-title">SPORUN KURALINI SEN YAZ</h2>
            <p class="brand-statement-text">MODA AREN, atletik performansı şık sokak stiliyle harmanlayarak sınırları aşmanızı sağlar. En kaliteli materyallerle dokunan, modern dikiş dikiş işlenmiş Primeknit yapılarımız ve gold metalik detaylarımızla kendinizi sahada ve sokakta ayrıcalıklı hissedin.</p>
            
            <div class="brand-features">
                <div class="feature-item">
                    <div class="feature-icon-container">✓</div>
                    <h4 class="feature-title">Ücretsiz Hızlı Kargo</h4>
                    <p class="feature-desc">1500 TL ve üzeri siparişlerinizde hızlı ve ücretsiz teslimat fırsatı.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-container">⇆</div>
                    <h4 class="feature-title">Kolay İade & Değişim</h4>
                    <p class="feature-desc">Kullanılmamış ürünlerde 30 gün boyunca koşulsuz şartsız iade imkanı.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-container">☆</div>
                    <h4 class="feature-title">Premium Kalite Garantisi</h4>
                    <p class="feature-desc">MODA AREN etiketli her ürün üst düzey dayanıklılık ve konfor testlerinden geçer.</p>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a class="footer-link-item" href="women.php">Yeni Sezon</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-col-title">SPORTS</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="men.php">Koşu</a></li>
                        <li><a class="footer-link-item" href="men.php">Antrenman</a></li>
                        <li><a class="footer-link-item" href="men.php">Futbol</a></li>
                        <li><a class="footer-link-item" href="women.php">Yoga</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-col-title">SUPPORT</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="#">Yardım & SSS</a></li>
                        <li><a class="footer-link-item" href="#">İade ve Değişim</a></li>
                        <li><a class="footer-link-item" href="#">Sipariş Takibi</a></li>
                        <li><a class="footer-link-item" href="#">Ödeme Seçenekleri</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-col-title">COMPANY INFO</h4>
                    <ul class="footer-links">
                        <li><a class="footer-link-item" href="#">MODA AREN Hakkında</a></li>
                        <li><a class="footer-link-item" href="#">Sürdürülebilirlik</a></li>
                        <li><a class="footer-link-item" href="#">Kariyer</a></li>
                        <li><a class="footer-link-item" href="#">İletişim</a></li>
                    </ul>
                </div>
            </div>

            <!-- Middle Bar with Newsletter and Socials -->
            <div class="footer-middle-bar">
                <div class="footer-newsletter">
                    <span class="newsletter-label">BÜLTENİMİZE ABONE OLUN</span>
                    <form class="footer-newsletter-form" onsubmit="event.preventDefault(); alert('Bültene başarıyla kaydoldunuz!'); this.reset();">
                        <input type="email" placeholder="E-posta Adresiniz" class="footer-newsletter-input" required>
                        <button type="submit" class="footer-newsletter-btn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                </div>
                <div class="footer-social-wrapper">
                    <span class="socials-label">BİZİ TAKİP EDİN</span>
                    <div class="footer-socials-minimal">
                        <a href="#" class="social-icon-minimal">FB</a>
                        <a href="#" class="social-icon-minimal">IG</a>
                        <a href="#" class="social-icon-minimal">YT</a>
                        <a href="#" class="social-icon-minimal">TW</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p class="copyright">&copy; <?php echo date('Y'); ?> MODA AREN. Tüm Hakları Saklıdır.</p>
                <div class="footer-bottom-links">
                    <a href="#">Gizlilik Politikası</a>
                    <a href="#">Kullanım Şartları</a>
                    <a href="#">Çerez Tercihleri</a>
                </div>
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
            <div class="mobile-nav-footer">
                <button class="btn-primary mobile-nav-btn" onclick="document.querySelector('.profile-modal').classList.add('active'); document.querySelector('.mobile-nav-drawer').classList.remove('active');">GİRİŞ YAP</button>
            </div>
        </div>
    </div>

    <!-- SIDE DRAWER: SLIDING CART -->
    <div class="side-drawer cart-drawer">
        <div class="side-drawer-header">
            <span class="side-drawer-title">ALIŞVERİŞ SEPETİ</span>
            <button class="side-drawer-close">&times;</button>
        </div>
        <div class="cart-drawer-content">
            <!-- Filled dynamically via javascript -->
        </div>
        <div class="cart-drawer-footer">
            <div class="cart-summary-row">
                <span>Ara Toplam</span>
                <span class="cart-subtotal-val">0,00 TL</span>
            </div>
            <div class="cart-summary-row total-row">
                <span>Toplam (KDV Dahil)</span>
                <span class="cart-total-val">0,00 TL</span>
            </div>
            <a href="cart.php" class="btn-secondary cart-drawer-checkout-btn" style="margin-bottom: 12px; width:100%; box-sizing:border-box;">SEPETİ DÜZENLE</a>
            <a href="checkout.php" class="btn-primary cart-drawer-checkout-btn" style="width:100%; box-sizing:border-box;">ÖDEMEYE GEÇ</a>
        </div>
    </div>

    <!-- SEARCH PANEL OVERLAY -->
    <div class="search-overlay-panel">
        <div class="container search-panel-container">
            <div class="search-panel-row">
                <input type="text" placeholder="Ürün adı, kategori veya marka arayın..." class="search-input-field">
                <button class="search-close-btn">&times;</button>
            </div>
            <div class="search-suggestions">
                <span class="suggestion-title">Popüler Aramalar</span>
                <div class="suggestion-tags">
                    <button class="suggestion-tag-btn" onclick="document.querySelector('.search-input-field').value='Runner'; document.querySelector('.search-input-field').dispatchEvent(new Event('input'));">Runner</button>
                    <button class="suggestion-tag-btn" onclick="document.querySelector('.search-input-field').value='Gold'; document.querySelector('.search-input-field').dispatchEvent(new Event('input'));">Gold</button>
                    <button class="suggestion-tag-btn" onclick="document.querySelector('.search-input-field').value='Hoodie'; document.querySelector('.search-input-field').dispatchEvent(new Event('input'));">Hoodie</button>
                    <button class="suggestion-tag-btn" onclick="document.querySelector('.search-input-field').value='Tayt'; document.querySelector('.search-input-field').dispatchEvent(new Event('input'));">Tayt</button>
                </div>
            </div>
            <!-- Search Results Preview Grid -->
            <div class="search-results-preview"></div>
        </div>
    </div>

    <!-- QUICK VIEW MODAL -->
    <div class="custom-modal">
        <button class="modal-close-btn">&times;</button>
        <div class="quickview-layout">
            <div class="quickview-gallery">
                <!-- Image loaded dynamically -->
            </div>
            <div class="quickview-details">
                <span class="quickview-brand">Brand</span>
                <h3 class="quickview-title">Product Name</h3>
                <div class="quickview-price-row">Price TL</div>
                <p class="quickview-desc">Description text.</p>
                
                <span class="quickview-option-title">Beden Seçin</span>
                <div class="quickview-sizes">
                    <!-- Sizes loaded dynamically -->
                </div>
                
                <button class="btn-primary quickview-add-btn">SEPETE EKLE</button>
            </div>
        </div>
    </div>

    <!-- USER ACCOUNT MODAL -->
    <div class="custom-modal profile-modal">
        <button class="modal-close-btn" onclick="document.querySelector('.profile-modal').classList.remove('active'); document.querySelector('.overlay-backdrop').classList.remove('active');">&times;</button>
        <div class="profile-modal-header">
            <h3 class="profile-modal-title">HESABIM</h3>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Logged In View -->
            <div style="text-align: center; padding: 20px 0; display:flex; flex-direction:column; gap:15px;">
                <span style="font-size:1.05rem; font-weight:700;">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                <span style="font-size:0.85rem; color:var(--color-text-muted);">Üyelik Durumu: <strong><?php echo $_SESSION['user_role'] === 'admin' ? 'Yönetici' : 'Müşteri'; ?></strong></span>
                
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php" class="btn-gold" style="justify-content:center; padding:12px 0; font-size:0.8rem; box-sizing:border-box; width:100%;">YÖNETİM PANELİ</a>
                <?php endif; ?>
                
                <button class="btn-primary auth-logout-btn" style="justify-content:center; padding:12px 0; font-size:0.8rem; width:100%;">ÇIKIŞ YAP</button>
            </div>
        <?php else: ?>
            <!-- Logged Out View -->
            <div class="profile-modal-tabs">
                <div class="profile-tab active" data-target="login">ÜYE GİRİŞİ</div>
                <div class="profile-tab" data-target="register">KAYIT OL</div>
            </div>
            
            <!-- Login Form -->
            <form class="profile-form auth-login-form" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="auth-error-box" style="display:none; background-color:#fdf2f2; border:1.5px solid red; color:red; padding:10px; font-size:0.75rem; font-weight:700; text-transform:uppercase; text-align:center;"></div>
                
                <div class="form-group">
                    <label class="form-label">E-POSTA ADRESİ *</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">ŞİFRE *</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-primary profile-submit-btn" style="width:100%; justify-content:center;">GİRİŞ YAP</button>
            </form>
            
            <!-- Register Form -->
            <form class="profile-form auth-register-form" style="display: none; flex-direction: column; gap: 15px;">
                <div class="auth-error-box" style="display:none; background-color:#fdf2f2; border:1.5px solid red; color:red; padding:10px; font-size:0.75rem; font-weight:700; text-transform:uppercase; text-align:center;"></div>
                
                <div class="form-group">
                    <label class="form-label">AD SOYAD *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">E-POSTA ADRESİ *</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">ŞİFRE (En az 6 karakter) *</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-primary profile-submit-btn" style="width:100%; justify-content:center;">KAYIT OL</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Database Products Injected For Client-Side Search / Quick View -->
    <script>
        window.DB_PRODUCTS = <?php echo json_encode($products); ?>;
    </script>
    
    <!-- Client Javascript -->
    <script src="script.js"></script>
</body>
</html>
