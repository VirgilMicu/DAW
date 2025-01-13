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

//foreach ($_POST as $x => $y)// {echo $x.": ". $y ." <br>";}
$nu_robot = captcha_ok();

if (isset($_COOKIE['tmp_rst'])) {
	header('Location: confirmare_parola.php');
	exit;
}


$erori ='';
$info=array ('email'=>'', 'token'=>'','incercari'=>'0','expirare'=>'');
$flag='';


if (isset($_POST['email']))  {
$etapa=1;
	if (!empty($_POST['email']) &&  $nu_robot)  {
	$email = filter_var(htmlspecialchars ($_POST['email']), FILTER_VALIDATE_EMAIL);//-------------- format email
		if ($email){	
			$flag= cautare_email($email, $db);
			$info['token']=rand(100000000,999999999);
			$info['expirare']= time() + 600;
				if ($flag) { //-------------------- user existent
					$mesaj="Buna ziua,<br>Codul de resetare a parolei la <i>Teatrul de opera</i> este <b>".$info['token']."</b>. Copiati acest cod in pagina de de schimbare a parolei.<br>Salutari,<br>Echipa <i>Teatrul de opera</i>";
					$info['email']= $email ;
					$rez = trimite_mail($email,'','Resetare parola Teatrul de opera',$mesaj);
					if ($rez==0) {
						setcookie('tmp_rst', cripteaza(json_encode($info)), $info['expirare'],"","", True, True);
						header('Location: confirmare_parola.php');
						exit;
					} else { $erori .= "Nu s-a putut trimie mailul.";}					
				} else { //-------------------- user existent
					$info=['token'=> "user_inexistent_".rand(100000,999999)]; // punem in token o valoare care nu poate fi validata
					setcookie('tmp_rst', cripteaza(json_encode($info)), $info['expirare'],"","",True, True );
					header('Location: confirmare_parola.php');
					exit;
				}
		} else { $erori .= "Emailul nu este in forma corecta. ";}
	} else {$erori = "Trebuie sa completati emailul si sa bifati ca nu sinteti robot!";}
}
 
 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); ?>
<main> 
     <script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div id="login-c" class="form_centrat">

<h4> Resetare parola</h4>

<p> Daca v-ati uitat parola sau vreti sa o schimbati, aici este locul potrivit.
 Introduceti adresa de email cu care va logati si veti primi un cod de resetare a parolei.</p>
<form method="post" action="resetare_parola.php" id="resetare1" >
<table class="formular" > 
	<tr> <td style="text-align: end"> Email: </td> <td> <input type="text" name="email" size="30">  </td> </tr>  
	<tr> <td colspan="2" align="center"> </br> 
		<input type="submit"  value="Trimite cod" align="center" >  <br><br>
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