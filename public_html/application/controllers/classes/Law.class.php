<?
session_start();

class Law extends LawController implements AController{
	private $_post, $_get, $_adress;
	public function __construct(array $data){
		if($_SERVER['REQUEST_METHOD']=='POST'){ $this->setPostData($_POST); }
		$this->_adress = $this->getParamsAddresString();
		$this->_post = $this->getPostData();
		$this->rc = new ReflectionClass($this);
		$rm = new ReflectionMethod($this, $data['action']);
		if(isset($_SESSION['auth']) && isset($_SESSION['user']) && $this->checkSession($_SESSION['user'])){
			$rm = new ReflectionMethod($this, $data['action']);
			$rm->invoke($this);				
		}else{
			if(isset($this->_post) && !empty($this->_post)){
				$this->loginPost($this->_post);
			}else{
				$model = new LoginModel();
				$output = $model->render(ADMIN_LOGIN);
				FrontController::getInstance()->setBody($output);
			}
		}
	}

/* ----------- МОДЕЛЬ ----------- */
	public function ViewIndexAction(){
		$model = new AdminIndexModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if($tarifs = $this->CallToDB("getCountTarifs", NULL)){
			$model->usedtarifs = $model->nousedtarifs = 0;
			$model->counttarifs = count($tarifs);
			foreach($tarifs as $tarif){
				if($tarif->used == 1)
					$model->usedtarifs++;
				else
					$model->nousedtarifs++;
			}
		}
		$output = $model->render(ADMIN_INDEX);
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewClientsAction(){
		$model = new AdminClientsModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			if(isset($this->_post['delClient']) && !empty($this->_post['delClient'])){
				echo $this->CallToDB("delClient", $this->_post['delClient']);exit;
			}
		}
		if($this->_adress && isset($this->_adress['view'])){
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if(isset($this->_post['editClient']) && !empty($this->_post['editClient'])){
					$data1 = explode('.', $this->_post['editClient']['passport_data']);
					$data2 = explode('.', $this->_post['editClient']['passport_birthday_data']);
					if(count($data1) == 3 && count($data2) == 3){
						$this->_post['editClient']['passport_data'] = mktime (0, 0, 1, $data1[1], $data1[0], $data1[2]);
						$this->_post['editClient']['passport_birthday_data'] = mktime (0, 0, 1, $data2[1], $data2[0], $data2[2]);
						echo $this->CallToDB("editClient", $this->_post['editClient']);exit;
					}else{
						echo 2; exit;
					}
				}
			}
			$model->oneclient = $this->CallToDB("getClients", $this->_adress['view']);
			$model->oneclient->prop_corpus = $model->oneclient->prop_corpus == 0?'':$model->oneclient->prop_corpus;
			$model->oneclient->prop_appart = $model->oneclient->prop_appart == 0?'':$model->oneclient->prop_appart;
			$model->oneclient->passport_birthday_data = empty($model->oneclient->passport_birthday_data)?'':date('d.m.Y',$this->oneclient->passport_birthday_data);
			$model->oneclient->passport_data = empty($model->oneclient->passport_data)?'':date('d.m.Y',$this->oneclient->passport_data);
			$model->regions = $this->CallToDB("getRegions", NULL);
			$model->payments = $this->CallToDB("getClientPayments", $this->_adress['view']);
			$output = $model->render(ADMIN_CLIENT);
		}elseif($this->_adress && isset($this->_adress['registration'])){
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				if(isset($this->_post['checkClientlogin']) && !empty($this->_post['checkClientlogin'])){
					echo json_encode($this->CallToDB("checkFreeIccRegister", $this->_post['checkClientlogin'])); exit;
				}else if(isset($this->_post['checkfio']) && !empty($this->_post['checkfio'])){
					$array = explode(' ', $this->_post['checkfio']);
					if(count($array) > 2){
						if(isset($array[0]) && !empty($array[0])){
							if($this->CallToDB("checkLastname", $array[0])){
								if($this->CallToDB("checkFirstname", array('lastname'=>$array[0], 'firstname'=>$array[1]))){
									echo ($this->CallToDB("checkMiddlename", array('lastname'=>$array[0], 'firstname'=>$array[1], 'middlename'=>$array[2])))?1:0;
								}else{
									echo 0;
								}
							}else{
								echo 0;
							}
						}
					}else{
						echo 2;
					}
					exit;
				}else if(isset($this->_post['checkmobile']) && !empty($this->_post['checkmobile'])){
					echo ($this->CallToDB("checkMobile", json_decode($this->_post['checkmobile'])))?1:0; exit;
				}
				if(isset($this->_post['addClient']) && !empty($this->_post['addClient']) && count($this->_post['addClient']) == 22){
					$array = [];
					foreach($this->_post['addClient'] as $data){ $array[$data[0]] = $data[1]; }
					if(isset($array['fio']) && !empty($array['fio']) && isset($array['mobile']) && !empty($array['mobile']) && isset($array['passSeriya']) && !empty($array['passSeriya']) && isset($array['passNumber']) && !empty($array['passNumber']) && isset($array['passKod']) && !empty($array['passKod']) && isset($array['passData']) && !empty($array['passData']) && isset($array['passBirthdayCity']) && !empty($array['passBirthdayCity']) && isset($array['passBirthdayData']) && !empty($array['passBirthdayData']) && isset($array['propOblast']) && !empty($array['propOblast']) && isset($array['propCity']) && !empty($array['propCity']) && isset($array['propHouse']) && !empty($array['propHouse']) && isset($array['reglogin']) && !empty($array['reglogin']) && isset($array['address']) && !empty($array['address']) && isset($array['password']) && !empty($array['password'])){
						unset($array['passwordRepeat']);
						$fio = explode(' ', $array['fio']);
						$array['lastname'] = isset($fio[0])?$fio[0]:'';
						$array['firstname'] = isset($fio[1])?$fio[1]:'';
						$array['middlename'] = isset($fio[2])?$fio[2]:'';
						unset($array['fio']);
						$array['salt'] = $this->generateRandomString();
						$array['password'] = $this->getHash($array['password'], $array['salt'], 100);
						$array['passData'] = strtotime($array['passData']);
						$array['passBirthdayData'] = strtotime($array['passBirthdayData']);
						echo $this->CallToDB("addNewClient", $array);
					}else{
						echo 0;
					}
					exit;
				}

			}
			$model->regions = $this->CallToDB("getRegions", NULL);
			$output = $model->render(ADMIN_CLIENT_REGISTRATION);
		}else{
			$model->clients = $this->CallToDB("getClients", NULL);
			$output = $model->render(ADMIN_CLIENTS);
		}
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewTarifsAction(){
		$model = new AdminTarifsModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if($this->_adress && isset($this->_adress['import']) && $this->_adress['import'] == 'new' && $model->isadmin){
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if(isset($_FILES['import']["type"])){
					$datapath = './data/';
					if(is_dir($datapath) && isset($_FILES['import']['name']) && isset($_FILES['import']['tmp_name'])){
						if(move_uploaded_file($_FILES['import']['tmp_name'], $datapath.$_FILES['import']['name'])){
							$file = [];
							foreach (file($datapath.$_FILES['import']['name']) as $numstr=>$line) {
								$arrline = str_getcsv($line, ';');
								if(count($arrline) == 5){
									$file[$numstr] = $arrline;
									$check = $this->CallToDB("checkTarifs", array('phone'=>$arrline[1],'icc'=>$arrline[2]));
									if($check){
										$file[$numstr]['used'] = $check['used'];
									}
								}else{
									echo 5; exit;
								}
							}
							echo json_encode($file);	
						}else{
							echo 4; exit;
						}
					}
					exit;
				}
				if(isset($this->_post['addTarifs']) && !empty($this->_post['addTarifs'])){
					foreach($this->_post['addTarifs'] as $key=>$tarif){
						$responce[$key] = explode(',',	$tarif);
						$responce[$key][6] = substr($responce[$key][1], -4).substr($responce[$key][2], -4);
						$responce[$key][5] = $this->CallToDB("addTarif", $responce[$key])?1:0;
					}
					echo json_encode($responce, JSON_UNESCAPED_UNICODE);
					exit;
				}
			}
			$output = $model->render(ADMIN_TARIFS_IMPORT);
		}else{
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if(isset($this->_post['changeTarif']) && !empty($this->_post['changeTarif']) && isset($this->_post['changeTarif']['account']) && !empty($this->_post['changeTarif']['account']) && isset($this->_post['changeTarif']['fullsum']) && !empty($this->_post['changeTarif']['fullsum']) && isset($this->_post['changeTarif']['oplatasum']) && !empty($this->_post['changeTarif']['oplatasum'])){
					echo $this->CallToDB("changeTarif", $this->_post['changeTarif'])?1:0; exit;
				}elseif(isset($this->_post['delTarif']) && !empty($this->_post['delTarif'])){
					echo $this->CallToDB("delTarif", $this->_post['delTarif'])?1:0; exit;
				}
			}
			$model->tarifs = $this->CallToDB("getTarifs", NULL);
			$output = $model->render(ADMIN_TARIFS);
		}
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewSettingAction(){
		$model = new AdminIndexModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		list($model->login, $password, $model->salt, $model->iteration) = explode(':',$this->checkUser($_SESSION['user']));
		if(isset($this->_post['update']) && count($this->_post) == 5){
			if(!empty(trim($this->_post['password']))){
				$this->_post['password'] = $this->getHash($this->_post['password'], $this->_post['salt'], $this->_post['iterator']);
				$newstr = $this->_post['login'].":".$this->_post['password'].":".$this->_post['salt'].":".$this->_post['iterator']."\n";
			}else{
				echo "<script language='javascript'>alert('Пароль не может быть пустым!'); window.location.href = '/law/setting/';</script>"; exit;
			}
			$str = '';
			$users = file('./application/inc/.htpasswd');
			foreach ($users as $user){
				$str .= (strpos($user, $this->_post['login']) === false)?$user:$newstr;
			}
  		if(file_put_contents('./application/inc/.htpasswd', $str)){
				echo "<script language='javascript'>alert('Профиль обновлен!'); window.location.href = '/law/';</script>";
			}else{
				echo "<script language='javascript'>alert('Ошибка обновления профиля'); window.location.href = '/law/setting/';</script>";
			}
			
			
		}
		$output = $model->render(ADMIN_SETTING);
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewYandexAction(){
		$model = new AdminYandexModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		$yandex = new RecursiveArrayIterator($this->CallToDB("getYandexParams", NULL));
		foreach(new ArrayIterator($yandex) as $key => $value) { $model->yandex[$value->param_name] = $value->param_value; }
		if($this->_adress && isset($this->_adress['token']) && isset($model->yandex) && !empty($model->yandex)){
			$model->addBlock = 'start';
			$output = $model->render(ADMIN_YANDEX);
		}else{
			$output = $model->render(ADMIN_YANDEX);
		}		
		FrontController::getInstance()->setBody($output);	
	}
	
	public function ViewLogsAction(){
		$model = new AdminLogsModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			if(isset($this->_post['delPaymentError']) && !empty($this->_post['delPaymentError'])){
				echo $this->CallToDB("delPaymentError", $this->_post['delPaymentError']); exit;
			}elseif(isset($this->_post['delPriorityError']) && !empty($this->_post['delPriorityError'])){
				echo $this->CallToDB("delPriorityError", $this->_post['delPriorityError']); exit;
			}
		}
		if($this->_adress){
			if(isset($this->_adress['error'])){
				switch($this->_adress['error']){
					case 'payments': $model->logs = $this->CallToDB("getPaymentLogs", NULL);
													 $output = $model->render(ADMIN_LOGS_PAYMENTS);
													 break;
					case 'priority': $model->logs = $this->CallToDB("getPriorityList", NULL);
													 $output = $model->render(ADMIN_LOGS_PRIORITY);
													 break;
				}
			}
		}else{
			$model->logs = $this->CallToDB("getAllLogs", NULL);
			$output = $model->render(ADMIN_LOGS);
		}
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewStatisticsAction(){
		$model = new AdminStatisticsModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if($this->_adress){
			if(isset($this->_adress['clients']) && !empty($this->_adress['clients'])){
				switch($this->_adress['clients']){
					case "joined": 
						if(isset($this->_post['findNewClient']) && isset($this->_post['daterange']) && !empty($this->_post['daterange'])){
							$this->_post['daterange'] = explode(' - ', $this->_post['daterange']);
							if(count($this->_post['daterange']) == 2){
								$this->_post['daterange']['startdate'] = explode('.', $this->_post['daterange'][0]);
								$this->_post['daterange']['startdate'] = mktime (0, 0, 1, $this->_post['daterange']['startdate'][1], $this->_post['daterange']['startdate'][0], $this->_post['daterange']['startdate'][2]);
								unset($this->_post['daterange'][0]);
								$this->_post['daterange']['stopdate'] = explode('.', $this->_post['daterange'][1]);
								$this->_post['daterange']['stopdate'] = mktime (23, 59, 59, $this->_post['daterange']['stopdate'][1], $this->_post['daterange']['stopdate'][0], $this->_post['daterange']['stopdate'][2]);
								unset($this->_post['daterange'][1]);
								$stats = $this->CallToDB("getStatisticsNewClient", $this->_post['daterange']);
								if($stats != 'none'){
									foreach($stats as $key=>$obj){
										$stats[$obj->data] = $obj;
										unset($stats[$key]);
									}
									krsort($stats);
								}
								$model->newclientstatistics = $stats;
							}
						}
						$output = $model->render(ADMIN_STATISTICS_JOINED);
				}
			}else{
				$output = $model->render(ADMIN_STATISTICS);
			}
		}else{
			$output = $model->render(ADMIN_STATISTICS);
		}
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewSilentsAction(){
		$model = new AdminSilentsModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->gsmerrors = $this->CallToDB("getCountGsmErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		$model->silents = $this->CallToDB("getSilents", $data);
		$output = $model->render(ADMIN_SILENTS);
		FrontController::getInstance()->setBody($output);		
	}
	
	private function getSilentData(){
		$currentmo = date('m', strtotime('- 1 month'));
		$currentyear = $currentmo == 12?date('Y', strtotime('- 1 year')):date('Y');
		return mktime(00, 00, 01, $currentmo  , 26, $currentyear);
	}
	
	public function ViewPagesAction(){
		$model = new AdminPagesModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->countnewclients = $this->CallToDB("getCountNewClients", NULL);
		$model->countagents = $this->CallToDB("getCountAgents", NULL);
		$model->countdilers = $this->CallToDB("getCountDilers", NULL);
		$model->countengineers = $this->CallToDB("getCountEngineers", NULL);
		$model->counthucksters = $this->CallToDB("getCountHucksters", NULL);
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->allmodems = $this->CallToDB("getCountModems", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if($this->_adress && isset($this->_adress['add'])){
			if($model->isadmin === true){
				if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					if(isset($this->_post['addPage']) && !empty($this->_post['addPage']) && count($this->_post['addPage']) == 3){
						echo $this->CallToDB("addPage", $this->_post['addPage']); exit;
					}	
				}
			}
			$output = $model->render(ADMIN_PAGE_ADD);
		}elseif($this->_adress && isset($this->_adress['edit'])){
			if($model->isadmin === true){
				if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					if(isset($this->_post['editPage']) && !empty($this->_post['editPage']) && count($this->_post['editPage']) == 4){
						echo $this->CallToDB("editPage", $this->_post['editPage']);
						exit;
					}	
				}
			}
			$model->pages = $this->CallToDB("getPages", $this->_adress['edit']);
			$output = $model->render(ADMIN_PAGE_EDIT);
		}else{
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if(isset($this->_post['delPage']) && !empty($this->_post['delPage'])){
					echo $this->CallToDB("delPage", $this->_post['delPage']); exit;
				}	
			}
			$model->pages = $this->CallToDB("getPages", NULL);
			$output = $model->render(ADMIN_PAGES);
		}
		FrontController::getInstance()->setBody($output);	
	}
	
	public function ViewDkpAction(){
		$model = new AdminDkpModel();
		$model->countclients = $this->CallToDB("getCountClients", NULL);
		$model->countnewclients = $this->CallToDB("getCountNewClients", NULL);
		$model->countagents = $this->CallToDB("getCountAgents", NULL);
		$model->countdilers = $this->CallToDB("getCountDilers", NULL);
		$model->countengineers = $this->CallToDB("getCountEngineers", NULL);
		$model->counthucksters = $this->CallToDB("getCountHucksters", NULL);
		$model->counterrors = $this->CallToDB("getCountErrors", NULL);
		$data = $this->getSilentData();
		$model->countsilents = $this->CallToDB("getCountSilents", $data);
		$model->paymenterrors = $this->CallToDB("getCountPaymentErrors", NULL);
		$model->allmodems = $this->CallToDB("getCountModems", NULL);
		$model->counttarifs = count($this->CallToDB("getCountTarifs", NULL));
		$model->isadmin = $_SESSION['user'] == 'admin'?true:false;
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			if(isset($this->_post['generateDkp']) && !empty($this->_post['generateDkp']) && count($this->_post['generateDkp']) == 1){
				$arrdate = explode('-', $this->_post['generateDkp']);
				if(count($arrdate) == 2){
					$arrdate[0] = explode('.',$arrdate[0]);
					$arrdate[0] = mktime(0, 0, 1, $arrdate[0][1], $arrdate[0][0], $arrdate[0][2]);
					$arrdate[1] = explode('.',$arrdate[1]);
					$arrdate[1] = mktime(23, 59, 59, $arrdate[1][1], $arrdate[1][0], $arrdate[1][2]);
					$newClients = $this->CallToDB("getNewClientsDkp", $arrdate);
					if($newClients){
						foreach($newClients as $key=>$client){
							$newClients[$key]->msisdn[0]=8;
							$newClients[$key]->gender = mb_substr($client->secondname, -1) == 'а'?'женский':'мужской';
							$client->addr_house = !empty($client->prop_corpus) && !is_null($client->prop_corpus)?$client->addr_house." корпус ".$client->prop_corpus:$client->addr_house;
						}
						$xls = new PHPExcel();
						$xls->getProperties()->setTitle("Название");
						$xls->getProperties()->setSubject("Тема");
						$xls->getProperties()->setCreator("Автор");
						$xls->getProperties()->setManager("Руководитель");
						$xls->getProperties()->setCompany("Организация");
						$xls->getProperties()->setCategory("Группа");
						$xls->getProperties()->setKeywords("Ключевые слова");
						$xls->getProperties()->setDescription("Примечания");
						$xls->getProperties()->setLastModifiedBy("Автор изменений");
						$xls->getProperties()->setCreated(date('d.m.Y', time()));
						$xls->setActiveSheetIndex(0);
						$sheet = $xls->getActiveSheet();
						$sheet->setTitle('Лист 1');
						
						$sheet->setCellValueExplicit("A1", 'REGION_NAME', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("B1", 'MSISDN', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("C1", 'SURNAME', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("D1", 'NAME', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("E1", 'SECONDNAME', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("F1", 'GENDER', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("G1", 'BIRTHDAY', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("H1", 'ADDR_INDEX', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("I1", 'ADDR_REGION', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("J1", 'ADDR_DISTRICT', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("K1", 'ADDR_CITY', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("L1", 'ADDR_STREET', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("M1", 'ADDR_HOUSE', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("N1", 'ADDR_APART', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("O1", 'LOC_INDEX', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("P1", 'LOC_REGION', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("Q1", 'LOC_DISTRICT', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("R1", 'LOC_CITY', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("S1", 'LOC_STREET', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("T1", 'LOC_HOUSE', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("U1", 'LOC_APART', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("V1", 'CONTACT_NAME', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("W1", 'CONTACT_PHONE', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("X1", 'DOC_TYPE', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("Y1", 'DOC_SERIAL', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("Z1", 'DOC_NUMBER', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("AA1", 'DOC_DATE', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("AB1", 'DOC_ORGANIZATION', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("AC1", 'DOC_CODE', PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicit("AD1", 'SIGN_DATE', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("AE1", 'EMAIL', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						$sheet->setCellValueExplicit("AF1", 'MANAGER', PHPExcel_Cell_DataType::TYPE_STRING); // NULL
						
						$cell = 2;
						foreach($newClients as $client){
							$sheet->setCellValueExplicit("A".$cell, 'Воронеж', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("B".$cell, $client->msisdn, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("C".$cell, $client->surname, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("D".$cell, $client->name, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("E".$cell, $client->secondname, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("F".$cell, $client->gender, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("G".$cell, date('d.m.Y',$client->birthday), PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("H".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("I".$cell, $client->addr_region, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("J".$cell, $client->addr_district, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("K".$cell, $client->addr_city, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("L".$cell, $client->addr_street, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("M".$cell, $client->addr_house, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("N".$cell, $client->addr_apart, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("O".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("P".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("Q".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("R".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("S".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("T".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("U".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("V".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("W".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("X".$cell, 'Паспорт РФ', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("Y".$cell, $client->doc_serial, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("Z".$cell, $client->doc_number, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("AA".$cell, date('d.m.Y',$client->doc_date), PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("AB".$cell, $client->doc_organization, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("AC".$cell, $client->doc_code, PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("AD".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("AE".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValueExplicit("AF".$cell, '', PHPExcel_Cell_DataType::TYPE_STRING);
							$cell ++;
						}
						
						header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
						header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
						header("Cache-Control: no-cache, must-revalidate");
						header("Pragma: no-cache");
						header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
						header("Content-Disposition: attachment; filename=file.xlsx");
						// отдача файла
						$objWriter = new PHPExcel_Writer_Excel2007($xls);
						$objWriter->save('php://output'); 
						exit();	
						// если сохранить в файл
						//$objWriter = new PHPExcel_Writer_Excel2007($xls);
						//echo $objWriter->save(__DIR__ . '/file.xlsx');

						//var_dump($newClients);
					}
				}
				exit;
			}
		}
		$output = $model->render(ADMIN_DKP);
		FrontController::getInstance()->setBody($output);	
	}
}