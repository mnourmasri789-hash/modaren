<?php
require_once 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// Helper to get wishlist for current session user
function getWishlistItems($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT w.product_id, p.name, p.brand, p.price, p.image_url
            FROM wishlist w
            JOIN products p ON p.id = w.product_id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Require login
if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmanız gerekiyor.', 'requireLogin' => true]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

switch ($action) {
    case 'toggle':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if ($product_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün.']);
            exit;
        }

        if ($db_connected && $conn) {
            try {
                // Check if already wishlisted
                $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                $check->execute([$user_id, $product_id]);
                $existing = $check->fetch();

                if ($existing) {
                    // Remove from wishlist
                    $del = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                    $del->execute([$user_id, $product_id]);
                    echo json_encode(['status' => 'success', 'wishlisted' => false, 'message' => 'İstek listesinden çıkarıldı.']);
                } else {
                    // Add to wishlist
                    $ins = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                    $ins->execute([$user_id, $product_id]);
                    echo json_encode(['status' => 'success', 'wishlisted' => true, 'message' => 'İstek listesine eklendi!']);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $e->getMessage()]);
            }
        } else {
            // Offline fallback using session
            if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];
            if (in_array($product_id, $_SESSION['wishlist'])) {
                $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$product_id]));
                echo json_encode(['status' => 'success', 'wishlisted' => false, 'message' => 'İstek listesinden çıkarıldı.']);
            } else {
                $_SESSION['wishlist'][] = $product_id;
                echo json_encode(['status' => 'success', 'wishlisted' => true, 'message' => 'İstek listesine eklendi!']);
            }
        }
        break;

    case 'get':
        if ($db_connected && $conn) {
            $items = getWishlistItems($conn, $user_id);
            echo json_encode(['status' => 'success', 'items' => $items]);
        } else {
            $ids = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];
            echo json_encode(['status' => 'success', 'items' => [], 'ids' => $ids]);
        }
        break;

    case 'get_ids':
        if ($db_connected && $conn) {
            try {
                $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['status' => 'success', 'ids' => $ids]);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'success', 'ids' => []]);
            }
        } else {
            $ids = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];
            echo json_encode(['status' => 'success', 'ids' => $ids]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Bilinmeyen işlem.']);
        break;
}
?>
