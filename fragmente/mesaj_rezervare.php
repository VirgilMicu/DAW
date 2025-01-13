
<?php 
 require('fragmente/head.php'); 
 require('fragmente/nav.php'); 
 ?>

<main>

<?php 

if ($_SESSION['mesaj_rezervare']=='0' ) { ?> 
	
<div class="form_centrat"> <p> Locurile NU au fost rezervate. Probabil o parte din locuri au fost rezervate intre timp de alti utilizatori. Apasati <a href="rezervare_locuri.php?id=<?php echo $_SESSION['id_spectacol']; ?>">aici<a> pentru a vedea locurile disponibile in acest moment.</p> </div> 

<?php  } elseif ($_SESSION['mesaj_rezervare']=='1') { ?>

<div class="form_centrat"> <p>Locurile au fost rezervate. Puteti vedea rezervarea in sectiunea <a href="rezervari.php">Rezervarile mele<a> </p> </div> 

<?php }

unset( $_SESSION['mesaj_rezervare']); ?>

</main> 
<?php require('fragmente/footer.php'); ?>
</body>
</html>
<?php exit; ?>