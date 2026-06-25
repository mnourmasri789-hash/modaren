<?php
// Include database connection (which starts session)
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// Server-side mock products array for database-offline fallback
$PHP_MOCK_PRODUCTS = [
    1 => ['name' => 'MODA AREN Chronos Gold Runner', 'brand' => 'MODA AREN GOLD', 'price' => 3299.00, 'image_url' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80'],
    2 => ['name' => 'Gold Stripe Kadın Antrenman Taytı', 'brand' => 'MODA AREN ACTIVE', 'price' => 1299.00, 'image_url' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=600&q=80'],
    3 => ['name' => 'Performance Pamuklu Kapüşonlu Sweatshirt', 'brand' => 'MODA AREN CLIMA', 'price' => 1899.00, 'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=600&q=80'],
    4 => ['name' => 'Primeknit Metalic Gold Krampon', 'brand' => 'MODA AREN PRO', 'price' => 4199.00, 'image_url' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80'],
    5 => ['name' => 'Genç Çocuk Eşofman Takımı İkili', 'brand' => 'MODA AREN KIDS', 'price' => 1499.00, 'image_url' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80'],
    6 => ['name' => 'Gold Reflection Su Geçirmez Rüzgarlık', 'brand' => 'MODA AREN OUTDOOR', 'price' => 2899.00, 'image_url' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=600&q=80'],
    7 => ['name' => 'Fitilli Kadın Crop Antrenman Üstü', 'brand' => 'MODA AREN ACTIVE', 'price' => 899.00, 'image_url' => 'https://images.unsplash.com/photo-1518622358385-8ea7d0794bf6?auto=format&fit=crop&w=600&q=80'],
    8 => ['name' => 'Premium Gold Metal Fermuarlı Spor Çantası', 'brand' => 'MODA AREN ACCESSORIES', 'price' => 1599.00, 'image_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80']
];

// Initialize cart session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Helper to get total price & badge count
function getCartTotals() {
    $subtotal = 0;
    $badge_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $badge_count += $item['quantity'];
    }
    
    // Shipping: free above 1500 TL, else 99 TL
    $shipping = ($subtotal > 1500 || $subtotal == 0) ? 0 : 99;
    $total = $subtotal + $shipping;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $total,
        'badge_count' => $badge_count
    ];
}

switch ($action) {
    case 'get':
        $totals = getCartTotals();
        echo json_encode([
            'status' => 'success',
            'cart' => array_values($_SESSION['cart']),
            'totals' => $totals,
            'badge_count' => $totals['badge_count']
        ]);
        break;
        
    case 'add':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $size = isset($_POST['size']) ? trim($_POST['size']) : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($product_id <= 0 || empty($size)) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün veya beden bilgisi.']);
            exit;
        }
        
        // Fetch product information safely
        $product_info = null;
        if ($db_connected && $conn) {
            try {
                $stmt = $conn->prepare("SELECT name, brand, price, image_url FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product_info = $stmt->fetch();
            } catch (PDOException $e) {
                $product_info = null;
            }
        }
        
        // Fallback to PHP mock if product not found in database
        if (!$product_info && isset($PHP_MOCK_PRODUCTS[$product_id])) {
            $product_info = $PHP_MOCK_PRODUCTS[$product_id];
        }
        
        if (!$product_info) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün bulunamadı.']);
            exit;
        }
        
        $cart_key = $product_id . '_' . $size;
        
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'product_id' => $product_id,
                'name' => $product_info['name'],
                'brand' => $product_info['brand'],
                'price' => (float)$product_info['price'],
                'image_url' => $product_info['image_url'],
                'size' => $size,
                'quantity' => $quantity
            ];
        }
        
        $totals = getCartTotals();
        echo json_encode([
            'status' => 'success',
            'message' => 'Ürün sepete eklendi.',
            'badge_count' => $totals['badge_count']
        ]);
        break;
        
    case 'update':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $size = isset($_POST['size']) ? trim($_POST['size']) : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        $cart_key = $product_id . '_' . $size;
        
        if (isset($_SESSION['cart'][$cart_key])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$cart_key]);
            } else {
                $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
            }
            
            $totals = getCartTotals();
            echo json_encode([
                'status' => 'success',
                'badge_count' => $totals['badge_count']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ürün sepette bulunamadı.']);
        }
        break;
        
    case 'remove':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $size = isset($_POST['size']) ? trim($_POST['size']) : '';
        
        $cart_key = $product_id . '_' . $size;
        
        if (isset($_SESSION['cart'][$cart_key])) {
            unset($_SESSION['cart'][$cart_key]);
            $totals = getCartTotals();
            echo json_encode([
                'status' => 'success',
                'badge_count' => $totals['badge_count']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ürün sepette bulunamadı.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Bilinmeyen işlem.']);
        break;
}
?>
