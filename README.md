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

> [!NOTE]
> 抽卡石🪨是用來抽卡的，金幣🪙則是到商城兌換成抽卡石<br>

* 點擊`角色背包`即可查看抽到的角色<br>
![](https://i.meee.com.tw/iopvaXX.png)<br>

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
### 商城系統
