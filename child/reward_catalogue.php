<?php
// child/reward_catalogue.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['child_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once("../includes/db_connection.php");
include_once("../includes/header_child.php");

// small helper to pick emoji by category (extended)
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

// fetch child's points and parent_id
$child_id = intval($_SESSION['child_id']);
$sql = "SELECT child_points, parent_id FROM Child WHERE child_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($child_points, $parent_id);
$stmt->fetch();
$stmt->close();

// fetch rewards created by this parent (active)
$rewards = [];
$q = "SELECT reward_id, reward_title, reward_desc, reward_cost, reward_category
      FROM Reward
      WHERE parent_id = ? AND reward_status = 'active'
      ORDER BY created_at DESC";
if ($stmt = $conn->prepare($q)) {
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $rewards[] = $r;
    $stmt->close();
}
?>

<div class="parent-content">
    <div style="display:flex;align-items:center;gap:18px;justify-content:space-between;">
        <h2 style="color:#2F4B8A; font-weight:700; font-size:32px;">My Rewards</h2>

        <!-- Points badge -->
       <div id="myPointsBadge" style="background:#1180e6;color:#fff;padding:10px 16px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:8px;box-shadow:0 6px 18px rgba(17,128,230,0.18);">
    <span style="opacity:0.9;">Points</span>
    <span style="font-size:18px;">⭐</span>
    <span id="myPointsValue"><?php echo intval($child_points); ?></span>
</div>

    </div>

    <div style="height:18px;"></div>

    <!-- rewards grid -->
    <div class="reward-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;">
        <?php foreach ($rewards as $rw): 
            $emoji = emoji_for_category($rw['reward_category'] ?? '');
            $canAfford = intval($child_points) >= intval($rw['reward_cost']);
        ?>
            <div class="card reward-card" data-reward-id="<?= intval($rw['reward_id']) ?>" style="position:relative;padding:18px;border-radius:16px;background:#fff;">
                <!-- light blue inner bg like screenshot -->
                <div style="background:#f1f8ff;padding:16px;border-radius:12px;min-height:120px;display:flex;flex-direction:column;justify-content:flex-start; gap:10px;">
                    <!-- emoji top-left / in screenshot it's image box - we place emoji top-right -->
                   <div style="display:flex;justify-content:center;margin-top:10px;">
    <div style="font-size:44px;background:#fff;padding:12px 16px;border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,0.1);">
        <?= $emoji ?>
    </div>
</div>


                    <!-- title and desc -->
                    <div style="margin-top:6px;">
                        <div style="font-weight:700;color:#223a74;font-size:16px;margin-bottom:6px;"><?= htmlspecialchars($rw['reward_title']) ?></div>
                        <div style="color:#6b93d1;font-size:13px;min-height:36px;"><?= htmlspecialchars($rw['reward_desc']) ?></div>
                    </div>
                </div>

                <!-- footer row with cost + button -->
               <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding:12px;background:#e8f3ff;border-radius:12px;">

                    <div style="color:#2F4B8A;font-weight:700;"> Cost: <span style="color:#1180e6;"><?= intval($rw['reward_cost']) ?> Points</span></div>
                    <div>
                        <button class="btn redeem-btn" data-reward-id="<?= intval($rw['reward_id']) ?>" data-cost="<?= intval($rw['reward_cost']) ?>" style="<?= $canAfford ? '' : 'opacity:0.6;' ?>">
                            <span style="margin-right:8px;">🛍️</span> Redeem Reward
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($rewards)): ?>
            <div class="card" style="padding:20px;">
                <p style="color:#556;">No rewards available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Confirm Modal -->
<div id="redeemModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:420px;">
        <button class="modal-close" onclick="closeRedeemModal()">✕</button>
        <h3 id="modalTitle" style="color:#2F4B8A;"></h3>
        <p id="modalDesc" style="color:#555;"></p>

        <div id="modalNeedMsg" style="color:#c24a00;font-weight:700;margin-top:8px;display:none;"></div>

        <div style="margin-top:14px;display:flex;gap:12px;justify-content:flex-end;">
            <button class="btn" onclick="closeRedeemModal()">Cancel</button>
            <button id="confirmRedeemBtn" class="btn">Confirm Redeem</button>
        </div>
    </div>
</div>

<?php include_once("../includes/footer.php"); ?>

<!-- JS (load your new file) -->
<script src="../js/rewards_child.js"></script>
