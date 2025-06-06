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
	    <form action="one-gacha.php" method="post">
        <button class="gacha-button" type="submit">one gacha</button>
      </form>
	    <br><br>
      <form action="ten-gacha.php" method="post">
        <button class="gacha-button" type="submit">ten gacha</button>
      </form>
	    <br><br>
      <form action="role_backpack.php" method="post">
	      <button class="bag-button" type="submit">ROLE BACKPACK</button>
      </form>
      <br><br>
      <form action="tool_backpack.php" method="post">
	      <button class="bag-button" type="submit">TOOL BACKPACK</button>
      </form>
      <br><br>
      <button class="store-button" >STORE</button>

  </body>
</html>
