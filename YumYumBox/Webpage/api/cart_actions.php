<?php
// customer-react/api/cart_actions.php

// --- CORS Configuration Headers (Modified to allow OPTIONS) ---
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json; charset=UTF-8");

// **CRUCIAL FIX: Allow POST and explicitly allow OPTIONS**
header("Access-Control-Allow-Methods: POST, OPTIONS");

header("Access-Control-Allow-Credentials: true"); 
header("Access-Control-Max-Age: 3600");

// The browser will check all headers it intends to send in the actual POST
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ---------------------------------------------------------------------

// --- CRUCIAL FIX: Handle Preflight OPTIONS Request ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond to the preflight request with a 200 OK status. 
    // The headers above are all that's required for the browser to proceed.
    http_response_code(200);
    exit; // Stop script execution
}

// ---------------------------------------------------------------------

session_start();
require_once "../dbconnect.php"; // Adjust path as necessary

$response = [
    'success' => false,
    'message' => 'Invalid Request.'
];

// Must be a POST request (This check now only applies to non-OPTIONS methods)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method Not Allowed. Use POST.';
    echo json_encode($response);
    exit;
}

// Check for login
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

// Helper: Apply discount (match cart_data.php)
function applyDiscount($basePrice, $discountType, $discountValue) {
    if ($discountType === 'percent' && $discountValue > 0) {
        return $basePrice - ($basePrice * ($discountValue / 100));
    } elseif ($discountType === 'fixed' && $discountValue > 0) {
        return max(0, $basePrice - $discountValue);
    }
    return $basePrice;
}

// Get the action type from the request body
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($data['action']) ? $data['action'] : '';


try {
    switch ($action) {
        
        // --- ADD ITEM (From LunchboxPage.jsx) ---
        case 'add':
            $lunchbox_id = isset($data['lunchbox_id']) ? intval($data['lunchbox_id']) : 0;
            $plan_id     = isset($data['plan_id']) ? intval($data['plan_id']) : 0;
            $image       = isset($data['image']) ? $data['image'] : '';

            if (!$lunchbox_id || !$plan_id) {
                throw new Exception("Missing lunchbox or plan ID.");
            }

            // Fetch lunchbox
            $stmt = $conn->prepare("SELECT price, discount_type, discount_value FROM lunchboxes WHERE id = ?");
            $stmt->execute([$lunchbox_id]);
            $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

            // Fetch plan
            $stmt = $conn->prepare("SELECT duration_days, discount_type, discount_value FROM plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lunchbox || !$plan) {
                throw new Exception("Invalid lunchbox or plan configuration.");
            }

            // Recalculate and use the secure price (as in cart_data.php)
            $price = $lunchbox['price'] * ($plan['duration_days'] / 30);
            $price = applyDiscount($price, $lunchbox['discount_type'], $lunchbox['discount_value']);
            $price = applyDiscount($price, $plan['discount_type'], $plan['discount_value']);
            $price = round($price, 2);

            // Check if item already exists and update quantity
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['lunchbox_id'] == $lunchbox_id && $item['plan_id'] == $plan_id) {
                    $item['quantity'] += 1;
                    $found = true;
                    break;
                }
            }
            unset($item);

            // If not found, add new
            if (!$found) {
                $_SESSION['cart'][] = [
                    'lunchbox_id' => $lunchbox_id,
                    'plan_id'     => $plan_id,
                    'price'       => $price,
                    'image'       => $image,
                    'quantity'    => 1
                ];
            }
            
            $response['success'] = true;
            $response['message'] = 'Item added to cart successfully.';
            break;

        // --- UPDATE QUANTITY (+/-) (From CartPage.jsx) ---
        case 'update_quantity':
            $index = isset($data['index']) ? intval($data['index']) : -1;
            $change = isset($data['change']) ? $data['change'] : 0; // +1 or -1

            if (!isset($_SESSION['cart'][$index])) {
                 throw new Exception("Invalid cart item index.");
            }
            
            $_SESSION['cart'][$index]['quantity'] += $change;

            // Remove if quantity drops to 0 or below
            if ($_SESSION['cart'][$index]['quantity'] <= 0) {
                array_splice($_SESSION['cart'], $index, 1); 
                $response['message'] = 'Item removed from cart.';
            } else {
                $response['message'] = 'Cart quantity updated.';
            }
            
            // Re-index the array keys after removal if necessary
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
            
            $response['success'] = true;
            break;

        // --- REMOVE ITEM (From CartPage.jsx) ---
        case 'remove':
            $index = isset($data['index']) ? intval($data['index']) : -1;
            
            if (!isset($_SESSION['cart'][$index])) {
                 throw new Exception("Invalid cart item index.");
            }
            
            array_splice($_SESSION['cart'], $index, 1);
            // Re-index the array keys after removal
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
            
            $response['success'] = true;
            $response['message'] = 'Item removed successfully.';
            break;
            
        // --- CLEAR CART (From CartPage.jsx) ---
        case 'clear':
            $_SESSION['cart'] = [];
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully.';
            break;

        default:
            http_response_code(400);
            $response['message'] = 'Unknown action.';
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>