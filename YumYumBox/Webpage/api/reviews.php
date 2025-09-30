<?php
// customer-react/api/reviews.php

// --- CORS Configuration Headers ---
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json; charset=UTF-8");

// Allow GET for fetching reviews and POST for submitting reviews
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

header("Access-Control-Allow-Credentials: true"); 
header("Access-Control-Max-Age: 3600");

// The browser will check all headers it intends to send in the actual POST
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ---------------------------------------------------------------------

// --- CRUCIAL FIX: Handle Preflight OPTIONS Request ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit; // Stop script execution
}

// ---------------------------------------------------------------------

session_start();
require_once "../dbconnect.php"; // Adjust path as necessary

// Helper function to send JSON response and terminate
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Note: Using $_SESSION["user_id"] is recommended for consistency after a successful login/auth check
$isLoggedIn = isset($_SESSION["user_id"]);
$user_id = $isLoggedIn ? $_SESSION["user_id"] : null;


// ====================================================================
// --- Handle POST Request (Submit New Review) ---
// ====================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Authentication Check for POST
    if (!$isLoggedIn) {
        sendResponse(["success" => false, "error" => "Authentication required to submit a review."], 401);
    }

    // 2. Get data from JSON body
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Ensure rating is a valid integer between 1 and 5
    $rating = filter_var($data['rating'] ?? '', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
    $review_text = trim($data['review_text'] ?? '');

    // 3. Validation
    if ($rating === false || empty($review_text)) {
        sendResponse(["success" => false, "error" => "Please provide a valid rating (1-5) and a review."], 400);
    }

    try {
        // 4. Insert review into database
        // Use user_id from session for security
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, review_text) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $rating, $review_text])) {
            sendResponse([
                "success" => true, 
                "message" => "Thank you for your review!", 
                "id" => $conn->lastInsertId()
            ], 201);
        } else {
            sendResponse(["success" => false, "error" => "Failed to submit review. Please try again."], 500);
        }
    } catch (PDOException $e) {
        sendResponse(["success" => false, "error" => "Database error: " . $e->getMessage()], 500);
    }
}


// ====================================================================
// --- Handle GET Request (Fetch All Reviews) ---
// ====================================================================
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Fetch all reviews, joining with user and profile tables
        $stmt = $conn->prepare("
            SELECT 
                r.rating, 
                r.review_text, 
                r.created_at, 
                u.username, 
                p.profile_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN profiles p ON u.id = p.user_id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process reviews to provide full image URLs or fallback
        foreach ($reviews as &$review) {
            $imagePath = $review['profile_image'];
            if (!empty($imagePath)) {
                // Construct the full URL for the React app to access the image
                // ASSUMPTION: The 'uploads' directory is accessible via the base path
                // This path is relative to the React development server's access point 
                // which often points to the root of the webserver (`Webpage/`)
                $review['profile_image_url'] = "http://localhost:3000/Webpage/" . $imagePath;
            } else {
                $review['profile_image_url'] = "http://localhost:3000/Webpage/productImage/default-avatar.png";
            }
            unset($review['profile_image']); // Clean up internal path
        }

        sendResponse(["success" => true, "reviews" => $reviews]);
        
    } catch (PDOException $e) {
        sendResponse(["success" => false, "error" => "Failed to fetch reviews: " . $e->getMessage()], 500);
    }
}

// --- Handle Other HTTP Methods ---
sendResponse(["success" => false, "error" => "Method not allowed."], 405);
?>