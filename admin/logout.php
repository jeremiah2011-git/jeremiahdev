
<?php
require __DIR__ . "/../includes/session.php";

// Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit("Method not allowed.");
}

// Validate CSRF token
if (
    !isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    http_response_code(403);
    exit("Invalid request. Please refresh the page.");
}

// Clear all session variables
$_SESSION = [];

// Expire the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"] ?? "Lax"
        ]
    );
}

// Destroy the session
session_destroy();

// Return to login page
header("Location: login.php");
exit;
