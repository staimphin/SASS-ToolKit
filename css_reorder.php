<!DOCTYPE html>
<html dir="ltr" lang="ja">
	<head>
		<meta charset="UTF-8">
		<title>TEST</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="<?php   ?>" />
		<meta name="keywords" content="" />
		<!-- External files -->
		<link type="text/css" href="./sogostyle.css" rel="stylesheet" >
		<link rel="shortcut icon" href="/images/favicon/favicon.ico" />
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<!-- javascript 
		<script type="text/javascript" src="./js/jquery.js"></script>-->
	</head>
<?php 
require_once("functions.php");
	$folder=PROJECT."test";
	$path=$folder."/";
	$varpath=$path."scss/modules/";
	$csspath=$path."scss/partials/";
	$source=$path."style.css";
	$destination=$path."style_remixed.css";
// OPEN CSS FILE
/**
TO DO
upload CSS
and donwload new version
*/
echo "SOURCE file: $source<br />";
// OPEN CSS FILE
$CSS= new ReorderCSS($source, $destination);
//if(file_exists($source)){$file=$source ;}else{ echo "file $source not found";}
//else {$file=$csspath.'_footer.scss';}//master css

//echo $source;

//OPENVARS

//LIST CONST
$READ_CSS = $CSS->reorder();
//print_r($CSS);
/* reorder the options*/

//$READ_CSS2 = reorderCSS($READ_CSS);
//print_r($CSS2);
//$CSS->writeCSS($READ_CSS2,$destination )

?>
