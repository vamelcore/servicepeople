<?php
class AdminStatisticsModel extends Model{
	public $newclientstatistics;
  
	public function getClientrasultat() {
		if(is_array($this->newclientstatistics)){
			echo "<div class='box box-success'><div class='box-header text-center'><h4 class=''>Найдено: ".count($this->newclientstatistics)." чел.</h4></div><div class='box-body'><table id='example1' class='table table-bordered table-striped'><thead><tr><th>ФИО</th><th>№ счета</th><th>Тариф</th><th>Дата реги</th></tr></thead><tbody>";
			foreach($this->newclientstatistics as $newclient){
				$view = "<tr>";
				$view .= "<td><a href='/law/clients/view/".$newclient->login."' target='_blank'>".$newclient->lastname." ".$newclient->firstname." ".$newclient->middlename."</a>";
				$view .= "</td>";
				$view .= "<td id='login'>".$newclient->login."</td>";
				$view .= "<td id='login'>".$newclient->tarif."</td>";
				$view .= "<td>".date('d.m.Y H:i', $newclient->data)."</td>";
				$view .= "</tr>";
				echo $view;
			}
			echo "</tbody><tfoot><tr><th>ФИО</th><th>№ счета</th><th>Дата реги</th></tr></tfoot></table></div></div>";
		}elseif(!empty($this->newclientstatistics)){
			echo "<div class='box box-warning'><div class='box-header text-center'><h4 class=''>Нет новых клиентов за указанный период</h4></div></div>";
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