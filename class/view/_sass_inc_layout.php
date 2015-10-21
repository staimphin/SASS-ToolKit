<?php 
/**
 * SASS view main menu
 * File: _sass_inc_layout.php
 *
 *
 */
?>
	<h3>Layout type:</h3>
	<select name="style">
<?php for($j=0; $j < count($LIST); $j++){
?>
	 <option value="<?= str_replace(".scss","",$LIST[$j]['SCSS']);?>"<?=($LIST[$j]['SCSS']==$selected)? " selected":'';?>><?= str_replace(".scss","",$LIST[$j]['SCSS']);?></option>
<?php 
	}
?>	
	</select>