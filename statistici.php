<?php 
session_start();
 require('../conexiune.php');
 require('fragmente/functii.php'); 

if ( tip_utilizator()!=9){
	header('Location: index.php');
	exit;
}

$statistici = [
'Pagini pe zile si IP-uri - numar de vizualizari' => 'statistici_pag_ip_zile',
'Pagini pe zile - numar de vizualizari' => 'statistici_pag_zile',
'Logari utilizatori'=>'statistici_logari_useri',
'Nr. vizitatori unici / zi (dupa IP)'=>'vizitatori_unici_zi'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset ($_GET['stat'])) {
		$nr = htmlspecialchars($_GET['stat']);
		$index = array_keys($statistici);
		if (array_key_exists($nr, $index) ) {
			$_SESSION['statistica'] = $statistici[$index[$nr]];
		}
	}
}

$raport='';
if (isset($_SESSION['statistica'])) {
	$raport=$_SESSION['statistica'] ;
} else { $raport= $statistici[array_keys($statistici)[0]];}


if ($_SERVER['REQUEST_METHOD'] === 'POST') { //-----------------------------export in csv
	if (isset ($_POST['csv'])) {
		
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=csv_export.csv');
		ob_end_clean();
		$output = fopen( 'php://output', 'w' );
		
		$stmt = $db->prepare ('SELECT * FROM '.$raport.';');
		$stmt->execute();	
		$rezultat = $stmt->get_result();
		$cap_tabel = "";
		while  ( $row = $rezultat->fetch_assoc() ) {
			if ($cap_tabel == "" ) { 
				$cap_tabel = array_keys($row); 
				fputcsv( $output, $cap_tabel );}
			fputcsv( $output, array_values($row));
		}
		fclose( $output );
		exit; 
	}
}
	
log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
require('fragmente/head.php'); 
require('fragmente/nav.php');
?>
<main> 
<link rel="stylesheet" href="/css/statistici.css" type="text/css"/>
<div id="pag-spec">
<div id="lista-spec" class="form_centrat">
<div id="lista"> 

<?php
$stmt = $db->prepare ('SELECT * FROM '.$raport.';');
$stmt->execute();	
$rezultat = $stmt->get_result();?> 

<div class="tableFixHead">
<table><thead> <tr>
<?php
while ( $col = $rezultat->fetch_field() ) {  //----------------------------------------- capul de tabel 
	echo '<th>'.$col->name.'</th>';
}
?> </tr></thead> <tbody> <?php		
while ( $row = $rezultat->fetch_row() ) {  //----------------------------------------------valori in tabel
?> <tr> <?php
	for ($i = 0 ; $i < $rezultat->field_count ; $i++ ) {
			echo '<td>'.$row[$i].'</td>'; 
	}
?> </tr> <?php
}
?>  </tbody> </table></div> <?php



	?>
	</div>
	</div>
	<div class="form_centrat">

		<p style="text-align:center" ><b>Rapoarte</b></p> 
		<form class="inp-prod" id="statistici" action="statistici.php" method="get" >
		<label for="stat"></label>
		<select name="stat" class="inp-prod" size="20" onchange=" this.form.submit();" >		
		<?php
		$index = array_keys($statistici);  // ---------------------------------------------lista de rapoarte
		for ($i = 0 ; $i < sizeof($index) ; $i++ ) {
			$tx = '';
			if (isset ($_GET['stat'])) { if ($_GET['stat'] == $i) { $tx = "selected='selected'";}}
			echo "<option value='".$i."' ".$tx.">".$index[$i]."</option>";
		}
			 ?>
		</select>
		</form>
		<br> <br> 
		
		<a href="" class="link-evident" onclick=" document.getElementById('exp-csv').submit(); return false; "> <div >Exporta in .csv</div> </a>
		
		<form  id="exp-csv" action="statistici.php" name="exp-csv" method="post" type="hidden"  >
		 <input type="hidden" id="csv" name="csv" value="csv">
		</form>
		
		<div class="form_centrat" style="color: red">  <p></p> </div> 			
	</div>
</div>


</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>
