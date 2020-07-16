<?php
$session = true;
if(session_status() === PHP_SESSION_DISABLED){
    $session = false;
}
elseif(session_status() !== PHP_SESSION_ACTIVE)
    session_start();
if($session){
    if (!isset($_SESSION['npag'])) {
        $_SESSION['npag'] = 1;
    } else {
        $_SESSION['npag']++;
    }
}
$user = $_SESSION['user'];

include ('header.php');
echo "<div id='h2'><h2>Benvenuto nella pagina FINALE </h2></div>";
include ('menu.php');
echo "<div id='contenuto'>";
echo "<p>Grazie <b>".$user."</b> !! Il tuo ordine è stato effettuato con successo 
            e sono stati detratti i soldi dal tuo borsellino.</p>\n ";
echo "<p><a href='home.php'>torna alla home</a> o <a href='acquista.php'>acquista ancora</a> </p>";
echo "</div>";
include ('footer.php');


