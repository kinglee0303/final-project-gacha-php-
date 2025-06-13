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
$password = "121314";
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
     $amount = isset($_POST['exchange_amount']) ? intval($_POST['exchange_amount']) : 0;
    $exchange_cost = 150;
    $total_cost = $exchange_cost * $amount;
    $sql = "SELECT player_money FROM player WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $player_id);
    $stmt->execute();
    $stmt->bind_result($money);
    $stmt->fetch();
    $stmt->close();

    if ($money >= $total_cost) {
        $sql = "UPDATE player SET player_money = player_money - ?, gacha_stone = gacha_stone + ?  WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $total_cost, $amount,$player_id);
        $stmt->execute();
        $stmt->close();
        $message = "兌換成功！共花費金幣：".$total_cost.",得到".$amount."顆抽卡石。";
    } else {
        $message = "金幣不足, 無法兌換抽卡石。";
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
$_SESSION['player_stone']=$stone;
$_SESSION['player_money']=$money;

// 取得商城道具列表
$tools = [];
$result = $conn->query("SELECT tool_id, tool_name FROM tool");
while ($row = $result->fetch_assoc()) {
    $tools[] = $row;
}
$conn->close();

if(strpos($message, '成功儲值') === 0||$message==='購買成功！'||$message==='金幣不足, 無法兌換抽卡石。'||$message==='金幣不足，無法購買該道具。')
{
 $t_message="目前金幣餘額： ".$money;
}
else if(strpos($message, '兌換成功') === 0)
{
 $t_message="目前金幣餘額： ".$money.", 目前抽卡石餘額: ".$stone;
}

$_SESSION['message']=$message;
$_SESSION['t_message']=$t_message;


?>

<!DOCTYPE html>
<html>
<head>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>商城頁面</title>
    <style>
    .balance {
            margin: 0;
            font-weight: bold;
	    font-size: 30px;
	    display: inline;
	    color: rgba(255, 255, 255);
        }
	body {
            height: 100%;
            min-height: 100vh;
            margin: 20px;
            padding: 0;
            background-image: url('shop_background.jpg');
            background-size: cover;         /* 背景圖片填滿畫面 */
            background-position: center;    /* 圖片置中 */
            background-repeat: no-repeat;   /* 不重複鋪排 */
        }
	.store {
	    text-align: center;
            background-color: rgba(255, 255, 255, 0.40); /* 白色 + 50% 不透明度 */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
	    width: 700px;
	    height: 660px;
	    padding: 25px;
	}
	
	 .back {
	    width: 15vh;
	    height: 7vh;
            padding: 12px;   
            background-color: #1c92e9;
            color: white;
            border: none;
	    font-weight: bold;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1.5em;
            transition: background-color 0.3s ease;

	  }
	 .top {
	    text-align: left;
	    padding: 10px; 
	 }
	 .block {
            display: flex;
            justify-content: center;   /* 水平置中 */
            align-items: center;       /* 垂直置中 */
            height: 70vh;             /* 整個視窗高度 */
	   

        }
	.tabs {
	         display: flex;
		 width: 100%;
		 max-width: 690px;
		 margin: 0px auto;
		 border-radius: 12px;
		 overflow: hidden;
		 box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);		

	}
	.tab-button {
	    flex: 1;
	    padding: 15px 0;
	    font-size: 25px;
	    color: #333;
	    background: rgba(225, 233, 238, 0.5);
	    border: none;
	    cursor: pointer;
	    transition: all 0.3s ease;
	    font-weight: normal;
	    border-right: 1px solid #ccc;
	}
	.tab-button.active {
	    background: linear-gradient(to bottom, #48acf5, #0065f6);
	    color: #fff;
	    font-weight: bold;
	    box-shadow: inset 0 -5px 0 #1e90ff;
	}
	.tab-content {
	     display: none;
	     align-items: center;       /* 垂直置中 */

	    height: 390px;
	    font-size: 30px;
	    padding:5px;
	    
	}
	.tab-content.active {
	    display: block;
	    background-color: rgba(255, 255, 255, 0.5);
            font-weight: bold;
	    border-radius: 12px;
    	    border: 6px solid rgba(55, 138, 226, 0.5);
	    padding:15px 0 0 0;
	}
	.tab-button:last-child {
	    border-right: none;
	}

	.tab-button:hover {
	    color: black;
	    background: linear-gradient(to bottom, #7ab6ff, #76c4fd);
	    font-weight: bold;
	}
	.money, .tool  {
	    font-size: 30px;

	}
	.money.input {
	    width: 100px;
	}
	.money.h1, .tool.h1 {
	  font-size: 33px;
	  font-weight: bold;
	  padding 15px 0;
	  background: linear-gradient(to right, rgba(30, 105, 222, 0.8), rgba(109, 179, 242, 0.5));
	  color: white;
	  border-radius: 12px;
	  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
	  text-align: center;
	  margin-bottom: 20px;
	  text-shadow: 1px 1px 2px black;
	  width: 80%;
	  display: block;
  	  margin: 10px  auto 20px  auto;
  	  
	}
	
	.money.button , .tool.button {
	  background: #62c2f1; 
	  color: black;
	  font-weight: bold;
	  padding: 10px 20px;
	  font-size: 22px;
	  border: none;
	  border-radius: 15px;
	  cursor: pointer;
	  transition: all 0.3s ease;
	  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
	}

	.money.button:hover , .tool.button:hover {
	  transform: translateY(-2px);
	  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
	  background: #eeaa13; 
	}
	.money.input {
	  width: 200px;              /* 控制整體寬度 */
	  height: 40px;              /* 控制整體高度 */
	  font-size: 25px;           /* 文字大小 */
	  padding: 0 12px;         /* 內距 */
	  border: 2px solid #ccc;    /* 邊框 */
	  border-radius: 8px;        /* 圓角 */
	  box-sizing: border-box;    /* 讓 padding 不會超出寬度 */
	  zoom: 1.2;  
	}
	.tool.select {
	  width: 100%;
	  max-width: 320px;
	  padding: 10px 10px;
	  font-size: 22px;
	  font-weight: bold;
	  border: 2px solid #6da9e4;
	  border-radius: 12px;
	  background: #f0f8ff;
	  color: #333;
	  box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.2);
	  outline: none;
	  transition: all 0.3s ease;
	  cursor: pointer;
	    /* 不移除系統樣式 */
  	  -webkit-appearance: menulist;
  	  -moz-appearance: menulist;
  	  appearance: menulist;
	}

	/* 滑鼠移上時改變外觀 */
	.tool.select:hover {
	  border-color: #4c91d9;
	  background: linear-gradient(to bottom, #e8f3ff, #c6e2ff);
	}

	/* 聚焦時樣式 */
	.tool.select:focus {
	  box-shadow: 0 0 5px 2px #87bfff;
	  border-color: #4c91d9;
	}
        .tool_block {
	  display: flex;
  	  flex-direction: column;
  	  align-items: center;
  	  justify-content: center;
  	  padding: 100px 0 0 0;	  
	}

	.swal-popup {
	  font-size: 18px; /* 整體字體大小 */  
	  width: auto !important;
  	  max-width: none !important;
	}

	.swal-title {
	  font-size: 33px; /* 標題字體大小 */
	}

	.swal-text {
	  font-size: 25px; /* 這就是 text 的字體大小 */
	}


    </style>
</head>
<body>
     
    <div class="shop">

	    <div class="top">
		  <button class="back" type="button" onclick="location.href='gacha.php'">返回上一頁</button>
	          <h1 class="balance">&nbsp;  🪨 抽卡石: <?= $stone ?> |  💰 金幣: <?= $money ?></h1> 
	    </div>
	    <div class="block">
		    <div class="store">
			    <h1 style="font-weight: bold; font-size: 50px;margin-top:20px; color: white;  text-shadow:-2px -2px 0 #000,2px -2px 0 #000,-2px  2px 0 #000,2px  2px 0 #000;"> 商 城 </h1>
			     <!-- 分頁標籤 -->
			    <div class="tabs">
			        <button class="tab-button active" onclick="showTab('recharge-tab')">儲值 / 兌換</button>
			        <button class="tab-button" onclick="showTab('tool-tab')">道具購買</button>
			    </div>
			    <!-- 儲值 / 兌換頁面 -->
			    <div class="tab-content active" id="recharge-tab">
			        <form  method="post">
				    <div style="width:5px;"></div>
				    <h1 class="money h1">💰 儲值金幣 💰 </h1>
			            <label class="money label">儲值金額：</label>
			            <input class="money input" type="number" name="money" min="1" value="150">
			            <button class="money button" type="submit" name="recharge">儲值</button>
			        </form>			        
				<br>
				<hr style="height:2px;border-width:0;color:gray;background-color:gray; margin: 0 0 25px 0;">
			     <form method="post">
				    <h1 class="money h1">🪨 兌換抽卡石 🪨</h1>
				    <!--<label class="money label">花費150金幣兌換1顆抽卡石：</label>
			            <button class="money button" type="submit" name="exchange">兌換</button>-->
				    
				    <label class="money label">花費150金幣可兌換1顆抽卡石</label><br>
				    <label class="money label">兌換數量：</label>
				    <input class="money input" type="number" name="exchange_amount" min="1" value="1" required>
				    <button class="money button" type="submit" name="exchange">兌換</button>
			     </form>
			     <br>
			</div>
			<!-- 道具購買頁面 -->
			    <div class="tab-content" id="tool-tab">
			    <div class="tool_block">
			        <h1 class="tool h1">📦 道具購買 📦</h1>
			        <form style=" align-items: center;" method="post">
			            <label class="tool label">選擇道具：</label>
			            <select  class="tool select" name="tool_id">
			                <?php foreach ($tools as $tool): ?>
			                    <option value="<?= $tool['tool_id'] ?>">
			                        <?= $tool['tool_name'] ?>（價格：<?= $tool_prices[$tool['tool_id']] ?? '未知' ?> 金幣）
			                    </option>
			                <?php endforeach; ?>
			            </select>
			            <button class="tool button" type="submit" name="buy_tool">購買</button>
			        </form>
			    </div></div>
		    </div>
	    </div>	
        </div>
	<!-- 分頁切換的 JavaScript -->
	<script>
	function showTab(tabId) {
	    // 取消所有分頁內容顯示
	    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
	    // 顯示選中的分頁內容
	    document.getElementById(tabId).classList.add('active');

	    // 更新 tab 標籤樣式
	    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
	    event.target.classList.add('active');
	}
	</script>
	<?php if (isset($_SESSION['message'])): ?>
	    <script>
	        const message = <?php echo json_encode($_SESSION['message'], JSON_UNESCAPED_UNICODE); ?>;
	 	const t_message = <?php echo json_encode($_SESSION['t_message'], JSON_UNESCAPED_UNICODE); ?>;
	        if(message.startsWith('成功儲值')||message==='購買成功！'||message.startsWith('兌換成功！'))
		{
		      Swal.fire({
	               icon: 'success',
	               title: message,
	               text: t_message,
	               confirmButtonText: '好',
			  customClass: {
			    popup: 'swal-popup',
			    title: 'swal-title',
			    htmlContainer: 'swal-text'
			  }
	              });
			const newURL = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, newURL);


		}
		else if(message==='金幣不足, 無法兌換抽卡石。'||message==='金幣不足，無法購買該道具。')
		{
       	             Swal.fire({
	               icon: 'error',
	               title: message,
	               text: t_message,
	               confirmButtonText: '好',
			  customClass: {
			    popup: 'swal-popup',
			    title: 'swal-title',
			    htmlContainer: 'swal-text'
			  }
	              });
			const newURL = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, newURL);

		}
	    </script>
	    <?php unset($_SESSION['message']); ?>
	<?php endif; ?>

</body>
</html>
