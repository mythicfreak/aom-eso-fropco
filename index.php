<? 
$player = $_GET['player']; $ver = $_GET['ver'];  $time = $_GET['time']; $type = $_GET['type'];
$offset = $_GET['offset']; $do = $_GET['do'];
$detailed = $_GET['detailed'];
$cook = $_COOKIE['fropco_eso_time_offset']; 
if($do=="SetTime"&&$offset) { $onload = '4'; SetTime($offset);  } 
elseif($do=="SetTime" || $do=="ShowTime") { $onload = '4';  }
elseif($do=="PlayerInfo") { $onload = '2'; } 
elseif($do=="TopList") { $onload = '3'; }
elseif($do=="RecentGames") { $onload = '5'; }
elseif(empty($do)&&empty($cook)) { $onload = '4'; }
else { $onload = '1'; }
   


$starttime = LoadTime();
 
//===========================================
// Sets our time in a cookie
//===========================================
function SetTime($offset) {
$ntime = time();

if(setcookie ("fropco_eso_time_offset",$offset, $ntime+23328000)==true) 
{
if ($offset > 0) { $plussign ="+"; }
if ($offset = 0) { $offset =""; }

$boolc = true;

}
else {
print("Could not set cookie"); 
$boolc = false;
}
header("Location: ?do=ShowTime&boolcook=$boolc"); 

}
  function TimeOffset($time)
{
$cookie = $_COOKIE['fropco_eso_time_offset'];

$cookie = $cookie * 3600;

$time = $time + 18000;

$time = $time + $cookie;

return "$time";

}
function LoadTime (){
$gettime = explode( " ", microtime());
$usec = (double)$gettime[0];
$sec = (double)$gettime[1];
return $sec + $usec;
}

 // Sets the Active tab!
?>  
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ESO Stats Page</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="eso.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Age of Mythology, Ensemble Studios, Norse, Greek, Gods, RTS, strategy, gaming, AoM, aomx, The Titans, Age of Mythology: The Titans, AoM:TT, games, akm, akm_clan, akm_ clan">
<meta name="description" content="ESO AoM and AoM:TT Ensemble Studios Online Realtime Stats">
<script language="javascript" type="text/javascript" src="domtab.js"></script>
<link rel="StyleSheet" href="basictabs.css" type="text/css" />
<SCRIPT LANGUAGE="JavaScript">
<!-- Original:  Gilbert Davis -->

<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->

<!-- Begin
function loadImages() {
if (document.getElementById) {  // DOM3 = IE5, NS6
document.getElementById('hidepage').style.visibility = 'hidden';
}
else {
if (document.layers) {  // Netscape 4
document.hidepage.visibility = 'hidden';
}
else {  // IE 4
document.all.hidepage.style.visibility = 'hidden';
      }
   }
}
//  End -->
</script>
<style type="text/css">
<!--
.copyright {
	font-size: 10px;
}
-->
</style>
</head>



<body  onload="loadImages(); domTab(<? print "$onload"; ?>)"><a name="top"></a><!-- necessary to send Netscape users back to the top -->
<!-- Show a little loading box -->


 <DIV id="hidepage" STYLE="position:relative;z-index:5;top:1%;left:35%;">
    <TABLE BGCOLOR="#000000" BORDER=1 BORDERCOLOR="silver" 
	CELLPADDING=0 CELLSPACING=0 HEIGHT="100" WIDTH="30%">
      <TR>
        <TD WIDTH="100%" HEIGHT="100%" BGCOLOR="black" ALIGN="CENTER" VALIGN="MIDDLE">
          <BR><BR>
          <FONT FACE="Verdana,Arial" SIZE=3 COLOR="silver">
		  <B>Loading data...  Please wait...</B></FONT> <BR> <BR>
        </TD>
      </TR>
    </TABLE>
  </DIV>
		

<span id="contentblock1"></span>


<?
echo "$cookieset";
 ?> 

<table width="975" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr > 
    <td height="79" colspan="3" class="logo"><div align="right" class="ad">
</div>
</td>
  </tr>
  <tr>
    <td class="left"><img src="img/top_left_bord.gif" width="16" height="16"></td>
    <td valign="top" class="topbord"></td>
    <td class="right"><img src="img/top_right_border.gif" width="16" height="16"></td>
  </tr>
  <tr> 
    <td class="left">&nbsp;</td>
    <td width="943" height="450" valign="top" class="midsect"><table  width="99%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr >
          <td width="572" align="left" class="tablink2">
		  <a href='javascript:domTab(1)' class="tablink" id="link1">Close Tab</a>
		   <a href='javascript:domTab(2)'  class="tablink" id="link2">Player Stats</a> 
	<a href='javascript:domTab(3)' class="tablink" id="link3">Top Players</a> 
	<a href='javascript:domTab(5)' class="tablink" id="link5">Recent Games</a> 
	<a href='javascript:domTab(4)' class="tablink" id="link4">Time Zone</a>
	 

	
	</td>
	

          <td  width="210" align="center"> 
		  <center class="nav">
<a href="http://akmclan.100webspace.net">Home</a> | <a href='index.php'>First Page</a>
</center>
</td>
        </tr>
      </table>
<br>
  
<form method="get" action="" id="contentblock2" class="tab">
          

          <!-- Start Player Table -->
          
        <table width="920" border="0" align="center" valign="middle" cellpadding="0" cellspacing="0">
          <tr > 
            <td width="120"><strong>Player Stats: </strong></td>
            <td width="190" align="left"><input name="player" value="<? print "$player"; ?>" type="text" size="25" class="form"></td>
            <td width="80" align="left" ><select name="ver" size="1" class="form">
                <? if($ver=="aom") { $aomsel="selected"; } ?>
                <? if($ver=="aomx") { $aomxsel="selected"; } ?>
                <option <? print "$aomxsel"; ?> value="aomx">AoM:TT</option>
              </select></td>
            <td width="120" align="left" ><select name="type" class="form">
			    <? if($type=="NotRated") { $cussel="selected"; } ?>
                <option <? print "$cussel"; ?> value="NotRated">Not Rated</option>
                <? if($type=="Supremacy") { $supsel="selected"; } ?>
                <option <? print "$supsel"; ?> value="Supremacy">Supremacy</option>
                <? if($type=="Conquest") { $coqsel="selected"; } ?>
                <option <? print "$coqsel"; ?> value="Conquest">Conquest</option>
                <? if($type=="Deathmatch") { $dmsel="selected"; } ?>
                <option <? print "$dmsel"; ?> value="Deathmatch">Death Match</option>
                <? if($type=="Lightning") { $lghsel="selected"; } ?>
                <option <? print "$lghsel"; ?> value="Lightning">Lightning</option>

          
			                  <? if($type=="All") { $alls="selected"; } ?>
                <option <? print  "$alls"; ?> value="All">All (Summary)</option>
         
			      </select>
			  </td>
            <td width="106" height="20" align="left"><select name="time" size="1" class="form">
                <? if($time=="AllTime") { $allsel="selected"; } ?>
                <option <? print "$allsel"; ?> value="AllTime">All Time</option>
                <? if($time=="Weekly") { $weksel="selected"; } ?>
                <option <? print "$weksel"; ?> value="Weekly">Weekly</option>
                <? if($time=="Monthly") { $monsel="selected"; } ?>
                <option <? print "$monsel"; ?> value="Monthly">Monthly</option>
              </select> <input name="do" type="hidden" value="PlayerInfo"> </td>
            <td width="150" align="left" valign="middle">
			 <? if($ForceSearch) { $ForceSearchSel="checked"; } ?>
			<input <? print "$ForceSearchSel"; ?> type="checkbox" name="ForceSearch" value="1">
              <em>Force Search?</em></td>
            <td width="272" height="30" align="right"><input type="submit" value="Submit"  class="form"></td>
          </tr>
        </table>
	  <!-- End Player Table -->
	
		    </form> 
		
			<form id="contentblock3" class="tab">
		 
          <!-- Start TopList Table -->
          
        <table width="920" border="0" align="center" valign="middle" cellpadding="0" cellspacing="0">
          <tr > 
            <td width="92"><strong>Top Players: </strong></td>
                  <td width="70" align="left">
			<select name="detailed" size="1" class="form" onChange="Tab()">
        <? if ($detailed) { $detsel = "selected"; 
		} else { $realsel = "selected"; } ?>
	            <option <? print "$realsel "; ?> value="0">Top1000 - Real Time</option>
			 
				     
			</select>
			</td>
		   
		    <td width="73" align="left" ><select name="ver" size="1" class="form">
                <? if($ver=="aom") { $aomsel="selected"; } ?>
                <? if($ver=="aomx") { $aomxsel="selected"; } ?>
                <option <? print "$aomxsel"; ?> value="aomx">AoM:TT</option>
              </select></td>
            <td width="96" align="left" ><select id="gtype" name="type" class="form">
                <? if($type=="Supremacy") { $supsel="selected"; } ?>
                <option <? print "$supsel"; ?> value="Supremacy">Supremacy</option>
                <? if($type=="Conquest") { $coqsel="selected"; } ?>
                <option <? print "$coqsel"; ?> value="Conquest">Conquest</option>
                <? if($type=="Deathmatch") { $dmsel="selected"; } ?>
                <option <? print "$dmsel"; ?> value="Deathmatch">Death Match</option>
                <? if($type=="Lightning") { $lghsel="selected"; } ?>
                <option <? print "$lghsel"; ?> value="Lightning">Lightning</option>
              </select></td>
            <td width="192" height="20" align="left"><select id="gtime" name="time" size="1" class="form">
                <? if($time=="AllTime") { $allsel="selected"; } ?>
                <option <? print "$allsel"; ?> value="AllTime">All Time</option>
                <? if($time=="Weekly") { $weksel="selected"; } ?>
                <option <? print "$weksel"; ?> value="Weekly">Weekly</option>
                <? if($time=="Monthly") { $monsel="selected"; } ?>
                <option <? print "$monsel"; ?> value="Monthly">Monthly</option>
              </select> 
			 
			   </td>
     
            <td width="275" height="30" align="right"><input type="submit" value="Submit"  class="form"></td>
          </tr>
        </table>
	<input name="do" type="hidden" value="TopList">
	  </form>
	  <!--End TopList-->
	  

	  
	  <?
	  // Call our Tab Function to kill off the 2 extra options when loading
	   if($detailed) {echo "<script> Tab(); </script>";} ?>
	  
	  <form id="contentblock4" class="tab">
		 
        	<!--Start Time List-->
          
        <table width="920" border="0" align="center" valign="middle" cellpadding="0" cellspacing="0">
          <tr > 
     
		   
		    <td width="130" align="left" ><strong>Set Time Zone:</strong> </td>
            <td width="600" align="left" >
			
		
			
		
<? // This actually checks the cookie,
   // Not  any GET value, That way.
   // we know it is working.
?>

			<select name="offset" size="1" class="form">
			
		       <? if($cook=="-12") { $n12="selected"; } ?>
                <option <? print "$n12"; ?> value="-12">GMT -12</option>
				
				 <? if($cook=="-11") { $n11="selected"; } ?>
                <option <? print "$n11"; ?> value="-11">GMT -11</option>
				
				<? if($cook=="-10") { $n10="selected"; } ?>
                <option <? print "$n10"; ?> value="-10">GMT -10</option>
				
				<? if($cook=="-9") { $n9="selected"; } ?>
                <option <? print "$n9"; ?> value="-9">GMT -9</option>
				
				<? if($cook=="-8") { $n8="selected"; } ?>
                <option <? print "$n8"; ?> value="-8">GMT -8</option>
				
				 <? if($cook=="-7") { $n7="selected"; } ?>
                <option <? print "$n7"; ?> value="-7">GMT -7</option>
				
				 <? if($cook=="-6") { $n6="selected"; } ?>
                <option <? print "$n6"; ?> value="-6">GMT -6</option>
			  
			  	 <? if($cook=="-5") { $n5="selected"; } ?>
                <option <? print "$n5"; ?> value="-5">GMT -5</option>
				
				 <? if($cook=="-4") { $n4="selected"; } ?>
                <option <? print "$n4"; ?> value="-4">GMT -4</option>
				
				<? if($cook=="-3") { $allsel="selected"; } ?>
                <option <? print "$n3"; ?> value="-3">GMT -3</option>
				
				<? if($cook=="-2") { $n2="selected"; } ?>
                <option <? print "$n2"; ?> value="-2">GMT -2</option>
				
				 <? if($cook=="-1") { $n1="selected"; } ?>
                <option <? print "$n1"; ?> value="-1">GMT -1</option>
			
				<? if($cook=="GMT00" || !$cook) { $zero="selected"; } ?>
                <option <? print "$zero"; ?> value="GMT00">GMT</option>
				
				<? if($cook=="1") { $p1="selected"; } ?>
                <option <? print "$p1"; ?> value="1">GMT +1</option>
				
				<? if($cook=="2") { $p2="selected"; } ?>
                <option <? print "$p2"; ?> value="2">GMT +2</option>
				
				 <? if($cook=="3") { $p3="selected"; } ?>
                <option <? print "$p3"; ?> value="3">GMT +3</option>
				
				<? if($cook=="4") { $p4="selected"; } ?>
                <option <? print "$p4"; ?> value="4">GMT +4</option>
				
				<? if($cook=="5") { $p5="selected"; } ?>
                <option <? print "$p5"; ?> value="5">GMT +6</option>
			  
			   <? if($cook=="6") { $p6="selected"; } ?>
                <option <? print "$p6"; ?> value="6">GMT +6</option>
				
				<? if($cook=="7") { $p7="selected"; } ?>
                <option <? print "$p7"; ?> value="7">GMT +7</option>
				
				 <? if($cook=="8") { $p8="selected"; } ?>
                <option <? print "$p8"; ?> value="8">GMT +8</option>
				
				 <? if($cook=="9") { $p9="selected"; } ?>
                <option <? print "$p9"; ?> value="9">GMT +9</option>
				
				 <? if($cook=="10") { $p10="selected"; } ?>
                <option <? print "$p10"; ?> value="10">GMT +10</option>

				 <? if($cook=="11") { $p11="selected"; } ?>
                <option <? print "$p11"; ?> value="11">GMT +11</option>

	
				<? if($cook=="12") { $p12="selected"; } ?>
                <option <? print "$p12"; ?> value="12">GMT +12</option>   
			
			
		
			</select> 
			  
			  </td>
      
     
            <td width="275" height="30" align="right">
			<input type="submit" value="Submit"  class="form"></td>
          </tr>
        </table>
	  <input name="do" type="hidden" value="SetTime">
	  </form>
	 

      <!-- End Time List -->
     
	  
	  <!-- Start TopList Table -->
          	<form id="contentblock5" class="tab">
        <table width="920" border="0" align="center" valign="middle" cellpadding="0" cellspacing="0">
          <tr > 
            <td width="120"><strong>Last 500 Games: </strong></td>
          
		   
		    <td width="73" align="left" ><select name="ver" size="1" class="form">
                <? if($ver=="aom") { $aomsel="selected"; } ?>
                <? if($ver=="aomx") { $aomxsel="selected"; } ?>
                <option <? print "$aomxsel"; ?> value="aomx">AoM:TT</option>
              </select></td>
            <td width="400" align="left" ><select id="gtype" name="type" class="form">
                <? if($type=="Supremacy") { $supsel="selected"; } ?>
                <option <? print "$supsel"; ?> value="Supremacy">Supremacy</option>
                <? if($type=="Conquest") { $coqsel="selected"; } ?>
                <option <? print "$coqsel"; ?> value="Conquest">Conquest</option>
                <? if($type=="Deathmatch") { $dmsel="selected"; } ?>
                <option <? print "$dmsel"; ?> value="Deathmatch">Death Match</option>
                <? if($type=="Lightning") { $lghsel="selected"; } ?>
                <option <? print "$lghsel"; ?> value="Lightning">Lightning</option>
              </select></td>
            
     
            <td width="275" height="30" align="right"><input type="submit" value="Submit"  class="form"></td>
          </tr>
        </table>
	<input name="do" type="hidden" value="RecentGames">
	  </form>
	  <!--End TopList-->
	  
      <table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td height="18"><? include "engine.php"; ?></td>
        </tr>
      </table>
     </td>
    <td class="right">&nbsp;</td>
  </tr>
  <tr> 
    <td valign="top"><img src="img/bottom_left_bord.gif" width="16" height="16"></td>
    <td height="16" class="bottombord">&nbsp;</td>
    <td valign="top"><img src="img/bottom_right_bord.gif" width="16" height="16"></td>
  </tr>
</table>
<table width="975" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="812" class="foot">Page loaded in 
      <? $endtime = loadtime(); $runtime = $endtime - $starttime; echo substr($runtime, 0, 5);?>
      Seconds on 
      <?
				$offset = $_COOKIE['fropco_eso_time_offset']; if(strlen($offset) > 3) { $offset =""; } 
				 $LoadTime = date("M.j.y - h:iA",TimeOffset(time()));
				print $LoadTime; print " GMT $offset"; 
				 ?>
    </td>
    <td width="163" align="right" valign="top" class="foot">Powered by ESO </td>
  </tr>
</table>

</body>
</html>

