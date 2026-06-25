<?php
// Include database connection (which starts session)
require_once 'db_connect.php';

$order_id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$transaction_id = isset($_GET['tx']) ? htmlspecialchars(trim($_GET['tx'])) : '';

$order       = null;
$order_items = [];

if ($db_connected && $conn && $order_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        if ($order) {
            $item_stmt = $conn->prepare("
                SELECT oi.*, p.name as product_name, p.brand as product_brand, p.image_url
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $item_stmt->execute([$order_id]);
            $order_items = $item_stmt->fetchAll();

            // Extract transaction ID from payment_method field if not in URL
            if (!$transaction_id && !empty($order['payment_method'])) {
                if (preg_match('/TX:\s*(.+)$/', $order['payment_method'], $m)) {
                    $transaction_id = trim($m[1]);
                }
            }
        }
    } catch (PDOException $e) {
        $order = null;
    }
}

// Fallback to session mock order if database is offline
if (!$order && isset($_SESSION['last_mock_order']) && $_SESSION['last_mock_order']['id'] == $order_id) {
    $mock_order = $_SESSION['last_mock_order'];
    $order = [
        'id'               => $mock_order['id'],
        'customer_name'    => $mock_order['customer_name'],
        'customer_email'   => $mock_order['customer_email'],
        'shipping_address' => $mock_order['shipping_address'],
        'shipping_city'    => $mock_order['shipping_city'],
        'shipping_zip'     => $mock_order['shipping_zip'],
        'total_price'      => $mock_order['total_price'],
        'payment_method'   => 'ParamPOS | TX: ' . ($mock_order['transaction_id'] ?? 'N/A'),
        'created_at'       => $mock_order['created_at'],
    ];
    if (!$transaction_id && !empty($mock_order['transaction_id'])) {
        $transaction_id = $mock_order['transaction_id'];
    }
    foreach ($mock_order['items'] as $item) {
        $order_items[] = [
            'product_name'  => $item['name'],
            'product_brand' => $item['brand'],
            'size'          => $item['size'],
            'quantity'      => $item['quantity'],
            'price'         => $item['price'],
            'image_url'     => $item['image_url'] ?? '',
        ];
    }
}

// If no order is found, redirect to homepage
if (!$order) {
    header('Location: index.php');
    exit;
}

$order_date = date('d.m.Y H:i', strtotime($order['created_at']));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişiniz Onaylandı! | MODA AREN</title>
    <meta name="description" content="Sipariş onay sayfası. ParamPOS ödeme başarıyla tamamlandı.">
    <link rel="stylesheet" href="style.css">
    <style>
    /* ── Success page extras ── */
    .success-page-layout {
        max-width: 680px; margin: 60px auto 100px;
        text-align: center;
    }
    @keyframes successPop {
        0%   { transform: scale(0) rotate(-45deg); opacity:0; }
        60%  { transform: scale(1.18) rotate(6deg); opacity:1; }
        80%  { transform: scale(0.94) rotate(-3deg); }
        100% { transform: scale(1) rotate(0); opacity:1; }
    }
    @keyframes successGlow {
        0%, 100% { box-shadow: 0 0 30px rgba(74,222,128,0.3), 0 0 60px rgba(74,222,128,0.1); }
        50%       { box-shadow: 0 0 50px rgba(74,222,128,0.5), 0 0 100px rgba(74,222,128,0.2); }
    }
    .success-icon-container {
        width: 90px; height: 90px;
        background: linear-gradient(135deg, #16a34a, #4ade80);
        border-radius: 50%;
        display: flex; align-items:center; justify-content:center;
        font-size: 2.5rem; color: #fff;
        margin: 0 auto 28px;
        animation: successPop 0.7s cubic-bezier(0.34,1.56,0.64,1) both,
                   successGlow 2.5s ease-in-out infinite 0.7s;
    }
    .success-title {
        font-size: 2rem; font-weight: 900; letter-spacing: 2px;
        text-transform: uppercase; margin-bottom: 14px;
    }
    .success-message { color: var(--color-text-muted); margin-bottom: 36px; font-size: 0.95rem; line-height:1.7; }

    .tx-badge {
        display: inline-flex; align-items: center; gap: 10px;
        background: rgba(74,222,128,0.08);
        border: 1px solid rgba(74,222,128,0.25);
        border-radius: 10px;
        padding: 12px 20px; margin-bottom: 32px;
        font-size: 0.78rem; font-weight: 700;
        letter-spacing: 1px; text-transform: uppercase;
    }
    .tx-badge svg { color: #4ade80; flex-shrink:0; }
    .tx-badge .tx-label { color: rgba(255,255,255,0.4); margin-right:4px; }
    .tx-badge .tx-id    { color: #4ade80; font-family:'Courier New',monospace; letter-spacing:1.5px; }

    .success-summary-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(212,175,55,0.2);
        border-radius: 16px;
        padding: 28px;
        text-align: left;
        margin-bottom: 32px;
    }
    .success-summary-title {
        font-size: 0.75rem; font-weight: 800; letter-spacing: 2px;
        text-transform: uppercase; color: var(--color-gold);
        margin-bottom: 20px; padding-bottom: 14px;
        border-bottom: 1px solid rgba(212,175,55,0.15);
    }
    .success-summary-row {
        display: flex; justify-content: space-between; align-items:flex-start;
        gap: 12px; padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        font-size: 0.85rem;
    }
    .success-summary-row:last-child { border-bottom: none; }
    .success-summary-row span:first-child { color: var(--color-text-muted); flex-shrink:0; }
    .success-summary-row strong { color: #fff; }
    .total-row {
        padding-top: 16px; margin-top: 8px;
        border-top: 1px solid rgba(212,175,55,0.2) !important;
        border-bottom: none !important;
        font-size: 1rem; font-weight: 800;
    }
    .success-item-row {
        display:flex; align-items:center; gap:12px;
        padding:10px 0;
        border-bottom:1px solid rgba(255,255,255,0.04);
        font-size:0.83rem;
    }
    .success-item-row img {
        width:48px; height:48px; object-fit:cover;
        border-radius:6px; flex-shrink:0;
        border:1px solid rgba(255,255,255,0.08);
    }
    .success-item-row .item-info { flex:1; min-width:0; }
    .success-item-row .item-name { font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .success-item-row .item-meta { font-size:0.73rem; color:var(--color-text-muted); margin-top:2px; }
    .success-item-row .item-price { font-weight:800; color:var(--color-gold); white-space:nowrap; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="header" style="border-bottom:1px solid var(--color-gray-border);">
        <div class="container" style="text-align:center;justify-content:center;display:flex;align-items:center;">
            <a href="index.php" class="logo-link"><span>MODA AREN</span></a>
        </div>
    </header>

    <!-- SUCCESS MAIN -->
    <main class="container">
        <div class="success-page-layout">

            <!-- Animated Check Icon -->
            <div class="success-icon-container">✓</div>

            <h1 class="success-title">Ödeme Başarılı!</h1>
            <p class="success-message">
                MODA AREN'i tercih ettiğiniz için teşekkür ederiz.<br>
                ParamPOS güvenli ödeme altyapısı üzerinden işleminiz onaylanmış ve siparişiniz sisteme kaydedilmiştir.
            </p>

            <?php if ($transaction_id): ?>
            <!-- ParamPOS Transaction Badge -->
            <div class="tx-badge">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                <span><span class="tx-label">ParamPOS TX No:</span><span class="tx-id"><?php echo htmlspecialchars($transaction_id); ?></span></span>
            </div>
            <?php endif; ?>

            <!-- Order Summary Card -->
            <div class="success-summary-card">
                <h2 class="success-summary-title">Sipariş Detayı &amp; Fatura</h2>

                <div class="success-summary-row">
                    <span>Sipariş No:</span>
                    <strong style="color:var(--color-gold);">#<?php echo htmlspecialchars($order['id']); ?></strong>
                </div>
                <div class="success-summary-row">
                    <span>Tarih:</span>
                    <span><?php echo $order_date; ?></span>
                </div>
                <div class="success-summary-row">
                    <span>Müşteri:</span>
                    <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="success-summary-row">
                    <span>E-posta:</span>
                    <span><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="success-summary-row">
                    <span>Teslimat Adresi:</span>
                    <span style="text-align:right;max-width:300px;"><?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city'] . ' - ' . $order['shipping_zip']); ?></span>
                </div>
                <div class="success-summary-row">
                    <span>Tahmini Teslimat:</span>
                    <strong style="color:var(--color-gold);">2-3 İş Günü</strong>
                </div>

                <!-- Items -->
                <div style="border-top:1px solid rgba(255,255,255,0.06);margin-top:16px;padding-top:16px;">
                    <span style="font-size:0.7rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:var(--color-text-muted);display:block;margin-bottom:12px;">ALINAN ÜRÜNLER</span>
                    <?php foreach ($order_items as $item): ?>
                    <div class="success-item-row">
                        <?php if (!empty($item['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <?php endif; ?>
                        <div class="item-info">
                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="item-meta">Beden: <?php echo htmlspecialchars($item['size']); ?> &bull; Adet: <?php echo $item['quantity']; ?></div>
                        </div>
                        <span class="item-price"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?> TL</span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="success-summary-row total-row">
                    <span>Toplam Ödenen:</span>
                    <span class="text-gold" style="font-size:1.1rem;"><?php echo number_format($order['total_price'], 2, ',', '.'); ?> TL</span>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;">
                <a href="index.php" class="btn-primary" style="padding:16px 36px;">ALIŞVERİŞE DEVAM ET</a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="profile.php?tab=orders" class="btn-secondary" style="padding:16px 28px;">Siparişlerim</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer" style="margin-top:60px;padding:40px 0;">
        <div class="container" style="text-align:center;">
            <p class="copyright">&copy; <?php echo date('Y'); ?> MODA AREN. Tüm Hakları Saklıdır. | ParamPOS Güvenli Ödeme</p>
        </div>
    </footer>

    <script>
    // Confetti burst on page load
    document.addEventListener('DOMContentLoaded', function () {
        const colors = ['#d4af37','#f0c940','#4ade80','#fff','#fbbf24'];
        for (let i = 0; i < 60; i++) {
            const el = document.createElement('div');
            el.style.cssText = `
                position:fixed;
                top:${-10 + Math.random() * 20}px;
                left:${Math.random() * 100}vw;
                width:${6 + Math.random() * 8}px;
                height:${6 + Math.random() * 8}px;
                background:${colors[Math.floor(Math.random() * colors.length)]};
                border-radius:${Math.random() > 0.5 ? '50%' : '2px'};
                pointer-events:none;
                z-index:9999;
                animation: confettiFall ${1.5 + Math.random() * 2}s ease ${Math.random() * 0.8}s forwards;
            `;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        if (!document.getElementById('confetti-style')) {
            const s = document.createElement('style');
            s.id = 'confetti-style';
            s.textContent = `
                @keyframes confettiFall {
                    0%   { transform: translateY(0) rotate(0); opacity:1; }
                    100% { transform: translateY(100vh) rotate(${Math.random() > 0.5 ? '' : '-'}${360 + Math.random()*360}deg); opacity:0; }
                }
            `;
            document.head.appendChild(s);
        }
    });
    </script>
</body>
</html>
