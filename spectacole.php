<?php 
session_start();
 require('../conexiune.php');
 require('fragmente/functii.php'); 

if ( tip_utilizator()!=9){
	header('Location: index.php');
	exit;
}


DEFINE ('LUNI_TRECUT', 12);
DEFINE ('LUNI_VIITOR', 24);
$mesaj='';

if (!isset ($_SESSION['an'])) { $_SESSION['an'] = date('Y');}
if (!isset ($_SESSION['luna'])) { $_SESSION['luna'] = str_pad(date('m'),2,'0', STR_PAD_LEFT);}
if (!isset ($_SESSION['zi'])) { $_SESSION['zi'] = '';}

//------------------------------------------------------------------preluare valori luna si an, nagivare << >>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (isset ($_POST['productie'])) { //-----------------------------------------adaugare spectacole
	$idprod = htmlspecialchars($_POST['productie']);
	$data_programare = $_SESSION['an'].'-'.$_SESSION['luna'].'-'.$_SESSION['zi'];
	if (is_numeric($idprod) && data_ref_prezent($data_programare,'>')) {  //------- daca avem un id de productie si o data din viitor
		$rezultat = cautare_productii($db, $idprod);
		if ($rezultat->num_rows > 0) {
			$row = $rezultat->fetch_assoc();
				if ($row['status'] == 2) { // ----------------------daca productia are status activ 
				try {
					$stmt = $db->prepare ("INSERT INTO spectacole (id_productie, data_spectacol) VALUES (?, ?);");
					$stmt->bind_param("is", $idprod, $data_programare);
					$stmt->execute();
					$_SESSION['zi'] = '';
					header('Location: spectacole.php');
					exit;
				} catch (mysqli_sql_exception $exception) {
				$mesaj= "spectacolul nu a fost adaugat.".$exception->getMessage();		
				}
				} else {$mesaj = "Input necorespunzator";}
		} else {$mesaj = "Input necorespunzator";}
	} else {$mesaj = "Input necorespunzator";}
	}
}

//------------------------------------------------------------------preluare valori zi din luna
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset ($_GET['zi'])) {
		$zi=substr($_GET['zi'],0,2);
		$check_data = $_SESSION['an'].'-'.$_SESSION['luna'].'-'.$zi;
		if (data_valida($check_data)) {
			if (data_ref_prezent($check_data, '>')) {   //----- ziua-luna-an trebuie sa fie in viitor
				$_SESSION['zi'] = date('d', strtotime($check_data));
			}
		}
	}
}
//-----------------------------------------------	navigarea pe luni
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset ($_GET['luna']) &&  isset ($_GET['an'])) {
		$luna = validare_intregi($_GET['luna'], range(1, 12));
		$an = validare_intregi($_GET['an'], range(idate('Y') - intdiv(LUNI_TRECUT, 12) - 2, idate('Y') + intdiv(LUNI_VIITOR, 12) + 2));
		if ($luna && $an) {
			$target = $an * 12 +  $luna; 
			$actual = idate('Y') * 12 + idate('m') ;
			if ($target >= $actual - LUNI_TRECUT && $target <= $actual + LUNI_VIITOR) {
				$_SESSION['an'] = $an ;
				$_SESSION['luna'] = str_pad($luna, 2, '0', STR_PAD_LEFT);
				$_SESSION['zi']='';
			}
		}
  }
}

$luna = $_SESSION['luna'] ;
$an = $_SESSION['an'];		
$zi = $_SESSION['zi'] ;		

switch ($luna) {
	case 1 : $back = 'luna='.'12'.'&an='.($an-1); $forth = 'luna='.'2'.'&an='.$an; break;
	case 12 : $back = 'luna='.'11'.'&an='.$an; $forth = 'luna='.'1'.'&an='.($an+1); break;
	default: $back = 'luna='.($luna-1).'&an='.$an; $forth = 'luna='.($luna+1).'&an='.$an; break;
}

$zile= cal_days_in_month(CAL_GREGORIAN, $luna, $an);
$prima_zi = (jddayofweek (gregoriantojd($luna,1,$an)) + 6 ) % 7 + 1; //----- calculam ce zi a saptamanii este prima zi din luna (luni=1 ... duminica=7);
$zile_sapt = array ('Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica');

log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
require('fragmente/head.php'); 
require('fragmente/nav.php');
?>
<main> 
<link rel="stylesheet" href="/css/spectacole.css" type="text/css"/>
<div id="pag-spec">
<div id="lista-spec" class="form_centrat">
	
<div id="lista"> 
<?php
$stmt = $db->prepare ('SELECT * FROM spectacole_locuri  WHERE data_spectacol BETWEEN ? AND ? ORDER BY data_spectacol;');
$start = $an.'-'.$luna.'-01';
$end = $an.'-'.$luna.'-'.$zile;
$stmt->bind_param('ss', $start, $end);
$stmt->execute();	
$rezultat = $stmt->get_result();
$row = $rezultat->fetch_assoc();

for ( $i = 1 ; $i <= $zile ; $i ++) {
$data_spectacol = $an.'-'.$luna.'-'.str_pad($i,2,'0',STR_PAD_LEFT);
	$zi_sapt=($i + $prima_zi - 2) % 7 ;  // calculam, pentru ziua curenta, ce zi a saptamanii este, intre luni=0 .. duminica =6. va fi folosita ca index in vectorul $zile_sapt 
	$flag =0;
	$flag_data_viitor=0;
	if ($row) {
		if ($row['data_spectacol'] == ($data_spectacol) ){
		 $flag=1;
		}	
		if (strtotime($data_spectacol) > strtotime($an.'-'.$luna.'-'.idate('d')) ){
		 $flag_data_viitor =1;
		}	
	}	
	if (strtotime($data_spectacol) > strtotime(idate('Y').'-'.idate('m').'-'.idate('d')) ){
		 $flag_data_viitor =1;
		}	
?>
		<div class="elem-lista <?php if ($i == $zi) {echo 'zi-selectata';} ?>" <?php if ($i == 1) {echo 'style="grid-column-start: '.$prima_zi.'";';} ?> >
			<div class="elem-lista-sus">
				<div class="elem-lista-zisapt">
				<span <?php if ($zi_sapt >4) {echo 'class="w-e"';} echo '>'. $zile_sapt[$zi_sapt]; ?></span><br>
				</div>	
				<div class="elem-lista-sus-data">
				<span <?php if ($zi_sapt >4) {echo 'class="w-e"';}?>> <?php echo str_pad($i,2,'0',STR_PAD_LEFT).'-'.$luna.'-'.$an;?></span><br>
				</div>					
			</div>
			<div class="elem-lista-cen">
				<div class="elem-lista-cen-tit">
				<span><b> <?php  
				echo ( $flag ? (isset($row['titlu'])?$row['titlu']:''): ''); ?> </b> <?php  echo ( !$flag &&  $flag_data_viitor ? "<a class='link-ad' href='spectacole.php?zi=".str_pad($i,2,'0',STR_PAD_LEFT)."'>Adauga</a>" : ''); 
				?></span>
				</div>	
				<div class="elem-lista-cen-desc">
				<span><small><?php  echo ( $flag ? (isset($row['descriere_interna'])?$row['descriere_interna']:'') : ''); ?></small></span>
				</div>				
			</div>
			<div class="elem-lista-jos">
				<div class="elem-lista-jos-rez">
				<?php  
				if ($flag && isset($row['nr_locuri_vandute'])) {
				echo '<a class="link-normal" href="rezervare_locuri.php?id='.$row['id_spectacol'].'">'.'<b>'.$row['nr_locuri_vandute'].'</b> &nbsp;bilete vandute'.'</a>' ;
				}?>   
				</div>	
			</div>
		</div>
<?php 
if ($flag) { $row = $rezultat->fetch_assoc();};
}
	?>
	
	</div>
	</div>
	<div id="ad-spec" class="form_centrat">

			<div id="perioada"> 
			<a class="astdr" href="spectacole.php?<?php echo $back;?>" ><div class="stdr"> <span class="fa-solid fa-caret-left"></div></span></a>
				<div class="stdr luna-selectata"> <b><?php echo data_romaneasca('', $luna, $an)?> </b></div>
			<a class="astdr" href="spectacole.php?<?php echo $forth;?>" ><div class="stdr"> <span class="fa-solid fa-caret-right"></div></span></a>
			</div><br>
		<p style="text-align:center" ><b>Adaugare spectacole</b></p> 
		<form class="inp-prod" action="spectacole.php" method="post" >
		<label for="productie">Productii active:</label> <br> 
		<select name="productie" class="inp-prod" size="10" >		
		<?php echo $start." - " . $end;
			$stmt = $db->prepare ("SELECT * FROM productii  WHERE status=2 ORDER BY id_productie DESC;");
			$stmt->execute();	
			$rezultat = $stmt->get_result();
			while ($row = $rezultat->fetch_assoc()) {
				echo "<option value='".$row['id_productie']."'>".$row['titlu']." (".$row['descriere_interna'].")</option>";
			} ?>
		</select>
		<br><br>
		<div style="justify-self:center"> <span><b> Data: <?php if ($_SESSION['zi'] !='') {echo data_romaneasca($_SESSION['zi'], $_SESSION['luna'], $_SESSION['an']);} ?> </b></span></div>
		<br>
		<input type="submit" value="Programeaza in calendar" style="display: flex; justify-self: center;" > 
		</form>
		<p style="justify-self:center"> 
		 <a href="spectacole.php">Renunta<a> </p>
		<div class="form_centrat" style="color: red">  <p><?php echo $mesaj ; ?></p> </div> 			
	</div>
</div>


</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>