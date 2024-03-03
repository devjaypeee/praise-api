<?php

	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=utf-8");
	header("Access-Control-Allow-Methods: POST");
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Auth-User");

	date_default_timezone_set("Asia/Manila");
    set_time_limit(1000);

	require_once("./vendor/autoload.php");

	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();

	define("SERVER", $_ENV['__SERVER']);
	define("DBASE",  $_ENV['__DBASE']);
	define("USER",  $_ENV['__USER']);
	define("PASSWORD", $_ENV['__PASSWORD']);
	define("CHARSET", $_ENV['__CHARSET']);

	class Connection {
		protected $conString = "mysql:host=".SERVER.";dbname=".DBASE."; charset=".CHARSET;
		protected $options = [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES => false
		];

		public function connect() {
			return new \PDO($this->conString, USER, PASSWORD, $this->options);
		}
	}
	
?>
