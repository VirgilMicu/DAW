<?php 
session_start();
 require('../conexiune.php');
 require('../sneaky.php');
 require('../phpmailer/mail_cod.php');
 require('fragmente/functii.php'); 

if ( tip_utilizator()){
	header('Location: index.php');
	exit;
}

//foreach ($_POST as $x => $y) //{echo $x.": ". $y ." <br>";}
$erori ='';
$info=array ('email'=>'','token'=>'');


if (isset($_COOKIE['tmp_rst'])) {
	$praji = json_decode (decripteaza ($_COOKIE['tmp_rst']), True);
	//foreach ($praji as $x => $y) //{echo $x.": ". $y ." <br>";}
	if (isset($_POST['token'])){ // ------------verificam daca e setat $_POST['token'], adica nu intra in pagina din alta parte
		if (!empty($_POST['token']) && !empty($_POST['parola1']) && !empty($_POST['parola2'])) {  //-----------------daca a introdus codul si parolele
			$temp = htmlspecialchars (trim($_POST['token']));
			if ($temp == $praji['token'] && preg_match('/[0-9]{9}/',$temp)==1){ // --- ------token corect introdus 
				$temp1 = htmlspecialchars(trim($_POST['parola1']));//-------------format parola
				$temp2 = htmlspecialchars(trim($_POST['parola2']));
				if (preg_match('/[^A-Z,a-z,0-9,~!@#$%^&*()\-_+={}|\[\]]/', $temp1)>0 || strlen($temp1)>20 || strlen($temp1)<8 ||
				preg_match('/[A-Z]/', $temp1)==0 || preg_match('/[a-z]/', $temp1)==0 || preg_match('/[0-9]/', $temp1)==0 || preg_match('/[~!@#$%^&*()\-_+={}|\[\]]/', $temp1)==0 ||
				$temp1 !== $temp2 ) {
					$erori = "Parolele nu coincid, sau nu respecta cerintele. ";
					} else { 
					$pass = password_hash($temp1, PASSWORD_DEFAULT);
					if (!$db->connect_error) { //--------------- actualizam parola in baza
						$stmt = $db->prepare ("UPDATE utilizatori SET parola=? WHERE email=?;");
						$stmt->bind_param("ss", $pass, $praji['email']);
						$stmt->execute();
						if (autentificare ($praji['email'], $temp1, $db) !=0){ //--- verificam daca noua parola este setata
							setcookie('tmp_rst', '', time() - 6000,"","",True, True );
							require('fragmente/head.php'); 
							require('fragmente/nav.php');
							echo "<div class='form_centrat'>  <p> Parola schimbata cu succes. Va puteti loga <a href='login.php'>aici</a> </p> </div> </main>";
							require('fragmente/footer.php'); 
							echo "</body> </html>";
							exit;
						} else { $erori = "Parola nu a fost schimbata. Incercati mai tarziu.";}
					} else{ $erori  ='Eroare conexiune la baza de date.';} 
				}  
			} else{	 // --- token gresit introdus	
				$praji['incercari']++;   // ------- incrementam numarul de incercari facute cu tokenul
				$inc = $praji['incercari'];
				if ($inc > 2){ // ---------daca a introdus gresit de 3 ori tokenul
					setcookie('tmp_rst', '', time() - 6000,"","",True, True );
					header('Location: resetare_parola.php');
					exit;
					}
				$erori = "Cod gresit de ". $inc." ori."; 
				setcookie('tmp_rst', cripteaza(json_encode($praji)), $praji['expirare'],"","",True, True );
			}
		} else {$erori = "Introduceti codul si parola.";}
	}
} else {header('Location: resetare_parola.php'); 
		exit;}

 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); ?>
<main> 

<div id="login-c" class="form_centrat">

<h4> Resetare parola</h4>

<p>Daca emailul exista in baza noastra, ati primit un cod de resetarea a parolei. Codul este valabil 10 minute. Daca ati vrut sa va logati, mergeti la pagina de logare.</p>
<form method="post" action="confirmare_parola.php" id="resetare2" >
<table class="formular" > 
	<tr> <td style="text-align: end"> Cod: </td> <td> <input type="text" name="token" size="30">  </td> </tr> 
	<tr> <td style="text-align: end"> Parola noua: </td> <td> <input type="password" name="parola1" size="30"autocomplete="new-password">  </td> </tr> 
	<tr> <td style="text-align: end"> Confirmare parola: </td> <td> <input type="password" name="parola2" size="30" autocomplete="new-password">  </td> </tr> 
	<tr> <td colspan="2" align="center"> <br>  <b>Parola</b>: 8-20 caractere: A-Z,a-z,0-9,~!@#$%^&*()-_+={}|[]. Cel putin o litera mare, una mica, o cifra si un caracter special. </td> </tr> 
	<tr> <td colspan="2" align="center"> </br> 
		<input type="submit"  value="Salveaza parola" align="center" >  <br><br>
	</td> </tr> 
</table>
</form>

<div class="form_centrat" style="color: red">  <p> <?php echo $erori ; ?></p> </div> 
</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>