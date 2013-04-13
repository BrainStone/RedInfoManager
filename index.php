<?php
/**
  * Das hier ist die Haupdatei des RedInfoManager's
  * 
  * Author: BrainStone    
  * Version:
  *   v0.0.4  
  */

// Code
session_start();
register_shutdown_function("display");

$time = $_SERVER['REQUEST_TIME'];
$title = "";
$output = "";

session_handler();

check_connection();

switch($_SESSION["state"])
{
  case 0:
    ;
    break;
  case 1:
    ;
    break;
  case 2:
    ;
    break;
}

display();

// Funktionen

function session_handler()
{
  if(!isset($_SESSION["lastaction"])
  {
    $_SESSION["lastaction"] = 0;
  }
  
  if(!isset($_SESSION["timeout"]))
  {
    $_SESSION["timeout"] = 300;
  }
  
  if($time - $_SESSION["lastaction"] > $_SESSION["timeout"])
  {
    $_SESSION["state"] = 0;
    $_SESSION["timeout"] = 300;
  }
  
  if(!isset($_SESSION["state"]))
  {
    $_SESSION["state"] = 0;
  }
  
  $_SESSION["lastaction"] = $time;
}

function check_connection()
{
  if(ftp_connect("") !== false)
  {
    session_destroy();
    
    $title += "FTP-Verbindungsfehler"
    $output += "<h1>Keine Verbindung mit dem FTP-Server möglich!</h1>"
    
    exit();
  }
}

function display()
{
?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>RedInfoManager
<?php

  echo ($title != "") ? (" - $title") : "";
  
?></title>
  </head>
  <body>
<?php

  echo $output;
  
?>
  </body>
</html>
<?php
}
?>