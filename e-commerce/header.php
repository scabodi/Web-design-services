<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Sara Cabodi">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="alimenti">
    <meta name="keywords" content="supermercato">
    <meta name="keywords" content="e-commerce">
    <link rel="stylesheet" type="text/css" href="layout.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon">
    <?php
        $url = $_SERVER['PHP_SELF'];
        $url = substr($url,strripos($url,"/")+1, -4);
        echo "<title>".ucfirst($url)." - Sara's e-commerce</title>";
    ?>
</head>
<body>
<div id="wrapper">
<div id="header">
    <h1>Sara's e-commerce <img src="logo.png" alt="logo_sito" id="logo" width="40" height="40"></h1>
</div>
<?php include('info_utente.php');?>

