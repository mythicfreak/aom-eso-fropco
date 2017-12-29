<html>
<head>
<meta http-equiv='refresh' content='5'>
</head>
<body>
<?php
$parser = xml_parser_create();
xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); 
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
$query = "http://config.aom.eso.com/ConfigUS.aspx/?4874064l";
$data = implode("",file("$query"));
xml_parse_into_struct($parser,$data,&$d_ar,&$i_ar) or $this->PrintError("xml error!");
xml_parser_free($parser);
echo $d_ar['39']['attributes']['Population'];
?>
</body>
</html>