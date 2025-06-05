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
	    <form action="drawgacha.php" method="post">
        <button class="gacha-button" type="submit">GO</button>
      </form>
	    <br><br>
	    <button class="bag-button" >BAG</button>
	    <br><br>
            <button class="store-button" >STORE</button>

  </body>
</html>
