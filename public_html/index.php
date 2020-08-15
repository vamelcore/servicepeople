<?php
  ini_set('error_reporting', E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE); // hide all basic notices from PHP
	header('Content-Type: text/html; charset=utf-8');
	try{
		/* Пути по-умолчанию для поиска файлов */
		header("Content-type: text/html; charset=utf-8;");
		
		/* ШЛЮЗ */
		define('GATEWAY_IP', '109.106.138.152');
		define('GATEWAY_LOGIN', 'jurik');
		define('GATEWAY_PASS', 'hD8Hdw1jhdAlR');
		
		/* MAIL */
		define('MAIL_ADDRESS', '{imap.gmail.com:993/imap/ssl}INBOX');
		define('MAIL_LOGIN', 'vitbsd@gmail.com');
		define('MAIL_PASS', 'kilativ1');
		
		/* База данных конфиг */
		define('INDEX_DB', 'application/inc/db.ini');
		
		/* Имена файлов: views */
		define('HEADER', 'template/header.html');
		define('HEADER_NOLOGIN', 'template/header_nologin.html');
		define('HEADER_INDEX_NOLOGIN', 'template/header_index_nologin.html');
		define('INDEX', 'template/index.html');
		define('LOGIN', 'template/login.html');
		define('REGISTER', 'template/register.html');
		define('FOOTER', 'template/footer.html');
		define('FOOTER_INDEX', 'template/footer_index.html');
		define('HEADER_MYCABINET', 'template/header_mycabinet.html');
		define('MY_CABINET', 'template/my_cabinet.html');
		define('DOCS', 'template/docs.html');
		
		
		define('ADMIN_HEADER', 'template/admin_header.html');
		define('ADMIN_HEADER_NOLOGIN', 'template/admin_header_nologin.html');
		define('ADMIN_LOGIN', 'template/admin_login.html');
		define('ADMIN_INDEX', 'template/admin_index.html');
		define('ADMIN_CLIENTS', 'template/admin_clients.html');
		define('ADMIN_CLIENT', 'template/admin_client.html');
		define('ADMIN_CLIENT_REGISTRATION', 'template/admin_client_registration.html');
		define('ADMIN_TARIFS', 'template/admin_tarifs.html');
		define('ADMIN_TARIFS_IMPORT', 'template/admin_tarifs_import.html');
		define('ADMIN_SETTING', 'template/admin_setting.html');
		define('ADMIN_YANDEX', 'template/admin_yandex.html');
		define('ADMIN_LOGS', 'template/admin_logs.html');
		define('ADMIN_LOGS_PAYMENTS', 'template/admin_logs_payments.html');
		define('ADMIN_LOGS_PRIORITY', 'template/admin_logs_priority.html');
		define('ADMIN_STATISTICS', 'template/admin_statistics.html');
		define('ADMIN_STATISTICS_JOINED', 'template/admin_statistics_joined.html');
		define('ADMIN_SILENTS', 'template/admin_silents.html');
		define('ADMIN_PAGES', 'template/admin_pages.html');
		define('ADMIN_PAGE_ADD', 'template/admin_page_add.html');
		define('ADMIN_PAGE_EDIT', 'template/admin_page_edit.html');
		define('ADMIN_DKP', 'template/admin_dkp.html');
		define('ADMIN_FOOTER', 'template/admin_footer.html');

		/* Автозагрузчик */
		function autoloadController($className) {
		 $filename = "application/controllers/" . $className . ".php";
		 if(is_readable($filename)){
			require $filename;
		 }
		}
		function autoloadModel($className) {
		 $filename = "application/models/" . $className . ".php";
		 if(is_readable($filename)){
			require $filename;
		 }
		}
		function autoloadInterface($className) {
		 $filename = "application/controllers/interface/" . $className . ".php";
		 if(is_readable($filename)){
			require $filename;
		 }
		}
		function autoloadClass($className) {
		 $filename = "application/controllers/classes/" . $className . ".class.php";
		 if(is_readable($filename)){
			require $filename;
		 }
		}
		spl_autoload_register("autoloadInterface");
		spl_autoload_register("autoloadController");
		spl_autoload_register("autoloadModel");
		spl_autoload_register("autoloadClass");

		/* Инициализация и запуск FrontController */
		$front = FrontController::getInstance();
		$front->route();

		/* Вывод данных */
		echo $front->getBody();

	}catch(Exception $e){
		echo $e->getMessage();
		echo "<pre>"; var_dump($e);
		exit;
		
		echo "&nbsp;<a href='http://".$_SERVER['HTTP_HOST']."'>Назад</a>"; exit;
	}