<?php
/**
  * Das hier ist die Haupdatei des RedInfoManager's
  * 
  * Author: BrainStone    
  * Version:
  *   v0.8.17
  */

// Header

if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on"))
{ 
  $httpsurl = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . (($_SERVER["QUERY_STRING"] == "") ? "" : ("?" . $_SERVER["QUERY_STRING"])); 

  header("Location: " . $httpsurl); 
}

header("Content-Type: text/html;charset=utf-8");

// Code

session_start();

$time = $_SERVER["REQUEST_TIME"];
$title = "";
$output = "";
$data = array();
$kategorien = array();
$stationsstatus = array();
$welten = array();
$infostatus = array();
$notifications = array();
$ftp = null;
$mysqli = null;

session_handler();
check_connection();

if(isset($_POST["ajax"]) && ($_POST["ajax"] == "true"))
{
  if(isset($_POST["action"]) && ($_POST["action"] == "updateDB"))
  {
    if(isset($_POST["row"]) && $_POST["row"] >= 0)
    {
      unset($_POST["ajax"]);
      unset($_POST["action"]);
      unset($_POST["row"]);
      
      connect_to_database();     
      set_defaults();
      
      if($mysqli->query("UPDATE `redinfomanager` SET " . generate_query() . " WHERE `Station-ID` = ". $_POST["Station-ID"]))
      {
        die("true");
      }
      else
      {
        die("Error in query: (" . $mysqli->sqlstate . ") " . $mysqli->error);
      }
    }
    else
    {
      die("FAIL");
    }
  }
}
else
{
  register_shutdown_function("display");

  switch($_SESSION["state"])
  {
    case 0:
      login_page();
      break;
    case 1:
      display_data();
      break;
  }
}

// Funktionen

function session_handler()
{
  global $time, $output, $notifications;
  
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
      $notifications[] = "Die Sitzung ist abgelaufen!";
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
    
    $title = "FTP-Verbindungsfehler";
    $output .= "<h1>Keine Verbindung mit dem FTP-Server möglich!</h1>";
    
    exit();
  }
}

function login_page()
{
  global $output, $ftp, $notifications, $title;
  
  $title = "Login";
  
  if(isset($_POST["action"]) && isset($_POST["username"]) && isset($_POST["password"]) && ($_POST["action"] == "login"))
  {
    if(@ftp_login($ftp, $_POST["username"], $_POST["password"]))
    {
      $notifications[] = "Anmeldung erfolgreich!";
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
  global $output, $title, $data, $mysqli, $kategorien, $stationsstatus, $infostatus, $welten;
  
  $title = "Admin-Seite";
  
  if(isset($_POST["action"]) && ($_POST["action"] == "logout"))
  {
    destroy_session();
    
    login_page();
    
    return;
  }
  
  $output .=
"<form class=\"search\">
Station-ID: <input id=\"search1\" class=\"search\" name=\"Station-ID\">&nbsp;&nbsp;&nbsp;&nbsp;
Station: <input id=\"search2\" class=\"search\" name=\"Station\">&nbsp;&nbsp;&nbsp;&nbsp;
Kategorie: <input id=\"search3\" class=\"search\" name=\"Kategorie\">&nbsp;&nbsp;&nbsp;&nbsp;
Unterkategorie: <input id=\"search4\" class=\"search\" name=\"Unterkategorie\">&nbsp;&nbsp;&nbsp;&nbsp;
Button-Position: <input id=\"search5\" class=\"search\" name=\"Button-Position\">
</form>
<form method=\"POST\" class=\"logout\" style=\"right: 5px;\">
<input type=\"submit\" value=\"Abmelden\">
<input type=\"hidden\" name=\"action\" value=\"logout\">
</form>
<br>\n";
  
  connect_to_database();
  
  $output .= printTable($mysqli->query("SELECT `Station-ID` AS `ID`, `Station`, CONCAT(`Kategorie`, ' (', `Unterkategorie`, ')') AS `Kategorie`, CONCAT(`Position-Welt`, ': ', `Position-X`, ', ', `Position-Y`, ', ', `Position-Z`) AS `Position`, `Artikel`, `Stations-Status`, `Info-Status`, CONCAT(`Position-Welt`, ': ', `Warp-X`, ', ', `Warp-Y`, ', ', `Warp-Z`) AS `Warp`, `Quelle`, `Erbauer`, `Datei` FROM `redinfomanager` ORDER BY `Station-ID`"), true);
  
  $result = $mysqli->query("SELECT * FROM `redinfomanager`");
  
  while($r = $result->fetch_assoc())
  {
    $data[] = $r;
  }
  
  $result->free();
  
  $result = $mysqli->query("SELECT `Kategorie`, GROUP_CONCAT(`Unterkategorie` SEPARATOR '\r\n') AS `Unterkategorien` FROM `kategorien` GROUP BY `Kategorie` ORDER BY `Kategorie`, `Unterkategorie`");
  
  while($r = $result->fetch_assoc())
  {
    $kategorien[$r["Kategorie"]] = explode("\r\n", $r["Unterkategorien"]);
  }
  
  $result->free();
  
  $result = $mysqli->query("SELECT * FROM `stations-status`");
  
  while($r = $result->fetch_assoc())
  {
    $stationsstatus[] = $r["Status"];
  }
  
  $result->free();
  
  $result = $mysqli->query("SELECT * FROM `info-status`");
  
  while($r = $result->fetch_assoc())
  {
    $infostatus[] = $r["Status"];
  }
  
  $result->free();
  
  $result = $mysqli->query("SELECT * FROM `welten`");
  
  while($r = $result->fetch_assoc())
  {
    $welten[] = $r["Welt"];
  }
  
  $result->free();
  $mysqli->close();
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
  
  $i = 0;
  
  while($row = $result->fetch_assoc())
  {
    $output .= "<tr id=\"#$i\">";
    $j = 0;
    
    foreach($row as $field => $value)
    {     
      $output .= "<td id=\"#$i#$j\">$value</td>";
      
      $j++;
    }
    
    $output .= "</tr>\n";
    
    $i++;
  }
  
  $output .= "</table>\n";
  
  $result->free();
  
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
  
  $tmp = explode("\r\n", wordwrap($string, $length - 3, "\r\n", true));
  
  return $tmp[0] . "...";
}

function set_defaults()
{
  if(!isset($_POST["Station-ID"]))
    $_POST["Station-ID"] = 0;
  if(!isset($_POST["Station"]))
    $_POST["Station"] = "";
  if(!isset($_POST["Kategorie"]))
    $_POST["Kategorie"] = "Modelle";   
  if(!isset($_POST["Unterkategorie"]))
    $_POST["Unterkategorie"] = "Block";
  if(!isset($_POST["Position-Welt"]))
    $_POST["Position-Welt"] = "RedstoneWorld";
  if(!isset($_POST["Position-X"]))
    $_POST["Position-X"] = 0;
  if(!isset($_POST["Position-Y"]))
    $_POST["Position-Y"] = 0;
  if(!isset($_POST["Position-Z"]))
    $_POST["Position-Z"] = 0;
  if(!isset($_POST["Artikel"]))
    $_POST["Artikel"] = "";
  if(!isset($_POST["Stations-Status"]))
    $_POST["Stations-Status"] = "Geplant";
  if(!isset($_POST["Info-Status"]))
    $_POST["Info-Status"] = "unvollständig";
  if(!isset($_POST["Warp-X"]))
    $_POST["Warp-X"] = 0;
  if(!isset($_POST["Warp-Y"]))
    $_POST["Warp-Y"] = 0;
  if(!isset($_POST["Warp-Z"]))
    $_POST["Warp-Z"] = 0;
  if(!isset($_POST["Quelle"]))
    $_POST["Quelle"] = "";
  if(!isset($_POST["Erbauer"]))
    $_POST["Erbauer"] = "";
  if(!isset($_POST["Datei"]))
    $_POST["Datei"] = "";
}

function generate_query()
{
  global $mysqli;
  
  $table = array();
  
  foreach($_POST as $field => $text)
  {
    if(preg_match("/^-?[0-9]+$/", $text))
    {
      $table[] = "`$field`=" . $mysqli->real_escape_string($text);
    }
    else
    {
      $table[] = "`$field`='" . $mysqli->real_escape_string($text) . "'";
    }
  }
  
  return implode(", ", $table);
}

function connect_to_database()
{
  global $mysqli;
  
  $mysqli = new mysqli("redstoneworld.de", "rober_root", "fm/)X2urY=cB-N6*G.yb", "RedInfoManager", 3306);
  if($mysqli->connect_errno)
  {
    die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
  }
  
  $mysqli->set_charset("utf8");
  $mysqli->query("SET NAMES utf8");
}

function destroy_session()
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
}

function utf8_encode_array(array $array)
{
  $convertedArray = array();
  
  foreach($array as $key => $value)
  {
    if(!mb_check_encoding($key, 'UTF-8'))
    {
      $key = utf8_encode($key);
    }
    
    if(is_array($value))
    {
      $value = utf8_encode_array($value);
    }
    else
    {
      if(!mb_check_encoding($value, 'UTF-8'))
      {
        $value = utf8_encode($value);
      }
    }

    $convertedArray[$key] = $value;
  }
  
  return $convertedArray;
} 

function display()
{
  global $title, $output, $ftp, $notifications, $data, $kategorien, $stationsstatus, $infostatus, $welten;
  
?>
<!DOCTYPE HTML>
<html>  
  <head>    
    <meta name="author" content="BrainStone">    
    <meta name="publisher" content="RobertLP">    
    <meta name="copyright" content="BrainStone">    
    <meta http-equiv="content-language" content="de">    
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="cache-control" content="no-cache">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.png">
    <script language="JavaScript" src="../core/js/jquery.js"></script>
    <script language="JavaScript" src="script.js"></script>
    <script>
     var rawdata = <?php echo json_encode(utf8_encode_array($data)); ?>;
     var sessiontimeout = <?php echo ((isset($_SESSION["timeout"]) ? $_SESSION["timeout"] : -1) * 1000); ?>;
     var categories = <?php echo json_encode(utf8_encode_array($kategorien)); ?>;
     var stationstatus = <?php echo json_encode(utf8_encode_array($stationsstatus)); ?>;
     var infostatus = <?php echo json_encode(utf8_encode_array($infostatus)); ?>;
     var worlds = <?php echo json_encode(utf8_encode_array($welten)); ?>;
    </script>    
    <title>RedInfoManager<?php

  echo ($title != "") ? (" - $title") : "";
  
?>
</title>  
  </head>  
  <body>
<?php

  if(count($notifications))
  {
    echo "<div id=\"meldung\">";
    
    foreach($notifications as $text)
    {
      echo "<p>$text</p>";
    }
    
    echo "</div>\n";
  }
    
  echo $output;
  
?>

  </body>
</html>
<?php
  
  @ftp_close($ftp);
}
?>