<?php 
    class Post {
        protected $gm;
        protected $pdo;
        protected $get;
        protected $auth;

        public function __construct(\PDO $pdo) {
            $this->pdo = $pdo;
            $this->gm = new GlobalMethods($pdo);
            $this->get = new Get($pdo);
            $this->auth = new Auth($pdo);
        }

        public function submitQuiz($dt) {
            $payload = [];
			$code = 0;
			$remarks = "failed";
			$message = "Unable to add quiz";

			$res = $this->gm->insert('quiz_tbl', $dt);

			if ($res['code'] == 200) {
				// $payload = $res['data'];
				$code = 200;
				$remarks = "success";
				$message = "Quiz added successfully";
			}
			return $this->gm->response($payload, $remarks, $message, $code);
        }

       
    }
?>