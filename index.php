<?php
/**
  * Das hier ist die Haupdatei des RedInfoManager's
  * 
  * Author: BrainStone    
  * Version:
  *   v0.2.0
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
    display_data();
    break;
}

// Funktionen

function session_handler()
{
  global $time, $output;
  
  if(!isset($_SESSION["lastaction"]))
  {
    $_SESSION["lastaction"] = 0;
  }
  
  if(!isset($_SESSION["timeout"]))
  {
    $_SESSION["timeout"] = 300;
  }
  
  if(!isset($_SESSION["state"]))
  {
    $_SESSION["state"] = 0;
  }
  
  if($time - $_SESSION["lastaction"] > $_SESSION["timeout"])
  {
    if($_SESSION["state"] != 0)
    {
      $output .= "<h2>Die Sitzung ist abgelaufen!</h2>\n";
    }
    
    $_SESSION["state"] = 0;
    $_SESSION["timeout"] = 300;
  }
  
  $_SESSION["lastaction"] = $time;
}

function check_connection()
{  
  global $title, $output, $ftp;
  
  if(($ftp = @ftp_connect("faldoria.com", 2121)) === false)
  {  
    destroy_session();
    
    $title .= "FTP-Verbindungsfehler";
    $output .= "<h1>Keine Verbindung mit dem FTP-Server möglich!</h1>";
    
    exit();
  }
}

function login_page()
{
  global $output, $ftp;
  
  if(isset($_POST["action"]) && isset($_POST["username"]) && isset($_POST["password"]) && ($_POST["action"] == "login"))
  {
    if(@ftp_login($ftp, $_POST["username"], $_POST["password"]))
    {
      $output .= "<h2>Anmeldung erfolgreich!</h2>";
      $_SESSION["state"] = 1;
      
      display_data();
      
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

function display_data()
{
  global $output;
  
  if(isset($_POST["action"]) && ($_POST["action"] == "logout"))
  {
    destroy_session();
    
    login_page();
    
    return;
  }
  
  $output .=
"<form method=\"POST\">
<input type=\"submit\" value=\"Abmelden\">
<input type=\"hidden\" name=\"action\" value=\"logout\">
</form>\n";

  $mysqli = new mysqli("localhost", "root", "", "redinfomanager");
  if($mysqli->connect_errno)
  {
    die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
  }
  
  $output .= printTable($mysqli->query("SELECT `Station-ID` AS `ID`, `Station`, CONCAT(`Kategorie`, ' (', `Unterkategorie`, ')') AS `Kategorie`, CONCAT(`Position-Welt`, ': ', `Position-X`, ', ', `Position-Y`, ', ', `Position-Z`) AS `Position`, `Artikel`, `Status`, CONCAT(`Position-Welt`, ': ', `Warp-X`, ', ', `Warp-Y`, ', ', `Warp-Z`) AS `Warp`, `Quelle`, `Erbauer`, `Info`, `Team-Info` FROM `redinfomanager` WHERE 1"), true);
}

function printTable($result, $return)
{
  $output = "<table>\n<tr>";
  
  $row = $result->fetch_assoc();
  
  foreach($row as $field => $x)
  {
    $output .= "<th>$field</th>";
  }
  
  $output .= "</tr>\n";
  
  $result->data_seek(0);
  
  while($row = $result->fetch_assoc())
  {
    $output .= "<tr>";
    
    foreach($row as $field => $value)
    {
      if(strpos($field, "Info") !== false)
      {
        $value = short_string($value, 50);
      }
      
      $output .= "<td>$value</td>";
    }
    
    $output .= "</tr>\n";
  }
  
  $output .= "</table>\n";
  
  if($return)
  {
    return $output;
  }
  
  echo $output; 
}

function short_string($string, $length)
{
  if(strlen($string) <= $length)
  {
    return $string;
  }
  
  $string = wordwrap($string, $length - 3, "\n", true);
  
  return substr($string, 0, strpos($string, "\n")) . "...";
}

function destroy_session()
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
    <link rel="stylesheet" type="text/css" href="style.css">
    <script language="JavaScript" src="script.js"></script>    
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