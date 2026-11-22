
<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../config/database.php";

$error = "";

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Identify the visitor using a hashed IP address
$ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";
$ipHash = hash("sha256", $ipAddress);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $error = "Invalid request. Please refresh the page.";

    } else {

        // Remove old login-attempt records
        $cleanup = $pdo->prepare(
            "DELETE FROM admin_login_attempts
             WHERE attempted_at < (NOW() - INTERVAL 1 DAY)"
        );
        $cleanup->execute();

        // Count failed attempts from this IP in the last 15 minutes
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM admin_login_attempts
             WHERE ip_hash = :ip_hash
             AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)"
        );

        $stmt->execute([":ip_hash" => $ipHash]);
        $attempts = (int) $stmt->fetchColumn();

        if ($attempts >= 5) {
            $error = "Too many attempts. Please try again after 15 minutes.";

        } else {

            $email = trim($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";

            if (
                strlen($email) > 190 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($password) > 4096
            ) {
                $error = "Incorrect email or password.";

            } else {

                $stmt = $pdo->prepare(
                    "SELECT id, email, password_hash
                     FROM admins
                     WHERE email = :email
                     LIMIT 1"
                );

                $stmt->execute([":email" => $email]);
                $admin = $stmt->fetch();

                if (
                    $admin &&
                    password_verify($password, $admin["password_hash"])
                ) {

                    // Clear failed attempts after successful login
                    $delete = $pdo->prepare(
                        "DELETE FROM admin_login_attempts
                         WHERE ip_hash = :ip_hash"
                    );
                    $delete->execute([":ip_hash" => $ipHash]);

                    session_regenerate_id(true);

                    $_SESSION["admin_id"] = $admin["id"];
                    $_SESSION["admin_email"] = $admin["email"];
                    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                    header("Location: dashboard.php");
                    exit;

                } else {

                    // Record failed login attempt
                    $insert = $pdo->prepare(
                        "INSERT INTO admin_login_attempts (ip_hash)
                         VALUES (:ip_hash)"
                    );

                    $insert->execute([":ip_hash" => $ipHash]);

                    $error = "Incorrect email or password.";
                }
            }
        }
    }
}

$pageTitle = "Admin Login | Jeremiah.dev";
$isAdminPage = true;

require __DIR__ . "/../includes/header.php";
?>

<main class="section container narrow">

    <div class="section-heading">
        <p class="eyebrow">PRIVATE AREA</p>
        <h1>Admin Login</h1>
        <p>Sign in to manage your portfolio.</p>
    </div>

    <?php if ($error): ?>
        <p class="notice error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form class="contact-form" method="POST" action="">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="email">Admin Email</label>
        <input
            id="email"
            name="email"
            type="email"
            maxlength="190"
            autocomplete="username"
            required
        >

        <label for="password">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
        >

        <button class="button" type="submit">
            Log In
        </button>

    </form>

</main>

<?php require __DIR__ . "/../includes/footer.php"; ?>
