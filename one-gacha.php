<?php
session_start();

if (!isset($_SESSION['player_id'])) {
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
$sql = "SELECT gacha_stone , gacha_counter FROM player WHERE player_id = ?";
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
$gacha_counter = $row['gacha_counter'];

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

//建立抽卡池
$sql = "SELECT role_id, role_name, role_weight, star FROM role";
$result = $conn->query($sql);
$roles = [];
$total_weight = 0;
$max_star = 5;

while ($r = $result->fetch_assoc()) {
    $roles[] = $r;
    $total_weight += $r['role_weight'];
}
//當你的資料表為空，$total_weight 會是 0
if ($total_weight <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid total weight']);
    exit;
}

// 檢查保底，<=1表示最後一抽必須為5星
$is_guaranteed = ($gacha_counter <= 1);
if ($is_guaranteed) {
    $selected_star = $max_star;
    foreach ($roles as $r) {
        if ($r['star'] == $max_star) {
            $selected_id = $r['role_id'];
            $selected_name = $r['role_name'];
            break; // 假設只有一張 5 星卡
        }
    }
}else {
    $rand = mt_rand(1, $total_weight); // 在 1 ~ 總權重之間隨機取一個數
    $acc = 0;
    foreach ($roles as $r) {
        $acc += $r['role_weight']; // 累加目前的權重
        if ($rand <= $acc) {
            //$selected = $r; // 當隨機數落在這張卡的權重範圍內，選擇它
            $selected_star = $r['star'];
            $selected_id = $r['role_id'];
            $selected_name = $r['role_name']; 
            break;
        }
    }
}
// 判斷這張抽到的卡是否是最高權重
if ($selected_star == $max_star) {
    $gacha_counter = 40; // 若是最高權重卡，重置保底倒數
} else {
    $gacha_counter--; // 否則照常遞減
}

// 檢查玩家是否已有該角色
$sql = "SELECT * FROM player_role WHERE player_id = ? AND role_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $player_id, $selected_id);
$stmt->execute();
$stmt->store_result();
$own_role = $stmt->num_rows;
$stmt->close();
// 判斷是否已有該角色
if ($own_role == 0) {
    // 尚未擁有，新增角色
    $sql = "INSERT INTO player_role (player_id, role_id, owned, quantity) VALUES (?, ?, TRUE, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $player_id, $selected_id);
    $stmt->execute();
    
    //echo "This role does not exist, the backpack list has been updated.<br>";
} else {
    // 已擁有，將 quantity 欄位 +1
    $sql = "UPDATE player_role SET quantity = quantity + 1 WHERE player_id = ? AND role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $player_id, $selected_id);
    $stmt->execute();
    
    //echo "This role does exist, the backpack list has been updated.<br>";
}

//更新保底計數
$stmt->close();
$sql = "UPDATE player SET gacha_counter = ? WHERE player_id = ?";
$stmt = $conn->prepare($sql);//用於SQL語句中有參數
$stmt->bind_param("is",$gacha_counter, $player_id);
$stmt->execute();
$stmt->close();

// 最終回傳 JSON 結果
$response = [
    'success' => true,
    'selected_id' => $selected_id,
    'selected_name' => $selected_name,
    'selected_star' => $selected_star,
    'gacha_counter' => $gacha_counter,
    'gacha_stone_before' => $gacha_stone,
    'gacha_stone_after' => $gacha_stone-1,
    'message_type' => ($is_guaranteed ? '保底出貨' : '一般抽卡'),
    'message_counter' => "Just draw $gacha_counter more times to get a 5-star character.",
    'message_own' => ($own_role==0 ? 'This role does not exist, the backpack list has been updated.' 
                                    : 'his role does exist, the backpack list has been updated.')
];

//echo "before : ". $gacha_stone. "<br>"; //
//echo "gacha $selected_id : $selected_name <br>";
//echo "gacha star = $selected_star <br>";
//echo "Just draw $gacha_counter more times to get a 5-star character.";
$conn->close();
echo "gacha ended<br>";
//header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;

?>
