<?php
session_start();
if (!isset($_SESSION["admin_id"])) { header("Location: login.php"); exit; }
require __DIR__ . "/../config/database.php";
if (empty($_SESSION["csrf_token"])) $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
$csrf = $_SESSION["csrf_token"];
$notice = $_SESSION["dashboard_notice"] ?? "";
$noticeType = $_SESSION["dashboard_notice_type"] ?? "success";

unset(
    $_SESSION["dashboard_notice"],
    $_SESSION["dashboard_notice_type"]
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($csrf, $_POST["csrf_token"] ?? "")) {
        $notice = "Your session token expired. Refresh the page and try again.";
        $noticeType = "error";
    } else {
        $action = $_POST["action"] ?? "";
        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
        if ($id && $action === "toggle_read") {
            $stmt = $pdo->prepare("UPDATE messages SET is_read = CASE WHEN is_read = 1 THEN 0 ELSE 1 END WHERE id = ?");
                        $stmt->execute([$id]);

            $_SESSION["dashboard_notice"] = "Message status updated.";
            $_SESSION["dashboard_notice_type"] = "success";

            header("Location: dashboard.php");
            exit;
            $notice = "Message status updated.";
        } elseif ($id && $action === "delete") {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
                      $stmt->execute([$id]);

            $_SESSION["dashboard_notice"] = "Message deleted.";
            $_SESSION["dashboard_notice_type"] = "success";

            header("Location: dashboard.php");
            exit;
        }
    }
}
$messages = $pdo->query("SELECT id, name, email, subject, message, created_at, is_read FROM messages ORDER BY created_at DESC")->fetchAll();
$totalMessages = count($messages);
$unreadMessages = count(array_filter($messages, fn($m) => (int)$m["is_read"] === 0));
$totalProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$pageTitle = "Admin Dashboard | Jeremiah.dev";
require __DIR__ . "/../includes/header.php";
?>
<main class="section container">
  <div class="dashboard-heading">
    <div><p class="eyebrow">ADMINISTRATION / OVERVIEW</p><h1>Welcome back.</h1><p>Signed in as <?= htmlspecialchars($_SESSION["admin_email"], ENT_QUOTES, "UTF-8") ?></p></div>
    <div class="admin-actions"><a class="button" href="projects.php">Manage projects <span aria-hidden="true">↗</span></a><a class="button button-outline" href="logout.php">Log out</a></div>
  </div>
  <?php if ($notice): ?><div class="notice <?= $noticeType === "error" ? "error" : "" ?>"><?= htmlspecialchars($notice, ENT_QUOTES, "UTF-8") ?></div><?php endif; ?>
  <div class="admin-stats">
    <article class="admin-stat"><span>Total messages</span><strong><?= $totalMessages ?></strong><small>All contact form submissions</small></article>
    <article class="admin-stat"><span>Unread messages</span><strong><?= $unreadMessages ?></strong><small>Messages waiting for review</small></article>
    <article class="admin-stat"><span>Portfolio projects</span><strong><?= $totalProjects ?></strong><small>Published and draft projects</small></article>
  </div>
  <div class="section-heading admin-list-heading"><p class="eyebrow">INBOX</p><h2>Contact messages <span>(<?= $totalMessages ?>)</span></h2></div>
  <?php if (!$messages): ?><p class="notice">No messages yet. Messages submitted through your contact form will appear here.</p><?php else: ?>
    <div class="admin-message-list">
      <?php foreach ($messages as $item): ?>
        <article class="admin-message <?= (int)$item["is_read"] === 0 ? "unread" : "" ?>">
          <div class="message-main">
            <div class="message-heading"><div><span class="message-status"><?= (int)$item["is_read"] === 0 ? "UNREAD" : "READ" ?></span><h3><?= htmlspecialchars($item["subject"], ENT_QUOTES, "UTF-8") ?></h3></div><time><?= htmlspecialchars($item["created_at"], ENT_QUOTES, "UTF-8") ?></time></div>
            <p class="message-sender"><strong><?= htmlspecialchars($item["name"], ENT_QUOTES, "UTF-8") ?></strong> · <a href="mailto:<?= htmlspecialchars($item["email"], ENT_QUOTES, "UTF-8") ?>"><?= htmlspecialchars($item["email"], ENT_QUOTES, "UTF-8") ?></a></p>
            <p class="message-body"><?= nl2br(htmlspecialchars($item["message"], ENT_QUOTES, "UTF-8")) ?></p>
          </div>
          <div class="admin-actions">
            <form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="action" value="toggle_read"><input type="hidden" name="id" value="<?= (int)$item["id"] ?>"><button class="button button-outline" type="submit"><?= (int)$item["is_read"] === 0 ? "Mark read" : "Mark unread" ?></button></form>
            <form method="POST" onsubmit="return confirm('Delete this message permanently?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$item["id"] ?>"><button class="button button-danger" type="submit">Delete</button></form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . "/../includes/footer.php"; ?>
