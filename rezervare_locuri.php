<?php 
session_start();
 require('../conexiune.php');
 require('../sneaky.php');
 require('fragmente/functii.php'); 

 
$mesaj = '';
//---------------------------------------------------  un id sau cu cerere de login (din aceeasi pagina)
 if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset ($_GET['id'])) {
		$id = htmlspecialchars (trim($_GET['id']));
		$id = ltrim ( $id, '0');
	if (preg_match('/[^0-9]/', $id) == 0) {
	if (tab_camp_val ($db, 'spectacole', 'id_spectacol', $id, 'i')){
	$_SESSION['id_spectacol'] = $id;}
	//echo "id  salvat in sesiune: ".$_SESSION['id_spectacol'];
		}
	} elseif (isset ($_GET['login'])) {
		$_SESSION['redirect_after_login']= $_SERVER['PHP_SELF'].'?id='.$_SESSION['id_spectacol'] ;
		header('Location: login.php');
		exit;
	}
}

if (!$_SESSION['id_spectacol']) {
	header('Location: calendar_spectacole.php');
	exit;
} else { // ---------- vizitatorii sau userii obisnuiti nu au access la situatia locurilor pentru spectacole la care e prea tarziu pentru a mai face rezervari sau sint SOLD OUT
	$stmt = $db->prepare ("SELECT * FROM spectacole_locuri WHERE id_spectacol= ?;");
	$stmt->bind_param("i", $_SESSION['id_spectacol']);
	$stmt->execute();
	$rezultat = $stmt->get_result() ;
	$row = $rezultat->fetch_assoc();
	$data_spectacol = $row['data_spectacol'];
	$locuri_vandute = $row['nr_locuri_vandute'];
	if ($locuri_vandute == 100 || data_ref_prezent($data_spectacol, '<') || 
		(data_ref_prezent($data_spectacol, '==') && strtotime(date('H:i:s')) > strtotime(date('19:00:00'))) ) { // daca este SOLD OUT sau este prea tarziu pentru a face rezervari  
		if (tip_utilizator() != 9 ) { // ----------------------- daca nu avem un user logat de tip admin 
			header('Location: calendar_spectacole.php');
			exit;
		}
	}
}

  if ($_SERVER['REQUEST_METHOD'] === 'POST') { //----------------------------------salvare in baza a rezervarii si locurilor aferente
	if(isset ($_POST['rezervare']) & tip_utilizator()==1) {
		$locuri =($_POST);
		array_pop ($locuri);
		$string_locuri='INSERT INTO rezervari_locuri (id_rezervare, id_loc, id_spectacol) VALUES';
		$id_rezervare=0;
		$nr_locuri_alese =0;
		for ($i=1; $i <= 100; $i++){
			if (isset ($locuri ['lc'.$i])) {
				if ($locuri ['lc'.$i]==$i){
					if ($id_rezervare == 0) {$id_rezervare= ($_SESSION['id_spectacol'] *100 + $i - 1) * 1000 + rand(0,999);} // id rezervare = [id_spectacol].[2 cifre = nr primului loc rezervat -1].[3 cifre aleatorii]
					$string_locuri .= ' ('.$id_rezervare.', '.$i.', '.$_SESSION['id_spectacol'].'),';
					$nr_locuri_alese ++;
				}
			}
		}
			if ($nr_locuri_alese > 0) {
			$string_locuri = rtrim( $string_locuri,',');
			$string_locuri .= ';';
			$cod_rezervare= rand(1000,9999);
			try {
				//$string_rezervare= 'INSERT INTO rezervari (id_rezervare, id_user, id_spectacol, cod_control) VALUES ('.$id_rezervare.', '.$_SESSION['id_user'].', '.$_SESSION['id_spectacol'].');';
				$stmt1 = $db->prepare('INSERT INTO rezervari (id_rezervare, id_user, id_spectacol, cod_control) VALUES (?, ?, ?, ?);');
				$stmt1->bind_param('iiii', $id_rezervare, $_SESSION['id_user'], $_SESSION['id_spectacol'], $cod_rezervare);
				$stmt2 = $db->prepare($string_locuri);
				$db->query("SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE");
				$db->begin_transaction();
				$stmt1->execute();
				$stmt2->execute();	
				$db->commit();
				$_SESSION['mesaj_rezervare']= "1";
			} catch (mysqli_sql_exception $exception) {
				$db->rollback();
				//throw $exception;
				$_SESSION['mesaj_rezervare']= "0";//.$exception->getMessage();		
			}
			 require('fragmente/mesaj_rezervare.php'); 
			} else { $mesaj = 'Nu ati selectat niciun loc.';} 
	} else { $mesaj = 'Momentan nu puteti rezerva locuri.';}
  }
 
 
  $spectacol=[];
 $locuri_rezervate = [];
 if (isset ($_SESSION['id_spectacol'])){
	$id = $_SESSION['id_spectacol'];
	$stmt = $db->prepare ("SELECT * FROM spectacole_rezervari WHERE id_spectacol= ? ORDER BY id_loc DESC;");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$rezultat = $stmt->get_result() ;
	if ($rezultat->num_rows > 0) {
		while ($row = $rezultat->fetch_assoc()) {
		$locuri_rezervate [] = array ('id_loc'=>$row['id_loc'], 'id_user'=>$row['id_user']);
		}
	}
	$stmt = $db->prepare ("SELECT * FROM spectacole_locuri WHERE id_spectacol= ?;");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$rezultat = $stmt->get_result() ;
	$spectacol = $rezultat->fetch_assoc ();
 }
 
log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); 
 
 ?>
 
<main> 
<link rel="stylesheet" href="/css/rezervari-locuri.css" type="text/css"/>

<div id="pag-rezl">
<div id="descriere">
<div id="descr-sus">
<h2><?php echo $spectacol['titlu']; ?></h2>
<p><b><?php $d= strtotime($spectacol['data_spectacol']); echo data_romaneasca ( date('d', $d), date ('m', $d), date('Y', $d)); ?></b></p>
<p><?php echo $spectacol['descriere_scurta']; ?></p>
<p><?php echo $spectacol['descriere']; ?></p>
</div>
<div id="descr-jos">
<p><small> Spectacolele incep la ora 20:00. <br> <br> Rezervari online se pot face pana la ora 19:00. Pentru a putea rezerva locuri online trebuie sa aveti un cont la <span style= "font-family: 'Limelight', sans-serif;">Teatrul de opera</span>.</small></p>
</div>
</div>
<div id="dreapta">
<div  class="form_centrat">
<h2>Aici este scena</h2> 
<div class="formular" > 
<form method="post" action="rezervare_locuri.php" id="locuri_sala" >
<div class='sala'>
<?php	

$pop = array_pop ($locuri_rezervate);

for ($l = 1; $l <= 100; $l++) { 
$flag= 1;
if ($pop) {	
	if ($pop['id_loc'] == $l) {
		if (isset($_SESSION['id_user'])) {
			if ( $pop['id_user'] == $_SESSION['id_user']) {
			$flag = 3 ; // ----- loc rezervat deja de utilizatorul logat
			} else {$flag = 2 ;} // ----- loc rezervat deja de de alt utilizator
		} else {$flag = 2 ;} // ----- loc rezervat deja de de alt utilizator
	} else {$flag = 1 ;} // ----- loc liber
}
?>
			<div class="<?php if ($flag == 3) {echo "rezervat-user";} if ($flag == 2) {echo "rezervat";} if ($flag == 1) {echo "loc";}?>"> <label for="loc<?php echo $l;?>" > <?php echo ($flag > 1 ? "<small>rezervat</small>" : $l);?> </label><input class="check-loc" id="loc<?php echo $l;?>" name="lc<?php echo $l;?>" type="checkbox" value="<?php echo $l;?>" style="display: none" <?php if ($flag > 1) {echo "disabled";}?>>  </div>
		<?php 
		if ($pop) { if ($pop['id_loc'] == $l) { $pop = array_pop ($locuri_rezervate);}}
	} ?>	
</div>	<br> <br> 	 <input type="hidden" name="rezervare" value="rezervare">
<?php 
					if (tip_utilizator() == 1 ) { ?>
		<button type="submit" style="display: flex; justify-self: center;">Rezerva locurile selectate</button>
			<?php } elseif (tip_utilizator() ==9 )  {?> 
		<span style="display: flex; justify-self: center; color: red;">Cont de administrator.</span>
			<?php } elseif (tip_utilizator() == 0) { ?> 
		<span style="display: flex; justify-self: center;">Pentru a rezerva bilete trebuie sa va&nbsp;<a href="rezervare_locuri.php?login" >logati</a></span>
			<?php }  ?>
		<div class="form_centrat" style="color: red">  <p> <?php if ($mesaj=='') {echo '<br>';} else {echo $mesaj;} ?></p> </div> 
		<div id="atentie" ><small>Situatia locurilor disponibile este cea de la momentul incarcarii paginii. Este posibil ca in momentul trimiterii cererii de rezervare unele locuri sa fi fost deja rezervate de alti utilizatori. In aceasta situatie nu se va rezerva niciunul din locurile selectate in cerere. Veti putea face o noua selectie in functie de situatia actualizata. </small></div>

</form>

</div>
</div>
<div class="form_centrat" style="color: red">  <p> </p> </div> 
</div>
</main> 

<?php require('fragmente/footer.php'); ?>
</body>
</html>
