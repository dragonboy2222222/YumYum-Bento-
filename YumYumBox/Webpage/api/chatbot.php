<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ DB connection
require_once "../dbconnect.php";

// Read user input
$input = json_decode(file_get_contents("php://input"), true);
$userMessage = strtolower(trim($input["message"] ?? ""));
$reply = "Sorry, I didn’t understand that. Can you try again? 😊";
$buttons = ["YumYum Bento", "Track Delivery"];

// 🔹 Get current user info
session_start();
$username = $_SESSION['username'] ?? null;
$user_id = null;
$first_name = "Friend";

if ($username) {
    try {
        $stmt = $conn->prepare("SELECT u.id, p.full_name FROM users u JOIN profiles p ON u.id = p.user_id WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $first_name = $user['full_name'] ?? "Friend";
            $user_id = $user['id'] ?? null;
        }
    } catch (PDOException $e) {
        error_log("Database error fetching user info: " . $e->getMessage());
    }
}

// 🔹 Use a switch statement for clear intent matching
switch ($userMessage) {
    case 'hi':
    case 'hello':
    case 'hey':
        $reply = "Hi $first_name! 👋 Welcome to YumYum Bento. How can I help you today?";
        $buttons = ["Lunchboxes", "My Sub", "Plans", "About Us"];
        break;
        
    case 'yumyum bento':
    case 'about us':
        $reply = "YumYum Bento is dedicated to providing delicious and healthy lunchboxes delivered right to your door. Our mission is to make mealtime easy and enjoyable for everyone!";
        $buttons = ["Lunchboxes", "My Sub", "Plans"];
        break;

    case 'plans':
    case 'view plans':
        $reply = "Our plans are based on duration: 30 days, 60 days, and 90 days. The longer the plan, the more you save!";
        $buttons = ["View Lunchboxes", "Talk to Support"];
        break;

    case 'lunchboxes':
    case 'view lunchboxes':
        try {
            $stmt = $conn->query("SELECT name, price FROM lunchboxes");
            $lunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($lunchboxes) {
                $reply = "Here are some of our delicious lunchboxes:\n";
                foreach ($lunchboxes as $lunchbox) {
                    $reply .= "• **{$lunchbox['name']}** - Price: $" . number_format($lunchbox['price'], 2) . "\n";
                }
                $buttons = ["Order a Bento", "Talk to Support"];
            } else {
                $reply = "I'm sorry, I don't have any lunchbox information at the moment.";
            }
        } catch (PDOException $e) {
            $reply = "Sorry, I'm having trouble getting lunchbox information. Please try again later.";
        }
        break;

    case 'my sub':
    case 'manage subscription':
    case 'manage my subscription':
    case 'my plan':
        if ($user_id) {
            $stmt = $conn->prepare("SELECT s.id AS subscription_id, l.name AS lunchbox_name, p.name AS plan_name, s.status, d.delivery_date
                                     FROM subscriptions s
                                     JOIN lunchboxes l ON s.lunchbox_id = l.id
                                     JOIN plans p ON s.plan_id = p.id
                                     LEFT JOIN deliveries d ON s.id = d.subscription_id
                                     WHERE s.user_id = ?
                                     ORDER BY d.delivery_date DESC LIMIT 1");
            $stmt->execute([$user_id]);
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sub) {
                $next_delivery = $sub['delivery_date'] ?? 'N/A';
                $reply = "📦 Your subscription: {$sub['plan_name']} ({$sub['lunchbox_name']}) — Status: {$sub['status']} — Next delivery: {$next_delivery}.";
                $buttons = ["Pause Sub", "Skip Delivery", "Cancel Sub"];
            } else {
                $reply = "You don’t have an active subscription yet. Would you like to start one?";
                $buttons = ["View Plans", "Order a Bento"];
            }
        } else {
            $reply = "Please log in to see your subscriptions.";
            $buttons = ["Login", "Talk to Support"];
        }
        break;

    case 'track delivery':
    case 'track my delivery':
    case 'track order':
    case 'track my order':
        $reply = "We will deliver your bento safely and for free every time!";
        $buttons = ["Find latest order", "Talk to Support"];
        break;

    case 'discounts':
    case 'promotions':
    case 'discount':
        $reply = "Exciting promotions are coming soon! Stay tuned to our social media and website for updates.";
        $buttons = ["Order a Bento", "Talk to Support"];
        break;

    case 'terms and conditions':
        $reply = "You can view our full terms and conditions on our website. Would you like me to send you the link?";
        $buttons = ["Go to T&C page", "Talk to Support"];
        break;

    case 'goodbye':
    case 'bye':
        $reply = "Goodbye! 👋 Have a great day!";
        $buttons = [];
        break;

    default:
        $reply = "Sorry, I'm still learning. You can ask me about 'Lunchboxes', 'Plans', 'My Sub', or 'About Us'.";
        $buttons = ["Lunchboxes", "My Sub", "Plans", "About Us"];
        break;
}

// Send JSON
echo json_encode([
    "reply" => $reply,
    "buttons" => $buttons
]);