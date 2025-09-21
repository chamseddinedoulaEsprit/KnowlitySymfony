<?php
require 'vendor/autoload.php'; // Make sure the Composer autoloader is included

use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Builder\Builder;

// Ensure the directory exists or create it if it doesn't
$directory = realpath('public/uploads/qr-codes');
if (!$directory) {
    mkdir('public/uploads/qr-codes', 0775, true); // Create directory with permissions
}

// Create the QR code using SvgWriter
$result = Builder::create()
    ->writer(new SvgWriter()) // Use SvgWriter for SVG output
    ->data('Sample QR Code Data') // Data to encode
    ->size(300)
    ->margin(10)
    ->build();

// Save the SVG to the specified directory
$qrCodePath = $directory . '/qr-code.svg';
$result->saveToFile($qrCodePath);

// Check if the file was successfully created and saved
if (file_exists($qrCodePath)) {
    echo "QR Code has been saved successfully to: " . $qrCodePath;
} else {
    echo "Failed to save the QR Code.";
}
?>
