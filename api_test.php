<?php
/**
 * API Test Script for Molhanout
 * Run: php api_test.php
 */

$baseUrl = 'http://mol.o-dev.store/api/v1';
$shopToken = null;
$distToken = null;

function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

function testApi($name, $method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    
    $result = makeRequest($method, $baseUrl . $endpoint, $data, $token);
    $status = ($result['code'] >= 200 && $result['code'] < 300) ? '✅' : '❌';
    
    echo "\n$status [$method] $endpoint - HTTP {$result['code']}\n";
    
    if ($result['code'] >= 400) {
        echo "   Error: " . ($result['body']['message'] ?? 'Unknown error') . "\n";
    }
    
    return $result;
}

echo "======================================\n";
echo "🧪 MOLHANOUT API TESTS\n";
echo "======================================\n";

// ═══════════════════════════════════════
// 1. APP APIs (Public)
// ═══════════════════════════════════════
echo "\n📱 APP APIs\n";
echo "-------------------\n";

testApi('App Config', 'GET', '/app/config');
testApi('App Theme', 'GET', '/app/theme');
testApi('Categories', 'GET', '/categories');
testApi('Products', 'GET', '/products');
testApi('Product Detail', 'GET', '/products/1');

// ═══════════════════════════════════════
// 2. AUTH APIs
// ═══════════════════════════════════════
echo "\n🔐 AUTH APIs\n";
echo "-------------------\n";

// Login as Shop Owner
$loginResult = testApi('Login Shop Owner', 'POST', '/auth/login', [
    'login' => 'shop@molhanout.ma',
    'password' => 'password'
]);

if ($loginResult['code'] === 200) {
    $shopToken = $loginResult['body']['data']['token'] ?? null;
    echo "   Shop Token: " . substr($shopToken ?? 'N/A', 0, 20) . "...\n";
}

// Login as Distributor
$loginResult = testApi('Login Distributor', 'POST', '/auth/login', [
    'login' => 'dist@molhanout.ma',
    'password' => 'password'
]);

if ($loginResult['code'] === 200) {
    $distToken = $loginResult['body']['data']['token'] ?? null;
    echo "   Dist Token: " . substr($distToken ?? 'N/A', 0, 20) . "...\n";
}

// Test profile with shop token
if ($shopToken) {
    testApi('Get Profile (Shop)', 'GET', '/auth/profile', null, $shopToken);
}

// ═══════════════════════════════════════
// 3. SHOP OWNER APIs
// ═══════════════════════════════════════
echo "\n🏪 SHOP OWNER APIs\n";
echo "-------------------\n";

if ($shopToken) {
    // Orders
    $ordersResult = testApi('List Orders', 'GET', '/shop/orders', null, $shopToken);
    
    // Get first order ID for further tests
    $orderId = $ordersResult['body']['data'][0]['id'] ?? null;
    
    if ($orderId) {
        testApi("Order Detail #$orderId", 'GET', "/shop/orders/$orderId", null, $shopToken);
        testApi("Order Offers #$orderId", 'GET', "/shop/orders/$orderId/offers", null, $shopToken);
    }
    
    // Create new order
    $newOrderResult = testApi('Create Order', 'POST', '/shop/orders', [
        'delivery_address' => 'Test Address, Casablanca',
        'delivery_latitude' => 33.5731,
        'delivery_longitude' => -7.5898,
        'notes' => 'Test order from API',
        'items' => [
            ['product_id' => 1, 'quantity' => 10, 'unit' => 'pcs'],
            ['product_id' => 2, 'quantity' => 5, 'unit' => 'kg'],
        ]
    ], $shopToken);
    
    $newOrderId = $newOrderResult['body']['data']['id'] ?? null;
    
    if ($newOrderId) {
        // Publish the order
        testApi("Publish Order #$newOrderId", 'POST', "/shop/orders/$newOrderId/publish", null, $shopToken);
    }
    
    // Customers & Credits
    testApi('List Customers', 'GET', '/shop/customers', null, $shopToken);
    
    // Create customer
    $customerResult = testApi('Create Customer', 'POST', '/shop/customers', [
        'name' => 'Test Customer API',
        'phone' => '+212600000001',
        'address' => 'Test Address'
    ], $shopToken);
    
    $customerId = $customerResult['body']['data']['id'] ?? null;
    
    if ($customerId) {
        testApi("Customer Detail #$customerId", 'GET', "/shop/customers/$customerId", null, $shopToken);
        
        // Add credit
        $creditResult = testApi('Add Credit', 'POST', '/shop/credits', [
            'customer_id' => $customerId,
            'amount' => 500,
            'description' => 'Test credit from API',
            'due_date' => date('Y-m-d', strtotime('+30 days'))
        ], $shopToken);
        
        $creditId = $creditResult['body']['data']['id'] ?? null;
        
        if ($creditId) {
            // Add payment
            testApi('Add Payment', 'POST', "/shop/credits/$creditId/payment", [
                'amount' => 100,
                'payment_method' => 'cash',
                'description' => 'Partial payment'
            ], $shopToken);
        }
        
        testApi("Customer Transactions #$customerId", 'GET', "/shop/customers/$customerId/transactions", null, $shopToken);
    }
    
    // Frequent products
    testApi('Frequent Products', 'GET', '/shop/products/frequent', null, $shopToken);
}

// ═══════════════════════════════════════
// 4. DISTRIBUTOR APIs
// ═══════════════════════════════════════
echo "\n🚚 DISTRIBUTOR APIs\n";
echo "-------------------\n";

if ($distToken) {
    // Available orders
    $availableResult = testApi('Available Orders', 'GET', '/distributor/orders/available', null, $distToken);
    
    // Get first available order with items
    $availableOrder = $availableResult['body']['data'][0] ?? null;
    $availableOrderId = $availableOrder['id'] ?? null;
    
    // My offers
    testApi('My Offers', 'GET', '/distributor/offers', null, $distToken);
    
    // Submit offer for an available order
    if ($availableOrderId && isset($availableOrder['items'])) {
        // Build items array from order items
        $offerItems = [];
        foreach ($availableOrder['items'] as $item) {
            $offerItems[] = [
                'order_item_id' => $item['id'],
                'product_id' => $item['product_id'],
                'unit_price' => ($item['product']['reference_price'] ?? 50) * 0.95,
                'quantity' => $item['quantity'],
                'is_available' => true,
            ];
        }
        
        $offerResult = testApi('Submit Offer', 'POST', '/distributor/offers', [
            'order_id' => $availableOrderId,
            'delivery_cost' => 30,
            'estimated_delivery_time' => '2 heures',
            'notes' => 'Test offer from API',
            'items' => $offerItems
        ], $distToken);
    }
    
    // Deliveries
    $deliveriesResult = testApi('My Deliveries', 'GET', '/distributor/deliveries', null, $distToken);
    
    $deliveryId = $deliveriesResult['body']['data'][0]['id'] ?? null;
    
    if ($deliveryId) {
        testApi("Delivery Detail #$deliveryId", 'GET', "/distributor/deliveries/$deliveryId", null, $distToken);
    }
}

// ═══════════════════════════════════════
// 5. NOTIFICATIONS APIs
// ═══════════════════════════════════════
echo "\n🔔 NOTIFICATIONS APIs\n";
echo "-------------------\n";

if ($shopToken) {
    testApi('List Notifications', 'GET', '/notifications', null, $shopToken);
    testApi('Mark All Read', 'PUT', '/notifications/read-all', null, $shopToken);
}

echo "\n======================================\n";
echo "🏁 API TESTS COMPLETED\n";
echo "======================================\n";
