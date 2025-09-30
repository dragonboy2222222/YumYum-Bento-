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
    'data' => []
];

$is_logged_in = isset($_SESSION["username"]);
$username = null;
$user_id = null;
// Correct public path for the default image based on your file structure
$profilePic = "http://localhost:3000/Webpage/productImage/profilesample.jpg"; 
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$user = null;

if ($is_logged_in) {
    $username = $_SESSION["username"];
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user["id"];
            $stmt = $conn->prepare("SELECT profile_image FROM profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($profile && !empty($profile["profile_image"])) {
                // This line converts the database path to a public URL
                $profilePic = "/Webpage/" . $profile["profile_image"];
            }
        } else {
            session_destroy();
            $is_logged_in = false;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $response['message'] = 'Database error fetching user data: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    // Cart count is already correctly fetched here
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

$lunchboxes = [];
try {
    $stmt = $conn->prepare("SELECT id, name FROM lunchboxes ORDER BY id DESC");
    $stmt->execute();
    $lunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'message' => 'Home data fetched successfully.',
        'data' => [
            'nav' => [
                'isLoggedIn' => $is_logged_in,
                'user' => $is_logged_in ? [
                    'username' => $username,
                    'profilePic' => $profilePic
                ] : null,
                'cartCount' => $cartCount,
                'lunchboxes' => $lunchboxes,
            ],
        ]
    ];

    http_response_code(200);

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error fetching lunchboxes: ' . $e->getMessage();
}

echo json_encode($response);
?>
