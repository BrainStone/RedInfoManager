<?php
/**
  * Das hier ist die Haupdatei des RedInfoManager's
  * 
  * Author: BrainStone    
  * Version:
  *   v0.1.3  
  */
// Code
session_start();
register_shutdown_function("display");
$time = $_SERVER["REQUEST_TIME"];
$title = "";
$output = "";
$ftp = null;
session_handler();
check_connection();
switch($_SESSION["state"])
{
  case 0:
    login_page();
    break;
  case 1:
    ;
    break;
}
// Funktionen
function session_handler()
{
  global $time;
  
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
  global $title, $output, $ftp;
  
  if(($ftp = @ftp_connect("faldoria.com", 2121)) === false)
  {  
    // Session beenden
    // 78630rsw
    $_SESSION = array();
    if (ini_get("session.use_cookies"))
    {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params["path"],
          $params["domain"], $params["secure"], $params["httponly"]
      );
    }
    session_destroy();
    
    $title .= "FTP-Verbindungsfehler";
    $output .= "<h1>Keine Verbindung mit dem FTP-Server möglich!</h1>";
    
    exit();
  }
  
  @ftp_close($ftp);
}
function login_page()
{
  global $output;
  
  if(isset($_POST["action"]) && isset($_POST["username"]) && isset($_POST["password"]) && ($_POST["action"] == "login"))
  {
    $ftp = @ftp_connect("faldoria.com", 2121);
    
    if(@ftp_login($ftp, $_POST["username"], $_POST["password"]))
    {
      $output .= "<h2>Anmeldung erfolgreich!</h2>";
      $_SESSION["state"] = 1;
      
      return;
    }
    
    $output .= "<h2>Anmeldung fehlgeschlagen!</h2>\n";
  }
  
  $output .=
"<form method=\"POST\">
Benutzername: <input type=\"text\" name=\"username\"><br>
Password: <input type=\"password\" name=\"password\"><br>
<input type=\"submit\" value=\"Anmelden\">
<input type=\"hidden\" name=\"action\" value=\"login\">
</form>";
}
function display()
{
  global $title, $output, $ftp;
  
?>
<!DOCTYPE HTML>
<html>  
  <head>    
    <meta name="author" content="BrainStone">    
    <meta name="publisher" content="RobertLP">    
    <meta name="copyright" content="BrainStone">    
    <meta http-equiv="content-language" content="de">    
    <meta name="robots" content="noindex, nofollow">    
    <title>RedInfoManager
<?php
  echo ($title != "") ? (" - $title") : "";
  
      ?>
    </title>  
  </head>  
  <body>
<?php
  //echo ($output == "") ? "" : $output;
  echo $output;
  
    ?>  
  </body>
</html>
<?php
  
  @ftp_close($ftp);
}
?>