<?php
// Include database connection (which starts session)
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek yöntemi.']);
    exit;
}

switch ($action) {
    case 'register':
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurunuz.']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen geçerli bir e-posta adresi giriniz.']);
            exit;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Şifre en az 6 karakterden oluşmalıdır.']);
            exit;
        }
        
        if ($db_connected && $conn) {
            try {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    echo json_encode(['status' => 'error', 'message' => 'Bu e-posta adresi zaten kayıtlı.']);
                    exit;
                }
                
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert new user
                $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                $insert_stmt->execute([$name, $email, $hashed_password]);
                $user_id = $conn->lastInsertId();
                
                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
                
                echo json_encode(['status' => 'success', 'message' => 'Kayıt ve giriş başarılı!']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında hata oluştu: ' . $e->getMessage()]);
            }
        } else {
            // Offline/Mock Mode Fallback Registration
            $user_id = rand(1000, 9999);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            
            echo json_encode(['status' => 'success', 'message' => 'Kayıt başarılı (Çevrimdışı Mod)!']);
        }
        break;
        
    case 'login':
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurunuz.']);
            exit;
        }
        
        $user = null;
        
        if ($db_connected && $conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                // Ignore and degrade
            }
        }
        
        if ($user) {
            // Verify securely hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                echo json_encode(['status' => 'success', 'message' => 'Giriş başarılı!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Hatalı şifre girdiniz.']);
            }
        } else {
            // Offline/Mock Fallback Authentication
            // admin@modaren.com / password123
            // ahmet@modaren.com / password123
            if ($email === 'admin@modaren.com' && $password === 'password123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['user_name'] = 'MODA AREN Admin';
                $_SESSION['user_role'] = 'admin';
                echo json_encode(['status' => 'success', 'message' => 'Giriş başarılı (Yönetici, Çevrimdışı)!']);
            } elseif ($email === 'ahmet@modaren.com' && $password === 'password123') {
                $_SESSION['user_id'] = 2;
                $_SESSION['user_name'] = 'Ahmet Yılmaz';
                $_SESSION['user_role'] = 'user';
                echo json_encode(['status' => 'success', 'message' => 'Giriş başarılı (Çevrimdışı)!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı veya hatalı şifre.']);
            }
        }
        break;
        
    case 'logout':
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        echo json_encode(['status' => 'success', 'message' => 'Çıkış yapıldı.']);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Bilinmeyen işlem.']);
        break;
}
?>
