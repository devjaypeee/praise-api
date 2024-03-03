<?php
	
	require_once("./vendor/autoload.php");

	class Auth {
		protected $gm;
		protected $pdo;
        protected $get;

		public function __construct(\PDO $pdo) {
			$this->gm = new GlobalMethods($pdo);
            $this->get = new Get($pdo);
			$this->pdo = $pdo;
		}

		// JWT Methods

		protected function generate_header() {
			$header = [
				"typ"=>'PWA',
				"alg"=>'HS256',
				"ver"=>'1.0.0',
				"dev"=>'Simon Gerard Granil'
			];
			return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
		}

		protected function generate_payload($id, $email) {
			$payload = [   
				'uid'=>$id,
				'un'=>$email,
				'iby'=>'Project: RAISE',
				'ie'=>'dev.simongranil@gmail.com',
				'idate'=>date_create(),
				'exp'=>time() + (10 * 10 * 12 * 0)
			];
			return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
		}

		protected function generate_token($id, $email) {
			$header = $this->generate_header();
			$payload = $this->generate_payload($id, $email);
			$hashSignature = hash_hmac('sha256', $header. "." .$payload, "www.projectraise.com");
			$signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($hashSignature));

			return $header . "." . $payload . "." . $signature;
		}

		// User Authorization Methods

		public function encrypt_password($password) {
			$hashFormat = "$2y$10$";
		    $saltLength = 22;
		    $salt = $this->generate_salt($saltLength);
		    return crypt($password, $hashFormat.$salt);
		}

        protected function generate_salt($len) {
			$urs = md5(uniqid(mt_rand(), true));
            $b64String = base64_encode($urs);
            $mb64String = str_replace('+', '.', $b64String);
            return substr($mb64String, 0, $len);
		}

        protected function password_check($password, $existingHash) {
			$hash = crypt($password, $existingHash);
			if($hash === $existingHash){
				return true;
			}
			return false;
		}

		protected function get_payload($token) {
			$token = explode('.', $token);
			return $token[1];
		}

		protected function is_authorized($token) {
			$token = explode('.', $token);
			$payload = json_decode(base64_decode($token[1]));
			$exp = $payload->exp;
			$now = time();
			if($now < $exp) {
				return true;
			}
			return false;
		}

		protected function check_auth($token) {
			if($this->is_authorized($token)) {
				return $this->get_payload($token);
			}
			return false;
		}

		protected function getTokenSignature($d) {
			$token = explode('.', $d);
			return $token[2];
		}

		public function check_token_log($id, $token) {
			$sql = "SELECT * FROM token_tbl WHERE studid_fld = $id";
			$res = $this->gm->executeQuery($sql);

			switch($res['code']) {
				case 200:
					if (strlen($res['data'][0]['tokenlog_fld']) > 0) {
						return true;
					} else {
						$this->gm->update('token_tbl', ['tokenlog_fld'=>$token], "studid_fld = $id");
					}
				break;

				case 404:
					$payload = [
						"studid_fld" => $id,
						"tokenlog_fld" => $token,
					];
					
					$this->gm->insert("token_tbl", $payload);
				break;

				default:
					return false;
				break;
			}
		}

		public function checkValidSignature($param1, $param2) {
			$sql = "SELECT * FROM token_tbl WHERE studid_fld = ?";
			$prep = $this->pdo->prepare($sql);
			$prep->execute([
				$param1,
			]);

			if ($res = $prep->fetchAll()) {
				$userToken = explode('.', $param2);

				return $this->getTokenSignature($res[0]['tokenlog_fld']) == $userToken[2];
			}
			return false;
		}
		
		// Login

		public function loginStudent($dt) {
			$payload = [];	
			$code = 200;
			$remarks = "failed";
			$message = "Login failed. Check you account credentials.";

			$sql = "SELECT * FROM student_tbl WHERE studno_fld = '$dt->studno_fld' LIMIT 1";
			$res = $this->gm->executeQuery($sql);	

			if ($res['code'] == 200) {
				if ($this->password_check($dt->studpwd_fld, $res['data'][0]['studpwd_fld'])) {
					$id = $res['data'][0]['studid_fld'];
					$email = $res['data'][0]['studno_fld'];
					$token = $this->generate_token($id, $email);

					// Checking current user's token

					if ($this->check_token_log($id, $token)) {
						$remarks = "auth";
						$message = "Authorization failed. You already logged in with other device.";
					} else {
						$payload = [
							"id" => $id,
							"token" => $token
						];
						$code = 200;
						$remarks = "success"; 
						$message = "Login success.";
					}
				}
			}
			return $this->gm->response($payload, $remarks, $message, $code);		
		}

		public function logoutStudent($d) {
			$payload = [];	
			$code = 200;
			$remarks = "failed";
			$message = "Logout failed";

			$res = $this->gm->delete('token_tbl', "studid_fld = $d->studid_fld");

			if ($res['code'] == 200) {
				$code = 200;
				$remarks = "success";
				$message = "Logout successfully";
			}
			return $this->gm->response($payload, $remarks, $message, $code);
		}

		public function AddStudent($dt) {
            $payload = [];
			$code = 0;
			$remarks = "failed";
			$message = "Unable to add student";
            $data = [
                "studno_fld" => $dt->studno_fld,
                "studpwd_fld" => $this->encrypt_password($dt->studpwd_fld),
                "studfname_fld" => $dt->studfname_fld,
                "studmname_fld" => $dt->studmname_fld,
                "studlname_fld" => $dt->studlname_fld,
			];

			$res = $this->gm->insert('student_tbl', $data);

			if ($res['code'] == 200) {
				// $payload = $res['data'];
				$code = 200;
				$remarks = "success";
				$message = "Student added successfully";
			}
			return $this->gm->response($payload, $remarks, $message, $code);
        }


		// Add

        // public function addStaff($dt) {
        //     $code = 0;
        //     $payload = null;
        //     $remarks = "failed";
        //     $message = "Unable to add data";
        //     $data = $dt;

        //     $res = $this->gm->insert('staff_tbl', $data);

        //     if($res['code'] == 200) {
        //         $code = 200;
        //         $remarks = "success";
        //         $message = "Staff added to database";
        //     }
        //     return $this->gm->response($payload, $remarks, $message, $code);
        // }
	}
?>