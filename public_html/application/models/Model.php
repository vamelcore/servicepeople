<?php
class Model{
	public $client, $data, $header, $pageConfig, $leftmenu, $clients, $countclients, $counttarifs, $countsilents, $tarifs, $usedtarifs, $nousedtarifs, $oneclient, $payments;
	public $isadmin = false;
	
	public function gettitle(){
		echo $this->pageConfig['metatitle'];
	}
	
	public function getDescription(){
		echo $this->pageConfig['metadescription'];
	}
	
	public function getTopUserMenu(){
		echo "<span>Служба поддержки:  <b>8-951-548-16-49</b></span>";
		if(!empty($this->client)){
			echo "<li class='user user-menu'><span>Лицевой счет:&nbsp;&nbsp;".$this->client->login."</span></li><li class='user user-menu'><span class='hidden-xs'>".$this->client->lastname." ".$this->client->firstname." ".$this->client->middlename."</span></li>";
		}
		echo "<li class='user user-menu'><a href='/index/mycabinet'>Мой кабинет</a></li><li class='user user-menu'><a href='/index/logout'>Выход</a></li>";
	}
	
	public function getTopUserDatas(){
		if(!empty($this->client)){
			echo " , ".$this->client->lastname." ".$this->client->firstname." ".$this->client->middlename.". Ваш тариф: ".$this->client->tarif." руб. Номер лицевого счета: ".$this->client->login;
		}
	}
	
	public function getCountClients(){
		return isset($this->countclients->allclients) && !empty($this->countclients->allclients)?$this->countclients->allclients:0;
	}
	
	public function getCountTarifs(){
		return isset($this->counttarifs) && !empty($this->counttarifs)?$this->counttarifs:0;
	}	
	
	public function getCountSilents(){
		return isset($this->countsilents) && !empty($this->countsilents)?$this->countsilents:0;
	}	
	
	public function getCountUsedTarifs(){
		return isset($this->usedtarifs) && !empty($this->usedtarifs)?$this->usedtarifs:0;
	}
	
	public function getCountNousedTarifs(){
		return isset($this->nousedtarifs) && !empty($this->nousedtarifs)?$this->nousedtarifs:0;
	}
	
	public function getCountErrors(){
		return isset($this->counterrors->allerrors) && !empty($this->counterrors->allerrors)?$this->counterrors->allerrors:0;
	}
	
	public function getCountPaymentErrors(){
		return isset($this->paymenterrors->allerrors) && !empty($this->paymenterrors->allerrors)?$this->paymenterrors->allerrors:0;
	}
	
	public function getCountGsmErrors(){
		return isset($this->gsmerrors) && ($this->gsmerrors > 0)?"<small class='label pull-right bg-red'>".$this->gsmerrors."</small>":"<small class='label pull-right bg-green'>0</small>";
	}
	
	public function getHeaderGsmPriority(){
		if(!empty($this->gsmerrors) && ($this->gsmerrors > 0)){
			echo "<li class='dropdown notifications-menu'>
            <a href='#' class='dropdown-toggle' data-toggle='dropdown' aria-expanded='false'>
              <i class='fa fa-bell-o'></i>
              <span class='label label-danger'>1</span>
            </a>
            <ul class='dropdown-menu'>
              <li class='header'>Внимание! GSM очередь!</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class='menu'>
                  <li>
                    <a href='/law/logs/error/priority''>
                      <i class='fa fa-warning text-yellow'></i> Есть ожидающая GSM очередь!
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </li>";
		}
	}
	
	public function getAdminLeftMenu(){
		$allclients = $this->getCountClients();
		$alltarifs = $this->getCountTarifs();
		$allsilents = $this->getCountSilents();
		$allerrors = $this->getCountErrors();
		$gsmerrors = $this->getCountGsmErrors();
		$paymenterrors = $this->getCountPaymentErrors();
		$adminrole = $this->isadmin === true?'Суперадмин':'Инженер';
		echo "
		<aside class='main-sidebar'>
			<!-- sidebar: style can be found in sidebar.less -->
			<section class='sidebar'>
				<!-- Sidebar user panel -->
				<div class='user-panel'><div class='pull-left image'><img src='/template/dist/img/avatar04.png' class='img-circle' alt='User Image'></div><div class='pull-left info'><p>".$adminrole."</p><i class='fa fa-circle text-success'></i> Online</div></div>
				<!-- sidebar menu: : style can be found in sidebar.less -->
				<ul class='sidebar-menu' data-widget='tree'>
					<li><a href='/law'><i class='fa fa-dashboard'></i> <span>Панель управления</span><span class='pull-right-container'><i class='fa fa-angle-left pull-right'></i></span></a></li>
					<li><a href='/law/clients'><i class='fa fa-user'></i><span>Клиенты</span><span class='pull-right-container'><span class='label label-primary pull-right'>".$allclients."</span></span></a></li>
					<li><a href='/law/dkp'><i class='fa fa-user'></i><span>ДКП</span></a></li>
					<li><a href='/law/silents'><i class='fa fa-user-times'></i> <span>Молчуны</span><span class='pull-right-container'><span class='label pull-right bg-red'>".$allsilents."</span></span></a></li>
					<li><a href='/law/tarifs'><i class='glyphicon glyphicon-th-list'></i> <span>Тарифы</span><span class='pull-right-container'><small class='label pull-right bg-green'>".$alltarifs."</small></span></a></li>
					<li><a href='/law/statistics'><i class='glyphicon glyphicon-stats'></i> <span>Статистика</span></a></li>";
		if($this->isadmin === true){
			echo "<li><a href='/law/pages'><i class='fa fa-book'></i> <span>Страницы</span></a></li>
						<li><a href='/law/yandex'><i class='fa fa-dollar'></i> <span>Настройки Яндекс</span></a></li>
						<li><a href='/law/logs/error/priority' class='text-yellow'><i class='glyphicon glyphicon-bell'></i> <span>Ошибки GSM</span><span class='pull-right-container'>".$gsmerrors."</span></a></li>
						<li><a href='/law/logs/error/payments'><i class='glyphicon glyphicon-bell'></i> <span>Ошибки платежей</span><span class='pull-right-container'><small class='label pull-right bg-red'>".$paymenterrors."</small></span></a></li>";
		}
		echo "<li><a href='/law/logs'><i class='glyphicon glyphicon-bell'></i> <span>Все ошибки</span><span class='pull-right-container'><small class='label pull-right bg-red'>".$allerrors."</small></span></a></li>
				</ul>
			</section>
			<!-- /.sidebar -->
		</aside>";		
	}
	
	public function getClientStatisticPayments(){
		if(!empty($this->payments)){
			foreach($this->payments as $payment){
				if($payment->status == 1){
					echo "<li class='time-label'><span class='bg-blue'>".date('d.m.Y G:i',$payment->data)."</span><li><i class='fa fa-dollar bg-green'></i><div class='timeline-item'><h3 class='timeline-header no-border'>Сумма <b>".$payment->sum."</b> рублей была оплачена.</h3></div></li></li>";
				}else{
					echo "<li class='time-label'><span class='bg-blue'>".date('d.m.Y G:i',$payment->data)."</span><li><i class='fa fa-remove bg-red'></i><div class='timeline-item'><h3 class='timeline-header no-border'>Сумма в <b>".$payment->sum."</b> рублей не оплачена</h3></div></li></li>";
				}
			}	
		}else{
			echo "<li class='time-label'><span class='bg-blue'>------------</span><li><i class='fa fa-dollar bg-red'></i><div class='timeline-item'><h3 class='timeline-header no-border'>Нет платежей</h3></div></li></li>";
		}
	}
}