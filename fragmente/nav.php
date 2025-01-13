<nav>
<?php if (isset($_SESSION['tip_utilizator'])){
    $tip_pagini = $_SESSION['tip_utilizator'];
} else {$tip_pagini = 0;} ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" />
    <ul class="meniu">
        <li><a href="index.php">
                <div id="acasa">
                    <div id="ac"><span> Acasa</span></div>
                </div>
            </a>
            <ul>
                <li><a href="/calendar_spectacole.php">Calendar spectacole</a></li>
				<li><a href="/despre.php">Despre</a></li>
            </ul>
        </li>
        <!-- <li><a href="#despre">Despre</a>
            <ul>
                 <li><a href="/prezentare">Prezentare</a></li>
                 <li><a href="/prezentare#istoric">Istoric</a></li>
                 <li><a href="/galerie">Galerie</a></li>  
                 <li><a href="#jos">Contact</a></li>  
             </ul>
        </li> -->
        <?php 
        $meniuri = array(0=>"Vizitatori", 1=>"Contul meu", 9=>"Administrare");
        $meniu0 = array ("Contact"=>"mesaje.php", "Logare"=>"login.php","Creare cont"=>"inregistrare.php", "Resetare parola"=>"resetare_parola.php");
        $meniu1 = array ("Rezervarile mele"=>"rezervari.php","Datele contului"=>"date_cont.php", "Contact"=>"mesaje.php","Delogare"=>"logout.php");
		$meniu9 = array ("Productii"=>"productii.php","Spectacole"=>"spectacole.php", "Statistici"=>"statistici.php","Delogare"=>"logout.php");
        echo "<li><div>".$meniuri[$tip_pagini]."</div>
                <ul>\n";
                $meniu_selectat = "meniu".$tip_pagini;
                foreach ($$meniu_selectat as $x => $y) {
                    echo "                    <li><a href=\"/".$y."\">".$x."</a></li>\n";
                    }
                echo "                </ul>
        </li>"; 
		if ($tip_pagini == 0) {
			echo "<li id='dr'><div><a style ='all: unset; cursor:pointer;' href='login.php'> Logare</a>";}
		if ($tip_pagini == 1) {
			echo "<li id='dr'><div><span class='fa-solid fa-user'>  &nbsp </span>".$_SESSION['prenume']." ".$_SESSION['nume'];}
		if ($tip_pagini == 9) {
			echo "<li id='dr'><div style='color: orange'><span class='fa-solid fa-wrench' >  &nbsp </span>".$_SESSION['prenume']." ".$_SESSION['nume'];} ?>
           
        </div></li>
    </ul>

</nav>
