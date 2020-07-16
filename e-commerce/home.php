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
include('header.php');
?>

<div id="h2"><h2>HOME</h2></div>
<?php
include ('menu.php');
?>
<div id="contenuto">
<p class="benvenuto">Benvenuto sul sito e-commerce di Sara!</p>
<p>In questo sito potrai <a href="new.php">creare</a>/<a href="login.php">accedere</a> alla tua pagina personale,
    <a href="acquista.php">acquistare</a> i prodotti alimentari non deperibili disponibili,
    <a href="ricarica.php">ricaricare</a> il credito del tuo o di di altri utenti o semplicemente <a href="info.php">consultare
    l'elenco dei prodotti</a> disponibili in magazzino.</p>
<p>Se accederai con il tuo utente, in alto a destra troverai le informazioni relative al tuo nickname ed al tuo borsellino.</p>
<p>Qui a sinistra puoi trovare tutti i link alle varie pagine. BUONA NAVIGAZIONE :)</p>

<?php
include ('errore.php');
?>
</div>
<?php
include ('footer.php');
?>



