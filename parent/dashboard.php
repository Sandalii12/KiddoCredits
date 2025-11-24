<?php
// parent/dashboard.php
// Backend-enabled dashboard page for Parent

// include DB connection and auth session first
include_once("../includes/db_connection.php");
include_once("../includes/auth_session.php");

// header_parent.php contains HTML head, links to parent.css and starts the <body>
// it expects session to be already available (auth_session.php ensures that)
include_once("../includes/header_parent.php");

// Ensure dashboard body style (uses the CSS rules in parent.css)
echo "<script>document.body.classList.add('dashboard-body');</script>";

// initialize default values
$total_children = 0;
$pending_tasks = 0;
$completed_tasks = 0;
$due_soon = 0;
$children = [];    // list of children
$recent_tasks = []; // list of recent tasks

// get parent id from session (auth_session.php should have set this)
$parent_id = $_SESSION['parent_id'] ?? null;

if ($parent_id) {
    // 1) Total children
    $sql = "SELECT COUNT(*) FROM Child WHERE parent_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $stmt->bind_result($total_children);
        $stmt->fetch();
        $stmt->close();
    } else {
        // optional: log error
         error_log("Prepare failed (total_children): " . $conn->error);
    }

    // 2) Pending tasks
    $sql = "SELECT COUNT(*) FROM Task WHERE parent_id = ? AND task_status = 'pending'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $stmt->bind_result($pending_tasks);
        $stmt->fetch();
        $stmt->close();
    }

    // 3) Completed tasks
    $sql = "SELECT COUNT(*) FROM Task WHERE parent_id = ? AND task_status = 'completed'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $stmt->bind_result($completed_tasks);
        $stmt->fetch();
        $stmt->close();
    }

 // 4) Due soon tasks (all upcoming pending tasks)
$sql = "SELECT COUNT(*) FROM Task 
    WHERE parent_id = ? 
    AND task_status = 'pending'
    AND task_duedate >= CURDATE()
    AND task_duedate <= DATE_ADD(CURDATE(), INTERVAL 2 DAY)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $stmt->bind_result($due_soon);
    $stmt->fetch();
    $stmt->close();
}


    // 5) Children list (basic info)
    $sql = "SELECT child_id, child_name, child_points, created_at FROM Child WHERE parent_id = ? ORDER BY created_at DESC";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $children[] = $row;
        }
        $stmt->close();
    }

    // 6) Recent tasks (limit 5)
    $sql = "SELECT T.task_id, T.task_title, T.task_points, T.task_duedate, T.task_status, C.child_name
            FROM Task T
            LEFT JOIN Child C ON T.child_id = C.child_id
            WHERE T.parent_id = ?
            ORDER BY T.created_at DESC
            LIMIT 5";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $recent_tasks[] = $row;
        }
        $stmt->close();
    }
}
?>

<!-- ========================= HOME / DASHBOARD ========================= -->

<div class="parent-content">

    <!-- PAGE TITLE -->
    <h2 style="color:#2F4B8A; font-weight:600; margin-bottom:20px;">
        Home Overview
    </h2>

    <!-- ================== STATS GRID ================== -->
    <div class="stats-grid">

        <!-- Total Children -->
        <div class="card">
            <div class="card-emoji">👶</div>
            <div class="card-title">Total Children</div>
            <div class="card-value"><?php echo intval($total_children); ?></div>
        </div>

        <!-- Pending Tasks -->
        <div class="card">
            <div class="card-emoji">⏳</div>
            <div class="card-title">Pending Tasks</div>
            <div class="card-value"><?php echo intval($pending_tasks); ?></div>
        </div>

        <!-- Completed Tasks -->
        <div class="card">
            <div class="card-emoji">✔️</div>
            <div class="card-title">Completed Tasks</div>
            <div class="card-value"><?php echo intval($completed_tasks); ?></div>
        </div>

        <!-- Tasks Due Soon -->
        <div class="card">
            <div class="card-emoji">🔔</div>
            <div class="card-title">Due Soon</div>
            <div class="card-value"><?php echo intval($due_soon); ?></div>
        </div>

    </div>


    <!-- ================== CHILDREN OVERVIEW CARD ================== -->
    <div class="card" style="margin-top:40px; text-align:left;">
        <h3 style="color:#2F4B8A; margin-bottom:15px;">Children Overview</h3>

        <?php if (count($children) === 0): ?>
            <p style="color:#555;">No children added yet. Use <a href="add_child.php">Add Child</a> to create one.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($children as $c): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:12px;background:#f8fbff;">
                        <div>
                            <strong style="color:#2F4B8A;"><?php echo htmlspecialchars($c['child_name']); ?></strong>
                            <div style="font-size:13px;color:#666;"><?php echo "Added: " . date("d M Y", strtotime($c['created_at'])); ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700;color:#4A76D1;"><?php echo intval($c['child_points']); ?> pts</div>
                            <div style="font-size:13px;color:#666;">ID: <?php echo intval($c['child_id']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================== TASK SUMMARY CARD ================== -->
    <div class="card" style="margin-top:25px; text-align:left;">
        <h3 style="color:#2F4B8A; margin-bottom:15px;">Recent Tasks</h3>

        <?php if (count($recent_tasks) === 0): ?>
            <p style="color:#555;">No tasks have been created yet.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#2F4B8A;">
                        <th style="padding:10px 8px;">Task</th>
                        <th style="padding:10px 8px;">Child</th>
                        <th style="padding:10px 8px;">Points</th>
                        <th style="padding:10px 8px;">Due</th>
                        <th style="padding:10px 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_tasks as $t): ?>
                        <tr style="background:#fff;border-radius:8px;">
                            <td style="padding:12px 8px;"><?php echo htmlspecialchars($t['task_title']); ?></td>
                            <td style="padding:12px 8px;color:#555;"><?php echo htmlspecialchars($t['child_name'] ?? '—'); ?></td>
                            <td style="padding:12px 8px;"><?php echo intval($t['task_points']); ?></td>
                            <td style="padding:12px 8px;"><?php echo htmlspecialchars(date("d M Y", strtotime($t['task_duedate']))); ?></td>
                            <td style="padding:12px 8px;"><?php echo htmlspecialchars(ucfirst($t['task_status'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<!-- FOOTER -->
<?php include_once("../includes/footer.php"); ?>
