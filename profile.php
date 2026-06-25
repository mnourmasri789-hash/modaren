<?php
require_once 'db_connect.php';

// Redirect to home if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id   = (int)$_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Kullanıcı';
$user_role = $_SESSION['user_role'] ?? 'user';

$success_msg = '';
$error_msg   = '';

// ------------------------------------------------------------------
// Handle Profile Update
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $post_action = $_POST['action'];

    if ($post_action === 'update_profile') {
        $new_name  = trim($_POST['name'] ?? '');
        $new_email = trim($_POST['email'] ?? '');
        $new_pass  = trim($_POST['password'] ?? '');
        $cur_pass  = trim($_POST['current_password'] ?? '');

        if (empty($new_name) || empty($new_email)) {
            $error_msg = 'Ad ve e-posta alanları zorunludur.';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Geçerli bir e-posta giriniz.';
        } elseif ($db_connected && $conn) {
            try {
                // Fetch current user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $current_user = $stmt->fetch();

                if ($new_pass !== '') {
                    if (!password_verify($cur_pass, $current_user['password'])) {
                        $error_msg = 'Mevcut şifreniz hatalı.';
                    } elseif (strlen($new_pass) < 6) {
                        $error_msg = 'Yeni şifre en az 6 karakter olmalıdır.';
                    } else {
                        $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                        $upd = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
                        $upd->execute([$new_name, $new_email, $hashed, $user_id]);
                        $_SESSION['user_name'] = $new_name;
                        $user_name = $new_name;
                        $success_msg = 'Profil ve şifreniz başarıyla güncellendi.';
                    }
                } else {
                    $upd = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
                    $upd->execute([$new_name, $new_email, $user_id]);
                    $_SESSION['user_name'] = $new_name;
                    $user_name = $new_name;
                    $success_msg = 'Profiliniz başarıyla güncellendi.';
                }
            } catch (PDOException $e) {
                $error_msg = 'Güncelleme hatası: ' . $e->getMessage();
            }
        } else {
            $_SESSION['user_name'] = $new_name;
            $user_name = $new_name;
            $success_msg = 'Profil güncellendi (Çevrimdışı Mod).';
        }
    }
}

// ------------------------------------------------------------------
// Fetch User Data
// ------------------------------------------------------------------
$user_data = [
    'name'       => $user_name,
    'email'      => '',
    'created_at' => date('Y-m-d'),
];

if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        if ($row) $user_data = $row;
    } catch (PDOException $e) { /* silent */ }
}

// ------------------------------------------------------------------
// Fetch Order History
// ------------------------------------------------------------------
$orders = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("
            SELECT o.id, o.customer_name, o.total_price, o.payment_method, o.created_at,
                   COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) { /* silent */ }
} else {
    // Demo orders for mock mode
    $orders = [
        ['id'=>1001, 'customer_name'=>$user_name, 'total_price'=>3398.00, 'payment_method'=>'Kredi Kartı', 'created_at'=>'2025-06-01 14:22:00', 'item_count'=>2],
        ['id'=>1002, 'customer_name'=>$user_name, 'total_price'=>1299.00, 'payment_method'=>'Kredi Kartı', 'created_at'=>'2025-05-20 09:15:00', 'item_count'=>1],
    ];
}

// ------------------------------------------------------------------
// Fetch Wishlist Items
// ------------------------------------------------------------------
$wishlist_items = [];
if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("
            SELECT p.id, p.name, p.brand, p.price, p.image_url
            FROM wishlist w JOIN products p ON p.id = w.product_id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $wishlist_items = $stmt->fetchAll();
    } catch (PDOException $e) { /* silent */ }
}

$cart_badge = 0;
foreach (($_SESSION['cart'] ?? []) as $item) $cart_badge += $item['quantity'];

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim | MODA AREN</title>
    <meta name="description" content="MODA AREN hesabınızı yönetin, siparişlerinizi görüntüleyin ve istek listenize göz atın.">
    <link rel="stylesheet" href="style.css">
    <style>
        /* ── Profile Page Exclusive Styles ── */
        .profile-hero {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0f0f0f 100%);
            border-bottom: 1px solid rgba(212,175,55,0.2);
            padding: 60px 0 40px;
        }
        .profile-hero-inner {
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .profile-avatar {
            width: 90px; height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-gold-dark) 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; font-weight: 800; color: #000;
            flex-shrink: 0;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.3), 0 8px 32px rgba(212,175,55,0.15);
        }
        .profile-hero-info h1 {
            font-size: 1.8rem; font-weight: 800;
            letter-spacing: 1px; margin-bottom: 4px;
        }
        .profile-hero-info p {
            color: var(--color-text-muted); font-size: 0.9rem;
        }
        .profile-hero-info .role-badge {
            display: inline-block;
            background: linear-gradient(90deg, var(--color-gold), var(--color-gold-dark));
            color: #000;
            font-size: 0.65rem; font-weight: 800;
            letter-spacing: 2px; text-transform: uppercase;
            padding: 2px 10px; border-radius: 30px;
            margin-top: 6px;
        }
        /* Tabs */
        .profile-tabs-wrap {
            border-bottom: 1px solid var(--color-gray-border);
            margin-top: 40px;
        }
        .profile-tabs {
            display: flex; gap: 0; overflow-x: auto;
        }
        .profile-tab-btn {
            background: none; border: none; cursor: pointer;
            font-family: var(--font-primary); font-size: 0.8rem;
            font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
            color: var(--color-text-muted);
            padding: 14px 24px;
            border-bottom: 2px solid transparent;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .profile-tab-btn:hover { color: #fff; }
        .profile-tab-btn.active {
            color: var(--color-gold);
            border-bottom-color: var(--color-gold);
        }
        /* Tab Content */
        .profile-tab-content { display: none; padding: 40px 0; }
        .profile-tab-content.active { display: block; }

        /* Orders */
        .orders-table-wrap { overflow-x: auto; }
        .orders-table {
            width: 100%; border-collapse: collapse;
            font-size: 0.88rem;
        }
        .orders-table th {
            text-align: left; padding: 12px 16px;
            font-size: 0.72rem; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase;
            color: var(--color-text-muted);
            border-bottom: 1px solid var(--color-gray-border);
        }
        .orders-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .orders-table tr:hover td { background: rgba(255,255,255,0.02); }
        .order-status {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; padding: 4px 12px; border-radius: 30px;
        }
        .order-status.delivered {
            background: rgba(74,222,128,0.1); color: #4ade80;
        }
        .order-detail-btn {
            font-size: 0.75rem; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: var(--color-gold);
            text-decoration: none;
            padding: 6px 14px; border: 1px solid rgba(212,175,55,0.3);
            border-radius: 4px;
            transition: all 0.2s;
        }
        .order-detail-btn:hover {
            background: var(--color-gold); color: #000;
        }
        /* Profile Form */
        .profile-form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
            max-width: 700px;
        }
        @media (max-width: 600px) { .profile-form-grid { grid-template-columns: 1fr; } }
        .profile-form-grid .full-width { grid-column: 1 / -1; }
        .form-group label {
            display: block; font-size: 0.72rem; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase;
            color: var(--color-text-muted); margin-bottom: 8px;
        }
        .form-group input {
            width: 100%; padding: 12px 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--color-gray-border);
            border-radius: 6px; color: #fff;
            font-family: var(--font-primary); font-size: 0.9rem;
            transition: border-color 0.25s;
        }
        .form-group input:focus {
            outline: none; border-color: var(--color-gold);
        }
        .form-divider {
            grid-column: 1 / -1;
            border: none; border-top: 1px solid var(--color-gray-border);
            margin: 10px 0;
        }
        .form-note {
            grid-column: 1 / -1;
            font-size: 0.75rem; color: var(--color-text-muted);
        }

        /* Wishlist Grid */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }
        .wishlist-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px; overflow: hidden;
            transition: transform 0.3s, border-color 0.3s;
        }
        .wishlist-card:hover {
            transform: translateY(-4px);
            border-color: rgba(212,175,55,0.3);
        }
        .wishlist-card-img {
            width: 100%; aspect-ratio: 1;
            object-fit: cover; display: block;
        }
        .wishlist-card-body {
            padding: 14px;
        }
        .wishlist-card-brand {
            font-size: 0.65rem; font-weight: 700; letter-spacing: 2px;
            text-transform: uppercase; color: var(--color-text-muted);
            display: block; margin-bottom: 4px;
        }
        .wishlist-card-name {
            font-size: 0.85rem; font-weight: 700; margin-bottom: 8px;
            display: block;
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .wishlist-card-price {
            color: var(--color-gold); font-weight: 800; font-size: 0.95rem;
        }
        .wishlist-card-actions {
            display: flex; gap: 8px; margin-top: 12px;
        }
        .wl-btn {
            flex: 1; padding: 8px 0; border: none;
            border-radius: 4px; font-size: 0.72rem; font-weight: 700;
            letter-spacing: 1px; text-transform: uppercase;
            cursor: pointer; transition: all 0.2s; text-align: center;
            text-decoration: none; display: block;
        }
        .wl-btn-primary {
            background: var(--color-gold); color: #000;
        }
        .wl-btn-primary:hover { background: #f0c93c; }
        .wl-btn-secondary {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.15); color: var(--color-text-muted);
        }
        .wl-btn-secondary:hover { border-color: #f00; color: #f00; }

        /* Alert messages */
        .alert-success, .alert-error {
            padding: 14px 20px; border-radius: 8px;
            font-size: 0.88rem; font-weight: 600;
            margin-bottom: 28px;
        }
        .alert-success {
            background: rgba(74,222,128,0.1);
            border: 1px solid rgba(74,222,128,0.3); color: #4ade80;
        }
        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3); color: #f87171;
        }

        /* Empty state */
        .empty-state {
            text-align: center; padding: 80px 20px;
        }
        .empty-state svg { color: rgba(255,255,255,0.15); margin-bottom: 20px; }
        .empty-state h3 {
            font-size: 1.15rem; font-weight: 700; margin-bottom: 10px;
        }
        .empty-state p {
            color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 28px;
        }
    </style>
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
            <div class="nav-center">
                <a href="index.php" class="logo-link"><span>MODA AREN</span></a>
            </div>
            <div class="nav-right">
                <button class="nav-icon-btn search-toggle-btn" aria-label="Arama">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <a href="profile.php" class="nav-icon-btn" aria-label="Profil" style="display:inline-flex;align-items:center;justify-content:center;color:var(--color-gold);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <button class="nav-icon-btn cart-toggle-btn" aria-label="Sepet">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="cart-count-badge" style="<?php echo $cart_badge > 0 ? 'display:flex;' : 'display:none;'; ?>"><?php echo $cart_badge; ?></span>
                </button>
            </div>
        </div>
    </header>

    <!-- PROFILE HERO -->
    <div class="profile-hero">
        <div class="container">
            <div class="profile-hero-inner">
                <div class="profile-avatar"><?php echo mb_strtoupper(mb_substr($user_name, 0, 1)); ?></div>
                <div class="profile-hero-info">
                    <h1><?php echo htmlspecialchars($user_data['name']); ?></h1>
                    <p><?php echo htmlspecialchars($user_data['email'] ?: 'E-posta yükleniyor...'); ?></p>
                    <span class="role-badge"><?php echo $user_role === 'admin' ? '⭐ Admin' : 'Premium Üye'; ?></span>
                    <p style="margin-top:8px; font-size:0.78rem; color:var(--color-text-muted);">
                        Üyelik Tarihi: <?php echo date('d.m.Y', strtotime($user_data['created_at'])); ?>
                    </p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="profile-tabs-wrap">
                <div class="profile-tabs">
                    <button class="profile-tab-btn <?php echo $active_tab==='orders'?'active':''; ?>" onclick="switchTab('orders')">📦 Siparişlerim</button>
                    <button class="profile-tab-btn <?php echo $active_tab==='wishlist'?'active':''; ?>" onclick="switchTab('wishlist')">❤️ İstek Listem (<?php echo count($wishlist_items); ?>)</button>
                    <button class="profile-tab-btn <?php echo $active_tab==='settings'?'active':''; ?>" onclick="switchTab('settings')">⚙️ Hesap Ayarları</button>
                    <?php if ($user_role === 'admin'): ?>
                    <a href="admin.php" class="profile-tab-btn" style="color:var(--color-gold);">🛡️ Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PROFILE MAIN CONTENT -->
    <main class="container" style="padding-top:0;">

        <!-- TAB: ORDERS -->
        <div class="profile-tab-content <?php echo $active_tab==='orders'?'active':''; ?>" id="tab-orders">
            <h2 style="font-size:1.25rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;margin-bottom:28px;">Sipariş Geçmişim</h2>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 7H4C2.9 7 2 7.9 2 9v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM16 3H8c-1.1 0-2 .9-2 2v2h12V5c0-1.1-.9-2-2-2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <h3>Henüz siparişiniz bulunmuyor.</h3>
                    <p>MODA AREN koleksiyonlarını keşfedin ve ilk siparişinizi verin!</p>
                    <a href="index.php" class="btn-primary">ALIŞVERİŞE BAŞLA</a>
                </div>
            <?php else: ?>
                <div class="orders-table-wrap">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Tarih</th>
                                <th>Ürün Sayısı</th>
                                <th>Toplam</th>
                                <th>Ödeme</th>
                                <th>Durum</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td style="font-weight:700; color:var(--color-gold);">#<?php echo $order['id']; ?></td>
                                <td style="color:var(--color-text-muted);"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                <td style="font-weight:600;"><?php echo $order['item_count']; ?> ürün</td>
                                <td style="font-weight:800; color:var(--color-gold);"><?php echo number_format($order['total_price'], 2, ',', '.'); ?> TL</td>
                                <td style="color:var(--color-text-muted); font-size:0.82rem;"><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td><span class="order-status delivered">✓ Teslim Edildi</span></td>
                                <td><a href="order_success.php?order_id=<?php echo $order['id']; ?>" class="order-detail-btn">Detay</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB: WISHLIST -->
        <div class="profile-tab-content <?php echo $active_tab==='wishlist'?'active':''; ?>" id="tab-wishlist">
            <h2 style="font-size:1.25rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;margin-bottom:28px;">İstek Listem</h2>

            <?php if (empty($wishlist_items)): ?>
                <div class="empty-state">
                    <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <h3>İstek listeniz boş.</h3>
                    <p>Beğendiğiniz ürünlerin üzerindeki kalp ikonuna tıklayarak kaydedin.</p>
                    <a href="index.php" class="btn-primary">ÜRÜNLERİ KEŞFET</a>
                </div>
            <?php else: ?>
                <div class="wishlist-grid">
                    <?php foreach ($wishlist_items as $witem): ?>
                    <div class="wishlist-card" id="wl-card-<?php echo $witem['id']; ?>">
                        <a href="product.php?id=<?php echo $witem['id']; ?>">
                            <img class="wishlist-card-img" src="<?php echo htmlspecialchars($witem['image_url']); ?>" alt="<?php echo htmlspecialchars($witem['name']); ?>" loading="lazy">
                        </a>
                        <div class="wishlist-card-body">
                            <span class="wishlist-card-brand"><?php echo htmlspecialchars($witem['brand']); ?></span>
                            <span class="wishlist-card-name"><?php echo htmlspecialchars($witem['name']); ?></span>
                            <span class="wishlist-card-price"><?php echo number_format($witem['price'], 2, ',', '.'); ?> TL</span>
                            <div class="wishlist-card-actions">
                                <a href="product.php?id=<?php echo $witem['id']; ?>" class="wl-btn wl-btn-primary">Ürüne Git</a>
                                <button class="wl-btn wl-btn-secondary" onclick="removeFromWishlist(<?php echo $witem['id']; ?>)">Kaldır</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB: SETTINGS -->
        <div class="profile-tab-content <?php echo $active_tab==='settings'?'active':''; ?>" id="tab-settings">
            <h2 style="font-size:1.25rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;margin-bottom:28px;">Hesap Ayarları</h2>

            <?php if ($success_msg): ?>
                <div class="alert-success">✓ <?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert-error">✕ <?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <form method="POST" action="profile.php?tab=settings">
                <input type="hidden" name="action" value="update_profile">
                <div class="profile-form-grid">
                    <div class="form-group">
                        <label for="profile-name">Ad Soyad</label>
                        <input type="text" id="profile-name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="profile-email">E-posta Adresi</label>
                        <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                    </div>
                    <hr class="form-divider">
                    <p class="form-note">Şifrenizi değiştirmek istiyorsanız aşağıdaki alanları doldurun. Değiştirmek istemiyorsanız boş bırakın.</p>
                    <div class="form-group">
                        <label for="current_password">Mevcut Şifre</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Mevcut şifreniz">
                    </div>
                    <div class="form-group">
                        <label for="new_password">Yeni Şifre</label>
                        <input type="password" id="new_password" name="password" placeholder="En az 6 karakter">
                    </div>
                    <div class="full-width" style="margin-top:8px;">
                        <button type="submit" class="btn-primary">KAYDET</button>
                        <a href="auth_action.php?action=logout" onclick="event.preventDefault(); fetch('auth_action.php?action=logout',{method:'POST'}).then(()=>window.location='index.php')" style="display:inline-block;margin-left:16px;font-size:0.78rem;font-weight:700;letter-spacing:1px;color:var(--color-text-muted);text-transform:uppercase;text-decoration:underline;">Çıkış Yap</a>
                    </div>
                </div>
            </form>
        </div>

    </main>

    <!-- FOOTER PLACEHOLDER -->
    <footer style="background:#000; border-top:1px solid var(--color-gray-border); padding:40px 0; text-align:center; color:var(--color-text-muted); font-size:0.78rem; letter-spacing:1px; margin-top:80px;">
        <p>© 2025 MODA AREN. Tüm hakları saklıdır.</p>
    </footer>

    <script src="script.js"></script>
    <script>
    // Tab switching
    function switchTab(tab) {
        document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.profile-tab-content').forEach(c => c.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
        history.replaceState(null, '', '?tab=' + tab);
    }

    // Remove from wishlist
    function removeFromWishlist(productId) {
        const card = document.getElementById('wl-card-' + productId);
        if (!card) return;
        const fd = new FormData();
        fd.append('product_id', productId);
        fetch('wishlist_action.php?action=toggle', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    card.style.transition = 'all 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => card.remove(), 300);
                }
            })
            .catch(() => card.remove());
    }
    </script>
</body>
</html>
