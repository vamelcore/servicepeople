<?php
class FrontController{
	protected $_controller, $_action, $_params, $_body, $_userArray, $_pageRequest;
	static $_instance;

	public static function getInstance() {
		if(!(self::$_instance instanceof self)) 
			self::$_instance = new self();
		return self::$_instance;
	}
	private function __construct(){	
		if($_SERVER['REQUEST_METHOD']=='POST'){ $_POST = ClearData::clearArray($_POST); }
		if($_SERVER['REQUEST_METHOD']=='GET'){ $_POST = ClearData::clearArray($_GET); }
		if(strrpos($_SERVER['REQUEST_URI'], '.html') && $_SERVER['REQUEST_URI'] != '/index.html'){
			$this->_pageRequest = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
			$request = 'Index'.$_SERVER['REQUEST_URI'];
		}elseif(strrpos($_SERVER['REQUEST_URI'], '.')){
			$request = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '.'));
			$splits = explode('/', trim($request,'/'));		
		}else{
			$request = $_SERVER['REQUEST_URI'];
			$splits = explode('/', trim($request,'/'));
		}
	//Какой action использовать?
		$this->_action = !empty($splits[1]) ? $splits[1].'Action' : 'IndexAction';	
	//Какой сontroller использовать?
		if(!empty($splits[0])){
			if($splits[0] == ADMINPAGE){
				$this->_controller = 'IndexController';
			}else{
				$this->_controller = ucfirst($splits[0]).'Controller' ; 
			}
		}else{
			$this->_controller = 'IndexController';
		}
	//Есть ли параметры и их значения?
		if(!empty($splits[2])){
			$keys = $values = array();
				for($i=2, $cnt = count($splits); $i<$cnt; $i++){
					if($i % 2 == 0){
						//Чётное = ключ (параметр)
						$keys[] = $splits[$i];
					}else{
						//Значение параметра;
						$values[] = $splits[$i];
					}
				}
			if(!empty($keys) && !empty($values)){
				if(count($keys) == count($values)){
					$this->_params = array_combine($keys, $values);
				}else{
					throw new Exception("ERROR COUNT ALL PARAMS");
				}
			}
		}
	}
	public function route(){
		if(class_exists($this->getController())){
			$rc = new ReflectionClass($this->getController());
			if($rc->implementsInterface('IController') || $rc->implementsInterface('AController')){
				if($rc->hasMethod($this->getAction())){
					$controller = $rc->newInstance();
					$method = $rc->getMethod($this->getAction());
					$method->invoke($controller);
				}else{
					throw new Exception("NOT USE Action");
				}
			}else{
				throw new Exception("NOT USE Interface");
			}
		}else{
			throw new Exception("NOT USE Controller");
		}
	}
	
	public function getPageRequest() {
		return $this->_pageRequest;
	}
	public function getParams() {
		if (is_array($this->_params)){
			return ClearData::clearArray($this->_params);
		}elseif(is_numeric($this->_params)){
			return ClearData::clearInt($this->_params);
		}else{
			return ClearData::clearStr($this->_params);
		}
	}
	public function getController() {
		return $this->_controller;
	}
	public function getAction() {
		return $this->_action;
	}
	public function getBody() {
		return $this->_body;
	}
	public function setBody($body) {
		$this->_body = $body;
	}
}