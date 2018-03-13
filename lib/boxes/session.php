<?php
session_start();

if (!isset($_SESSION['valid']) || !isset($_SESSION['user']))
{
  header("Location: ../logout.php");
}
?>
