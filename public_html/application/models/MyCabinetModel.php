<?php
class MyCabinetModel extends Model{
	public $access_token, $wallet, $client_id, $order_id, $success_url, $day, $firstpayment, $text, $oferta;
	
	public function getPaymentForm(){
		if(isset($this->access_token->param_value) && isset($this->wallet->param_value) && !empty($this->order_id) && !empty($this->success_url)){
/* 			if(($this->day <= 15) && $this->firstpayment == 1){
				$this->client->tarif = $this->client->tarif + ($this->client->tarif/2);
				$text = "<div class='form-group'><label for='method' class='col-sm-offset-2 col-sm-10 bg-red text-center'>Оплата за полтора месяца</label></div>";
			} */
			echo "<input type='hidden' name='clientid' class='precheck' value=".$this->client->login.">";
			echo "<input type='hidden' name='orderid' id='orderid' class='precheck' value=''>";
			echo "<input type='hidden' name='tarif' class='precheck' value=".$this->client->tarif.">";
			echo "<form method='post' id='sendform' class='form-horizontal' action='https://money.yandex.ru/quickpay/confirm.xml'>
							<input type='hidden' name='receiver' value='".$this->wallet->param_value."'>  
							<input type='hidden' name='formcomment' value='Авансовый платеж: ".$this->client->login."'>
							<input type='hidden' name='short-dest' value='Авансовый платеж: ".$this->client->login."'>
							<input type='hidden' name='label' value='".$this->order_id.":".$this->client->login."'>    
							<input type='hidden' name='quickpay-form' value='shop'>    
							<input type='hidden' name='targets' value='Авансовый платеж'>    
							<input type='hidden' name='sum' value='".$this->client->tarif."' data-type='number'>    
							<input type='hidden' name='comment' value='Авансовый платеж: ".$this->client->login."'>  
							<input type='hidden' name='need-fio' value='false'>   
							<input type='hidden' name='need-phone' value='false'>    
							<input type='hidden' name='need-address' value='false'>  
							<input type='hidden' name='successURL' value='".$this->success_url->param_value."/order/".$this->order_id."'>
							<div class='box-body'>
								<div class='form-group'>
									<label for='method' class='col-sm-offset-2 col-sm-10'>Сумма оплаты</label>
									<div class='col-sm-offset-2 col-sm-10'>
										<input type='email' class='form-control' value='".$this->client->tarif."' disabled>
									</div>
								</div>
								".$text."
								<div class='form-group'>
									<label for='method' class='col-sm-offset-2 col-sm-10'>Выберите способ оплаты</label>
									<div class='col-sm-offset-2 col-sm-10'>
									<select class='form-control' name='paymentType'>
										<option value='AC'>Оплата картой</option>
										<option value='PC'>С кошелька Яндекс деньги</option>
									</select>
									</div>
								</div>
								<div class='form-group'>
									<div class='col-sm-offset-2 col-sm-10'>
										<div class='checkbox'>
											<label>
												<input type='checkbox'> Сохранить данные для последующих оплат?
											</label>
										</div>
									</div>
								</div>
							</div>
							<div class='box-footer'>
								<div class='col-sm-offset-2 col-sm-10'>
								<!-- <button type='submit' class='btn btn-block btn-success btn-lg' id='sendMoney'>Оплатить</button> -->
								<button type='button' class='btn btn-block btn-success btn-lg' data-toggle='modal' data-target='#modal-default'>Оплатить</button>
								</div>
							</div>
						</form>";
		}else{
			echo "<div class='alert alert-danger alert-dismissible'><h4><i class='icon fa fa-ban'></i>Ошибка!</h4>Не удается показать форму оплаты!<br/>Обратитесь в техподдержку.</div>";
		}
	}
	
	public function getOferta(){
		echo isset($this->oferta->content)?$this->oferta->content:'';
	}
	
	public function render($files) {
		/* $file - текущее представление */
		ob_start();
		include $this->header;
		include($files);
		include FOOTER;
		return ob_get_clean();
	}
}