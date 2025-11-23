<?php
// parent/children.php
include_once("../includes/db_connection.php");
include_once("../includes/auth_session.php");
include_once("../includes/header_parent.php");

// ensure dashboard styles
echo "<script>document.body.classList.add('dashboard-body');</script>";

// messages
$success_msg = "";
$error_msg = "";

// handle add-child POST from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_child') {
    $child_name = trim($_POST['child_name'] ?? "");
    $child_username = trim($_POST['child_username'] ?? "");
    $child_password = trim($_POST['child_password'] ?? "");

    if ($child_name === "" || $child_username === "" || $child_password === "") {
        $error_msg = "All fields are required.";
    } else {
        $hashed_pass = password_hash($child_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Child (parent_id, child_name, child_username, child_password) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isss", $_SESSION['parent_id'], $child_name, $child_username, $hashed_pass);
            if ($stmt->execute()) {
                $success_msg = "Child added successfully.";
            } else {
                if ($conn->errno == 1062) {
                    $error_msg = "Username already exists. Please choose another.";
                } else {
                    $error_msg = "Database error: " . htmlspecialchars($conn->error);
                }
            }
            $stmt->close();
        } else {
            $error_msg = "Database error: " . htmlspecialchars($conn->error);
        }
    }
}

// handle delete child POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_child') {
    $child_id = intval($_POST['child_id']);
    if ($child_id) {
        // ensure child belongs to this parent
        $sql = "DELETE FROM Child WHERE child_id = ? AND parent_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $child_id, $_SESSION['parent_id']);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success_msg = "Child removed successfully.";
                } else {
                    $error_msg = "Child not found or you don't have permission to delete.";
                }
            } else {
                $error_msg = "Database error while deleting child.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database error: " . htmlspecialchars($conn->error);
        }
    } else {
        $error_msg = "Invalid child id.";
    }
}

// Fetch children for this parent
$children = [];
$sql = "SELECT child_id, child_name, child_username, child_points, created_at FROM Child WHERE parent_id = ? ORDER BY created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['parent_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        // Fetch active task count for this child
        $active_count = 0;
        $q2 = "SELECT COUNT(*) FROM Task WHERE child_id = ? AND task_status = 'pending'";
        if ($s2 = $conn->prepare($q2)) {
            $s2->bind_param("i", $r['child_id']);
            $s2->execute();
            $s2->bind_result($active_count);
            $s2->fetch();
            $s2->close();
        }
        $r['active_tasks'] = intval($active_count);
        $children[] = $r;
    }
    $stmt->close();
}
?>

<div class="parent-content">

    <div class="children-header">
        <h2 class="page-heading">My Children</h2>

        <!-- Add Child button (opens modal) -->
        <button class="btn add-child-btn" id="openAddChildModal">Add Child</button>
    </div>

    <!-- Messages -->
    <?php if ($success_msg): ?>
        <div class="alert success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert error"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <!-- Children grid -->
    <div class="children-grid">
        <?php if (count($children) === 0): ?>
            <div class="card" style="text-align:center; padding:40px;">
                <p style="color:#666;">No children yet. Click <strong>Add Child</strong> to create one.</p>
            </div>
        <?php else: ?>
            <?php foreach ($children as $c): ?>
                <div class="child-card card">
                    <div class="child-top">
                        <div class="child-emoji">👶</div>
                        <div class="child-name"><?php echo htmlspecialchars($c['child_name']); ?></div>
                        <div class="child-username">@<?php echo htmlspecialchars($c['child_username']); ?></div>
                    </div>

                    <div class="child-stats">
    <div class="stat">
        <div class="stat-value"><?php echo intval($c['child_points']); ?></div>
        <div class="stat-label">Points</div>
    </div>

    <div class="stat">
        <div class="stat-value"><?php echo intval($c['active_tasks']); ?></div>
        <div class="stat-label">Active Tasks</div>
    </div>
</div>


                    <div class="child-actions">
    <form method="POST" class="inline-delete-form" 
          onsubmit="return confirmDeleteChild(event, <?php echo intval($c['child_id']); ?>);">
        <input type="hidden" name="action" value="delete_child">
        <input type="hidden" name="child_id" value="<?php echo intval($c['child_id']); ?>">
        <button type="submit" class="btn danger">Remove</button>
    </form>
</div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Child Modal -->
<div id="addChildModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" id="closeAddChildModal">&times;</button>
        <h3 style="color:#2F4B8A; margin-bottom:10px;">Add Child</h3>

        <form method="POST" id="addChildForm">
            <input type="hidden" name="action" value="add_child">

            <label>Child Name</label>
            <input type="text" name="child_name" placeholder="e.g., Alice Doe" required>

            <label>Child Username</label>
            <input type="text" name="child_username" placeholder="unique username" required>

            <label>Child Password</label>
            <input type="password" name="child_password" placeholder="password" required>

            <div style="display:flex;gap:10px;margin-top:12px;">
                <button type="submit" class="btn">Add Child</button>
                <button type="button" class="btn outline" id="cancelAddChild">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/children.js"></script>

<?php include_once("../includes/footer.php"); ?>
