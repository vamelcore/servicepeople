<?php
class AdminSilentsModel extends Model{
	public $silents;
	
	public function getSilents(){
		if(!empty($this->silents)){
			foreach($this->silents as $silent){
				$view = "<tr>";
				$view .= "<td><a href='/law/clients/view/".$silent->login."'>".$silent->lastname." ".$silent->firstname." ".$silent->middlename."</a>";
				$view .= $silent->pay->data?"<td bgcolor='yellow'>".date('d.m.Y H:i:s',$silent->pay->data)."</td>":"<td bgcolor='red'>Никогда</td>";
				$view .= "<td>".$silent->phone."</td>";
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