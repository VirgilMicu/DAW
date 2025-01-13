<?php 
session_start();
 require('../conexiune.php');
 require('fragmente/functii.php'); 

if ( tip_utilizator()!=9){
	header('Location: index.php');
	exit;
}

$mesaj='';

require('fragmente/head.php'); 
require('fragmente/nav.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$titlu = (isset($_POST['titlu']) ? substr(htmlspecialchars(trim($_POST['titlu'])),0,100) : '');
	$descriere_interna = (isset($_POST['descriere_interna']) ? substr(htmlspecialchars(trim($_POST['descriere_interna'])),0,50) : '');
	$descriere_scurta = (isset($_POST['descriere_scurta']) ? substr(htmlspecialchars(trim($_POST['descriere_scurta'])),0,200) : '');
	$descriere = (isset($_POST['descriere']) ? substr(htmlspecialchars(trim($_POST['descriere'])),0,1000) : '');
	$status = (isset($_POST['status']) ? validare_intregi($_POST['status'],[1,2,5]) : '');
	
	if (isset( $_SESSION['id_productie_de_modificat']) && !is_null($status)) {    //-------------------- actualizare  productii 	

			$idp = $_SESSION['id_productie_de_modificat'];	
			unset ($_SESSION['id_productie_de_modificat']);
			$rezultat = cautare_productii($db, $idp);
			$row = $rezultat->fetch_assoc();
			
			if ($row['nr_spectacole'] == 0) { // ------------------actualizare productii fara spectacole (inclusiv nume si descriere vizibile public)
				if ($titlu !='' && $descriere_interna !='' && $descriere_scurta !='' && $descriere !='') {
				$stmt = $db->prepare ("UPDATE productii SET titlu = ?, descriere_scurta = ?, descriere_interna = ?, descriere =?, status = ? WHERE id_productie=?;");
				$stmt->bind_param("ssssii", $titlu, $descriere_scurta, $descriere_interna, $descriere, $status, $idp); 
				$stmt->execute();
				header('Location: productii.php');
				exit;} else {$mesaj='imputurile nu corespund';}
			}
			if ($row['nr_spectacole'] > 0) { // ---------------actualizare productii cu spectacole (doar status si descriere interna)
				if ( $descriere_interna !='') {
				$stmt = $db->prepare ("UPDATE productii SET descriere_interna = ?, status = ? WHERE id_productie=?;");
				$stmt->bind_param("sii", $descriere_interna, $status, $idp);				
				$stmt->execute();
				header('Location: productii.php');
				exit;} else {$mesaj='imputurile nu corespund';}
			}
	} else {     //------- ------------------------------------------------------------- adaugare productii 
			if ($titlu !='' && $descriere_interna !='' && $descriere_scurta !='' && $descriere !='') {
			$stmt = $db->prepare ("INSERT INTO productii (titlu, descriere_scurta, descriere_interna, descriere) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("ssss", $titlu, $descriere_scurta, $descriere_interna, $descriere);
			$stmt->execute();
			header('Location: productii.php');
			exit;} else {$mesaj='imputurile nu corespund';}
	}
}	

$row = array('id_productie'=>'','titlu'=>'','descriere_scurta'=>'','descriere_interna'=>'','descriere'=>'','status'=>'','nr_spectacole'=>'');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['productie'])) {
		$id=htmlspecialchars($_GET['productie']);
		if (is_numeric($id)) {
			$rezultat = cautare_productii($db, $id);
			if ($rezultat->num_rows > 0) {
			$row = $rezultat->fetch_assoc();
				//if  ($row['nr_spectacole'] == 0) {
					$_SESSION['id_productie_de_modificat']=$row['id_productie'];
				//} else {
					//$row = array('id_productie'=>'','titlu'=>'','descriere_scurta'=>'','descriere_interna'=>'','descriere'=>'','status'=>'','nr_spectacole'=>'');
					//unset($_SESSION['id_productie_de_modificat']);	}
			//} else { unset($_SESSION['id_productie_de_modificat']); }
			} else { unset($_SESSION['id_productie_de_modificat']); }		
		} else { unset($_SESSION['id_productie_de_modificat']); }	
	}
	if (isset($_GET['new'])) {unset($_SESSION['id_productie_de_modificat']); }
}
 
 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
?>
<main> 
<link rel="stylesheet" href="/css/productii.css" type="text/css"/>
<div id="pag-prod">
<div id="lista-prod" class="form_centrat">
	<div id="lista"> 
<?php 

if (!$db->connect_error) { 
	$rezultat = cautare_productii($db, '');
	while ($row_l = $rezultat->fetch_assoc()) {
		switch ($row_l['status']) {
			case 1; $status='Inactiva'; break;
			case 2; $status='Activa'; break;
			case 5; $status='Arhivata'; break; 
			}
?>
		<div class="elem-lista <?php echo $status;?>">
			<div class="elem-lista-st">
				<div class="elem-lista-st-titlu">
				<span><b>Titlu: </b><?php echo $row_l['titlu'];?></span><br><br>
				</div>
				<div class="elem-lista-st-descriere-interna">
				<span><b>Descriere interna: </b><?php echo $row_l['descriere_interna'];?></span>
				</div>	
				<div class="elem-lista-st-descriere-scurta">
				<span><b>Descriere scurta: </b><?php echo $row_l['descriere_scurta'];?></span>
				</div>						
			</div>
			<div class="elem-lista-cen">
				<div class="elem-lista-cen-descriere">
				<span><b>Descriere: </b><?php echo $row_l['descriere'];?> </span>
				</div>	
			</div>
			<div class="elem-lista-dr">
				<div class="elem-lista-dr-status">
				<p> <b><?php echo $status;?> </b></p>
				</div>	
				<div class="elem-lista-dr-spectacole">
				<p><b><?php echo $row_l['nr_spectacole'];?> </b><small>spectacole</small></p>
				</div>	
				<div class="elem-lista-dr-modif">
				<a href="productii.php?productie=<?php echo $row_l['id_productie'];?>">Modifica</a>
				</div>
				
			</div>
		</div>
<?php 
	}
}
	?>
	
<script type="text/javascript">
function countChars(countfrom,displayto) {
  var len = document.getElementById(countfrom).value.length;
  document.getElementById(displayto).innerHTML = len;
}
</script>
	
	</div>
	</div>
	<div id="modif-lista" class="form_centrat">
		<p style="text-align:center" ><b>Productii</b><br>
		<?php if (isset( $_SESSION['id_productie_de_modificat'])) 
			{ echo "Modificare (Id=".$_SESSION['id_productie_de_modificat'].")"; 
				if ($row['nr_spectacole'] > 0) 
					{echo"<br><small>Productia are spectacole deja programate, nu se pot modifica decat descrierea interna si statusul</small>";} 
			} else {echo "Adaugare";} ?></p> 
		<form method="post" action="productii.php" id="productii" >
		
		<label for="titlu">Titlu (100)<span style="color: grey" id="n1" >0</span><span style="color: grey">/100</span> </label> 
		<br> <input type="text" name="titlu" class="inp-prod" style="color:red" id="ct1" value="<?php echo $row['titlu'];?>" 
		onkeyup="countChars('ct1','n1');" onkeydown="countChars('ct1','n1');" onmouseout="countChars('ct1','n1');" <?php if ($row['nr_spectacole'] > 0) {echo "disabled";} ?>> </input><br>
		
		<label for="descriere_interna">Descriere interna (50)</label> <br> 
		<input type="text" name="descriere_interna" class="inp-prod" style="color:red" value="<?php echo $row['descriere_interna'];?>"><br>
		
		<label for="descriere_scurta">Descriere scurta (200)</label> <br> 
		<textarea name="descriere_scurta" rows="4" class="inp-prod" <?php if ($row['nr_spectacole'] > 0) {echo "disabled";} ?> ><?php echo $row['descriere_scurta'];?></textarea> <br>
		
		<label for="descriere">Descriere (1000)</label> <br> 
		<textarea name="descriere" rows="10" class="inp-prod" <?php if ($row['nr_spectacole'] > 0) {echo "disabled";} ?> ><?php echo $row['descriere'];?></textarea> <br>
		
		  <label for="status" class="selectie" >Status: </label>
		  <select name="status" id="status" style="width: 40%" value='5'>
			<option value="1" <?php if($row['status']==1){echo "selected=\"selected\"";}?>>Inactiva</option>
			<option value="2" <?php if($row['status']==2){echo "selected=\"selected\"";}?>>Activa</option>
			<option value="5" <?php if($row['status']==5){echo "selected=\"selected\"";}?>>Arhivata</option>
		  </select> <br><br>
		<input type="submit"  value="<?php if (isset( $_SESSION['id_productie_de_modificat'])) { echo "Modifica";} else {echo "Adauga";} ?>" style="display: flex; justify-self: center;" > 
		</form>
		<p style=\"justify-self:center\"> <a href="productii.php?new"> Renunta<a> </p>

		
		<div class="form_centrat" style="color: red">  <p> <?php echo $mesaj;?> </p> </div> 	
		
	</div>
</div>


</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>