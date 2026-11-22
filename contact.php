
<?php
require __DIR__ . "/includes/session.php";

require __DIR__ . "/config/database.php";

// Accept POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#contact");
    exit;
}
// Honeypot spam protection
if (!empty($_POST["website"])) {
    header("Location: index.php#contact");
    exit;
}
// Verify CSRF token
$sessionToken = $_SESSION["csrf_token"] ?? null;
$submittedToken = $_POST["csrf_token"] ?? null;

if (
    !is_string($sessionToken) ||
    $sessionToken === "" ||
    !is_string($submittedToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    $_SESSION["flash"] = "Invalid request. Please refresh the page and try again.";
    $_SESSION["flash_type"] = "error";
    header("Location: index.php#contact");
    exit;
}


// Collect form data
$name = is_string($_POST["name"] ?? null) ? trim($_POST["name"]) : "";
$email = is_string($_POST["email"] ?? null) ? trim($_POST["email"]) : "";
$subject = is_string($_POST["subject"] ?? null) ? trim($_POST["subject"]) : "";
$message = is_string($_POST["message"] ?? null) ? trim($_POST["message"]) : "";

// Validate input
if (
    $name === "" ||
    strlen($name) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 190 ||
    $subject === "" ||
    strlen($subject) > 150 ||
    $message === "" ||
    strlen($message) > 10000
) {
    $_SESSION["flash"] = "Please check your details and try again.";
    $_SESSION["flash_type"] = "error";
    header("Location: index.php#contact");
    exit;
}
// Identify visitor using their connection IP
$visitorIp = $_SERVER["REMOTE_ADDR"] ?? "unknown";
$ipHash = hash("sha256", $visitorIp);

// Count successful submissions in the last 10 minutes
$rateStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM contact_rate_limits
     WHERE ip_hash = :ip_hash
     AND submitted_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
);

$rateStmt->execute([":ip_hash" => $ipHash]);

if ((int) $rateStmt->fetchColumn() >= 3) {
    $_SESSION["flash"] = "Too many messages. Please try again in 10 minutes.";
    $_SESSION["flash_type"] = "error";
    header("Location: index.php#contact");
    exit;
}
// Save message to database
try {
    $stmt = $pdo->prepare(
        "INSERT INTO messages (name, email, subject, message)
         VALUES (:name, :email, :subject, :message)"
    );

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":subject" => $subject,
        ":message" => $message
    ]);
// Record successful contact submission
// Record successful submission in rate-limit table
$recordStmt = $pdo->prepare(
    "INSERT INTO contact_rate_limits (ip_hash, submitted_at)
     VALUES (:ip_hash, NOW())"
);

$recordStmt->execute([
    ":ip_hash" => $ipHash
]);
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    $_SESSION["flash"] = "Thank you! Your message has been sent successfully.";
    $_SESSION["flash_type"] = "success";

} catch (PDOException $e) {
    error_log("Contact form database error: " . $e->getMessage());

    $_SESSION["flash"] = "Sorry, your message could not be sent. Please try again.";
    $_SESSION["flash_type"] = "error";
}

header("Location: index.php#contact");
exit;
