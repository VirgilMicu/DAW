<?php 
session_start();
 require('../conexiune.php');
 require('fragmente/functii.php'); 

if ( tip_utilizator()!=1){
	header('Location: index.php');
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset ($_POST['id_rezervare'])) {
		$id_rezervare = htmlspecialchars($_POST['id_rezervare']);
		$stmt = $db->prepare ("SELECT * FROM rezervari_lista_locuri WHERE id_rezervare =? AND id_user =? ;");
		$stmt->bind_param("ii", $id_rezervare, $_SESSION['id_user']);
		$stmt->execute();
		$rezultat = $stmt->get_result();
		if ($rezultat->num_rows >0) {
			$row = $rezultat->fetch_assoc();
			 require('fragmente/bilet_pdf.php'); 
		}
	}
}

log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
require('fragmente/head.php'); 
require('fragmente/nav.php');
 ?>
 
<main> 
<link rel="stylesheet" href="/css/rezervari.css" type="text/css"/>
<div id="pag-rez">


<div id="lista-rez" class="form_centrat">
<p>Rezervarile mele la <span style="font-family: 'Limelight', sans-serif;">Teatrul de opera</span></p>

<div id="rezervari">


<form class="inp-prod" id="print-pdf" action="rezervari.php" name="print-pdf" method="post" type="hidden" target="_blank" >
 <input type="hidden" id="id_rezervare" name="id_rezervare" value="hhh">
</form>

<?php 
//foreach ($_SESSION as $x => $y) { echo $x.": ". $y ." <br>"; }
	$stmt = $db->prepare ("SELECT * FROM rezervari_lista_locuri WHERE id_user = ?;");
	$stmt->bind_param ('i', $_SESSION['id_user']);
	$stmt->execute();
	$rezultat = $stmt->get_result() ;
	if ($rezultat->num_rows > 0) {
		while ($row = $rezultat->fetch_assoc()) { 
			$lista_locuri = str_replace(',',', ',$row['lista_locuri']);?>
		<div class ="next-item">
			<div class ="next-titlu"><span><?php echo $row['titlu'] ?></span> </div> 
			<div class ="next-data"><span><small><?php $d= explode('-',$row['data_spectacol']); echo data_romaneasca ($d[2], $d[1], $d[0]);?></small></span> </div> 
			<div class ="status"><span><b>Locuri: </b><?php echo $lista_locuri; ?></span> </div>
			<div class ="next-rez"><span><small>Rezervat la: <?php $d= strtotime($row['data_adaugare']); echo date('d',$d).'-'.date ('m',$d).'-'.date ('Y',$d).' '.date('H:i:s', $d);?></small></span> </div> 
			<a href=""  onclick="<?php echo 'document.getElementById(\'id_rezervare\').value='.$row['id_rezervare'].'; document.getElementById(\'print-pdf\').submit(); return false; "'		
			
			?>> <div class ="next-print"> <span class="fa-solid fa-file-pdf"></span> </div> </a>
		</div>
<?php 
		}
	}

?>
</div>
<div id="altceva">

</div>
</div>
</div>
</main> 
<div/>
        <?php require('fragmente/footer.php'); ?>
</body>
</html>


