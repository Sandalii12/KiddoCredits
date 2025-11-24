<?php
// parent/tasks.php

include_once("../includes/db_connection.php");
include_once("../includes/auth_session.php");
include_once("../includes/header_parent.php");

// apply dashboard styling
echo "<script>document.body.classList.add('dashboard-body');</script>";

$success_msg = "";
$error_msg = "";

// logged-in parent
$parent_id = $_SESSION['parent_id'] ?? null;
if (!$parent_id) {
    header("Location: ../auth/login.php");
    exit;
}

/*************************************************
  POST HANDLERS (NO AJAX)
*************************************************/

// ADD TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'add_task') {

    $child_id = intval($_POST['child_id']);
    $title    = trim($_POST['task_title']);
    $desc     = trim($_POST['task_desc']);
    $points   = intval($_POST['task_points']);
    $due      = trim($_POST['task_duedate']);

    // Validate due date is not in the past (allow today)
    $dueDateObj = DateTime::createFromFormat('Y-m-d', $due);
    $today = new DateTime('today');

    if (!$dueDateObj) {
        $error_msg = "Please provide a valid due date.";
        echo "<script>alert('Please provide a valid due date.');</script>";
    }

    if (empty($error_msg) && $dueDateObj < $today) {
        $error_msg = "Due date cannot be in the past.";
        echo "<script>alert('Due date cannot be in the past.');</script>";
    }

    if (empty($error_msg) && $child_id && $title && $points > 0 && $due) {
        $sql = "INSERT INTO Task (parent_id, child_id, task_title, task_desc, task_points, task_duedate)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissis", $parent_id, $child_id, $title, $desc, $points, $due);
        $stmt->execute();
        $stmt->close();
        $success_msg = "Task added successfully.";
    } else {
        if (empty($error_msg)) $error_msg = "All required fields must be filled.";
    }
}

// UPDATE TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'update_task') {

    $task_id  = intval($_POST['task_id']);
    $child_id = intval($_POST['child_id']);
    $title    = trim($_POST['task_title']);
    $desc     = trim($_POST['task_desc']);
    $points   = intval($_POST['task_points']);
    $due      = trim($_POST['task_duedate']);

    if ($task_id && $child_id && $title && $points > 0 && $due) {

        $sql = "UPDATE Task SET child_id=?, task_title=?, task_desc=?, task_points=?, task_duedate=?
                WHERE task_id=? AND parent_id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issisii", $child_id, $title, $desc, $points, $due, $task_id, $parent_id);
        $stmt->execute();
        $stmt->close();
        $success_msg = "Task updated successfully.";

    } else {
        $error_msg = "Invalid update data.";
    }
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_task') {

    $task_id = intval($_POST['task_id']);
    if ($task_id) {
        $stmt = $conn->prepare("DELETE FROM Task WHERE task_id=? AND parent_id=?");
        $stmt->bind_param("ii", $task_id, $parent_id);
        $stmt->execute();
        $stmt->close();
        $success_msg = "Task deleted.";
    }
}

// COMPLETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'complete_task') {

    $task_id = intval($_POST['task_id']);
    if ($task_id) {

        // fetch child + points
        $stmt = $conn->prepare("SELECT child_id, task_points FROM Task WHERE task_id=? AND parent_id=?");
        $stmt->bind_param("ii", $task_id, $parent_id);
        $stmt->execute();
        $stmt->bind_result($child_id, $points);
        $stmt->fetch();
        $stmt->close();

        if ($child_id) {
            // set complete
            $stmt2 = $conn->prepare("UPDATE Task SET task_status='completed' WHERE task_id=?");
            $stmt2->bind_param("i", $task_id);
            $stmt2->execute();
            $stmt2->close();

            // add points
            $stmt3 = $conn->prepare("UPDATE Child SET child_points = child_points + ? WHERE child_id=?");
            $stmt3->bind_param("ii", $points, $child_id);
            $stmt3->execute();
            $stmt3->close();
        }

        $success_msg = "Task completed + points awarded.";
    }
}

/*************************************************
  FETCH CHILDREN & TASKS
*************************************************/

$children = $conn->query("SELECT child_id, child_name FROM Child WHERE parent_id=$parent_id ORDER BY child_name")->fetch_all(MYSQLI_ASSOC);

$tasks = $conn->query("
    SELECT T.*, C.child_name
    FROM Task T
    LEFT JOIN Child C ON T.child_id = C.child_id
    WHERE T.parent_id=$parent_id
    ORDER BY T.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<div class="children-header">
    <h2 class="page-heading">Tasks</h2>
    <button id="openAssignModal" class="btn add-child-btn">Assign Task</button>
</div>

<?php if ($success_msg): ?>
<div class="alert success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>

<?php if ($error_msg): ?>
<div class="alert error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<!-- Hidden children options for JS -->
<select id="childrenOptions" style="display:none;">
    <?php foreach ($children as $c): ?>
        <option value="<?= $c['child_id'] ?>"><?= htmlspecialchars($c['child_name']) ?></option>
    <?php endforeach; ?>
</select>

<div class="children-grid">
<?php if (empty($tasks)): ?>
    <div class="card" style="padding:40px;text-align:center;">No tasks yet.</div>
<?php else: ?>

    <?php foreach ($tasks as $t): ?>
    <div class="task-card card">

        <div class="task-top">
            <div class="task-title"><?= htmlspecialchars($t['task_title']) ?></div>
            <div class="task-meta">for <b><?= htmlspecialchars($t['child_name']) ?></b></div>
        </div>

        <div class="task-info">
            <div class="task-points"><?= $t['task_points'] ?> pts</div>
            <div class="task-due"><?= date("d M Y", strtotime($t['task_duedate'])) ?></div>
            <div class="task-status <?= $t['task_status'] === 'completed' ? 'status-completed' : 'status-pending' ?>">
                <?= ucfirst($t['task_status']) ?>
            </div>
        </div>

        <div class="task-actions">

            <!-- Update Button -->
            <button class="btn outline btn-update"
                data-taskid="<?= $t['task_id'] ?>"
                data-childid="<?= $t['child_id'] ?>"
                data-title="<?= htmlspecialchars($t['task_title'], ENT_QUOTES) ?>"
                data-desc="<?= htmlspecialchars($t['task_desc'], ENT_QUOTES) ?>"
                data-points="<?= $t['task_points'] ?>"
                data-duedate="<?= $t['task_duedate'] ?>"
            >Update</button>

            <!-- Complete -->
            <?php if ($t['task_status'] !== 'completed'): ?>
            <form method="POST">
                <input type="hidden" name="action" value="complete_task">
                <input type="hidden" name="task_id" value="<?= $t['task_id'] ?>">
                <button class="btn secondary" onclick="return confirm('Mark completed?')">Complete</button>
            </form>
            <?php endif; ?>

            <!-- Delete -->
            <form method="POST">
                <input type="hidden" name="action" value="delete_task">
                <input type="hidden" name="task_id" value="<?= $t['task_id'] ?>">
                <button class="btn danger" onclick="return confirm('Delete task?')">Remove</button>
            </form>

        </div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>
</div>
<?php include_once("../includes/footer.php"); ?>

<script src="../js/sidecard.js"></script>
<script src="../js/tasks.js"></script>

