<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['child_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include "../includes/db_connection.php";
include "../includes/header_child.php";

$child_id = intval($_SESSION['child_id']);

// Fetch tasks helper
function fetch_tasks($conn, $child_id, $statuses) {
    $tasks = [];
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $sql = "SELECT task_id, task_title, task_desc, task_points, task_duedate, task_status, created_at, updated_at
            FROM Task
            WHERE child_id = ? AND task_status IN ($placeholders)
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);

    $types = 'i' . str_repeat('s', count($statuses));
    $params = array_merge([$child_id], $statuses);

    $stmt->bind_param($types, ...array_map(function ($v) { return $v; }, $params));

    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $tasks[] = $row;
    $stmt->close();

    return $tasks;
}

$active_tasks = fetch_tasks($conn, $child_id, ['pending', 'waiting_for_parent']);
$completed_tasks = fetch_tasks($conn, $child_id, ['completed']);

function fmt_date($d) {
    return $d ? date("d M, Y", strtotime($d)) : '—';
}
?>

<div class="parent-content">

    <h2 style="color:#2F4B8A; font-weight:600; margin-bottom:15px;">Tasks List</h2>

    <!-- PILL SUB NAV -->
    <div class="tasks-pill-wrap">
        <button class="tasks-pill active" data-target="activeSection">🚀 Active Tasks</button>
        <button class="tasks-pill" data-target="completedSection">✔️ Completed Tasks</button>
    </div>

    <!-- ACTIVE TASKS -->
    <div id="activeSection" class="tasks-section">
        <?php if (empty($active_tasks)): ?>
            <div class="card" style="padding:20px;">
                <p style="color:#556;">No active tasks right now.</p>
            </div>
        <?php else: ?>
            <div class="task-grid">
            <?php foreach ($active_tasks as $t): ?>
                <div class="task-card-square">

                    <div class="task-emoji-top">⏰</div>

                    <div class="task-info-set">

                        <div class="info-line">
                            <span class="info-label">📝 Task Title:</span>
                            <span class="info-value"><?= htmlspecialchars($t['task_title']) ?></span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">📄 Description:</span>
                            <span class="info-value"><?= nl2br(htmlspecialchars($t['task_desc'])) ?></span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">⭐ Points:</span>
                            <span class="info-value"><?= $t['task_points'] ?> pts</span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">⏳ Due Date:</span>
                            <span class="info-value"><?= fmt_date($t['task_duedate']) ?></span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">📌 Status:</span>
                            <span class="info-value">
                                <?php if ($t['task_status'] == 'pending'): ?>
                                    <span class="task-status status-pending">Pending</span>
                                <?php else: ?>
                                    <span class="task-status status-waiting">Waiting Approval</span>
                                <?php endif; ?>
                            </span>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- COMPLETED TASKS -->
    <div id="completedSection" class="tasks-section" style="display:none;">
        <?php if (empty($completed_tasks)): ?>
            <div class="card" style="padding:20px;">
                <p style="color:#556;">No completed tasks yet.</p>
            </div>
        <?php else: ?>
            <div class="task-grid">
            <?php foreach ($completed_tasks as $t): ?>
                <div class="task-card-square">

                    <div class="task-emoji-top">🎖️</div>

                    <div class="task-info-set">

                        <div class="info-line">
                            <span class="info-label">📝 Task Title:</span>
                            <span class="info-value"><?= htmlspecialchars($t['task_title']) ?></span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">📄 Description:</span>
                            <span class="info-value"><?= nl2br(htmlspecialchars($t['task_desc'])) ?></span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">⭐ Points:</span>
                            <span class="info-value"><?= $t['task_points'] ?> pts</span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">🏁 Completed On:</span>
                            <span class="info-value"><?= fmt_date($t['updated_at']) ?></span>
                        </div>

                        <div class="info-line">
                            <span class="info-label">📌 Status:</span>
                            <span class="info-value">
                                <span class="task-status status-completed">Completed</span>
                            </span>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
<script src="../js/tasks_child.js"></script>
