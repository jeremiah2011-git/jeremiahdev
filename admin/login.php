
<?php
require __DIR__ . "/../includes/session.php";

require __DIR__ . "/../config/database.php";

$error = "";

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Basic session-based login attempt protection
if (!isset($_SESSION["login_attempts"])) {
    $_SESSION["login_attempts"] = 0;
}

if (!isset($_SESSION["login_locked_until"])) {
    $_SESSION["login_locked_until"] = 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (time() < $_SESSION["login_locked_until"]) {
        $error = "Too many attempts. Please try again later.";
    } elseif (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $error = "Invalid request. Please refresh the page.";
    } else {

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if (
            strlen($email) > 190 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($password) > 4096
        ) {
            $error = "Incorrect email or password.";
            $_SESSION["login_attempts"]++;
        } else {

            $stmt = $pdo->prepare(
                "SELECT id, email, password_hash
                 FROM admins
                 WHERE email = :email
                 LIMIT 1"
            );

            $stmt->execute([":email" => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin["password_hash"])) {

                session_regenerate_id(true);

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_email"] = $admin["email"];

                unset(
                    $_SESSION["login_attempts"],
                    $_SESSION["login_locked_until"]
                );

                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                header("Location: dashboard.php");
                exit;

            } else {
                $_SESSION["login_attempts"]++;

                if ($_SESSION["login_attempts"] >= 5) {
                    $_SESSION["login_locked_until"] = time() + 900;
                    $_SESSION["login_attempts"] = 0;
                }

                $error = "Incorrect email or password.";
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





