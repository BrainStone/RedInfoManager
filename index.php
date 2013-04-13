<?php
/**
  * Das hier ist die Haupdatei des RedInfoManager's
  * 
  * Author: BrainStone    
  * Version:
  *   v0.0.3  
  */

// Code
session_start();
register_shutdown_function("display");

$time = $_SERVER['REQUEST_TIME'];
$title = "";
$output = "";

if(!isset($_SESSION["lastlogin"])
{
  $_SESSION["lastlogin"] = $time;
  $_SESSION["state"] = 0;
}

if(!isset())
{
  $_SESSION["state"] = 0;
}

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