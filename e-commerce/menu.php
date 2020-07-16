<div id="navigation">
    <nav>
        <ul>
            <li><a href="home.php">HOME</a></li>
            <li><a href="new.php">NEW</a></li>
        <?php
            if(empty($_SESSION['user'])){
                echo "<li><a href='login.php'>LOGIN</a></li>";
            }else {
                echo "<li><a href='login.php' class='disabled' >LOGIN</a></li>";
            }
        ?>
            <li><a href="ricarica.php">RICARICA</a></li>
            <li><a href="info.php">INFO</a></li>
            <li><a href="acquista.php">ACQUISTA</a></li>

        <?php
            if(!empty($_SESSION['user'])){
                echo "<li><a href='logout.php'>LOGOUT</a></li>";
            }else{
                echo "<li><a href='logout.php' class='disabled' >LOGOUT</a></li>";
            }
        ?>
        </ul>
    </nav>
</div>