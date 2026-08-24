<?php
$result   = null;
$otp      = null;
$phone    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
    $otp   = rand(100000, 999999);
    $msg   = "קוד האימות שלך: {$otp}";

    // SMS4Free API
    $payload = json_encode([
        'key'       => 'HlbUyun9G',
        'user'      => '0509222392',
        'pass'      => '13683843',
        'sender'    => 'Gizra',
        'recipient' => $phone,
        'msg'       => $msg,
    ]);

    $ch = curl_init('https://api.sms4free.co.il/ApiSMS/v2/SendSMS');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = [
        'phone'    => $phone,
        'otp'      => $otp,
        'http'     => $httpCode,
        'response' => $response ?: $curlErr,
        'payload'  => $payload,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SMS4Free OTP Test</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system,sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
  .card { background: #fff; border-radius: 12px; padding: 32px; width: 100%; max-width: 440px; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
  h2 { font-size: 20px; color: #1e2022; margin-bottom: 6px; }
  p.sub { font-size: 12px; color: #8c98a4; margin-bottom: 24px; }
  label { font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px; }
  input[type=text] { width: 100%; border: 1.5px solid #dee2e6; border-radius: 8px; padding: 10px 14px; font-size: 15px; outline: none; transition: border .2s; }
  input[type=text]:focus { border-color: #0d6efd; }
  button { width: 100%; background: #0d6efd; color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 16px; transition: opacity .2s; }
  button:hover { opacity: .88; }
  .result { margin-top: 20px; background: #f8f9fa; border-radius: 8px; padding: 16px; font-size: 13px; }
  .result .row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #eee; }
  .result .row:last-child { border-bottom: none; }
  .result .key { color: #8c98a4; font-weight: 600; }
  .result .val { color: #1e2022; font-weight: 500; word-break: break-all; text-align: right; max-width: 60%; }
  .ok { color: #28a745; font-weight: 700; }
  .err { color: #dc3545; font-weight: 700; }
  .raw { margin-top: 10px; background: #1e2022; color: #a8ff78; border-radius: 6px; padding: 10px; font-family: monospace; font-size: 11px; white-space: pre-wrap; word-break: break-all; }
  .hint { font-size: 11px; color: #8c98a4; margin-top: 8px; }
</style>
</head>
<body>
<div class="card">
  <h2>📱 SMS4Free OTP Test</h2>
  <p class="sub">API Key: <?= htmlspecialchars(substr($apiKey,0,4)) ?>••••• &nbsp;|&nbsp; Real SMS sent to Israel numbers</p>

  <form method="POST">
    <label>Israel Mobile Number</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
           placeholder="e.g. 0521234567 or +972521234567" autofocus required>
    <p class="hint">Enter number with or without country code. Digits only also fine.</p>
    <button type="submit">Send OTP →</button>
  </form>

  <?php if ($result): ?>
  <div class="result">
    <div class="row">
      <span class="key">Phone sent to</span>
      <span class="val"><?= htmlspecialchars($result['phone']) ?></span>
    </div>
    <div class="row">
      <span class="key">OTP generated</span>
      <span class="val" style="font-size:18px;font-weight:800;color:#0d6efd"><?= $result['otp'] ?></span>
    </div>
    <div class="row">
      <span class="key">HTTP status</span>
      <span class="val <?= $result['http'] == 200 ? 'ok' : 'err' ?>"><?= $result['http'] ?></span>
    </div>
    <div class="row">
      <span class="key">API response</span>
      <span class="val"><?= htmlspecialchars($result['response']) ?></span>
    </div>
    <div class="raw"><?= htmlspecialchars($result['payload']) ?></div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
