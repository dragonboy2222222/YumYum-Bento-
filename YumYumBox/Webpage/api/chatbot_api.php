<?php
// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Cookie");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
// Use the same dbconnect file as your other APIs
require_once("../dbconnect.php"); 

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Method not allowed.']);
    exit;
}

// Get the JSON data sent from the React component
$data = json_decode(file_get_contents("php://input"));

// Check if a message was provided
if (!isset($data->message) || empty(trim($data->message))) {
    http_response_code(400);
    echo json_encode(['reply' => 'Please provide a message.']);
    exit;
}

// --- 1. Fetch User Data for Personalization ---

$userMessage = strtolower(trim($data->message));
$greetingName = "Hello there";
$lastSubscription = null;
$isLoggedIn = isset($_SESSION["username"]);

if ($isLoggedIn) {
    try {
        // Fetch user ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$_SESSION["username"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $user['id'] ?? null;

        if ($user_id) {
            // Fetch User's Full Name from profiles table
            $stmt = $conn->prepare("SELECT full_name FROM profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($profile && !empty($profile['full_name'])) {
                // Use the first part of the name for a friendly greeting
                $nameParts = explode(' ', trim($profile['full_name']));
                $greetingName = "Hello, " . ucfirst(strtolower($nameParts[0]));
            }

            // Fetch Last Subscription Info
            $sql_subs = "SELECT s.id, b.name AS lunchbox_name, s.start_date, s.duration_days FROM subscriptions s JOIN lunchboxes b ON s.lunchbox_id = b.id WHERE s.user_id = ? ORDER BY s.start_date DESC LIMIT 1";
            $stmt_subs = $conn->prepare($sql_subs);
            $stmt_subs->execute([$user_id]);
            $lastSubscription = $stmt_subs->fetch(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        // Log the error but continue with non-personalized response
        error_log("Chatbot DB Error: " . $e->getMessage());
        $greetingName = "Hello there"; // Fallback
    }
}

// --- 2. Simple Rule-Based Chatbot Logic ---

$reply = '';

// Personalized Response: Greeting
if (str_contains($userMessage, 'hello') || str_contains($userMessage, 'hi') || str_contains($userMessage, 'hey')) {
    if ($lastSubscription) {
        $reply = "$greetingName! Welcome back! I see your last order was the **{$lastSubscription['lunchbox_name']}** subscription. Can I help you renew, or do you have another question?";
    } else if ($isLoggedIn) {
        $reply = "$greetingName! I'm your YumYum Bento assistant. How can I help you find the perfect lunchbox today?";
    } else {
        $reply = "Hello there! I'm your YumYum Bento assistant. I can answer questions about our plans, menus, or delivery. Try clicking one of the buttons below! 👇";
    }
} 
// Personalized Response: Subscriptions/Plans (If they ask about their order)
else if ($lastSubscription && (str_contains($userMessage, 'order') || str_contains($userMessage, 'my plan') || str_contains($userMessage, 'my subscription'))) {
    $date = date('F j, Y', strtotime($lastSubscription['start_date']));
    $reply = "$greetingName, your last subscription was for the **{$lastSubscription['lunchbox_name']}** and started on {$date} for {$lastSubscription['duration_days']} days. You can view full details in your Profile. How else can I assist?";
}
// General Keywords for Subscription/Plans
else if (str_contains($userMessage, 'subscribe') || str_contains($userMessage, 'plan') || str_contains($userMessage, 'work')) {
    $reply = "Our subscription model is simple! You choose your **Lunchbox category**, then select a plan (30, 60, or 90 days). We deliver freshly prepared meals on your schedule.";
} 
// General Keywords for Delivery
else if (str_contains($userMessage, 'delivery') || str_contains($userMessage, 'area') || str_contains($userMessage, 'deliver')) {
    $reply = "We currently deliver across **all major metropolitan areas**. You can check the specific delivery zones on our checkout page. 🚚";
} 
// General Keywords for Customization/Meals
else if (str_contains($userMessage, 'customize') || str_contains($userMessage, 'meal') || str_contains($userMessage, 'menu')) {
    $reply = "While we can't customize individual ingredients, we offer six different **Lunchbox categories** with dynamic menus to fit various tastes and dietary needs!";
}
// General Keywords for Cancellation
else if (str_contains($userMessage, 'cancel') || str_contains($userMessage, 'stop')) {
    $reply = "You can easily cancel or pause your subscription directly through your **Profile** management page under 'Subscription Details'.";
}
// Default Fallback
else {
    $reply = "I'm sorry, I don't understand that question. Try asking about **plans**, **delivery**, **cancellation**, or just say **hello**!";
}

// Simulate a brief processing time 
usleep(500000); // 0.5 seconds delay

http_response_code(200);
echo json_encode(['reply' => $reply]);
?>