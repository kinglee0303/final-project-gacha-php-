<?php
  session_start();
  $user = $_SESSION['player_name'];
  $id = $_SESSION['player_id'];
  $stone = $_SESSION['player_stone'];
  $money = $_SESSION['player_money'];

?>
<!DOCTYPE html>
<html>
  <head>
  <title>轉蛋頁面</title>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
	 body {
	    height: 100%;
	     min-height: 100vh;
  	    margin: 20px;
  	    padding: 0;
	    background-image: url('gacha_background.jpg');
	    background-size: cover;         /* 背景圖片填滿畫面 */
	    background-position: center;    /* 圖片置中 */
	    background-repeat: no-repeat;   /* 不重複鋪排 */
	  }
	  .box { 
	    min-height: 60vh;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.40); /* 白色 + 50% 不透明度 */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
	  }
	.center-wrapper {
	  margin-top: 5vh;
	  margin-left: 0vh;
	  display: flex;
	  justify-content: center;   /* 水平置中 */
	  align-items: center;       /* 垂直置中 */
	  height: 70vh;             /* 整個視窗高度 */
	  width: 80vh;
	}
	img {
	    display: block;
	    margin: 0 auto;
	    max-width: 40%;
	    max-height: 600px;
	  }
	.player {
	    color: rgba(255, 255, 255);
	  }
                /* 設定提交按鈕樣式 */
          button {
            width: 80%;
            padding: 12px;
            background-color: #060f1c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 2.2em;
            transition: background-color 0.5s ease;
          }

          /* 提交按鈕懸停效果 */
          button: hover {
            background-color: #116fb5;
          }
	 /* 按下去的效果（點擊時） */
	 button:active {
  	    transform: scale(0.98);
	    background-color: #0d5e94;
	 }
    	  .gacha-button {
		border-radius: 15px;

	 }

	  
	  .button-row {
	   width: 100%;
	    box-sizing: border-box;
	    display: flex;
	    justify-content: center;     /* 水平置中 */
	    align-items: center;         /* 垂直置中（如果有高度） */
	    gap: 1px;
	    margin: 20px auto;           /* 自動置中 + 上下間距 */
	    width: fit-content;          /* 根據內容自動寬度，也可用固定值 */
	  }

	  .bag-button, .store-button {
            box-sizing: border-box;
	    width: 300px;
	    height: 70px; 
	    margin-left: 10px;
            padding: 1px 1px;
            font-size: 2.0em;
            border: none;
            border-radius: 15px;
            background-color: #060f1c;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
          }
        

	
	
	.gacha-button:hover {
	  background-color: #f8150d;
	}
	.bag-button:hover, .store-button:hover {
          background-color: #550bf8;
        }   	
	@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap');

	.cyber-title {
	  font-size: 4.0em;	 
	  text-align: center;
	  font-family: 'Orbitron', sans-serif;
	  color: #4b0082; /* 主紫色 */
	  text-shadow:
	    0 0 3px #d3e5fb,
	    0 0 6px #0066d1,
	    0 0 9px #107aea;
	  letter-spacing: 2px;
	  margin-bottom: 2px;
  	  color: transparent;
  	  -webkit-text-stroke: 1px #7e22ce;

	}
	 .my-confirm-button {
	    width: 150px;
	    font-size: 20px;   /* 字體大小 */
	    padding: 12px 20px; /* 上下、左右間距 */
	    border-radius: 8px; /* 圓角 */
	    text-align: center;
	 }
   	 .stone, .money, .logout {
	   color: rgba(255, 255, 255); 
	 }
 	 .logout {
	    font-size: 25px;
	    font-weight: bold;
	 }
	 .top-bar {
	  display: flex;
	  gap: 20px; /* 這裡設定兩者間的間距 */
	  align-items: center;
	  margin-bottom: 0px;
	  height: 50px;
	}
         .sec-bar {
	  margin: 0px;
	  margin-bottom: 0px;
 	  font-size:  15px;
	}
	 .inf {
	   display: flex;  
	   gap: 400px;
	 }
	  .gacha-container form {
	    margin: 0;
	  }
	.swal-popup {
          font-size: 18px; /* 整體字體大小 */
	  width: auto !important;
          max-width: none;

        }

        .swal-title {
          font-size: 33px; /* 標題字體大小 */
        }

        .swal-text {
          font-size: 25px; /* 這就是 text 的字體大小 */
        }
        .swal-auto-size {
	  width: 40vw !important;        /* 寬度為視窗寬度的 90% */
	  max-width: none !important;
	  padding: 20px;
	}
  </style>
  </head>
  <body>
	   
	    <div class="top-bar">
	    	<h1 class="player">Welcome Player: <?= htmlspecialchars($user); ?>&nbsp;</h1>
	    	<a href="login.html" class="logout">登出</a>
	    </div>
	    <div class="inf">
	    	<div class="sec-bar">
	       		<h1 class="stone">&nbsp;&nbsp;&nbsp;&nbsp;🪨 抽卡石：<?= htmlspecialchars($stone); ?></h1>
	       		<h1 class="money">&nbsp;&nbsp;&nbsp;&nbsp;💰 金幣：<?= htmlspecialchars($money); ?></h1>
			<br>
			<form action="role_backpack.php" method="post">
                                <button class="bag-button" type="submit"> 角色背包 </button>
                        </form>
                        <br><br>
                        <form action="tool_backpack.php" method="post">
                                <button class="bag-button" type="submit"> 道具背包 </button>
                         </form>
                        <br><br>
                        <form action="store.php" method="post">
                                <button class="store-button" type="submit">商城</button>
                         </form>

	    	</div>
	 	<div  class="center-wrapper">
	   	     <div class="box">
	    		<br>
	    		<div class="title-row">
	    			<h1 class="cyber-title" >抽 卡</h1>
	    		</div><br><br>
	    		<img src="box_gacha.png" alt="gacha">
	    		<br><br><br>
			<div class="gacha-container">
			        <form id="one-gacha-form" action="one-gacha.php" method="post">
                                        <button class="gacha-button" type="submit">one gacha</button>
                                </form>
				<br><br>
	            		<form id="ten-gacha-form" action="ten-gacha.php" method="post">
	             		 	<button class="gacha-button" type="submit">ten gacha</button>
	            		</form>
				<br><br>
				<form id="gacha_all" action="gacha_all.php" method="post">
                                        <button class="gacha-button" type="submit">抽卡圖鑑</button>
                                </form>
                                <br><br>
			</div>
		   </div>
		</div>
	   </div>
<script>
         window.onload = function () {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => input.value = '');
          };
         const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.get('msg') === 'no stone') {
              Swal.fire({
               icon: 'error',
               title: '抽卡失敗,抽卡石不足！',
               text: '目前抽卡石餘額：<?= htmlspecialchars($stone); ?> , 請去商城兌換足夠的抽卡石。',
               confirmButtonText: '好',
                  customClass: {
                            popup: 'swal-popup',
                            title: 'swal-title',
                            htmlContainer: 'swal-text'
                          }
              });
                const newURL = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, newURL);
            }         const urlParams2 = new URLSearchParams(window.location.search);
            if (urlParams2.get('msg') === 'role_null') {
              Swal.fire({
               icon: 'error',
               title: '目前角色背包為空！',
               text: '請抽卡',
               confirmButtonText: '好',
                  customClass: {
                            popup: 'swal-popup',
                            title: 'swal-title',
                            htmlContainer: 'swal-text'
                          }
              });
                const newURL2 = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, newURL2);
            }
         const urlParams3 = new URLSearchParams(window.location.search);
            if (urlParams3.get('msg') === 'tool_null') {
              Swal.fire({
               icon: 'error',
               title: '目前道具背包為空！',
               text: '請去商城購買',
               confirmButtonText: '好',
                  customClass: {
                            popup: 'swal-popup',
                            title: 'swal-title',
                            htmlContainer: 'swal-text'
                          }
              });
                const newURL3 = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, newURL3);
            }

	

</script>
<?php if (isset($_SESSION['gacha_result'])): ?>
    <script>
        const result = <?php echo json_encode($_SESSION['gacha_result'], JSON_UNESCAPED_UNICODE); ?>;
	const gacha_t = <?php echo json_encode($_SESSION['gacha_t'], JSON_UNESCAPED_UNICODE); ?>;
	const message_f = result.message_result;
	const match = '恭喜抽中以下卡片,'+message_f.match(/目前抽卡石剩餘\s*\d+/);
	if (match) {
	  console.log(match[0]); // ➜ 例如 "目前抽卡石剩餘 80"
	} else {
	  console.log("沒找到句子");
	}
	let message = '';
	if(gacha_t==10){
	        //let message = `<p>${result.message_result}</p>`;
	        message = `<p>${match}</p>`;
		message += `<p>${result.message_counter}</p>`;
	        //message += `<p>${result.message_own}</p>`;
		// 統計卡片名稱與出現次數
		const cardCountMap = {};

		for (let i = 0; i < result.selected_name.length; i++) {
		    const name = result.selected_name[i];
		    const star = result.selected_star[i];
		    const key = `${name}-${star}`;
		    
		    if (!cardCountMap[key]) {
		        cardCountMap[key] = { name, star, count: 1 };
		    } else {
		        cardCountMap[key].count++;
		    }
		}
	        //message += `<ul>`;
	        for (const key in cardCountMap) {
		    const card = cardCountMap[key];
		    message += `<p>${card.name} - ${card.star}⭐ * ${card.count}</p>`;
		}
	        //message += `</ul>`;
	}
	else if (gacha_t==1)
	{
		message = `<p>${match}</p>`;
	 	message += `<p>${result.message_counter}</p>`;
	        //message += `<p>${result.message_own}</p>`;
		message += `<p>${result.selected_name} - ${result.selected_star}⭐ * 1 </p>`;

	}
        Swal.fire({
            icon: 'success',
            title: '🎉 抽卡結果！',
            html: message,
            confirmButtonText: '確認',
            width: '600px',
	    customClass: {
	    	confirmButton: 'my-confirm-button',
		popup: 'swal-popup',
                title: 'swal-title',
                htmlContainer: 'swal-text'
	    }
        }).then(() => {
	    location.reload(); // 按下確認後刷新頁面
	});
        // 清除 URL 的殘留參數（避免重複觸發）
        const newURL = window.location.origin + window.location.pathname;
        window.history.replaceState({}, document.title, newURL);
    </script>
    <?php unset($_SESSION['gacha_result']); ?>
    <?php unset($_SESSION['gacha_t']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['player_role'])): ?>
    <script>
	const roles = <?php echo json_encode($_SESSION['player_role'], JSON_UNESCAPED_UNICODE); ?>;
	let titleHTML = `<h1 style="border-collapse: collapse; width: 100%; text-align: center;">我的角色背包</h1>`
	let tableHTML = `<table border="1" style="font-size: 25px;border-collapse: collapse; width: 100%; text-align: center;">
	  <thead>
	    <tr>
	      <th>角色ID</th>
	      <th>角色名稱</th>
	      <th>星級</th>
	      <th>數量</th>
	    </tr>
	  </thead>
	  <tbody>`;
	roles.forEach(role => {
	  tableHTML += `
	    <tr>
	      <td>${role.role_id}</td>
	      <td>${role.role_name}</td>
	      <td>${role.star}</td>
	      <td>${role.quantity}</td>
	    </tr>`;
	});
	tableHTML += `</tbody></table>`;
	Swal.fire({ html: titleHTML+tableHTML })
    </script>
    <?php unset($_SESSION['player_role']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['player_tool'])): ?>
    <script>
        const tools = <?php echo json_encode($_SESSION['player_tool'], JSON_UNESCAPED_UNICODE); ?>;
	let titleHTML = `<h1 style="border-collapse: collapse; width: 100%; text-align: center;">我的道具背包</h1>`
        let tableHTML = `<table border="1" style="font-size: 25px;border-collapse: collapse; width: 100%; text-align: center;">
          <thead>
            <tr>
              <th>道具ID</th>
              <th>道具名稱</th>
              <th>數量</th>
            </tr>
          </thead>
          <tbody>`;
        tools.forEach(tool => {
          tableHTML += `
            <tr>
              <td>${tool.tool_id}</td>
              <td>${tool.tool_name}</td>
              <td>${tool.quantity}</td>
            </tr>`;
        });
        tableHTML += `</tbody></table>`;
        Swal.fire({ html: titleHTML+tableHTML })
    </script>
    <?php unset($_SESSION['player_tool']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['role_all'])): ?>
    <script>
        const roles = <?php echo json_encode($_SESSION['role_all'], JSON_UNESCAPED_UNICODE); ?>;
        let titleHTML = `<h1 style="border-collapse: collapse; width: 100%; text-align: center;">抽卡圖鑑</h1>`
        let tableHTML = `<table border="1" style="font-size: 25px; border-collapse: collapse; width: 100%; text-align: center;">
          <thead>
            <tr>
              <th> 角色ID </th>
              <th> 角色名稱 </th>
	      <th> 星級 </th>
              <th>抽中機率</th>
            </tr>
          </thead>
          <tbody>`;
        roles.forEach(role => {
	let percent = ((role.role_weight / 152) * 100).toFixed(2);

          tableHTML += `
            <tr>
              <td> ${role.role_id} </td>
              <td> ${role.role_name} </td>
              <td> ${role.star} </td>
	      <td>${role.role_weight}/152（約 ${percent}%）</td>
            </tr>`;
        });
        tableHTML += `</tbody></table>`;
        Swal.fire({ html: titleHTML+tableHTML,
	  customClass: {
	    popup: 'swal-auto-size'
	  } })
    </script>
     <?php unset($_SESSION['role_all']); ?>
<?php endif; ?>



  </body>
</html>
