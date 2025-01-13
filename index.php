<?php 
session_start();
 //foreach ($_SERVER as $x => $y)//{ echo $x.": ". $y ." <br>"; }
 require('../conexiune.php');
 require('fragmente/functii.php'); 

 require('fragmente/meteo.php'); 
 
 
 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
  require('fragmente/head.php');
 require('fragmente/nav.php'); 

//echo date('Y-m-d H:i:s', time());
 ?>

<main> 
<link rel="stylesheet" href="css/homepage.css" type="text/css"/>
<div id="grid-pagina">
<div id="urmaroare">
<h2 style="font-weight: normal">Ce urmeaza la <span style="font-family: 'Limelight', sans-serif;">Teatrul de opera</span></h2>
<?php 
	$stmt = $db->prepare ("SELECT * from spectacole_locuri WHERE CONCAT(data_spectacol,' ','19:00:00') > NOW() LIMIT 3;"); //CONVERT_TZ(NOW(),'SYSTEM','Europe/Bucharest')
	$stmt->execute();
	$rezultat = $stmt->get_result() ;
	if ($rezultat->num_rows > 0) {
		while ($row = $rezultat->fetch_assoc()) { ?>
		<div class ="next-item">
		<div class ="next-st">
			<p>
			<div class ="next-titlu"><span><?php echo $row['titlu'] ?></span> </div> 
			<div class ="next-data"><span><small><?php $d= explode('-',$row['data_spectacol']); echo data_romaneasca ($d[2], $d[1], $d[0])?></small></span> </div> 
			</p>
			<div class ="status"><p> 
			<?php 
			echo (($row['nr_locuri_vandute']< 100) ? '<a class="link-ad" href="rezervare_locuri.php?id='.$row['id_spectacol'].'">Rezerva locuri</a>' :'<span style="color:red;">Sold out</span>')
		    ?></p> </div> 
		</div>
		<div class ="next-cen"><p><?php echo $row['descriere_scurta'] ?> </p></div> 
		<div class ="next-dr"> <p><?php echo $row['descriere'] ?> </p></div> 
		</div>
<?php 
		}
	}

?>
</div>
<div id="meteo">
<p><div id="titlu-meteo">Mergem la opera. Ne luam ceva mai gros pe sub costum / rochie? Depinde! <span style="font-family: 'Limelight', sans-serif;">Teatrul de opera</span> va prezinta vremea 
<?php echo ($locatie_preferata == ''? ' intr-o locatie aleatorie din tara.' : ' in locatia dvs. preferata, <b>'.$locatie_preferata.'</b>.'); ?></div></p>

<form class="inp-prod" action="index.php" method="get" id="selectare_locatie" >
		<label for="locatie"><?php echo ($locatie_preferata == ''? 'Sau puteti sa va alegeti locatia preferata:' : 'Dar mai puteti vedea si...'); ?></label> <br> 
		<select name="locatie" class="inp-prod"  onchange="document.getElementById('selectare_locatie').submit()" value ="">
		<option hidden disabled selected value></option>
		<?php
			foreach ($vect_asoc AS $loc=>$html) {
				echo "<option value='".$loc."'>".$loc."</option>\n";
			} ?>
		</select>
</form>

<?php
if ($locatie_preferata == '') {
	$i = rand(0, sizeof($vect));
	echo $vect[$i];
} else {
	echo $vect_asoc [$locatie_preferata];
}
?>
</div>
</div>
</main> 
<div/>
        <?php require('fragmente/footer.php'); ?>
</body>
</html>


