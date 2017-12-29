<?php

include ("http://eso.fropco.com/jpgraph/src/jpgraph.php");
include ("http://eso.fropco.com/jpgraph/src/jpgraph_line.php");

$parser = xml_parser_create(); 
xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0); 
$ver = $_GET['ver'];
$game = $_GET['game'];
$unit = $_GET['unit'];

// 96208df2-fa8e-f74b-b8e9-04f74e3cc665

$ver = "aomx";
$query = "http://72.3.239.130/AOM_XPACK/query/query.aspx?<clr><cmd%20v='query'/><co%20g='AOM_XPACK'%20s='100'%20z='1.0.3'%20t='1065358143'%20U='6'/><qgs%20id='0'%20gi='$game'/></clr>";

$data = implode("",file("$query"));

xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar);

$Num = $d_ar[$i_ar['ZS_PL']['0']]['attributes']['v'];

if($unit=="M")
{
$pot = "ZS_MPOT";
$echo = "Military";
}
if($unit=="C")
{
$pot = "ZS_CPOT";
$echo = "Civillian";
}

$graph = new Graph(904,400,"auto");	
$graph->img->SetMargin(40,160,20,40);
$graph->SetScale("linlin");
//$graph->SetY2Scale("log");


$graph->SetMarginColor('#DFD5B7');

$graph->ygrid->Show(true,true);
$graph->xgrid->Show(true,false);

for($n=0; $n < $Num; $n++) {
$pot1 = $d_ar[$i_ar[$pot][$n]]['attributes']['v'];
$rr = array(")", "(");
$nn = array(" ", "");

$pot2= str_replace($rr, $nn, $pot1);
$exp = explode(" ", $pot2);

$counted[$n] = count($exp)-1; 

}

rsort($counted);
for($n=0; $n < $Num; $n++) 
{
$cpot = $d_ar[$i_ar[$pot][$n]]['attributes']['v'];

$replace = array(")", "(");
$new = array(" ", "");

$cpot2= str_replace($replace, $new, $cpot);
$cexp = explode(" ", $cpot2);



//print "$cexp[$n] ";
for($int=0; $int < $counted[0]; $int++) 
{
list($x,$y) = explode(",",$cexp[$int]); 
$x=$x/2;

//print "|$n - $x| ";

$xdata[$int] = $x;
$ydata[$int] = $y;

}

$lineplot[$n]=new LinePlot($ydata,$xdata);

$graph->Add($lineplot[$n]);

}

$graph->title->Set("$echo Population");
$graph->xaxis->title->Set("Time");
$graph->yaxis->title->Set("Population");

$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

if($lineplot[0]) {
$lineplot[0]->SetColor("blue");
$lineplot[0]->SetWeight(2);
$lineplot[0]->SetLegend($d_ar[$i_ar['ei']['0']]['attributes']['v']);
}
if($lineplot[1]){
$lineplot[1]->SetColor("red");
$lineplot[1]->SetWeight(2);
//$b = $i_ar['ZS_Player']['0'] +3;
$lineplot[1]->SetLegend($d_ar[$i_ar['ei']['2']]['attributes']['v']);
}
if($lineplot[2]){
$lineplot[2]->SetColor("green");
$lineplot[2]->SetWeight(2);
$lineplot[2]->SetLegend($d_ar[$i_ar['ei']['4']]['attributes']['v']);
}
if($lineplot[3]){
$lineplot[3]->SetColor("teal");
$lineplot[3]->SetWeight(2);
$lineplot[3]->SetLegend($d_ar[$i_ar['ei']['6']]['attributes']['v']);
}
if($lineplot[4]){
$lineplot[4]->SetColor("purple");
$lineplot[4]->SetWeight(2);
$lineplot[4]->SetLegend($d_ar[$i_ar['ei']['8']]['attributes']['v']);
}
if($lineplot[5]){
$lineplot[5]->SetColor("yellow");
$lineplot[5]->SetWeight(2);
$lineplot[5]->SetLegend($d_ar[$i_ar['ei']['10']]['attributes']['v']);
}
if($lineplot[6]){
$lineplot[6]->SetColor("orange");
$lineplot[6]->SetWeight(2);
$lineplot[6]->SetLegend($d_ar[$i_ar['ei']['12']]['attributes']['v']);
}
if($lineplot[7]){
$lineplot[7]->SetColor("gray");
$lineplot[7]->SetWeight(2);
$lineplot[7]->SetLegend($d_ar[$i_ar['ei']['14']]['attributes']['v']);
}
if($lineplot[8]){
$lineplot[8]->SetColor("darkblue");
$lineplot[8]->SetWeight(2);
$lineplot[8]->SetLegend($d_ar[$i_ar['ei']['16']]['attributes']['v']);
}
if($lineplot[9]){
$lineplot[9]->SetColor("pink");
$lineplot[9]->SetWeight(2);
$lineplot[9]->SetLegend($d_ar[$i_ar['ei']['18']]['attributes']['v']);
}
if($lineplot[10]){
$lineplot[10]->SetColor("brown");
$lineplot[10]->SetWeight(2);
$lineplot[10]->SetLegend($d_ar[$i_ar['ei']['20']]['attributes']['v']);
}
if($lineplot[11]){
$lineplot[11]->SetColor("black");
$lineplot[11]->SetWeight(2);
$lineplot[11]->SetLegend($d_ar[$i_ar['ei']['22']]['attributes']['v']);
}


/*if($lineplot[4]){
$lineplot[4]->SetColor("yellow");
$lineplot[4]->SetWeight(2);
$lineplot[4]->SetLegend($d_ar[$i_ar['ei']['0']]['attributes']['v']);
}*/
//$lineplot2->SetColor("orange");
//$lineplot2->SetWeight(2);




$graph->legend->Pos(0.01,0.21,"right","top");
$graph->legend->SetColor("brown");

$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetTextTickInterval(2);

// Display the graph
$graph->Stroke();

xml_parser_free($parser);
?>
