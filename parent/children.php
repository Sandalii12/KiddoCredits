<?php
include_once("../includes/db_connection.php");
include_once("../includes/auth_session.php");
include_once("../includes/header_parent.php");

// apply dashboard background
echo "<script>document.body.classList.add('dashboard-body');</script>";

$success_msg = "";
$error_msg = "";

$parent_id = $_SESSION['parent_id'] ?? null;
if (!$parent_id) {
    header("Location: ../auth/login.php");
    exit;
}

/* ======================
   ADD CHILD HANDLER
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'add_child') {

    $child_name = trim($_POST['child_name'] ?? '');
    $child_username = trim($_POST['child_username'] ?? '');
    $child_password = trim($_POST['child_password'] ?? '');

    if ($child_name === "" || $child_username === "" || $child_password === "") {
        $error_msg = "All fields are required.";
    } else {
        $hashed_pass = password_hash($child_password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Child (parent_id, child_name, child_username, child_password, child_points) 
                VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $parent_id, $child_name, $child_username, $hashed_pass);

        if ($stmt->execute()) {
            $success_msg = "Child added successfully.";
        } else {
            $error_msg = "Username already exists or database error.";
        }
        $stmt->close();
    }
}

/* ======================
    REMOVE CHILD
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_child') {
    $child_id = intval($_POST['child_id']);

    $sql = "DELETE FROM Child WHERE child_id = ? AND parent_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $child_id, $parent_id);
    $stmt->execute();
    $stmt->close();

    $success_msg = "Child removed.";
}

/* ======================
   FETCH CHILDREN
====================== */
$children = [];
$sql = "SELECT * FROM Child WHERE parent_id = ? ORDER BY child_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}
$stmt->close();
?>

<div class="children-header">
    <h2 class="page-heading">My Children</h2>

    <!-- BUTTON THAT TRIGGERS SIDECARD -->
    <button id="openAddChild" class="btn add-child-btn">Add Child</button>
</div>

<?php if ($success_msg): ?>
    <div class="alert success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="children-grid">

<?php if (empty($children)): ?>

    <div class="card" style="text-align:center;padding:40px;">
        No children yet. Click <strong>Add Child</strong>.
    </div>

<?php else: ?>

    <?php foreach ($children as $child): ?>
    <div class="child-card">

        <div class="child-top">
            <div class="child-emoji">👶</div>
            <div class="child-name"><?= htmlspecialchars($child['child_name']) ?></div>
            <div class="child-username">@<?= htmlspecialchars($child['child_username']) ?></div>
        </div>

        <div class="child-stats">
            <div class="stat">
                <div class="stat-value"><?= intval($child['child_points']) ?></div>
                <div class="stat-label">Points</div>
            </div>
        </div>

        <div class="child-actions">
            <form method="POST">
                <input type="hidden" name="action" value="delete_child">
                <input type="hidden" name="child_id" value="<?= $child['child_id'] ?>">
                <button class="btn danger" onclick="return confirm('Remove this child?')">Remove</button>
            </form>
        </div>

    </div>
    <?php endforeach; ?>

<?php endif; ?>

</div>

<?php include_once("../includes/footer.php"); ?>

<!-- Load scripts AFTER footer so DOM + sidecard exists -->
<script src="../js/sidecard.js"></script>
<script src="../js/children.js"></script>

