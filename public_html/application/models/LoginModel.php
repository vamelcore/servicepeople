<?php
class LoginModel extends Model{	
	public function render($file) {
		/* $file - текущее представление */
		ob_start();
		include ADMIN_HEADER_NOLOGIN;
		include($file);
		return ob_get_clean();
	}
}