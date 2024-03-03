<?php 
	
    require_once("./config/Config.php");
    require_once("./modules/Procedural.php");
    require_once("./modules/Global.php");
    require_once("./modules/Auth.php");
    require_once("./modules/Get.php");
    require_once("./modules/Post.php");

    $db = new Connection();
	$pdo = $db->connect();
    $gm = new GlobalMethods($pdo);
	$auth = new Auth($pdo);
    $get = new Get($pdo);
    $post = new Post($pdo);

    if (isset($_REQUEST['request'])) {
        $req = explode('/', rtrim($_REQUEST['request'], '/'));
    } else {
        $req = array("errorcatcher");
    }

    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':

            $d = json_decode(file_get_contents("php://input"));

            switch($req[0]) {

                case 'encrypt':
                    echo json_encode($auth->encrypt_password($req[1]));
                break;

                case 'loginstud':
                    echo json_encode($auth->loginStudent($d));
                break;

                case 'logoutstud': 
                    echo json_encode($auth->logoutStudent($d));
                break;

                case 'addstud':
                    echo json_encode($auth->addStudent($d));
                break;

                case 'getstud':
                    if ($auth->checkValidSignature($d->id, $d->token)) {
                        echo json_encode($get->getStudent($d->payload));
                    } else {
                        echo errMsg(401);
                    }
                break;

                case 'getquiz':
                    if ($auth->checkValidSignature($d->id, $d->token)) {
                        echo json_encode($get->getQuiz($d->payload));
                    } else {
                        echo errMsg(401);
                    }
                break;

                case 'submitquiz':
                    if ($auth->checkValidSignature($d->id, $d->token)) {
                        echo json_encode($post->submitQuiz($d->payload));
                    } else {
                        echo errMsg(401);
                    }
                break;
            }
        break;

        case 'OPTIONS':
            return 200;
        break;

        default:
            echo errMsg(403);
        break;
    }

?>