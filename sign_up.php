<?php
// 取得 POST 資料
$player_id = $_POST['player_id'] ?? '';
$player_name = $_POST['player_name'] ?? '';
$player_password = $_POST['player_password'] ?? '';
$pattern="/[^a-zA-Z0-9]+/";

if(preg_match($pattern, $player_id) || preg_match($pattern, $player_name)||preg_match($pattern, $player_password)) {
  header("Location: sign_up.html?msg=not allowed input");

  exit();
}


// 資料庫連線
$host = "localhost";
$dbname = "final_gacha";
$user = "zhouu";
$pass = "ispower";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 插入資料
    $stmt = $pdo->prepare("INSERT INTO player (player_id, player_name, player_password,gacha_stone,player_money) VALUES (?, ?, ?, 100, 1000)");
    $stmt->execute([$player_id, $player_name, $player_password]);

    //echo "Registration successful!";
    header("Location: login.html?msg=Registration Successful");
    exit();
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        // 23000: 重複主鍵或唯一鍵違反（如 player_id 重複）
        $errorMsg = $e->getMessage();
	$stmt = $pdo->prepare("SELECT * FROM player WHERE player_id = ?");
	$stmt->execute([$player_id]);
	if ($stmt->rowCount() > 0) {
            header("Location: sign_up.html?msg=duplicate_id");
        } elseif (strpos($errorMsg, 'player_name') !== false) {
            header("Location: sign_up.html?msg=duplicate_name");
        }
	else{echo "Error: " . $e->getMessage();} 
        //header("Location: register.html?msg=duplicate");
    } else {
        // 其他錯誤
        echo "Error: " . $e->getMessage();

       // header("Location: register.html?msg=error");
    }
}
?>
