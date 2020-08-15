<?php
class DocsModel extends Model{
	public $docscontent;
	public function getContent(){
		if(!empty($this->docscontent)){
			echo $this->docscontent;
		}
	}
	public function getTitle(){
		if(!empty($this->docstitle)){
			echo $this->docstitle;
		}
	}
	public function getAllRegions(){
		if(!empty($this->regions)){
			$out = '';
			foreach($this->regions as $region){
				$out .= "<option value=".$region->regid.">".$region->name."</option>";
			}
			echo $out;
		}
	}
	public function render($files) {
		/* $file - текущее представление */
		ob_start();
		include $this->header;
		include($files);
		include $this->footer;
		return ob_get_clean();
	}
}