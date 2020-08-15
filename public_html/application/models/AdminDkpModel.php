<?php
class AdminDkpModel extends Model{
	
	public function render($file) {
		/* $file - текущее представление */
		ob_start();
		include ADMIN_HEADER;
		include($file);
		include ADMIN_FOOTER;
		return ob_get_clean();
	}
	
}