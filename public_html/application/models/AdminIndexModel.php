<?php
class AdminIndexModel extends Model{
	public $login, $salt, $iteration;
	
	public function getlogin(){
		echo $this->login;
	}
	
	public function getSalt(){
		echo $this->salt;
	}
	
	public function getIterator(){
		echo $this->iteration;
	}
	
	public function render($file) {
		/* $file - текущее представление */
		ob_start();
		include ADMIN_HEADER;
		include($file);
		include ADMIN_FOOTER;
		return ob_get_clean();
	}
	
}