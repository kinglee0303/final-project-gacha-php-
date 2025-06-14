# final-project-gacha-php-
Project Name: final-project-gacha-php-

Project Description: According to the repository description, this project is a "web server final report in mcu.".

Content Overview: The list of files in the project indicates it contains files related to a web server, such as .gitignore, README.md. It includes PHP (drawgacha.php, gacha.php, login.php, phpinfo.php) and HTML (login.html, tlogin.html) files. The presence of these files suggests the project likely incorporates a Gacha system (drawgacha.php, gacha.php), and user login functionality (login.html, login.php, tlogin.html). Additionally, there is a user.sql file and a phpmyadmin directory, which may indicate that the project uses an SQL database to store user or gacha-related data. The phpinfo.phpphpinfo.php file is typically used to display detailed information about the PHP environment.

Languages Used: The project primarily uses PHP (56.0%) and HTML (44.0%).

Repository Status: The repository currently has 1 star, 0 forks, a total of 8 commits, and 3 contributors. There are currently no releases or packages published.

## 功能介紹
* 註冊並登入帳號
![](https://meee.com.tw/zYO3Ymr.png)<br>
![](https://i.meee.com.tw/9bfTHNp.png)<br>
* `抽卡頁面`，可選要單抽還是十抽<br>
![](https://i.meee.com.tw/pV62gYo.png)<br>
* `抽卡圖鑑`可以查看角色抽中的機率，公開透明為原則<br>
![](https://i.meee.com.tw/2IkVWp6.png)<br>

> [!TIP]
> 40抽保底一次UR<br>

* 點擊`道具背包`即可查看道具內容<br>
![](https://i.meee.com.tw/N8bOwhs.png)<br>

* 點擊`角色背包`即可查看抽到的角色<br>
![](https://i.meee.com.tw/iopvaXX.png)<br>

* 無法抽卡時，可以到`商城`兌換所需的石頭<br>
> [!NOTE]
> 抽卡石🪨是用來抽卡的，金幣🪙則是用來兌換成抽卡石<br>
> 金幣儲值僅供演示用，不會花到半毛錢<br>

![](https://i.meee.com.tw/qAUwvCz.png)<br>
* 此外，金幣還可以用來購買`回復藥水`。`回復藥水`目前暫無任何功能。
![](https://i.meee.com.tw/uhcEU4k.png)<br>


## 程式介紹
### 登入系統
1. **驗證是否有收到帳號與密碼**
```ruby
If(!isset($_POST['id']) || !isset($_POST['key'])) {
  echo "invalid input";
  exit();
}
```
* 檢查是否有收到 ''POST'' 的 ''id''（帳號）和 ''key''（密碼），如果沒有就中止程式。

2. **開啟 session 並取出輸入資料**
```ruby
session_start();
$id = $_POST['id'];
$key = $_POST['key'];
```

3. **驗證是否為合法英數字**
```ruby
$pattern="/[^a-zA-Z0-9]+/";
if(preg_match($pattern, $id) || preg_match($pattern, $key)) {
  header("Location: login.html?msg=not allowed input");
  exit();
}
```

4. **連接資料庫並查詢帳號密碼**
```ruby
$mysqli = new mysqli("localhost","root","121314","phpmyadmin");
$sql_str = "SELECT * FROM player WHERE `player_id`='" . $id . "' AND `player_password`='" . $key . "'";
```

5. **確認查詢結果**
```ruby
$row = $result->fetch_array();
...
```

6. **帳密檢查與錯誤處理**
```ruby
if(!($id==$db_id && $key==$db_key)) {
  header("Location: login.html?message=" . urlencode("登入失敗，請再試一次"));
  exit();
}
```
* 如果資料庫找不到，或密碼錯誤，就導回登入頁。

7. **Redis Token 建立**
```ruby
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$id_cache = $redis->get($db_id);
if(!empty($id_cache)) {
  $redis->del($id_cache);
}

$token=md5($id . time() . floor(rand()*100000+0.5));//建立一個簡單的 token
$redis->set($id, $token);// 用帳號儲存 token
$redis->expire($id, 86400);// 設定過期時間 1 天
$redis->set($token, $id);// 也用 token 對應帳號
$redis->expire($token, 86400);
$redis->quit;
```
* Redis 是用來儲存登入 Token，用戶只要有 token 就算登入成功。

8. **儲存資料進 PHP session**
```ruby
$_SESSION['player_name'] = $db_nick;
$_SESSION['player_id'] = $db_id;
$_SESSION['player_stone'] = $db_stone;
$_SESSION['player_money'] = $db_money;
```
* 這樣之後進入 ``gacha.php`` 時可以直接用 ``$_SESSION`` 讀取使用者資料。

   
### 註冊系統
1. **接收表單**
```ruby
$player_id = $_POST['player_id'] ?? '';
$player_name = $_POST['player_name'] ?? '';
$player_password = $_POST['player_password'] ?? '';
```
* 使用``$_POST``取得從 HTML 表單送來的資料。
* 如果沒有值就預設為空字串（``?? ''``是 null 合併運算子）。
 
2. **驗證是否包含非英數字元**
```ruby
$pattern="/[^a-zA-Z0-9]+/";
if(preg_match($pattern, $player_id) || preg_match($pattern, $player_name)||preg_match($pattern, $player_password)) {
  header("Location: sign_up.html?msg=not allowed input");
  exit();
}
```
* 用正規表達式檢查三個欄位是否包含``非英文字母或數字``。

* 若有違規，將導向 ``sign_up.html`` 並傳送錯誤訊息 ``?msg=not allowed input``。
 
3. **連接資料庫**
```ruby
$host = "localhost";
$dbname = "final_gacha";
$user = "zhouu";
$pass = "ispower";
$pdo = new PDO(...);
```
* 使用 PDO 連線至名為 ``final_gacha`` 的 MySQL 資料庫。
 
4. **寫入新玩家資料**
```ruby
$stmt = $pdo->prepare("INSERT INTO player (player_id, player_name, player_password,gacha_stone,player_money) VALUES (?, ?, ?, 100, 1000)");
$stmt->execute([$player_id, $player_name, $player_password]);
```
* 新玩家的 ``gacha_stone`` 預設為 ``100``，``player_money`` 預設為 ``1000``。
  
  
5. **註冊成功 → 導向 login.html**
```ruby
  header("Location: login.html?msg=Registration Successful");
  exit();
```

6. **錯誤處理（主鍵衝突、名稱重複）**
```ruby
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
```
* 錯誤代碼 23000 表示資料庫限制違反，像是：
  * ``player_id`` 已存在（重複）
  * ``player_name`` 重複（如果有設唯一限制）

* 依據不同情況導向對應錯誤訊息頁面：
  * ``?msg=duplicate_id``
  * ``?msg=duplicate_name``

### 抽卡系統
#### 單抽
1. **登入驗證**
```ruby
session_start();

if (!isset($_SESSION['player_id'])) {
```
* 檢查玩家是否登入。若未登入（``$_SESSION['player_id']`` 沒有設定），就結束程式並回傳錯誤訊息。

2. **資料庫連線**
```ruby
$servername = "localhost";
$username = "root";
$password = "121314";
$dbname = "phpmyadmin";
$conn = new mysqli($servername, $username, $password, $dbname);
```
* 這裡連接到本地 MySQL 資料庫，帳號是 root，密碼為 121314，使用的資料庫為 phpmyadmin。

3. **檢查抽卡石與保底次數**
```ruby
   $sql = "SELECT gacha_stone , gacha_counter FROM player WHERE player_id = ?";
```
* 這裡從 ``player`` 表中取得當前玩家剩下的：

  * ``gacha_stone``：抽卡石數量

  * ``gacha_counter``：距離保底的剩餘次數
    
4. **沒有抽卡石就不能抽**
```ruby
   if ($gacha_stone <= 0) {
```
* 如果抽卡石為 0，則禁止抽卡並回傳錯誤。

5. **抽卡石扣除 1 顆**
```ruby
   $sql = "UPDATE player SET gacha_stone = gacha_stone - 1 WHERE player_id = ?";
```
* 從資料庫中將抽卡石減少 1。
  
6. **抽卡池建構**
```ruby
   $sql = "SELECT role_id, role_name, role_weight, star FROM role";
```
* 從 ``role`` 表中取得所有角色的：

  * ``role_id``：角色編號

  * ``role_name``：角色名稱

  * ``role_weight``：抽中機率權重

  * ``star``：星級（1~5星）

* 使用 ``role_weight`` 建立隨機抽卡的「加權隨機」邏輯。

#### 十抽
```ruby
while(--$while_num){
    ...
}
```
添加``while``進入 10 抽迴圈
> [!NOTE]
> 是從 9 次開始，最後 1 抽可保底。

7. **抽卡邏輯**
```ruby
   $is_guaranteed = ($gacha_counter <= 1);
```
* 如果 gacha_counter <= 1，表示下一抽必出 5 星（保底觸發）。

* 否則執行加權隨機選角。
```ruby
if ($is_guaranteed) {
    // 找出第一個 5 星角色
} else {
    // 用 mt_rand 和 role_weight 做機率抽卡
}
```
8. **更新保底計數器**
```ruby
   if ($selected_star == $max_star) {
    $gacha_counter = 40; // 抽中5星，保底次數重置
} else {
    $gacha_counter--; // 未中5星，保底次數 -1
}
```
9. **更新玩家角色背包**
```ruby
    // 檢查玩家是否已擁有該角色
$sql = "SELECT * FROM player_role WHERE player_id = ? AND role_id = ?";
```
* 如果沒擁有，則新增角色。

* 如果已有，則 ``quantity`` 數量 +1（重複抽到會堆疊）。

10. **更新玩家保底計數**
```ruby
$sql = "UPDATE player SET gacha_counter = ? WHERE player_id = ?";
```
* 將最新的 ``gacha_counter`` 更新回 ``player`` 表中。

11. **回傳抽卡結果**
```ruby
$response = [
    'success' => true,
    ...
];
```
* 會包含以下資訊：

  * 抽到哪一張卡（``selected_name``, ``selected_star``）

  * 抽卡石扣除前後

  * 保底倒數還剩幾抽

  * 是否是保底抽中

  * 抽到的角色是否重複
  
### 商城系統

1. **使用者驗證**
```ruby
session_start();
if (!isset($_SESSION['player_id'])) {
    echo "請先登入！";
    exit;
}
```
* 這段會檢查是否登入，若沒登入，直接提示「請先登入！」並終止程式。

2. **金幣儲值功能**
```ruby
if (isset($_POST['recharge'])) {
    $amount = intval($_POST['money']);
    $sql = "UPDATE player SET player_money = player_money + ? WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $amount, $player_id);
    $stmt->execute();
    $stmt->close();
    $message = "成功儲值 $amount 金幣！";
}
```
* 檢查是否有從表單送出一個名為 `recharge` 的 POST 請求。
* 用戶可以輸入儲值金額（例如：160），然後點擊「儲值」按鈕。
* 該金額會直接加到 ``player`` 表中的 ``player_money`` 欄位。
3. **兌換抽卡石功能**
```ruby
if (isset($_POST['exchange'])) {
    $exchange_cost = 160;
    ...
    if ($money >= $exchange_cost) {
        $sql = "UPDATE player SET player_money = player_money - ?, gacha_stone = gacha_stone + 1 WHERE player_id = ?";
        ...
    }
}
```
* 玩家可以使用 160 金幣兌換 1 顆抽卡石。

* 若金幣不足，會顯示錯誤訊息。
4. **購買商城道具功能**
```ruby
  if (isset($_POST['buy_tool'])) {
    $tool_id = intval($_POST['tool_id']);
    ...
}
```
* 透過 ``<select>`` 下拉式選單選擇道具。
* 系統會：
  * 先查金幣是否足夠；

* 如果足夠：

  * 扣除金幣

  * 將道具加進 ``player_tool`` 表格中，並用 ``ON DUPLICATE KEY UPDATE`` 機制增加數量。
   
    * 如果該玩家已有這個道具，就把數量加 1。否則插入新的一筆資料。

5. **道具價格與列表**
```ruby
$sql = "SELECT player_money, gacha_stone FROM player WHERE player_id = ?";
...
$_SESSION['player_stone'] = $stone;
$_SESSION['player_money'] = $money;

$result = $conn->query("SELECT tool_id, tool_name FROM tool");
...
```
* 將玩家餘額和商城道具讀入頁面。

6. **決定提示補充訊息（t_message）**
```ruby
if (strpos($message, '成功儲值') === 0 || $message === '購買成功！' || ...) {
    $t_message = "目前金幣餘額： ".$money;
} else if (strpos($message, '兌換成功') === 0) {
    $t_message = "目前金幣餘額： ".$money.", 目前抽卡石餘額: ".$stone;
}
```
* 根據操作訊息內容產出「補充提示內容」。

7. **SweetAlert 前端提示（成功／錯誤）**
```ruby
Swal.fire({
    icon: 'success',
    title: message,
    text: t_message,
    ...
});
```
* 若是儲值 / 購買 / 兌換成功，就顯示綠色提示框。

* 若金幣不足，就顯示紅色錯誤提示。
  
8. **UI 分頁切換功能**
```ruby
function showTab(tabId) {
    ...
    document.getElementById(tabId).classList.add('active');
}
```
* JS 控制兩個分頁：「儲值 / 兌換」 和 「道具購買」的切換顯示。

9. **前端介面說明**
* 使用 SweetAlert2 美化彈窗。

* 背景圖使用商城主題圖。

* 兩個主要 tab：

  * 💰 儲值 / 兌換

  * 📦 購買道具（從 DB 撈道具列表）

* 選單與按鈕都設計了 `hover、active` 效果。
### 背包系統
#### 顯示玩家背包角色資料
1. **Session 驗證無誤**
```ruby
if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => '尚未登入'], JSON_UNESCAPED_UNICODE );
    exit;
}
```
* 如果沒有登入，就回傳錯誤訊息並結束。
2. **資料庫連線正確處理**
```ruby
$conn = new mysqli(...);
if ($conn->connect_error) {
    die(json_encode(...));
}
```
* 如果資料庫連不上，也正確輸出錯誤訊息並中止。
3. **查詢玩家背包角色**
```ruby
$sql = "SELECT role_id, quantity FROM player_role WHERE player_id = ? AND owned = TRUE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_id);
$stmt->execute();
$result_bag = $stmt->get_result();
```
* 用 `prepared statement` 保護資料。

* 如果查不到任何角色，回傳「背包為空」。
4. **查詢角色資訊（`role_name`, `star`）**
```ruby
$sql = "SELECT role_id, role_name, star FROM role";
```
* 取出角色名稱與星級並以`role_id`為索引存入陣列。
5. **合併資料成角色清單**
```ruby
while ($row = $result_bag->fetch_assoc()) {
    $role_id = $row['role_id'];
    $roles[] = [
      'role_id' => $role_id,
      'role_name' => $roles_menu[$role_id]['role_name'],
      'quantity' => $row['quantity'],
      'star' => $roles_menu[$role_id]['star']
    ];
}
```
* 把「玩家擁有的角色」與「角色總表資訊」合併輸出。
#### 查詢目前玩家的道具背包
```ruby
if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => '尚未登入'], JSON_UNESCAPED_UNICODE );
    exit;
}
```
* 確認是否已登入（是否有 player_id）。
* 若未登入，回傳 JSON 錯誤訊息並結束執行。
```ruby
$player_id = $_SESSION['player_id'];
```
* 取得登入玩家的 ID。
```ruby
$servername = "localhost";
$username = "root";
$password = "121314";
$dbname = "phpmyadmin";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗'], JSON_UNESCAPED_UNICODE ));
    exit;
}
```
* 建立資料庫連線，如果失敗則回傳錯誤 JSON 並停止執行。
```ruby
$sql = "SELECT tool_id, quantity FROM player_tool WHERE player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $player_id);
$stmt->execute();
$result_bag = $stmt->get_result();
$stmt->close();
```
* 查詢玩家目前擁有的道具（道具 ID 和數量）。使用 `prepare` 是為了防止 SQL injection。
```ruby
if ($result_bag->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => '背包為空'], JSON_UNESCAPED_UNICODE );
    exit;
}
```
* 如果查不到任何資料，代表背包是空的，回傳錯誤訊息。
```ruby
$sql = "SELECT tool_id, tool_name FROM tool";
$result_menu = $conn->query($sql);
$tools_menu = [];
while ($row = $result_menu->fetch_assoc()) {
    $tools_menu[$row['tool_id']] = $row['tool_name'];
}
```
* 查詢所有道具的名稱，建立對應表：`tool_id ➝ tool_name`。
```ruby
$tools = [];
while ($row = $result_bag->fetch_assoc()) {
    $tools[] = [
        'tool_id' => $row['tool_id'],
        'tool_name' => $tools_menu[$row['tool_id']],
        'quantity' => $row['quantity']
    ];
}
```
* 整合每個道具的名稱和數量，組合成一個資料陣列 `$tools`。
```ruby
echo json_encode([
    'success' => true,
    'player_id' => $player_id,
    'tools' => $tools
], JSON_UNESCAPED_UNICODE );
```
* 將資料輸出為 JSON 格式（用於除錯或前端 AJAX 測試）。

  
### 抽卡圖鑑
```ruby
<?php
session_start();

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => '尚未登入'], JSON_UNESCAPED_UNICODE ); // 尚未登入
    exit;
}

$player_id = $_SESSION['player_id'];

// 資料庫連線設定
$servername = "localhost";
$username = "zhouu";  // 請依環境修改
$password = "ispower";
$dbname = "final_gacha";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗'], JSON_UNESCAPED_UNICODE )); // 如果連線失敗，就輸出 JSON 提示錯誤。
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

$_SESSION['role_all']=$roles_menu; // 將所有角色資料存到 Session，供後續頁面使用（如 gacha.php）。
echo json_encode([ // 輸出查詢結果的 JSON 格式，方便前端 Ajax 使用。
    'success' => true,
    'player_id' => $player_id,
    'roles' => $roles_menu
], JSON_UNESCAPED_UNICODE );
header("Location: gacha.php?msg=all_gacha_load");
```
* 驗證玩家是否登入。

* 從資料庫撈取所有角色資訊並儲存到 `$_SESSION['role_all']`。

* 回傳 JSON 格式的角色資料。
  
