<?php
  session_start();
  $user = $_SESSION['player_name'];
?>
<!DOCTYPE html>
<html>
  <head>
  <style>
  </style>
  </head>
  <body>
	    <h1>welcome player <?= htmlspecialchars($user); ?></h1>
	    <h1>GACHA</h1>
	    <button class="gacha-button" >GO</button>
	    <br><br>
	    <button class="bag-button" >BAG</button>
	    <br><br>
            <button class="store-button" >STORE</button>

  </body>
</html>
