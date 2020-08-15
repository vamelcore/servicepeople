<?php
class AdminLogsModel extends Model{
	public $logs, $script;
	
	public function getAllLogs(){
		if(!empty($this->logs)){
			foreach($this->logs as $log){
				$view = "<tr>";
				$view .= "<td><a href='/law/logs/view/".$log->id."'>".date('d.m.Y H:m:s',$log->data)."</a></td>";
				$arr = explode(':',$log->text);
				$view .= "<td>".$arr[0]."</td>";
				$view .= "<td>".$arr[1]."</td>";
				switch($log->ordercategory){
					case 1: $view .= "<td style='background: red; color: #fff;'>ошибки записи в базу</td>"; break;
					case 2: $view .= "<td style='background: orange; color: #000;'>ошибки проведения оплаты</td>"; break;
					case 3: $view .= "<td style='background: violet; color: #fff;'>ошибки пополнения симки</td>"; break;
				}
				$view .= "<td>".$log->text."</td>";
				$view .= "</tr>";
				echo $view;
			}
		}
	}
	
	public function getPaymentsLogs(){
		if(!empty($this->logs)){
			foreach($this->logs as $log){
				$view = "<tr>";
				$view .= "<td>".date('d.m.Y H:m:s',$log->data)."</td>";
				$view .= "<td>".$log->login."</td>";
				$view .= "<td id='orderid'>".$log->order_id."</td>";
				$view .= "<td id='orderid'>".$log->operation_id."</td>";
				$view .= "<td>";
				if($this->isadmin === true && empty($log->operation_id)){
					$view .= "<a href='#' id='delPaymentError' class='bg-red' style='float:right;padding:5px;margin-left:5px;'>Удалить</a>";
				}
				$view .= "</td>";
				$view .= "</tr>";
				echo $view;
			}
		}
	}
	
	public function getPriorityLogs(){
		if(!empty($this->logs)){
			foreach($this->logs as $log){
				if($log->status == 1){
					$log->status = "Отправлено подтверждение sms";
				}elseif($log->status == 2){
					$log->status = "Платеж в обработке";
				}
				$view = "<tr>";
				$view .= "<td>".date('d.m.Y H:m:s',$log->data)."</td>";
				$view .= "<td>".$log->login."</td>";
				$view .= "<td id='orderid'>".$log->orderid."</td>";
				$view .= "<td>".$log->sum."</td>";
				$view .= "<td>".$log->status."</td>";
				$view .= "<td>";
				if($this->isadmin === true){
					$view .= "<a href='#' id='delPriorityError' class='bg-green' style='float:right;padding:5px;margin-left:5px;'>Одобрить</a>";
				}
				$view .= "</td>";
				$view .= "</tr>";
				echo $view;
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