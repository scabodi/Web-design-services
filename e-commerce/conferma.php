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
    $user = $_SESSION['user'];
    $bors = $_SESSION['soldi'];
}
$errore = "";
if($_SERVER["REQUEST_METHOD"] == "POST") {

    $con = mysqli_connect("localhost", "uPower", "SuperPippo!!!", "magazzino");

    include('header.php');
    echo "<div id='h2'><h2>Benvenuto nella pagina di CONFERMA dell'acquisto dei prodotti</h2></div>";
    include ('menu.php');
    echo "<div id='contenuto'>";
    if (mysqli_connect_errno()) {
        printf("<p class='err'>errore - collegamento al DB IMPOSSIBILE: %s</p>\n", mysqli_connect_error());
    }else {
        if (isset($_POST["conferma"])) { //conferma della pag precedente

            mysqli_set_charset($con, "utf8");

            $query = "SELECT pid,nome,prezzo,qty FROM prodotti ";
            $result = mysqli_query($con, $query);

            if (!$result) {
                printf("<p class='err'>Errore - query fallita: %s</p>\n", mysqli_error($con));
            } else { // ottengo un risultato dalla query e posso costruire il mio form

                $nrow = mysqli_num_rows($result);
                $totale = 0;
                for ($i = 1; $i <= $nrow; $i++){
                    $totale += $_POST[$i];
                }
                if($totale > 0){
                    echo "<p>Riepilogo ORDINE:</p>\n";

                    echo "<table>
                        <tr>
                            <th>PRODOTTO</th>
                            <th>QUANTITA</th>
                            <th>COSTO UNITARIO</th>
                            <th>COSTO TOTALE PRODOTTO</th>
                        </tr>\n";

                    $tot = 0;
                    $qta_aggiornate = array();

                    for ($i = 1; $i <= $nrow; $i++) {
                        $row = mysqli_fetch_assoc($result);
                        //echo "row di qty = ".$row["qty"];
                        $qta = ($_POST["$i"]);
                        //echo "qta tabella ".$qta;
                        //echo "quantita del prodotto " . $i . " è " . $qta;
                        $prezzo = $row["prezzo"] / 100;
                        //se è stata selezionato un numero diverso da 0
                        if ($qta > 0) {
                            $prezzo2 = $prezzo * $qta;
                            $tot += $prezzo2;
                            $qta_agg = $row["qty"] - $qta;

                            printf("<tr><td>%s</td><td>%d</td><td>%3.2f</td><td>%5.2f</td></tr>\n",
                                htmlentities($row["nome"]), $qta, $prezzo, $prezzo2);
                        }else{
                            $qta_agg = $row["qty"];
                        }
                        $qta_aggiornate[$i] = $qta_agg;
                        // echo "qta agg = ".$qta_aggiornate[$i];

                    }

                    echo "</table>\n";
                    printf("<p>Il costo totale dell'ordine è : <b>%5.2f</b> euro</p>\n", $tot);
                    //$pag = htmlspecialchars($_SERVER["PHP_SELF"]);
                    echo "<form method='post' action='conferma.php'>";
                    for ($i = 1; $i <= $nrow; $i++) {

                        echo "<input type='hidden' name='$i' value='$qta_aggiornate[$i]'>";
                    }
                    echo "<input type='hidden' name='tot' value='$tot'>";
                    echo "<input type='hidden' name='nrow' value='$nrow'>";
                    echo "<p><input type='submit' name='annulla' value='ANNULLA'>   ";
                    if($bors>$tot) {
                        echo "<input type='submit' name='procedi' value='PROCEDI'></p>  ";
                    }else{
                        $errore = "Non ci sono abbastanza soldi nel borsellino per procedere. Cliccare ANNULLA!";
                        include ('errore.php');
                        //echo "Cliccare ANNULLA o <a href='ricarica.php'>andare alla pagina di RICARICA</a>";
                    }
                }else{
                    $errore = " Non sono stati selezionati prodotti da acquistare! 
                                       <a href='acquista.php'>Tornare alla pagina degli acquisti</a>";
                    include('errore.php');
                }
                mysqli_free_result($result);
            }

            echo "</div>";
            include('footer.php');
        }
        if (isset($_POST["annulla"])) {
            //rimandare alla pagina iniziale
            header("location:acquista.php");
        }
        if (isset($_POST["procedi"])) {
            //detrarre denaro dal borsellino dell'utente

            $query = "UPDATE utenti SET soldi=? WHERE nick=?";
            $stmt = mysqli_prepare($con,$query);

            $tot = $_POST["tot"];
            $aggiornato = $bors - $tot;
            $aggiornato *= 100;

            mysqli_stmt_bind_param($stmt, "is",$aggiornato,$user);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            //aggiornare le quantita dei vari prodotti
            for($i=1 ; $i<=$_POST["nrow"];$i++){

                $qta = $_POST["$i"];
                $query = "UPDATE prodotti SET qty=? WHERE pid=?";
                $stmt = mysqli_prepare($con,$query);
                mysqli_stmt_bind_param($stmt, "is",$qta,$i);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

            }
            //aggiorno le info di sessione sui soldi
            $_SESSION['soldi'] = ($aggiornato/100);

            //rimando alla prossima pagina cioè finale
            header("location:finale.php");
        }
    }
    mysqli_close($con);
}