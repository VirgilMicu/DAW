<?php


 require('../recaptcha_keys.php');

function cripteaza ($deCriptat) {
global $encryption_key, $iv;
$criptat =  openssl_encrypt($deCriptat, "aes-256-cbc", $encryption_key, '0', $iv);
return $criptat;
}

function decripteaza ($deDecriptat) {
global $encryption_key, $iv;
$decriptat = openssl_decrypt($deDecriptat, "aes-256-cbc", $encryption_key, '0', $iv);
return $decriptat;
}
// --------------------------- functie car returneaza tipul de utilizator, sau 0 daca este vizitator

function tip_utilizator() {
if (isset($_SESSION['tip_utilizator'])) { // ----------------------- daca avem un user logat
	if ($_SESSION['tip_utilizator'] == 9) { return 9;}
	if ($_SESSION['tip_utilizator'] == 1) { return 1;}
} else {return 0;}
}



//--------------------------------------------------cautare email in baza de date. returneaza 1 sau 0 (numarul de rezultate
function cautare_email ($email, $conexiune ) {
	$stmt = $conexiune->prepare ("SELECT * FROM utilizatori WHERE email=?;");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$num_results = $result->num_rows;
	return $num_results;
}

// --------------------------------------------- daca autentificarea reuseste, returneaza datele userului
function autentificare ($email, $parola, $conexiune ) { 
	$stmt = $conexiune->prepare ("SELECT * FROM utilizatori WHERE email=?;");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$num_results = $result->num_rows;
		if ($num_results == 1){  // daca emailul este in baza
		$row = $result->fetch_assoc();
		if (password_verify ($parola,$row['parola'])){// daca parola e corecta
			unset ($row['parola']); // --- returnam date fara parola
			return  $row;
		} else { return 0;} // parola gresita
	} else { return 0;} // emailul nu e in baza
}

//------------------------------------------ verificare g-recaptcha-response
function captcha_ok() { 
	global $skey;
    if(isset($_POST['g-recaptcha-response'])){
        $captcha=$_POST['g-recaptcha-response'];
        } else {return 0;}
	//$ip = $_SERVER['REMOTE_ADDR'];
	$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($skey) .  '&response=' . urlencode($captcha);
    $response = file_get_contents($url);
    $responseKeys = json_decode($response,true);
	if($responseKeys["success"]) {
	return 1;} else {return 0;}
}

//------------------------------------------ validare nume/prenume. rturneaza numele / prenumele daca este valid (contine doar anumite tipuri de caractere), sau 0 altfel
function validare_nume($txt, $lungime_minima){
	$txt = htmlspecialchars (trim($txt));
	if (preg_match('/[^A-Z,a-z,\s,-]/', $txt)>0 || strlen($txt)>50 || strlen($txt)<$lungime_minima) {
		return 0;
	} else {return $txt;}
}


//------------------------------------------ cautare lista productii, eventual dupa ID. returneaza obiect mysqli_result
function cautare_productii($conexiune, $id) {
	if ($id!=''){  //------------ cautare cu ID
	$stmt = $conexiune->prepare ("SELECT * FROM productii_spectacole WHERE id_productie=?;");
	$stmt->bind_param("i", $id);
	} else { //------------ cautare fara ID
	$stmt = $conexiune->prepare ("SELECT * FROM productii_spectacole");	
	}
	$stmt->execute();	
	$rezultat = $stmt->get_result();
	return $rezultat;
	exit;
}

//--------------------------------------------------------------------verifica daca un intreg apartine unei lista
function validare_intregi($numar, $lista) {
	$numar = htmlspecialchars ($numar);
	if (is_numeric ($numar)) {
		foreach ($lista as $val) {
			if ($numar == $val) {return $val;}
		}
	}
	return NULL;
}	


//--------------------------------------------- verifica data valida in format YYYY-MM-DD
function data_valida($x) {
    return (date('Y-m-d', strtotime($x)) == $x);
}

//--------------------------------------------- verifica data valida in format YYYY-MM-DD    + o compara cu data actuala in functie de operatorul dat ca al doilea argument: < <= = >= >
function data_ref_prezent($x, $comparatie) {
	if (data_valida($x)) {
		switch ($comparatie) {
			case '<': if (strtotime($x) < strtotime(date('Y-m-d',time()))) {return 1;} else {return 0;}
			case '<=': if (strtotime($x) <= strtotime(date('Y-m-d',time()))) {return 1;} else {return 0;}
			case '==': if (strtotime($x) == strtotime(date('Y-m-d',time()))) {return 1;} else {return 0;}
			case '>=': if (strtotime($x) >= strtotime(date('Y-m-d',time()))) {return 1;} else {return 0;}
			case '>': if (strtotime($x) > strtotime(date('Y-m-d',time()))) {return 1;} else {return 0;}
		default: return 0;
		}
	} else { return 0;}
}


// ----------------------------------------------- string cu data in romana (poate fi doar luna si an) 
function data_romaneasca($z, $l, $a) {
	$luni = array ('Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie');
	if ($z == '') {
		return $luni[$l-1].'-'.$a; 
	} else {
	$z = ltrim($z, '0');
	$l = ltrim($l, '0');
	$chk=$a.'-'.str_pad($l, 2, '0',STR_PAD_LEFT).'-'.str_pad($z, 2, '0',STR_PAD_LEFT) ;
	if (data_valida( $chk)){
		return $z.' '.$luni[$l-1].' '.$a;
	} else { return "#data_invalida(".$chk.')';}
	}
}

//-------------------------------------------------------------------returneaza daca o valoare exista intr-un camp dintr-un tabel
function tab_camp_val ($conexiune, $tab, $camp, $val, $tip) {
	$sql = "SELECT * FROM ".$tab." WHERE ".$camp." = ?;";
	$stmt = $conexiune->prepare ($sql);
	if ($tip == "i" || $tip == "s") { $stmt->bind_param($tip, $val);}
	$stmt->execute();
	$rezultat = $stmt->get_result() ;
	return  $rezultat->num_rows;
}


function log_logare ($conexiune, $id_user, $ip) {
		$tsp = date('Y-m-d H:i:s', time());
		$stmt = $conexiune->prepare ('INSERT INTO logari (id_user, tsp, ip) VALUES (?, ?, INET6_ATON(?));');
		$stmt->bind_param("iss", $id_user, $tsp, $ip);
		$stmt->execute();
}

function log_accesare($conexiune, $pagina, $id_user, $ip) {
		$tsp = date('Y-m-d H:i:s', time());
		$stmt = $conexiune->prepare ('INSERT INTO accesari (pagina, id_user, tsp, ip) VALUES (?, ?, ?, INET6_ATON(?));');
		$stmt->bind_param("siss", $pagina, $id_user, $tsp, $ip);
		$stmt->execute();
}

?>