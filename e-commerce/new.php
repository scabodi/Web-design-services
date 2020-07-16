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
$errore = "";
include('header.php');?>
<div id="h2"><h2>Benvenuto nella pagina per creare un nuovo utente</h2></div>
<?php include ('menu.php');?>
<div id="contenuto" >
<div id="h3"><h3> Crea nuovo utente </h3></div>

<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name = trim($_POST["name"]);
    $pwd = trim($_POST["pwd"]);
    $pwd2 = trim($_POST["pwd2"]);
    $bors = trim($_POST["bors"]);

    $rname = "/^[a-zA-Z|$]{1}[a-zA-Z0-9$]{2,7}$/";
    $min = "/[a-zA-Z]+/";
    $max = "/[0-9]+/";
    $rpwd = "/^[0-9]{4}$/";
    $rcifra1 = "/^[1-9]{1,3}(\,|\.)?[0-9]{0,2}$/";
    $rcifra2 = "/^[0]{1}(\,|\.){1}[0-9]{1,2}$/";
    $virgola = "/^[1-9]{1,3}(\,)?[0-9]{0,2}$/";

    //controlli sul NICKNAME
    if(empty($name)){
        $errore = "Inserire un nickname!";
    }elseif (!preg_match($rname,$name)){
        $errore = "Il nickname deve iniziare con un carattere alfabetico o '$' ed essere lungo almeno 3 caratteri!";
    }elseif (!preg_match($min,$name)){
        $errore = "Il nickname deve contenere almeno un carattere alfabetico!";
    }elseif (!preg_match($max,$name)){
        $errore =  "Il nickname deve contenere almeno un carattere numerico!";
    }
    //controlli sulle PASSWORD
    if (empty($pwd)){
        $errore .= "Inserire una password!";
    }elseif (empty($pwd2)){
        $errore = "Inserire nuovamente la password!";
    }elseif (!preg_match($rpwd,$pwd)){
        $errore = " la password deve contenere solo caratteri numerici ed essere lunga 4 caratteri!";
    }elseif ($pwd!=$pwd2){
        $errore = "ripetizione della password non corretta!";
    }
    //controlli sul BORSELLINO
    if (!preg_match($rcifra1,$bors) && !preg_match($rcifra2,$bors ) && !empty($bors)){
        $errore = "inserire un numero per il borsellino (max centinaia) con o senza virgola o punto e massimo due cifre
         dopo la virgola oppure non inserire nulla!";
    }elseif ($bors>655.35){
        $errore = "inserire un numero minore di 655.35 euro da depositare nel borsellino";
    } else {
        //controlli se l'input è tutto corretto
        if (preg_match($rname, $name) && preg_match($min, $name) && preg_match($max, $name) &&
            preg_match($rpwd, $pwd) && preg_match($rpwd, $pwd2) && $pwd == $pwd2 ) { //input corretto
            if((preg_match($rcifra1, $bors) || preg_match($rcifra2, $bors)) && $bors<=655.35) {
                if(preg_match($virgola, $bors)){ // se c'è la virgola la sostituisco con il punto
                    $bors = str_replace(',','.', $bors);
                }//setto il borsellino
                $bors = (int)($bors * 100);
            }elseif (empty($bors)){
                $bors = 100;
            }
            //collegamento al DB e inserimento del nuovo utente
            $con = mysqli_connect("localhost", "uPower", "SuperPippo!!!", "magazzino");
            if (mysqli_connect_errno()) {
                printf("<p class='err'>errore - collegamento al DB IMPOSSIBILE: %s</p>\n", mysqli_connect_error());
            } else { //operazioni sul db
                $query = "SELECT userid FROM utenti WHERE nick=?";
                $stmt = mysqli_prepare($con,$query);
                mysqli_stmt_bind_param($stmt, "s",$name);

                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    //trovato un utente uguale!!
                    $errore = "Inserire un nuovo nickname perchè quello scelto è gia stato utilizzato!\n";
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                }
                else { // se è tutto corretto allora lo aggiungo e reindirizzo
                    mysqli_stmt_close($stmt);

                    $query = "INSERT INTO utenti ( nick, pass, soldi) VALUES (?,?,?)";
                    $stmt = mysqli_prepare($con, $query);
                    mysqli_stmt_bind_param($stmt, "ssi", $name, $pwd, $bors);
                    mysqli_stmt_execute($stmt);

                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    //header_remove();
                    header("location:utente_creato.php");
                }
            }
        }
    }
}
?>
<script type="text/javascript">
    function action() {
        document.getElementById('info_nick').style.visibility = 'visible';
        document.getElementById('info_pwd').style.visibility = 'visible';
        document.getElementById('info_bors').style.visibility = 'visible';
    }
    function verifica(user, pass, pass2, bors){
        var rname = /^[a-zA-Z|$]{1}[a-zA-Z0-9$]{2,7}$/;
        var min = /[a-zA-Z]+/;
        var max = /[0-9]+/;
        var rpwd = /^[0-9]{4}$/;
        var rcifra1 = /^[1-9]{1,3}(\,|\.)?[0-9]{0,2}$/;
        var rcifra2 = /^[0]{1}(\,|\.){1}[0-9]{1,2}$/;

        //controlli sul NICKNAME
        if(!user){
            window.alert("Inserire un nickname!");
            return false;
        }else if (!rname.test(user)){
            window.alert("Il nickname deve iniziare con un carattere alfabetico o '$' ed essere lungo almeno 3 caratteri!");
            return false;
        }else if (!min.test(user)){
            window.alert("Il nickname deve contenere almeno un carattere alfabetico!");
            //location.reload();
            return false;
        }else if (!max.test(user)){
            window.alert( "Il nickname deve contenere almeno un carattere numerico!");
            //location.reload();
            return false;
        }
        //controlli sulle PASSWORD
        if (!pass){
            window.alert(" Inserire una password!");
            return false;
        }else if (!pass2){
            window.alert("Inserire nuovamente la password!");
            return false;
        }else if (!rpwd.test(pass)){
            window.alert(" la password deve contenere solo caratteri numerici ed essere lunga 4 caratteri!");
            return false;
        }else if (pass!==pass2){
            window.alert("ripetizione della password non corretta!");
            return false;
        }
        //controlli sul BORSELLINO
        if (!rcifra1.test(bors) && !rcifra2.test(bors)){ // && bors
            window.alert("inserire un numero per il borsellino (max centinaia) con o senza virgola o punto" +
                "e massimo due cifre dopo la virgola oppure non inserire nulla!");
            return false;
        }if (bors>655.35){
            window.alert( "inserire un numero minore di 655.35 euro da depositare nel borsellino");
            return false;
        }
    }
</script>
<form id="myform" method="post"  onsubmit="return verifica(name.value, pwd.value, pwd2.value, bors.value); "
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" onload='action();'>
    <p><label>Nickname*: <input type="text" name="name" id="name" maxlength="8" required></label>
        <input id="info_nick" class="button" type="button" value="?" onclick="alert('Info NICKNAME:\n' +
     ' - deve iniziare con un carattere alfabetico o $ \n' +
     ' - deve contenere almeno un carattere alfabetico e uno numerico\n' +
     ' - deve essere lungo almeno 3 caratteri')"></p>
    <p><label>Password*: <input type="password" name="pwd" id="pwd" maxlength="4" required></label>
        <input id="info_pwd" class="button" type="button" value="?" onclick="alert('Info PASSWORD:\n' +
     ' - deve contenere solo caratteri numerici \n' +
     ' - deve essere lunga 4 caratteri')"></p>
    <p><label>Ripetere password*: <input type="password" name="pwd2" id="pwd2" maxlength="4" required></label></p>
    <p><label>Deposito Borsellino: <input type="text" name="bors" id="bors" maxlength="6"></label>
        <input id="info_bors" class="button" type="button" value="?" onclick="alert('Info BORSELLINO:\n' +
     ' - deve contenere solo un caratteri numerici, interi o decimali \n' +
     ' - deve essere un numero minore di 655.35 euro ')"></p>
    <p><small>(*): campi obbligatori</small></p>
    <p><input type="submit" value="Crea Nuovo utente" >
        <input type="reset" value="Cancella"></p>
</form>

<noscript>
    <style>.button{visibility: hidden;}</style>
    <p><span class="grassetto"> NOTA</span>: javascript è disabilitato.</p>
    <div class="commento_login">
        <p>Regole per la creazione di un nuovo utente: <br>
        <p class="grassetto">Nickname:</p>
        <ul>
            <li>deve iniziare con un carattere alfabetico o '$'</li>
            <li>deve contenere almeno un carattere alfabetico e uno numerico</li>
            <li>deve essere lungo almeno 3 caratteri</li>
        </ul>
        <p class="grassetto">Password:</p>
        <ul>
            <li>deve contenere solo caratteri numerici</li>
            <li>deve essere lunga 4 caratteri</li>
        </ul>
        <p class="grassetto">Borsellino: (opzionale)</p>
        <ul>
            <li>deve contenere solo un caratteri numerici, interi o decimali</li>
            <li>deve essere un numero minore di 655.35 euro </li>
        </ul>

    </div>
</noscript>
<?php include ('errore.php');
echo '</div>';
include ('footer.php');
?>

