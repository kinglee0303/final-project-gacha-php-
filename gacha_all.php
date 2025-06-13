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

//查看所有角色
$sql = "SELECT * FROM role";
$result_menu = $conn->query($sql);

// 將查詢結果存入陣列
$roles_menu = [];
while ($row = $result_menu->fetch_assoc()) {
    $roles_menu[] = [
	'role_id' => $row['role_id'],
        'role_name' => $row['role_name'],
	'role_weight' => $row['role_weight'],
        'star' => $row['star']
    ];
}
$conn->close();

$_SESSION['role_all']=$roles_menu;
echo json_encode([
    'success' => true,
    'player_id' => $player_id,
    'roles' => $roles_menu
], JSON_UNESCAPED_UNICODE );
header("Location: gacha.php?msg=all_gacha_load");


