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
    // tutte le volte che cambio pagina la salvo in caso di logout
    //$_SESSION['precedente'] = $_SERVER['PHP_SELF'];
}
include ('header.php');
echo "<div id='h2'><h2>Benvenuto nella pagina di ACQUISTO dei prodotti</h2></div>";
include ('menu.php');
echo "<div id='contenuto'>";
$errore = "";
if($session && isset($_SESSION['user'])){ // se l'utente è loggato

    $user = $_SESSION['user'] ;

    echo "<p>Benvenuto <b>$user</b> nella pagina degli ACQUISTI!<br> Di seguito è possobile selezionare le quantità dei prodotti desiderata.</p>\n";
    echo "<form name='form_acquista' method='post' action='conferma.php'> ";
    //conneto al DB per ottenere i dati sui prodotti
    $con = mysqli_connect("localhost", "uNormal", "posso_solo_leggere", "magazzino");
    if (mysqli_connect_errno()) {
        printf("<p class='err'>errore - collegamento al DB IMPOSSIBILE: %s</p>\n", mysqli_connect_error());
    } else {
        mysqli_set_charset($con,"utf8");

        $query = "SELECT nome,prezzo,qty FROM prodotti";
        $result = mysqli_query($con, $query);

        if (!$result) {
            printf("<p class='err'>Errore - query fallita: %s</p>\n", mysqli_error($con));
        } else { // ottengo un risultato dalla query e posso costruire il mio form

            $nrow = mysqli_num_rows($result);

           // printf("<p>Sono disponibili %d prodotti : </p>", $nrow);

            echo "<table>
                    <tr>
                        <th>PRODOTTO</th>
                        <th>QUANTITA</th>
                        <th>COSTO UNITARIO</th>
                    </tr>\n";

            for ($i = 1; $i <= $nrow; $i++) {
                $row = mysqli_fetch_assoc($result);

                $prezzo = $row["prezzo"] / 100;
                printf("<tr><td>%s</td><td>%d</td><td>%3.2f</td>", htmlentities($row["nome"]), $row["qty"], $prezzo);

                if($row["qty"] > 0) {
                    echo "<td><select name='$i'>";
                    //ciclo per riempire il menu a tendina di modo da inserire da 0 alla qta disponibile
                    for ($j = 0; $j <= $row["qty"]; $j++) {

                        if ($j == 0) { // se è il primo metto automaticamente lo 0 come opzione iniziale
                            echo "<option selected value='$j'>" . $j . "</option> ";
                        } else {
                            echo "<option value='$j'>" . $j . "</option> ";
                        }
                    }
                    echo "</select></td></tr>";
                } else{
                    echo "<td><input type='hidden' name='$i' value='0'></td></tr>";
                }

            }
            echo "</table>";
            //elimino il risultato e chiudo la connessione
            mysqli_free_result($result);
            //chiudo il form
            echo "<p><input type='reset' value='AZZERA' >   ";
            echo"<input type='submit' name='conferma' value='PROCEDI'></p>";
            echo "</form>";
        }
        mysqli_close($con);
    }
}else{
    $errore = "la procedura di acquisto è accessibile solo agli utenti autenticati.";
}
include ('errore.php');
echo "</div>";
include('footer.php');
