<div id="info_utente">
    <?php
    if (isset($_SESSION['logged'])) { // se loggato
        printf("<p>Nickname: <span class='grassetto'>%s</span></p>",$_SESSION['user']);
        //echo "Nickname: ".$_SESSION['user'];
        printf("<p>Borsellino: <span class='grassetto'>%3.2f </span></p>",$_SESSION['soldi']);
    } else {
        printf("<p>Nickname: <span class='grassetto'>anonimo</span></p>");
        //echo "Nickname: ".$_SESSION['user'];
        printf("<p>Borsellino: <span class='grassetto'>0 </span> </p>");
    }
    ?>
</div>