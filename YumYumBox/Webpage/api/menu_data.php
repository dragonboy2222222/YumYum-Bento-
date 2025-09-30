<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
require_once "../dbconnect.php";

$response = [
    'success' => false,
    'message' => 'An unexpected error occurred.',
    'data' => null
];

try {
    // --- Data for Navbar (as per homepage.api.php) ---
    $is_logged_in = isset($_SESSION["username"]);
    $username = null;
    $profilePic = "http://localhost:3000/Webpage/productImage/profilesample.jpg"; 
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

    if ($is_logged_in) {
        $username = $_SESSION["username"];
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user["id"];
            $stmt = $conn->prepare("SELECT profile_image FROM profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($profile && !empty($profile["profile_image"])) {
                $profilePic = "/Webpage/" . $profile["profile_image"];
            }
        } else {
            session_destroy();
            $is_logged_in = false;
        }
    }
    
    // Fetch all lunchboxes for navbar dropdowns
    $stmt = $conn->prepare("SELECT id, name FROM lunchboxes ORDER BY id DESC");
    $stmt->execute();
    $allLunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // --- Data for Main Content ---
    $lunchboxId = isset($_GET['lunchbox_id']) ? (int)$_GET['lunchbox_id'] : 0;
    $menus = [];
    $lunchboxName = "";

    if ($lunchboxId > 0) {
        // Get specific lunchbox name and menus
        $stmt = $conn->prepare("SELECT name FROM lunchboxes WHERE id = ?");
        $stmt->execute([$lunchboxId]);
        $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lunchbox) {
            $lunchboxName = $lunchbox['name'];
            $stmt = $conn->prepare("SELECT id, name, description, image, lunchbox_id FROM menus WHERE lunchbox_id = ? ORDER BY id DESC");
            $stmt->execute([$lunchboxId]);
            $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Get all menus
        $lunchboxName = "All Lunchboxes";
        $stmt = $conn->prepare("SELECT m.id, m.name, m.description, m.image, m.lunchbox_id, l.name AS lunchbox_name 
                                FROM menus m
                                JOIN lunchboxes l ON m.lunchbox_id = l.id
                                ORDER BY m.id DESC");
        $stmt->execute();
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Build the final response array
    $response['success'] = true;
    $response['message'] = 'Menu data fetched successfully.';
    $response['data'] = [
        'nav' => [
            'isLoggedIn' => $is_logged_in,
            'user' => $is_logged_in ? [
                'username' => $username,
                'profilePic' => $profilePic
            ] : null,
            'cartCount' => $cartCount,
            'lunchboxes' => $allLunchboxes
        ],
        'mainContent' => [
            'lunchboxName' => $lunchboxName,
            'menus' => $menus
        ]
    ];
    http_response_code(200);

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);