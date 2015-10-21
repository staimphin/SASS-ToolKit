<?php 
/* functions.php
 *
 * 
 */
 //settings
 ob_start();
error_reporting(E_ALL);
ini_set('display_errors', true);
define('PROJECT',"project/");
define('SCSS',"scss/");
//$test = shell_exec('sass -v');//sass version

//print_r($_POST);
//include class
require_once("class/class_reordercss.php");
require_once("class/class_sass.php");
$projectFolder =trim("project");
$CSSName=trim($_POST['cssname']);
$dest=(isset($_POST['projectPath']))?trim($_POST['projectPath']) :'';
$prjName=(isset($_POST['prj']))?trim($_POST['prj']) :'';
$project= new sass($prjName,$CSSName,$dest,$projectFolder );
//print_r($_POST);


/*===== VALIDATION=======*/
/*==== MAIN ====*/



/*==== FUNCTION ====*/

?>