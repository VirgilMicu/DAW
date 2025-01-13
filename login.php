<?php 
session_start();
 require('../conexiune.php');
 require('../sneaky.php');
 require('fragmente/functii.php'); 

if (tip_utilizator()){
		header('Location: index.php');
		exit;
	}

$mesaj='';

if (isset($_POST['email']) && isset($_POST['parola']))  {
	if (!empty($_POST['email']) && !empty($_POST['parola']))  {
	$email = filter_var(htmlspecialchars ($_POST['email']), FILTER_VALIDATE_EMAIL);
	$parola = trim($_POST['parola']);
	
	//$query = "SELECT * FROM utilizatori WHERE email='".$email."';";
		if (!$db->connect_error) { 
			$rez = autentificare ($email, $parola, $db);
			if ($rez) { // data email si parola sint corecte (functia a returnat datele, si nu 0)
				$tip_utiliz = $rez['tip'];
					if ($tip_utiliz == 1 || $tip_utiliz == 9 ) { //daca userul este de tip 1 sau 9.
						$_SESSION['tip_utilizator'] = $rez['tip'];
						$_SESSION['nume'] = $rez['nume'];
						$_SESSION['prenume'] = $rez['prenume'];
						$_SESSION['email'] = $rez['email'];
						$_SESSION['id_user'] = $rez['id_user'];
						session_regenerate_id();
						$str = 'index.php';
						if (isset($_SESSION['redirect_after_login'])){
							$str = $_SESSION['redirect_after_login'];
							unset ($_SESSION['redirect_after_login']);
						}	
						log_logare ($db, $_SESSION['id_user'], $_SERVER['REMOTE_ADDR']);
						header('Location: '.$str);
						exit;
					}
			}else{ $mesaj='Autentificare esuata';} // autentificare esuata
		}else{ $mesaj='Eroare conexiune la baza de date.';}
	}
}

 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);

 require('fragmente/head.php'); 
 require('fragmente/nav.php'); ?>
<main> 

<div id="login-c" class="form_centrat">
<h4> Pagina de logare</h4> 
<table class="formular" > 
<form method="post" action="login.php" id="login">
	<tr> <td style="text-align: end"> Email: </td> <td> <input type="text" name="email" size="30">  </td> </tr> 
	<tr> <td style="text-align: end"> Parola: </td> <td> <input type="password" name="parola" size="30">  </td> </tr> 
	<tr> <td colspan="2" align="center"> </br> <input type="submit" value="Logare" align="center" >  </td> </tr> 
    <tr> <td colspan="2" align="center"> </br>  <a href="resetare_parola.php">Am uitat parola :(</a> </td> </tr>
</form>
</table>
</div>

<div class="form_centrat" style="color: red">  <p> <?php echo $mesaj; ?></p> </div> 

</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>
