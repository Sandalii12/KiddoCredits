<?php
// child/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['child_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include "../includes/db_connection.php";
include "../includes/header_child.php";

$child_id = $_SESSION['child_id'];
$child_name = $_SESSION['child_name'];

// ---------- FETCH CHILD POINTS ----------
$stmt = $conn->prepare("SELECT child_points FROM Child WHERE child_id = ?");
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($child_points);
$stmt->fetch();
$stmt->close();

// ---------- ACTIVE TASK COUNT ----------
$stmt = $conn->prepare("SELECT COUNT(*) FROM Task WHERE child_id = ? AND (task_status='pending' OR task_status='waiting_for_parent')");
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($active_count);
$stmt->fetch();
$stmt->close();

// ---------- COMPLETED TASK COUNT ----------
$stmt = $conn->prepare("SELECT COUNT(*) FROM Task WHERE child_id = ? AND task_status='completed'");
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($completed_count);
$stmt->fetch();
$stmt->close();

// ---------- REWARD COUNT ----------
$stmt = $conn->prepare("SELECT COUNT(*) FROM Reward WHERE reward_status='active' AND (child_id IS NULL OR child_id=?)");
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($reward_count);
$stmt->fetch();
$stmt->close();

// ---------- RECENT TASKS (Option C logic) ----------
$recent_tasks = [];

// Get active first
$stmt = $conn->prepare("
    SELECT task_id, task_title, task_points, task_duedate, task_status, created_at
    FROM Task
    WHERE child_id = ? AND (task_status='pending' OR task_status='waiting_for_parent')
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $child_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $recent_tasks[] = $row;
$stmt->close();

// Fill with completed if needed
if (count($recent_tasks) < 3) {
    $needed = 3 - count($recent_tasks);
    $stmt = $conn->prepare("
        SELECT task_id, task_title, task_points, task_duedate, task_status, updated_at AS created_at
        FROM Task
        WHERE child_id = ? AND task_status='completed'
        ORDER BY updated_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $child_id, $needed);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $recent_tasks[] = $row;
    $stmt->close();
}

?>

<!-- ===========================
     MY OVERVIEW
=========================== -->
<div class="parent-content">

<h2 style="color:#2F4B8A; font-weight:600; margin-bottom:20px;">
    My Overview
</h2>

<div class="stats-grid">

    <div class="card overview-card">
        <div class="card-emoji">⭐</div>
        <div class="card-title">My Points</div>
        <div class="card-value"><?= $child_points ?></div>
    </div>

    <div class="card overview-card">
        <div class="card-emoji">🚀</div>
        <div class="card-title">Active Tasks</div>
        <div class="card-value"><?= $active_count ?></div>
    </div>

    <div class="card overview-card">
        <div class="card-emoji">✔️</div>
        <div class="card-title">Completed Tasks</div>
        <div class="card-value"><?= $completed_count ?></div>
    </div>

    <div class="card overview-card">
        <div class="card-emoji">🎁</div>
        <div class="card-title">Rewards Available</div>
        <div class="card-value"><?= $reward_count ?></div>
    </div>

</div>

<!-- ===========================
     RECENT TASKS
=========================== -->
<div class="card" style="margin-top:25px; text-align:left;">
    <h3 style="color:#2F4B8A; margin-bottom:15px;">Recent Tasks</h3>

    <?php if (count($recent_tasks) == 0): ?>
        <p style="color:#555;">No recent tasks yet</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($recent_tasks as $task): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:12px;background:#f8fbff;">
                    <div style="flex:1;">
                        <strong style="color:#2F4B8A;display:block;margin-bottom:4px;"><?= htmlspecialchars($task['task_title']) ?></strong>
                        <div style="font-size:13px;color:#666;">
                            <?= $task['task_points'] ?> pts • Due: <?= date("d M", strtotime($task['task_duedate'])) ?>
                        </div>
                    </div>
                    <div style="text-align:right;margin-left:12px;">
                        <?php
                            if ($task['task_status'] == 'pending') 
                                echo "<span class='task-status pending'>Pending</span>";
                            else if ($task['task_status'] == 'waiting_for_parent') 
                                echo "<span class='task-status waiting'>Waiting</span>";
                            else 
                                echo "<span class='task-status completed'>Done</span>";
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</div>

<?php include "../includes/footer.php"; ?>
