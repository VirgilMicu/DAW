
<?php 
//actualizare_svg();

function actualizare_svg () {
for ($i= 1 ; $i<200 ; $i++) {
	$img = $i.'.svg';	
	//if (strpos( $text , '/'.$img) ){
		$url = 'https://www.meteoromania.ro/wp-content/plugins/meteo/img/icons/'.$i.'.svg';
		$img = '/svg/'.$img; 
		echo $img.'<br>';
		try {
		copy($url, $_SERVER['DOCUMENT_ROOT'].'/svg/'.$i.'.svg');
		} catch (Exception $ex) { }
			
}
}
$text = file_get_contents ('https://www.meteoromania.ro/vremea/starea-vremii-romania/');
$vect = explode ('<div class="situatiameteo-slider nu-vizibil" style="margin-top: 20px;">',$text );
$text = $vect[1];
$vect = explode ('<ul class="slides" style="width:',$text );
$text = $vect[1];
$vect = explode ('</ul>',$text );
$text = $vect[0];

$text = str_replace('<li','#separator#<div', $text);
$text = str_replace('/li>','/div>#separator#', $text);
$text = str_replace('#separator##separator#','#separator#', $text);
$text = str_replace('https://www.meteoromania.ro/wp-content/plugins/meteo/img/icons/','svg/', $text);
$text = str_replace('h2>','h3>', $text);

//var_dump //(htmlspecialchars($text));

$vect = explode ('#separator#',$text );
array_shift ($vect);
array_pop ($vect);

$vect_asoc=[];
foreach ($vect AS $locatie) {
	$html = $locatie ;
	$locatie = str_replace('<h3>','#separator#', $locatie );
	$locatie = str_replace('</h3>','#separator#', $locatie );
	$vtemp = explode ('#separator#',$locatie );
	$locatie = $vtemp [1];
	$vect_asoc[$locatie]= $html ;
}


$locatie_preferata = '';

  if (isset ($_COOKIE['locatie'])) {
	  if (array_key_exists($_COOKIE['locatie'], $vect_asoc)) {
	  $locatie_preferata = $_COOKIE['locatie'];
	  } else { setcookie('locatie','-', time() - 6000);}
  }

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset ($_GET['locatie'])) {
	  $loc = htmlspecialchars ($_GET['locatie'] );
	  if (array_key_exists($loc, $vect_asoc)) {
		setcookie('locatie',$loc);
	  $locatie_preferata = $loc;
	  }
	  unset ($_GET['locatie'] );
	  header('Location: index.php'); 
	  exit;  
  }
  
}

?>

