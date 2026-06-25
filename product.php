<?php
// Include database connection
require_once 'db_connect.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
if ($product_id <= 0) {
    $product_id = 1;
}

// PHP Mock products matching SQL seed for failover
$PHP_MOCK_PRODUCTS = [
    1 => [
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
        'description' => 'Aerodinamik koşu tasarımı, ayağı çorap gibi saran Primeknit üst yüzey yapısı ve asil altın detaylarıyla antrenmanlarınızda ve sokakta maksimum konfor sunar.'
    ],
    2 => [
        'id' => 2,
        'category_id' => 1,
        'category_name' => 'BAYAN',
        'name' => 'Gold Stripe Kadın Antrenman Taytı',
        'brand' => 'MODA AREN ACTIVE',
        'price' => 1299.00,
        'original_price' => null,
        'rating' => 4.80,
        'reviews' => 89,
        'image_url' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=600&q=80',
        'hover_image_url' => 'https://images.unsplash.com/photo-1506152983158-b4a74a01c721?auto=format&fit=crop&w=600&q=80',
        'sizes' => 'XS,S,M,L,XL',
        'is_gold' => 1,
        'description' => 'Yüksek belli, toparlayıcı dikişsiz esnek kumaş. Bacak kenarlarındaki 3 ince metalik altın sarısı şerit ile antrenman şıklığını zirveye çıkarır.'
    ],
    3 => [
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
        'description' => 'Geri dönüştürülmüş pamuk karışımlı kalın polar dokusu sayesinde soğuk havalarda vücut sıcaklığını koruyan rahat kesim spor kapüşonlu üst.'
    ],
    4 => [
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
        'description' => 'Çim sahada yüksek çekiş gücü ve top kontrolü sağlayan esnek bilekli Primeknit dış yüzey. Topuk ve logo bölgesinde altın varak kaplamalar.'
    ],
    5 => [
        'id' => 5,
        'category_id' => 3,
        'category_name' => 'KIDS',
        'name' => 'Genç Çocuk Eşofman Takımı İkili',
        'brand' => 'MODA AREN KIDS',
        'price' => 1499.00,
        'original_price' => null,
        'rating' => 4.70,
        'reviews' => 74,
        'image_url' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80',
        'hover_image_url' => 'https://images.unsplash.com/photo-1502904582529-0a1a2b9910d6?auto=format&fit=crop&w=600&q=80',
        'sizes' => '8-9 Yaş,10-11 Yaş,12-13 Yaş,14-15 Yaş',
        'is_gold' => 0,
        'description' => 'Çocukların gün boyu özgürce hareket edebileceği, yumuşak dokulu ve terletmeyen özel örgü kumaşlı ceket ve pantolon takımı.'
    ],
    6 => [
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
        'description' => 'Su itici ultra hafif kumaş, rüzgar kesen fermuar yapısı ve ayarlanabilir kordonlu kapüşon. Göğsünde şık yansımalı gold logo detayı bulunur.'
    ],
    7 => [
        'id' => 7,
        'category_id' => 1,
        'category_name' => 'BAYAN',
        'name' => 'Fitilli Kadın Crop Antrenman Üstü',
        'brand' => 'MODA AREN ACTIVE',
        'price' => 899.00,
        'original_price' => null,
        'rating' => 4.50,
        'reviews' => 112,
        'image_url' => 'https://images.unsplash.com/photo-1518622358385-8ea7d0794bf6?auto=format&fit=crop&w=600&q=80',
        'hover_image_url' => 'https://images.unsplash.com/photo-1483721310020-03333e577078?auto=format&fit=crop&w=600&q=80',
        'sizes' => 'XS,S,M,L',
        'is_gold' => 0,
        'description' => 'Yumuşak örgü kumaştan üretilen ribanalı şık tasarım. Hem antrenman esnasında hem de günlük yaşamda sokak kombinleri için idealdir.'
    ],
    8 => [
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
        'description' => 'Islak ve kuru eşya ayırıcı özel bölmeler, fermuarlı yan ayakkabı cebi ve asil altın sarısı metal fermuar başlıkları içeren spor seyahat çantası.'
    ]
];

$product = null;
$related_products = [];

// Fetch product details from DB
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Fetch related products from same category
            $r_stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? LIMIT 4");
            $r_stmt->execute([$product['category_id'], $product_id]);
            $related_products = $r_stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $product = null;
    }
}

// Fallback to PHP mock product if offline/missing
if (!$product) {
    if (isset($PHP_MOCK_PRODUCTS[$product_id])) {
        $product = $PHP_MOCK_PRODUCTS[$product_id];
    } else {
        // Default to first product if invalid ID requested
        $product = $PHP_MOCK_PRODUCTS[1];
    }
    
    // Fallback related products
    $related_products = array_filter($PHP_MOCK_PRODUCTS, function($item) use ($product) {
        return $item['category_id'] === $product['category_id'] && $item['id'] !== $product['id'];
    });
    $related_products = array_slice($related_products, 0, 4);
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
    <title><?php echo htmlspecialchars($product['name']); ?> | MODA AREN Resmi Mağazası</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 150)); ?>">
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
                    <a href="women.php" class="nav-link-item <?php echo $product['category_name'] === 'BAYAN' ? 'active' : ''; ?>">BAYAN</a>
                    <a href="men.php" class="nav-link-item <?php echo $product['category_name'] === 'BAY' ? 'active' : ''; ?>">BAY</a>
                    <a href="women.php?cat=KIDS" class="nav-link-item <?php echo $product['category_name'] === 'KIDS' ? 'active' : ''; ?>">KIDS</a>
                    <a href="men.php?cat=SPORTS" class="nav-link-item accent-link <?php echo $product['category_name'] === 'SPORTS' ? 'active' : ''; ?>">SPORTS</a>
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

    <!-- PRODUCT DETAIL LAYOUT -->
    <main class="container section-padding">
        <div class="product-detail-layout">
            <!-- Left Side: Product Image Gallery -->
            <div class="product-detail-gallery">
                <div class="product-gallery-main">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-gallery-thumbs">
                    <div class="product-gallery-thumb">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Detail 1">
                    </div>
                    <div class="product-gallery-thumb">
                        <img src="<?php echo htmlspecialchars($product['hover_image_url'] ?? $product['image_url']); ?>" alt="Detail 2">
                    </div>
                </div>
            </div>

            <!-- Right Side: Product Details & Add to Cart -->
            <div class="product-detail-info">
                <span class="detail-brand"><?php echo htmlspecialchars($product['brand']); ?></span>
                <h1 class="detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="detail-price-row">
                    <span class="text-gold"><?php echo number_format($product['price'], 2, ',', '.'); ?> TL</span>
                    <?php if (isset($product['original_price']) && !empty($product['original_price'])): ?>
                        <span class="detail-price-original"><?php echo number_format($product['original_price'], 2, ',', '.'); ?> TL</span>
                    <?php endif; ?>
                </div>

                <!-- Size Picker Section -->
                <div class="detail-size-section">
                    <div class="detail-size-header">
                        <span class="qty-label">BEDEN SEÇİN</span>
                        <a href="#" class="size-guide-link" onclick="event.preventDefault(); alert('Beden Tablosu:\n\nAyakkabı: 39 - 45 (Standart Kalıp)\nGiyim: XS, S, M, L, XL, XXL\nEkipman: Standart (Tek Beden)');">Beden Tablosu</a>
                    </div>
                    
                    <div id="size-warning" style="display:none; color:red; margin-bottom:15px; font-weight:700; font-size:0.8rem; letter-spacing:1px; text-transform:uppercase;">✕ Lütfen Devam Etmek İçin Beden Seçiniz!</div>
                    
                    <div class="detail-sizes-grid">
                        <?php 
                        $sizes = explode(',', $product['sizes']);
                        foreach ($sizes as $size): 
                        ?>
                            <div class="size-box" data-size="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="detail-qty-section">
                    <span class="qty-label">ADET</span>
                    <div class="detail-qty-selector">
                        <button class="detail-qty-btn minus">-</button>
                        <div class="detail-qty-val">1</div>
                        <button class="detail-qty-btn plus">+</button>
                    </div>
                </div>

                <!-- Add to Cart Form -->
                <form id="add-to-cart-form" method="POST" action="cart_action.php?action=add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                    <input type="hidden" name="size" id="selected-size-input">
                    <input type="hidden" name="quantity" id="qty-input" value="1">
                    
                    <button type="submit" class="btn-primary detail-add-btn">SEPETE EKLE</button>
                </form>

                <!-- Product Specifications Accordion -->
                <div class="detail-accordion-item">
                    <div class="detail-accordion-header">
                        <span>Açıklama & Detaylar</span>
                        <span class="accordion-icon">+</span>
                    </div>
                    <div class="detail-accordion-content active">
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p style="margin-top: 15px;">Adidas tasarımlarından esinlenen bu premium ürün, hem günlük kullanıma hem de zorlu spor antrenmanlarına uyum sağlar. Sürtünmeyi azaltan dikiş yerleşimi ve hava aldıran gözenekli üst yüzeyi ile en üst düzey konforu yakalayın.</p>
                    </div>
                </div>

                <div class="detail-accordion-item">
                    <div class="detail-accordion-header">
                        <span>Özellikler</span>
                        <span class="accordion-icon">+</span>
                    </div>
                    <div class="detail-accordion-content">
                        <ul style="list-style-type: square; margin-left: 20px; display: flex; flex-direction: column; gap: 8px;">
                            <li>%100 geri dönüştürülmüş Primegreen sürdürülebilir malzemeler</li>
                            <li>Metalik altın sarısı reflektör detaylar ve özel dikişler</li>
                            <li>Kaymayı önleyen yüksek çekiş gücüne sahip özel taban yapısı</li>
                            <li>Ter tutmayan ve nefes alabilen mikrofiber dokuma</li>
                        </ul>
                    </div>
                </div>

                <div class="detail-accordion-item" style="border-bottom: 1px solid var(--color-gray-border);">
                    <div class="detail-accordion-header">
                        <span>Teslimat & Kolay İade</span>
                        <span class="accordion-icon">+</span>
                    </div>
                    <div class="detail-accordion-content">
                        <p>1500 TL üzeri tüm siparişlerde kargo ücretsizdir. Siparişleriniz 24 saat içinde kargoya verilir ve ortalama 2-3 iş günü içinde adresinize teslim edilir.</p>
                        <p style="margin-top: 10px;">Kullanılmamış ürünleri fatura tarihinden itibaren 30 gün içinde orijinal kutusuyla birlikte ücretsiz iade kodu kullanarak geri gönderebilirsiniz.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RELATED PRODUCTS SECTION -->
        <?php if (!empty($related_products)): ?>
        <section class="section-padding" style="margin-top: 50px;">
            <div class="section-header">
                <h2 class="section-title">BENZER ÜRÜNLER</h2>
            </div>
            <div class="products-grid">
                <?php foreach ($related_products as $rel): ?>
                <div class="product-card" onclick="window.location.href='product.php?id=<?php echo $rel['id']; ?>'">
                    <div class="product-card-img-container">
                        <?php if (isset($rel['is_gold']) && $rel['is_gold']): ?>
                            <span class="product-badge badge-sale">GOLD EDITION</span>
                        <?php endif; ?>
                        <img class="product-img main-img" src="<?php echo htmlspecialchars($rel['image_url']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                        <img class="product-img hover-img" src="<?php echo htmlspecialchars($rel['hover_image_url'] ?? $rel['image_url']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                        
                        <div class="product-actions-panel">
                            <button class="action-btn-quickadd" onclick="event.stopPropagation(); window.location.href='product.php?id=<?php echo $rel['id']; ?>'">İNCELE</button>
                            <button class="action-btn-quickview" data-product-id="<?php echo $rel['id']; ?>" onclick="event.stopPropagation();" aria-label="Hızlı Bakış">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="product-brand"><?php echo htmlspecialchars($rel['brand']); ?></span>
                        <h3 class="product-title"><?php echo htmlspecialchars($rel['name']); ?></h3>
                        <div class="product-meta-row">
                            <span class="product-price">
                                <?php echo number_format($rel['price'], 2, ',', '.'); ?> TL
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
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
            </div>
        </div>
    </footer>

    <!-- OVERLAY BACKDROP -->
    <div class="overlay-backdrop"></div>

    <!-- MOBILE NAV DRAWER -->
    <div class="side-drawer mobile-nav-drawer">
        <div class="side-drawer-header">
            <span class="side-drawer-title">MENÜ</span>
            <button class="side-drawer-close">&times;</button>
        </div>
        <div class="mobile-nav-content">
            <div class="mobile-nav-menu">
                <a href="women.php" class="mobile-nav-link <?php echo $product['category_name'] === 'BAYAN' ? 'active' : ''; ?>">BAYAN</a>
                <a href="men.php" class="mobile-nav-link <?php echo $product['category_name'] === 'BAY' ? 'active' : ''; ?>">BAY</a>
                <a href="women.php?cat=KIDS" class="mobile-nav-link <?php echo $product['category_name'] === 'KIDS' ? 'active' : ''; ?>">KIDS</a>
                <a href="men.php?cat=SPORTS" class="mobile-nav-link accent">SPORTS</a>
            </div>
        </div>
    </div>

    <!-- CART DRAWER -->
    <div class="side-drawer cart-drawer">
        <div class="side-drawer-header">
            <span class="side-drawer-title">ALIŞVERİŞ SEPETİ</span>
            <button class="side-drawer-close">&times;</button>
        </div>
        <div class="cart-drawer-content"></div>
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

    <!-- QUICK VIEW MODAL (for related products) -->
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
</body>
</html>
