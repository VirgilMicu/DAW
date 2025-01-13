<?php 
session_start();
 require('../conexiune.php');
 require('fragmente/functii.php'); 

DEFINE ('LUNI_TRECUT', 6);
DEFINE ('LUNI_VIITOR', 12);
$mesaj='';


if (!isset ($_SESSION['an_c'])) { $_SESSION['an_c'] = date('Y');}
if (!isset ($_SESSION['luna_c'])) { $_SESSION['luna_c'] = str_pad(date('m'),2,'0', STR_PAD_LEFT);}

$spectacol=[];
//------------------------------------------------------------------preluare valori luna si an, nagivare << >>


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset ($_GET['id'])) {
		$id = htmlspecialchars (trim($_GET['id']));
		$id = ltrim ( $id, '0');
		if (preg_match('/[^0-9]/', $id) == 0) {
			$stmt = $db->prepare ('SELECT * FROM spectacole_locuri WHERE id_spectacol=?;');
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$rezultat = $stmt->get_result();
			if ( $rezultat->num_rows > 0) {
				$spectacol = $rezultat->fetch_assoc();
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
				$_SESSION['an_c'] = $an ;
				$_SESSION['luna_c'] = str_pad($luna, 2, '0', STR_PAD_LEFT);
			}
		}
  }
}

$luna = $_SESSION['luna_c'] ;
$an = $_SESSION['an_c'];	

switch ($luna) {
	case 1 : $back = 'luna='.'12'.'&an='.($an-1); $forth = 'luna='.'2'.'&an='.$an; break;
	case 12 : $back = 'luna='.'11'.'&an='.$an; $forth = 'luna='.'1'.'&an='.($an+1); break;
	default: $back = 'luna='.($luna-1).'&an='.$an; $forth = 'luna='.($luna+1).'&an='.$an; break;
}

$zile= cal_days_in_month(CAL_GREGORIAN, $luna, $an);
$prima_zi = (jddayofweek (gregoriantojd($luna,1,$an)) + 6 ) % 7 + 1;
$zile_sapt = array ('Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica');


//---------------------------logarea accesarilor - problema e ca logheaza de oricate ori reincarci 
log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
require('fragmente/head.php'); 
require('fragmente/nav.php');

?>
<main> 
<link rel="stylesheet" href="/css/calendar-spectacole.css" type="text/css"/>
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
	$zi_sapt=($i + $prima_zi - 2) % 7 ;
	$flag_spectacol =0; 
	$flag_data_viitor=0;
	if ($row) {
		if ($row['data_spectacol'] == $data_spectacol ){
		 $flag_spectacol=1;
		}	
		if (strtotime($data_spectacol) > strtotime($an.'-'.$luna.'-'.idate('d')) ){
		 $flag_data_viitor =1;
		}	
	}	
	if (strtotime($data_spectacol) > strtotime(idate('Y').'-'.idate('m').'-'.idate('d')) ){
		 $flag_data_viitor =1;
		}	
?>
		<div class="elem-lista" <?php if ($i == 1) {echo 'style="grid-column-start: '.$prima_zi.'";';} ?> >
			<div class="elem-lista-sus">
				<div class="elem-lista-zisapt">
				<span style="font-size: 0.7rem;" <?php if ($zi_sapt >4) {echo 'class="w-e"';} echo '>'. $zile_sapt[$zi_sapt]; ?></span><br>
				</div>	
				<div class="elem-lista-sus-data">
				<span <?php if ($zi_sapt >4) {echo 'class="w-e"';}?>> <?php echo $i;?></span><br>
				</div>					
			</div>
			<div class="elem-lista-cen">
				<div class="elem-lista-cen-tit">
				<span><b> <?php  echo ( $flag_spectacol ? '<a class="link-normal" href="calendar_spectacole.php?id='.$row['id_spectacol'].'">'.$row['titlu'].'</a>':''); ?> </b></span>
				</div>	
			
			</div>
			<div class="elem-lista-jos">
				<div class="elem-lista-jos-rez"><small>
				<?php  
				if ( data_ref_prezent($data_spectacol, '>') || (data_ref_prezent($data_spectacol, '==') && strtotime(date('H:i:s')) < strtotime(date('19:00:00'))) ) {
				echo ( $flag_spectacol ? ( ($row['nr_locuri_vandute']< 100) ? '<a class="link-ad" href="rezervare_locuri.php?id='.$row['id_spectacol'].'">Rezerva locuri</a>' :'<span style="color:red;">Sold out</span>') : ''); 
				} else { echo '&nbsp;';}
				?>   
				</small></div>	
			</div>
		</div>
<?php 
if ($flag_spectacol) { $row = $rezultat->fetch_assoc();};
}
	?>
	
	</div>
	</div>
	<div id="descriere">
	<div id="descr-sus">
		<div id="ad-spec" class="form_centrat">
			<div id="perioada"> 

			<a class="astdr" href="calendar_spectacole.php?<?php echo $back;?>"><div class="stdr"> <span class="fa-solid fa-caret-left"></div></span></a>
				<div class="stdr luna-selectata"> <b><?php echo data_romaneasca('', $luna, $an)?> </b></div>
			<a class="astdr" href="calendar_spectacole.php?<?php echo $forth;?>"><div class="stdr"> <span class="fa-solid fa-caret-right"></div></span></a>
			</div>
		<?php 
		if (isset($spectacol['titlu'])) { ?>
			<h3 style="margin-bottom: 0px;"><?php echo $spectacol['titlu']; ?></h3>
			<p style="margin-bottom: 0px;"><b><?php $d= strtotime($spectacol['data_spectacol']); echo data_romaneasca ( date('d', $d), date ('m', $d), date('Y', $d)); ?></b></p>
			<p style="margin-bottom: 0px;"><?php echo $spectacol['descriere_scurta']; ?></p>
			<p style="margin-bottom: 0px;"><small><?php echo $spectacol['descriere']; ?></small></p>
		<?php } ?>
		</div>
	</div>
	<div id="descr-jos">
			<?php 
		if (isset($spectacol['titlu'])) { ?>
				<div class="rezerva">
				<?php  
				$data_spectacol = $an.'-'.$luna.'-'.date('d',strtotime($spectacol['data_spectacol']));
				if ( data_ref_prezent($data_spectacol, '>') || (data_ref_prezent($data_spectacol, '==') && strtotime(date('H:i:s')) < strtotime(date('19:00:00'))) ) {
				echo ( ($spectacol['nr_locuri_vandute']< 100) ? '<a class="link-evident" href="rezervare_locuri.php?id='.$spectacol['id_spectacol'].'">Rezerva locuri</a>' :'<span style="color:red;">Sold out</span>');
				}
				?>   
				</div>	
			<?php } ?>
	</div>
	</div>


</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>