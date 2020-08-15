<?php
class AdminClientsModel extends Model{
	public $regions;
	public function getAllClients(){
		if(!empty($this->clients)){
			foreach($this->clients as $client){
				$client->data = $client->data?date('d.m.Y',$client->data):"-------";
				$view = "<tr>";
				$view .= "<td><a href='/law/clients/view/".$client->login."'>".$client->lastname." ".$client->firstname." ".$client->middlename."</a>";
				if($this->isadmin === true){
					$view .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='#".$client->login."' id='delClient' class='bg-red' style='padding:5px;float:right;'>Удалить</a>";
				}
				$view .= "</td>";
				$view .= "<td id='login'>".$client->login."</td>";
				$view .= "<td>".$client->phone."</td>";
				$view .= "<td>".$client->data."</td>";
				$view .= "</tr>";
				echo $view;
			}
		}
	}
	
	public function getClientInfo(){
		if(!empty($this->oneclient)){
			$this->oneclient->data = $this->oneclient->data?date('d.m.Y',$this->oneclient->data):"-------";
			echo "<input type='hidden' class='userinfo useredit' name='userlogin' id='userlogin' value='".$this->oneclient->login."' required>
				<h3 class='profile-username text-center'><b>".$this->oneclient->lastname." ".$this->oneclient->firstname." ".$this->oneclient->middlename."</b></h3>
				<div class='form-group'>
					<label for='userinfo' class='col-sm-3 control-label'>Фамилия</label>
					<div class='col-sm-9'>
						<input type='text' class='form-control userinfo useredit' name='lastname' id='lastname' value='".$this->oneclient->lastname."' required>
					</div>
				</div>
				<div class='form-group'>
					<label for='userinfo' class='col-sm-3 control-label'>Имя</label>
					<div class='col-sm-9'>
						<input type='text' class='form-control userinfo useredit' name='firstname' id='firstname' value='".$this->oneclient->firstname."' required>
					</div>
				</div>
				<div class='form-group'>
					<label for='userinfo' class='col-sm-3 control-label'>Отчество</label>
					<div class='col-sm-9'>
						<input type='text' class='form-control userinfo useredit' name='middlename' id='middlename' value='".$this->oneclient->middlename."' required>
					</div>
				</div>
				<p class='text-muted text-center'>№ счета: ".$this->oneclient->login."</p>
				<p class='text-muted text-center' style='color:red;'>Дата регистрации: ".$this->oneclient->data."</p>
				<ul class='list-group list-group-unbordered'>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-3 control-label text-orange'>Телефон</label>
								<div class='col-sm-9'>
									<input type='number' class='form-control userinfo useredit' name='phone' id='phone' value=".$this->oneclient->clientphone." oninput='checkLength(11,this)' required>
								</div>
						</div>
					</li>
					<p class='text-center margin bg-green'>Паспортные данные</p>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-3 control-label text-green'>Номер</label>
								<div class='col-sm-9'>
									<input type='number' class='form-control userinfo useredit' name='passport_number' id='passport_number' value='".$this->oneclient->passport_number."' oninput='checkLength(6,this)' required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-3 control-label text-green'>Серия</label>
								<div class='col-sm-9'>
									<input type='number' class='form-control userinfo useredit' name='passport_seriya' id='passport_seriya' value='".$this->oneclient->passport_seriya."' oninput='checkLength(4,this)' required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-6 control-label text-green'>Код подразделения</label>
								<div class='col-sm-6'>
									<input type='text' class='form-control userinfo useredit' name='passport_cod' id='passport_cod' value='".$this->oneclient->passport_cod."' oninput='checkLength(7,this)' required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-4 control-label text-green'>Дата выдачи</label>
								<div class='col-sm-8'>
									<input type='text' class='form-control userinfo useredit' name='passport_data' id='passport_data' placeholder='11.22.2000' oninput='checkLength(10,this)' value='".$this->oneclient->passport_data."' required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-3 control-label text-green'>Выдан</label>
								<div class='col-sm-9'>
									<textarea class='form-control userinfo useredit' name='passport_organ' id='passport_organ' required>".$this->oneclient->passport_organ."</textarea>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-green'>Место рожден</label>
								<div class='col-sm-7'>
									<input type='text' class='form-control userinfo useredit' name='passport_birthday_city' id='passport_birthday_city' value='".$this->oneclient->passport_birthday_city."' oninput='checkLength(25,this)' required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-4 control-label text-green'>Дата рожден</label>
								<div class='col-sm-8'>
									<input type='text' class='form-control userinfo useredit' name='passport_birthday_data' id='passport_birthday_data' placeholder='11.22.2000' oninput='checkLength(10,this)' value='".$this->oneclient->passport_birthday_data."' required>
								</div>
						</div>
					</li>
					<p class='text-center margin bg-orange'>Прописка</p>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-orange'>Прописка: область</label>
								<div class='col-sm-7'>
									<select class='form-control userinfo useredit' name='prop_oblast'>
										<option value='' selected>Выберите область</option>";
										$this->getRegion($this->oneclient->prop_oblast);
						echo "</select>
								</div>
						</div
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-orange'>Прописка: район</label>
								<div class='col-sm-7'>
									<input type='text' class='form-control userinfo' name='prop_region' id='prop_region' value=".$this->oneclient->prop_region.">
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-orange'>Прописка: город\нас пункт</label>
								<div class='col-sm-7'>
									<input type='text' class='form-control userinfo useredit' name='prop_city' id='prop_city' value='".$this->oneclient->prop_city."' required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-orange'>Прописка: улица</label>
								<div class='col-sm-7'>
									<input type='text' class='form-control userinfo' name='prop_street' id='prop_street' value=".$this->oneclient->prop_street.">
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-orange'>Прописка: дом</label>
								<div class='col-sm-7'>
									<input type='number' class='form-control userinfo useredit' name='prop_house' id='prop_house' value=".$this->oneclient->prop_house." required>
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-5 control-label text-orange'>Прописка: корпус</label>
								<div class='col-sm-7'>
									<input type='number' class='form-control userinfo' name='prop_corpus' id='prop_corpus' value=".$this->oneclient->prop_corpus.">
								</div>
						</div>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-6 control-label text-orange'>Прописка: квартира</label>
								<div class='col-sm-6'>
									<input type='number' class='form-control userinfo' name='prop_appart' id='prop_appart' value=".$this->oneclient->prop_appart.">
								</div>
						</div>
					</li>
					<p class='text-center margin bg-blue'>Данные модема</p>
					<li class='list-group-item no-border'>
						<b>Номер телефона</b> <span class='pull-right'>".$this->oneclient->simka."</span>
					</li>
					<li class='list-group-item no-border'>
						<b>Оператор</b> <span class='pull-right'>".$this->oneclient->operator."</span>
					</li>
					<li class='list-group-item no-border'>
						<b>Тариф</b> <span class='pull-right'>".$this->oneclient->tarif."</span>
					</li>
					<li class='list-group-item'>
						<b>ICC</b> <span class='pull-right'>".$this->oneclient->icc."</span>
					</li>
					<li class='list-group-item'>
						<div class='form-group'>
							<label for='userinfo' class='col-sm-3 control-label'>Адрес установки</label>
								<div class='col-sm-9'>
									<textarea class='form-control userinfo' name='address_modem' id='address_modem' required>".$this->oneclient->address_modem."</textarea>
								</div>
						</div>
					</li>
				</ul>";
		}
	}
	
	public function getRegion($regionid){
		if(!empty($this->regions)){
			if(is_null($regionid)){
				foreach($this->regions as $region){
					echo "<option value=".$region->id.">".$region->reg_name."</option>";
				}
			}else{
				foreach($this->regions as $region){
					echo $regionid == $region->id?"<option value=".$region->id." selected>".$region->reg_name."</option>":"<option value=".$region->id.">".$region->reg_name."</option>";
				}
			}
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