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

if (isset($_COOKIE['tmp_inreg'])) {
	header('Location: validare.php');
	exit;
}

//foreach ($_POST as $x => $y) //{echo $x.": ". $y ." <br>";}
$nu_robot = captcha_ok();

$erori ='';
$info=array ('email'=>'', 'nume'=>'', 'prenume'=>'', 'parola'=>'','token'=>'','incercari'=>'0','expirare'=>'');
$flag='';

if (isset($_POST['email']))  {
		if (!empty($_POST['email']) && !empty($_POST['nume']) && !empty($_POST['prenume']) && !empty($_POST['parola1']) && !empty($_POST['parola2']) &&  $nu_robot)  {
		 
		 $temp = filter_var(htmlspecialchars ($_POST['email']), FILTER_VALIDATE_EMAIL);//-------------- format email
		if (!$temp){
			$erori .= "Emailul nu este in forma corecta. ";
		} else { 		
			if ( cautare_email($temp, $db) > 0 ) { 
				$flag= "user existent";
			} 
			$info['email'] = $temp;
		}

		$temp = validare_nume($_POST['nume'], 3);//-------------format nume
		if (!$temp) {
			$erori .= "Numele contine caractere nepermise, sau nu are lungimea acceptata (3-50). ";
		} else { $info['nume'] = $temp;}

		$temp = validare_nume($_POST['prenume'], 3);//-------------format prenume
		if (!$temp) {
			$erori .= "Prenumele contine caractere nepermise, sau nu are lungimea acceptata (3-50). ";
		} else { $info['prenume'] = $temp;}

		$temp1 = trim($_POST['parola1']);//-------------format parola
		$temp2 = trim($_POST['parola2']);
		if (preg_match('/[^A-Z,a-z,0-9,~!@#$%^&*()\-_+={}|\[\]]/', $temp1)>0 || strlen($temp1)>20 || strlen($temp1)<8 ||
			preg_match('/[A-Z]/', $temp1)==0 || preg_match('/[a-z]/', $temp1)==0 || preg_match('/[0-9]/', $temp1)==0 || preg_match('/[~!@#$%^&*()\-_+={}|\[\]]/', $temp1)==0 ||
			$temp1 !== $temp2 ) {
				$erori .= "Parolele nu coincid, sau nu respecta cerintele. ";
			} else { $info['parola'] = $temp1;}

		$info['token']=rand(100000000,999999999);
				
			if (strlen ($erori) == 0) {
				$email = $info['email'];
				if ($flag==''){
					$mesaj="Buna ziua,<br>Codul de validare al contului la <i>Teatrul de opera</i> este <b>".$info['token']."</b>. Copiati acest cod in pagina de validare.<br>Salutari,<br>Echipa <i>Teatrul de opera</i>";
				} else {
					$mesaj="Buna ziua,<br>Acest mesaj este trimis prin formularul de creare cont la <i>Teatrul de opera</i>. Deja exista un cont pe aceasta adresa. Daca ati incercat sa va logati sau resetati parola, va invitam sa folositi formularul corespunzator.<br>Salutari,<br>Echipa <i>Teatrul de opera</i>";
					$info['token'] = "user_existent_".rand(100000,999999);
				}
				$rez = trimite_mail($email,'','Creare cont Teatrul de opera',$mesaj);
				if ($rez==0) {
                    $info['expirare']= time() + 600;
					if (setcookie('tmp_inreg', cripteaza(json_encode($info)), $info['expirare'],"","",True, True ))  {
					header('Location: validare.php');
					exit;
					} else { $erori = "Eroare in procesul de inregistrare. Va rugam reveniti."; }
				} else {$erori = "Eroare in procesul de inregistrare. Va rugam reveniti.";}
			}
		} else {$erori = "Trebuie sa completati toate campurile si sa bifati ca nu sinteti robot!";}
	}
 
  log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); ?>
<main> 
     <script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div id="login-c" class="form_centrat">

<?php

?>
<h4> Creare cont</h4> 

<form method="post" action="inregistrare.php" id="inregistrare" >
<table class="formular" > 
	<tr> <td style="text-align: end"> Email: </td> <td> <input type="text" name="email" size="30">  </td> </tr> 
	<tr> <td style="text-align: end"> Nume: </td> <td> <input type="text" name="nume" size="30">  </td>  </tr> 
	<tr> <td style="text-align: end"> Prenume: </td> <td> <input type="text" name="prenume" size="30">  </td> </tr> 
	<tr> <td style="text-align: end"> Parola: </td> <td> <input type="password" name="parola1" size="30"autocomplete="new-password">  </td> </tr> 
	<tr> <td style="text-align: end"> Confirmare parola: </td> <td> <input type="password" name="parola2" size="30" autocomplete="new-password">  </td> </tr> 
	<tr> <td colspan="2" align="center"> <br> <b> Nume si prenume</b>: 3-50 litere, spatiu sau - (minus). <br> <b>Parola</b>: 8-20 caractere: A-Z,a-z,0-9,~!@#$%^&*()-_+={}|[]. Cel putin o litera mare, una mica, o cifra si un caracter special. </td> </tr> 
	<tr> <td colspan="2" align="center"> </br> 
		<input type="submit"  value="Inregistrare" align="center" >  <br><br>
		<div class="g-recaptcha" data-sitekey="6LdjwaQqAAAAAD7U9IvUlTWIIIgx5AKsJQFEH5RY"></div>
	</td> </tr> 
</table>
</form>
</div>

<div class="form_centrat" style="color: red">  <p> <?php echo $erori ; ?></p> </div> 

</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>
