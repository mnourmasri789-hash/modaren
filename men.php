<?php
// Include database connection
require_once 'db_connect.php';

// Check category parameter
$cat_filter = isset($_GET['cat']) ? trim($_GET['cat']) : '';

// Fetch products from database if connected
$products = [];
if ($db_connected && $conn) {
    try {
        if (!empty($cat_filter)) {
            $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = ? ORDER BY p.id ASC");
            $stmt->execute([$cat_filter]);
        } else {
            // Default men products include BAY (category 2) and SPORTS (category 4)
            $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = 'BAY' OR c.name = 'SPORTS' ORDER BY p.id ASC");
            $stmt->execute();
        }
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
}

// Fallback to mock data if database is empty or offline
if (empty($products)) {
    $all_mock = [
        [
            'id' => 1,
            'category_id' => 4,
            'category_name' => 'SPORTS',
            'name' => 'MODA AREN Chronos Gold Runner',
            'brand' => 'MODA AREN GOLD',
            'price' => 3299.00,
            'original_price' => 3899.00,
            'rating' => 4.90,
            'reviews' => 124,
            'image_url' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80',
            'hover_image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
            'sizes' => '39,40,41,42,43,44',
            'is_gold' => 1,
            'description' => 'Aerodinamik koşu tasarımı.'
        ],
        [
            'id' => 3,
            'category_id' => 2,
            'category_name' => 'BAY',
            'name' => 'Performance Pamuklu Kapüşonlu Sweatshirt',
            'brand' => 'MODA AREN CLIMA',
            'price' => 1899.00,
            'original_price' => null,
            'rating' => 4.60,
            'reviews' => 56,
            'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=600&q=80',
            'hover_image_url' => 'https://images.unsplash.com/photo-1519985176271-adb1088fa94c?auto=format&fit=crop&w=600&q=80',
            'sizes' => 'S,M,L,XL,XXL',
            'is_gold' => 0,
            'description' => 'Pamuklu sweatshirt.'
        ],
        [
            'id' => 4,
            'category_id' => 4,
            'category_name' => 'SPORTS',
            'name' => 'Primeknit Metalic Gold Krampon',
            'brand' => 'MODA AREN PRO',
            'price' => 4199.00,
            'original_price' => 4999.00,
            'rating' => 5.00,
            'reviews' => 42,
            'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80',
            'hover_image_url' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80',
            'sizes' => '40,41,42,43,44',
            'is_gold' => 1,
            'description' => 'Primeknit krampon.'
        ],
        [
            'id' => 6,
            'category_id' => 4,
            'category_name' => 'SPORTS',
            'name' => 'Gold Reflection Su Geçirmez Rüzgarlık',
            'brand' => 'MODA AREN OUTDOOR',
            'price' => 2899.00,
            'original_price' => null,
            'rating' => 4.70,
            'reviews' => 31,
            'image_url' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=600&q=80',
            'hover_image_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&q=80',
            'sizes' => 'S,M,L,XL',
            'is_gold' => 1,
            'description' => 'Su itici rüzgarlık.'
        ],
        [
            'id' => 8,
            'category_id' => 4,
            'category_name' => 'SPORTS',
            'name' => 'Premium Gold Metal Fermuarlı Spor Çantası',
            'brand' => 'MODA AREN ACCESSORIES',
            'price' => 1599.00,
            'original_price' => null,
            'rating' => 4.80,
            'reviews' => 65,
            'image_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
            'hover_image_url' => 'https://images.unsplash.com/photo-1547949003-9792a18a2601?auto=format&fit=crop&w=600&q=80',
            'sizes' => 'Standart',
            'is_gold' => 1,
            'description' => 'Spor duffle çanta.'
        ]
    ];
    
    if (!empty($cat_filter)) {
        $products = array_filter($all_mock, function($item) use ($cat_filter) {
            return $item['category_name'] === $cat_filter;
        });
    } else {
        $products = $all_mock;
    }
}

// Calculate cart count
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
    <title>Erkek Spor Giyim ve Ayakkabı | MODA AREN</title>
    <meta name="description" content="Erkek spor giyim, ayakkabı, rüzgarlık ve aksesuarları MODA AREN'de keşfedin. Spor salonundan sokağa premium şıklık.">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- PROMO BAR -->
    <div class="promo-bar" id="promoBar">
        <div class="promo-slider">
            <div class="promo-slide active">1500 TL ÜZERİ ALIŞVERİŞLERDE ÜCRETSİZ KARGO</div>
            <div class="promo-slide">TÜM KARTLARA VADE FARKSIZ 3 TAKSİT FIRSATI</div>
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
                    <a href="men.php" class="nav-link-item <?php echo empty($cat_filter) ? 'active' : ''; ?>">BAY</a>
                    <a href="women.php?cat=KIDS" class="nav-link-item">KIDS</a>
                    <a href="men.php?cat=SPORTS" class="nav-link-item accent-link <?php echo $cat_filter === 'SPORTS' ? 'active' : ''; ?>">SPORTS</a>
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

    <!-- CATEGORY BANNER -->
    <div style="background-color: var(--color-black); color: var(--color-white); padding: 60px 0; border-bottom: 2px solid var(--color-gold); text-align: center; position: relative;">
        <div class="container">
            <span style="font-size: 0.8rem; font-weight: 800; letter-spacing: 3px; color: var(--color-gold); text-transform: uppercase;">MODA AREN KOLEKSİYONU</span>
            <h1 style="font-size: 3rem; font-weight: 900; letter-spacing: 2px; margin-top: 10px; text-transform: uppercase;">
                <?php echo $cat_filter === 'SPORTS' ? 'SPORTS & EKİPMAN' : 'ERKEK (BAY) KOLEKSİYONU'; ?>
            </h1>
            <p style="font-size: 0.95rem; color: var(--color-text-muted); max-width: 600px; margin: 15px auto 0 auto; line-height: 1.6;">En yeni teknolojilerle donatılmış yüksek performanslı koşu ayakkabıları, rüzgarlıklar ve antrenman ekipmanları ile gücünüze güç katın.</p>
        </div>
    </div>

    <!-- MAIN PRODUCT CATALOG GRID -->
    <main class="section-padding container">
        <!-- Filter Category Tabs -->
        <div class="filter-tabs">
            <a href="men.php" class="filter-tab <?php echo empty($cat_filter) ? 'active' : ''; ?>">TÜM ERKEK ÜRÜNLERİ</a>
            <a href="men.php?cat=SPORTS" class="filter-tab <?php echo $cat_filter === 'SPORTS' ? 'active' : ''; ?>">SPORTS & EKİPMAN</a>
            <a href="men.php?cat=BAY" class="filter-tab <?php echo $cat_filter === 'BAY' ? 'active' : ''; ?>">BAY GİYİM</a>
        </div>

        <!-- Catalog Product Cards Grid -->
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'">
                <div class="product-card-img-container">
                    <?php if (isset($product['is_gold']) && $product['is_gold']): ?>
                        <span class="product-badge badge-sale">GOLD EDITION</span>
                    <?php endif; ?>
                    <img class="product-img main-img" src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <img class="product-img hover-img" src="<?php echo htmlspecialchars($product['hover_image_url'] ?? $product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <div class="product-actions-panel">
                        <!-- Quick Add redirects to detail page for sizing -->
                        <button class="action-btn-quickadd" onclick="event.stopPropagation(); window.location.href='product.php?id=<?php echo $product['id']; ?>'">İNCELE</button>
                        <!-- Quick View displays popup overlay with size options -->
                        <button class="action-btn-quickview" data-product-id="<?php echo $product['id']; ?>" onclick="event.stopPropagation();" aria-label="Hızlı Bakış">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></span>
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-meta-row">
                        <span class="product-price">
                            <?php echo number_format($product['price'], 2, ',', '.'); ?> TL
                            <?php if (isset($product['original_price']) && !empty($product['original_price'])): ?>
                                <span class="product-price-original"><?php echo number_format($product['original_price'], 2, ',', '.'); ?> TL</span>
                            <?php endif; ?>
                        </span>
                        <div class="product-rating">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <span><?php echo number_format($product['rating'] ?? 4.8, 1); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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
                <a href="men.php" class="mobile-nav-link active">BAY</a>
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
            <div class="search-results-preview"></div>
        </div>
    </div>

    <!-- QUICK VIEW MODAL -->
    <div class="custom-modal">
        <button class="modal-close-btn">&times;</button>
        <div class="quickview-layout">
            <div class="quickview-gallery"></div>
            <div class="quickview-details">
                <span class="quickview-brand">Brand</span>
                <h3 class="quickview-title">Product Name</h3>
                <div class="quickview-price-row">Price TL</div>
                <p class="quickview-desc">Description text.</p>
                
                <span class="quickview-option-title">Beden Seçin</span>
                <div class="quickview-sizes"></div>
                
                <button class="btn-primary quickview-add-btn">SEPETE EKLE</button>
            </div>
        </div>
    </div>

    <!-- SIMULATED USER ACCOUNT MODAL -->
    <div class="custom-modal profile-modal">
        <button class="modal-close-btn" onclick="document.querySelector('.profile-modal').classList.remove('active'); document.querySelector('.overlay-backdrop').classList.remove('active');">&times;</button>
        <div class="profile-modal-header">
            <h3 class="profile-modal-title">HESABIM</h3>
        </div>
        <div class="profile-modal-tabs">
            <div class="profile-tab active">ÜYE GİRİŞİ</div>
        </div>
        <form class="profile-form" onsubmit="event.preventDefault(); alert('Simüle giriş başarılı! Keyifli alışverişler.'); document.querySelector('.profile-modal').classList.remove('active'); document.querySelector('.overlay-backdrop').classList.remove('active');">
            <div class="form-group">
                <label class="form-label">E-POSTA ADRESİ</label>
                <input type="email" class="form-input" value="ahmet@modaren.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">ŞİFRE</label>
                <input type="password" class="form-input" value="••••••••" required>
            </div>
            <a href="#" class="forgot-password-link">Şifremi Unuttum</a>
            <button type="submit" class="btn-primary profile-submit-btn">GİRİŞ YAP</button>
        </form>
    </div>

    <!-- Global products inject -->
    <script>
        // Use PHP to populate active items on load
        window.DB_PRODUCTS = <?php 
            // Query all products for global searches
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
</body>
</html>
