<?php
If(!isset($_POST['id']) || !isset($_POST['key'])) {
  echo "invalid input";
  exit();
}
//session
session_start();

$id = $_POST['id'];
$key = $_POST['key'];
$pattern="/[^a-zA-Z0-9]+/";

if(preg_match($pattern, $id) || preg_match($pattern, $key)) {
  //echo "not allowed input";
  header("Location: login.html?msg=not allowed input");
  exit();
}
// must create new database,name: final_gacha
// put github_sql content  in final_gacha
$mysqli = new mysqli("localhost","root","121314","phpmyadmin");
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
echo "id:";
echo $_POST ['id'];
echo "<br> key:";
echo $_POST ['key'];
echo "<br>";

$sql_str = "SELECT * FROM player WHERE `player_id`='" . $id . "' AND `player_password`='" . $key . "'";
if ($result = $mysqli -> query($sql_str)) { 
  $row = $result->fetch_array();
  $db_id = $row['player_id'];
  $db_key = $row['player_password'];
  $db_nick = $row['player_name'];
  $db_stone = $row['gacha_stone'];
  $db_money = $row['player_money'];
  $result -> free_result();
} else {
  echo "Login Failed1_SQL語法或查詢錯誤";
  exit();
}
$mysqli -> close();

if(!($id==$db_id && $key==$db_key)) {
  echo "Login Failed2_查詢成功但帳密不符";
  header("Location: login.html?message=" . urlencode("登入失敗，請再試一次"));

  exit();
}

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$id_cache = $redis->get($db_id);
if(!empty($id_cache)) {
  $redis->del($id_cache);
}

$token=md5($id . time() . floor(rand()*100000+0.5));
$redis->set($id, $token);
$redis->expire($id, 86400);
$redis->set($token, $id);
$redis->expire($token, 86400);
$redis->quit;

echo "welcome name:" . $db_nick . "<br>";
echo "your token is " . $token;

//record db_nick
$_SESSION['player_name'] = $db_nick;
$_SESSION['player_id'] = $db_id;
$_SESSION['player_stone'] = $db_stone;
$_SESSION['player_money'] = $db_money;
//into gacha.html
header('Location: gacha.php');
