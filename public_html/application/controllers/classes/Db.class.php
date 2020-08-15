<?
class Db{
	private static $_parse = null;	
	private $_db;
	private static $_instance;// здесь храним экземпляр класса
	private function __construct(){
		self::$_parse = parse_ini_file(INDEX_DB);
		if(self::$_parse){
			try{
				$this->_db = new PDO("mysql:host=".self::$_parse['host'].";dbname=".self::$_parse['dbname'],self::$_parse['user'],self::$_parse['pwd']);
				$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){
				throw new Exception(json_encode(['params'=>$e->getMessage(), 'request'=>$_SERVER['REQUEST_URI']]), 1);
			}
		}
		$this->_db->query('SET NAMES utf8');
	}
	private function __clone(){}
	public function __destruct(){
		unset($this->_db);
	}	
	public static function getInstance(){
		if(!self::$_instance instanceof self)
			self::$_instance = new self;
		return self::$_instance;
	}

	/* ------------------- */
	public function requestToDb($met, $params){
		$rc = new ReflectionClass(Db::getInstance());
		return $rc->hasMethod($met)?$this->$met($params):false;
	}
	
	// ФУНКЦИЯ ВОЗВРАЩАЕТ MAX DOC PART ID
	private function getMaxPartid($tbl, $fld){
		$sql = "SELECT MAX($fld) $fld FROM $tbl";
		$stmt = $this->_db->query($sql)or die('Error Get Max');
		$req = $stmt->fetch(PDO::FETCH_ASSOC);
		return $req[$fld] + 1;
	}
	
	private function checkPhoneIndex($phone){
		$sql = "SELECT * FROM clients WHERE phone=$phone";
		$stmt = $this->_db->query($sql)or die('Error Get clients');
		return $stmt->fetch(PDO::FETCH_ASSOC);		
	}
	
	private function getPageConfigIndex($name){
		$sql = "SELECT * FROM config WHERE name = '$name'";
		$stmt = $this->_db->query($sql)or die('Error Get Page Config');
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	private function checkPhoneIccIndex($login){
		$sql = "SELECT id, account FROM tarifs WHERE account =".$login." AND used=0";
		$stmt = $this->_db->query($sql)or die('Error Get Page Phone');
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	private function checkLastnameAdmin($lastname){
		$sql = "SELECT * FROM clients WHERE lastname='$lastname'";
		$stmt = $this->_db->query($sql)or die('Error Get lastname');
		return $stmt->fetch(PDO::FETCH_ASSOC);		
	}	
	
	private function checkFirstnameAdmin($array){
		$sql = "SELECT * FROM clients WHERE firstname='".$array['firstname']."' AND lastname='".$array['lastname']."'";
		$stmt = $this->_db->query($sql)or die('Error Get firstname');
		return $stmt->fetch(PDO::FETCH_ASSOC);		
	}	
	
	private function checkMiddlenameAdmin($array){
		$sql = "SELECT * FROM clients WHERE firstname='".$array['firstname']."' AND lastname='".$array['lastname']."' AND middlename='".$array['middlename']."' ";
		$stmt = $this->_db->query($sql)or die('Error Get firstname');
		return $stmt->fetch(PDO::FETCH_ASSOC);		
	}	
	
	private function checkMobileAdmin($tel){
		$sql = "SELECT * FROM clients WHERE phone=".$tel;
		$stmt = $this->_db->query($sql)or die('Error Get phone');
		return $stmt->fetch(PDO::FETCH_ASSOC);		
	}
	
	private function addNewClientAdmin($arr){
		if($res = $this->checkPhoneIccIndex($arr['reglogin'])){
			$maxid = $this->getMaxPartid('clients', 'id');
			$sql = "INSERT INTO clients (id,login,password,salt,phone,firstname,middlename,lastname,passport_number,passport_seriya,passport_cod,passport_data,passport_organ,passport_birthday_city,passport_birthday_data,passport_issued,passport_registration,prop_oblast,prop_region,prop_city,prop_street,prop_house,prop_corpus,prop_appart,address_modem) VALUES(".$maxid.",'".$arr['reglogin']."','".$arr['password']."','".$arr['salt']."',".$arr['mobile'].",'".$arr['firstname']."','".$arr['middlename']."','".$arr['lastname']."',".$arr['passNumber'].",".$arr['passSeriya'].",'".$arr['passKod']."',".$arr['passData'].",'".$arr['passIssued']."','".$arr['passBirthdayCity']."',".$arr['passBirthdayData'].",'','',".$arr['propOblast'].",'".$arr['propRegion']."','".$arr['propCity']."','".$arr['propStreet']."','".$arr['propHouse']."','".$arr['propCorpus']."','".$arr['propAppart']."','".$arr['address']."')";
			if($this->_db->exec($sql) !== false){
				$sql = "UPDATE tarifs SET used = 1 WHERE id=".$res['id'];
				if($this->_db->exec($sql) !== false){
					$maxid = $this->getMaxPartid('statistics', 'id');
					$sql = "INSERT INTO statistics (id,login,data,event,status) VALUES(".$maxid.",'".$arr['reglogin']."',".time().",1,1)";
					if($this->_db->exec($sql) !== false){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
			exit;
		}else{
			return false;
		}
	}
	
	private function delClientAdmin($login){
		$sql = "DELETE FROM clients WHERE login=$login";
		if($this->_db->exec($sql) !== false){
			$sql = "DELETE FROM notifications WHERE login=$login";
			$this->_db->exec($sql);
			$sql = "DELETE FROM payments WHERE login=$login";
			$this->_db->exec($sql);
			$sql = "DELETE FROM sessions WHERE userlogin=$login";
			$this->_db->exec($sql);
			$sql = "UPDATE tarifs SET used = 0 WHERE account=$login";
			$this->_db->exec($sql);
			$sql = "DELETE FROM statistics WHERE login='$login'";
			$this->_db->exec($sql);
			if($this->_db->exec($sql) !== false){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	private function checkLoginIndex($login){
		$sql = "SELECT salt FROM clients WHERE login=".$login;
		$stmt = $this->_db->query($sql)or die('Error Check Login');
		return $stmt->fetch(PDO::FETCH_OBJ);		
	}
	
	private function checkPasswordIndex($array){
		$sql = "SELECT * FROM clients WHERE login=".$array['lgn']." AND password='".$array['pwd']."'";
		$stmt = $this->_db->query($sql)or die('Error Check Login');
		return $stmt->fetch(PDO::FETCH_OBJ);		
	}
	
	private function addClientSessionIndex($array){
		$sql = "SELECT id FROM sessions WHERE userlogin=".$array['userlogin'];
		$stmt = $this->_db->query($sql)or die('Error Check Sessions');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		if(!$res){
			$maxid = $this->getMaxPartid('sessions', 'id');
			$sql = "INSERT INTO sessions (id,userlogin,sessionid,data) VALUES(".$maxid.",'".$array['userlogin']."','".$array['sessionid']."',".time().")";
			return ($this->_db->exec($sql) !== false)?true:false;
		}else{
			$sql = "UPDATE sessions SET sessionid='".$array['sessionid']."', data=".time()." WHERE id=".$res->id;
			return ($this->_db->exec($sql) !== false)?true:false;
		}
	}
	
	private function checkClientSessionIndex($array){
		if(is_numeric($array['userlogin'])){
			$sql = "SELECT data FROM sessions WHERE userlogin=".$array['userlogin']." AND sessionid = '".$array['sessionid']."'";
			$stmt = $this->_db->query($sql)or die('Error Check Sessions');
			return $stmt->fetch(PDO::FETCH_OBJ);
		}
		return false;
	}
	
	private function getUserInfoIndex($login){
		$sql = "SELECT c.id, c.login, c.phone, c.firstname, c.middlename, c.lastname, t.phone as tphone, t.tarif, t.oplata FROM clients as c, tarifs as t WHERE t.account=c.login AND c.login=".$login;
		$stmt = $this->_db->query($sql)or die('Error Check Login');
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	private function getCountClientsAdmin(){
		$sql = "SELECT COUNT(*) AS allclients FROM clients";
		$stmt = $this->_db->query($sql)or die('Error Get Count');
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	private function getCountErrorsAdmin(){
		$sql = "SELECT COUNT(*) AS allerrors FROM errors";
		$stmt = $this->_db->query($sql)or die('Error Get Count');
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	private function getCountPaymentErrorsAdmin(){
		$sql = "SELECT COUNT(*) AS allerrors FROM payments WHERE status=0";
		$stmt = $this->_db->query($sql)or die('Error Get Count');
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	private function getCountGsmErrorsAdmin(){
		return $this->checkSuspendedPaymentsIndex(null)?1:0;
	}
	
	private function getClientsAdmin($login){
		if(is_null($login)){
			$sql = "SELECT c.login, c.firstname, c.middlename, c.lastname, t.phone FROM clients as c, tarifs as t WHERE c.login=t.account ORDER BY c.lastname";
			$stmt = $this->_db->query($sql)or die('Error Get Count');
			$clients = $stmt->fetchAll(PDO::FETCH_OBJ);
			if($clients){
				foreach($clients as $key=>$client){
					$sql = "SELECT data FROM statistics WHERE login=".$client->login;
					$stmt = $this->_db->query($sql)or die('Error Get Count');
					$res = $stmt->fetch(PDO::FETCH_OBJ);
					if($res){
						$clients[$key]->data = $res->data;
					}else{
						$sql = "SELECT data FROM payments WHERE login=".$client->login;
						$stmt = $this->_db->query($sql)or die('Error Get Count');
						$res = $stmt->fetch(PDO::FETCH_OBJ);
						$clients[$key]->data = $res->data;
						if($res){
							$maxid = $this->getMaxPartid('statistics', 'id');
							$sql = "INSERT INTO statistics (id,login,data,event,status) VALUES(".$maxid.",'".$client->login."',".$res->data.",1,1)";
							$this->_db->exec($sql);
						}
					}
				}
			}
			return $clients;
		}else{
			$sql = "SELECT c.*, c.phone as clientphone, t.*, t.phone as simka FROM clients as c, tarifs as t WHERE c.login=t.account AND c.login=".$login;
			$stmt = $this->_db->query($sql)or die('Error Get Count');
			$client = $stmt->fetch(PDO::FETCH_OBJ);
			if($client){
				$sql = "SELECT data FROM statistics WHERE login=".$client->login;
				$stmt = $this->_db->query($sql)or die('Error Get Count');
				$res = $stmt->fetch(PDO::FETCH_OBJ);
				if($res){
					$client->data = $res->data;
				}else{
					$sql = "SELECT data FROM payments WHERE login=".$client->login;
					$stmt = $this->_db->query($sql)or die('Error Get Count');
					$res = $stmt->fetch(PDO::FETCH_OBJ);
					$client->data = $res->data;
					if($res){
						$maxid = $this->getMaxPartid('statistics', 'id');
						$sql = "INSERT INTO statistics (id,login,data,event,status) VALUES(".$maxid.",'".$client->login."',".$res->data.",1,1)";
						$this->_db->exec($sql);
					}
				}
			}
			return $client;
		}		
	}
	
	private function getCountTarifsAdmin(){
		$sql = "SELECT used FROM tarifs";
		$stmt = $this->_db->query($sql)or die('Error Get Count');
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}
	
	private function getTarifsAdmin($id){
		if(is_null($id)){
			$sql = "SELECT * FROM tarifs ORDER BY used";
		}else{
			//$sql = "SELECT c.*, t.* FROM clients as c, tarifs as t WHERE c.login=t.account AND c.id=".$id;
		}
		$stmt = $this->_db->query($sql)or die('Error Get Count');
		return $stmt->fetchAll(PDO::FETCH_OBJ);		
	}
	
	private function checkTarifsAdmin($arr){
		$sql = "SELECT id, used FROM tarifs WHERE phone=".$arr['phone']." OR icc='".$arr['icc']."'";
		$stmt = $this->_db->query($sql)or die('Error Check Phone Icc');
		return $stmt->fetch(PDO::FETCH_ASSOC);	
	}
	
	private function addTarifAdmin($arr){
		$maxid = $this->getMaxPartid('tarifs', 'id');
		$sql = "INSERT INTO tarifs (id,used,account,operator,phone,icc,tarif,oplata) VALUES(".$maxid.",0,'".$arr[6]."','".$arr[0]."',".$arr[1].",'".$arr[2]."',".$arr[3].",".$arr[4].")";
		return ($this->_db->exec($sql) !== false)?true:false;		
	}
	
	private function changeTarifAdmin($arr){
		$sql = "UPDATE tarifs SET tarif=".$arr['fullsum'].",oplata=".$arr['oplatasum']." WHERE account='".$arr['account']."'";
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function delTarifAdmin($login){
		$sql = "DELETE FROM tarifs WHERE account=$login";
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function getClientPaymentsIndex($login){
		return $this->getClientPaymentsAdmin($login);
	}
	private function getClientPaymentsAdmin($login){
		if(!is_null($login)){
			$sql = "SELECT * FROM payments WHERE login=$login ORDER BY data DESC";
			$stmt = $this->_db->query($sql)or die('Error Get Payments');
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}		
	}
	
	private function getYandexParamsAdmin(){
		$sql = "SELECT * FROM yandex";
		$stmt = $this->_db->query($sql)or die('Error Get Yandex');
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}
	
	private function getQiwiParamsIndex($params){
		$sql = "SELECT param_value FROM qiwi WHERE param_name LIKE '%".$params."%'";
		$stmt = $this->_db->query($sql)or die('Error Get ACCESS TOKEN');
		return $stmt->fetch(PDO::FETCH_OBJ);		
	}
	
	private function getYandexParamsIndex($params){
		$sql = "SELECT param_value FROM yandex WHERE param_name LIKE '%".$params."%'";
		$stmt = $this->_db->query($sql)or die('Error Get ACCESS TOKEN');
		return $stmt->fetch(PDO::FETCH_OBJ);		
	}
	
	private function getMaxOrderIdIndex($params){
		return $this->getMaxPartid('payments', 'id');		
	}
		
	private function CheckOrderPaymentIndex($orderid){
		$sql = "SELECT p.status,t.phone,t.oplata FROM payments as p, tarifs as t WHERE p.login=t.account AND p.order_id=".$orderid;
		$stmt = $this->_db->query($sql)or die('Error Get status');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		return $res->status == 1?$res:false;
	}
	
	private function CheckOrderIndex($orderid){
		$sql = "SELECT id FROM payments WHERE order_id=".$orderid;
		$stmt = $this->_db->query($sql)or die('Error Get status');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		return $res->id?true:false;
	}
	
	private function CheckPhonePaymentIndex($orderid){
		$sql = "SELECT p.mobile_funds FROM payments as p WHERE p.order_id=".$orderid;
		$stmt = $this->_db->query($sql)or die('Error Get mobile_funds');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		return $res->mobile_funds == 1?true:false;
	}
	
	private function AddPaymentIndex($arr){
		$maxid = $this->getMaxPartid('payments', 'id');
		$sql = "INSERT INTO payments (id,login,data,sum,status,order_id,operation_id,mobile_funds) VALUES(".$maxid.",'".$arr['login']."',".time().",".$arr['withdraw_amount'].",1,".$arr['orderid'].",".$arr['operation_id'].",0)";
		return ($this->_db->exec($sql) !== false)?true:false;	
	}
	
	private function AddEmptyPaymentIndex($arr){
		$maxid = $this->getMaxPartid('payments', 'id');
		$sql = "INSERT INTO payments (id,login,data,sum,status,order_id,operation_id,mobile_funds) VALUES(".$maxid.",'".$arr['clientid']."',".time().",".$arr['tarif'].",0,".$maxid.",0,0)";
		return ($this->_db->exec($sql) !== false)?$maxid:0;	
	}
	
	private function UpdatePaymentStatusIndex($arr){
		$sql = "UPDATE payments SET status=1,operation_id=".$arr['operation_id']." WHERE login=".$arr['login']." AND order_id=".$arr['orderid'];
		return ($this->_db->exec($sql) !== false)?true:false;	
	}
	
	private function AddHttpNotificationIndex($arr){
		$maxid = $this->getMaxPartid('notifications', 'id');
		$sql = "INSERT INTO notifications (id,login,orderid,notification,data) VALUES(".$maxid.",'".$arr['login']."',".$arr['orderid'].",'".$arr['string']."',".time().")";
		return ($this->_db->exec($sql) !== false)?true:false;	
	}
	
	private function updateMobileFundsIndex($params){
		$sql = "UPDATE payments SET mobile_funds=".$params['status_code']." WHERE login=".$params['login']." AND order_id=".$params['orderid'];
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function addErrorIndex($arr){
		// ordercategory 1 - ошибки записи в базу
		// ordercategory 2 - ошибки проведения оплаты
		// ordercategory 3 - ошибки из яндекса
		// ordercategory 4 - ошибки ussd3
		$sql = "SELECT id FROM errors WHERE ordercategory=".$arr['ordercategory']." AND text LIKE '%".$arr['string']."%'";
		$stmt = $this->_db->query($sql)or die('Error Get ACCESS TOKEN');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		if($res == false){
			$maxid = $this->getMaxPartid('errors', 'id');
			$texterror = $arr['login'].':'.$arr['orderid'].':'.$arr['string'];
			$sql = "INSERT INTO errors (id,ordercategory,text,data) VALUES(".$maxid.",".$arr['ordercategory'].",'".$texterror.":',".time().")";
			return ($this->_db->exec($sql) !== false)?true:false;	
		}
		return true;
	}
	
	private function getAllLogsAdmin($logid){
		if(is_null($logid)){
			$sql = "SELECT * FROM errors ORDER BY data DESC";
			$stmt = $this->_db->query($sql)or die('Error Get Logs');
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}else{
			$sql = "SELECT c.*, c.phone as clientphone, t.*, t.phone as simka FROM clients as c, tarifs as t WHERE c.login=t.account AND c.login=".$login;
			$stmt = $this->_db->query($sql)or die('Error Get Count');
			return $stmt->fetch(PDO::FETCH_OBJ);
		}			
	}
	
	private function getPaymentLogsAdmin($logid){
		if(is_null($logid)){
			$sql = "SELECT * FROM payments WHERE status=0 OR mobile_funds=0 ORDER BY data DESC";
			$stmt = $this->_db->query($sql)or die('Error Get Logs');
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}		
	}
	
	private function delPaymentErrorAdmin($id){
		$sql = "DELETE FROM payments WHERE id=$id";
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function checkFirstPaymentIndex($login){
		$sql = "SELECT id FROM payments WHERE status=1 AND login=$login";
		$stmt = $this->_db->query($sql)or die('Error Check First Payment');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		return $res == false?1:0;
	}
	
	private function getStatisticsNewClientAdmin($date){
		$sql = "SELECT c.login, c.firstname, c.middlename, c.lastname, s.data, t.tarif FROM statistics as s, clients as c, tarifs as t WHERE c.login=s.login AND c.login=t.account AND s.data >= ".$date['startdate']." AND s.data <= ".$date['stopdate']." ORDER BY s.data DESC";
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		$res = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $res != false?$res:'none';
	}
	
	private function editClientAdmin($arr){
		$sql = "UPDATE clients SET ";
		$sql .= "firstname='".$arr['firstname']."', ";
		$sql .= "middlename='".$arr['middlename']."', ";
		$sql .= "lastname='".$arr['lastname']."', ";
		$sql .= "passport_number=".$arr['passport_number'].", ";
		$sql .= "passport_seriya=".$arr['passport_seriya'].", ";
		$sql .= "passport_cod='".$arr['passport_cod']."', ";
		$sql .= "passport_data=".$arr['passport_data'].", ";
		$sql .= "passport_organ='".$arr['passport_organ']."', ";
		$sql .= "passport_birthday_city='".$arr['passport_birthday_city']."', ";
		$sql .= "passport_birthday_data=".$arr['passport_birthday_data'].", ";
		$sql .= "prop_oblast=".$arr['prop_oblast'].", ";
		$sql .= "prop_region='".$arr['prop_region']."', ";
		$sql .= "prop_city='".$arr['prop_city']."', ";
		$sql .= "prop_street='".$arr['prop_street']."', ";
		$sql .= "prop_house='".$arr['prop_house']."', ";
		$sql .= "prop_corpus='".$arr['prop_corpus']."', ";
		$sql .= "prop_appart='".$arr['prop_appart']."', ";
		$sql .= "address_modem='".$arr['address_modem']."'";
		$sql .= " WHERE login='".$arr['userlogin']."'";
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function getSilentsAdmin($data){
		$sql = "SELECT login, firstname, middlename, lastname, phone FROM clients";
		$stmt = $this->_db->query($sql)or die('Error Get All Clients');
		$allclietns = $stmt->fetchAll(PDO::FETCH_OBJ);
		if($allclietns){
			foreach($allclietns as $key=>$client){
				$sql = "SELECT data FROM payments WHERE login=".$client->login." AND data >".$data;
				$stmt = $this->_db->query($sql)or die('Error Get Pay');
				$clientpay = $stmt->fetchAll(PDO::FETCH_OBJ);
				if($clientpay){
					unset($allclietns[$key]);
				}else{
					$sql = "SELECT MAX(data) data FROM payments WHERE login=".$client->login;
					$stmt = $this->_db->query($sql)or die('Error Get Pay');
					$client->pay = $stmt->fetch(PDO::FETCH_OBJ);
				}
			}
		}
		return $allclietns;
	}
	
	private function getCountSilentsAdmin($data){
		return count((array)$this->getSilentsAdmin($data));
	}
	
	private function addPriorityPaymentIndex($arr){
		// проверка на очередь всех платежей
		$sql = "SELECT id FROM priority WHERE status<3 AND orderid != ".$arr['orderid'];
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		$prorityActive = $stmt->fetchAll(PDO::FETCH_OBJ); // платежи в очереди
		$sql = "SELECT id, status FROM priority WHERE orderid=".$arr['orderid']." AND yandex_operationid=".$arr['yandex_operationid'];
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		$res = $stmt->fetch(PDO::FETCH_OBJ);
		if($res){ // если есть в очереди этот платеж
			return empty($prorityActive)?true:false;
		}else{ // если нет в очереди этого платежа
			$maxid = $this->getMaxPartid('priority', 'id');
			$sql = "INSERT INTO priority (id,login,orderid,phone,sum,yandex_operationid,status,data) VALUES(".$maxid.",".$arr['login'].",".$arr['orderid'].",".$arr['phone'].",".$arr['sum'].",".$arr['yandex_operationid'].",0,".time().")";
			if($this->_db->exec($sql) !== false){
				return empty($prorityActive)?true:false;
			}else{
				return false;
			}
		}
	}
	
	private function checkNextPriorityPaymentIndex($payid){
		$sql = "SELECT * FROM priority WHERE status=0";
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	private function checkSuspendedPaymentsIndex($payid){
		$sql = "SELECT * FROM priority WHERE status > 0 AND status < 3";
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		$ress = $stmt->fetchAll(PDO::FETCH_OBJ);
		if($ress){
			$i = 0;
			foreach($ress as $res){
				if($res->data + 900 < time()){
					$i ++;
				}
			}
			return $i > 0?true:false;
		}else{
			return false;
		}
	}
	
	private function changePriorityPaymentStatusIndex($arr){
		$sql = "UPDATE priority SET status=".$arr['status']." WHERE orderid=".$arr['orderid'];
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function getPriorityListAdmin($priorityid){
		$sql = "SELECT * FROM priority WHERE status > 0 AND status < 3";
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		$ress = $stmt->fetchAll(PDO::FETCH_OBJ);
		if($ress){
			foreach($ress as $key=>$res){
				if($res->data + 900 > time()){
					unset($ress[$key]);
				}
			}
			return $ress;
		}else{
			return false;
		}
	}
	
	private function delPriorityErrorAdmin($id){
		$sql = "UPDATE priority SET status=3 WHERE orderid=".$id;
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function checkFreeIccRegisterLk($login){
		return $this->checkFreeIccRegisterAdmin($login);
	}
	private function checkFreeIccRegisterAdmin($login){
		$sql = "SELECT id, account, icc FROM tarifs WHERE account =".$login." AND used=0";
		$stmt = $this->_db->query($sql)or die('Error Get Page Phone');
		$icc = $stmt->fetch(PDO::FETCH_ASSOC);
		return ($icc)?true:false;
	}
	
	private function getPagesIndex($pageurl){
		return$this->getPagesAdmin($pageurl);
	}
	private function getPagesLk($pageurl){
		return$this->getPagesAdmin($pageurl);
	}
	private function getPagesAdmin($pageurl){
		if(is_null($pageurl)){
			$sql = "SELECT * FROM pages ORDER BY title";
			$stmt = $this->_db->query($sql)or die('Error Get All Pages');
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}else{
			$sql = "SELECT * FROM pages WHERE url='$pageurl'";
			$stmt = $this->_db->query($sql)or die('Error Get Page');
			return $stmt->fetch(PDO::FETCH_OBJ);			
		}
	}
	
	private function editPageAdmin($arr){
		$sql = "UPDATE pages SET title = '".$arr['title']."', content = '".$arr['content']."' WHERE id=".$arr['pageid'];
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function addPageAdmin($arr){
		$maxid = $this->getMaxPartid('pages', 'id');
		$sql = "INSERT INTO pages (id,title,description,content,url) VALUES(".$maxid.",'".$arr['title']."','".$arr['title']."','".$arr['content']."','".$arr['url']."')";
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function delPageAdmin($pageid){
		$sql = "DELETE FROM pages WHERE id=$pageid";
		return ($this->_db->exec($sql) !== false)?true:false;
	}
	
	private function getNewClientsDkpAdmin($date){
		$sql = "SELECT c.firstname as name, 
									 c.middlename as secondname, 
									 c.lastname as surname, 
									 c.passport_number as doc_number, 
									 c.passport_seriya as doc_serial, 
									 c.passport_cod as doc_code,
									 c.passport_data as doc_date,
									 c.passport_organ as doc_organization,
									 c.passport_birthday_data as birthday,
									 c.prop_oblast as addr_region,
									 c.prop_region as addr_district,
									 c.prop_city as addr_city,
									 c.prop_street as addr_street,
									 c.prop_house as addr_house,
									 c.prop_corpus,
									 c.prop_appart as addr_apart,
									 c.phone as contact_phone, 
									 t.phone as msisdn 
									 FROM statistics as s, clients as c, tarifs as t WHERE c.login=s.login AND c.login=t.account AND s.data >= ".$date[0]." AND s.data <= ".$date[1]." ORDER BY s.data DESC";
		$stmt = $this->_db->query($sql)or die('Error Get Stats New Clients');
		$clients = $stmt->fetchAll(PDO::FETCH_OBJ);
		if($clients){
			foreach($clients as $key=>$client){
				if(!is_null($client->addr_region)){
					$sql = "SELECT reg_name FROM regions WHERE id=".$client->addr_region;
					$stmt = $this->_db->query($sql);
					$res = $stmt->fetch(PDO::FETCH_OBJ);
					if($res){
						$clients[$key]->addr_region = $res->reg_name;
					}
				}
			}
		}
		return $clients != false?$clients:false;
	}
	
	private function getRegionsAdmin($oblid){
		if(is_null($oblid)){
			$sql = "SELECT * FROM regions ORDER BY reg_name";
			$stmt = $this->_db->query($sql);
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}
	}
}