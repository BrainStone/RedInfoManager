<?php
/**
  * Das hier ist die Haupdatei des RedInfoManager's
  * 
  * Author: BrainStone    
  * Version:
  *   v0.0.7  
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

// Funktionen

function session_handler()
{
  if(!isset($_SESSION["lastaction"]))
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
  global $title, $output;
  
  if(ftp_connect("56") === false)
  {
    // Session beenden
    $_SESSION = array();
    if (ini_get("session.use_cookies"))
    {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params["path"],
          $params["domain"], $params["secure"], $params["httponly"]
      );
    }
    session_destroy();
    
    $title += "FTP-Verbindungsfehler";
    $output += "<h1>Keine Verbindung mit dem FTP-Server möglich!</h1>";
    
    exit();
  }
  
  ftp_close();
}

function display()
{
  global $title, $output;
  
?>
<!DOCTYPE HTML>
<html>
  <head>
    <meta name="author" content="BrainStone">
    <meta name="publisher" content="RobertLP">
    <meta name="copyright" content="BrainStone">
    <meta http-equiv="content-language" content="de">
    <meta name="robots" content="noindex, nofollow">
    <title>RedInfoManager<?php

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
  
  ftp_close();
}
?>