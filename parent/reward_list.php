<?php
// parent/reward_list.php
// Reward List + Add/Update Reward (no AJAX)
// UI reference: /mnt/data/reward.png

include_once("../includes/db_connection.php");
include_once("../includes/auth_session.php");
include_once("../includes/header_parent.php");

echo "<script>document.body.classList.add('dashboard-body');</script>";

$parent_id = $_SESSION['parent_id'] ?? null;
if (!$parent_id) {
    header("Location: ../auth/login.php");
    exit;
}

$success_msg = "";
$error_msg = "";

// Helper: safe echo for attributes
function attr($s) { return htmlspecialchars($s, ENT_QUOTES); }

// POST handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add Reward
    if (isset($_POST['mode']) && $_POST['mode'] === 'add_reward') {
        $title = trim($_POST['reward_title'] ?? '');
        $desc  = trim($_POST['reward_desc'] ?? '');
        $cost  = intval($_POST['reward_cost'] ?? 0);
        $category = trim($_POST['reward_category'] ?? '');
        $assign_to = $_POST['assign_to'] ?? 'all';
        $child_id = ($assign_to === 'child') ? intval($_POST['child_id'] ?? 0) : null;

        if ($title === '' || $cost <= 0) {
            $error_msg = "Please enter reward title and a positive cost.";
        } else {
            $sql = "INSERT INTO Reward (parent_id, child_id, reward_title, reward_desc, reward_cost, reward_category, reward_status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
            if ($stmt = $conn->prepare($sql)) {
                if ($child_id === null) {
                    // bind as null: use NULL for child_id
                    $stmt->bind_param("iissis", $parent_id, $child_id, $title, $desc, $cost, $category);
                    // the above may set child_id=0 rather than NULL; instead handle with proper param types below
                }
                // Because PHP mysqli cannot bind NULL easily with types when param is null, use this approach:
                $stmt->close();
                $sql = "INSERT INTO Reward (parent_id, child_id, reward_title, reward_desc, reward_cost, reward_category, reward_status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    if ($child_id === null) {
                        // pass NULL via null binding using types and explicit null param via mysqli_stmt::send_long_data isn't necessary;
                        // easier: use explicit NULL in query when child_id is null
                        $stmt->close();
                        $sql2 = "INSERT INTO Reward (parent_id, child_id, reward_title, reward_desc, reward_cost, reward_category, reward_status) VALUES (?, NULL, ?, ?, ?, ?, 'active')";
                        $stmt2 = $conn->prepare($sql2);
                        if ($stmt2) {
                            $stmt2->bind_param("issis", $parent_id, $title, $desc, $cost, $category);
                            $ok = $stmt2->execute();
                            if ($ok) $success_msg = "Reward added successfully.";
                            else $error_msg = "DB error inserting reward: " . $conn->error;
                            $stmt2->close();
                        } else {
                            $error_msg = "DB error preparing insert (null child): " . $conn->error;
                        }
                    } else {
                        $stmt->bind_param("iissis", $parent_id, $child_id, $title, $desc, $cost, $category);
                        if ($stmt->execute()) $success_msg = "Reward added successfully.";
                        else $error_msg = "DB error inserting reward: " . $conn->error;
                        $stmt->close();
                    }
                } else {
                    $error_msg = "DB error preparing insert: " . $conn->error;
                }
            } else {
                $error_msg = "DB error preparing insert: " . $conn->error;
            }
        }
    }

    // Update Reward
    if (isset($_POST['mode']) && $_POST['mode'] === 'update_reward') {
        $reward_id = intval($_POST['reward_id'] ?? 0);
        $title = trim($_POST['reward_title'] ?? '');
        $desc  = trim($_POST['reward_desc'] ?? '');
        $cost  = intval($_POST['reward_cost'] ?? 0);
        $category = trim($_POST['reward_category'] ?? '');
        $assign_to = $_POST['assign_to'] ?? 'all';
        $child_id = ($assign_to === 'child') ? intval($_POST['child_id'] ?? 0) : null;

        if ($reward_id <= 0 || $title === '' || $cost <= 0) {
            $error_msg = "Please fill all required fields.";
        } else {
            if ($child_id === null) {
                $sql = "UPDATE Reward SET child_id = NULL, reward_title = ?, reward_desc = ?, reward_cost = ?, reward_category = ? WHERE reward_id = ? AND parent_id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssiiii", $title, $desc, $cost, $category, $reward_id, $parent_id);
                    if ($stmt->execute()) $success_msg = "Reward updated.";
                    else $error_msg = "DB error updating reward: " . $conn->error;
                    $stmt->close();
                } else $error_msg = "DB error preparing update: " . $conn->error;
            } else {
                $sql = "UPDATE Reward SET child_id = ?, reward_title = ?, reward_desc = ?, reward_cost = ?, reward_category = ? WHERE reward_id = ? AND parent_id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("issisii", $child_id, $title, $desc, $cost, $category, $reward_id, $parent_id);
                    if ($stmt->execute()) $success_msg = "Reward updated.";
                    else $error_msg = "DB error updating reward: " . $conn->error;
                    $stmt->close();
                } else $error_msg = "DB error preparing update: " . $conn->error;
            }
        }
    }

    // Delete Reward
    if (isset($_POST['action']) && $_POST['action'] === 'delete_reward') {
        $reward_id = intval($_POST['reward_id'] ?? 0);
        if ($reward_id > 0) {
            $sql = "DELETE FROM Reward WHERE reward_id = ? AND parent_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ii", $reward_id, $parent_id);
                if ($stmt->execute()) $success_msg = "Reward removed.";
                else $error_msg = "DB error deleting reward.";
                $stmt->close();
            } else {
                $error_msg = "DB error preparing delete: " . $conn->error;
            }
        } else {
            $error_msg = "Invalid reward id.";
        }
    }
}

// Fetch children for assign dropdown
$children_list = [];
$sql = "SELECT child_id, child_name FROM Child WHERE parent_id = ? ORDER BY child_name ASC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $children_list[] = $r;
    $stmt->close();
}

// Fetch rewards for list
$rewards = [];
$sql = "SELECT R.*, C.child_name FROM Reward R LEFT JOIN Child C ON R.child_id = C.child_id WHERE R.parent_id = ? ORDER BY R.created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $rewards[] = $r;
    $stmt->close();
}

// helper to pick emoji by category (simple)
function emoji_for_category($cat) {
    $c = strtolower(trim($cat));
    if ($c === '') return '🎁';
    if (strpos($c, 'toy') !== false) return '🚗';
    if (strpos($c, 'screen') !== false) return '📺';
    if (strpos($c, 'snack') !== false || strpos($c, 'ice') !== false || strpos($c, 'treat') !== false) return '🍦';
    if (strpos($c, 'time') !== false) return '⏰';
    if (strpos($c, 'book') !== false || strpos($c, 'study') !== false) return '📚';
    return '🎁';
}
?>

<div class="parent-content">
    <div class="children-header">
        <h2 class="page-heading">My Rewards</h2>
        <div>
            <button id="openAddReward" class="btn add-child-btn">+ Add New Reward</button>
        </div>
    </div>

    <?php if ($success_msg): ?><div class="alert success"><?php echo htmlspecialchars($success_msg); ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class="alert error"><?php echo htmlspecialchars($error_msg); ?></div><?php endif; ?>

    <div class="children-grid">
        <?php if (empty($rewards)): ?>
            <div class="card" style="text-align:center;padding:40px;color:#666;">No rewards yet. Click <strong>Add New Reward</strong>.</div>
        <?php else: ?>
            <?php foreach ($rewards as $rw): ?>
                <div class="reward-card card">
                    <div class="reward-top" style="text-align:center;">
                        <div class="reward-emoji"><?php echo emoji_for_category($rw['reward_category']); ?></div>
                        <div class="reward-title"><?php echo htmlspecialchars($rw['reward_title']); ?></div>
                        <div class="reward-desc" style="color:#666;margin-top:6px;"><?php echo htmlspecialchars($rw['reward_desc']); ?></div>
                    </div>

                    <div class="reward-meta" style="display:flex;gap:12px;margin-top:12px;align-items:center;justify-content:space-between;">
                        <div style="flex:1">
                            <div style="font-weight:700;color:#2F4B8A;"><?php echo intval($rw['reward_cost']); ?> pts</div>
                            <div style="font-size:13px;color:#6b93d1;"><?php echo htmlspecialchars($rw['reward_category'] ?: 'Uncategorized'); ?></div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:13px;color:#666;"><?php echo ($rw['child_id'] ? "Assigned to: " . htmlspecialchars($rw['child_name']) : "Assigned to: All Children"); ?></div>
                            <div style="font-size:13px;color:#666;">Status: <?php echo htmlspecialchars(ucfirst($rw['reward_status'])); ?></div>
                        </div>
                    </div>

                    <div class="child-actions" style="margin-top:18px;justify-content:center;">
                        <!-- Update button (fills modal via data attributes) -->
                        <button class="btn outline btn-update-reward"
                            data-rewardid="<?php echo intval($rw['reward_id']); ?>"
                            data-title="<?php echo attr($rw['reward_title']); ?>"
                            data-desc="<?php echo attr($rw['reward_desc']); ?>"
                            data-cost="<?php echo intval($rw['reward_cost']); ?>"
                            data-category="<?php echo attr($rw['reward_category']); ?>"
                            data-childid="<?php echo intval($rw['child_id']); ?>"
                        >Update Reward</button>

                        <form method="POST" style="display:inline;margin-left:10px;" onsubmit="return confirm('Delete this reward?');">
                            <input type="hidden" name="action" value="delete_reward">
                            <input type="hidden" name="reward_id" value="<?php echo intval($rw['reward_id']); ?>">
                            <button class="btn danger" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="../js/sidecard.js"></script>

<!-- Hidden children options for rewards JS -->
<select id="childrenOptions" style="display:none;">
    <?php foreach ($children_list as $c): ?>
        <option value="<?= $c['child_id'] ?>"><?= htmlspecialchars($c['child_name']) ?></option>
    <?php endforeach; ?>
</select>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function getRewardFormHTML(childrenOptionsHTML = "") {
        return `
            <input type="hidden" name="reward_id" value="0">
            <label>Reward Title</label>
            <input type="text" name="reward_title" required>

            <label>Description</label>
            <textarea name="reward_desc" rows="3"></textarea>

            <label>Cost (Points)</label>
            <input type="number" name="reward_cost" min="1" required>

            <label>Category</label>
            <input type="text" name="reward_category" placeholder="E.g. Toys, Screen Time">

            <label>Assign To</label>
            <div class="assign-options">
                <label><input type="radio" name="assign_to" value="all" checked> All Children</label>
                <label><input type="radio" name="assign_to" value="child"> Specific Child</label>
            </div>

            <div id="childSelectWrapper" class="hidden">
                <label>Select Child</label>
                <select name="child_id">
                    <option value="">Choose child</option>
                    ${childrenOptionsHTML}
                </select>
            </div>

            <!-- Buttons are provided by the universal sidecard form, do not inject here -->
        `;
    }

    var openBtn = document.getElementById('openAddReward');
    var childrenOptionsEl = document.getElementById('childrenOptions');
    var childrenOptionsHTML = childrenOptionsEl ? childrenOptionsEl.innerHTML : '';

    if (openBtn) {
        openBtn.addEventListener('click', function() {
            window.SideCard.open({
                title: 'Add New Reward',
                mode: 'add_reward',
                entityId: 0,
                innerHTML: getRewardFormHTML(childrenOptionsHTML),
                focusSelector: "input[name='reward_title']"
            });
            setTimeout(attachRewardFieldListeners, 30);
        });
    }

    document.querySelectorAll('.btn-update-reward').forEach(function(btn) {
        btn.addEventListener('click', function() {
            window.SideCard.open({
                title: 'Update Reward',
                mode: 'update_reward',
                entityId: btn.dataset.rewardid || 0,
                innerHTML: getRewardFormHTML(childrenOptionsHTML),
                focusSelector: "input[name='reward_title']"
            });
            setTimeout(function() {
                var f = window.SideCard.formElement;
                if (!f) return;
                var rid = f.querySelector("input[name='reward_id']"); if (rid) rid.value = btn.dataset.rewardid || '0';
                var t = f.querySelector("input[name='reward_title']"); if (t) t.value = btn.dataset.title || '';
                var d = f.querySelector("textarea[name='reward_desc']"); if (d) d.value = btn.dataset.desc || '';
                var c = f.querySelector("input[name='reward_cost']"); if (c) c.value = btn.dataset.cost || '';
                var cat = f.querySelector("input[name='reward_category']"); if (cat) cat.value = btn.dataset.category || '';
                var childId = btn.dataset.childid || '';
                var assignAll = f.querySelector("input[name='assign_to'][value='all']");
                var assignChild = f.querySelector("input[name='assign_to'][value='child']");
                var childWrapper = f.querySelector('#childSelectWrapper');
                var childSelect = f.querySelector("select[name='child_id']");
                if (childId && childId !== '0') {
                    if (assignChild) assignChild.checked = true;
                    if (childWrapper) childWrapper.classList.remove('hidden');
                    if (childSelect) childSelect.value = childId;
                } else {
                    if (assignAll) assignAll.checked = true;
                    if (childWrapper) childWrapper.classList.add('hidden');
                }
                attachRewardFieldListeners();
            }, 40);
        });
    });

    function attachRewardFieldListeners() {
        var f = window.SideCard.formElement;
        if (!f) return;
        var cancelBtn = f.querySelector('#sideCardCancelInner');
        if (cancelBtn) cancelBtn.addEventListener('click', function() { window.SideCard.close(); });
        var radios = f.querySelectorAll("input[name='assign_to']");
        var childWrapper = f.querySelector('#childSelectWrapper');
        radios.forEach(function(r) {
            r.addEventListener('change', function() {
                if (r.value === 'child' && r.checked) {
                    if (childWrapper) childWrapper.classList.remove('hidden');
                } else if (r.checked) {
                    if (childWrapper) childWrapper.classList.add('hidden');
                    var sel = f.querySelector("select[name='child_id']"); if (sel) sel.value = '';
                }
            });
        });
    }
});
</script>

<?php include_once("../includes/footer.php"); ?>
