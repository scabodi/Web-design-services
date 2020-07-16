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
$errore = "";
?>
<?php include ('header.php');?>
<div id="h2"><h2> Benvenuto nella pagina di LOGIN</h2></div>
<?php include ('menu.php'); ?>
<div id="contenuto">
<script type="text/javascript">
    var submit;
    function set_submit(value){
        submit = value;
    }
    function verifica(user, pass) {

        //window.alert(submit);
        if(submit === 'OK') {
            var rname = /^[a-zA-Z|$]{1}[a-zA-Z0-9$]{2,7}/;
            var min = /[a-zA-Z]+/;
            var max = /[0-9]+/;
            var rpwd = /^[0-9]{4}/;

            //controlli sul NICKNAME
            if (!user) {
                window.alert("Inserire un nickname!");
                return false;
            } else if (!rname.test(user)) {
                window.alert("Il nickname deve iniziare con un carattere alfabetico o '$' ed essere lungo almeno 3 caratteri!");
                return false;
            } else if (!min.test(user)) {
                window.alert("Il nickname deve contenere almeno un carattere alfabetico!");
                return false;
            } else if (!max.test(user)) {
                window.alert("Il nickname deve contenere almeno un carattere numerico!");
                return false;
            }
            //controlli sulle PASSWORD
            if (!pass) {
                window.alert("Inserire una password!");
                return false;
            } else if (!rpwd.test(pass)) {
                window.alert("La password deve contenere solo caratteri numerici ed essere lunga 4 caratteri!");
                return false;
            }
        }else if(submit === 'PULISCI'){
            document.getElementById('name').value = '';
            document.getElementById('pwd').value = '';
        }
    }
</script>

<p>Qui puoi inserire le tue credenziali:</p>
<form id="f" method="post" onsubmit="return verifica(name.value, pwd.value);"
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?> ">
    <p><label>Nickname:
            <input type="text" id="name" name="name"
                <?php
                if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btnPulisci"])){
                    echo " value=''";
                }
                elseif (isset($_COOKIE['nickname']) && $_COOKIE['nickname']!==null) {
                    echo " value=".$_COOKIE['nickname'];
                }
                ?> ></label>
    </p>
    <p><label>Password:
            <input type="password" id="pwd" name="pwd" maxlength="4"></label>
    </p>
    <p><input type="submit" name="btnOk" id="btnOk" value="OK" onclick="set_submit(this.value)">
        <input type="submit" name="btnPulisci" id="btnPulisci" value="PULISCI" onclick="set_submit(this.value)"><br></p>
</form>
<noscript>
    <p><span class="grassetto"> NOTA</span>: javascript è disabilitato o non supportato.</p>
</noscript>

<?php if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btnOk"])){

    //controlli sull'input ---> devono essere presenti sia user che password
    if (empty($_POST['name'])) {
        //echo "user ".$_POST['name'];
        $errore = "Inserire un Nickname";
    }elseif (empty($_POST['pwd'])) {
        //echo " pass ".$_POST['pwd'];
        $errore = "Inserire una password";
    } else {

        $user = trim($_POST['name']);
        $pass = trim($_POST['pwd']);

        //controlli di scrittura di user e pwd
        $rname = "/^[a-zA-Z|$]{1}[a-zA-Z0-9$]{2,7}/";
        $rpwd = "/^[0-9]{4}/";

        if (!preg_match($rname, $user)) {
            $errore = "Nickname non corretto! Deve iniziare con un carattere alfabetico o '$' ed essere lungo almeno 3 caratteri!";
        } elseif (!preg_match($rpwd, $pass)) {
            $errore = " Password non corretta! Deve contenere SOLO caratteri numerici ed essere lunga 4 caratteri!";
        } else {
            $con = mysqli_connect("localhost", "uNormal", "posso_solo_leggere", "magazzino");
            if (mysqli_connect_errno()) {
                printf("<p class='err'>errore - collegamento al DB IMPOSSIBILE: %s</p>\n", mysqli_connect_error());
            } else {

                $query = "SELECT nick,pass,soldi FROM utenti WHERE nick=? AND pass=?";
                // echo "La query fatta è : ".$query."<br>";

                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ss", $user, $pass);

                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {

                    mysqli_stmt_bind_result($stmt, $user, $pass, $bors);
                    mysqli_stmt_fetch($stmt);
                    // printf("<p>Il risultao della query è : %s, %s, %3.2f euro</p>", $user,$pass,($bors/100));
                    $_SESSION['user'] = $user;
                    $_SESSION['soldi'] = $bors / 100;
                    $_SESSION['logged'] = true;
                    //setto la scadenza e il relativo cookie cioè 48 ore dopo il tempo attuale
                    $scadenza = time() + (86400 * 2);
                    setcookie("nickname", $user, $scadenza);
                    //chiudo le connessioni
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    //redirect alla pagina dell'utente --> info.php
                    header("location:info.php");

                } else {
                    $errore = "Nickname o password non validi";
                    // echo "<p class='error'>Nickname o password non validi!</p>";
                }
            }
        }
    }
}
include ('errore.php');
echo '</div>';
include ('footer.php');
?>
