<?php
require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
    exit;
}

$code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
$subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;

if (empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Lütfen bir promosyon kodu giriniz.']);
    exit;
}

$discount_amount = 0;
$discount_type = '';
$discount_value = 0;

if ($db_connected && $conn) {
    try {
        $stmt = $conn->prepare("
            SELECT * FROM promo_codes
            WHERE code = ? AND is_active = 1
            AND (expires_at IS NULL OR expires_at > NOW())
            AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stmt->execute([$code]);
        $promo = $stmt->fetch();

        if ($promo) {
            $discount_type = $promo['discount_type'];
            $discount_value = (float)$promo['discount_value'];
            $min_order = (float)$promo['min_order_amount'];

            if ($subtotal < $min_order && $min_order > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bu kodu kullanmak için minimum sepet tutarı: ' . number_format($min_order, 2, ',', '.') . ' TL'
                ]);
                exit;
            }

            if ($discount_type === 'percentage') {
                $discount_amount = $subtotal * ($discount_value / 100);
            } elseif ($discount_type === 'fixed') {
                $discount_amount = min($discount_value, $subtotal);
            }

            echo json_encode([
                'status' => 'success',
                'message' => '🎉 Promosyon kodu uygulandı!',
                'discount_amount' => round($discount_amount, 2),
                'discount_type' => $discount_type,
                'discount_value' => $discount_value,
                'code' => $code
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz veya süresi dolmuş promosyon kodu.']);
        }
    } catch (PDOException $e) {
        // Fallback to hardcoded demo codes
        goto fallback;
    }
} else {
    fallback:
    // Offline demo promo codes
    $demo_codes = [
        'MODAREN10' => ['type' => 'percentage', 'value' => 10, 'min' => 0],
        'MODAREN20' => ['type' => 'percentage', 'value' => 20, 'min' => 2000],
        'GOLD500'   => ['type' => 'fixed',      'value' => 500, 'min' => 1500],
        'WELCOME'   => ['type' => 'percentage', 'value' => 15, 'min' => 0],
    ];

    if (isset($demo_codes[$code])) {
        $promo_data = $demo_codes[$code];
        if ($subtotal < $promo_data['min'] && $promo_data['min'] > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bu kodu kullanmak için minimum sepet tutarı: ' . number_format($promo_data['min'], 2, ',', '.') . ' TL'
            ]);
            exit;
        }
        if ($promo_data['type'] === 'percentage') {
            $discount_amount = $subtotal * ($promo_data['value'] / 100);
        } else {
            $discount_amount = min($promo_data['value'], $subtotal);
        }
        echo json_encode([
            'status' => 'success',
            'message' => '🎉 Promosyon kodu uygulandı!',
            'discount_amount' => round($discount_amount, 2),
            'discount_type' => $promo_data['type'],
            'discount_value' => $promo_data['value'],
            'code' => $code
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz veya süresi dolmuş promosyon kodu.']);
    }
}
?>
