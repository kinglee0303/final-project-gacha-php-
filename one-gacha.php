<?php
session_start();

if (!isset($_SESSION['player_name'])) {
    echo json_encode(['success' => false, 'message' => 'no login']); // 尚未登入
    exit;
}

$player_id = $_SESSION['player_id'];

// 資料庫連線設定
$servername = "localhost";
$username = "root";  // 請依環境修改
$password = "121314";
$dbname = "phpmyadmin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    exit;
}

// 檢查抽卡石數量
$sql = "SELECT gacha_stone FROM player WHERE player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_id);
$stmt->execute();
$result = $stmt->get_result();// 取得查詢結果物件
$stmt->close();
if ($result->num_rows === 0) {// 如果找不到任何資料（表示資料庫中沒有這個 player_id）
    echo json_encode(['success' => false, 'message' => 'not found player']);
    exit;
}

$row = $result->fetch_assoc();// 從結果中取得一筆資料（以關聯式陣列形式）
$gacha_stone = $row['gacha_stone'];


if ($gacha_stone <= 0) {
    echo json_encode(['success' => false, 'message' => 'no gacha stone']);
    exit;
}

// 扣除一顆抽卡石
$sql = "UPDATE player SET gacha_stone = gacha_stone - 1 WHERE player_id = ?";
$stmt = $conn->prepare($sql);//用於SQL語句中有參數
$stmt->bind_param("s", $player_id);
$stmt->execute();
$stmt->close();

// 抽一個隨機角色
$sql = "SELECT role_id, role_name FROM role ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql); //用於SQL語句中沒有參數
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => '沒有可抽角色']);
    exit;
}
$row = $result->fetch_assoc();
$role_id = $row['role_id'];
$role_name = $row['role_name'];


// 檢查玩家是否已有該角色
$sql = "SELECT * FROM player_role WHERE player_id = ? AND role_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $player_id, $role_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    // 尚未擁有，新增角色
    $stmt->close();
    $sql = "INSERT INTO player_role (player_id, role_id, owned, quantity) VALUES (?, ?, TRUE, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $player_id, $role_id);
    $stmt->execute();
    $stmt->close();
    echo "This role does not exist, the backpack list has been updated.<br>";
} else {
    // 已擁有，將 quantity 欄位 +1
    $stmt->close();
    $sql = "UPDATE player_role SET quantity = quantity + 1 WHERE player_id = ? AND role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $player_id, $role_id);
    $stmt->execute();
    $stmt->close();
    echo "This role does exist, the backpack list has been updated.<br>";
}



echo "before : ". $gacha_stone. "<br>"; //
echo "gacha $role_id : $role_name <br>";
$conn->close();

echo "gacha ended";
?>
