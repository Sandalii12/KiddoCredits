<?php
// child/wallet.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['child_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once("../includes/db_connection.php");
include_once("../includes/header_child.php");

// emoji helper (reuse / extend)
function emoji_for_category($cat) {
    $c = strtolower(trim($cat));
    if ($c === '') return '🎁';
    if (strpos($c, 'toy') !== false) return '🚗';
    if (strpos($c, 'screen') !== false) return '📺';
    if (strpos($c, 'snack') !== false || strpos($c, 'ice') !== false || strpos($c, 'treat') !== false) return '🍦';
    if (strpos($c, 'time') !== false) return '⏰';
    if (strpos($c, 'book') !== false || strpos($c, 'study') !== false) return '📚';
    if (strpos($c, 'pizza') !== false) return '🍕';
    if (strpos($c, 'video') !== false || strpos($c, 'game') !== false) return '🎮';
    if (strpos($c, 'outing') !== false || strpos($c, 'park') !== false) return '🛝';
    if (strpos($c, 'movie') !== false) return '🎬';
    if (strpos($c, 'coloring') !== false) return '🎨';
    if (strpos($c, 'chocolate') !== false) return '🍫';
    if (strpos($c, 'sports') !== false || strpos($c, 'play') !== false) return '⚽';
    return '🎁';
}

$child_id = intval($_SESSION['child_id']);

// Fetch balance from Child table (Choice A)
$child_points = 0;
$parent_id = null;
if ($stmt = $conn->prepare("SELECT child_points, parent_id FROM Child WHERE child_id = ?")) {
    $stmt->bind_param("i", $child_id);
    $stmt->execute();
    $stmt->bind_result($child_points, $parent_id);
    $stmt->fetch();
    $stmt->close();
}

// Earned points (completed tasks)
$earned = [];
$q1 = "SELECT task_id, task_title, task_points, updated_at
       FROM Task
       WHERE child_id = ? AND task_status = 'completed'
       ORDER BY updated_at DESC
       LIMIT 50";
if ($stmt = $conn->prepare($q1)) {
    $stmt->bind_param("i", $child_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $earned[] = $r;
    $stmt->close();
}

// Redeemed history (join)
/* -----------------------------------------
   FETCH REDEEMED HISTORY (FIXED)
------------------------------------------*/

$redeemed = [];
$sql = "SELECT 
            R.reward_title,
            R.reward_category,
            RD.cost,
            RD.redeemed_date AS redeemed_at      -- FIXED here
        FROM redeemed RD
        JOIN reward R ON RD.reward_id = R.reward_id
        WHERE RD.child_id = ?
        ORDER BY RD.redeemed_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $redeemed[] = $row;
}

$stmt->close();


/* small helper */
function fmt_date($d) {
    return $d ? date("d M, Y", strtotime($d)) : '—';
}
?>

<div class="parent-content">
    <!-- Wallet Header -->
    <!-- WALLET HEADER WITH RIGHT-ALIGNED BLUE POINTS BOX -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

    <h2 style="color:#2F4B8A;font-weight:700;font-size:28px;margin:0;">
        My Credits💰
    </h2>

    <!-- BLUE POINTS BOX (Solid background, white text) -->
    <div 
        style="
            background:#1180e6;
            color:#fff;
            padding:10px 18px;
            border-radius:12px;
            font-size:18px;
            font-weight:700;
            display:flex;
            align-items:center;
            gap:10px;
            box-shadow:0 4px 12px rgba(0,0,0,0.15);
        ">
        <span style="font-size:25px;"></span>
        <span><?php echo intval($child_points); ?> Points ✨</span>
    </div>

</div>


    <!-- Tabs -->
    <div style="display:flex;justify-content:center;margin-top:18px;">
        <div class="tasks-pill-wrap" role="tablist">
            <button class="tasks-pill active" data-target="earnedTab">🥳 Earned History</button>
            <button class="tasks-pill" data-target="redeemedTab">😎 Redeemed History</button>
        </div>
    </div>

    <!-- Earned Section -->
    <div id="earnedTab" class="wallet-tab-section" style="margin-top:18px;">
        <div class="section-title" style="margin-bottom:8px;">Recently Earned</div>

        <?php if (empty($earned)): ?>
            <div class="card"><p style="color:#556;">No earned points yet.</p></div>
        <?php else: ?>
            <div class="reward-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;">
                <?php foreach ($earned as $e): ?>
                    <div class="card reward-card" style="padding:18px;">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div style="font-size:36px;background:#fff;padding:10px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
                                🎉
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:700;color:#223a74;font-size:16px;"><?= htmlspecialchars($e['task_title']) ?></div>
                                <div style="color:#6b93d1;font-size:13px;margin-top:6px;">Completed: <?= fmt_date($e['updated_at']) ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:800;color:#1180e6;font-size:20px;"><?= intval($e['task_points']) ?> pts</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Redeemed Section -->
    <div id="redeemedTab" class="wallet-tab-section" style="display:none;margin-top:18px;">
        <div class="section-title" style="margin-bottom:8px;">Redeemed History</div>

        <?php if (empty($redeemed)): ?>
            <div class="card"><p style="color:#556;">No redeemed rewards yet.</p></div>
        <?php else: ?>
            <div class="reward-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;">
                <?php foreach ($redeemed as $rd): 
                    $emoji = emoji_for_category($rd['reward_category'] ?? '');
                ?>
                    <div class="card reward-card" style="padding:18px;">
                        <div style="display:flex;gap:12px;align-items:center;">
                            <div style="font-size:36px;background:#fff;padding:10px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
                                <?= $emoji ?>
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:700;color:#223a74;font-size:16px;"><?= htmlspecialchars($rd['reward_title'] ?? '—') ?></div>
                                <div style="color:#6b93d1;font-size:13px;margin-top:6px;">Redeemed: <?= fmt_date($rd['redeemed_at']) ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:800;color:#c23a00;font-size:18px;">- <?= intval($rd['cost']) ?> pts</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include_once("../includes/footer.php"); ?>

<!-- small JS for tabs -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.tasks-pill').forEach(btn => {
        btn.addEventListener('click', function(){
            document.querySelectorAll('.tasks-pill').forEach(b=>b.classList.remove('active'));
            this.classList.add('active');

            const target = this.dataset.target;
            document.querySelectorAll('.wallet-tab-section').forEach(s => s.style.display = 'none');
            const el = document.getElementById(target);
            if (el) el.style.display = 'block';
        });
    });
});
</script>