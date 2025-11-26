<?php
// child/redeem_reward.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

// simple auth check
if (!isset($_SESSION['child_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include_once("../includes/db_connection.php");

$child_id = intval($_SESSION['child_id']);
$reward_id = isset($_POST['reward_id']) ? intval($_POST['reward_id']) : 0;
$confirm = isset($_POST['confirm']) ? boolval($_POST['confirm']) : false;

if ($reward_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid reward']);
    exit;
}

// fetch child points and parent_id
$q = "SELECT child_points, parent_id FROM Child WHERE child_id = ?";
$stmt = $conn->prepare($q);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($child_points, $parent_id);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Child not found']);
    $stmt->close();
    exit;
}
$stmt->close();

// fetch reward
$q2 = "SELECT reward_cost, reward_title, reward_status FROM Reward WHERE reward_id = ? AND parent_id = ?";
$stmt = $conn->prepare($q2);
$stmt->bind_param("ii", $reward_id, $parent_id);
$stmt->execute();
$stmt->bind_result($reward_cost, $reward_title, $reward_status);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Reward not found']);
    $stmt->close();
    exit;
}
$stmt->close();

if ($reward_status !== 'active') {
    echo json_encode(['success' => false, 'message' => 'This reward is not available']);
    exit;
}

// if not enough points
if (intval($child_points) < intval($reward_cost)) {
    $need = intval($reward_cost) - intval($child_points);
    echo json_encode(['success' => false, 'need_more' => true, 'need' => $need, 'message' => "You need $need more points"]);
    exit;
}

// perform transaction: deduct points and insert into Redeemed
$conn->begin_transaction();

$update = $conn->prepare("UPDATE Child SET child_points = child_points - ? WHERE child_id = ? AND child_points >= ?");
$cost = intval($reward_cost);
$update->bind_param("iii", $cost, $child_id, $cost);
$update->execute();

if ($update->affected_rows <= 0) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to deduct points.']);
    $update->close();
    exit;
}
$update->close();

// insert into Redeemed (store cost)
$insert = $conn->prepare("INSERT INTO Redeemed (child_id, parent_id, reward_id, cost) VALUES (?, ?, ?, ?)");
$insert->bind_param("iiii", $child_id, $parent_id, $reward_id, $cost);
$ok = $insert->execute();
$insert->close();

if (!$ok) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to record redemption.']);
    exit;
}

$conn->commit();

// fetch new points
$stmt = $conn->prepare("SELECT child_points FROM Child WHERE child_id = ?");
$stmt->bind_param("i", $child_id);
$stmt->execute();
$stmt->bind_result($new_points);
$stmt->fetch();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Reward redeemed successfully!', 'new_points' => intval($new_points), 'reward_title' => $reward_title]);
exit;
