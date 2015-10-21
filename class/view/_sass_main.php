<?php 
/** * SASS view main menu * File: _sass_main.php * * */
?>
	<h1>SASS: Template Generator</h1>
	<p><?= $this->_message;?>
	</p>
	<form method="post" action="index.php" enctype="multipart/form-data">
		Select Project :<?php $this->listMenu($this->_PROJECTSLIST, $this->_CHK);?><br>
		New project Name:<input type="text" value="" name="outfolder">
<?php // COMPONENT SELECTOR _ check box: scss content files list
		if(isset($this->_folder)){ ?>
		<div id="tab">
<?php $this->scssTab();?>
		</div>
		<p>
			Custom Path<input type="text" name="projectPath" value="<?php if(isset($this->_custompath)) echo $this->_custompath;?>">
			CSS NAME<input type="text" name="cssname" value="<?=  ($this->_savename!='')?$this->_savename:$this->_folder;?>">
		</p>
<?php 
}?>
	<input type="submit" name="<?= $this->_step;?>">
</form>
<p><a href="http://127.0.0.1/template/index.php?CSS=<?= /* $folder."/".*/ $this->_savename.".css";?>" target="blank">view Template</a></p>
<p><a href="http://127.0.0.1/template/css/<?= /* $folder."/".*/ $this->_savename.".css";?>" target="blank">view CSS</a></p>