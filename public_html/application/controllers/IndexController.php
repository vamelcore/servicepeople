<?php
session_start();
class IndexController implements IController{
	protected $_data;
	private $_postData = array();
	private static $_instanceUser;

	function __construct(){
		$this->_data['action'] = "View".ucfirst(FrontController::getInstance()->getAction());
	}
	
	public function getInstance(array $data){
		if(isset($data['action'])){
			self::$_instanceUser = new Index($data);
		}else{
			throw new Exception("THIS ROLE NOT FOUND");
		}
	}
	
	protected function setPostData($data){
		$this->_postData = $data;
	}
	
	protected function getPostData(){
		return $this->_postData;
	}
	
	protected function getParamsAddresString(){
		return FrontController::getInstance()->getParams();
	}
	
	protected function getHTMLAddresString(){
		return FrontController::getInstance()->getPageRequest();
	}

	// генерируем хэш
	protected function getHash($pwd, $salt, $iteration){
		for($i = 0; $i < $iteration; $i++)
			$pwd = sha1($pwd . $salt);
		return $pwd;
	}	
	// генератор слуайных строк
	protected function generateRandomString($length = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
	}
	
	// функция logout
	protected function logout(){
		if(isset($_SESSION['user'])) unset($_SESSION['user']);
		header("Location:/");
	}	

/* --- ACTIONS --- */	
	public function IndexAction() {
		$this->getInstance($this->_data);
	}
	
	public function LoginAction() {
		$this->getInstance($this->_data);
	}
	
	public function MycabinetAction() {
		$this->getInstance($this->_data);
	}
	
	public function DocsAction() {
		$this->getInstance($this->_data);
	}
	
	public function MoneypushAction() {
		$this->getInstance($this->_data);
	}

	public function LogoutAction() {
		$this->logout();
	}

// Универсальная функция запроса в БД
	protected function CallToDB($met, $params){
		if($this->rc->implementsInterface('IInterface') && $this->rc->getName() == 'Index'){
			return Db::getInstance()->requestToDb($met.'Index', $params);
		}
	}
	
}