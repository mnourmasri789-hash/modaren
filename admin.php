<?php
// Include database connection (which starts session)
require_once 'db_connect.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    header('Location: admin.php');
    exit;
}

$login_error = '';

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($email) || empty($password)) {
        $login_error = 'Lütfen tüm alanları doldurunuz.';
    } else {
        $admin_user = null;
        
        // Try to verify in database
        if ($db_connected && $conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $admin_user = $user;
                }
            } catch (PDOException $e) {
                // Ignore and fall back to hardcoded check
            }
        }
        
        // Fallback for offline mode or initial install
        if (!$admin_user && $email === 'admin@modaren.com' && $password === 'password123') {
            $admin_user = [
                'id' => 1,
                'name' => 'MODA AREN Admin',
                'role' => 'admin'
            ];
        }
        
        if ($admin_user) {
            $_SESSION['user_id'] = $admin_user['id'];
            $_SESSION['user_name'] = $admin_user['name'];
            $_SESSION['user_role'] = 'admin';
            header('Location: admin.php');
            exit;
        } else {
            $login_error = 'Geçersiz e-posta adresi veya şifre.';
        }
    }
}

// Security Check: Block non-admins from dashboard
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Page Routing inside Dashboard
$tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'products';
$msg = isset($_SESSION['admin_msg']) ? $_SESSION['admin_msg'] : '';
$msg_type = isset($_SESSION['admin_msg_type']) ? $_SESSION['admin_msg_type'] : 'success';
unset($_SESSION['admin_msg']);
unset($_SESSION['admin_msg_type']);

// Handle CRUD Operations if logged in
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action'])) {
    $crud_action = $_POST['crud_action'];
    
    if ($crud_action === 'add' || $crud_action === 'edit') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 1;
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
        $original_price = isset($_POST['original_price']) && $_POST['original_price'] !== '' ? (float)$_POST['original_price'] : null;
        $sizes = isset($_POST['sizes']) ? trim($_POST['sizes']) : 'Standart';
        $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
        $hover_image_url = isset($_POST['hover_image_url']) ? trim($_POST['hover_image_url']) : '';
        $is_gold = isset($_POST['is_gold']) ? 1 : 0;
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        if (empty($name) || empty($brand) || $price <= 0 || empty($image_url)) {
            $_SESSION['admin_msg'] = "Lütfen zorunlu alanları (Ad, Marka, Fiyat, Görsel) doldurunuz.";
            $_SESSION['admin_msg_type'] = "error";
            header("Location: admin.php?tab=" . ($crud_action === 'add' ? 'add_product' : 'edit_product&id=' . $_POST['product_id']));
            exit;
        }
        
        if ($db_connected && $conn) {
            try {
                if ($crud_action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO products (name, brand, category_id, price, original_price, sizes, image_url, hover_image_url, is_gold, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $brand, $category_id, $price, $original_price, $sizes, $image_url, $hover_image_url, $is_gold, $description]);
                    $_SESSION['admin_msg'] = "Ürün başarıyla eklendi.";
                } else {
                    $product_id = (int)$_POST['product_id'];
                    $stmt = $conn->prepare("UPDATE products SET name = ?, brand = ?, category_id = ?, price = ?, original_price = ?, sizes = ?, image_url = ?, hover_image_url = ?, is_gold = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $brand, $category_id, $price, $original_price, $sizes, $image_url, $hover_image_url, $is_gold, $description, $product_id]);
                    $_SESSION['admin_msg'] = "Ürün başarıyla güncellendi.";
                }
                $_SESSION['admin_msg_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['admin_msg'] = "İşlem sırasında hata oluştu: " . $e->getMessage();
                $_SESSION['admin_msg_type'] = "error";
            }
        } else {
            $_SESSION['admin_msg'] = "Veritabanı bağlantısı yok. CRUD simüle edilmiştir.";
            $_SESSION['admin_msg_type'] = "error";
        }
        header("Location: admin.php?tab=products");
        exit;
    }
}

// Handle Delete product request
if ($is_admin && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    
    if ($db_connected && $conn) {
        try {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$delete_id]);
            $_SESSION['admin_msg'] = "Ürün başarıyla silindi.";
            $_SESSION['admin_msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['admin_msg'] = "Ürün silinemedi: " . $e->getMessage();
            $_SESSION['admin_msg_type'] = "error";
        }
    } else {
        $_SESSION['admin_msg'] = "Veritabanı bağlantısı yok. Silme işlemi simüle edilmiştir.";
        $_SESSION['admin_msg_type'] = "error";
    }
    header("Location: admin.php?tab=products");
    exit;
}

// Retrieve data for rendering dashboard
$products_list = [];
$orders_list = [];
$categories_list = [
    1 => 'BAYAN',
    2 => 'BAY',
    3 => 'KIDS',
    4 => 'SPORTS'
];

if ($is_admin) {
    if ($db_connected && $conn) {
        try {
            // Get products
            $p_stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
            $p_stmt->execute();
            $products_list = $p_stmt->fetchAll();
            
            // Get orders
            $o_stmt = $conn->prepare("SELECT * FROM orders ORDER BY id DESC");
            $o_stmt->execute();
            $orders_list = $o_stmt->fetchAll();
        } catch (PDOException $e) {
            // connection failed or table missing
        }
    }
    
    // Server-side mock products fallback if DB is offline
    if (empty($products_list)) {
        $products_list = [
            [
                'id' => 1,
                'category_id' => 4,
                'category_name' => 'SPORTS',
                'name' => 'MODA AREN Chronos Gold Runner',
                'brand' => 'MODA AREN GOLD',
                'price' => 3299.00,
                'original_price' => 3899.00,
                'sizes' => '39,40,41,42,43,44',
                'is_gold' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80',
                'description' => 'Aerodinamik koşu tasarımı.'
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'category_name' => 'BAYAN',
                'name' => 'Gold Stripe Kadın Antrenman Taytı',
                'brand' => 'MODA AREN ACTIVE',
                'price' => 1299.00,
                'original_price' => null,
                'sizes' => 'XS,S,M,L,XL',
                'is_gold' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=600&q=80',
                'description' => 'Yüksek belli tayt.'
            ]
        ];
    }
    
    // Server-side mock orders fallback if DB is offline
    if (empty($orders_list)) {
        $orders_list = [
            [
                'id' => 1024,
                'customer_name' => 'Ahmet Yılmaz',
                'customer_email' => 'ahmet@modaren.com',
                'shipping_address' => 'Barbaros Bulvarı No: 124 D: 6 Beşiktaş',
                'shipping_city' => 'İstanbul',
                'shipping_zip' => '34353',
                'total_price' => 3299.00,
                'payment_method' => 'Credit Card',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ]
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MODA AREN | Yönetim Paneli</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Admin dashboard custom overrides on top of style.css */
        .admin-dashboard {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: calc(100vh - 80px);
        }
        .admin-sidebar {
            background-color: var(--color-black);
            color: var(--color-white);
            padding: 40px 20px;
            border-right: 1px solid #222;
        }
        .admin-sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        .admin-menu-link {
            padding: 14px 20px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #888;
            border: 1px solid transparent;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-menu-link:hover, .admin-menu-link.active {
            color: var(--color-white);
            background-color: #111;
            border-color: var(--color-gold);
        }
        .admin-content {
            padding: 50px 40px;
            background-color: #fafafa;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--color-white);
            border: 1px solid var(--color-gray-border);
            margin-top: 20px;
        }
        .admin-table th, .admin-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid var(--color-gray-border);
            font-size: 0.9rem;
        }
        .admin-table th {
            background-color: var(--color-black);
            color: var(--color-white);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 2px;
        }
        .admin-table tr:hover td {
            background-color: var(--color-light-bg);
        }
        .admin-btn-action {
            padding: 8px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid var(--color-black);
            transition: var(--transition-fast);
            display: inline-block;
        }
        .admin-btn-action.edit {
            background-color: var(--color-white);
            color: var(--color-black);
        }
        .admin-btn-action.edit:hover {
            background-color: var(--color-black);
            color: var(--color-white);
        }
        .admin-btn-action.delete {
            background-color: #fff;
            color: red;
            border-color: red;
        }
        .admin-btn-action.delete:hover {
            background-color: red;
            color: #fff;
        }
        .admin-form-container {
            max-width: 800px;
            background-color: var(--color-white);
            border: 1px solid var(--color-black);
            padding: 40px;
            margin-top: 20px;
        }
        .admin-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        .admin-form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .admin-form-group.full-width {
            grid-column: 1 / -1;
        }
        .admin-form-group label {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .admin-form-group input, .admin-form-group select, .admin-form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--color-gray-border);
            font-size: 0.9rem;
            border-radius: 0;
            transition: var(--transition-fast);
        }
        .admin-form-group input:focus, .admin-form-group select:focus, .admin-form-group textarea:focus {
            border-color: var(--color-black);
        }
        .admin-msg-box {
            padding: 18px 20px;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            border-left: 5px solid;
        }
        .admin-msg-box.success {
            background-color: #f0fdf4;
            color: #166534;
            border-color: #166534;
        }
        .admin-msg-box.error {
            background-color: #fdf2f2;
            color: #9b1c1c;
            border-color: #9b1c1c;
        }
        .order-detail-card {
            background-color: var(--color-light-bg);
            border: 1px dashed var(--color-gold);
            padding: 25px;
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR HEADER -->
    <header class="header">
        <div class="container">
            <div class="nav-left">
                <a href="index.php" class="nav-link-item">← MAĞAZAYA GİT</a>
            </div>
            <div class="nav-center">
                <a href="admin.php" class="logo-link">
                    <span>MODA AREN ADMIN</span>
                </a>
            </div>
            <div class="nav-right">
                <?php if ($is_admin): ?>
                    <span style="font-size: 0.75rem; font-weight:700; letter-spacing: 1px; color: var(--color-gold);">
                        [<?php echo htmlspecialchars($_SESSION['user_name']); ?>]
                    </span>
                    <a href="admin.php?action=logout" class="nav-link-item" style="margin-left: 15px;">ÇIKIŞ</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if (!$is_admin): ?>
        <!-- LOGIN FORM FOR ADMIN -->
        <main class="container section-padding" style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
            <div class="admin-form-container" style="width: 450px; padding: 50px 40px; border: 1px solid var(--color-black);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 3px; color: var(--color-gold); text-transform: uppercase;">MODA AREN</span>
                    <h1 style="font-size: 1.85rem; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; margin-top: 8px;">ADMİN GİRİŞİ</h1>
                </div>
                
                <?php if (!empty($login_error)): ?>
                    <div style="background-color: #fdf2f2; border: 1px solid red; color: red; padding: 12px 15px; margin-bottom: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; text-align: center;">
                        ✕ <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>
                
                <form action="admin.php" method="POST" class="profile-form">
                    <div class="form-group">
                        <label class="form-label">E-POSTA ADRESİ</label>
                        <input type="email" name="email" class="form-input" value="admin@modaren.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ŞİFRE</label>
                        <input type="password" name="password" class="form-input" value="password123" required>
                    </div>
                    <button type="submit" name="login_submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px;">YÖNETİME BAĞLAN</button>
                </form>
                
                <div style="text-align: center; font-size:0.75rem; color: var(--color-text-muted); margin-top: 25px; line-height: 1.5;">
                    * Veritabanı çevrimdışı durumunda local failover <br>
                    <strong>admin@modaren.com / password123</strong> ile giriş yapılabilir.
                </div>
            </div>
        </main>
    <?php else: ?>
        <!-- ADMIN DASHBOARD PANEL -->
        <main class="admin-dashboard">
            <!-- Sidebar Navigation -->
            <aside class="admin-sidebar">
                <span style="font-size: 0.65rem; font-weight:800; letter-spacing: 2px; color: var(--color-gold); text-transform: uppercase;">GÖSTERGE PANELİ</span>
                <div class="admin-sidebar-menu">
                    <a href="admin.php?tab=products" class="admin-menu-link <?php echo $tab === 'products' ? 'active' : ''; ?>">
                        ■ Ürün Yönetimi
                    </a>
                    <a href="admin.php?tab=add_product" class="admin-menu-link <?php echo $tab === 'add_product' ? 'active' : ''; ?>">
                        + Yeni Ürün Ekle
                    </a>
                    <a href="admin.php?tab=orders" class="admin-menu-link <?php echo $tab === 'orders' ? 'active' : ''; ?>">
                        ■ Müşteri Siparişleri
                    </a>
                    <a href="admin.php?action=logout" class="admin-menu-link">
                        ✕ Güvenli Çıkış
                    </a>
                </div>
            </aside>

            <!-- Main Work Content Area -->
            <section class="admin-content">
                <?php if (!empty($msg)): ?>
                    <div class="admin-msg-box <?php echo $msg_type; ?>">
                        <?php echo $msg_type === 'success' ? '✓' : '✕'; ?> <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>

                <!-- TAB 1: PRODUCTS LIST -->
                <?php if ($tab === 'products'): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="font-size: 1.75rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">ÜRÜNLER LİSTESİ</h2>
                        <a href="admin.php?tab=add_product" class="btn-primary" style="padding: 10px 20px; font-size: 0.8rem;">+ YENİ ÜRÜN EKLE</a>
                    </div>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>GÖRSEL</th>
                                <th>MARKA / ÜRÜN ADI</th>
                                <th>KATEGORİ</th>
                                <th>FİYAT</th>
                                <th>BEDENLER</th>
                                <th>DURUM</th>
                                <th>İŞLEMLER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products_list as $prod): ?>
                                <tr>
                                    <td style="width: 80px;">
                                        <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="" style="width: 60px; height: 75px; object-fit: cover; border: 1px solid var(--color-gray-border);">
                                    </td>
                                    <td>
                                        <strong style="color: var(--color-gold-dark); font-size: 0.75rem; display:block; letter-spacing: 1px;"><?php echo htmlspecialchars($prod['brand']); ?></strong>
                                        <span style="font-weight: 700; font-size: 0.95rem;"><?php echo htmlspecialchars($prod['name']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($prod['category_name'] ?? ($categories_list[$prod['category_id']] ?? 'DIĞER')); ?></td>
                                    <td>
                                        <strong><?php echo number_format($prod['price'], 2, ',', '.'); ?> TL</strong>
                                        <?php if (!empty($prod['original_price'])): ?>
                                            <div style="text-decoration: line-through; font-size:0.75rem; color: var(--color-text-muted);"><?php echo number_format($prod['original_price'], 2, ',', '.'); ?> TL</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.8rem;"><?php echo htmlspecialchars($prod['sizes']); ?></td>
                                    <td>
                                        <?php if (isset($prod['is_gold']) && $prod['is_gold']): ?>
                                            <span style="background-color: var(--color-gold-light); color: var(--color-gold-dark); font-weight:800; font-size:0.65rem; padding: 4px 8px; letter-spacing:1px; border: 1px solid var(--color-gold);">GOLD</span>
                                        <?php else: ?>
                                            <span style="font-size:0.75rem; color: var(--color-text-muted);">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 10px;">
                                            <a href="admin.php?tab=edit_product&id=<?php echo $prod['id']; ?>" class="admin-btn-action edit">DÜZENLE</a>
                                            <a href="admin.php?action=delete&id=<?php echo $prod['id']; ?>" class="admin-btn-action delete" onclick="return confirm('Bu ürünü kalıcı olarak silmek istediğinize emin misiniz?');">SİL</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <!-- TAB 2 & 3: ADD / EDIT PRODUCT FORM -->
                <?php elseif ($tab === 'add_product' || $tab === 'edit_product'): ?>
                    <?php 
                    $edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                    $fields = [
                        'name' => '', 'brand' => '', 'category_id' => 1, 'price' => '', 
                        'original_price' => '', 'sizes' => 'S,M,L,XL', 'image_url' => '', 
                        'hover_image_url' => '', 'is_gold' => 0, 'description' => ''
                    ];
                    
                    if ($tab === 'edit_product' && $edit_id > 0) {
                        if ($db_connected && $conn) {
                            try {
                                $edit_stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                $edit_stmt->execute([$edit_id]);
                                $fetched = $edit_stmt->fetch();
                                if ($fetched) {
                                    $fields = $fetched;
                                }
                            } catch (PDOException $e) {}
                        } else {
                            // Offline edit simulation lookup
                            foreach ($products_list as $prod) {
                                if ($prod['id'] === $edit_id) {
                                    $fields = $prod;
                                    break;
                                }
                            }
                        }
                    }
                    ?>
                    
                    <h2 style="font-size: 1.75rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">
                        <?php echo $tab === 'add_product' ? 'YENİ ÜRÜN EKLE' : 'ÜRÜNÜ DÜZENLE (ID: #' . $edit_id . ')'; ?>
                    </h2>
                    
                    <div class="admin-form-container">
                        <form action="admin.php" method="POST">
                            <input type="hidden" name="crud_action" value="<?php echo $tab === 'add_product' ? 'add' : 'edit'; ?>">
                            <input type="hidden" name="product_id" value="<?php echo $edit_id; ?>">
                            
                            <div class="admin-form-grid">
                                <div class="admin-form-group">
                                    <label>ÜRÜN ADI *</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($fields['name']); ?>" required>
                                </div>
                                <div class="admin-form-group">
                                    <label>MARKA / KOLEKSİYON *</label>
                                    <input type="text" name="brand" value="<?php echo htmlspecialchars($fields['brand']); ?>" required>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label>KATEGORİ *</label>
                                    <select name="category_id">
                                        <?php foreach ($categories_list as $c_id => $c_name): ?>
                                            <option value="<?php echo $c_id; ?>" <?php echo $fields['category_id'] == $c_id ? 'selected' : ''; ?>>
                                                <?php echo $c_name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="admin-form-group">
                                    <label>BEDENLER (Virgülle Ayrılmış) *</label>
                                    <input type="text" name="sizes" value="<?php echo htmlspecialchars($fields['sizes']); ?>" required>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label>SATIŞ FİYATI (TL) *</label>
                                    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($fields['price']); ?>" required>
                                </div>
                                <div class="admin-form-group">
                                    <label>İNDİRİMSİZ ORİJİNAL FİYAT (TL - İsteğe Bağlı)</label>
                                    <input type="number" step="0.01" name="original_price" value="<?php echo htmlspecialchars($fields['original_price']); ?>">
                                </div>
                                
                                <div class="admin-form-group">
                                    <label>BİRİNCİL GÖRSEL URL *</label>
                                    <input type="url" name="image_url" value="<?php echo htmlspecialchars($fields['image_url']); ?>" required>
                                </div>
                                <div class="admin-form-group">
                                    <label>HOVER GÖRSEL URL (İsteğe Bağlı)</label>
                                    <input type="url" name="hover_image_url" value="<?php echo htmlspecialchars($fields['hover_image_url']); ?>">
                                </div>
                                
                                <div class="admin-form-group full-width" style="flex-direction: row; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="is_gold" id="is_gold" style="width: auto;" <?php echo $fields['is_gold'] ? 'checked' : ''; ?>>
                                    <label for="is_gold" style="cursor: pointer; font-weight:800; color: var(--color-gold-dark);">GOLD EDITION SÜRÜMÜ (ÖZEL ALTIN ETİKET VE PARLAK DETAYLAR)</label>
                                </div>
                                
                                <div class="admin-form-group full-width">
                                    <label>DETAYLI AÇIKLAMA</label>
                                    <textarea name="description" rows="5" required><?php echo htmlspecialchars($fields['description']); ?></textarea>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 15px;">
                                <button type="submit" class="btn-primary" style="padding: 15px 30px;">KAYDET</button>
                                <a href="admin.php?tab=products" class="btn-secondary" style="padding: 15px 30px;">İPTAL</a>
                            </div>
                        </form>
                    </div>

                <!-- TAB 4: ORDERS LIST -->
                <?php elseif ($tab === 'orders'): ?>
                    <h2 style="font-size: 1.75rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px;">MÜŞTERİ SİPARİŞLERİ</h2>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>SİPARİŞ NO</th>
                                <th>MÜŞTERİ DETAYI</th>
                                <th>TESLİMAT ŞEHRİ</th>
                                <th>ÖDENEN TUTA</th>
                                <th>ÖDEME YÖNTEMİ</th>
                                <th>TARİH</th>
                                <th>İŞLEM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders_list as $ord): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($ord['id']); ?></strong></td>
                                    <td>
                                        <div style="font-weight: 700;"><?php echo htmlspecialchars($ord['customer_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?php echo htmlspecialchars($ord['customer_email']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($ord['shipping_city']); ?></td>
                                    <td class="text-gold"><strong><?php echo number_format($ord['total_price'], 2, ',', '.'); ?> TL</strong></td>
                                    <td><?php echo htmlspecialchars($ord['payment_method']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($ord['created_at'])); ?></td>
                                    <td>
                                        <a href="admin.php?tab=orders&view_order=<?php echo $ord['id']; ?>" class="admin-btn-action edit">İÇERİĞİ GÖR</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Single Order Detail Panel -->
                    <?php 
                    $view_order_id = isset($_GET['view_order']) ? (int)$_GET['view_order'] : 0;
                    if ($view_order_id > 0):
                        $order_details = null;
                        $items_bought = [];
                        
                        if ($db_connected && $conn) {
                            try {
                                $o_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
                                $o_stmt->execute([$view_order_id]);
                                $order_details = $o_stmt->fetch();
                                
                                if ($order_details) {
                                    $i_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.brand as product_brand FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                    $i_stmt->execute([$view_order_id]);
                                    $items_bought = $i_stmt->fetchAll();
                                }
                            } catch (PDOException $e) {}
                        } else {
                            // Offline order detail simulation
                            if ($orders_list[0]['id'] == $view_order_id) {
                                $order_details = $orders_list[0];
                                $items_bought = [
                                    [
                                        'product_name' => 'MODA AREN Chronos Gold Runner',
                                        'product_brand' => 'MODA AREN GOLD',
                                        'size' => '42',
                                        'quantity' => 1,
                                        'price' => 3299.00
                                    ]
                                ];
                            }
                        }
                        
                        if ($order_details):
                        ?>
                            <div class="order-detail-card">
                                <h3 style="font-size:1.15rem; font-weight:800; letter-spacing:1px; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed var(--color-black); padding-bottom:8px;">
                                    SİPARİŞ DETAYI (NO: #<?php echo $view_order_id; ?>)
                                </h3>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 20px; font-size: 0.85rem;">
                                    <div>
                                        <strong>MÜŞTERİ BİLGİLERİ:</strong><br>
                                        Ad Soyad: <?php echo htmlspecialchars($order_details['customer_name']); ?><br>
                                        E-posta: <?php echo htmlspecialchars($order_details['customer_email']); ?>
                                    </div>
                                    <div>
                                        <strong>TESLİMAT ADRESİ:</strong><br>
                                        <?php echo htmlspecialchars($order_details['shipping_address']); ?><br>
                                        <?php echo htmlspecialchars($order_details['shipping_zip']); ?> / <?php echo htmlspecialchars($order_details['shipping_city']); ?>
                                    </div>
                                </div>
                                
                                <strong>SATIN ALINAN ÜRÜNLER:</strong>
                                <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background-color: #eee; text-align: left;">
                                            <th style="padding: 8px; color: #000; font-size: 0.7rem;">ÜRÜN</th>
                                            <th style="padding: 8px; color: #000; font-size: 0.7rem;">BEDEN</th>
                                            <th style="padding: 8px; color: #000; font-size: 0.7rem;">ADET</th>
                                            <th style="padding: 8px; color: #000; font-size: 0.7rem;">BİRİM FİYAT</th>
                                            <th style="padding: 8px; color: #000; font-size: 0.7rem;">ARA TOPLAM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items_bought as $item): ?>
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 8px;">
                                                    <strong>[<?php echo htmlspecialchars($item['product_brand']); ?>]</strong> 
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </td>
                                                <td style="padding: 8px;"><?php echo htmlspecialchars($item['size']); ?></td>
                                                <td style="padding: 8px;"><?php echo $item['quantity']; ?></td>
                                                <td style="padding: 8px;"><?php echo number_format($item['price'], 2, ',', '.'); ?> TL</td>
                                                <td style="padding: 8px; font-weight:700;"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?> TL</td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="font-weight: 800; font-size: 0.95rem;">
                                            <td colspan="4" style="padding: 8px; text-align: right;">SİPARİŞ TOPLAMI:</td>
                                            <td style="padding: 8px; color: var(--color-gold-dark);"><?php echo number_format($order_details['total_price'], 2, ',', '.'); ?> TL</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </main>
    <?php endif; ?>

</body>
</html>
