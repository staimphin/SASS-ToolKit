<?php
/**
 * SASS view main menu
 * File: _sass_inc_list.php
 *
 *
 */
?>
<ul>
		<li>
			<h2 class="tab">Make CSS</h2>
			<div><?php $this->scssInclude($this->_folder);?></div>
		</li>
	<?php for($i=0; $i < $max; $i++){
	if(!is_array($FILES[$i])){
	?>
		<li><h2 class="tab"><?= $FILES[$i];?></h2>
		<div><textarea name="<?= $FILES[$i];?>"><?php  showContent($this->_projectFolder .$this->_folder."/scss/_".$FILES[$i].".scss");?></textarea></div>
		</li>
	<?php }
	}?>
	</ul>