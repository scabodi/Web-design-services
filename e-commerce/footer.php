<div id="footer">
    <footer>
        <small>
            <?php
                $url = $_SERVER['PHP_SELF'];
                $url = substr($url,strripos($url,"/")+1, strlen($url));
                echo $url." &copy ";
                $tags = get_meta_tags("header.php");
                echo $tags['author'];
            ?>
        </small>
    </footer>
</div>
</div>
</body>
</html>
