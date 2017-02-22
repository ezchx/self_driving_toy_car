<?php

$username="";
$password="";
$database="";

$readings = $_GET["readings"];
$direction = $_GET["direction"];

if ($readings != "") {
  mysql_connect("localhost",$username,$password);
  @mysql_select_db($database) or die( "Unable to select database");
  $query = "SELECT MAX(ref) FROM sense";
  $result = mysql_query($query);
  $new_ref = mysql_result($result,0) + 1;
  
  $query = "INSERT INTO sense VALUES (
    '$new_ref',
    '$readings')";
  mysql_query($query);
  mysql_close();
}


if ($direction != "") {
  mysql_connect("localhost",$username,$password);
  @mysql_select_db($database) or die( "Unable to select database");

  $query = "UPDATE move SET direction = '$direction' WHERE ref = 0";
  mysql_query($query);
  mysql_close();
}




?>
