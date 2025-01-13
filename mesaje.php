<?php 
session_start();
 require('../conexiune.php');
 require('../sneaky.php');
 require('../phpmailer/mail_cod.php');
 require('fragmente/functii.php'); 


if ( tip_utilizator()==9){
	header('Location: index.php');
	exit;
}

//foreach ($_POST as $x => $y) //{echo $x.": ". $y ." <br>";}
if (isset ($_SESSION['tip_utilizator'])) {
	if ($_SESSION['tip_utilizator'] == 1) {
	$logat = 1; //------------- tip pagina utilizatori logati
	} else { $logat = 0;} //------------- tip pagina utilizatori NE-logati
} else { $logat = 0;} //------------- tip pagina utilizatori NE-logati

if ($logat) {$nu_robot =1;}
if (!$logat) {$nu_robot = captcha_ok();}

$erori ='';
$flag_mail_trimis=0;


if (isset($_POST['mesaj']))  {
		if ((!$logat && !empty($_POST['email']) && !empty($_POST['nume_prenume']) && !empty($_POST['mesaj']) &&  $nu_robot) ||
		     ($logat &&  !empty($_POST['mesaj']))) {

 			 if (!$logat) {
				 $email = filter_var(htmlspecialchars ($_POST['email']), FILTER_VALIDATE_EMAIL);//-------------- format email
					if (!$email){
						$erori .= "Emailul nu este in forma corecta. ";} 
				$nume_prenume = validare_nume($_POST['nume_prenume'],7);//-------------format nume si prenume
					if (!$nume_prenume) {
						$erori .= "Numele contine caractere nepermise, sau nu are lungimea acceptata (7-50). ";} 
			 }		 
			 $mesaj = htmlspecialchars(substr($_POST['mesaj'],0,500));
			 $email_user = ( $logat ? $_SESSION['email'] : $email );
			 $nume_user = ( $logat ? $_SESSION['prenume'].' '.$_SESSION['nume'] : $nume_prenume );
			if (strlen ($erori) == 0) {
				if (!$logat) { 
					$subiect = 'Mesaj de la vizitator site';
					$text_mail = 'Nume: '.$nume_prenume.'<br>Email: '.$email.'<br>Mesaj: '.$mesaj;
				}
				if ($logat) { 
					$subiect = 'Mesaj de la utilizator '.$_SESSION['prenume'].' '.$_SESSION['nume'].' ('.$_SESSION['email'].')';
					$text_mail = 'Mesaj:<br> '.$mesaj;
				}
				$rez = trimite_mail($adresa_email_administrator,'Mesaje pentru Teatrul de opera',$subiect, $text_mail); //mailul catre Teatrul de opera cu mesajul de lautilizator
				$conf = trimite_mail($email_user, $nume_user, 'Mesajul dvs catre Teatrul de opera a fost trimis', 'Mesajul dvs.: "'.$mesaj.'" a fost trimis. Va multumim!<br>Echipa Teatrul de opera');
				if ($rez==0) {
					$flag_mail_trimis=1;  
				} else {
					$erori = "Eroare in transmiterea mesajului. Va rugam reveniti.";
				}
			}
		} else {$erori = 'Trebuie sa completati toate campurile'.($logat ? '!' : ' si sa bifati ca nu sinteti robot!');}
	}
	
log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 

 require('fragmente/head.php'); 
 require('fragmente/nav.php'); 
// foreach ($_SESSION as $x => $y) { echo $x.": ". $y ." <br>"; }
?>
<main> 

<div id="login-c" class="form_centrat">
<script type="text/javascript">
function countChars(countfrom,displayto) {
  var len = document.getElementById(countfrom).value.length;
  document.getElementById(displayto).innerHTML = len;
}
</script>
<?php

?>
<h4>Tansmitere mesaje</h4> 
<div id="form-mesaje" style="width:30%">
<form method="post" action="mesaje.php" id="mesaje" >
<table class="formular" style="width:100%;"> 
<?php if (!$logat) { ?>
	<tr> <td style="text-align: start"> Email: </td> </tr>
	</tr><td> <input type="text" name="email" style="width:75%;">  </td> </tr> 
	<tr> <td style="text-align: start"> Nume si prenume: </td></tr> 
	</tr><td> <input type="text" name="nume_prenume" style="width:75%;">  </td>  </tr>  
	<tr> <td >  <b> Nume si prenume</b>: 7-50 litere, spatiu sau - (minus). <br> <br></td> </tr> 
<?php } ?>
	<tr> <td style="text-align: start"> Mesaj (maxim 500 caractere) <span style="color: grey" id="n" >0</span><span style="color: grey">/500</span> </td> </tr>
	<tr><td> <textarea name="mesaj" id="msj" style="width:100%;" rows="7" onkeyup="countChars('msj','n');" onkeydown="countChars('msj','n');";></textarea> </td> </tr> 
	<tr> <td colspan="2" align="center">
		<input type="submit"  value="Trimite mesaj" align="center" >  <br><br>
<?php if (!$logat) { ?>		
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<div class="g-recaptcha" data-sitekey="6LdjwaQqAAAAAD7U9IvUlTWIIIgx5AKsJQFEH5RY"></div>
<?php } ?>		
	</td> </tr> 
</table>
</form>
</div>
</div>

<div class="form_centrat" style="color: red">  <p> <?php echo $erori ; ?></p> </div> 

<div class="form_centrat">  <p><b> <?php if ($flag_mail_trimis) {echo 'Mesajul a fost trimis';} ?></b></p> </div> 
</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>