<?php
class AdminYandexModel extends Model{
	public $yandex, $addBlock;
	
	public function getClientId(){
		if(!empty($this->yandex)){
			foreach($this->yandex as $param=>$value){
				if($param == 'CLIENT_ID')
					echo "ID приложения: ".$value;
				if($param == 'CLIENT_SECRET')
					echo "<br/>Секрет приложения: ".$value;
			}
		}else{
			echo "Не получен ID приложения. Приложение не может работать!";
		}
	}
	
	public function autorizeBlock(){
		if($this->addBlock == 'start'){
			echo "<div class='box box-primary'><div class='box-body box-profile' style='overflow: scroll;'>";
			if(empty($_GET['code'])) {
				$url = "https://money.yandex.ru/oauth/authorize";
				if($curl = curl_init()) {
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($curl, CURLOPT_POSTFIELDS, "client_id=".$this->yandex['CLIENT_ID']."&response_type=code&redirect_uri=".$this->yandex['REDIRECT_URL']."&scope=account-info operation-details payment-p2p payment-shop");
					$out = curl_exec($curl);
					curl_close($curl); 
				}				
			}else{
				$url = "https://money.yandex.ru/oauth/token";
				if($curl = curl_init()) {
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($curl, CURLOPT_POSTFIELDS, "code=".$_GET["code"]."&client_id=".$this->yandex['CLIENT_ID']. "&grant_type=authorization_code&redirect_uri=".$this->yandex['REDIRECT_URL']."&client_secret=".$this->yandex['CLIENT_SECRET']);
					$out = curl_exec($curl);
					curl_close($curl);
				}
			}
			echo "</div>";
		}
	}
	
	public function getToken(){
		if(!empty($this->yandex)){
			foreach($this->yandex as $param=>$value){
				if($param == 'ACCESS_TOKEN' && $value != ''){
					echo "<div class='col-md-9'>".$param.": <b class='bg-green'>&nbsp;ПОЛУЧЕН&nbsp;</b></div><div class='col-md-3'><a class='btn btn-block btn-warning btn-flat' href='/law/yandex/token/new'>Перевыпустить токен</a></div>";
					exit;
				}
			}
			echo "<div class='col-md-9'>ACCESS_TOKEN: <b class='bg-red'>&nbsp;НЕ ПОЛУЧЕН&nbsp;(Приложение не будет работать!)&nbsp;</b></div><div class='col-md-3'><a class='btn btn-block btn-primary btn-flat' href='/law/yandex/token/new'>Получить токен</a></div>";
		}else{
			echo "Не получен ID приложения. Приложение не может работать!";
		}		
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