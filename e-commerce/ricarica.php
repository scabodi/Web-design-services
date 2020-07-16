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
echo "<div id='h2'><h2>Benvenuto nella pagina per le RICARICHE</h2></div>";
include ('menu.php');
echo "<div id='contenuto'>";
?>
<script type="text/javascript">
    function verifica(importo, dest){
        var rcifra1 = /^[1-9]{1,3}(\,|\.)?[0-9]{0,2}$/;
        var rcifra2 = /^[0]{1}(\,|\.){1}[0-9]{1,2}$/;

        if(!importo){
            window.alert("inserire un numero intero o decimale con al massimo due cifre dopo la virgola o punto!");
            return false;
        }
        else if (!rcifra1.test(importo) && !rcifra2.test(importo)){
            window.alert("inserire un numero per il borsellino (max centinaia) con o senza virgola o punto" +
                "e massimo due cifre dopo la virgola oppure non inserire nulla!");
            return false;
        }
        if(!dest){
            window.alert("selezionare un destinatario!");
            return false;
        }
    }
</script>
<?php
if($session && isset($_SESSION['user'])) {
    if (!empty($_SESSION['user'])) {//effettuo le operazioni solo se l'utente è loggato
        $user = $_SESSION['user'];
        //creo il collegamento con il DB che mi servirà per entrambe le operazioni
        $con = mysqli_connect("localhost", "uPower", "SuperPippo!!!", "magazzino");
        if (mysqli_connect_errno()) {
            printf("<p class='err'>errore - collegamento al DB IMPOSSIBILE: %s</p>\n", mysqli_connect_error());
        } else {//se la connessione va a buon fine procedo

            $pag = $_SERVER['PHP_SELF'];
            //creo il form in cui l'utente inserirà i dati
            echo "<form name='form_ricarica' method='post' onsubmit='return verifica(importo.value, scelta.value);' action='$pag'>";
            echo "Importo: <input type='text' name='importo' id='importo'><br>";
            echo "<p>Scegliere il destinatario della ricarica:</p>\n";
            //interrogo il DB per inserire tutti gli utenti nei bottoni radio
            $query = "SELECT userid,nick,soldi FROM utenti ";
            $result = mysqli_query($con, $query);
            if (!$result) {
                printf("<p class='err'>Errore - query fallita: %s</p>\n", mysqli_error($con));
            } else {

                //ciclo sul risultato e creo i pulsanti radio
                $nrow = mysqli_num_rows($result);

                for ($i = 1; $i <= $nrow; $i++) {
                    $row = mysqli_fetch_assoc($result);
                    $nick = $row["nick"];
                    echo "<input type='radio' name='scelta' value=' $i'>$nick<br>";

                }
                mysqli_free_result($result); // libero il risultato

                echo "<br><input type='submit' value='RICARICA'>";
            }
            if(isset($_SESSION['ricaricato']) && $_SESSION['ricaricato'] == true){
                // vuol dire che ho appena ricaricato e metto il messaggio di avvenuta ricarica
                echo "<p class='grassetto'>L'importo è stato correttamente inserito nel borsellino del destinatario!</p>\n";
                //rimetto a false il ricaricato
                $_SESSION['ricaricato'] = false;
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") { // ricevo i dati per la ricarica

                $rcifra1 = "/^[1-9]{1,3}(\,|\.)?[0-9]{0,2}$/";
                $rcifra2 = "/^[0]{1}(\,|\.){1}[0-9]{1,2}$/";
                $virgola = "/^[1-9]{1,3}(\,)?[0-9]{0,2}$/";

                if (empty($_POST["importo"])) {
                    $errore = "inserire un importo!";
                } elseif (empty($_POST["scelta"])) {
                    $errore = "selezionare un destinatario";
                } else{

                    //controllo sull'input
                    $importo = $_POST["importo"];
                    //echo "<p>IMPORTO : $importo</p>\n";
                    $destid = trim($_POST["scelta"]);
                    // echo "<p>Id destinatario: $destid</p>\n";

                    if (!preg_match($rcifra1, $importo) && !preg_match($rcifra2, $importo)) {
                        $errore = "inserire un numero intero o con la virgola con massimo due cifre decimali!";
                    } elseif (preg_match($rcifra1, $importo) || preg_match($rcifra2, $importo)) {
                        if(preg_match($virgola, $importo)){ // se c'è la virgola la sostituisco con il punto
                            $importo = str_replace(',','.', $importo);
                        }
                        //se corretto aggiungo i soldi sul borsellino dell'utente specificato
                        //input corretto posso procedere con l'aggiunta del denaro
                        $importo = (int)($importo * 100);
                        //echo "<p>IMPORTO : $importo</p>\n";
                        $vecchio = 0;

                        //prendo l'importo esistente
                        $query = "SELECT nick,soldi FROM utenti WHERE userid=?";

                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "i", $destid);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_store_result($stmt);

                        if (mysqli_stmt_num_rows($stmt) == 1) {
                            mysqli_stmt_bind_result($stmt,$nick, $vecchio);
                            mysqli_stmt_fetch($stmt);
                            // printf("<p>Il risultao della query è : %d </p>", $vecchio);
                        }
                        mysqli_stmt_close($stmt);

                        //sommo la qta da aggiungere
                        $importo += $vecchio;
                        //echo $importo;
                        if($importo > 65535){
                            $max = (65535-$vecchio)/100;
                            //importo troppo grande
                            $errore = " importo troppo grande! Inserirne al massimo ".$max." euro!";
                        }else{
                            //aggiorno il DB con il nuovo importo
                            $query = "UPDATE utenti SET soldi=? WHERE userid=?";
                            $stmt = mysqli_prepare($con, $query);
                            mysqli_stmt_bind_param($stmt, "ii", $importo, $destid);

                            if (mysqli_stmt_execute($stmt)) { //messaggio di successo
                                if($nick == $_SESSION['user']){
                                    $_SESSION['soldi'] = ($importo/100);
                                }
                                //setcookie('ricaricato',true);
                                $_SESSION['ricaricato'] = true;
                                mysqli_stmt_close($stmt);
                                header("location:ricarica.php");
                            } else {
                                $errore = "query non effettuata!!";
                            }
                        }

                    }
                }
            }

            mysqli_close($con);
        }
    }
}else{
  $errore =" è necessario effettuare il login per poter fare una ricarica!</p>";
}
echo "<noscript><p><span class=\"grassetto\"> NOTA</span>: javascript è disabilitato.</p></noscript>";
include ('errore.php');
echo "</div>";
include ('footer.php');
