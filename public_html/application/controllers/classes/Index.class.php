<?
session_start();
class Index extends IndexController implements IInterface{
	
	public function __construct(array $data){
		if($_SERVER['REQUEST_METHOD']=='POST'){ $this->setPostData($_POST); }
		$this->_adress = $this->getParamsAddresString();
		$this->_post = $this->getPostData();
		$this->rc = new ReflectionClass($this);
		//$rm = new ReflectionMethod('Index', $data['action']);
		$rm = new ReflectionMethod($this, $data['action']);
		$rm->invoke($this);	
	}

/* ----------- МОДЕЛЬ INDEX ЗАГРУЗКА ПО УМОЛЧАНИЮ ----------- */
	public function ViewIndexAction() {
	/* инициализация модели */
		$model = new IndexModel();
	/* установка параметров модели */
		if(isset($_SESSION['user']) && $this->checkSession($_SESSION['user'])){
			header("Location:/index/mycabinet");
		}else{
			$model->header = HEADER_INDEX_NOLOGIN;
			$blocks = INDEX;
			$model->footer = FOOTER_INDEX;
		}
	/* запуск модели */
		$output = $model->render($blocks);
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewLoginAction() {
		if(isset($_SESSION['user']) && $this->checkSession($_SESSION['user'])){
			header("Location:/index/mycabinet");
		}else{
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if(isset($this->_post['checkClient']) && !empty($this->_post['checkClient'])){
					foreach($this->_post['checkClient'] as $name=>$data){ $array[$data[0]] = $data[1]; }
					if(isset($array['lgn']) && !empty($array['lgn']) && isset($array['pwd']) && !empty($array['pwd'])){
						if($salt = $this->CallToDB("checkLogin", $array['lgn'])){
							$array['pwd'] = $this->getHash($array['pwd'], $salt->salt, 100);
							if($user = $this->CallToDB("checkPassword", $array)){
								$arr['sessionid'] = session_id();
								$arr['userlogin'] = $user->login;
								if($this->CallToDB("addClientSession", $arr)){
									$_SESSION['user'] = $user->login;
									echo 1;
								}
							}
						}
					}
					exit;
				}
			}
			$model = new IndexModel();
			$model->header = HEADER_NOLOGIN;
			$model->footer = FOOTER;
			$model->pageConfig = $this->CallToDB("getPageConfig", "login");
			$blocks = LOGIN;			
		}
	/* запуск модели */
		$output = $model->render($blocks);
		FrontController::getInstance()->setBody($output);		
	}

	public function ViewMycabinetAction(){
		$model = new MyCabinetModel();
		if(isset($_SESSION['user']) && $this->checkSession($_SESSION['user'])){
			$model->header = HEADER;
			$model->footer = FOOTER;
			$model->pageConfig = $this->CallToDB("getPageConfig", "mycabinet");
			$model->client = $this->CallToDB("getUserInfo", $_SESSION['user']);
			if(!$model->client){
				$this->logout();
			}
			$time = getdate();
			$model->day = $time["mday"];
			$model->firstpayment = $this->CallToDB("checkFirstPayment", $model->client->login);
			$model->access_token = $this->CallToDB("getYandexParams", 'ACCESS_TOKEN');
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
 				if(isset($this->_post['addEmptyPayments']) && !empty($this->_post['addEmptyPayments']) && count($this->_post['addEmptyPayments']) == 3){
					echo $this->CallToDB("addEmptyPayment", $this->_post['addEmptyPayments']); exit;
				}else{
					echo -1; exit;
				}
			}
			if($this->_adress && isset($this->_adress['payments']) && $this->_adress['payments'] == 'wait' && isset($this->_adress['order']) && !empty($this->_adress['order'])){
				if($this->CallToDB("CheckOrderPayment", $this->_adress['order']) != false){
					if($this->CallToDB("CheckPhonePayment", $this->_adress['order']) == false){
						echo "Данный платеж в обработке! Перейдите на страницу Вашего кабинета. <br/> <a href='/index/mycabinet'>Вернуться в кабинет</a>"; exit;
					}else{
						header("Location: http://".$_SERVER['HTTP_HOST']."/index/mycabinet");
					}
				}else{
					$this->CallToDB("addError", array('login'=>$model->client->login,'orderid'=>$this->_adress['order'],'ordercategory'=>2,'string'=>'Нет платежа в таблице!'));
					echo "Ошибка оплаты!<br/>";
					echo "Проверьте еще раз или обратитесь в техподдержку!<br/>";
					echo "<p><a href='http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."'>Проверить еще раз</a></p>";
					echo "<p><a href='http://".$_SERVER["HTTP_HOST"]."/index/mycabinet'>Вернуться в кабинет</a></p>";
				}
			}else{
				$model->payments = $this->CallToDB("getClientPayments", $_SESSION['user']);
				$model->wallet = $this->CallToDB("getYandexParams", 'WALLET');
				$model->success_url = $this->CallToDB("getYandexParams", 'SUCCESS_URL');
				$model->order_id = $this->CallToDB("getMaxOrderId", NULL);
				$model->oferta = $this->CallToDB("getPages", "dogovor_oferty");
			/* запуск модели */
				$output = $model->render(MY_CABINET);
			}
			FrontController::getInstance()->setBody($output);
		}else{
			header("Location:/index/login");
		}
	}

	public function ViewDocsAction(){
		$model = new DocsModel();
		if(isset($_SESSION['user']) && $this->checkSession($_SESSION['user'])){
			$model->header = HEADER_INDEX;
			$model->client = $this->CallToDB("getUserInfo", $_SESSION['user']);
		}else{
			$model->header = HEADER_INDEX_NOLOGIN;
		}
		if($this->_adress && !empty($this->_adress) && isset($this->_adress['view']) && !empty($this->_adress['view'])){
			$page = $this->CallToDB("getPages", $this->_adress['view']);
			if(isset($page->title) && isset($page->content)){
				$model->docstitle = $page->title;
				$model->docscontent = $page->content;
			}
			$blocks = DOCS;
		}
		$model->footer = FOOTER_INDEX;
	/* запуск модели */
		$output = $model->render($blocks);
		FrontController::getInstance()->setBody($output);
	}
	
	public function ViewMoneypushAction(){
		$params = file_get_contents('php://input');
		//$params = "notification_type=card-incoming&amount=10.50&datetime=2019-01-04T13%3A29%3A04Z&codepro=false&withdraw_amount=1725.00&sender=&sha1_hash=f08e0c094977fafd3095041086a5f4bde528cfe2&unaccepted=false&operation_label=23c1732b-0011-5000-a000-151bae9dce4f&operation_id=599923744040037012&currency=643&label=875%3A42538602";
		$access_token = $this->CallToDB("getYandexParams", 'ACCESS_TOKEN');
		if($params && !is_null($params) && $params != ''){
			$arr = explode('&',$params);
			// если нотификация с Яндекса
			if(is_array($arr) && isset($arr[1])){
				$noteiterator = new RecursiveArrayIterator($arr, NULL);
				foreach(new ArrayIterator($noteiterator) as $value) {
					$tmp = explode('=',$value);
					if($tmp[0] != 'label'){
						$out[$tmp[0]] = $tmp[1];
					}else{
						$label = explode('%3A',$tmp[1]);
						$out['orderid'] = $label[0];
						$out['login'] = $label[1];
					}
				}
				$out['string'] = $params;
				if(isset($out['login']) && !empty($out['login']) && isset($out['orderid']) && !empty($out['orderid'])){
					$res = ($this->CallToDB("CheckOrder", $out['orderid']) == false)?$this->CallToDB("AddPayment", $out):$this->CallToDB("UpdatePaymentStatus", $out);
					$arr = $this->CallToDB("CheckOrderPayment", $out['orderid']);
					if($arr != false && isset($arr->phone) && isset($arr->status) && isset($arr->oplata) && isset($out['operation_id']) && !empty($out['operation_id'])){
						if($arr->phone == 8){$arr->phone = 8;}
						// проверка на очередь этого заказа, если не в очереди по номеру заказа и по статусу выполнено, то добавить
						$proirity = $this->CallToDB("addPriorityPayment", array('login'=>$out['login'], 'orderid'=>$out['orderid'], 'phone'=>$arr->phone, 'sum'=>$arr->oplata, 'yandex_operationid'=>$out['operation_id']));
						$suspendedPayments = $this->CallToDB("checkSuspendedPayments", null);
						if($this->CallToDB("CheckPhonePayment", $out['orderid']) == false && $proirity && $this->deleteMail(array('multi'=>1)) && $suspendedPayments == false){
							$this->startPriorityPayment(array('phone'=>$arr->phone, 'oplata'=>$arr->oplata, 'orderid'=>$out['orderid'], 'login'=>$out['login']));
						}else{
							// вытащить следующего из очереди, если в очереди нет статуса в работе
							$this->checkNextPriorityPayment(null);
						}
					}
				}else{
					$this->CallToDB("addError", array('login'=>0,'orderid'=>0,'ordercategory'=>3,'string'=>$params));
				}
			}
			exit;
		}
	}
	
	// старт платежей из очереди
	private function checkNextPriorityPayment($payid){
		if(is_null($payid)){
			$arr = $this->CallToDB("checkNextPriorityPayment", null);
			if($arr){
				$arr['oplata'] = $arr['sum'];
				$this->startPriorityPayment($arr);
			}else{
				exit;
			}
		}
	}
	
	// процесс оплаты
	private function startPriorityPayment($arr){
		if($curl = curl_init()){
			curl_setopt($curl, CURLOPT_URL,"http://".GATEWAY_IP."/cgi/WebCGI?1500102=account=".GATEWAY_LOGIN."&password=".GATEWAY_PASS."&port=1&content=%2A145%2A".$arr['phone']."%2A".$arr['oplata']."%23"); // перевод ussd 1
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			$ussd1 = curl_exec($curl);
			curl_close($curl);
			if(mb_strpos($ussd1, 'Подтвердить')){
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL,"http://".GATEWAY_IP."/cgi/WebCGI?1500102=account=".GATEWAY_LOGIN."&password=".GATEWAY_PASS."&port=1&content=1"); // перевод ussd 2
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
				$ussd2 = curl_exec($curl);
				curl_close($curl);
				if(mb_strpos($ussd2, 'Response: Success')){
					while(true){
						$body = $this->checkNewMail($arr['phone']);
						if($body['body']){
							if(mb_strpos($body['body'], $arr['phone']) && mb_strpos($body['body'], "Для﻿ подтверждения платежа и согласия с офертой")){
								$curl = curl_init();
								curl_setopt($curl, CURLOPT_URL,"http://".GATEWAY_IP."/cgi/WebCGI?1500101=account=".GATEWAY_LOGIN."&password=".GATEWAY_PASS."&port=1&destination=145&content=123"); // отправка подтверждения sms
								curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
								$ussd3 = curl_exec($curl);
								curl_close($curl);
								if(mb_strpos($ussd3, "Response: Success")){
									if($proirity = $this->CallToDB("changePriorityPaymentStatus", array('orderid'=>$arr['orderid'], 'status'=>1))){
										// ставим статус в работе
										continue;
									}else{
										$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>1,'string'=>'ussd3 changePriorityPaymentStatus'));
										exit;
									}
								}else{
									// ставим статус новое
									if($proirity = $this->CallToDB("changePriorityPaymentStatus", array('orderid'=>$arr['orderid'], 'status'=>0))){
										// пишем ошибку
										$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>4,'string'=>'ussd3 не пришло success'));
										exit;
									}else{
										$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>1,'string'=>'ussd3 changePriorityPaymentStatus'));
										exit;
									}
								}
							}elseif(mb_strpos($body['body'], "Платеж в обработке") == 1){
								if($proirity = $this->CallToDB("changePriorityPaymentStatus", array('orderid'=>$arr['orderid'], 'status'=>2))){
									// ставим статус в работе
									continue;
								}else{
									$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>1,'string'=>'Платеж в обработке changePriorityPaymentStatus'));
								}
							}elseif(mb_strpos($body['body'], "Недостаточно средств") == 1){
								if($proirity = $this->CallToDB("changePriorityPaymentStatus", array('orderid'=>$arr['orderid'], 'status'=>0))){
									// пишем ошибку
									$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>4,'string'=>'Недостаточно средств'));
									exit;
								}else{
									$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>1,'string'=>'Недостаточно средств changePriorityPaymentStatus'));
									exit;
								}
							}elseif(mb_strpos($body['body'], "Необходимо пополнить счет") == 1){
								if($proirity = $this->CallToDB("changePriorityPaymentStatus", array('orderid'=>$arr['orderid'], 'status'=>0))){
									// пишем ошибку
									$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>4,'string'=>'Необходимо пополнить счет'));
									exit;
								}else{
									$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>1,'string'=>'Необходимо пополнить счет changePriorityPaymentStatus'));
									exit;
								}
							}elseif(mb_strpos($body['body'], $arr['phone']) && mb_strpos($body['body'], "успешно обработан")){
								// пишем пополнение мобильного , удаляем из очереди и вытаскиваем следующего очередника если в очереди нет статуса в работе
								if($this->CallToDB("updateMobileFunds", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'status_code'=>1))){
									$this->CallToDB("changePriorityPaymentStatus", array('orderid'=>$arr['orderid'], 'status'=>3));
								}else{
									$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>1,'string'=>'успешно обработан'));
								}
								exit;
							}
						}else{
							continue;
						}
					}
					exit;
				}else{
					$this->CallToDB("addError", array('login'=>$arr['login'],'orderid'=>$arr['orderid'],'ordercategory'=>4,'string'=>'ussd2 не пришло success'));
				}
			}
			// вытащить следующего из очереди, если в очереди нет статуса в работе
			$this->checkNextPriorityPayment(null);
		}
	}
	
	// УДАЛЕНИЕ ПОЧТЫ
	private function deleteMail($arr){
		$connect = imap_open(MAIL_ADDRESS, MAIL_LOGIN, MAIL_PASS);
		if($arr['multi'] == 1){
			$mails = imap_search($connect, 'UNDELETED'); //Получим ID непрочитанных писем
			if($mails){
				foreach($mails as $ow) {
					imap_delete($connect, $ow);
				}
			}
			return imap_close($connect, CL_EXPUNGE);
		}else{
			imap_delete($connect, $arr['msgnum']);
			return imap_close($connect, CL_EXPUNGE);
		}
	}
	
	// ПРОВЕРКА ПОЧТЫ
	private function checkNewMail($tel){
		$tel[0]=7;
		$connect = imap_open(MAIL_ADDRESS, MAIL_LOGIN, MAIL_PASS);
		$new_mails = imap_search($connect, 'UNSEEN'); //Получим ID непрочитанных писем
		if($new_mails){
			$new_mails = implode(",", $new_mails); //Соберём все ID в строчку через запятую
			$overview = imap_fetch_overview($connect,$new_mails,0); //Получаем инфу из заголовков сообщений
			foreach ($overview as $i=>$ow) { //пробегаем по полученному массиву. Каждый элемент массива - новое письмо
				$subject = iconv_mime_decode($ow->subject,0,"UTF-8"); //Получаем тему письма и сразу декодируем её
				if(mb_strpos($subject, "from 145 on TG100's port 1") != false){
					$body = imap_fetchbody($connect,$ow->msgno,1); //Получаем содержимое письма. После выполнения этой функции письмо станет автоматически прочитанным.
					imap_close($connect); //Не забываем закрыть коннект
					return array('body'=>$body, 'msgno'=>$ow->msgno);
				}elseif(mb_strpos($subject, "from 313 on TG100's port 1") != false){
					$body = imap_fetchbody($connect,$ow->msgno,1); //Получаем содержимое письма. После выполнения этой функции письмо станет автоматически прочитанным.
					imap_close($connect); //Не забываем закрыть коннект
					return array('body'=>$body, 'msgno'=>$ow->msgno);
				}
			}
		}
	}

	// ПРИЕМ С КАССЫ /index/kassaresponse
	public function ViewKassaresponseAction(){
		// ОШИБКИ 4ХХ 5ХХ
		// ПРАВИЛЬНО statusCode 202
		$params = file_get_contents('php://input');
		if(!empty($params)){
			$params = json_decode($params);
			var_dump($params);
		}
	}
	
	// проверка сессии
	private function checkSession($login){
		$res = $this->CallToDB("checkClientSession", array('userlogin'=>$_SESSION['user'],'sessionid'=>session_id()));
		return ($res && isset($res->data) && (time() <= $res->data + 86400))?true:false;
	}
}