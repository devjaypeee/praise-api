<?php 

    class Get {
        protected $gm;
        protected $pdo;

        public function __construct(\PDO $pdo) {
            $this->gm = new GlobalMethods($pdo);
            $this->pdo = $pdo;
        }

        public function getStudent($dt) {
			$payload = [];
			$code = 0;
			$remarks = "failed";
			$message = "Unable to retrieve data";

			$sql = "SELECT * FROM student_tbl";
			if ($dt->studid_fld != null) {
				$sql.=" WHERE studid_fld = $dt->studid_fld";
			}
			
			$res = $this->gm->executeQuery($sql);
			if ($res['code'] == 200) {
				$payload = $res['data'];
				$code = 200;
				$remarks = "success";
				$message = "Retrieving data...";
			}
			return $this->gm->response($payload, $remarks, $message, $code);
		}

		public function getQuiz($dt) {
			$payload = [];
			$code = 0;
			$remarks = "failed";
			$message = "Unable to retrieve data";

			$sql = "SELECT * FROM quiz_tbl";
			if ($dt->studid_fld != null) {
				$sql.=" WHERE studid_fld = $dt->studid_fld";
			}
			
			$res = $this->gm->executeQuery($sql);
			if ($res['code'] == 200) {
				$payload = $res['data'];
				$code = 200;
				$remarks = "success";
				$message = "Retrieving data...";
			}
			return $this->gm->response($payload, $remarks, $message, $code);
		}
    }
?>