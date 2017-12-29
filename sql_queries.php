<?
require "./mysql.php";
mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_select_db($database) or die(mysql_error());

$createAoMSQL ="CREATE TABLE `top_detail_aom` (
  `id` int(5) NOT NULL auto_increment,
  `row` text NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM
";

$createAoMXSQL ="CREATE TABLE `top_detail_aomx` (
  `id` int(5) NOT NULL auto_increment,
  `row` text NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM
";

mysql_query($createAoMSQL) or die(mysql_error());;

print "1) Creating AOM Table<br>";

mysql_query($createAoMXSQL) or die(mysql_error());;

print "2) Creating AOMX Table<br>";

for($int =1; $int < 1001; $int++) 
{
$data = "PlaceHolder";
$time = time();
mysql_query("INSERT INTO top_detail_aom (row, timestamp) VALUES ('$data', '$time')") or die(mysql_error());
mysql_query("INSERT INTO top_detail_aomx (row, timestamp) VALUES ('$data', '$time')") or die(mysql_error());
}

print "3) Creating Insert PlaceHolders, the script only does updates, its simpler that way.<BR>";

print "<h2>SCRIPT COMPLETE DELETE THIS FILE NOW!!!</h2>";
?>