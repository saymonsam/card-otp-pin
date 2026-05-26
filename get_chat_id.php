<?php
// ============================================
// GET YOUR TELEGRAM CHAT ID
// ============================================
// 1. আপনার বটে একটি মেসেজ দিন: /start
// 2. এই ফাইলটি রান করুন
// ============================================

define('BOT_TOKEN', '8285609435:AAEDsESPpDkIEIbtFk3RBwu7O_uiZTYN96s');

$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getUpdates";
$response = file_get_contents($url);
$data = json_decode($response, true);

echo "<pre>";
if (isset($data['result']) && count($data['result']) > 0) {
    $chatId = $data['result'][0]['message']['chat']['id'];
    echo "✅ Your Chat ID: <b>" . $chatId . "</b>\n\n";
    echo "📝 Copy this ID and paste it in capture.php\n";
    echo "   define('TELEGRAM_CHAT_ID', '$chatId');\n";
} else {
    echo "❌ No messages found.\n";
    echo "👉 Send /start to your bot first, then refresh this page.\n";
}
echo "</pre>";
echo "<h3>Full Response:</h3>";
print_r($data);
?>
