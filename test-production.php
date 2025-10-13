<?php
/**
 * Production Test Script
 * Run this to test if the API endpoints are working
 */

// Test URLs
$baseUrl = '/'; // Replace with your actual domain
$testUrls = [
    'Health Check' => $baseUrl . '/health',
    'CSRF Cookie' => $baseUrl . '/sanctum/csrf-cookie',
    'API Test' => $baseUrl . '/api/test',
    'API CSRF' => $baseUrl . '/api/csrf-cookie',
    'API Debug' => $baseUrl . '/api/debug-session',
    'Blogs API' => $baseUrl . '/api/blogs?per_page=5&page=1',
];

echo "🧪 Testing Production API Endpoints\n";
echo "=====================================\n\n";

foreach ($testUrls as $name => $url) {
    echo "Testing: $name\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Requested-With: XMLHttpRequest'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Error: $error\n";
    } else {
        echo "✅ HTTP Code: $httpCode\n";
        if ($response) {
            $data = json_decode($response, true);
            if ($data) {
                echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "Response: $response\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "🔍 CORS Test\n";
echo "============\n\n";

// Test CORS headers
$corsUrl = $baseUrl . '/api/test';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $corsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: https://yourdomain.com',
    'Access-Control-Request-Method: GET',
    'Access-Control-Request-Headers: Content-Type'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
curl_close($ch);

echo "CORS Headers Test:\n";
echo "HTTP Code: $httpCode\n";
echo "Headers:\n$response\n";

echo "\n✅ Test completed!\n";
echo "\n📋 Next steps:\n";
echo "1. Update the \$baseUrl variable with your actual domain\n";
echo "2. Run: php test-production.php\n";
echo "3. Check the responses and fix any issues\n";
?>
