<?php
class AdminTarifsModel extends Model{
	
	public function getTarifs(){
		if(!empty($this->tarifs)){
			foreach($this->tarifs as $tarif){
				$view = "<tr>";
				//$view .= "<input type='hidden' id='tarifAccount' value='".$tarif->account."'>";
				$view .= "<td id='triflogin'><span id='account'>".$tarif->account."</span>";
				if($this->isadmin === true){
					if($tarif->used == 0){
						$view .= "<a href='#' id='delTarif' class='bg-red' style='float:right;padding:5px;margin-left:5px;'>Удалить</a>";
					}
					$view .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='/law/tarifs/edit/".$tarif->account."' id='editTarif' class='bg-orange' style='float:right;padding:5px'>Изменить</a>";
				}
				$view .= "</td>";
				$view .= ($tarif->used == 1)?"<td><span class='label label-danger'>Занят</span></td>":"<td><span class='label label-success'>Свободен</span></td>";
				$view .= "<td>".$tarif->phone."</td>";
				$view .= "<td id='trifall'>".$tarif->tarif."</td>";
				$view .= "<td id='trifoplata'>".$tarif->oplata."</td>";
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