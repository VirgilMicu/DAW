<?php 
session_start();
//foreach ($_SERVER as $x => $y) //{ echo $x.": ". $y ." <br>"; }
 require('../conexiune.php');
 require('fragmente/functii.php'); 

 
 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
  require('fragmente/head.php');
 require('fragmente/nav.php'); 
 ?>
 
<main> 
<link rel="stylesheet" href="css/homepage.css" type="text/css"/>
<div id="despre">
<h3>Despre</h3>
<p>Siteul unui teatru de opera imaginar.</
</div>
</main> 
<div/>
        <?php require('fragmente/footer.php'); ?>
</body>
</html>


