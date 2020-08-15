<?php
class AdminPagesModel extends Model{
	public $pages;
	
	public function getAllPages(){
		if(!empty($this->pages)){
			foreach($this->pages as $page){
				$view = "<tr>";
				$view .= "<td id='pageid'>".$page->id."</td>";
				$view .= "<td>".$page->title."</td>";
				if($this->isadmin === true){
					$view .= "<td><a href='/law/pages/delete/".$page->id."' id='delPage' class='bg-red' style='float:right;padding:5px;margin-left:5px;'>Удалить</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href='/law/pages/edit/".$page->url."' class='bg-orange' style='float:right;padding:5px'>Изменить</a></td>";
				}else{
					$view .= "Только админу";
				}
				$view .= "</tr>";
				echo $view;
			}
		}
	}

	public function getPageId(){
		if(isset($this->pages->id)){
			echo $this->pages->id;
		}
	}
	
	public function getPageTitle(){
		if(isset($this->pages->title)){
			echo $this->pages->title;
		}
	}
	
	public function getPageContent(){
		if(isset($this->pages->content)){
			echo $this->pages->content;
		}
	}
	
	public function getPageUrl(){
		if(isset($this->pages->url)){
			echo $this->pages->url;
		}
	}
	
	public function render($file) {
		/* $file - текущее представление */
		ob_start();
		include ADMIN_HEADER;
		include($file);
		include ADMIN_FOOTER;
		echo "<script src='/template/bower_components/ckeditor/ckeditor.js'></script>";
		echo "<script>let editor1 = $(document).find('textarea#editor1');if(editor1.length > 0){ $(function() {CKEDITOR.replace('editor1')})}</script>";
		return ob_get_clean();
	}
	
}