<?php
/**
 * SASS view main menu
 * File: _sass_inc_select.php
 *
 *
 */
?>
	<select name="prj">
		 <option value="new">--NEW PROJECT --</option>
<?php for($i=0; $i < $max; $i++){
	$option=trim($LIST[$i]);
	?>
	 <option value="<?= $option;?>"<?= ($option== $CHK)?" selected":'';?>><?= $option;?></option>
<?php }?>	
	</select>