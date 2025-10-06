<?php
// create-pwa-icons.php
echo "🚀 Creating PWA icons...\n";

function createIcon($size, $filename) {
    $image = imagecreatetruecolor($size, $size);
    
    // Background color (blue - #0d6efd)
    $bgColor = imagecolorallocate($image, 13, 110, 253);
    
    // Text color (white)
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add "POS" text for icons larger than 128px
    if ($size >= 128) {
        // Use a simple approach for text
        $text = "POS";
        $font = 5; // Built-in GD font
        
        // Calculate text position (centered)
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = ($size - $textWidth) / 2;
        $y = ($size - $textHeight) / 2;
        
        // Add text
        imagestring($image, $font, $x, $y, $text, $textColor);
    }
    
    // Save image
    if (imagepng($image, $filename)) {
        echo "✅ Created: $filename\n";
    } else {
        echo "❌ Failed to create: $filename\n";
    }
    
    imagedestroy($image);
}

// Create icons directory in public folder
$iconsDir = __DIR__ . '/public/icons';
if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
    echo "📁 Created directory: $iconsDir\n";
}

// Generate icons
createIcon(192, $iconsDir . '/icon-192x192.png');
createIcon(512, $iconsDir . '/icon-512x512.png');

echo "🎉 PWA icons created successfully!\n";

// Test if files exist
echo "\n📋 File check:\n";
echo "- " . (file_exists($iconsDir . '/icon-192x192.png') ? '✅' : '❌') . " icon-192x192.png\n";
echo "- " . (file_exists($iconsDir . '/icon-512x512.png') ? '✅' : '❌') . " icon-512x512.png\n";

echo "\n🌐 Test URLs:\n";
echo "- http://127.0.0.1:8000/icons/icon-192x192.png\n";
echo "- http://127.0.0.1:8000/icons/icon-512x512.png\n";
?>