<?php 
session_start();
 require('../conexiune.php');
 require('../sneaky.php');
 require('../phpmailer/mail_cod.php');
 require('fragmente/functii.php'); 

if (!tip_utilizator()){
		header('Location: index.php');
		exit;
	}

$mesaj ='';
$info=array ('email'=>'', 'nume'=>'', 'prenume'=>'', 'parola'=>'','token'=>'');
$flag='';

if (isset($_POST['nume']))  {
		if (!empty($_POST['nume']) && !empty($_POST['prenume']))  {
		 
			$temp = validare_nume($_POST['nume'],3);//-------------format nume
			if (!$temp) {
				$mesaj .= "Numele contine caractere nepermise, sau nu are lungimea acceptata (3-50). ";
			} else { $info['nume'] = $temp;}

			$temp = validare_nume($_POST['prenume'],3);//-------------format prenume
			if (!$temp) {
				$mesaj .= "Prenumele contine caractere nepermise, sau nu are lungimea acceptata (3-50). ";
			} else { $info['prenume'] = $temp;}
			
			$stmt = $db->prepare ("UPDATE utilizatori SET nume=?, prenume=? WHERE email=?;");
			$stmt->bind_param("sss", $info['nume'], $info['prenume'], $_SESSION['email']);
			if ($stmt->execute()) {
				$mesaj = "Date actualizate cu succes";
				$_SESSION['nume'] = $info['nume'];
				$_SESSION['prenume'] = $info['prenume'];
			} else {$mesaj = "Ceva nu a mers bine";}

		}else {$mesaj = "Trebuie sa completati toate campurile ";}
} 
 log_accesare($db, $_SERVER['REQUEST_URI'], (isset($_SESSION['id_user'])?$_SESSION['id_user']:0), $_SERVER['REMOTE_ADDR']);
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); ?>
<main> 

<div id="login-c" class="form_centrat">

<h4>Datele contului</h4> 

<form method="post" action="date_cont.php" id="actualizare" >
<table class="formular" > 
	<tr> <td style="text-align: end"> Nume: </td> <td> <input type="text" name="nume" value="<?php echo $_SESSION['nume']; ?>"size="30">  </td>  </tr> 
	<tr> <td style="text-align: end"> Prenume: </td> <td> <input type="text" name="prenume" value="<?php echo $_SESSION['prenume']; ?>" size="30">  </td> </tr> 
	<tr> <td colspan="2" align="center"> <br> <b> Nume si prenume</b>: 3-50 litere, spatiu sau - (minus). </td> </tr> 
	<tr> <td colspan="2" align="center"> </br> 
		<input type="submit"  value="Actualizare" align="center" >  <br><br>
	</td> </tr> 
</table>
</form>
</div>

<div class="form_centrat" style="color: red">  <p> <?php echo $mesaj ; ?></p> </div> 

</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>