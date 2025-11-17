<?php
session_start();
session_destroy();
// header("Location: signin.php");
header("Location: home.php");
?>