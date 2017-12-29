<?
/*
+==================================================================
| Fropco ESO
| A suite of ESO info parsed in PHP
| Supports both AoM and AoM:TT
|
| Made with the help of HTTPlook
| -http://www.httpsniffer.com/
| Developed for Age of Mythology Fropco
|
| Written by Kevin Kerr
| -http://fropco.com -
+==================================================================
*/

// $ver - version of aom (aom or aomx)
// $type - type of game (supremacy etc..
// $time - time rating ( all time, weekly...
// $do - the action thingy
// oldip: 207.46.203.186
// newip: 72.3.239.130

require "mysql.php";

mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_select_db($database);
	


$EsoEngine = new EsoEngine;

$player = $_GET['player'];
$ver = $_GET['ver'];
$time = $_GET['time'];
$type = $_GET['type'];
$do = $_GET['do']; //Instead act cuz I was already using act in my site
$gameid = $_GET['GameID'];
$number = $_GET['number']; 
$offset = $_GET['offset']; 
$boolcook = $_GET['boolcook']; 
$detailed = $_GET['detailed']; 

$en = $_GET['en']; 
$ForceSearch = $_GET['ForceSearch']; 
$cook = $_COOKIE['fropco_eso_time_offset'];

$type = $EsoEngine->UrlCleaner($type);
$time = $EsoEngine->UrlCleaner($time);

switch ($do) {
case "PlayerInfo":
 if($ForceSearch) { $Fs = true; }
 $EsoEngine->PlayerInfo($ver, $type, $time, $player, true, $Fs);
   break;
case "TopList":
  $EsoEngine->TopList($ver, $type, $time, $detailed, $en);
   break;
case "Match":
   $EsoEngine->GameInfo($ver, $type, $time, $player, $gameid);
   break;  
case "ShowTime":
   $EsoEngine->ShowTime($boolcook);
   break; 
    case "RecentGames":
  echo $EsoEngine->GameList($ver, $type, "");
    break; 
case "CronInclude":
// NOTHING!
   break;         
default:
   $EsoEngine->ServeInfo();
   break;
}

class EsoEngine {

var $page; 

//===========================================
// This function finds what modes a player
// has records in for each mode for AllTime.
// The script would run too slow if it had to
// do 15 queries instead of 5. So we dont 
// search monthly or weekly. This avoids 
// infinate search loops.
//===========================================
function PlayerModes($ver, $type, $time, $player, $inFunc=false) {



if($player)
{
echo $this->parse_tmpl("SumStatsHeader",array(
											"{player}"   =>$player,
				                            "{time}"   =>$this->GoEnglish($time),
											"{ver}"     => $this->GoEnglish($ver),
		
										)); 

}
else {
echo $this->parse_tmpl("SumStatsHeaderCom",array(
											"{player}"   => "Community",
											"{time}"   =>$this->GoEnglish($time),
											"{ver}"     => $this->GoEnglish($ver),

		
										)); 

}
									
$rownum =0;


for($int =1; $int < 6; $int++) {
if($int == 1) { $looptype = "ZS_Supremacy"; }
elseif($int == 2) { $looptype = "ZS_Conquest"; }
elseif($int == 3) { $looptype = "ZS_Deathmatch"; }
elseif($int == 4) { $looptype = "ZS_Lightning"; }
elseif($int == 5) { $looptype = "ZS_Custom"; } // remember custom gets no rating

$parser = xml_parser_create();

xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);



if($ver=="aomx"){
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qest%20id='0'%20si='0'%20en='$player'%20et='ZS_Human'%20md='$looptype'%20tp='$time'/></clr>";
}
else if($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qest%20id='0'%20si='0'%20en='$player'%20et='ZS_Human'%20md='$looptype'%20tp='$time'/></clr>";
}

$data = implode("",file("$query")); //Boom!

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or $this->PrintError("xml error!");
xml_parser_free($parser);
//print_r($d_ar);



if(!empty($d_ar['4']['attributes']['LastUpdated'])) //If they have an any info we will print the summary row.
{ 

$losses = $d_ar['4']['attributes']['ZS_Games'] - $d_ar['4']['attributes']['ZS_Wins'];







		 $stylebool = is_int($rownum/2);  
		 if($stylebool) {$class="row2"; 
		 } else { $class = "row1"; } 
		 
if($player)
{
$lstupd2 = $this->ParseTime($d_ar['4']['attributes']['LastUpdated']);
$lstupd2 = date("M.j.y - h:iA",$lstupd2);
echo $this->parse_tmpl("SumStats",array(
											"{rating}"   => $this->GoEnglish($d_ar['4']['attributes']['ZS_CombinedELORating']),
											"{wins}"     => $d_ar['4']['attributes']['ZS_Wins'],
											"{games}"    => $d_ar['4']['attributes']['ZS_Games'],
											"{losses}"   => $losses, 
											"{type}"     => $this->GoEnglish($looptype), 
											"{winpct}"   => $d_ar['4']['attributes']['ZS_WinningPct'],
											"{class}"   => $class,
											"{tGtime}"  => $this->GoTime($d_ar['4']['attributes']['ZS_TotalGametime']),
											"{avgGtime}"  => $this->GoTime($d_ar['4']['attributes']['ZS_AverageGametime']),
											"{avgscore}"  => $d_ar['4']['attributes']['ZS_AvgScore'],
											"{player}"  => $player,
											"{qtype}"  => $this->GoEnglish($looptype,true),
											"{ver}"  => $ver,
											"{rank}" => $this->GetRank($ver, $looptype, "ZS_AllTime", $player),
											"{lstgame}" => $lstupd2,
										"{Ctime}"   =>$this->GoEnglish($time,true),
								
											)); 
	}
	else {	
	
if($ver=="aomx") {
$d_ar['4']['attributes']['ZS_Games'] = $d_ar['4']['attributes']['ZS_TotalGames'];
}

									
echo $this->parse_tmpl("SumStatsCom",array(

											"{games}"    => $d_ar['4']['attributes']['ZS_Games'],
											"{type}"     => $this->GoEnglish($looptype), 
											"{class}"   => $class,
											"{tGtime}"  => $this->GoTime($d_ar['4']['attributes']['ZS_TotalGametime']),
											"{avgGtime}"  => $this->GoTime($d_ar['4']['attributes']['ZS_AverageGametime']),
											"{avgscore}"  => $d_ar['4']['attributes']['ZS_AvgScore'],
											"{player}"  => "TotalCommunityStats",
											"{qtype}"  => $this->GoEnglish($looptype,true),
											"{ver}"  => $ver,
												"{Ctime}"   =>$this->GoEnglish($time,true),
								
											)); 											
											
}
$rownum++;
}


}

if($type != "ZS_All") { 

echo $this->parse_tmpl("NoStatsEnd",array(
											"{player}"   =>$player,
											"{ver}"     => $this->GoEnglish($ver),
											"{type}"     => $this->GoEnglish($type),
											"{time}"     => $this->GoEnglish($time),

											)); 

}
else { echo $this->parse_tmpl("StatsEnd",array(
											"{type}"     => $this->GoEnglish($type),
											"{time}"     => $this->GoEnglish($time),

											));  } 
}

// PlayerInfo
// BIG function
function PlayerInfo ($ver, $type, $time, $player, $parse=true, $ForceSearch=false) {
if(!$player) {
$this->PrintError("Please Enter a player name");
}
else {
$qplayer = $player; 

if($player=="TotalCommunityStats")
{
$qplayer ="";
}


if ($ForceSearch==true)
{ $this->PlayerSearch($ver, $type, $time, $player, true); }
else {

$parser = xml_parser_create();

xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);



if($ver=="aomx") {
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qest%20id='0'%20si='0'%20en='$qplayer'%20et='ZS_Human'%20md='$type'%20tp='$time'/></clr>"; 
}

elseif($ver=="aom") {

$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qest%20id='0'%20si='0'%20en='$qplayer'%20et='ZS_Human'%20md='$type'%20tp='$time'/></clr>";
}





$data = implode("",file("$query")); //Boom!


xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or $this->PrintError("xml error!");
xml_parser_free($parser);

$mgames = $d_ar['4']['attributes']['ZS_Games']; 
$mwins = $d_ar['4']['attributes']['ZS_Wins'];
$mdcs = $d_ar['4']['attributes']['ZS_DC']; 
$mm = $mwins + $mdcs;
$losses = $mgames - $mm;


# print_r($d_ar);
# print_r($i_ar);

      $use = array (
	            "Zeus" => $d_ar['4']['attributes']['ZS_GWZeus'],
				"Poseidon" => $d_ar['4']['attributes']['ZS_GWPoseidon'],
				"Hades" => $d_ar['4']['attributes']['ZS_GWHades'],
				"Isis" => $d_ar['4']['attributes']['ZS_GWIsis'],
				"Ra"   => $d_ar['4']['attributes']['ZS_GWRa'],
				"Set"  => $d_ar['4']['attributes']['ZS_GWSet'],
				"Odin" => $d_ar['4']['attributes']['ZS_GWOdin'],
				"Thor" => $d_ar['4']['attributes']['ZS_GWThor'],
				"Loki" => $d_ar['4']['attributes']['ZS_GWLoki'],
				"Kronos" => $d_ar['4']['attributes']['ZS_GWKronos'],
				"Oranos" => $d_ar['4']['attributes']['ZS_GWOranos'],
				"Gaia" => $d_ar['4']['attributes']['ZS_GWGaia'],
);

      $wins = array (
	            "Zeus" => $d_ar['4']['attributes']['ZS_GWZeusTotalWins'],
				"Poseidon" => $d_ar['4']['attributes']['ZS_GWPoseidonTotalWins'],
				"Hades" => $d_ar['4']['attributes']['ZS_GWHadesTotalWins'],
				"Isis" => $d_ar['4']['attributes']['ZS_GWIsisTotalWins'],
				"Ra"   => $d_ar['4']['attributes']['ZS_GWRaTotalWins'],
				"Set"  => $d_ar['4']['attributes']['ZS_GWSetTotalWins'],
				"Odin" => $d_ar['4']['attributes']['ZS_GWOdinTotalWins'],
				"Thor" => $d_ar['4']['attributes']['ZS_GWThorTotalWins'],
				"Loki" => $d_ar['4']['attributes']['ZS_GWLokiTotalWins'],
				"Kronos" => $d_ar['4']['attributes']['ZS_GWKronosTotalWins'],
				"Oranos" => $d_ar['4']['attributes']['ZS_GWOranosTotalWins'],
				"Gaia" => $d_ar['4']['attributes']['ZS_GWGaiaTotalWins'],
); 
arsort($use);
$totalgr = array_sum($use);
list($FavGod, $gtimes) = each($use);
reset($use); //If we dont reset it then our loop will skip the first one
$stylec =0;
while(list($FGod, $gt) = each($use)) {

if($gt) { 

		 $styleboolc = is_int($stylec/2);  
		 if($styleboolc) {$gclass="row1"; 
		 } else { $gclass = "row2"; }
		 
if($ver=="aomx" && $player=="TotalCommunityStats")
{
$TotalGames = $d_ar['4']['attributes']['ZS_PlayersToAge1'];
}
else {
$TotalGames = $d_ar['4']['attributes']['ZS_Games'];
}
$Gwinpct = number_format(round((($wins[$FGod]  / $gt) * 100),2),"2",".","");
$Gusepct = number_format(round((($gt / $TotalGames) * 100),2),"2",".","");

$wWin = round(($wins[$FGod]  / $gt)* 100); 
$wUse = round((($gt  / $TotalGames) * 100)); 

$GodsRow .=  $this->parse_tmpl("godsRow",array(
                                         "{god}" => $FGod,
										 "{used}" => $gt, 
										 "{wins}" => $wins[$FGod],
										 "{wpct}" =>  $Gwinpct,
										 "{pct}" => $Gusepct,
									     "{class}" => $gclass,
										 "{widthwin}" => $wWin,
										 "{widthuse}" => $wUse,
																		 
					
																			 
));

$stylec++;
}

}


if (empty($d_ar['4']['attributes']['LastUpdated'])) 
{
//Hack to make All Community stats show up

if($player=="TotalCommunityStats")
{
$this->PlayerModes($ver, $type, $time, "");
} 
else {
$this->PlayerSearch($ver, $type, $time, $qplayer);  
}

}
else {

if ($parse) {

$lstupd2 = $this->ParseTime($d_ar['4']['attributes']['LastUpdated']);
$lstupd2 = date("M.j.y - h:iA",$lstupd2);
 if($player=="TotalCommunityStats") {

 if($ver=="aomx")
{
$d_ar['4']['attributes']['ZS_Games'] = $d_ar['4']['attributes']['ZS_TotalGames'];
}

	$out .= $this->parse_tmpl("PlayerInfoCom",array(
		"{games}" => $d_ar['4']['attributes']['ZS_Games'],
		"{avgGtime}"  => $this->GoTime($d_ar['4']['attributes']['ZS_AverageGametime']),
		"{avgscore}"  => $d_ar['4']['attributes']['ZS_AvgScore'],
		"{totGtime}" => $this->GoTime($d_ar['4']['attributes']['ZS_TotalGametime']),
		"{lstgame}" => $lstupd2,
		"{time}" => $this->GoEnglish($time), 
        "{ver}"     => $this->GoEnglish($ver),
        "{type}"     => $this->GoEnglish($type),
		"{time}"     => $this->GoEnglish($time),

	));
}
else
{
	$out .= $this->parse_tmpl("PlayerInfo",array(
		"{rating}" => $this->GoEnglish($d_ar['4']['attributes']['ZS_CombinedELORating']),
		"{wins}" => $d_ar['4']['attributes']['ZS_Wins'],
		"{games}" => $d_ar['4']['attributes']['ZS_Games'],
		"{losses}" => ($d_ar['4']['attributes']['ZS_Games'] - $d_ar['4']['attributes']['ZS_Wins']),
		"{avgGtime}"  => $this->GoTime($d_ar['4']['attributes']['ZS_AverageGametime']),
		"{avgscore}"  => $d_ar['4']['attributes']['ZS_AvgScore'],
        "{rank}"  => $this->GetRank($ver, $type, $time, $player), 
		"{totGtime}" => $this->GoTime($d_ar['4']['attributes']['ZS_TotalGametime']),
		"{lstgame}" => $lstupd2,
		"{winpct}"   => $d_ar['4']['attributes']['ZS_WinningPct'],
		"{time}" => $this->GoEnglish($time),
		"{player}" => $player,  
		"{ver}"     => $this->GoEnglish($ver),
	    "{type}"     => $this->GoEnglish($type),
	    "{time}"     => $this->GoEnglish($time),

	));
}

if($ver=="aomx" && $player=="TotalCommunityStats")
{
$cgames = $d_ar['4']['attributes']['ZS_PlayersToAge1'];
$tage2 = $d_ar['4']['attributes']['ZS_PlayersToAge2'];
$tage3 = $d_ar['4']['attributes']['ZS_PlayersToAge3'];
$tage4 = $d_ar['4']['attributes']['ZS_PlayersToAge4'];
}
else {
$tage2 = $d_ar['4']['attributes']['ZS_GamesToAge2'];
$tage3 = $d_ar['4']['attributes']['ZS_GamesToAge3'];
$tage4 = $d_ar['4']['attributes']['ZS_GamesToAge4'];
$cgames = $totalgr;
}

$out .= $this->parse_tmpl("age",array(
											"{time}"   =>$this->GoEnglish($time),
											"{ver}"     => $this->GoEnglish($ver),
											"{type}"     => $this->GoEnglish($type),
                                "{a1time}" => $this->GoTime($d_ar['4']['attributes']['ZS_AvgTimeInAge1']),
								"{a2time}" => $this->GoTime($d_ar['4']['attributes']['ZS_AvgTimeInAge2']),
							    "{a3time}" => $this->GoTime($d_ar['4']['attributes']['ZS_AvgTimeInAge3']),
								"{a4time}" => $this->GoTime($d_ar['4']['attributes']['ZS_AvgTimeInAge4']),
								
								"{a1games}" => $cgames,
								"{a2games}" => $tage2,
								"{a3games}" => $tage3,
								"{a4games}" => $tage4,
								
								"{a1pc}" => "100.00%",
								"{a2pc}" => number_format(round((($tage2 / $cgames) * 100),2),"2",".",""),
								"{a3pc}" => number_format(round((($tage3 / $cgames) * 100),2),"2",".",""),
								"{a4pc}" => number_format(round((($tage4 / $cgames) * 100),2),"2",".",""),
								
								"{a1tcp}" => $d_ar['4']['attributes']['ZS_TotalAge1CivPop'],
								"{a2tcp}" => $d_ar['4']['attributes']['ZS_TotalAge2CivPop'],
								"{a3tcp}" => $d_ar['4']['attributes']['ZS_TotalAge3CivPop'],
								"{a4tcp}" => $d_ar['4']['attributes']['ZS_TotalAge4CivPop'],
								
								"{a1tmp}" => $d_ar['4']['attributes']['ZS_TotalAge1MilPop'],
								"{a2tmp}" => $d_ar['4']['attributes']['ZS_TotalAge2MilPop'],
								"{a3tmp}" => $d_ar['4']['attributes']['ZS_TotalAge3MilPop'],
								"{a4tmp}" => $d_ar['4']['attributes']['ZS_TotalAge4MilPop'],
								
								"{a1tmyp}" => $d_ar['4']['attributes']['ZS_TotalAge1MythPop'],
								"{a2tmyp}" => $d_ar['4']['attributes']['ZS_TotalAge2MythPop'],
								"{a3tmyp}" => $d_ar['4']['attributes']['ZS_TotalAge3MythPop'],
								"{a4tmyp}" => $d_ar['4']['attributes']['ZS_TotalAge4MythPop'],
								//start killed
								"{a1tcpK}" => $d_ar['4']['attributes']['ZS_TotalAge1CivPopK'],
								"{a2tcpK}" => $d_ar['4']['attributes']['ZS_TotalAge2CivPopK'],
								"{a3tcpK}" => $d_ar['4']['attributes']['ZS_TotalAge3CivPopK'],
								"{a4tcpK}" => $d_ar['4']['attributes']['ZS_TotalAge4CivPopK'],
								
								"{a1tmpK}" => $d_ar['4']['attributes']['ZS_TotalAge1MilPopK'],
								"{a2tmpK}" => $d_ar['4']['attributes']['ZS_TotalAge2MilPopK'],
								"{a3tmpK}" => $d_ar['4']['attributes']['ZS_TotalAge3MilPopK'],
								"{a4tmpK}" => $d_ar['4']['attributes']['ZS_TotalAge4MilPopK'],
								
								"{a1tmypK}" => $d_ar['4']['attributes']['ZS_TotalAge1MythPopK'],
								"{a2tmypK}" => $d_ar['4']['attributes']['ZS_TotalAge2MythPopK'],
								"{a3tmypK}" => $d_ar['4']['attributes']['ZS_TotalAge3MythPopK'],
								"{a4tmypK}" => $d_ar['4']['attributes']['ZS_TotalAge4MythPopK'],
								
								//start average-----
								
								"{a1acp}" => $d_ar['4']['attributes']['ZS_AvgAge1CivPop'],
								"{a2acp}" => $d_ar['4']['attributes']['ZS_AvgAge2CivPop'],
								"{a3acp}" => $d_ar['4']['attributes']['ZS_AvgAge3CivPop'],
								"{a4acp}" => $d_ar['4']['attributes']['ZS_AvgAge4CivPop'],
								
								"{a1amp}" => $d_ar['4']['attributes']['ZS_AvgAge1MilPop'],
								"{a2amp}" => $d_ar['4']['attributes']['ZS_AvgAge2MilPop'],
								"{a3amp}" => $d_ar['4']['attributes']['ZS_AvgAge3MilPop'],
								"{a4amp}" => $d_ar['4']['attributes']['ZS_AvgAge4MilPop'],
								
								"{a1amyp}" => $d_ar['4']['attributes']['ZS_AvgAge1MythPop'],
								"{a2amyp}" => $d_ar['4']['attributes']['ZS_AvgAge2MythPop'],
								"{a3amyp}" => $d_ar['4']['attributes']['ZS_AvgAge3MythPop'],
								"{a4amyp}" => $d_ar['4']['attributes']['ZS_AvgAge4MythPop'],
								//start killed average
								"{a1acpK}" => $d_ar['4']['attributes']['ZS_AvgAge1CivPopK'],
								"{a2acpK}" => $d_ar['4']['attributes']['ZS_AvgAge2CivPopK'],
								"{a3acpK}" => $d_ar['4']['attributes']['ZS_AvgAge3CivPopK'],
								"{a4acpK}" => $d_ar['4']['attributes']['ZS_AvgAge4CivPopK'],
								
								"{a1ampK}" => $d_ar['4']['attributes']['ZS_AvgAge1MilPopK'],
								"{a2ampK}" => $d_ar['4']['attributes']['ZS_AvgAge2MilPopK'],
								"{a3ampK}" => $d_ar['4']['attributes']['ZS_AvgAge3MilPopK'],
								"{a4ampK}" => $d_ar['4']['attributes']['ZS_AvgAge4MilPopK'],
								
								"{a1amypK}" => $d_ar['4']['attributes']['ZS_AvgAge1MythPopK'],
								"{a2amypK}" => $d_ar['4']['attributes']['ZS_AvgAge2MythPopK'],
								"{a3amypK}" => $d_ar['4']['attributes']['ZS_AvgAge3MythPopK'],
								"{a4amypK}" => $d_ar['4']['attributes']['ZS_AvgAge4MythPopK'],

											)); 
		 if($ver=="aom") { $d_ar['4']['attributes']['ZS_STC'] = "<em>Unavailable in AoM</em>";
		 $d_ar['4']['attributes']['ZS_RLC'] = "<em>Unavailable in AoM</em>";
		 $d_ar['4']['attributes']['ZS_SettlementsAvg'] = "<em>Unavailable in AoM</em>";
		 $d_ar['4']['attributes']['ZS_RelicsAvg'] = "<em>Unavailable in AoM</em>";
		  }
$out .= $this->parse_tmpl("econ",array(
                                         "{af}" => $d_ar['4']['attributes']['ZS_EconFoodAvg'], 
										 "{aw}" => $d_ar['4']['attributes']['ZS_EconWoodAvg'], 
										 "{ag}" => $d_ar['4']['attributes']['ZS_EconGoldAvg'], 
										 "{afv}" => $d_ar['4']['attributes']['ZS_EconFavorAvg'], 
										
										 "{atc}" => $d_ar['4']['attributes']['ZS_SettlementsAvg'], 
										 "{ar}" => $d_ar['4']['attributes']['ZS_RelicsAvg'],
										 
										 "{tf}" => $d_ar['4']['attributes']['ZS_EF'], 
										 "{tw}" => $d_ar['4']['attributes']['ZS_EW'], 
										 "{tg}" => $d_ar['4']['attributes']['ZS_EG'], 
										 "{tfv}" => $d_ar['4']['attributes']['ZS_EFV'], 
										 "{ttc}" => $d_ar['4']['attributes']['ZS_STC'],
										 "{tar}" => $d_ar['4']['attributes']['ZS_RLC'], 
										
										 
));
if($player!="TotalCommunityStats")
{
$out .=$this->GameList($ver, $type, $player);
}

$out .= $this->parse_tmpl("godsHead",array(
                                         "{g1c}" => $FavGod,
										 "{g1x}" => $gtimes, 
										 "{g2c}" => $F2God, 
										 "{g2x}" => $g2times, ));

$out .= "$GodsRow";

$out .=  $this->parse_tmpl("godsFoot",array(
                                         "{g1c}" => $FavGod,
										 "{g1x}" => $gtimes, 
										 "{g2c}" => $F2God, 
										 "{g2x}" => $g2times,										 
					
										
										 
));

$out .=$this->UnitsList($ver, $type, $time, $qplayer, "ZS_EU");
$out .=$this->UnitsList($ver, $type, $time, $qplayer, "ZS_MU");
$out .=$this->UnitsList($ver, $type, $time, $qplayer, "ZS_MyU");
$out .=$this->UnitsList($ver, $type, $time, $qplayer, "ZS_GP");
										



if(empty($d_ar['4']['attributes']['LastUpdated'])) { return  $this->PrintError("Player not found.");}
	    else { print "$out"; }
}	


else {

// If the parse switch is false then we return 
// the $PlayerInfo array which is used
// throughout the script. Such as more detailed
// top list info. I could use the above array
// but in order to keep the script as fast as
// possible this is a smaller more cut to the 
// chase array.

$gpct = $gtimes/$d_ar['4']['attributes']['ZS_Games'];
$gpct = round($gpct,2);
$gpct = $gpct * 100; 

$wgpct = $wtimes/$d_ar['4']['attributes']['ZS_Wins'];
$wgpct = round($wgpct,2);
$wgpct = $wgpct * 100; 
		

$PlayerInfo = array(
											
											"rating"     => $d_ar['4']['attributes']['ZS_CombinedELORating'],
											"wins"     => $d_ar['4']['attributes']['ZS_Wins'],
											"games"    => $d_ar['4']['attributes']['ZS_Games'],
											"winpct"   => $d_ar['4']['attributes']['ZS_WinningPct'],
											"avgscore" => $d_ar['4']['attributes']['ZS_AvgScore'],
											"favgod"   => $FavGod,
											"gpct"     => $gpct,
											"wingod"   => $WinGod,
											"wgpct"    => $wgpct,
											"losses"   => $losses,
											"avgtime"   => $d_ar['4']['attributes']['ZS_AverageGametime'],
											"totaltime"   => $d_ar['4']['attributes']['ZS_TotalGametime'],
											
											);




return $PlayerInfo;

//print_r($PlayerInfo);

 }

} //search force else
} // exit to search
} // ! player name
}

function UnitsList($ver, $type, $time, $player, $sect) {

$parser = xml_parser_create(); 
xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 
$unitsHead = "unitsHead";

if($sect=="ZS_GP")
{
$unitsHead = "gpHead";
} 

if($ver=="aomx") {
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='6'/><qed%20id='0'%20fe='0'%20me='100'%20dg='$sect'%20en='$player'%20et='ZS_Human'%20md='$type'%20tp='$time'/></clr>";
} elseif($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='6'/><qed%20id='0'%20fe='0'%20me='100'%20dg='$sect'%20en='$player'%20et='ZS_Human'%20md='$type'%20tp='$time'/></clr>";
}


$data = implode("",file("$query")); 

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or PrintError("XML ERROR"); 
xml_parser_free($parser); 
//print $d_ar[4]['attributes']['DataGroupEnum'];
if($d_ar[4]['attributes']['DataGroupEnum'])
{
//print_r($d_ar);
$list .= $this->parse_tmpl("$unitsHead",array("{sect}" => $this->GoEnglish($sect)));

//print_r($d_ar);
//print "<hr>";

 $countArr =0;
  for ($Uint = 4; $Uint < count($i_ar['values'])+4; $Uint++) {

   

  $ZS_TotalProduced[$countArr] = $d_ar[$Uint]['attributes']['ZS_TotalProduced'];
  $ZS_TotalLost[$countArr] = $d_ar[$Uint]['attributes']['ZS_TotalLost'];
  $DataGroupEnum[$countArr] = $d_ar[$Uint]['attributes']['DataGroupEnum'];
  $ZS_ProducedPerGame[$countArr] = $d_ar[$Uint]['attributes']['ZS_ProducedPerGame'];
  $ZS_LostPerGame[$countArr] = $d_ar[$Uint]['attributes']['ZS_LostPerGame'];
  $ZS_Games[$countArr] = $d_ar[$Uint]['attributes']['ZS_Games'];
  $ZS_TotalUsedInAge1[$countArr] = $d_ar[$Uint]['attributes']['ZS_TotalUsedInAge1'];
  $ZS_TotalUsedInAge2[$countArr] = $d_ar[$Uint]['attributes']['ZS_TotalUsedInAge2'];
  $ZS_TotalUsedInAge3[$countArr] = $d_ar[$Uint]['attributes']['ZS_TotalUsedInAge3'];
  $ZS_TotalUsedInAge4[$countArr] = $d_ar[$Uint]['attributes']['ZS_TotalUsedInAge4'];
  $ZS_TimesCast[$countArr] = $d_ar[$Uint]['attributes']['ZS_TimesCast'];
  $LastUpdated[$countArr] = $d_ar[$Uint]['attributes']['LastUpdated'];
   $countArr++;

  }

if($sect == "ZS_MU" | $sect == "ZS_EU" | $sect == "ZS_MyU") {

	 array_multisort($ZS_TotalProduced, SORT_DESC,
                $ZS_TotalLost, 
			    $DataGroupEnum,
				$ZS_ProducedPerGame,
				$ZS_LostPerGame,
				$ZS_Games,
				$LastUpdated
				); 
 

}
elseif($sect=="ZS_GP") {

	 array_multisort($ZS_Games, SORT_DESC,
                 $ZS_TotalUsedInAge1, 
			     $ZS_TotalUsedInAge2,
				 $ZS_TotalUsedInAge3,
				 $ZS_TotalUsedInAge4,
			  $ZS_TimesCast,
			  $DataGroupEnum,
			  $LastUpdated
				); 

}

//arsort($sorter); // sort C biggest to smallest

 // this just counts at the end of loop for our order of use display
// $Sarray =0;
 

 for ($sint = 0; $sint < count($i_ar['values']); $sint++) {

//while(list($FUnit, $gamesU) = each($sorter)) {


    $lstupd = $this->ParseTime($LastUpdated[$sint]);
    $lstupd = date("M.j.y - h:iA",$lstupd);
	//print_r($ProducedPerGame);
	
	$stylebool = is_int($sint/2);  
		if($stylebool) {$class="row1"; }
		else { $class = "row2"; }
		



if($sect=="ZS_GP") {

if($ver=="aom") {
$casted = $ZS_TotalUsedInAge1[$sint] + $ZS_TotalUsedInAge2[$sint] + $ZS_TotalUsedInAge3[$sint] + $ZS_TotalUsedInAge4[$sint];

} else { 


// Gets the times available 
// multiplier for certain TT GPs
$multi = array(
"tech-721" => 2, //deconstruction
"tech-734" => 4, //gaia forest
"tech-730" => 3, //shockwave
"tech-755" => 2, //spider lair
"tech-756" => 3, //valor
"tech-722" => 3, //carnivora
"tech-723" => 2, //traitor
"tech-724" => 2, //chaos
"tech-752" => 2, //Hesperides
"tech-747" => 3, //vortex
);

$casted = $ZS_TimesCast[$sint]; 
} 
if(!$multi[$DataGroupEnum[$sint]]) {$multi[$DataGroupEnum[$sint]] =1; }


$wasted = ($ZS_Games[$sint] * $multi[$DataGroupEnum[$sint]]) - $casted;

$wastePct = number_format(round((($wasted / ($ZS_Games[$sint] * $multi[$DataGroupEnum[$sint]]))*100),2),"2",".","");
		if($casted && $player!="TotalCommunityStats")
		{
		$list .= $this->parse_tmpl("gpRow",array(
			"{games}" => $ZS_Games[$sint],
			"{casted}" => $casted,
            "{wasted}" => $wasted,
            "{pctw}" => $wastePct,
            "{gp}" => $this->unit_name($DataGroupEnum[$sint]), 
			"{class}"  => $class,
			"{lstgame}" => $lstupd,
));
}
}

else
{
$proto = $d_ar[$int]['attributes']['DataGroupEnum'];
$unitn = $units[$proto];
$survival = (($ZS_TotalProduced[$sint] - $ZS_TotalLost[$sint]) / $ZS_TotalProduced[$sint]) * 100; 
$survival = number_format(round($survival,2),"2",".","");

if(!$unitn) { // we really wouldnt need this, but I needa see the proto ID's while im setting up the array
//$unitn = $d_ar[$int]['attributes']['DataGroupEnum'];
}
		$list .= $this->parse_tmpl("unitsRow",array(
			"{TotP}" => $ZS_TotalProduced[$sint],
			"{surv}" => $survival,
            "{TotL}" => $ZS_TotalLost[$sint],
            "{TotG}" => $ZS_Games[$sint],
            "{AvgP}" =>round($ZS_ProducedPerGame[$sint]),
            "{AvgL}" => round($ZS_LostPerGame[$sint]),
            "{Uid}" => $this->unit_name($DataGroupEnum[$sint]), 
			"{lstgame}" => $lstupd,
			"{class}"  => $class,));

}


$sArray++;
}
//}
$list .= $this->parse_tmpl("unitsFoot",array());

return $list;
 
 }



}

//===========================================
// Gets the top player lists.
//===========================================
function TopList($ver, $type, $time, $detailed,$en) {
/*if (empty($number) || $number > '100' || $number < '2') { $number='10';  } //Default to top 10, saves us some error messages for idiots
if ($number <= '26') { $detailed = true;} //if its over 25 its  what eso gives us by default
*/


$parser = xml_parser_create(); 

xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 

if($en)
{
$en = "en='".$en."'%20";
}


if ($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qer%20id='0'%20np='0'%20nn='101'%20si='0'%20".$en."et='ZS_Human'%20md='$type'%20rn='ZS_TopPlayers'%20tp='$time'/></clr>";

} 

elseif ($ver=="aomx") {
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qer%20id='0'%20np='0'%20nn='101'%20si='0'%20".$en."et='ZS_Human'%20md='$type'%20rn='ZS_TopPlayers'%20tp='$time'/></clr>";

}
$data = implode("",file("$query")); 

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or PrintError("XML ERROR"); 
xml_parser_free($parser); 

 
if($detailed)
{

$table = $ver."_".$type; 
$TimeQuery = "SELECT timestamp FROM top_$ver WHERE id ='1000'";
$doTimeQuery= mysql_query($TimeQuery);

$CronUpdated=mysql_fetch_array($doTimeQuery);

$header .= $this->parse_tmpl("TopListDetailedHeader",array(
 "{type}" => $this->GoEnglish($type),
 "{time}" => $this->GoEnglish($time),
 "{ver}" => $this->GoEnglish($ver),
 "{update}" =>  date("M.j.y - h:iA",$this->ParseTime($CronUpdated['timestamp'],true)),
));
$Ppages .="<div align='center'><strong>Pages: </strong>";
$page = $_GET['page']; 

if(!$page || $page > 10 || $page < 1)
{ $page = 1; }

for($c=1; $c < 11; $c++)
{
$ttype = $this->GoEnglish($type,true);
$pages = "<a href=\"?do=TopList&detailed=1&ver=$ver&type=$ttype&time=AllTime&page=$c\">[$c]</a> ";
if($c==$page) {
$pages ="<b>[$c]</b> ";
}

$Ppages .= "$pages"; 
}
$Ppages .= "</div><br>";
}
else {

		//set up next and prev links
		$sten = $d_ar['104']['attributes']['EntryName'];
		
		 if(empty($d_ar['104']['attributes']['EntryName'])) { $next = ""; } 
         else { 
		 $etype = $this->GoEnglish($type,true);
		 $etime = $this->GoEnglish($time,true);
		 $next = "<a href=\"?do=TopList&detailed=0&en=$sten&type=$etype&ver=$ver&time=$etime\"><strong>Next Page -></strong></a>"; }
         if(!$en) { $prev = ""; }
         else{ $prev = "<a href='javascript:history.go(-1)'><strong><- Prev Page</strong></a> |";  }

$header = $this->parse_tmpl("TopListHeader",array(
 "{type}" => $this->GoEnglish($type),
 "{time}" => $this->GoEnglish($time),
 "{ver}" => $this->GoEnglish($ver),
 "{prev}" => $prev,
 "{next}" => $next,
 "{en}"  => $d_ar['104']['attributes']['EntryName'],

));
}

print "$header"; 
print "$Ppages";

       
 	   $int = 3; // 3 deep again 
       if($en&&$d_ar['104']['attributes']['EntryName']) { $val=1;  }
	 elseif(empty($d_ar['104']['attributes']['EntryName'])) { $val=3; }
	    else { $val=2; }
	 
	 if(!$detailed) { 
	   while ($int < count($i_ar['values'])+$val) {
	   $int++;
	  
	   
		$player = $d_ar[$int]['attributes']['EntryName'];
	    
		//$rating = round($d_ar[$int]['attributes']['primaryrating'],2);
      //  $rating = number_format("$rating","2",".","");	 // Thanks Andy
		
		$lstgame = $this->ParseTime($d_ar[$int]['attributes']['date']); // Parse the ESO time
	    $lstgame = date("F.j.Y - h:iA",$lstgame);
	
		 
		 $stylebool = is_int($int/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; }
		 

		 
		 
		 // Switch this part

	   		 // Switch this part
		

		
		 		 $out = $this->parse_tmpl("TopList",array(
						
											"{name}"   => $d_ar[$int]['attributes']['EntryName'],
											"{rating}" => $this->GoEnglish($d_ar[$int]['attributes']['primaryrating']),
											"{rank}"   => $d_ar[$int]['attributes']['rank'],
											"{lstgme}" => $lstgame,
											"{ver}"    => $ver, 
											"{time}"    => $this->GoEnglish($time,true), 
											"{type}"    => $this->GoEnglish($type,true),
											"{class}"    => $class, 

												
											));
       
	  //--- Ok stop
	   
	   
	   

	
print "$out";
} //stop the loop

} 

else
{ 




$limit=100;
$limitvalue = $page * $limit - ($limit); 



$SQLquery = "SELECT * FROM top_$ver LIMIT $limitvalue, $limit";


$doTopKQuery = mysql_query($SQLquery) or die(mysql_error());

while($TopDetailedRow=mysql_fetch_array($doTopKQuery)) 		 

{
print $TopDetailedRow['row']; 
}
	   } //--- Ok stop



echo $this->parse_tmpl("TopListFooter",array(
"{en}"  => $d_ar['104']['attributes']['EntryName'],
"{next}" => $next,
"{prev}" => $prev,
));
print "$Ppages";
 //include "templates/TopListFooter.html"; // Close of the templates


}

//==================================
function ServeInfo()
{

	echo $this->parse_tmpl("home",array(

												
											));

$parser = xml_parser_create(); 

 
xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 

xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 

//read XML file into $data 
$query = "http://config.aom.eso.com/ConfigUS.aspx/?4874064l";
$data = implode("",file("$query")); 


xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or PrintError("Error Parsing XML Chat data"); 
xml_parser_free($parser); 


	 echo $this->parse_tmpl("ServerInfoHeader",array(
						
											"{tpop}"   => $d_ar['39']['attributes']['Population'],

));

for($int=20; $int < 39; $int++)
{
//$int++;

$stylebool = is_int($int/2);
if($stylebool) {
$row = "row1";
}
else {
$row = "row2";
}


	 $serverinfo = $this->parse_tmpl("ServerInfo",array(
						
											"{name}"   => $d_ar[$int]['attributes']['Name'],
											"{class}"   => $row,
											"{pop}" => $d_ar[$int]['attributes']['Population'],

												
											));
echo "$serverinfo";

}



include "templates/ServerInfoFooter.html";


}


//===========================================
// This search for a player 
//===========================================
function PlayerSearch($ver, $type, $time, $player, $ForceSearch=false)
{
if(strlen($player) < 3)
{
$this->PrintError("Please Enter a player name atleast three (3) characters in length.");
}

else {

$parser = xml_parser_create(); 


xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 

xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 


if($ver=="aomx") {
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qse%20id='0'%20fe='0'%20me='200'%20en='$player'%20et='ZS_Human'/></clr>";
} 
else if($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qse%20id='0'%20fe='0'%20me='200'%20en='$player'%20et='ZS_Human'/></clr>";
}

$data = implode("",file("$query")); 

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or print_error(); 
xml_parser_free($parser); 

#print_r($i_ar['values']);

$stopit = false; 

$counttd = 0; 
$int = 3;

while ($int < count($i_ar['values'])+3)
{
  $counttd++; $int++;
//spent nearly 10 minutes looking for this on php.net
// this returns 0 is in case sensitve match is found  
if(strcasecmp($d_ar[$int]['attributes']['EntityName'], $player) == 0 && $ForceSearch==false)  
#if($d_ar[$int]['attributes']['EntityName'] == $player) //=didn't work because ESO search has no case-sensitivity
{
$this->PlayerModes($ver, $type, $time, $player);
$stopit = true;
}         
}

if(!$stopit) {
if($ForceSearch) { $SearchMsg = "Forced Search Running on"; }
else {
$SearchMsg = "Could Not Find Player in"; }

$counttd = 0; // We must reset these values 
$int = 3; 
echo $this->parse_tmpl("SearchHeader",array( 

"{msg}" => $SearchMsg,
"{ver}" => $this->GoEnglish($ver), )); 

while ($int < count($i_ar['values'])+3)
{
  
  $counttd++; $int++;
  
// If we find an exact match,
// then we send asking for header only
/*if($d_ar[$int]['attributes']['EntityName'] == $player) 
{
$this->PlayerModes($ver, $type, $time, $player);

}   */


		 $stylebool = is_int($counttd/2);   // Alternate style
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; }

  
  if(is_int($counttd/5)) { //Ever 5th Column make a new row
  $TR = "</tr><tr class=\"$class\">";
  }  else {$TR = ""; }
  
 	
	 		    
				$sout = $this->parse_tmpl("SearchRow",array(
						
				"{name}"  => $d_ar[$int]['attributes']['EntityName'],
				"{TR}" => $TR,
				"{count}" => $counttd,
				"{class}" => $class,
                "{ver}" => $ver,
				
 
 
				
				));


   echo "$sout";          

} // loop complete
include "templates/SearchFoot.html";

}		




} // PrintError at top if search term is smaller then 3 chars


}



//===========================================
// Turns the ESO time into a php timestamp
//then adds the cookie offset value.
//===========================================
function ParseTime($esotime,$eso=false)
{
$cookie = $_COOKIE['fropco_eso_time_offset'];

if(!$eso) {
$kill = array("T", ":", ".");
$esotime = str_replace($kill, "-", $esotime);

list($year, $month, $day, $hour, $min, , , , , ) = explode("-", $esotime);

//echo "$year, $month, $day, $hour, $min"; 

$esotime = mktime($hour, $min, 0, $month, $day, $year);

//$esotime = $esotime + 28800;
} else { $esotime = $esotime + 18000;}

if ($cookie == "GMT00" || !$cookie || $cookie > 12 || $cookie < -12)
{
$cookie = 0;
}
else {
$cookie = $cookie * 3600;

}
$esotime = $esotime + $cookie;

return "$esotime";

}

//===========================================
// Converts query calls into nice english
//===========================================
function GoEnglish($code,$nospace=false) {
if($code=="aom"){ $code = "AoM"; } 
elseif($code=="aomx") { $code = "AoM:TT"; }

elseif($code=="ZS_AllTime") {
if($nospace) { $code = "AllTime"; } else{
$code = "All Time"; }} 

elseif($code=="ZS_Weekly") {$code = "Weekly"; } 
elseif($code=="ZS_Monthly") {$code = "Monthly"; } 

elseif($code=="ZS_EU") {$code = "Civilian"; } 
elseif($code=="ZS_MU") {$code = "Military"; } 
elseif($code=="ZS_MyU") {$code = "Myth"; } 

elseif($code=="ZS_Monthly") {$code = "Monthly"; } 

elseif($code=="ZS_Supremacy") {$code = "Supremacy"; }
elseif($code=="ZS_Conquest") {$code = "Conquest"; }
elseif($code=="ZS_Deathmatch") {
if($nospace) { $code = "Deathmatch"; } else
{ $code = "Death Match"; } }
elseif($code=="ZS_Lightning") {$code = "Lightning"; }
elseif($code=="ZS_Custom") {
if($nospace) {$code = "NotRated"; }
else { $code = "Not Rated"; } }

else { // Does rating

		$code = round($code,2);
        $code = number_format("$code","2",".","");	 // Thanks Andy
if($code == 0.00) { $code = "n/a"; }
}
return "$code";
}

//===========================================
// UrlCleaner
//===========================================
function URLcleaner($code) {
if($code=="AllTime"){ $code = "ZS_AllTime"; } 
if($code=="All"){ $code = "ZS_All"; } 
elseif($code=="Weekly") { $code = "ZS_Weekly"; }
elseif($code=="Monthly") {$code = "ZS_Monthly"; } 

elseif($code=="Supremacy") {$code = "ZS_Supremacy"; }
elseif($code=="Conquest") {$code = "ZS_Conquest"; }
elseif($code=="Deathmatch") {$code = "ZS_Deathmatch"; }
elseif($code=="Lightning") {$code = "ZS_Lightning"; }
elseif($code=="NotRated") {$code = "ZS_Custom"; }

else { 
$code = "ZS_Supremacy";
$code = "ZS_AllTime";
}
return "$code";
}

function GetRank($ver, $type, $time, $player)
{
$parser = xml_parser_create(); 


xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 

xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 


if($ver=="aomx") {
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qer%20id='0'%20np='0'%20nn='0'%20si='0'%20en='$player'%20et='ZS_Human'%20md='$type'%20rn='ZS_TopPlayers'%20tp='$time'/></clr>";
} 
else if($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qer%20id='0'%20np='0'%20nn='0'%20si='0'%20en='$player'%20et='ZS_Human'%20md='$type'%20rn='ZS_TopPlayers'%20tp='$time'/></clr>";
}

$data = implode("",file("$query")); 

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or printerror("xmlerror"); 

xml_parser_free($parser); 

$return = $d_ar['4']['attributes']['rank'];
if (!$return) { $return = "-"; }

return $return;
}

//===========================================
// Prints a nice litte error page
//===========================================
function PrintError($err) {
// fill in w/ template
print "$err";
}

//===========================================
// Prints a nice litte error page
//===========================================
function showtime($boolcook) {
if ($boolcook) {
print "Time Zone was successfully set and stored in your cookies.";
}
else {
$this->PrintError("Cookie was unable to set. Make sure your browser accepts them.");
}

}
function GameList($ver, $type, $player, $comgames=false) {

$parser = xml_parser_create(); 

if($comgames) { $player==""; }

xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 

if($ver=="aomx"){
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='1065358138'%20U='6'/><qsg%20id='0'%20fe='0'%20me='0'%20en='$player'%20et='ZS_Human'%20md='$type'/></clr>";
} elseif($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='1065358138'%20U='6'/><qsg%20id='0'%20fe='0'%20me='0'%20en='$player'%20et='ZS_Human'%20md='$type'/></clr>";
}

$data = implode("",file("$query")); 

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or printerror("xml error"); 
xml_parser_free($parser); 

$out .=$this->parse_tmpl("GameListHeader",array("{player}" => $player, "{type}" => $this->GoEnglish($type), ));
$gmeday = 1;
for ($int =4; $int < count($i_ar['values'])+4; $int++) { 



$gameDate = $this->ParseTime($d_ar[$int]['attributes']['GameDate']);
   $gameDate = date("F.j.Y - h:iA",$gameDate);
   
   $stylebool = is_int($int/2);  
		if($stylebool) {$class="row1"; }
		else { $class = "row2"; }

     $out .= $this->parse_tmpl("GameListRow",array(
                "{number}" => $gmeday,
				"{ver}" => $ver,
				"{gdate}" => $gameDate,
				"{numpl}" => $d_ar[$int]['attributes']['ZS_PL'], 
				"{length}" => $this->GoTime($d_ar[$int]['attributes']['ZS_GL']),
				"{game}" => $d_ar[$int]['attributes']['GameID'],
				"{class}" => $class,  
	
             
        ));
	 if($gmeday > 499)
 { break; }
 
		$gmeday++;
	

 
} 







//$out .=$this->parse_tmpl("SearchRow",array());


 $out .= $this->parse_tmpl("GameListFooter",array());
return $out;
}

//===========================================
// Detailed Match info
//===========================================
function GameInfo($ver, $type, $time, $player, $game)
{

//print $game;
$parser = xml_parser_create(); 
xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 

if($ver=="aomx"){
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qgs%20id='0'%20gi='$game'/></clr>";
}
else if($ver=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qgs%20id='0'%20gi='$game'/></clr>";
}

$data = implode("",file("$query")); //Boom!

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar);
xml_parser_free($parser);

$Num = $d_ar[$i_ar['ZS_PL']['0']]['attributes']['v'];

if(!$Num) { $this->PrintError("Game does not exist"); }

else {

//print $Num;
$t=0;
for($n=0; $n < $Num; $n++) 
{

$Players[$n] = $d_ar[$i_ar['ei'][$t]]['attributes']['v'];

$Teams[$n] = $d_ar[$i_ar['ZS_TM'][$n]]['attributes']['v'];

$pw = $i_ar['ei'][$t]; $pw = $pw +1;  
$Winner[$n] = $d_ar[$pw]['attributes']['v'];

$t = $t+2;
}//player loop end 


// Kickass little function, 
// sorts multiple arrays by the first a
// rray in the function, in this case: Teams.
	 array_multisort($Teams, SORT_DESC,
                $Winner, 
			    $Players); 
	
	//rsort($Teams);
$NumTeams = $Teams[0]; // get the number of teams by finding the 

	
	 
	 echo  $this->parse_tmpl("DetailHeading",array(
"{GL}" => $this->GoTime($d_ar[$i_ar['ZS_GL']['0']]['attributes']['v']),
"{Map}" => $d_ar[$i_ar['ZS_GM']['0']]['attributes']['v'],
"{Type}" => $this->GoEnglish($d_ar[$i_ar['gs']['0']]['attributes']['m']),
"{colspan}" => $NumTeams,

));

$s=0;
for($nT=1; $nT < $NumTeams +1; $nT++) {

reset($Teams);
$class = "row".$nT;
echo "<td><table cellspacing=\"1\" cellpadding=\"0\" width='100%'><tr height='20' class='row2'><td><strong>Player Name</strong><td><strong>Team</strong><td><strong>Win/Loss</strong></td></tr>"; 
for($n=0; $n < $Num; $n++) 
{

if($Teams[$n]==$nT) {
//$wonlost = $WL[$Pname]; 

if($Winner[$n]) {
$Winner[$n] = "<font color=\"green\">(Won)</font>";
}else {
$Winner[$n] = "<font color=\"red\">(Lost)</font>";
}

$s++;		 
	$urlType = $this->GoEnglish($d_ar[$i_ar['gs']['0']]['attributes']['m'],true);
print "<tr class=\"row1\"><td  width='33%'><strong><a href=\"?do=PlayerInfo&player=".$Players[$n]."&time=AllTime&type=$urlType&ver=$ver\">".$Players[$n]."</a></strong></td> <td width='33%'>[Team ".$Teams[$n]."]</td><Td width='33%'> ".$Winner[$n]."</td></tr>";

}

}
echo "</table></td>";

}

	 	 echo  $this->parse_tmpl("DetailHeadingFoot",array(

));


$w =0; $wo =1; $ab = $ac = $ad =0;
for($int=0; $int < $Num; $int++) 
{
$ZS_A2D[$int] = $d_ar[$i_ar['ZS_A2D'][$ab]]['attributes']['v']; 
$ZS_A3D[$int] = $d_ar[$i_ar['ZS_A3D'][$ac]]['attributes']['v']; 
$ZS_A4D[$int] = $d_ar[$i_ar['ZS_A4D'][$ad]]['attributes']['v']; 


if(!$d_ar[$i_ar['ZS_TA2'][$int]]['attributes']['v'])
{
$ZS_A2D[$int] = "-"; 
}
if(!$d_ar[$i_ar['ZS_TA3'][$int]]['attributes']['v'])
{
$ZS_A3D[$int] = "-"; 
}
if(!$d_ar[$i_ar['ZS_TA4'][$int]]['attributes']['v'])
{
$ZS_A4D[$int] = "-"; 
}


echo  $this->parse_tmpl("GameAge",array(

"{player}" => $d_ar[$i_ar['ei'][$w]]['attributes']['v'],
"{a1cp}" =>  $d_ar[$i_ar['ZS_A1CP'][$int]]['attributes']['v'],
"{a1cpK}" =>  $d_ar[$i_ar['ZS_A1CPK'][$int]]['attributes']['v'],
"{a1mp}" =>  $d_ar[$i_ar['ZS_A1MP'][$int]]['attributes']['v'],
"{a1mpK}" =>  $d_ar[$i_ar['ZS_A1MPK'][$int]]['attributes']['v'],
"{a1myp}" =>  $d_ar[$i_ar['ZS_A1MyP'][$int]]['attributes']['v'],
"{a1mypK}" =>  $d_ar[$i_ar['ZS_A1MyPK'][$int]]['attributes']['v'],

"{a2cp}" =>  $d_ar[$i_ar['ZS_A2CP'][$int]]['attributes']['v'],
"{a2cpK}" =>  $d_ar[$i_ar['ZS_A2CPK'][$int]]['attributes']['v'],
"{a2mp}" =>  $d_ar[$i_ar['ZS_A2MP'][$int]]['attributes']['v'],
"{a2mpK}" =>  $d_ar[$i_ar['ZS_A2MPK'][$int]]['attributes']['v'],
"{a2myp}" =>  $d_ar[$i_ar['ZS_A2MyP'][$int]]['attributes']['v'],
"{a2mypK}" =>  $d_ar[$i_ar['ZS_A2MyPK'][$int]]['attributes']['v'],

"{a3cp}" =>  $d_ar[$i_ar['ZS_A3CP'][$int]]['attributes']['v'],
"{a3cpK}" =>  $d_ar[$i_ar['ZS_A3CPK'][$int]]['attributes']['v'],
"{a3mp}" =>  $d_ar[$i_ar['ZS_A3MP'][$int]]['attributes']['v'],
"{a3mpK}" =>  $d_ar[$i_ar['ZS_A3MPK'][$int]]['attributes']['v'],
"{a3myp}" =>  $d_ar[$i_ar['ZS_A3MyP'][$int]]['attributes']['v'],
"{a3mypK}" =>  $d_ar[$i_ar['ZS_A3MyPK'][$int]]['attributes']['v'],

"{a4cp}" =>  $d_ar[$i_ar['ZS_A4CP'][$int]]['attributes']['v'],
"{a4cpK}" =>  $d_ar[$i_ar['ZS_A4CPK'][$int]]['attributes']['v'],
"{a4mp}" =>  $d_ar[$i_ar['ZS_A4MP'][$int]]['attributes']['v'],
"{a4mpK}" =>  $d_ar[$i_ar['ZS_A4MPK'][$int]]['attributes']['v'],
"{a4myp}" =>  $d_ar[$i_ar['ZS_A4MyP'][$int]]['attributes']['v'],
"{a4mypK}" =>  $d_ar[$i_ar['ZS_A4MyPK'][$int]]['attributes']['v'],

"{a1t}" =>  $this->GoTime($d_ar[$i_ar['ZS_TA1'][$int]]['attributes']['v']),
"{a2t}" =>  $this->GoTime($d_ar[$i_ar['ZS_TA2'][$int]]['attributes']['v']),
"{a3t}" =>  $this->GoTime($d_ar[$i_ar['ZS_TA3'][$int]]['attributes']['v']),
"{a4t}" =>  $this->GoTime($d_ar[$i_ar['ZS_TA4'][$int]]['attributes']['v']),

"{a1d}" => $this->unit_name($d_ar[$i_ar['ZS_PD'][$int]]['attributes']['v']),
"{a2d}" => $this->unit_name($ZS_A2D[$int]),
"{a3d}" => $this->unit_name($ZS_A3D[$int]),
"{a4d}" => $this->unit_name($ZS_A4D[$int]),

));
 $w = $w+2;  $wo = $wo+2; 
 //hack\\
if($d_ar[$i_ar['ZS_TA2'][$int]]['attributes']['v']) { $ab++;  }
if($d_ar[$i_ar['ZS_TA3'][$int]]['attributes']['v']) { $ac++;  }
if($d_ar[$i_ar['ZS_TA4'][$int]]['attributes']['v']) { $ad++;  }

}


$int2=0;
include "templates/ScoreHead.html";

// This loop creates several arrays
// The highest value is found for each
// array. This is used to bold 
// highest scores in each section 
for($int=0; $int < $Num; $int++) {
$TS[$int] = $d_ar[$i_ar['ZS_TS'][$int]]['attributes']['v'];
$ES[$int] = $d_ar[$i_ar['ZS_ES'][$int]]['attributes']['v'];
$MS[$int] = $d_ar[$i_ar['ZS_MS'][$int]]['attributes']['v'];
$MyS[$int] = $d_ar[$i_ar['ZS_MyS'][$int]]['attributes']['v'];
$RS[$int] = $d_ar[$i_ar['ZS_RS'][$int]]['attributes']['v'];
}
rsort($TS); rsort($MS);  rsort($ES);  rsort($MyS); rsort($RS); 

 
for($int=0; $int < $Num; $int++) 
{

	
		 $stylebool = is_int($int/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; } 

if($TS[0] == $d_ar[$i_ar['ZS_TS'][$int]]['attributes']['v']) {
$d_ar[$i_ar['ZS_TS'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_TS'][$int]]['attributes']['v']."</font></b>";
} 
if($ES[0] == $d_ar[$i_ar['ZS_ES'][$int]]['attributes']['v']) {
$d_ar[$i_ar['ZS_ES'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_ES'][$int]]['attributes']['v']."</font></b>";
}
if($MS[0] == $d_ar[$i_ar['ZS_MS'][$int]]['attributes']['v']) {
$d_ar[$i_ar['ZS_MS'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_MS'][$int]]['attributes']['v']."</font></b>";
}
if($MyS[0] == $d_ar[$i_ar['ZS_MyS'][$int]]['attributes']['v']) {
$d_ar[$i_ar['ZS_MyS'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_MyS'][$int]]['attributes']['v']."</font></b>";
}
if($RS[0] == $d_ar[$i_ar['ZS_RS'][$int]]['attributes']['v']) {
$d_ar[$i_ar['ZS_RS'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_RS'][$int]]['attributes']['v']."</font></b>";
}
	 echo  $this->parse_tmpl("ScoreRow",array(
	 
"{TotalScore}" => $d_ar[$i_ar['ZS_TS'][$int]]['attributes']['v'],
"{EconScore}" => $d_ar[$i_ar['ZS_ES'][$int]]['attributes']['v'],
"{MiliScore}" => $d_ar[$i_ar['ZS_MS'][$int]]['attributes']['v'],
"{ImprovScore}" => $d_ar[$i_ar['ZS_RS'][$int]]['attributes']['v'],
"{MythScore}" => $d_ar[$i_ar['ZS_MyS'][$int]]['attributes']['v'],
"{class}" => $class,
"{Player}" =>  $d_ar[$i_ar['ei'][$int2]]['attributes']['v'],

));
$int2 = $int2 +2; 
}
include "templates/ScoreFoot.html";

//print_r($i_ar);
//print_r($i_ar);

//FROPCO FILE/////////////////////////
echo "<p align=\"center\"><img src=\"http://eso.fropco.com/gamegraph.php?unit=C&ver=$ver&game=$game\" border=\"0\"> 
</p>";
echo "<p align=\"center\"><img src=\"http://eso.fropco.com/gamegraph.php?unit=M&ver=$ver&game=$game\" border=\"0\"> 
</p>";

//OWN FILE/////////////////////////
/*
echo "<p align='center'><img src='./gamegraph.php?unit=C&ver=$ver&game=$game' border='0'> 
</p>";
echo "<p align='center'><img src='./gamegraph.php?unit=M&ver=$ver&game=$game' border='0'> 
</p>";
*/

$int2=0;
//Military
include "templates/MiliHead.html";

for($int=0; $int < $Num; $int++) {
if(!$d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'] || !$d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v']) {$killLoss[$int] = 0; } else {
$killLoss[$int] = round($d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'] /  $d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'],2); }
$MC[$int] = $d_ar[$i_ar['ZS_MC'][$int]]['attributes']['v'];
$UK[$int] = $d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'];
$UL[$int] = $d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'];
$BL[$int] = $d_ar[$i_ar['ZS_BL'][$int]]['attributes']['v'];
$BR[$int] = $d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v'];
$KL[$int] = $killLoss[$int]; 
}
rsort($KL); rsort($BR);  rsort($BL);  rsort($UL); rsort($UK); rsort($MC);

for($int=0; $int < $Num; $int++) 
{

	
		 $stylebool = is_int($int/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; } 

// We need these for division later, so we dont want any HTML tags around them
$k1 = $d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'];
$k2 = $d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'];
// Yeah I know its oogly
if($MC[0] == $d_ar[$i_ar['ZS_MC'][$int]]['attributes']['v']&& $d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_MC'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_MC'][$int]]['attributes']['v']."</font></b>";
} 
if($UK[0] == $d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v']."</font></b>";
}
if($UL[0] == $d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v']."</font></b>";
}
if($BL[0] == $d_ar[$i_ar['ZS_BL'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_BL'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_BL'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_BL'][$int]]['attributes']['v']."</font></b>";
}
if($BR[0] == $d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v'] =! 0) {
$d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v']."</font></b>";
}
if(!$k2) { $k2 =1; }
if($KL[0] == round($k1 / $k2,2)) {
$killLoss[$int] = 
"<b>".$killLoss[$int]."</b>";
}
	 echo  $this->parse_tmpl("MiliRow",array(
	 
"{MiliCrea}" => $d_ar[$i_ar['ZS_MC'][$int]]['attributes']['v'],
"{UntKill}" => $d_ar[$i_ar['ZS_UK'][$int]]['attributes']['v'],
"{UntLost}" => $d_ar[$i_ar['ZS_UL'][$int]]['attributes']['v'],
"{BuildRazed}" => $d_ar[$i_ar['ZS_BR'][$int]]['attributes']['v'],
"{BuildLost}" => $d_ar[$i_ar['ZS_BL'][$int]]['attributes']['v'],
"{KillLoss}" => $killLoss[$int],
"{class}" => $class,
"{Player}" =>  $d_ar[$i_ar['ei'][$int2]]['attributes']['v'],

));
$int2 = $int2 +2; 
}
include "templates/MiliFoot.html";

//End Military

$int2=0;

include "templates/EconHead.html";


for($int=0; $int < $Num; $int++) {
$EF[$int] = $d_ar[$i_ar['ZS_EF'][$int]]['attributes']['v'];
$EW[$int] = $d_ar[$i_ar['ZS_EW'][$int]]['attributes']['v'];
$EG[$int] = $d_ar[$i_ar['ZS_EG'][$int]]['attributes']['v'];
$EFV[$int] = $d_ar[$i_ar['ZS_EFV'][$int]]['attributes']['v'];
$EGT[$int] = $d_ar[$i_ar['ZS_EGT'][$int]]['attributes']['v'];
$EVH[$int] = $d_ar[$i_ar['ZS_EVH'][$int]]['attributes']['v']; //top civillian
}
rsort($EF); rsort($EW);  rsort($EG);  rsort($EFV); rsort($EGT); rsort($EVH);

 
for($int=0; $int < $Num; $int++) 
{

	
		 $stylebool = is_int($int/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; } 
// Yeah I know its oogly
if($EF[0] == $d_ar[$i_ar['ZS_EF'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_EF'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_EF'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_EF'][$int]]['attributes']['v']."</font></b>";
} 
if($EW[0] == $d_ar[$i_ar['ZS_EW'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_EW'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_EW'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_EW'][$int]]['attributes']['v']."</font></b>";
}
if($EG[0] == $d_ar[$i_ar['ZS_EG'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_EG'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_EG'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_EG'][$int]]['attributes']['v']."</font></b>";
}
if($EFV[0] == $d_ar[$i_ar['ZS_EFV'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_EFV'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_EFV'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_EFV'][$int]]['attributes']['v']."</font></b>";
}
if($EGT[0] == $d_ar[$i_ar['ZS_EGT'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_EGT'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_EGT'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_EGT'][$int]]['attributes']['v']."</font></b>";
}
if($EVH[0] == $d_ar[$i_ar['ZS_EVH'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_EVH'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_EVH'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_EVH'][$int]]['attributes']['v']."</font></b>";
}
	 echo  $this->parse_tmpl("EconRow",array(
	 
"{CivH}" => $d_ar[$i_ar['ZS_EVH'][$int]]['attributes']['v'],
"{Food}" => $d_ar[$i_ar['ZS_EF'][$int]]['attributes']['v'],
"{Wood}" => $d_ar[$i_ar['ZS_EW'][$int]]['attributes']['v'],
"{Gold}" => $d_ar[$i_ar['ZS_EG'][$int]]['attributes']['v'],
"{Favor}" => $d_ar[$i_ar['ZS_EFV'][$int]]['attributes']['v'],
"{GoldT}" => $d_ar[$i_ar['ZS_EGT'][$int]]['attributes']['v'],
"{class}" => $class,
"{Player}" =>  $d_ar[$i_ar['ei'][$int2]]['attributes']['v'],

));
$int2 = $int2 +2; 


}
include "templates/EconFoot.html";
// Misc stats
$int2=0;
include "templates/MiscHead.html";


for($int=0; $int < $Num; $int++) {
$STC[$int] = $d_ar[$i_ar['ZS_STC'][$int]]['attributes']['v'];
$RLC[$int] = $d_ar[$i_ar['ZS_RLC'][$int]]['attributes']['v'];
$RC[$int] = $d_ar[$i_ar['ZS_RC'][$int]]['attributes']['v'];
$ERT[$int] = $d_ar[$i_ar['ZS_ERT'][$int]]['attributes']['v'];
}
rsort($STC); rsort($RLC);  rsort($RC);  rsort($ERT);
 
for($int=0; $int < $Num; $int++) 
{

	
		 $stylebool = is_int($int/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; } 
// Yeah I know its oogly
if($STC[0] == $d_ar[$i_ar['ZS_STC'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_STC'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_STC'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_STC'][$int]]['attributes']['v']."</font></b>";
} 
if($RLC[0] == $d_ar[$i_ar['ZS_RLC'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_RLC'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_RLC'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_RLC'][$int]]['attributes']['v']."</font></b>";
}
if($RC[0] == $d_ar[$i_ar['ZS_RC'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_RC'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_RC'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_RC'][$int]]['attributes']['v']."</font></b>";
}
if($ERT[0] == $d_ar[$i_ar['ZS_ERT'][$int]]['attributes']['v'] && $d_ar[$i_ar['ZS_ERT'][$int]]['attributes']['v'] != 0) {
$d_ar[$i_ar['ZS_ERT'][$int]]['attributes']['v'] = 
"<b><font color='red'>".$d_ar[$i_ar['ZS_ERT'][$int]]['attributes']['v']."</font></b>";
}

	 echo  $this->parse_tmpl("MiscRow",array(
	 
"{TownC}" => $d_ar[$i_ar['ZS_STC'][$int]]['attributes']['v'],
"{Relics}" => $d_ar[$i_ar['ZS_RLC'][$int]]['attributes']['v'],
"{Research}" => $d_ar[$i_ar['ZS_RC'][$int]]['attributes']['v'],
"{Tribute}" => $d_ar[$i_ar['ZS_ERT'][$int]]['attributes']['v'],
"{class}" => $class,
"{Player}" =>  $d_ar[$i_ar['ei'][$int2]]['attributes']['v'],

));
$int2 = $int2 +2; 
}
include "templates/MiscFoot.html";



 $int2 =0; $oddint =1;
  for($int=0; $int < $Num; $int++) 
{
 
 //print_r($i_ar[$prkind]); 
 
 echo  $this->parse_tmpl("GUnitHead",array(
 "{player}" =>  $d_ar[$i_ar['ei'][$int2]]['attributes']['v'],
 ));
 $n2 = 0;
for($nunits=0; $nunits < count($i_ar['ZS_NP']); $nunits++) {


		 $stylebool = is_int($nunits/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; } 

//we print the rows if they are between the current players <ei> tags
if($i_ar['ZS_NP'][$nunits] < $i_ar['ei'][$oddint] && $i_ar['ZS_NP'][$nunits] > $i_ar['ei'][$int2]) {
 echo $this->parse_tmpl("GUnitRow",array(
 "{unit}" =>  $this->unit_name($d_ar[$i_ar['ZS_Units'][$n2]]['attributes']['v']),
 "{creat}" => $d_ar[$i_ar['ZS_NP'][$nunits]]['attributes']['v'],
 "{lost}" => $d_ar[$i_ar['ZS_NL'][$nunits]]['attributes']['v'],
 "{survive}" => number_format(100 - round((($d_ar[$i_ar['ZS_NL'][$nunits]]['attributes']['v'] / $d_ar[$i_ar['ZS_NP'][$nunits]]['attributes']['v']) * 100),2),"2",".",""),
 "{class}" => $class,

 ));
 }
  $n2 = $n2 +2;
 }
$int2 = $int2 +2;  $oddint = $oddint +2; 
include "templates/GUnitFoot.html";


}
}
}

//===========================================
// Converts seconds into D h:m:s
//===========================================
function GoTime($sec) {

$years = intval($sec / 31453600);
if($years) { $dhms.= $years .'Y '; }
$days = intval(($sec / 86400) % 365);

if($days) { $dhms.= $days .'D '; }

   $hours = intval(($sec / 3600) % 24); 
 
if($hours) { $dhms .= $hours. ':'; }
 
    $minutes = intval(($sec / 60) % 60); 

    $dhms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ':';

    $seconds = intval($sec % 60); 

    $dhms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    return $dhms;
    
  }

//===========================================
// Parses HTML Templates
//===========================================
function parse_tmpl( $template ,$toparse = array() ) {
	$topen    = "./templates/$template.html"; //in /templates
	$fp       = fopen( $topen , "r" );
	$contents = fread( $fp , filesize( $topen ) );
	fclose( $fp );

	foreach ( $toparse as $key => $value ) {
		$contents = str_replace( $key , $value , $contents );
	}
	return $contents;
}


//===========================================
// Convert Units Names
// This is really huge and nasty.
//===========================================
function unit_name($input)
{
$array = array(
"proto-2215" =>"Laborer",
"proto-2212" =>"Villager",
"proto-2208" =>"Gatherer",
"proto-2654" =>"Citizen",
"proto-612" =>"Dwarf",
"proto-2298" =>"Fishing Ship Norse",
"proto-2663" =>"Fishing Ship Atlantean",
"proto-2299" =>"Fishing Ship Egyptian",
"proto-786" =>"Fishing Ship Greek",
"proto-2676" =>"Llama Caravan",
"proto-2317" =>"Camel Caravan",
"proto-2657" =>"Citizen (Hero)",
"proto-2316" =>"Donkey Caravan",
"proto-763" =>"Ox Caravan",
// Military
"proto-2687" =>"Turma",
"proto-2216" =>"Throwing Axeman",
"proto-610" =>"Raiding Cavalry",
"proto-2233" =>"Toxotes",
"proto-609" =>"Slinger",
"proto-2658" =>"Murmillo",
"proto-592" =>"Spearman",
"proto-2217" =>"Ulfsark",
"proto-2230" =>"Hippikon",
"proto-2228" =>"Hoplite",
"proto-2220" =>"Hersir",
"proto-2685" =>"Katapeltes",
"proto-607" =>"Axemen",
"proto-2683" =>"Arcus",
"proto-611" =>"Camelry",
"proto-2674" =>"Cheiroballista",
"proto-2237" =>"Priest (Hero)",
"proto-2688" =>"Turma (Hero)",
"proto-2241" =>"Chariot Archer",
"proto-2232" =>"Peltast",
"proto-2670" =>"Contarius",
"proto-2659" =>"Destroyer",
"proto-2218" =>"Huskarl",
"proto-2229" =>"Hypaspist",
"proto-2276" =>"Jarl",
"proto-2678" =>"Fanatic",
"proto-2231" =>"Prodromos",
"proto-2664" =>"Bireme",
"proto-2661" =>"Murmillo (Hero)",
"proto-2242" =>"Myrmidon",
"proto-2246" =>"Mercenary",
"proto-2264" =>"Mercenary Cavalry",
"proto-2283" =>"Longboat",
"proto-2465" =>"War Elephant",
"proto-2213" =>"Portable Ram",
"proto-2713" =>"Oracle (Hero)",
"proto-699" =>"Siege Tower",
"proto-2222" =>"Pharaoh",
"proto-2282" =>"Kebenit",
"proto-2684" =>"Arcus (Hero)",
"proto-2235" =>"Petrobolos",
"proto-778" =>"Trireme",
"proto-2595" =>"Gastraphetes",
"proto-2686" =>"Katapeltes (Hero)",
"proto-2675" =>"Fire Siphon",
"proto-987" =>"Kataskopos (Scout)",
"proto-757" =>"Catapult",
"proto-694" =>"Ballista",
"proto-2671" =>"Contarius (Hero)",
"proto-2296" =>"Jason (Hero)",
"proto-2311" =>"Ajax (Hero)",
"proto-2244" =>"Hetairoi",
"proto-2309" =>"Theseus (Hero)",
"proto-2660" =>"Destroyer (Hero)",
"proto-2226" =>"Chiron (Hero)",
"proto-2225" =>"Odysseus (Hero)",
"proto-2679" =>"Fanatic (Hero)",
"proto-2308" =>"Hippolyta (Hero)",
"proto-2236" =>"Helepolis",
"proto-2667" =>"Siege Bireme",
"proto-2359" =>"War Barge",
"proto-2221" =>"Achilles (Hero)",
"proto-2295" =>"Herecles (Hero)",
"proto-2357" =>"Ramming Galley",
"proto-2358" =>"Dragon Ship",
"proto-2312" =>"Atlanta (Hero)",
"proto-2353" =>"Juggernaut",
"proto-2666" =>"Fireship",
"proto-2310" =>"Bellerophon (Hero)",
"proto-2356" =>"Drakkar",
"proto-2352" =>"Pentekonter",
"proto-2313" =>"Perseus (Hero)",
"proto-2227" =>"Polyphemus (Hero)",
"proto-2578" =>"Lost Ship",
"proto-2561" =>"Second Pharaoh",
"proto-2414" =>"Theocrat",

//Myth Units
"proto-2697" =>"Promethean",
"proto-2703" =>"Automaton",
"proto-767" =>"Einherjar",
"proto-770" =>"Valkyrie",
"proto-711" =>"Anubite",
"proto-764" =>"Troll",
"proto-895" =>"Centaur",
"proto-2701" =>"Stymphalian Bird",
"proto-910" =>"Sphinx",
"proto-2693" =>"Behemoth",
"proto-1025" =>"Scorpion Man",
"proto-911" =>"Minotaur",
"proto-2700" =>"Satyr",
"proto-771" =>"Batlle Boar",
"proto-913" =>"Hydra",
"proto-766" =>"Mountain Giant",
"proto-650" =>"Cyclops",
"proto-738" =>"Frost Giant",
"proto-2694" =>"Caladria",
"proto-916" =>"Colossus",
"proto-2715" =>"Heka Gigantes",
"proto-2148" =>"Pegasus",
"proto-726" =>"Fire Giant",
"proto-2409" =>"Petsuchos",
"proto-2408" =>"Wadjet",
"proto-2632" =>"Hyena of Set",
"proto-2727" =>"Lampades",
"proto-906" =>"Manticore",
"proto-921" =>"Mummy",
"proto-2762" =>"Argus",
"proto-2631" =>"Gazelle of Set",
"proto-2401" =>"Fenris Wolf Brood",
"proto-2410" =>"Nemean Lion",
"proto-905" =>"Phoenix",
"proto-2636" =>"Giraffe of Set",
"proto-2633" =>"Crocodile of Set",
"proto-693" =>"Scarab",
"proto-908" =>"Chimera",
"proto-964" =>"Leviathan",
"proto-924" =>"Roc",
"proto-2635" =>"Rhinoceros of Set",
"proto-2149" =>"Scylla",
"proto-2699" =>"Servant",
"proto-2474" =>"Hippocampus",
"proto-2630" =>"Ape of Set",
"proto-768" =>"Kraken",
"proto-2433" =>"Phoenix Egg",
"proto-926" =>"Medusa",
"proto-2150" =>"Avenger",
"proto-2634" =>"Hippo of Set",
"proto-2741" =>"Nereid",
"proto-2484" =>"Jormund Elver",
"proto-2706" =>"Man O' War",
"proto-769" =>"War Turtle",
"proto-2483" =>"Carcinos",
"proto-2735" =>"Phoenix",

//GPs
"tech-756" =>"Valor",
"tech-762" =>"Titan Gate",
"tech-721" =>"Deconstruction",
"tech-730" =>"Shockwave",
"tech-499" =>"Prosperity",
"tech-520" =>"Spy",
"tech-554" =>"Forest Fire",
"tech-394" =>"Ancestors",
"tech-503" =>"Eclipse",
"tech-431" =>"Undermine",
"tech-752" =>"Hesperides",
"tech-306" =>"Dwarven Mine",
"tech-426" =>"Ceasefire",
"tech-518" =>"Bolt",
"tech-723" =>"Traitor",
"tech-734" =>"Gaia's Forest",
"tech-425" =>"Great Hunt",
"tech-532" =>"Sentinel",
"tech-527" =>"Plague of Serpents",
"tech-755" =>"Spider Lair",
"tech-528" =>"Lure",
"tech-529" =>"Healing Spring",
"tech-407" =>"Restoration",
"tech-735" =>"Tartarian Gate",
"tech-722" =>"Carnivora",
"tech-460" =>"Bronze",
"tech-521" =>"Flaming Weapons",
"tech-555" =>"Pestilence",
"tech-724" =>"Chaos",
"tech-535" =>"Walking Woods",
"tech-539" =>"Son of Osiris",
"tech-437" =>"Rain",
"tech-760" =>"Implode",
"tech-134" =>"Frost",
"tech-399" =>"Underworld Passage",
"tech-530" =>"Curse",
"tech-747" =>"Vortex",
"tech-533" =>"Shifting Sands",
"tech-538" =>"Plenty",
"tech-116" =>"Fimbulwinter",
"tech-141" =>"Locust Swarm",
"tech-543" =>"Meteor",
"tech-516" =>"Earthquake",
"tech-536" =>"Vision",
"tech-534" =>"Ragnarok",
"tech-537" =>"Citadel",
"tech-455" =>"Nidhogg",
"tech-117" =>"Lightning Storm",
"tech-118" =>"Tornado",

// Minor gods
"md-0" =>"Athena",
"md-1" =>"Ares",
"md-2" =>"Hermes",
"md-3" =>"Dionysos",
"md-4" =>"Apollo",

"md-5" =>"Aphrodite",
"md-6" =>"Hera",
"md-7" =>"Artemis",
"md-8" =>"Hephaestus",
"md-9" =>"Anubis",

"md-10" =>"Bast",
"md-11" =>"Ptah",
"md-12" =>"Hathor",
"md-13" =>"Nephthys",
"md-14" =>"Sekhmet",

"md-15" =>"Thoth",
"md-16" =>"Osiris",
"md-17" =>"Horus",
"md-18" =>"Forseti",
"md-19" =>"Heimdall",

"md-20" =>"Freyja",
"md-21" =>"Skadi",
"md-22" =>"Bragi",
"md-23" =>"Njord",
"md-24" =>"Hel",

"md-25" =>"Baldr",
"md-26" =>"Tyr",
"md-27" =>"Okeanus",
"md-28" =>"Prometheus",
"md-29" =>"Leto",

"md-30" =>"Hyperion",
"md-31" =>"Theia",
"md-32" =>"Rheia",
"md-33" =>"Helios",
"md-34" =>"Hekate",
"md-35" =>"Atlas",

//major gods
"civ-0" =>"Zeus",
"civ-1" =>"Poseidon",
"civ-2" =>"Hades",
"civ-3" =>"Isis",
"civ-4" =>"Ra",
"civ-5" =>"Set",
"civ-6" =>"Odin",
"civ-7" =>"Thor",
"civ-8" =>"Loki",
"civ-9" =>"Kronos",
"civ-10" =>"Oranos",
"civ-11" =>"Gaia",


);

$output = $array[$input];
if(!$output) { $output = $input; }

return $output; 
}


}
?>