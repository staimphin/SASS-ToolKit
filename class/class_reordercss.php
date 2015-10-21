<?php 
/**
 * FILE :class_reordercss.php
 *  CLASS NAME: ReorderCSS
 * CSS Optimization function
 *
 */

class ReorderCSS
{
	private $_CSS;
	private $_reordored;
	private $_file;
	private $_destination;
	private $_endl;
	private $_tab;
	private $_space;
	private $_master;
	
	private $_in_class=0;
	private $_in_media=0;
	private $_mediaQuery=array(0=>'');
	private $_in_comment=0;
	private $_comment;
	
	//contain the working data
	private $_return=array();
	private $_class='';
	private $_multiline='';
	private $_MEDIA_KEY=0;
	private $_level=0;
	
	private $_closing;
	private $_trim=0;
	
	
	public function __construct($file, $destination='')
	{
		$this->_file=$file;
		$this->_destination=$destination;
		$this->set_option_order();
	}
	
	public function reorder( $option=0)
	{
		switch($option){
			case 0: default: 
				$this->_endl ="\r\n";
				$this->_tab="\t";
				$this->_space=" ";
				$this->_closing="\r\n";
			break;
			case 1: 
				$this->_endl ="";
				$this->_closing="\r\n";
			break;
			case 2:
				$this->_endl ="";
				$this->_trim=1;	
			break;
		}
	
		$this->_CSS= $this->readCSS();
		$this->reorderCSS();
		$this->writeCSS();
	}

	public function readCSS(){
			if(file_exists($this->_file))
		{
			$sourcefile= fopen($this->_file, "r");
			$inside=0;
			$return=array();
			$options=array();

			if ($sourcefile):// read all the file
				while (!feof($sourcefile)){
					$line = fgets (  $sourcefile ) ;
					$line=trim($line);
					if (mb_strpos($line,'/')===0 && substr_count($line,'/')>=1){//inside comment
						if (substr_count($line,'/*')==1||substr_count($line,'//')==1 ){
							$this->_in_comment=1;
						}
					}elseif (substr_count($line,'@media')>0 || substr_count($line,'@-webkit')>0 || substr_count($line,'@keyframes')>0 ) {
						if ( $this->_in_comment==0  ){//inside media
							$tmp =explode('{',$line);
							$this->_MEDIA_KEY=$tmp[0];
							if(!isset($this->_mediaQuery[$this->_MEDIA_KEY])){
								$this->_mediaQuery[$this->_MEDIA_KEY] ='';
							}
							$this->_in_media=1;
						}
					}
					/*  INSIDE CONTENT */
					if($this->_in_comment===0  && $this->_in_media===0){//
						$this->get_class_status($line);
					} else {	/* INSIDE COMMENT OR　 MEDIAQUERY*/
						if($this->_in_media==1 && $this->_in_comment!=1 && trim($line)!=''){// IN MEDIA QUERY 
							$this->set_class($line);
							if (substr_count($line,'{')==1){//READING the class
								$this->_level = $this->_level +1;
							} else {
							//$this->debug($line, "}");
								$found= $this->extract($line);
								$this->_mediaQuery[$this->_MEDIA_KEY][$this->_class][$found['CLASS']]= $found['VALUE'];
								}
						} elseif($this->_in_comment==1){
							$this->_comment .=$line."\r\n";
						}
						if (substr_count($line,'*/')==1||substr_count($line,'//')==1 ){
							$this->_in_comment=0;	
						}elseif(substr_count($line,'}')==1){
							//$this->debug($line, "}");
							$this->_level = $this->_level - substr_count($line,'}');
							/* addd at home*/
							if($this->_in_media==1){
								}
							}elseif(substr_count($line,'}')>1){
								$this->_level = $this->_level - substr_count($line,'}');
								if($this->_in_media==1){
									$this->_in_media=0;
									$this->_MEDIA_KEY=0;
								}
							}
					}
				}

				fclose( $sourcefile);
				$this->_mediaQuery[0]=$this->_return;
				//print_r($this->_mediaQuery);
				//print_r($this->_return);
				return $this->_mediaQuery;
			endif;
		}else{echo "path:".$this->_file." : Not Found";}
	}
	
	private function set_class($line)
	{
		if (substr_count($line,'{')>=1){
			$this->_level = $this->_level +(substr_count($line,'{'));
			if (substr_count($line,'@media')==0 ){
				$tmp=explode("{",$this->_multiline.$line);
				$this->_class=urlencode(trim($tmp[0]));
			}
			return true;
		}
	}

	private function debug($line, $patern)
	{
		if (substr_count($line,$patern)==1){
			echo "[DEBUG] $patern found! : level =".$this->_level." Media=".$this->_MEDIA_KEY." inMedia=".$this->_in_media."class=".$this->_class."  inClass= ".$this->_in_class."<br>\r\n"
			.$line."<br>\r\n";
		}
	}


	public function get_class_status($line)
	{	
		if ($this->set_class($line)==1){//READING the class
			//$this->_level = $this->_level +1;
			$this->_in_class=1;
			$this->_multiline='';
		} elseif (substr_count($line,'}')>=1){ // CLOSING  THE CLASS
			$this->_level = $this->_level -(substr_count($line,'}'));
			if(strlen($line)>4){
				$line=str_replace('}','',$line);
				$this->setData($line);
			}
			if($this->_in_media==1){
			
			} else {
				$this->_in_class=0;
			}
			if($this->_comment!=''){
				$this->_return[$this->_class]['COMMENT']=$this->_comment."\r\n";
				$this->_comment='';
			}	
		}	elseif($this->_in_class==1){//INSIDE CLASS
				$this->setData($line);
			}elseif($line!='' && mb_strpos($line,'@')=== false ){// list of class
				$this->_multiline.=$line;
		}
	}
	
	private function setData ($line)
	{
		$insert=$this->extract($line);
		$this->_return[$this->_class][$insert['CLASS']]=$insert['VALUE'];
	}
	
	public function extract($line){
		$items= explode(";",$line);
		$maxItem = count($items);
		for($i=0; $i < $maxItem; $i++){
			$items[$i]=trim(str_replace(';','',$items[$i]) );
			if(trim($items[$i])!=''){	
				$single= explode(":",$items[$i]);
				return array('CLASS'=> $single[0] , 'VALUE'=>" ".trim($single[1]).";");
			}
		} 
	}
	/* to delete*/
	public function readCSS_old()
	{
		if(file_exists($this->_file))
		{
			$sourcefile= fopen($this->_file, "r");
			$inside=0;
			$insideComment=0;
			$insideClass=0;
			$insideSpecial=0;
			$return=array();
			$options=array();
			$class='';
			$comment='';
			$multiline='';
			$optionIdent=" ";
			if ($sourcefile):// read all the file
				while (!feof($sourcefile)){
					$line = fgets (  $sourcefile ) ;
					$line=trim($line);
					//echo "$line<br>\r\n";
					if (mb_strpos($line,'/')===0 ){
						if (substr_count($line,'/*')==1||substr_count($line,'//')==1 ){
							$insideComment=1;
							$this->_in_comment=1;
						}//inside comment
					}elseif (mb_strpos($line,'@')===0 ) {
						if (substr_count($line,'@media')==1 ){
					//	echo "INSIDE @media<br>";
							$insideSpecial=1;
							$tmp =explode('{',$line);
							$MEDIA_KEY=$tmp[0];
							$this->_in_media=1;
						}//inside comment
					}
					//echo "**[DBG]: INSIDE COMMENT :($insideComment)<br>\r\n";
					
					/*  INSIDE CONTENT */
					
					if($insideComment===0 && $insideSpecial===0){
						/* DEBUG PURPOSE*/
						$newtest=$this->extractClass($multiline.$line);
						//print_r($newtest);/* find item listed yet */
						
						$case =  $this->lineCase($line);
						//echo "[ case ]".$case."<br>";
						//is it a class?
						
						switch($case){
						case 0: // one line class
							
						break;
						case 1: //start of class
						
						break;
						case 2: //End of class
							$insideClass=0;
							if($comment!=''){
								//echo "Inside class:".$comment."\r\n";
								$return[$class]['COMMENT']=$comment;
								$comment='';
							}
						break;
						}
						//replacement
						//extractClass($multiline.$line);
						//endofreplacement
						
						/* READING the class */
						/* BEGINING*/
						if (substr_count($line,'{')==1){
							$tmp=explode("{",$multiline.$line);
							$class=urlencode(trim($tmp[0]));
							//one line class?
							if (substr_count($line,'}')==1){
		//echo "**[DBG] END of class<br>\r\n";
								$tmp[1]=str_replace('}','',$tmp[1]);
								if (substr_count($line,';')>=1){
									$newLine= explode(";",$tmp[1]);
								} else {
									$tmp[1]=str_replace(';','',$tmp[1]);
									$newLine[0]=$tmp[1];
								}
							//	echo $line;
							//	print_R($newLine);
								for($c=0; $c< count($newLine); $c++){
									$single=explode(":",$newLine[$c]);
									
									if(count($single)>0 && isset($single[1])){
									//print_R($single);
										$return[$class][$single[0]]=$optionIdent.trim($single[1]).";";
									}
								}

								if($comment!=''){
									//echo "Inside class:".$comment."\r\n";
									$return[$class]['COMMENT']=$comment;
									$comment='';
								}
							} else {
								/* INSIDE */
								$insideClass=1;
							}
						$multiline='';
						} 
						/*  CLOSING  THE CLASS*/
						elseif (substr_count($line,'}')==1){
					//	echo "**[DBG] END of line<br>\r\n";
							if(strlen($line)>4){
						//	echo $line;
								$line=str_replace('}','',$line);
								if (substr_count($line,':')==1){

									$line=str_replace(';','',$line);
									$single= explode(":",$line);
									$return[$class][$single[0]]=$optionIdent.trim($single[1]).";";
								} else {
									$items= explode(";",$line);
									$maxItem = count($items);
									for($i=0; $i < $maxItem; $i++){

										$items[$i]=trim(str_replace(';','',$items[$i]) );
										if(trim($items[$i])!=''){	
									//	echo "*[DBG] FIND multi lines*[".$items[$i]."] <br>\r\n";
										$single= explode(":",$items[$i]);
										
											$return[$class][$single[0]]=$optionIdent.trim($single[1]).";";
										}
									}
									//print_R($return);
								}
							}
							$insideClass=0;
							if($comment!=''){
								//echo "Inside class:".$comment."\r\n";
								$return[$class]['COMMENT']=$comment;
								$comment='';
							}
							
						}
						 /* INSIDE CLASS*/
						elseif($insideClass==1){/*is it an option*/
							$tmp=explode(":",$line);
							if(count($tmp)>1){
								$tmp[1]=str_replace(';','',$tmp[1]);
								$return[$class][$tmp[0]]=$optionIdent.trim($tmp[1]).";";
							}
						}elseif($line!='' && mb_strpos($line,'@')=== false ){// list of class
							$multiline.=$line;
						}
					}
					/* INSIDE COMMENT OR　 MEDIAQUERY*/
					else { //inside comment
					//	echo "*INSIDE*<br>";
						if($this->_in_media==1 && $this->_in_comment!=1){
							if(substr_count($line,$MEDIA_KEY)==0){
								$extracted=$this->extractClass($line);
								//echo "$line<br>\r\n";
								//print_r($extracted);
								$this->_mediaQuery[$MEDIA_KEY][]=$line."\r\n";
							}
						} elseif($this->_in_comment==1){
							//$comment .=$line."\r\n";
						}
						$comment .=$line."\r\n";
						//echo$comment."<br>";
						if (substr_count($line,'*/')==1||substr_count($line,'//')==1 ){
							$insideComment=0;	
							$this->_in_comment=0;	
						//echo "*END OF COMMENT*<br>";
						}
						//end of comment comment : MUST　BE ABLE to found mediaquery inside comments!!!!
						elseif(substr_count($line,'}')>=1){
							$insideSpecial=0;
							$this->_in_media=0;
							//echo "*END OF MEDIA*<br>";
							}
					}
				}
				//print_r($return);
				fclose( $sourcefile);
				print_r($this->_mediaQuery);
				return $return;
			endif;
		}else{echo "path:".$this->_file." : Not Found";}
	}

	private function set_option_order()
	{
		/* defines the CSS element order */
		$this->_master=array(
		'-webkit-appearance',
		/* animation*/
		'-webkit-animation',
		'-webkit-animation-delay',		
		'-webkit-animation-direction',
		'-webkit-animation-duration',
		'-webkit-animation-fill-mode',
		'-webkit-animation-iteration-count',
		'-webkit-animation-name',
		'animation',
		'animation-delay',	
		'animation-direction',
		'animation-duration',
		'animation-fill-mode',	
		'animation-iteration-count',
		'animation-name',
		/* background */
		'background',
		'background-attachment',
		'background-color',
		'background-clip',
		'background-image',
		'background-position',
		'background-repeat',
		'background-size',
		
		/* positioning */
		'position',
		'top',
		'right',
		'bottom',
		'left',
		'float',
		'clear',
		'align-items',
		'vertical-align',
		'z-index',
		
		/* テキスト　関係 */
		'color',
		'font',
		'font-family',
		'font-style',
		'font-size',
		'font-weight',
		'line-height',	
		'text-align',
		'text-decoration',
		'text-indent',
		'text-shadow',
		'text-transform',
		'text-justify',
		'content',
		'justify-content',
		'-moz-hyphens',
		'-ms-hyphens',
		'-webkit-hyphens',	
		'hyphens',
		'page-break-before',
		'white-space',
		'word-break',
		'word-wrap',
		
		/* Spacing */
		'margin',
		'margin-top',
		'margin-right',
		'margin-bottom',
		'margin-left',
		'padding',
		'padding-top',
		'padding-right',
		'padding-bottom',
		'padding-left',
		
		/* List */
		'list-style',
		'list-style-type',
		
		/* Border */	
		'border',
		'border-top',
		'border-right',
		'border-bottom',
		'border-left',
		'border-collapse',
		'border-color',
		'border-image-width',
		'-moz-border-radius',
		'-ms-border-radius',
		'-o-border-radius',
		'-webkit-border-radius',
		'border-radius',
		'-webkit-border',
		'-webkit-border-horizontal-spacing',
		'-webkit-border-vertical-spacing',
		'-webkit-border-spacing',
		'border-top-right-radius',
		'border-top-left-radius',
		'border-bottom-right-radius',
		'border-bottom-left-radius',
		'border-spacing',
		'outline',

		/* FLEX - Box */
		'flex-direction',
		'-moz-box-align',
		'-moz-box-orient',
		'-moz-box-pack',
		'-moz-box-sizing',
		'-webkit-box-align',
		'-webkit-box-orient',
		'-webkit-box-pack',
		'-webkit-box-sizing',
		'box-sizing',
		'-moz-box-shadow',	
		'-webkit-box-shadow',
		'box-shadow',

		/* display*/
		'display',
		'height',	
		'max-height',
		'min-height',
		'width',	
		'max-width',
		'min-width',
		'-khtml-opacity',
		'-moz-opacity',
		'-ms-filter',
		'opacity',
		'filter',/* For IE8 and earlier */
		'overflow',	
		'overflow-x',	
		'overflow-y',	
		'table-layout',
		
		'-moz-user-select',
		'-ms-user-select',	
		'-webkit-user-select',
		'user-select',
		'visibility',

		/* animation */
		'-moz-transition',
		'-ms-transition',	
		'-o-transition',	
		'-webkit-transition',
			'transition',
		'-webkit-transform',
		'transform',
		/* その他*/
		'-webkit-tap-highlight-color',
		'-webkit-text-size-adjust',	
		'-webkit-touch-callout',
		'cursor',
		'list-style',
		'zoom',
		'_zoom',
		);
	}
	
	private function get_option_order($option)
	{
		$max= count($this->_master);
		for($i=0; $i < $max; $i++){
			if($this->_master[$i]==trim($option)){ return $i;}
		} 
		// echo $option."<br>";
	}

/* reoder the CSS content according to setup */
	 private function reorderCSS()
	{
		$mediaTotal = count($this->_CSS);
		$MediaKeys= array_keys($this->_CSS);
		sort($MediaKeys);
		//print_r($MediaKeys);
		$reordored = array();
		//print_r($MediaKeys);
		for($a=0; $a < $mediaTotal; $a++){
			$count = count($this->_CSS[$MediaKeys[$a]]);
			$classKeys= array_keys($this->_CSS[$MediaKeys[$a]]);
			//print_r($classKeys);
			
			for($i=0; $i < $count; $i++){// $count
				$TMP=array();
				$subkey= array_keys($this->_CSS[$MediaKeys[$a]][$classKeys[$i]]);
				$valmax=count($subkey);
				for($j=0; $j < $valmax; $j++){
	//echo "<br>\r SUBKEY=".$subkey[$j];
					$pos=$this->get_option_order($subkey[$j]);
					/*debug*/
					if($pos == 0 && $subkey[$j]!= $this->_master[0] && $subkey[$j]!="COMMENT" ){
						echo " please add to list: ".$subkey[$j]."!<br>\r\n";
					}
					
					if($subkey[$j]=="COMMENT"){
						$TMP["COMMENT"]= $this->_CSS[$MediaKeys[$a]][$classKeys[$i]][$subkey[$j]].$this->_endl;
					} else {
						$TMP[$pos]= trim($subkey[$j]).':'.$this->_CSS[$MediaKeys[$a]][$classKeys[$i]][$subkey[$j]].$this->_endl;
					}
	//echo " Position should be ".$pos."<br>\r\n";
				}
				
				ksort($TMP);
			//	echo "<br>\r CLASS=".$classKeys[$i]."<br>\r\n";
				//print_r($TMP);
				$reordored[$MediaKeys[$a]][$classKeys[$i]]=$TMP;
				
			}
			//print_r($reordored);
		}

//echo "<br>REORDERED<br>\r\n";
	//	print_r($reordored);
		$this->_reordored= $reordored;
	}
	
	private function isSingle($value, $text, $not='')
	{
		 if($value>1){
			return $text;
		 } else {return $not;}
	}
	
	private function writeCSS( )
	{
	$header='@charset "UTF-8";'.$this->_closing.$this->_endl;
	//echo "**[destfile]=(".$this->_destination.")";
	//print_r($CSS);
	$file=fopen($this->_destination, "w+");
		if (is_writable($this->_destination)){
			$mediaTab= "";
			//echo "write to:[".$this->_destination."]";
			fwrite( $file,$header);
			//print_R($this->_reordored);
			$max = count($this->_reordored);
			$mediaQuery= array_keys($this->_reordored);
			for($a=0; $a < $max; $a++){
				$count = count($this->_reordored[$mediaQuery[$a]]);
				$classKeys= array_keys($this->_reordored[$mediaQuery[$a]]);
				if($mediaQuery[$a] !== 0){
				//echo "** writting ".$mediaQuery[$a]."<br>\r\n";
					fwrite( $file,$mediaQuery[$a]." {".$this->_endl);
					$mediaTab = $this->_tab;
				}
					
				for($i=0; $i < $count; $i++){
					$TMP='';
					$className=$classKeys[$i];
					$class= array_keys($this->_reordored[$mediaQuery[$a]][$className]);
					if(isset($this->_reordored[$mediaQuery[$a]][$className]['COMMENT'])){
					
						if($this->_trim==1){
							$TMP .= str_replace("\r\n","" ,$this->_reordored[$mediaQuery[$a]][$className]['COMMENT']);
						}else {
							$TMP.=$this->_reordored[$mediaQuery[$a]][$className]['COMMENT'];
						}
					}
					
					$valmax=count($class);
					$TMP.=$mediaTab.urldecode($className).$this->_space."{".$this->isSingle($valmax, $this->_endl);

					for($j=0; $j < $valmax; $j++){
						if($class[$j]!=="COMMENT"){
							$TMP.=$mediaTab.$this->isSingle($valmax, $this->_tab). trim($this->_reordored[$mediaQuery[$a]][$className][$class[$j]]).$this->isSingle($valmax, $this->_endl);
						} 
					}
					
					$TMP.=$mediaTab.$this->isSingle($valmax, $this->_tab)."}". $this->_closing;
					if($valmax> 1){
						$TMP.= $this->isSingle($valmax, $this->_endl);
					}
				
					fwrite( $file,$TMP);
				}
				if($mediaQuery[$a]!== 0){
					fwrite( $file,"}".$this->_closing.$this->_endl);
				}
			
			
			}
			

		}
	}
/* not so usefull*/
	 public function lineCase($string)
	{
			if (substr_count($string,'{')==1 && substr_count($string,'}')==1){
				return 0;
			}
			if (substr_count($string,'{')==1){
				return 1;
			}
			if (substr_count($string,'}')==1){
				return 2;
			}
	}

	public function  extractClass($string)
	{
		$startPos=mb_strpos($string,"{");
		$stopPos=mb_strpos($string,"}");
		$lenght=mb_strlen($string);
		$tmp=array("start"=>0,"stop"=>0,"class"=>'',"options"=>'',"value"=>'',"comment"=>'',);
		if($startPos!== false){
			$tmp['start']=1;
			$class=mb_substr($string,0,$startPos);
			$tmp['class']=$class;
		}
		if($stopPos!== false){
			$tmp['stop']=1;
		}
		$text=mb_substr($string,$startPos,$stopPos);
		if(trim($text)!=''){
			if (substr_count($string,';')>=1){
				$newLine= explode(";",$string);
				for($c=0; $c< count($newLine); $c++){
					$single=explode(":",$newLine[$c]);
					
					if(count($single)>0 && isset($single[1])){
					//print_R($single);
						$tmp['options'][]=$single[0];
						$tmp['value'][]=$single[1];
					}
				}
			}
			return $tmp;
		}
		
		$tmp=mb_substr($string,$startPos,$lenght-$stopPos );
		//echo "*$string*find class: $class || $tmp<br>\r\n";
	}
} 

/** END of Object*/
?>