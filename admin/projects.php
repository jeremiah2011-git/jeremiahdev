<?php
session_start();
if (!isset($_SESSION["admin_id"])) { header("Location: login.php"); exit; }
require __DIR__ . "/../config/database.php";

if (empty($_SESSION["csrf_token"])) $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
$csrf = $_SESSION["csrf_token"];
$notice = "";
$noticeType = "success";
$uploadDir = __DIR__ . "/../assets/uploads/";
$uploadWeb = "assets/uploads/";

function cleanProjectInput(string $value, int $max): string {
    $value = trim($value);
    return mb_substr($value, 0, $max);
}
function validExternalUrl(string $url): bool {
    return $url === "" || (filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ["http", "https"], true));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($csrf, $_POST["csrf_token"] ?? "")) {
        $notice = "Your session token expired. Refresh the page and try again.";
        $noticeType = "error";
    } else {
        $action = $_POST["action"] ?? "";
        if ($action === "delete") {
            $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
            if ($id) {
                $find = $pdo->prepare("SELECT image_path FROM projects WHERE id = ?");
                $find->execute([$id]);
                $old = $find->fetch();
                $del = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $del->execute([$id]);
                if ($old && !empty($old["image_path"])) {
                    $oldFile = realpath(__DIR__ . "/../" . $old["image_path"]);
                    $uploadReal = realpath($uploadDir);
                    if ($oldFile && $uploadReal && strpos($oldFile, $uploadReal . DIRECTORY_SEPARATOR) === 0 && is_file($oldFile)) @unlink($oldFile);
                }
                $notice = "Project deleted.";
            }
        } elseif ($action === "save") {
            $id = (int)($_POST["id"] ?? 0);
            $title = cleanProjectInput($_POST["title"] ?? "", 150);
            $description = trim($_POST["description"] ?? "");
            $tech = cleanProjectInput($_POST["tech_stack"] ?? "", 500);
            $url = trim($_POST["project_url"] ?? "");
            $sort = (int)($_POST["sort_order"] ?? 0);
            $published = isset($_POST["is_published"]) ? 1 : 0;
            $errors = [];
            if ($title === "") $errors[] = "Project title is required.";
            if ($description === "" || mb_strlen($description) > 5000) $errors[] = "Description is required and must be under 5,000 characters.";
            if (!validExternalUrl($url)) $errors[] = "Project URL must be a valid http or https URL.";
            $imagePath = null;
            $existingImage = trim($_POST["existing_image"] ?? "");
            if (!empty($_FILES["project_image"]["name"])) {
                if ($_FILES["project_image"]["error"] !== UPLOAD_ERR_OK) $errors[] = "Image upload failed.";
                elseif ($_FILES["project_image"]["size"] > 3 * 1024 * 1024) $errors[] = "Image must be 3 MB or smaller.";
                else {
                    $info = @getimagesize($_FILES["project_image"]["tmp_name"]);
                    $mime = $info["mime"] ?? "";
                    $extensions = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
                    if (!$info || !isset($extensions[$mime])) $errors[] = "Use a JPG, PNG, or WEBP image.";
                    else {
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        $filename = bin2hex(random_bytes(16)) . "." . $extensions[$mime];
                        if (move_uploaded_file($_FILES["project_image"]["tmp_name"], $uploadDir . $filename)) {
                            $imagePath = $uploadWeb . $filename;
                        } else $errors[] = "Could not save the uploaded image.";
                    }
                }
            }
            if ($errors) {
                $notice = implode(" ", $errors);
                $noticeType = "error";
            } else {
                if ($id > 0) {
                    $sql = "UPDATE projects SET title=?, description=?, tech_stack=?, project_url=?, sort_order=?, is_published=?";
                    $params = [$title, $description, $tech, $url ?: null, $sort, $published];
                    if ($imagePath) { $sql .= ", image_path=?"; $params[] = $imagePath; }
                    $sql .= " WHERE id=?";
                    $params[] = $id;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    if ($imagePath && $existingImage) {
                        $oldFile = realpath(__DIR__ . "/../" . $existingImage);
                        $uploadReal = realpath($uploadDir);
                        if ($oldFile && $uploadReal && strpos($oldFile, $uploadReal . DIRECTORY_SEPARATOR) === 0 && is_file($oldFile)) @unlink($oldFile);
                    }
                    $notice = "Project updated.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO projects (title, description, tech_stack, image_path, project_url, sort_order, is_published) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $tech, $imagePath, $url ?: null, $sort, $published]);
                    $notice = "Project added and saved.";
                }
            }
        }
    }
}

$editProject = null;
if (isset($_GET["edit"]) && ctype_digit($_GET["edit"])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->execute([(int)$_GET["edit"]]);
    $editProject = $stmt->fetch() ?: null;
}
$projects = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC")->fetchAll();
$pageTitle = "Manage Projects | Jeremiah.dev";
require __DIR__ . "/../includes/header.php";
?>
<main class="section container">
  <div class="dashboard-heading">
    <div><p class="eyebrow">ADMINISTRATION / PORTFOLIO</p><h1>Manage projects</h1><p>Signed in as <?= htmlspecialchars($_SESSION["admin_email"], ENT_QUOTES, "UTF-8") ?></p></div>
    <div class="admin-actions"><a class="button button-outline" href="dashboard.php">Messages</a><a class="button button-outline" href="logout.php">Log out</a></div>
  </div>
  <?php if ($notice): ?><div class="notice <?= $noticeType === "error" ? "error" : "" ?>"><?= htmlspecialchars($notice, ENT_QUOTES, "UTF-8") ?></div><?php endif; ?>
  <div class="admin-form-panel">
    <div class="section-heading"><p class="eyebrow"><?= $editProject ? "EDIT EXISTING" : "ADD SOMETHING NEW" ?></p><h2><?= $editProject ? "Update project" : "Add a project" ?></h2></div>
    <form class="contact-form admin-project-form" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8") ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)($editProject["id"] ?? 0) ?>">
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProject["image_path"] ?? "", ENT_QUOTES, "UTF-8") ?>">
      <label for="title">Project title *</label><input id="title" name="title" maxlength="150" required value="<?= htmlspecialchars($editProject["title"] ?? "", ENT_QUOTES, "UTF-8") ?>">
      <label for="description">Description *</label><textarea id="description" name="description" rows="4" maxlength="5000" required><?= htmlspecialchars($editProject["description"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
      <label for="tech_stack">Technologies (comma separated)</label><input id="tech_stack" name="tech_stack" maxlength="500" placeholder="PHP, MySQL, JavaScript" value="<?= htmlspecialchars($editProject["tech_stack"] ?? "", ENT_QUOTES, "UTF-8") ?>">
      <label for="project_url">Project URL (optional)</label><input id="project_url" name="project_url" type="url" maxlength="500" placeholder="https://example.com" value="<?= htmlspecialchars($editProject["project_url"] ?? "", ENT_QUOTES, "UTF-8") ?>">
      <label for="project_image">Project image (JPG, PNG, WEBP; max 3 MB)</label><input id="project_image" name="project_image" type="file" accept="image/jpeg,image/png,image/webp">
      <?php if (!empty($editProject["image_path"])): ?><p class="small-note">Current image: <?= htmlspecialchars(basename($editProject["image_path"]), ENT_QUOTES, "UTF-8") ?></p><?php endif; ?>
      <div class="form-row">
        <div><label for="sort_order">Display order (smaller first)</label><input id="sort_order" name="sort_order" type="number" value="<?= (int)($editProject["sort_order"] ?? 0) ?>"></div>
        <div class="publish-check"><label><input type="checkbox" name="is_published" value="1" <?= !isset($editProject["is_published"]) || (int)$editProject["is_published"] === 1 ? "checked" : "" ?>> Publish on homepage</label></div>
      </div>
      <div class="admin-actions"><button class="button" type="submit"><?= $editProject ? "Save changes" : "Add project" ?></button><?php if ($editProject): ?><a class="button button-outline" href="projects.php">Cancel edit</a><?php endif; ?></div>
    </form>
  </div>

  <div class="section-heading admin-list-heading"><p class="eyebrow">YOUR PORTFOLIO CONTENT</p><h2>Existing projects <span>(<?= count($projects) ?>)</span></h2></div>
  <?php if (!$projects): ?><p class="notice">No projects yet. Add your first project using the form above.</p><?php else: ?>
    <div class="admin-project-list">
      <?php foreach ($projects as $project): ?>
        <article class="admin-project-row">
          <div class="admin-project-thumb"><?php if ($project["image_path"]): ?><img src="../<?= htmlspecialchars($project["image_path"], ENT_QUOTES, "UTF-8") ?>" alt=""><?php else: ?><span>J.</span><?php endif; ?></div>
          <div class="admin-project-info"><h3><?= htmlspecialchars($project["title"], ENT_QUOTES, "UTF-8") ?></h3><p><?= htmlspecialchars(mb_strimwidth($project["description"], 0, 130, "…"), ENT_QUOTES, "UTF-8") ?></p><div class="tags"><span><?= (int)$project["is_published"] ? "Published" : "Draft" ?></span><span>Order: <?= (int)$project["sort_order"] ?></span></div></div>
          <div class="admin-actions"><a class="button button-outline" href="projects.php?edit=<?= (int)$project["id"] ?>">Edit</a><form method="POST" onsubmit="return confirm('Delete this project permanently?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8") ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$project["id"] ?>"><button class="button button-danger" type="submit">Delete</button></form></div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . "/../includes/footer.php"; ?>
