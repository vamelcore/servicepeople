<?php
session_start();
class LawController implements AController{
	protected $_data;
	private $_postData = array();
	private $_getData = array();
	private static $_obj;

	function __construct(){
		$this->_data['action'] = "View".ucfirst(FrontController::getInstance()->getAction());
		$this->_data['params'] = FrontController::getInstance()->getParams();
	}
	
	public function getInstance(array $obj){
		self::$_obj = new Law($obj);
	}
	protected function setPostData($data){
		$this->_postData = $data;
	}
	protected function setGetData($data){
		$this->_getData = $data;
	}
	protected function getPostData(){
		return $this->_postData;
	}
	protected function getGetData(){
		return $this->_getData;
	}

/* ----------- Логинимся ----------- */	
	// функция логина
	protected function loginPost($fromPost){
		if($fromPost['lgn'] && $fromPost['pwd'] && isset($fromPost['authen']) && ($fromPost['authen'] === '')){
			$arr = $this->checkUser($fromPost['lgn']); // получаем строку по логину
			if($arr){
				list($login, $password, $salt, $iteration) = explode(':',$arr);
				if($this->getHash($fromPost['pwd'], $salt, $iteration) == $password){
					$_SESSION['user'] = $fromPost['lgn'];
					$_SESSION['auth'] = strrev(base64_encode(serialize($arr)));
					$this->_data['action'] = "View".ucfirst(FrontController::getInstance()->getAction());
					$this->IndexAction();
				}else{
					if(isset($_SESSION['auth'])) unset($_SESSION['auth']);
					if(isset($_SESSION['user'])) unset($_SESSION['user']);
					$this->logout();
				}
			}else{
				if(isset($_SESSION['auth'])) unset($_SESSION['auth']);
				if(isset($_SESSION['user'])) unset($_SESSION['user']);
				$this->logout();
			}
		}else{
			if(isset($_SESSION['auth'])) unset($_SESSION['auth']);
			if(isset($_SESSION['user'])) unset($_SESSION['user']);
			$this->logout();
		}
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
	// проверка пользователей на наличие в файле паролей
	protected function checkUser($login){
	  // если нет файла пароля
			if(!is_file('./application/inc/.htpasswd')){
				throw new Exception('Файл пользователей не найден!');
				$this->LoginAction();	
			}else{
				$users = file('./application/inc/.htpasswd');
				foreach ($users as $user){
					if(strpos($user, $login) !== false){ // если в $user(строке) $login (strpos вернет позицию или FALSE) а логин встретитс¤ в 0 позиции и поэтому жесткое приведение типов		
						return $user; // вернет $user:$hash:$salt:$iteration
					}
				}
			}
			return false;
	}	
	// проверка сессии
	protected function checkSession($login){
		return (strrev(base64_encode(serialize($this->checkUser($login)))) == $_SESSION['auth'])?true:false;
	}
	// функция logout
	protected function logout(){
		if(isset($_SESSION['auth'])) unset($_SESSION['auth']);
		if(isset($_SESSION['user'])) unset($_SESSION['user']);
		header("Location:/law");
	}	

/* --- ACTIONS --- */	
	public function IndexAction() {
		$this->getInstance($this->_data);
	}
	
	public function SettingAction(){
		$this->getInstance($this->_data);
	}
	
	public function ClientsAction(){
		$this->getInstance($this->_data);
	}
	
	public function DkpAction(){
		$this->getInstance($this->_data);
	}
	
	public function TarifsAction(){
		$this->getInstance($this->_data);
	}
	
	public function YandexAction(){
		$this->getInstance($this->_data);
	}
	
	public function SilentsAction(){
		$this->getInstance($this->_data);
	}
	
	public function PagesAction(){
		$this->getInstance($this->_data);
	}
	
	public function LogsAction(){
		$this->getInstance($this->_data);
	}

	public function StatisticsAction(){
		$this->getInstance($this->_data);
	}
	
	public function LogoutAction() {
		$this->logout();
	}

	protected function getParamsAddresString(){
		return FrontController::getInstance()->getParams();
	}
// Универсальная функция запроса в БД
	protected function CallToDB($met, $params){
		if($this->rc->implementsInterface('AController') && $this->rc->getName() == 'Law'){
			return Db::getInstance()->requestToDb($met.'Admin', $params);
		}
	}
}