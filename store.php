<?php
session_start();
if (!isset($_SESSION['player_id'])) {
    echo "請先登入！";
    exit;
}

$player_id = $_SESSION['player_id'];
$tool_prices = [
    1 => 50,   // 回復藥水
    2 => 80,   // 經驗書
    3 => 100,  // 進化石
    4 => 120,  // 強化石
    5 => 200,  // 金幣箱
    6 => 160,  // 抽卡券
    7 => 300   // 神秘寶箱
];
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "phpmyadmin";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

$message = "";

// 儲值模擬
if (isset($_POST['recharge'])) {
    $amount = intval($_POST['money']);
    $sql = "UPDATE player SET player_money = player_money + ? WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $amount, $player_id);
    $stmt->execute();
    $stmt->close();
    $message = "成功儲值 $amount 金幣！";
}

// 抽卡石兌換
if (isset($_POST['exchange'])) {
    $exchange_cost = 160;
    $sql = "SELECT player_money FROM player WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $player_id);
    $stmt->execute();
    $stmt->bind_result($money);
    $stmt->fetch();
    $stmt->close();

    if ($money >= $exchange_cost) {
        $sql = "UPDATE player SET player_money = player_money - ?, gacha_stone = gacha_stone + 1 WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $exchange_cost, $player_id);
        $stmt->execute();
        $stmt->close();
        $message = "兌換成功！";
    } else {
        $message = "金幣不足，無法兌換抽卡石。";
    }
}

// 購買道具
if (isset($_POST['buy_tool'])) {
    $tool_id = intval($_POST['tool_id']);
   

    if (array_key_exists($tool_id, $tool_prices)) {
        $price = $tool_prices[$tool_id];

        // 取得玩家金幣
        $sql = "SELECT player_money FROM player WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $player_id);
        $stmt->execute();
        $stmt->bind_result($money);
        $stmt->fetch();
        $stmt->close();

        if ($money >= $price) {
            // 扣金幣
            $conn->begin_transaction();
            $sql = "UPDATE player SET player_money = player_money - ? WHERE player_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $price, $player_id);
            $stmt->execute();
            $stmt->close();

            // 加入或更新道具數量
            $sql = "INSERT INTO player_tool (player_id, tool_id, quantity)
                    VALUES (?, ?, 1)
                    ON DUPLICATE KEY UPDATE quantity = quantity + 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $player_id, $tool_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $message = "購買成功！";
        } else {
            $message = "金幣不足，無法購買該道具。";
        }
    } else {
        $message = "無效的道具選項。";
    }
}

// 顯示餘額
$sql = "SELECT player_money, gacha_stone FROM player WHERE player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_id);
$stmt->execute();
$stmt->bind_result($money, $stone);
$stmt->fetch();
$stmt->close();

// 取得商城道具列表
$tools = [];
$result = $conn->query("SELECT tool_id, tool_name FROM tool");
while ($row = $result->fetch_assoc()) {
    $tools[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>商城</title>
    <style>
        .balance {
            position: absolute;
            top: 10px;
            right: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="balance">
        💰 金幣: <?= $money ?> | 🪨 抽卡石: <?= $stone ?>
    </div>

    <h1>商城</h1>

    <form method="post">
        <label>儲值金額：</label>
        <input type="number" name="money" min="1" value="160">
        <button type="submit" name="recharge">儲值</button>
    </form>

    <br>

    <form method="post">
        <button type="submit" name="exchange">花費160金幣兌換1顆抽卡石</button>
    </form>

    <hr>

    <h2>道具購買</h2>
    <form method="post">
        <label>選擇道具：</label>
        <select name="tool_id">
            <?php foreach ($tools as $tool): ?>
                <option value="<?= $tool['tool_id'] ?>">
                    <?= $tool['tool_name'] ?>（價格：<?= $tool_prices[$tool['tool_id']] ?? '未知' ?> 金幣）
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="buy_tool">購買</button>
    </form>

    <br>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
</body>
</html>
