<?php
// lunchbox_data.php

header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
require_once "../dbconnect.php"; // Ensure this path is correct

$response = [
    'success' => false,
    'message' => 'An unexpected error occurred.',
    'data' => null
];

// Helper function (same as in your original file)
function applyDiscount($basePrice, $discountType, $discountValue) {
    if ($discountType === 'percent' && $discountValue > 0) {
        return $basePrice - ($basePrice * ($discountValue / 100));
    } elseif ($discountType === 'amount' && $discountValue > 0) {
        return max(0, $basePrice - $discountValue);
    }
    return $basePrice;
}

try {
    // Check if lunchbox id is passed, otherwise use 4 as fallback
    $lunchbox_id = isset($_GET['id']) ? intval($_GET['id']) : 4; 

    // --- Fetch Lunchbox Details ---
    $stmt = $conn->prepare("SELECT id, name, description, price, image, discount_type, discount_value FROM lunchboxes WHERE id = ?");
    $stmt->execute([$lunchbox_id]);
    $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lunchbox) {
        http_response_code(404);
        $response['message'] = "Lunchbox not found!";
        echo json_encode($response);
        exit;
    }

    // --- Fetch Subscription Plans ---
    $plansStmt = $conn->query("SELECT id, name, duration_days, discount_type, discount_value FROM plans ORDER BY id ASC");
    $plans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate final price for each plan
    $finalPlans = [];
    foreach ($plans as $plan) {
        // Base plan price (e.g., if lunchbox price is per month, calculate based on duration_days/30)
        // **ASSUMPTION**: Lunchbox price is a monthly price. Adjust calculation if necessary.
        $planPrice = $lunchbox['price'] * ($plan['duration_days'] / 30); 

        // Apply lunchbox discount
        $planPrice = applyDiscount($planPrice, $lunchbox['discount_type'], $lunchbox['discount_value']);

        // Apply plan discount
        $finalPrice = applyDiscount($planPrice, $plan['discount_type'], $plan['discount_value']);
        
        $plan['final_price'] = round($finalPrice, 2); // Round to 2 decimal places

        $finalPlans[] = $plan;
    }


    $is_logged_in = isset($_SESSION["username"]);


    // Build the final response array
    $response['success'] = true;
    $response['message'] = 'Lunchbox data and plans fetched successfully.';
    $response['data'] = [
        // 'nav' => [ ... copy nav data structure from menu_data.php ... ], 
        'lunchbox' => $lunchbox,
        'plans' => $finalPlans,
        'isLoggedIn' => $is_logged_in,
    ];
    http_response_code(200);

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>