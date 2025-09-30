<?php
// customer-react/api/checkout_process.php

// --- CORS Configuration Headers ---
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true"); 
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
require_once "../dbconnect.php"; // Adjust path as necessary

// Helper: Apply discount (Must match cart_data.php and cart_actions.php)
function applyDiscount($basePrice, $discountType, $discountValue) {
    if ($discountType === 'percent' && $discountValue > 0) {
        return $basePrice - ($basePrice * ($discountValue / 100));
    } elseif ($discountType === 'fixed' && $discountValue > 0) {
        return max(0, $basePrice - $discountValue);
    }
    return $basePrice;
}

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit; 
}

$response = ['success' => false, 'message' => 'Invalid Request.'];

// Must be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method Not Allowed. Use POST.';
    echo json_encode($response);
    exit;
}

// Check for login
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    $response['message'] = "Authentication required.";
    echo json_encode($response);
    exit;
}
$user_id = $_SESSION['user_id'];

// Check for empty cart
if (empty($_SESSION['cart'])) {
    http_response_code(400);
    $response['message'] = "Cart is empty. Cannot checkout.";
    echo json_encode($response);
    exit;
}

// Get data from React frontend
$data = json_decode(file_get_contents("php://input"), true);
$method = isset($data['method']) ? $data['method'] : null;
$address = isset($data['address']) ? $data['address'] : null;

if (empty($method) || empty($address)) {
    http_response_code(400);
    $response['message'] = "Payment method and address are required.";
    echo json_encode($response);
    exit;
}

$grandTotal = 0;
$checkoutItems = []; // Array to store final, validated item data

try {
    // --- 1. Security Check: Recalculate and Validate Total on Server ---
    foreach ($_SESSION['cart'] as $index => $item) {
        // Fetch latest prices/details from DB (Crucial security step)
        $stmt = $conn->prepare("SELECT price, discount_type, discount_value FROM lunchboxes WHERE id = ?");
        $stmt->execute([$item['lunchbox_id']]);
        $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT duration_days, discount_type, discount_value FROM plans WHERE id = ?");
        $stmt->execute([$item['plan_id']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lunchbox || !$plan) {
            throw new Exception("One or more items in your cart is invalid and must be removed.");
        }

        // Calculate secure final price (Matching logic from cart_data.php)
        $basePricePerUnit = $lunchbox['price'] * ($plan['duration_days'] / 30);
        $discountedPrice = applyDiscount($basePricePerUnit, $lunchbox['discount_type'], $lunchbox['discount_value']);
        $discountedPrice = applyDiscount($discountedPrice, $plan['discount_type'], $plan['discount_value']);
        $discountedPrice = round($discountedPrice, 2);

        $lineTotal = $discountedPrice * $item['quantity'];
        $grandTotal += $lineTotal;

        // Store validated item for later transaction use
        $checkoutItems[] = [
            'lunchbox_id' => $item['lunchbox_id'],
            'plan_id'     => $item['plan_id'],
            'quantity'    => $item['quantity'],
            'price'       => $discountedPrice,
            'line_total'  => round($lineTotal, 2)
        ];
    }
    
    $grandTotal = round($grandTotal, 2);
    $status = ($method === 'cash') ? 'pending' : 'paid';

    // --- 2. Database Transaction ---
    $conn->beginTransaction();

    // Insert checkout record
    $stmt = $conn->prepare("
        INSERT INTO checkouts (user_id, total_amount, status)
        VALUES (:user_id, :total_amount, :status)
    ");
    $stmt->execute([
        ':user_id'      => $user_id,
        ':total_amount' => $grandTotal,
        ':status'       => $status
    ]);
    $checkoutId = $conn->lastInsertId();

    // Insert subscriptions for each item
    foreach ($checkoutItems as $item) {
        // Create a separate subscription for each unit purchased
        for ($i = 0; $i < $item['quantity']; $i++) {
            $stmt = $conn->prepare("
                INSERT INTO subscriptions 
                    (user_id, lunchbox_id, plan_id, start_date, end_date, status, checkout_id, address)
                VALUES 
                    (:user_id, :lunchbox_id, :plan_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :duration DAY), 'active', :checkout_id, :address)
            ");
            
            // Re-fetch duration for accurate end_date calculation
            $durationStmt = $conn->prepare("SELECT duration_days FROM plans WHERE id = ?");
            $durationStmt->execute([$item['plan_id']]);
            $durationDays = $durationStmt->fetchColumn() ?: 30; // Default to 30 days if plan detail is missed
            
            $stmt->execute([
                ':user_id'      => $user_id,
                ':lunchbox_id'  => $item['lunchbox_id'],
                ':plan_id'      => $item['plan_id'],
                ':duration'     => $durationDays,
                ':checkout_id'  => $checkoutId,
                ':address'      => $address
            ]);
        }
    }

    // Insert payment record
    $stmt = $conn->prepare("
        INSERT INTO payments (user_id, checkout_id, amount, method, status, transaction_id, paid_at)
        VALUES (:user_id, :checkout_id, :amount, :method, :status, :txid, NOW())
    ");
    $stmt->execute([
        ':user_id'      => $user_id,
        ':checkout_id'  => $checkoutId,
        ':amount'       => $grandTotal,
        ':method'       => $method,
        ':status'       => $status,
        ':txid'         => uniqid("TXN")
    ]);

    $conn->commit();

    // Clear cart session only after successful transaction
    $_SESSION['cart'] = [];

    // --- 3. Final Response to React ---
    http_response_code(200);
    $response['success'] = true;
    $response['message'] = 'Order placed successfully.';
    $response['data'] = [
        'checkout_id' => $checkoutId,
        'total' => $grandTotal,
        'status' => $status
    ];

} catch (Exception $e) {
    // Catch validation and transaction errors
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    $response['message'] = 'Checkout failed: ' . $e->getMessage();
} catch (PDOException $e) {
    // Catch database errors
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    $response['message'] = 'Database error during checkout.';
}

echo json_encode($response);
?>