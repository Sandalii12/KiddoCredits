<?php
// parent/tasks.php
// Combined Assign Task + Task List page (NO AJAX)
// UI reference image: /mnt/data/072a428b-e242-47a0-bb69-d4be535843bf.png

include_once("../includes/db_connection.php");
include_once("../includes/auth_session.php");
include_once("../includes/header_parent.php");

// ensure dashboard style
echo "<script>document.body.classList.add('dashboard-body');</script>";

// initialize messages
$success_msg = "";
$error_msg = "";

// get current parent id from session
$parent_id = $_SESSION['parent_id'] ?? null;
if (!$parent_id) {
    header("Location: ../auth/login.php");
    exit;
}

/*
  POST HANDLING (no AJAX)
  - mode = add_task  -> add a new task
  - mode = update_task -> update existing task
  - action = delete_task -> delete (via form)
  - action = complete_task -> mark completed (via form)
*/

// ADD TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] === 'add_task') {
    $child_id = intval($_POST['child_id'] ?? 0);
    $task_title = trim($_POST['task_title'] ?? '');
    $task_desc = trim($_POST['task_desc'] ?? '');
    $task_points = intval($_POST['task_points'] ?? 0);
    $task_duedate = trim($_POST['task_duedate'] ?? '');

    // basic validation
    if ($child_id <= 0 || $task_title === '' || $task_points <= 0 || $task_duedate === '') {
        $error_msg = "Please fill all required fields correctly.";
    } else {
        $sql = "INSERT INTO Task (parent_id, child_id, task_title, task_desc, task_points, task_duedate) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iissis", $parent_id, $child_id, $task_title, $task_desc, $task_points, $task_duedate);
            if ($stmt->execute()) {
                $success_msg = "Task assigned successfully.";
            } else {
                $error_msg = "Database error while inserting task.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database prepare error: " . htmlspecialchars($conn->error);
        }
    }
}

// UPDATE TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] === 'update_task') {
    $task_id = intval($_POST['task_id'] ?? 0);
    $child_id = intval($_POST['child_id'] ?? 0);
    $task_title = trim($_POST['task_title'] ?? '');
    $task_desc = trim($_POST['task_desc'] ?? '');
    $task_points = intval($_POST['task_points'] ?? 0);
    $task_duedate = trim($_POST['task_duedate'] ?? '');

    if ($task_id <= 0 || $child_id <= 0 || $task_title === '' || $task_points <= 0 || $task_duedate === '') {
        $error_msg = "Please fill all required fields correctly.";
    } else {
        $sql = "UPDATE Task SET child_id = ?, task_title = ?, task_desc = ?, task_points = ?, task_duedate = ? WHERE task_id = ? AND parent_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issisii", $child_id, $task_title, $task_desc, $task_points, $task_duedate, $task_id, $parent_id);
            if ($stmt->execute()) {
                $success_msg = "Task updated successfully.";
            } else {
                $error_msg = "Database error while updating task.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database prepare error: " . htmlspecialchars($conn->error);
        }
    }
}

// DELETE TASK (normal POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_task') {
    $task_id = intval($_POST['task_id'] ?? 0);
    if ($task_id > 0) {
        $sql = "DELETE FROM Task WHERE task_id = ? AND parent_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $task_id, $parent_id);
            if ($stmt->execute()) {
                $success_msg = ($stmt->affected_rows > 0) ? "Task removed." : "Task not found or permission denied.";
            } else {
                $error_msg = "DB error deleting task.";
            }
            $stmt->close();
        } else {
            $error_msg = "DB prepare error: " . htmlspecialchars($conn->error);
        }
    } else {
        $error_msg = "Invalid task id.";
    }
}

// MARK COMPLETE (normal POST)
// MARK COMPLETE (normal POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_task') {
    $task_id = intval($_POST['task_id'] ?? 0);
    if ($task_id > 0) {

        // ✔ Step 1: Fetch child_id and task_points for this task
        $sql = "SELECT child_id, task_points FROM Task WHERE task_id = ? AND parent_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $task_id, $parent_id);
            $stmt->execute();
            $stmt->bind_result($child_id, $task_points);
            $stmt->fetch();
            $stmt->close();

            if ($child_id) {

                // ✔ Step 2: Mark task completed
                $sql2 = "UPDATE Task SET task_status='completed' WHERE task_id = ? AND parent_id = ?";
                if ($stmt2 = $conn->prepare($sql2)) {
                    $stmt2->bind_param("ii", $task_id, $parent_id);
                    $stmt2->execute();
                    $stmt2->close();

                    // ✔ Step 3: Add points to child
                    $sql3 = "UPDATE Child SET child_points = child_points + ? WHERE child_id = ?";
                    if ($stmt3 = $conn->prepare($sql3)) {
                        $stmt3->bind_param("ii", $task_points, $child_id);
                        $stmt3->execute();
                        $stmt3->close();

                        $success_msg = "Task marked completed & points awarded.";
                    }
                }
            }

        } else {
            $error_msg = "DB error fetching task info.";
        }

    } else {
        $error_msg = "Invalid task id.";
    }
}


// --- Fetch children for dropdown ---
$children_list = [];
$sql = "SELECT child_id, child_name FROM Child WHERE parent_id = ? ORDER BY child_name ASC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($c = $res->fetch_assoc()) $children_list[] = $c;
    $stmt->close();
}

// --- Fetch tasks for rendering ---
$tasks = [];
$sql = "SELECT T.*, C.child_name FROM Task T LEFT JOIN Child C ON T.child_id = C.child_id WHERE T.parent_id = ? ORDER BY T.created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($t = $res->fetch_assoc()) {
        $tasks[] = $t;
    }
    $stmt->close();
}

?>

<div class="parent-content">
    <div class="children-header">
        <h2 class="page-heading">Tasks</h2>

        <div>
            <button id="openAssignModal" class="btn add-child-btn">Assign Task</button>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($success_msg): ?>
        <div class="alert success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert error"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <!-- Tasks grid (cards) -->
    <div class="children-grid">
        <?php if (empty($tasks)): ?>
            <div class="card" style="text-align:center;padding:40px;color:#666;">
                No tasks yet. Click <strong>Assign Task</strong> to create one.
            </div>
        <?php else: ?>
            <?php foreach ($tasks as $t): 
                $status = $t['task_status'] ?? 'pending';
                $status_class = ($status === 'completed') ? 'status-completed' : 'status-pending';
            ?>
                <div class="task-card card">
                    <div class="task-top">
                        <div class="task-title"><?php echo htmlspecialchars($t['task_title']); ?></div>
                        <div class="task-meta">for <strong><?php echo htmlspecialchars($t['child_name'] ?? '—'); ?></strong></div>
                    </div>

                    <div class="task-info">
                        <div class="task-points"><?php echo intval($t['task_points']); ?> pts</div>
                        <div class="task-due"><?php echo htmlspecialchars(date("d M Y", strtotime($t['task_duedate']))); ?></div>
                        <div class="task-status <?php echo $status_class; ?>"><?php echo ucfirst($status); ?></div>
                    </div>

                    <div class="task-actions">
                        <!-- Update button: carries data attributes for JS to fill modal -->
                        <button 
                            class="btn outline btn-update"
                            data-taskid="<?php echo intval($t['task_id']); ?>"
                            data-childid="<?php echo intval($t['child_id']); ?>"
                            data-title="<?php echo htmlspecialchars($t['task_title'], ENT_QUOTES); ?>"
                            data-desc="<?php echo htmlspecialchars($t['task_desc'], ENT_QUOTES); ?>"
                            data-points="<?php echo intval($t['task_points']); ?>"
                            data-duedate="<?php echo htmlspecialchars($t['task_duedate']); ?>"
                        >Update Task</button>

                        <?php if ($status !== 'completed'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="complete_task">
                                <input type="hidden" name="task_id" value="<?php echo intval($t['task_id']); ?>">
                                <button class="btn secondary" type="submit" onclick="return confirm('Mark this task as completed?')">Mark Completed</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="display:inline;margin-left:8px;">
                            <input type="hidden" name="action" value="delete_task">
                            <input type="hidden" name="task_id" value="<?php echo intval($t['task_id']); ?>">
                            <button class="btn danger" type="submit" onclick="return confirm('Delete this task? This cannot be undone.')">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Assign / Update Modal (no AJAX) -->
<div id="assignModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <button class="modal-close" id="closeAssignModal">&times;</button>
        <h3 style="color:#2F4B8A;margin-bottom:8px" id="modalTitle">Assign Task</h3>

        <form method="POST" id="assignForm">
            <input type="hidden" name="mode" value="add_task">
            <input type="hidden" name="task_id" value="0">

            <label>Child</label>
            <select name="child_id" required>
                <option value="">Select child</option>
                <?php foreach ($children_list as $c): ?>
                    <option value="<?php echo intval($c['child_id']); ?>"><?php echo htmlspecialchars($c['child_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Task Title</label>
            <input type="text" name="task_title" required>

            <label>Task Description</label>
            <textarea name="task_desc" rows="3" style="width:100%;padding:10px;border-radius:10px;border:1px solid #e6f0ff;margin-bottom:10px;"></textarea>

            <label>Points</label>
            <input type="number" name="task_points" min="1" required>

            <label>Due Date</label>
            <input type="date" name="task_duedate" required>

            <div class="actions">
                <button type="submit" class="btn">Save</button>
                <button type="button" class="btn outline" id="cancelAssign">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/tasks.js"></script>

<?php include_once("../includes/footer.php"); ?>
