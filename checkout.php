<?php
/**
 * MODA AREN — Güvenli Ödeme Sayfası
 * ParamPOS Sandbox Entegrasyonu (Simülasyon)
 *
 * ParamPOS Sandbox Kimlik Bilgileri (Dummy / Test):
 *   client_code : 10738
 *   guid        : 0c13d406-873b-403b-9581-40eb8e0a4def
 *   name        : MODA AREN TEST
 *   password    : MODAREN2025
 *
 * Gerçek entegrasyonda bu değerler güvenli config/env dosyasından okunmalıdır.
 */

require_once 'db_connect.php';

/* ── Redirect if cart is empty ── */
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

/* ── Compute totals ── */
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping         = $subtotal > 1500 ? 0 : 99;
$promo_code       = isset($_GET['promo'])    ? strtoupper(trim($_GET['promo'])) : '';
$discount_amount  = isset($_GET['discount']) ? (float)$_GET['discount']         : 0;
$discount_amount  = min($discount_amount, $subtotal);
$total            = max(0, $subtotal - $discount_amount) + $shipping;

/* ── ParamPOS Sandbox Configuration ── */
define('PARAMPOS_SANDBOX_URL', 'https://posws.param.com.tr/turkpos.asmx');
define('PARAMPOS_CLIENT_CODE', '10738');
define('PARAMPOS_GUID',        '0c13d406-873b-403b-9581-40eb8e0a4def');
define('PARAMPOS_NAME',        'MODA AREN TEST');
define('PARAMPOS_PASSWORD',    'MODAREN2025');

/* ────────────────────────────────────────────────────────────────────────
   ParamPOS Simülasyon Fonksiyonu
   ─────────────────────────────────────────────────────────────────────── */
function simulateParamPOS(array $cardData, float $amount): array
{
    /*
     * Gerçek API'de burası şu SOAP isteğini gönderir:
     *
     *   POST https://posws.param.com.tr/turkpos.asmx
     *   SOAPAction: "https://turkpos.com.tr/TP_WMD_Pay"
     *
     *   Gönderilen Parametreler:
     *     G.CLIENT_CODE, G.CLIENT_USERNAME, G.CLIENT_PASSWORD, G.GUID,
     *     KK_Sahibi, KK_No, KK_SK_Ay, KK_SK_Yil, KK_CVC,
     *     Hata_URL, Basarili_URL, Siparis_ID, Siparis_Aciklama,
     *     Taksit, Islem_Tutar, Toplam_Tutar, Doviz_Kodu,
     *     Referans_No, ...
     *
     * Biz burada cURL çağrısını hazırlıyor ancak sandbox olmadığı için
     * kart numarasına göre deterministik bir simülasyon yapıyoruz.
     */

    // Kart numarasını temizle
    $clean_no = str_replace([' ', '-'], '', $cardData['card_no']);
    $last4    = substr($clean_no, -4);

    // ── Simüle edilmiş cURL SOAP çağrısı (yapıyı görmek için) ──
    $soap_body = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <TP_WMD_UCD xmlns="https://turkpos.com.tr/">
      <G>
        <CLIENT_CODE>' . PARAMPOS_CLIENT_CODE . '</CLIENT_CODE>
        <CLIENT_USERNAME>' . PARAMPOS_NAME . '</CLIENT_USERNAME>
        <CLIENT_PASSWORD>' . PARAMPOS_PASSWORD . '</CLIENT_PASSWORD>
      </G>
      <GUID>' . PARAMPOS_GUID . '</GUID>
      <KK_Sahibi>' . htmlspecialchars($cardData['card_name']) . '</KK_Sahibi>
      <KK_No>' . $clean_no . '</KK_No>
      <KK_SK_Ay>' . $cardData['expiry_month'] . '</KK_SK_Ay>
      <KK_SK_Yil>' . $cardData['expiry_year'] . '</KK_SK_Yil>
      <KK_CVC>' . $cardData['cvv'] . '</KK_CVC>
      <Taksit>1</Taksit>
      <Islem_Tutar>' . number_format($amount, 2, '.', '') . '</Islem_Tutar>
      <Toplam_Tutar>' . number_format($amount, 2, '.', '') . '</Toplam_Tutar>
      <Doviz_Kodu>TL</Doviz_Kodu>
      <Siparis_ID>' . uniqid('MAREN-', true) . '</Siparis_ID>
      <Siparis_Aciklama>MODA AREN E-Ticaret Siparisi</Siparis_Aciklama>
    </TP_WMD_UCD>
  </soap:Body>
</soap:Envelope>';

    /*
     * Gerçek cURL bloğu (sandbox aktif olduğunda etkinleştirin):
     *
     * $ch = curl_init(PARAMPOS_SANDBOX_URL);
     * curl_setopt_array($ch, [
     *     CURLOPT_RETURNTRANSFER => true,
     *     CURLOPT_POST           => true,
     *     CURLOPT_POSTFIELDS     => $soap_body,
     *     CURLOPT_HTTPHEADER     => [
     *         'Content-Type: text/xml; charset=utf-8',
     *         'SOAPAction: "https://turkpos.com.tr/TP_WMD_UCD"',
     *         'Content-Length: ' . strlen($soap_body),
     *     ],
     *     CURLOPT_SSL_VERIFYPEER => false,
     *     CURLOPT_TIMEOUT        => 30,
     * ]);
     * $response = curl_exec($ch);
     * $curl_err = curl_error($ch);
     * curl_close($ch);
     *
     * // Parse XML response
     * // UCD_Sonuc = 1  → Başarılı
     * // UCD_Sonuc = 0  → Başarısız
     */

    /* ── Sandbox Simülasyon Kuralları ── */

    // Kural 1: "0000" ile biten kartlar ret edilir (hata simülasyonu)
    if ($last4 === '0000') {
        return [
            'success'       => false,
            'result_code'   => '-1',
            'message'       => 'ParamPOS: İşlem reddedildi. Kart limitiniz yetersiz veya kart bilgileri hatalı.',
            'transaction_id' => null,
        ];
    }

    // Kural 2: "9999" ile biten kartlar zaman aşımı simüle eder
    if ($last4 === '9999') {
        return [
            'success'       => false,
            'result_code'   => '-99',
            'message'       => 'ParamPOS: Ödeme ağ geçidi zaman aşımına uğradı. Lütfen tekrar deneyin.',
            'transaction_id' => null,
        ];
    }

    // Kural 3: Geçersiz CVV formatı
    if (!preg_match('/^\d{3,4}$/', $cardData['cvv'])) {
        return [
            'success'       => false,
            'result_code'   => '-3',
            'message'       => 'ParamPOS: CVV kodu geçersiz.',
            'transaction_id' => null,
        ];
    }

    // Kural 4: Geçmiş tarihli kart
    $exp_month = (int)$cardData['expiry_month'];
    $exp_year  = (int)('20' . $cardData['expiry_year']);
    $now_year  = (int)date('Y');
    $now_month = (int)date('n');
    if ($exp_year < $now_year || ($exp_year === $now_year && $exp_month < $now_month)) {
        return [
            'success'       => false,
            'result_code'   => '-5',
            'message'       => 'ParamPOS: Kartın son kullanma tarihi geçmiş.',
            'transaction_id' => null,
        ];
    }

    // Diğer tüm kartlar → Başarılı ödeme
    $transaction_id = 'PARAM-' . strtoupper(bin2hex(random_bytes(8)));
    return [
        'success'        => true,
        'result_code'    => '1',
        'message'        => 'İşlem başarılı.',
        'transaction_id' => $transaction_id,
    ];
}

/* ────────────────────────────────────────────────────────────────────────
   Form İşleme
   ─────────────────────────────────────────────────────────────────────── */
$errors         = [];
$payment_error  = '';
$payment_notice = '';  // İşleniyor animasyonu için (kullanılmıyor sunucu tarafında)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ── 1. Teslimat Bilgisi Doğrulama ── */
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city']    ?? '');
    $zip     = trim($_POST['zip']     ?? '');

    if (empty($name))                                          $errors[] = 'Ad Soyad alanı zorunludur.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    if (empty($address))                                       $errors[] = 'Teslimat adresi zorunludur.';
    if (empty($city))                                          $errors[] = 'Şehir seçimi zorunludur.';
    if (empty($zip))                                           $errors[] = 'Posta kodu zorunludur.';

    /* ── 2. Kart Bilgisi Doğrulama ── */
    $card_name  = strtoupper(trim($_POST['card_name']  ?? ''));
    $card_no    = trim($_POST['card_no']    ?? '');
    $card_date  = trim($_POST['card_date']  ?? '');   // AA/YY
    $card_cvv   = trim($_POST['card_cvv']   ?? '');

    $clean_card = str_replace([' ', '-'], '', $card_no);

    if (empty($card_name))                   $errors[] = 'Kart üzerindeki isim zorunludur.';
    if (strlen($clean_card) < 16 || !ctype_digit($clean_card)) $errors[] = 'Geçerli bir 16 haneli kart numarası giriniz.';

    // Ay/Yıl ayırma
    $expiry_parts  = explode('/', $card_date);
    $expiry_month  = isset($expiry_parts[0]) ? trim($expiry_parts[0]) : '';
    $expiry_year   = isset($expiry_parts[1]) ? trim($expiry_parts[1]) : '';
    if (!preg_match('/^\d{2}$/', $expiry_month) || !preg_match('/^\d{2}$/', $expiry_year)) {
        $errors[] = 'Son kullanma tarihini AA/YY formatında giriniz.';
    }
    if (!preg_match('/^\d{3,4}$/', $card_cvv)) $errors[] = 'Geçerli bir CVV kodu giriniz.';

    /* ── 3. Ödeme Simülasyonu (Validasyon geçtiyse) ── */
    if (empty($errors)) {

        $card_data = [
            'card_name'    => $card_name,
            'card_no'      => $clean_card,
            'expiry_month' => $expiry_month,
            'expiry_year'  => $expiry_year,
            'cvv'          => $card_cvv,
        ];

        $payment_result = simulateParamPOS($card_data, $total);

        if (!$payment_result['success']) {
            /* ── Ödeme Başarısız ── */
            $payment_error = $payment_result['message'];

        } else {
            /* ── Ödeme Başarılı → Sipariş Kaydet ── */
            $transaction_id = $payment_result['transaction_id'];
            $order_id       = 0;
            $user_id        = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            if ($db_connected && $conn) {
                try {
                    $conn->beginTransaction();

                    // Orders tablosuna transaction_id sütunu yoksa payment_method içine göm
                    $payment_method = 'ParamPOS | TX: ' . $transaction_id;

                    $stmt = $conn->prepare("
                        INSERT INTO orders
                          (user_id, customer_name, customer_email,
                           shipping_address, shipping_city, shipping_zip,
                           subtotal, discount_amount, promo_code,
                           shipping_fee, total_price, payment_method)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                    ");
                    $stmt->execute([
                        $user_id, $name, $email,
                        $address, $city, $zip,
                        $subtotal, $discount_amount, ($promo_code ?: null),
                        $shipping, $total, $payment_method
                    ]);
                    $order_id = $conn->lastInsertId();

                    $item_stmt = $conn->prepare("
                        INSERT INTO order_items (order_id, product_id, size, quantity, price)
                        VALUES (?,?,?,?,?)
                    ");
                    foreach ($cart_items as $item) {
                        $item_stmt->execute([
                            $order_id,
                            $item['product_id'],
                            $item['size'],
                            $item['quantity'],
                            $item['price'],
                        ]);
                    }

                    $conn->commit();

                } catch (PDOException $e) {
                    if ($conn->inTransaction()) $conn->rollBack();
                    $payment_error = 'Sipariş veritabanına kaydedilirken bir hata oluştu: ' . $e->getMessage();
                }

            } else {
                /* ── Çevrimdışı / Mock Mod ── */
                $order_id = rand(100000, 999999);
                $_SESSION['last_mock_order'] = [
                    'id'             => $order_id,
                    'customer_name'  => $name,
                    'customer_email' => $email,
                    'shipping_address' => $address,
                    'shipping_city'  => $city,
                    'shipping_zip'   => $zip,
                    'total_price'    => $total,
                    'items'          => $cart_items,
                    'transaction_id' => $transaction_id,
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
            }

            if (empty($payment_error)) {
                $_SESSION['cart'] = [];   // Sepeti temizle
                header('Location: order_success.php?id=' . $order_id . '&tx=' . urlencode($transaction_id));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güvenli Ödeme | MODA AREN × ParamPOS</title>
    <meta name="description" content="ParamPOS güvenli ödeme altyapısıyla siparişinizi tamamlayın.">
    <link rel="stylesheet" href="style.css">
    <style>
    /* ══════════════════════════════════════════════
       PARAMPOS PAYMENT CARD — Premium Dark & Gold
       ══════════════════════════════════════════════ */

    /* ── Section separator ── */
    .checkout-section-sep {
        display:flex; align-items:center; gap:14px;
        margin: 32px 0 24px;
    }
    .checkout-section-sep::before,
    .checkout-section-sep::after {
        content:''; flex:1; height:1px;
        background:linear-gradient(90deg, transparent, rgba(212,175,55,0.35), transparent);
    }
    .checkout-section-sep span {
        font-size:0.68rem; font-weight:800; letter-spacing:2.5px;
        text-transform:uppercase; color:var(--color-gold);
        white-space:nowrap;
    }

    /* ── Card UI shell ── */
    .card-ui-wrap {
        perspective: 1200px;
        margin-bottom: 28px;
    }
    .card-visual {
        position: relative;
        width: 100%; max-width: 380px;
        aspect-ratio: 1.586;
        border-radius: 18px;
        background: linear-gradient(135deg, #1a1108 0%, #2e2008 40%, #1a1108 100%);
        border: 1px solid rgba(212,175,55,0.35);
        box-shadow:
            0 0 0 1px rgba(212,175,55,0.1),
            0 20px 60px rgba(0,0,0,0.7),
            inset 0 1px 0 rgba(255,255,255,0.06);
        padding: 28px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
        overflow: hidden;
    }
    .card-visual::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse at 80% 10%, rgba(212,175,55,0.12) 0%, transparent 60%);
        pointer-events: none;
    }
    .card-visual:hover { transform: rotateY(-4deg) rotateX(2deg) scale(1.01); }

    .card-visual-logo {
        font-size: 1.05rem; font-weight: 900;
        letter-spacing: 3px; color: var(--color-gold);
        text-shadow: 0 0 20px rgba(212,175,55,0.4);
    }
    .card-visual-chip {
        width: 44px; height: 34px;
        background: linear-gradient(135deg, #c8a840 0%, #f0d060 40%, #c8a840 100%);
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        display: flex; align-items:center; justify-content:center;
    }
    .card-visual-chip svg { opacity: 0.6; }
    .card-visual-number {
        font-size: 1.15rem; font-weight: 700;
        letter-spacing: 3px; color: #fff;
        font-family: 'Courier New', monospace;
        text-shadow: 0 2px 8px rgba(0,0,0,0.5);
        word-spacing: 12px;
    }
    .card-visual-row {
        display:flex; justify-content:space-between; align-items:flex-end;
    }
    .card-visual-label {
        font-size: 0.58rem; font-weight: 700; letter-spacing: 1.5px;
        text-transform: uppercase; color: rgba(212,175,55,0.5);
        display:block; margin-bottom:3px;
    }
    .card-visual-value {
        font-size: 0.88rem; font-weight: 700; color: #fff;
        letter-spacing: 1.5px;
    }
    .card-network-logo {
        font-size: 0.85rem; font-weight: 900;
        color: rgba(212,175,55,0.6);
        letter-spacing: 1px;
    }

    /* ── Payment section wrapper — white background, dark text ── */
    .payment-card-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 28px;
        margin-top: 4px;
    }

    /* ── Payment inputs ── */
    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .payment-grid .full { grid-column: 1 / -1; }
    .payment-field { display:flex; flex-direction:column; gap:7px; }

    /* Dark, highly readable labels */
    .payment-label {
        font-size: 0.68rem; font-weight: 800; letter-spacing: 2px;
        text-transform: uppercase; color: #444444;
    }

    .payment-input-wrap { position: relative; }

    /* Icon tint: a mid-grey to sit cleanly on white */
    .payment-input-wrap .field-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #9ca3af; pointer-events: none;
        width: 18px; height: 18px;
    }

    /* Input: white bg, dark text, grey border by default */
    .payment-input {
        width: 100%; padding: 13px 14px 13px 42px;
        background: #ffffff;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        color: #1a1a1a;
        font-family: var(--font-primary);
        font-size: 0.92rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
        outline: none;
    }
    /* Gold border + subtle glow only on focus */
    .payment-input:focus {
        border-color: #d4af37;
        box-shadow: 0 0 0 3px rgba(212,175,55,0.15);
        background: #fffdf5;
    }
    .payment-input::placeholder { color: #9ca3af; }
    .payment-input.no-icon { padding-left: 14px; }

    /* ── ParamPOS security badge — green tint, DARK readable text ── */
    .parampos-badge {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 16px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        margin-top: 20px;
    }
    .parampos-badge svg { color: #16a34a; flex-shrink: 0; margin-top: 2px; }
    .parampos-badge-text {
        font-size: 0.73rem; font-weight: 600; letter-spacing: 0.3px;
        color: #166534;           /* dark forest green — max contrast on #f0fdf4 */
        line-height: 1.6;
    }
    .parampos-badge-text strong {
        display: block; color: #14532d; font-size: 0.75rem;
        font-weight: 800; letter-spacing: 1px; margin-bottom: 3px;
    }

    /* ── Security badge pills — dark text on white ── */
    .security-badges {
        display: flex; gap: 10px; flex-wrap: wrap;
        margin-top: 16px;
    }
    .sec-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 12px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        font-size: 0.67rem; font-weight: 700; letter-spacing: 0.8px;
        color: #374151;           /* dark grey — clearly readable */
        text-transform: uppercase;
    }

    /* ── Payment error / success banners ── */
    .payment-error-banner {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 28px;
        display: flex; gap: 14px; align-items: flex-start;
    }
    .payment-error-banner svg { color: #dc2626; flex-shrink:0; margin-top:2px; }
    .payment-error-title { font-size:0.85rem; font-weight:800; color:#991b1b; margin-bottom:5px; text-transform:uppercase; letter-spacing:1px; }
    .payment-error-msg   { font-size:0.83rem; color:#b91c1c; }

    .validation-error-banner {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 12px;
        padding: 16px 22px;
        margin-bottom: 28px;
    }
    .validation-error-banner h3 {
        font-size:0.78rem; font-weight:800; letter-spacing:1.5px;
        text-transform:uppercase; color:#92400e; margin-bottom:10px;
    }
    .validation-error-banner li {
        font-size:0.82rem; color:#78350f;
        margin-bottom:4px;
    }

    /* ── Submit button loading state ── */
    .pay-btn {
        position: relative; overflow: hidden;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 18px 0;
        font-size: 0.88rem; font-weight: 800; letter-spacing: 2px;
        text-transform: uppercase; border: none; border-radius: 8px;
        background: linear-gradient(135deg, #d4af37 0%, #f0c940 50%, #d4af37 100%);
        background-size: 200% 100%; background-position: right center;
        color: #000; cursor: pointer;
        transition: background-position 0.4s ease, transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 20px rgba(212,175,55,0.3);
        margin-top: 24px;
    }
    .pay-btn:hover:not(:disabled) {
        background-position: left center;
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(212,175,55,0.4);
    }
    .pay-btn:disabled { opacity:0.7; cursor:not-allowed; }
    .pay-btn .spinner {
        display: none; width: 18px; height: 18px;
        border: 2px solid rgba(0,0,0,0.25); border-top-color: #000;
        border-radius: 50%; animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Responsive ── */
    @media(max-width:600px) {
        .payment-grid { grid-template-columns: 1fr; }
        .card-visual  { max-width: 100%; }
    }
    </style>
</head>
<body>

    <!-- PROMO BAR -->
    <div class="promo-bar">
        <div class="promo-slider">
            <div class="promo-slide active">🔒 ParamPOS Güvenli Ödeme Altyapısı — 256-Bit SSL Şifreleme</div>
            <div class="promo-slide">✓ 3D Secure Destekli — Kartınız Güvende</div>
        </div>
    </div>

    <!-- HEADER: Clean checkout mode -->
    <header class="header">
        <div class="container" style="position:relative;">
            <div style="position:absolute;left:0;top:50%;transform:translateY(-50%);">
                <a href="cart.php" style="display:inline-flex;align-items:center;gap:7px;font-size:0.75rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--color-text-muted);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color=''">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Sepet
                </a>
            </div>
            <div class="nav-center" style="flex:1;text-align:center;">
                <a href="index.php" class="logo-link"><span>MODA AREN</span></a>
            </div>
            <div style="position:absolute;right:0;top:50%;transform:translateY(-50%);display:flex;align-items:center;gap:6px;font-size:0.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#4ade80;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Güvenli
            </div>
        </div>
    </header>

    <!-- CHECKOUT STEPS INDICATOR -->
    <div style="background:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.05); padding:14px 0;">
        <div class="container">
            <div style="display:flex;align-items:center;gap:0;justify-content:center;font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">
                <span style="color:rgba(255,255,255,0.3);">Sepet</span>
                <span style="margin:0 10px;color:rgba(255,255,255,0.15);">›</span>
                <span style="color:var(--color-gold);">Teslimat &amp; Ödeme</span>
                <span style="margin:0 10px;color:rgba(255,255,255,0.15);">›</span>
                <span style="color:rgba(255,255,255,0.3);">Onay</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="container section-padding">
        <h1 style="font-size:2rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">
            Güvenli Ödeme
        </h1>
        <p style="color:var(--color-text-muted);margin-bottom:32px;font-size:0.88rem;">
            Teslimat bilgilerinizi girin ve ParamPOS güvenli kart ödeme altyapısıyla siparişinizi tamamlayın.
        </p>

        <!-- Payment Gateway Failure Banner -->
        <?php if ($payment_error): ?>
        <div class="payment-error-banner" id="payment-error-block">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <div>
                <div class="payment-error-title">Ödeme Başarısız</div>
                <div class="payment-error-msg"><?php echo htmlspecialchars($payment_error); ?></div>
                <div style="font-size:0.75rem;color:rgba(255,255,255,0.35);margin-top:8px;">Kart bilgilerinizi kontrol edip tekrar deneyin veya farklı bir kart kullanın.</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Validation Errors Banner -->
        <?php if (!empty($errors)): ?>
        <div class="validation-error-banner">
            <h3>⚠ Lütfen aşağıdaki hataları düzeltin:</h3>
            <ul style="list-style:none;padding:0;margin:0;">
                <?php foreach ($errors as $err): ?>
                    <li>› <?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- CHECKOUT LAYOUT -->
        <div class="checkout-layout">

            <!-- ══ LEFT: Shipping + Payment Form ══ -->
            <form id="checkout-form"
                  action="checkout.php<?php echo $promo_code ? '?promo=' . urlencode($promo_code) . '&discount=' . $discount_amount : ''; ?>"
                  method="POST"
                  class="checkout-form-section"
                  onsubmit="handleCheckoutSubmit(event)">

                <!-- ─── Shipping Block ─── -->
                <h2 class="checkout-block-title">
                    <span style="color:var(--color-gold);margin-right:8px;">01</span>
                    TESLİMAT BİLGİLERİ
                </h2>

                <div class="form-group-billing">
                    <label>ALICI AD SOYAD</label>
                    <input type="text" name="name"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ($_SESSION['user_name'] ?? 'Ahmet Yılmaz')); ?>"
                           required autocomplete="name">
                </div>

                <div class="form-group-billing">
                    <label>E-POSTA ADRESİ</label>
                    <input type="email" name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? 'ahmet@modaren.com'); ?>"
                           required autocomplete="email">
                </div>

                <div class="form-group-billing">
                    <label>TESLİMAT ADRESİ</label>
                    <input type="text" name="address"
                           value="<?php echo htmlspecialchars($_POST['address'] ?? 'Barbaros Bulvarı No: 124 D: 6 Beşiktaş'); ?>"
                           required autocomplete="street-address">
                </div>

                <div class="checkout-grid-2">
                    <div class="form-group-billing">
                        <label>ŞEHİR</label>
                        <select name="city" required>
                            <?php foreach (['İstanbul','Ankara','İzmir','Bursa','Antalya','Adana','Konya','Gaziantep','Trabzon','Kayseri'] as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo (($_POST['city'] ?? 'İstanbul') === $c) ? 'selected' : ''; ?>>
                                <?php echo $c; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-billing">
                        <label>POSTA KODU</label>
                        <input type="text" name="zip"
                               value="<?php echo htmlspecialchars($_POST['zip'] ?? '34353'); ?>"
                               required pattern="\d{5}" maxlength="5" autocomplete="postal-code">
                    </div>
                </div>

                <!-- ─── Payment Block ─── -->
                <div class="checkout-section-sep">
                    <span>🔒 Güvenli Kart Ödeme</span>
                </div>

                <h2 class="checkout-block-title">
                    <span style="color:var(--color-gold);margin-right:8px;">02</span>
                    KART BİLGİLERİ
                    <span style="margin-left:auto;font-size:0.65rem;font-weight:700;letter-spacing:1px;color:#4ade80;background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.25);padding:3px 10px;border-radius:20px;">ParamPOS Sandbox</span>
                </h2>

                <!-- Live Card Preview -->
                <div class="card-ui-wrap">
                    <div class="card-visual" id="live-card">
                        <!-- Top row -->
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                            <span class="card-visual-logo">MODA AREN</span>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                <!-- NFC icon -->
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(212,175,55,0.5)" stroke-width="1.5"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
                            </div>
                        </div>
                        <!-- Chip -->
                        <div style="margin-top:8px;">
                            <div class="card-visual-chip">
                                <svg width="28" height="22" viewBox="0 0 28 22" fill="rgba(0,0,0,0.5)">
                                    <rect x="0" y="7" width="28" height="8" rx="1"/>
                                    <rect x="9" y="0" width="10" height="22" rx="1"/>
                                    <rect x="4" y="4" width="5" height="14" rx="1"/>
                                    <rect x="19" y="4" width="5" height="14" rx="1"/>
                                </svg>
                            </div>
                        </div>
                        <!-- Card Number -->
                        <div class="card-visual-number" id="cv-number">•••• &nbsp;•••• &nbsp;•••• &nbsp;••••</div>
                        <!-- Bottom row -->
                        <div class="card-visual-row">
                            <div>
                                <span class="card-visual-label">Kart Sahibi</span>
                                <span class="card-visual-value" id="cv-name">AD SOYAD</span>
                            </div>
                            <div>
                                <span class="card-visual-label">Son Kullanma</span>
                                <span class="card-visual-value" id="cv-expiry">••/••</span>
                            </div>
                            <div class="card-network-logo" id="cv-network">VISA</div>
                        </div>
                    </div>
                </div>


                <!-- Card Fields — white background section -->
                <div class="payment-card-section">
                <div class="payment-grid">
                    <!-- Cardholder Name -->
                    <div class="payment-field full">
                        <label class="payment-label">Kart Üzerindeki İsim</label>
                        <div class="payment-input-wrap">
                            <svg class="field-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input class="payment-input" type="text" name="card_name" id="card_name"
                                   placeholder="AHMET YILMAZ"
                                   value="<?php echo htmlspecialchars(strtoupper($_POST['card_name'] ?? 'AHMET YILMAZ')); ?>"
                                   autocomplete="cc-name" required
                                   oninput="updateCard()" style="text-transform:uppercase;">
                        </div>
                    </div>

                    <!-- Card Number -->
                    <div class="payment-field full">
                        <label class="payment-label">Kart Numarası</label>
                        <div class="payment-input-wrap">
                            <svg class="field-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            <input class="payment-input" type="text" name="card_no" id="card_no"
                                   placeholder="4355 8899 1234 5678"
                                   value="<?php echo htmlspecialchars($_POST['card_no'] ?? ''); ?>"
                                   maxlength="19" autocomplete="cc-number" required
                                   oninput="formatCardNumber(this); updateCard()">
                        </div>
                    </div>

                    <!-- Expiry -->
                    <div class="payment-field">
                        <label class="payment-label">Son Kullanma (AA/YY)</label>
                        <div class="payment-input-wrap">
                            <svg class="field-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <input class="payment-input" type="text" name="card_date" id="card_date"
                                   placeholder="12/28"
                                   value="<?php echo htmlspecialchars($_POST['card_date'] ?? ''); ?>"
                                   maxlength="5" autocomplete="cc-exp" required
                                   oninput="formatExpiry(this); updateCard()">
                        </div>
                    </div>

                    <!-- CVV -->
                    <div class="payment-field">
                        <label class="payment-label">CVV / CVC</label>
                        <div class="payment-input-wrap">
                            <svg class="field-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input class="payment-input" type="password" name="card_cvv" id="card_cvv"
                                   placeholder="•••"
                                   value="<?php echo htmlspecialchars($_POST['card_cvv'] ?? ''); ?>"
                                   maxlength="4" autocomplete="cc-csc" required
                                   oninput="if(this.value.length>4)this.value=this.value.slice(0,4);">
                        </div>
                    </div>
                </div>

                <!-- ParamPOS Badge -->
                <div class="parampos-badge">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/><polyline points="9 12 11 14 15 10"/></svg>
                    <div class="parampos-badge-text">
                        <strong>ParamPOS Güvenli Ödeme Altyapısı</strong>
                        İşleminiz 256-bit SSL şifrelemesi ve 3D Secure teknolojisi ile korunmaktadır. Kart bilgileriniz MODA AREN sunucularında saklanmaz.
                    </div>
                </div>

                <!-- Security Badges -->
                <div class="security-badges">
                    <span class="sec-badge">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        SSL Güvenli
                    </span>
                    <span class="sec-badge">🔐 3D Secure</span>
                    <span class="sec-badge">💳 VISA / MC / Troy</span>
                    <span class="sec-badge">🏦 ParamPOS</span>
                </div>

                <!-- Test card hint -->
                <div style="margin-top:16px;padding:12px 16px;background:#fafafa;border:1px dashed #e5e7eb;border-radius:8px;font-size:0.73rem;color:#6b7280;line-height:1.7;">
                    <strong style="color:#374151;display:block;margin-bottom:4px;">Sandbox Test Kartları:</strong>
                    ✅ <code style="color:#065f46;background:#d1fae5;padding:1px 5px;border-radius:3px;">4355 8899 1234 5678</code> — Başarılı Ödeme<br>
                    ❌ <code style="color:#991b1b;background:#fee2e2;padding:1px 5px;border-radius:3px;">4355 8899 1234 0000</code> — Ret (Limit Yetersiz)<br>
                    ⏱ <code style="color:#92400e;background:#fef3c7;padding:1px 5px;border-radius:3px;">4355 8899 1234 9999</code> — Zaman Aşımı
                </div>
                </div><!-- /.payment-card-section -->



                <!-- SUBMIT BUTTON -->
                <button type="submit" id="pay-btn" class="pay-btn">
                    <span class="spinner" id="pay-spinner"></span>
                    <svg id="pay-lock-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span id="pay-btn-text">SİPARİŞİ TAMAMLA — <?php echo number_format($total, 2, ',', '.'); ?> TL ÖDET</span>
                </button>

            </form>

            <!-- ══ RIGHT: Order Summary ══ -->
            <div class="cart-summary-panel">
                <h2 class="summary-title">SEPET ÖZETİ</h2>

                <div class="checkout-summary-list">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="checkout-summary-item">
                        <img class="checkout-summary-img"
                             src="<?php echo htmlspecialchars($item['image_url']); ?>"
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="checkout-summary-details">
                            <div>
                                <div class="checkout-summary-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="checkout-summary-qty-size">
                                    Adet: <?php echo $item['quantity']; ?> | Beden: <?php echo htmlspecialchars($item['size']); ?>
                                </div>
                            </div>
                            <span class="checkout-summary-price">
                                <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?> TL
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Ara Toplam</span>
                    <span><?php echo number_format($subtotal, 2, ',', '.'); ?> TL</span>
                </div>

                <?php if ($discount_amount > 0): ?>
                <div class="summary-row" style="color:#4ade80;">
                    <span>İndirim (<?php echo htmlspecialchars($promo_code); ?>)</span>
                    <span>- <?php echo number_format($discount_amount, 2, ',', '.'); ?> TL</span>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Kargo Ücreti</span>
                    <span><?php echo $shipping == 0 ? 'ÜCRETSİZ' : number_format($shipping, 2, ',', '.') . ' TL'; ?></span>
                </div>

                <div class="summary-row total">
                    <span>Ödenecek Tutar</span>
                    <span><?php echo number_format($total, 2, ',', '.'); ?> TL</span>
                </div>

                <!-- ParamPOS Mini Badge -->
                <div style="margin-top:20px;padding:10px 14px;background:rgba(74,222,128,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:8px;display:flex;align-items:center;gap:10px;">
                    <svg width="16" height="16" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    <span style="font-size:0.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#4ade80;">ParamPOS Korumalı</span>
                </div>

                <a href="cart.php" class="btn-secondary" style="width:100%;justify-content:center;box-sizing:border-box;margin-top:14px;">
                    ← Sepete Geri Dön
                </a>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container" style="text-align:center;">
            <p class="copyright">© <?php echo date('Y'); ?> MODA AREN. Tüm Hakları Saklıdır. | ParamPOS Güvenli Ödeme</p>
        </div>
    </footer>

    <script>
    /* ══════════════════════════════════════════════
       LIVE CARD PREVIEW & INPUT FORMATTING
       ══════════════════════════════════════════════ */

    function updateCard() {
        const name   = document.getElementById('card_name').value.toUpperCase() || 'AD SOYAD';
        const no     = document.getElementById('card_no').value;
        const expiry = document.getElementById('card_date').value || '••/••';

        // Format number for card display
        const cleaned = no.replace(/\s/g, '');
        let display = '';
        for (let i = 0; i < 16; i++) {
            if (i > 0 && i % 4 === 0) display += '\u00a0\u00a0';
            display += cleaned[i] || '•';
        }
        document.getElementById('cv-number').textContent = display;
        document.getElementById('cv-name').textContent   = name.slice(0, 22);
        document.getElementById('cv-expiry').textContent = expiry;

        // Detect network from first digit
        const first = cleaned[0];
        const net   = document.getElementById('cv-network');
        if (first === '4')      net.textContent = 'VISA';
        else if (first === '5') net.textContent = 'MASTERCARD';
        else if (first === '9') net.textContent = 'TROY';
        else                    net.textContent = 'CARD';
    }

    function formatCardNumber(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 16);
        input.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
    }

    function formatExpiry(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
        else if (v.length === 2 && !input.value.includes('/')) v += '/';
        input.value = v;
    }

    /* ── Submit with loading state ── */
    function handleCheckoutSubmit(e) {
        const btn     = document.getElementById('pay-btn');
        const spinner = document.getElementById('pay-spinner');
        const lock    = document.getElementById('pay-lock-icon');
        const label   = document.getElementById('pay-btn-text');

        btn.disabled  = true;
        spinner.style.display = 'block';
        lock.style.display    = 'none';
        label.textContent     = 'İşleniyor…';

        // Let the form submit naturally (not AJAX) — server handles ParamPOS
        return true;
    }

    /* ── Auto-trigger card preview on load ── */
    document.addEventListener('DOMContentLoaded', function() {
        updateCard();

        // Scroll to payment error if present
        const errBlock = document.getElementById('payment-error-block');
        if (errBlock) {
            errBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // CVV flip effect: highlight back of card area visually
        document.getElementById('card_cvv').addEventListener('focus', () => {
            document.getElementById('live-card').style.filter = 'brightness(0.7)';
        });
        document.getElementById('card_cvv').addEventListener('blur', () => {
            document.getElementById('live-card').style.filter = '';
        });
    });
    </script>

    <script src="script.js"></script>
</body>
</html>
