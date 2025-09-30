<?php
// customer-react/api/cart_data.php

header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true"); // Crucial for session/cookie sharing
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
require_once "../dbconnect.php"; // Adjust path as necessary

$response = [
    'success' => false,
    'message' => 'An unexpected error occurred.',
    'data' => [
        'items' => [],
        'total' => 0.00
    ]
];

// Helper: Apply discount (copied from your cart.php for consistency)
function applyDiscount($basePrice, $discountType, $discountValue) {
    if ($discountType === 'percent' && $discountValue > 0) {
        return $basePrice - ($basePrice * ($discountValue / 100));
    } elseif ($discountType === 'fixed' && $discountValue > 0) {
        // Use 'amount' for fixed-amount discounts if that's your DB standard,
        // but using 'fixed' here to match your provided code.
        return max(0, $basePrice - $discountValue);
    }
    return $basePrice;
}

try {
    // Check for login (React will handle redirect, but API should know status)
    if (!isset($_SESSION["username"])) {
        http_response_code(401);
        $response['message'] = "Authentication required.";
        echo json_encode($response);
        exit;
    }

    // Initialize cart if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cartItems = $_SESSION['cart'];
    $grandTotal = 0;
    $finalCart = [];

    // --- Process Cart Items ---
    foreach ($cartItems as $index => $item) {
        
        // 1. Fetch latest prices/details from DB
        $stmt = $conn->prepare("SELECT name, price, discount_type, discount_value FROM lunchboxes WHERE id = ?");
        $stmt->execute([$item['lunchbox_id']]);
        $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT name, duration_days, discount_type, discount_value FROM plans WHERE id = ?");
        $stmt->execute([$item['plan_id']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        // Skip if data is invalid (e.g., deleted from DB)
        if (!$lunchbox || !$plan) {
            continue; 
        }

        // 2. Calculate secure final price
        // ASSUMPTION: Lunchbox price is a 30-day base price.
        $basePricePerUnit = $lunchbox['price'] * ($plan['duration_days'] / 30);
        $discountedPrice = applyDiscount($basePricePerUnit, $lunchbox['discount_type'], $lunchbox['discount_value']);
        $discountedPrice = applyDiscount($discountedPrice, $plan['discount_type'], $plan['discount_value']);
        $discountedPrice = round($discountedPrice, 2);

        // 3. Calculate line totals
        $lineTotal = $discountedPrice * $item['quantity'];
        $grandTotal += $lineTotal;

        // 4. Build item for response
        $finalCart[] = [
            'index'         => $index, // Needed for actions (remove/update quantity)
            'lunchbox_id'   => $item['lunchbox_id'],
            'plan_id'       => $item['plan_id'],
            'name'          => $lunchbox['name'] . ' (' . $plan['name'] . ')',
            'image'         => $item['image'], // Image is trusted from when it was first added
            'quantity'      => $item['quantity'],
            'price_per_unit'=> $discountedPrice,
            'line_total'    => round($lineTotal, 2)
        ];
    }
    
    // --- Final Response ---
    $response['success'] = true;
    $response['message'] = 'Cart data fetched successfully.';
    $response['data']['items'] = $finalCart;
    $response['data']['total'] = round($grandTotal, 2);

    http_response_code(200);

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>