<?php
session_start();

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => '尚未登入'], JSON_UNESCAPED_UNICODE ); // 尚未登入
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
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗'], JSON_UNESCAPED_UNICODE ));
    exit;
}

// 翻出背包
$sql = "SELECT role_id, quantity FROM player_role WHERE player_id = ? AND owned = TRUE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_id);
$stmt->execute();
$result_bag = $stmt->get_result();// 取得查詢結果物件
$stmt->close();
if ($result_bag->num_rows === 0) {// 如果找不到任何資料（表示資料庫中沒有這個 player_id）
    header("Location: gacha.php?msg=role_null");
    //echo json_encode(['success' => false, 'message' => '背包為空'], JSON_UNESCAPED_UNICODE );
    exit;
}
$stmt->close();
$sql = "SELECT role_id, role_name, star FROM role";
$result_menu = $conn->query($sql);

// 將查詢結果存入陣列
$roles_menu = [];
while ($row = $result_menu->fetch_assoc()) {
    $roles_menu[$row['role_id']] = [
        'role_name' => $row['role_name'],
        'star' => $row['star']
    ];
}


// 將查詢結果存入陣列
$roles = [];
while ($row = $result_bag->fetch_assoc()) {
    $role_id = $row['role_id'];
    $roles[] = [
      'role_id' => $role_id,
      'role_name' => $roles_menu[$role_id]['role_name'],
      'quantity' => $row['quantity'],
      'star' => $roles_menu[$role_id]['star']
    ];
}

$conn->close();
// 輸出 JSON 格式的背包資料
echo json_encode([
    'success' => true,
    'player_id' => $player_id,
    'roles' => $roles
], JSON_UNESCAPED_UNICODE );

//傳遞回應至gacha.php顯示
$_SESSION['player_role']=$roles;
header("Location: gacha.php");

?>
