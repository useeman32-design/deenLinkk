<?php
// generate_key.php - Run this ONCE, then delete it
echo "<pre>";
echo "=========================================\n";
echo "YOUR SITE_KEY FOR DEENLINK:\n";
echo "=========================================\n";
echo "\n";

// Generate 32 bytes = 64 hex characters
$key = bin2hex(random_bytes(32));

echo "Option 1 (64 characters - Recommended):\n";
echo $key . "\n\n";

echo "Option 2 (44 characters - Base64):\n";
echo base64_encode(random_bytes(32)) . "\n\n";

echo "Option 3 (50 characters - Alphanumeric):\n";
echo substr(str_shuffle(str_repeat(
    '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 
    5)), 0, 50) . "\n\n";

echo "=========================================\n";
echo "HOW TO USE:\n";
echo "=========================================\n";
echo "1. Copy one of the keys above\n";
echo "2. Paste it in your config.php:\n";
echo "   define('SITE_KEY', 'your_key_here');\n";
echo "3. DELETE THIS FILE!\n";
echo "=========================================\n";
echo "</pre>";
?>