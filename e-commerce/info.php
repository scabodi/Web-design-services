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
    $_SESSION['precedente'] = $_SERVER['PHP_SELF'];
}
include('header.php');
echo "<div id='h2'><h2>Benvenuto nell pagina delle INFO</h2></div>";
include ('menu.php');
echo "<div id='contenuto'>";

$con = mysqli_connect("localhost", "uNormal", "posso_solo_leggere", "magazzino");
if (mysqli_connect_errno()) {
    printf("<p>errore - collegamento al DB IMPOSSIBILE: %s</p>\n", mysqli_connect_error());
} else {

    mysqli_set_charset($con,"utf8");

    if($session && isset($_SESSION['user']) ){ //se utente è loggato
        $user = $_SESSION['user'];
        // echo $user;

        $query = "SELECT nick,soldi FROM utenti WHERE nick=?";
        // echo "La query fatta è : " . $query . "<br>";

        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $user);

        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);


        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $user, $bors);
            mysqli_stmt_fetch($stmt);
            //inserisco messaggio di benvenuto
            printf("<p>Benvenuto <span class='grassetto'>%s</span> !! Hai <span class='grassetto'>%3.2f</span> euro disponibili nel tuo borsellino!</p>", $user, ($bors / 100));
        }
        mysqli_stmt_close($stmt);
    }
    //in ogni caso interrogo il DB per l'elenco dei prodotti e li metto in una tabella
    $query = "SELECT nome,prezzo,qty FROM prodotti";
    $result = mysqli_query($con, $query);

    if (!$result) {
        printf("<p class='err'>Errore - query fallita: %s</p>\n", mysqli_error($con));
    } else {

        $nrow = mysqli_num_rows($result);

        //printf("<p>Ci sono %d prodotti</p>", $nrow);
        if($session && isset($_SESSION['user'])){
            echo "<table>
            <tr>
                <th>PRODOTTO</th>
                <th>QUANTITA</th>
                <th>COSTO UNITARIO</th>
            </tr>\n";

            for ($i = 1; $i <= $nrow; $i++) {

                $row = mysqli_fetch_assoc($result);
                // espongo tutti i prodotti anche quelli con qta uguale a zero
                $prezzo = $row["prezzo"] / 100;
                printf("<tr><td>%s</td><td>%d</td><td>%3.2f</td></tr>", htmlentities($row["nome"]), $row["qty"], $prezzo);

            }
            echo "</table>\n";

            echo "<p class='solo_screen'>Per acquistare i prodotti <a href=\"acquista.php\">vai alla pagina Acquista</a></p>";

        }else{
            //se l'utente non è loggato mostro solo i prodotti
            echo "<p>PRODOTTI : </p><ul>";
            for ($i = 1; $i <= $nrow; $i++) {
                $row = mysqli_fetch_assoc($result);
               // if($row["qty"] > 0){
                printf("<li>%s</li>", htmlentities($row["nome"]));
               //}
            }
            echo "</ul>\n";
        }
        echo "</div>";

        mysqli_free_result($result);
    }
    mysqli_close($con);
}
include('footer.php');
