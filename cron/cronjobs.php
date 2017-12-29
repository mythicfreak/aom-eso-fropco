<?
//==================================================
// Cron Jobs Sheet
// Not open to the General Public
// by Kevin Kerr
//==================================================


$_GET['do'] = "CronInclude"; // Tells the Engine to STFU

// CHANGE THESE
include "../engine.php";
require "../mysql.php";

   mysql_connect($dbhost, $dbuser, $dbpass);
    mysql_select_db($database);

$cron = $_POST['cron'];
$game = $_POST['game'];
	
$CronEso = new CronEso;



switch ($cron) {
case "TopK":
$CronEso->DoTopK($game);
   break; 
   
 case "UpdateTrackers":
$CronEso->UpdateTrackers();
   break;   
        
default:
// Die 
   break;
}


class CronEso {



function DoTopK($game) {
global $EsoEngine; 

$parser = xml_parser_create(); 

xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 


if ($game=="aom") {
$query = "http://207.46.203.115/AOM_RC0/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_RC0'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qer%20id='0'%20np='0'%20nn='1000'%20si='0'%20et='ZS_Human'%20md='ZS_Supremacy'%20rn='ZS_TopPlayers'%20tp='ZS_AllTime'/></clr>";
} 

elseif ($game=="aomx") {
$query = "http://207.46.203.186/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='time()'%20U='5'/><qer%20id='0'%20np='0'%20nn='1000'%20si='0'%20et='ZS_Human'%20md='ZS_Supremacy'%20rn='ZS_TopPlayers'%20tp='ZS_AllTime'/></clr>";
}
$data = implode("",file("$query")); 

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar); 
xml_parser_free($parser); 
$RowId = 0; // Simpler then dealing with 3 for mysql.     
$int = 3; // 3 deep again 

$timestamp = time();

  while ($int < count($i_ar['values'])+3) {
	   $int++;
	   $RowId++;
	   $player = $d_ar[$int]['attributes']['EntryName'];
	    
	   $rating = round($d_ar[$int]['attributes']['primaryrating'],2);
       $rating = number_format("$rating","2",".","");	 // Thanks Andy
		
	   $lstgame = $EsoEngine->ParseTime($d_ar[$int]['attributes']['date']); // Parse the ESO time
	   $lstgame = date("M.j.y - h:iA",$lstgame);
		 
		 $stylebool = is_int($int/2);  
		 if($stylebool) {$class="row1"; 
		 } else { $class = "row2"; }
		 


		$GetInfo = $EsoEngine->PlayerInfo($game, "ZS_Supremacy", "ZS_AllTime", $player, false);
	 
		 
		
		 		 $Row = $EsoEngine->parse_tmpl("TopListDetailed",array(
						
											"{name}"   => $d_ar[$int]['attributes']['EntryName'],
											"{rating}" => $rating,
											"{rank}"   => $d_ar[$int]['attributes']['rank'],
											"{lstgme}" => $lstgame,
											"{games}"  => $GetInfo['games'],
											"{winpct}" => $GetInfo['winpct'],
											"{wins}"  =>  $GetInfo['wins'],
											"{avgscore}"  =>  $GetInfo['avgscore'],
											"{favgod}" => $GetInfo['favgod'],
											"{gpct}" => $GetInfo['gpct'],
											"{losses}" => $GetInfo['losses'],
											"{class}"    => $class, 						
										    "{ver}"    => $game, 
											"{time}"    => $EsoEngine->GoEnglish($time,true), 
											"{type}"    => $EsoEngine->GoEnglish($type,true),
											"{avgtime}"   => $EsoEngine->GoTime($GetInfo['avgtime']),
											
											));
											
       
print $RowId;
	   
mysql_query("UPDATE top_$game SET row='$Row', timestamp='$timestamp' WHERE id='$RowId'") or die(mysql_error());



	}   



}

}



?>