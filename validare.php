<?php
session_start();
 require('../conexiune.php');
 require('../sneaky.php');
 require('fragmente/functii.php'); 

if (tip_utilizator() ){
		header('Location: index.php');
		exit;
	}


$erori ="";

if (isset($_COOKIE['tmp_inreg'])) {
	$praji = json_decode (decripteaza ($_COOKIE['tmp_inreg']), True);
	if (isset($_POST['token'])){
		if (!empty($_POST['token'])) { 
			$temp = htmlspecialchars (trim($_POST['token']));
			if ($temp == $praji['token']){ // --- token corect introdus 
					if (!$db->connect_error) { //--------------- inserarea in baza a utilizatorului
						
						$stmt = $db->prepare ("INSERT INTO utilizatori (nume, prenume, email, parola, data_inregistrare) VALUES (?, ?, ?, ?, now());");
						$pass = password_hash($praji['parola'], PASSWORD_DEFAULT);
						$stmt->bind_param("ssss", $praji['nume'], $praji['prenume'], $praji['email'], $pass);
						$stmt->execute();
						if ( cautare_email($praji['email'], $db) > 0 ) {  //	-- dupa INSERT verificam daca emailul apare in baza		
							setcookie('tmp_inreg', '', time() - 6000,"","",True, True );
							require('fragmente/head.php'); 
							require('fragmente/nav.php');
							echo "<div class='form_centrat'>  <p> Cont creat cu succes. Va puteti loga <a href='login.php'>aici</a> </p> </div> </main>";
							require('fragmente/footer.php'); 
							echo "</body> </html>";
							exit;
						} else { $mesaj='Contul nu a fost creat.';}
					} else{ $mesaj='Eroare conexiune la baza de date.';} 
				} else { // --- token gresit introdus
					$praji['incercari']++;   // ------- incrementam numarul de incercari facute cu tokenul
					$inc = $praji['incercari'];
					if ($inc > 2){ // ---------daca a introdus gresit de 3 ori tokenul
						setcookie('tmp_inreg', '', time() - 6000,"","",True, True );
						header('Location: inregistrare.php');
						exit;
						}
					$erori = "Cod gresit, incercari: ". $inc.". Codul este valabil 3 incercari."; 
                    setcookie('tmp_inreg', cripteaza(json_encode($praji)), $praji['expirare'],"","",True, True );
				}
			} else {$erori = "Daca e casuta goala, de ce apasati?";}//------------- nu este introdus numic in casuta de token
	} 
} else {header('Location: inregistrare.php'); 
		exit;}

log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); ?>
<main> 


<div id="validare" class="form_centrat">
<h4>Validare cont</h4> 
<p> Sunteti in curs de validare a contului creat. Introduceti mai jos codul de validare primit pe email. Codul este valabil 10 minute. </p>
<table class="formular" > 
<form method="post" action="validare.php" id="inregistrare" >
	<tr> <td style="text-align: end"> Cod: </td> <td> <input type="text" name="token" size="30">  </td> </tr> 
	<tr> <td colspan="2" align="center"> </br> <input type="submit" value="Validare" align="center" >  </td> </tr> 
	</form>
</table>
</div>

<div class="form_centrat" style="color: red">  <p> <?php echo $erori ; ?></p> </div> 

</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>
