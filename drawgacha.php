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
$sql = "SELECT gacha_stone FROM player WHERE player_id = $player_id";
$stmt = $conn->prepare($sql);

$stmt->execute();
$stmt->bind_result($gacha_stone);  //查詢結果中的欄位（例如 gacha_stone）綁定到變數 $gacha_stone 上
$result = $stmt->get_result();// 取得查詢結果物件

if ($result->num_rows === 0) {// 如果找不到任何資料（表示資料庫中沒有這個 player_id）
    echo json_encode(['success' => false, 'message' => '找不到玩家']);
    exit;
}

$row = $result->fetch_assoc();// 從結果中取得一筆資料（以關聯式陣列形式）
$gacha_stone = $row['gacha_stone'];
echo "before : ". $gacha_stone. "<br>";

$stmt->close();

if ($gacha_stone <= 0) {
    echo json_encode(['success' => false, 'message' => '抽卡石不足']);
    exit;
}

// 扣除一顆抽卡石
$sql = "UPDATE player SET gacha_stone = gacha_stone - 1 WHERE player_id = $player_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
echo "after : ". $gacha_stone. "<br>";

$conn->close();

echo "gacha";
?>
