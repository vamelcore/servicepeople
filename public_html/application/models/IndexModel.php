<?php
class IndexModel extends Model{
	
	public function render($files) {
		/* $file - текущее представление */
		ob_start();
		include $this->header;
		include($files);
		include $this->footer;
		return ob_get_clean();
	}
}