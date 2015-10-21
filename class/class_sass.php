<?php 
/**
 * SASS INTERFACE
 * COMPILATION INTERFACE
 *
 *
 */
 class sass
{
	public $_step="SEND";
	public $_message="";
	
	public $_savename;
	public $_custompath;
	
	private $_base;
	private $_CHK="";
	private $_PROJECTSLIST;

	public $_folder;/* PROJECT NAME*/
	private $_projectFolder="project";/* WHERE THE BASE SOURCE ARE PATH*/
	private $_SCSSFolder="scss";/* SASS file */
	
	private $_CSS_HEADER= '@charset "UTF-8";
	/* css.scss 
SCSS: STAIMPHIN Gregory
Version: 1.0
*/
@charset "utf-8";
// Modules and Variables';
	
	public function __construct($projectNAME ='',$CSSName="style",$destination='',$projectFolder ="project")
	{
		/* setup*/
		$this->_projectFolder =$projectFolder."/";
		//$this->_SCSSFolder=$SCSSFolder."/";
		$this->_savename=$CSSName;
		$this->_custompath=$destination;
		$this->_folder=$projectNAME;
		//	echo "GENE: ". $projectFolder."/          || ".$SCSSFolder."/";
		/* CREATE PROJECT */
		if(isset($_POST['SEND'])){
			$this->_step="GENERATE";
			$this->create();
		}
		if(isset($_POST['GENERATE'])){
			$this->_step="GENERATE";
			$this->generate();
		}
	}

	/* INTERNAL FUNCTIONS*/
/*  Allow auto compile by refreshing page */
	public function  wp_sass()
	{
		echo "SASS4WP: ". $this->_projectFolder."/          || ".$this->_SCSSFolder."/";
		//$current=$this->_projectFolder."scss";
		//$content=$this->makeSCSS($current,1);
		//echo $content; 
		$this->generate(1);
	}
	/* list the files from the working folder : shopuld be private*/
	public function listFiles ($FPATH, $noarray=0)
	{
		$result=array();
		//echo $FPATH;
		if ($handle = opendir($FPATH) ) {
		while (false !== ($entry = readdir($handle))) {
			//echo "DBG:path= $FPATH ". $entry. "<br>"; 
			if ($entry != "." && $entry != ".."){
				if (substr($entry, -4) == "scss"){
					if($noarray==0){$result[]= array("SCSS" =>$entry);}
				}else{$result[]= $entry;}
			}	
		} 
		closedir($handle);
		sort($result);
		return $result;
		}else {
			 return false;
		}
	}

	/* MAKE the SCSS MASTER FILE (include list)*/
	private function makeSCSS($current,$bypass=0)
	{
		$content=$this->_CSS_HEADER;
		$CHKFOLDER=$this->listFiles($current);
		for($i=0; $i < count($CHKFOLDER); $i++){
			if(!is_array($CHKFOLDER[$i])){
				$content.='//'.$CHKFOLDER[$i]."\r";
				$CHKLIST=$this->listFiles($current."/".$CHKFOLDER[$i]);
				//print_R($CHKLIST);
				for($j=0; $j < count($CHKLIST); $j++){
					if(is_array($CHKLIST[$j])){
						$TMP= explode(".",$CHKLIST[$j]['SCSS']);
						//echo $TMP[0].".".$TMP[1]."<br>";
						if(isset($_POST[$TMP[0]]) || $bypass==1){
							$content.="@import '".$CHKFOLDER[$i]."/".substr($TMP[0],1)."';\r\n";
							//echo "@import '".$CHKFOLDER[$i]."/".substr($TMP[0],1)."';\r";
							} elseif($CHKFOLDER[$i]=="styles") {
						//	echo "***[DBG]  FOKLDER style (".$CHKFOLDER[$i].") LOOKING FOR :".$_POST["style"] ." CHK is ".$TMP[0]."<br>";
								if($TMP[0] == $_POST["style"]){
								//echo "@import '".$CHKFOLDER[$i]."/".substr($TMP[0],1)."';\r";
									$content.="@import '".$CHKFOLDER[$i]."/".substr($TMP[0],1)."';\r\n";
								}
							}
					}
				}
			}
		}
		return $content;
	}

	public function generate($bypass=0)
	{
		/* should not be take from post */	
		$path=dirname(__FILE__)."/../".$this->_projectFolder.$this->_folder."/";
		$this->_message .= "FOLDER:".$this->_folder." || CSS FOLDER:".$this->_SCSSFolder." || PATH:".$this->_custompath." || base PATH=".$path."<br>\r\n";/*DBG*/
		$input="scss/css.scss";
		$output=$this->_savename.".css";
		
		// Generate the SCSS file
		$current=$path."scss";
		$content=$this->makeSCSS($current,$bypass);

		
	//	if(file_exists($destpath.$output)){ unlink($destpath.$output);}
		
		$destfile=fopen($path.$input,"w");
		if (is_writable($path.$input)){
			fwrite( $destfile,$content);
			fclose( $destfile);
			$destpath=(isset($this->_custompath))?"/var/www/".$this->_custompath:$path;
			$this->_message .= "[".$destpath."]<br>\r\n";/*DBG*/; 
		//compile
			echo "<br>1st save".$destpath.$output."<br>";
			$result = shell_exec('sass '.$path.$input.' '.$destpath.$output);
			//print_r($result );
			$file=$destpath.$output;
			echo $file; 
			/* clean CSS file */
			if (file_exists($file)){
				$cleaner = new ReorderCSS($file,$file);
				//$cleaner->reorder();
				$save=0;
				/*export for donwload*/
				if ( $save==1) {$this->exportFile($file);}	
			}else{
				$this->_message.= "File [$file] NOT FOUND";
			}
		}else{
			$this->_message.= "ERROR!";
		}
	}

	private function exportFile($file)
	{
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;/* */
	}

	public function create()
	{
		if(isset($_POST['prj'])){
			if($_POST['prj']=="new"){
				if(isset($_POST['outfolder'])){
					$this->_folder=$_POST['outfolder'];
					$this->createProject($this->_folder);
				}else{
					$this->_message.= "Name the project!";
				}
			}else{$this->_folder=$_POST['prj'];}
		}
	}

/*  MAKE new PROJECT */
/* should be new class*/
	 public function copyAll($current, $destination)
	 {
			$FILES=$this->listFiles($current);
			for($i=0; $i <count($FILES); $i++){
				$this->_message .= "Current:".$current."/".$FILES[$i]["SCSS"]."  || DEST:".$destination."/".$FILES[$i]["SCSS"]."<br>";
				if(isset($FILES[$i]["SCSS"])){
					copy($current."/".$FILES[$i]["SCSS"], $destination."/".$FILES[$i]["SCSS"]);
				}
			}
	}

	 public function createProject()
	 {
		if(file_exists($this->_projectFolder .$this->_folder)){
			$this->_message.= "Project exist (".$this->_projectFolder .$this->_folder.")<br>";
		}else{
			$this->_message.= "Project  (".$this->_projectFolder .$this->_folder."): DOESN'T EXIST. CREATING.";
			mkdir($this->_projectFolder .$this->_folder,0777);
			mkdir($this->_projectFolder .$this->_folder."/scss",0777);
			$FILES=$this->listFiles($this->_SCSSFolder);
			for($i=0; $i <count($FILES); $i++){
				if(!is_array($FILES[$i])){
					$current=$this->_SCSSFolder.$FILES[$i];
					$destination=$this->_projectFolder .$this->_folder."/scss/".$FILES[$i];
					$this->_message .= "making and copying: $destination<br>";
					mkdir($destination,0777);
					$this->copyAll($current, $destination);
				}
			}
		}
	}

/*  INTERFACE*/
	public function mainMenu()
	{
		$this->_PROJECTSLIST=$this->listFiles($this->_projectFolder,1);
		if(isset($this->_folder)){$this->_CHK=$this->_folder;}else{$this->_CHK="";}
		include(dirname(__file__)."/view/_sass_main.php");	

	}
	/* Interface Elements*/
	public function listMenu($LIST, $CHK="")
	{
		$max= count($LIST);
		include(dirname(__file__)."/view/_sass_inc_select.php");	
	}
	/* main list*/
	public function scssTab()
	{
		$FILES=$this->listFiles($this->_SCSSFolder);
		$max=count($FILES);
		$max=0;
		include(dirname(__file__)."/view/_sass_inc_list.php");	
	}

	public function makeCB($current)
	{
		$FILES=$this->listFiles($current);
		$TMP= explode("/",$current);
		$curFolder= $TMP[count($TMP)-1];
		$MAX=count($FILES);
		$CB='';
		for($i=0; $i <$MAX; $i++){
			if(isset($FILES[$i]["SCSS"])){
				$TMP=explode(".",$FILES[$i]["SCSS"]);
				$filename=$TMP[0];
				$CB .= "<label><input type='checkbox' name='".$filename."' value='".$filename."' checked>".$FILES[$i]["SCSS"]."<label>\r\n";
			}
		}
		include(dirname(__file__)."/view/_sass_inc_CB.php");
	}

	public function scssInclude()
	{
	//list the folder and files
		//echo "FOLDER = ( $this->_folder ):";
		$this->_base= $this->_projectFolder .$this->_folder."/scss/";
		$FILES=$this->listFiles($this->_SCSSFolder);
		$selected= (isset( $_POST['style']))?  $_POST['style'].".scss":'';

		for($i=0; $i <count($FILES); $i++){
			if(!is_array($FILES[$i])){
				$current=$this->_base.$FILES[$i];
				if($FILES[$i]=="styles"){
					$LIST=$this->listFiles($current);
					include(dirname(__file__)."/view/_sass_inc_layout.php");		
				}else {
					$this->makeCB($current);
				}
			}
		}
	}


}//end of class

/* load the scss files*/
	function showContent($filename)
	{
	$file = fopen($filename, "r");
	$tmp= "";
	echo $filename;
		$key;
	if ($file):
		while (!feof($file)):
			$line = fgets (  $file ) ;
			/*if ( preg_match("#^\n#" ,$line) == true)
			$line=preg_replace("#\n#" ,"" ,$line);
			$key= explode(",", $line);
			$tmp[]=$key;*/
			$tmp.=$line;
		endwhile;	
		echo $tmp;
	else:
		echo $filename." Not found, Skipped<br/> ";
	return false;
	endif;
	}
// generate the file



?>
