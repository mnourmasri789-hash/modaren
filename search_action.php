<?php
require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['status' => 'ok', 'results' => []]);
    exit;
}

$results = [];

if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("
            SELECT id, name, brand, price, image_url
            FROM products
            WHERE name LIKE ? OR brand LIKE ?
            LIMIT 6
        ");
        $search_term = '%' . $query . '%';
        $stmt->execute([$search_term, $search_term]);
        $results = $stmt->fetchAll();
    } catch (PDOException $e) {
        $results = [];
    }
} else {
    // Mock data fallback for search
    $mock_products = [
        ['id'=>1,  'name'=>'MODA AREN Chronos Gold Runner',   'brand'=>'MODA AREN GOLD',    'price'=>3299.00, 'image_url'=>'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=100&q=60'],
        ['id'=>2,  'name'=>'Gold Stripe Kadın Antrenman Taytı','brand'=>'MODA AREN ACTIVE', 'price'=>1299.00, 'image_url'=>'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=100&q=60'],
        ['id'=>3,  'name'=>'Performance Kapüşonlu Sweatshirt', 'brand'=>'MODA AREN CLIMA',  'price'=>1899.00, 'image_url'=>'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=100&q=60'],
        ['id'=>8,  'name'=>'Premium Gold Fermuarlı Spor Çantası','brand'=>'MODA AREN ACCESSORIES','price'=>1599.00, 'image_url'=>'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=100&q=60'],
        ['id'=>11, 'name'=>'MODA AREN Ultraboost White',       'brand'=>'MODA AREN RUNNING', 'price'=>4899.00, 'image_url'=>'https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=100&q=60'],
        ['id'=>20, 'name'=>'Premium Altın Mat Çelik Matara',   'brand'=>'MODA AREN ACC',     'price'=>549.00,  'image_url'=>'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=100&q=60'],
    ];
    foreach ($mock_products as $p) {
        if (mb_stripos($p['name'], $query) !== false || mb_stripos($p['brand'], $query) !== false) {
            $results[] = $p;
        }
    }
}

echo json_encode(['status' => 'ok', 'results' => $results]);
?>
