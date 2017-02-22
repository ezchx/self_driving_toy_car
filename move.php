<?php

$username="";
$password="";
$database="";

mysql_connect("localhost",$username,$password);
@mysql_select_db($database) or die( "Unable to select database");
$query="SELECT direction FROM move WHERE ref = 0";
$result=mysql_query($query);
mysql_close();
$direction = mysql_result($result,0);
echo $direction . '~';

mysql_connect("localhost",$username,$password);
@mysql_select_db($database) or die( "Unable to select database");
$query="UPDATE move SET direction = 'P' WHERE ref = 0";
mysql_query($query);
mysql_close();


?>
