<?php
require_once '../config.php';
class Login extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;

		parent::__construct();
		ini_set('display_error', 1);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function index(){
		echo "<h1>Access Denied</h1> <a href='".base_url."'>Go Back.</a>";
	}
	public function login(){
		$username = isset($_POST['username']) ? $_POST['username'] : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		$stmt = $this->conn->prepare("SELECT * from users where username = ? ");
		$stmt->bind_param('s',$username);
		$stmt->execute();
		$qry = $stmt->get_result();
		if($qry->num_rows > 0){
			$res = $qry->fetch_array();
			if($res['status'] != 1){
				return json_encode(array('status'=>'notverified'));
			}
			if (password_verify($password, $res['password'])) {
				$is_correct = true;
			}

			if($is_correct){
				$allowed_session_keys = ['id', 'firstname', 'middlename', 'lastname', 'username', 'type', 'avatar'];
				foreach($res as $k => $v){
					if(in_array($k, $allowed_session_keys)){
						$this->settings->set_userdata($k,$v);
					}
				}
				$this->settings->set_userdata('login_type',1);
				return json_encode(array('status'=>'success'));
			}
		}
		return json_encode(array('status'=>'incorrect','msg'=>'Invalid username or password.'));
	}
	public function logout(){
		if($this->settings->sess_des()){
			redirect('admin/login.php');
		}
	}
	function client_login(){
		$email = isset($_POST['email']) ? $_POST['email'] : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		$stmt = $this->conn->prepare("SELECT *,concat(lastname,', ',firstname,' ',middlename) as fullname from client_list where email = ? ");
		$stmt->bind_param('s',$email);
		$stmt->execute();
		$qry = $stmt->get_result();
		if($this->conn->error){
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred while fetching data. Error:". $this->conn->error;
		}else{
			if($qry->num_rows > 0){
				$res = $qry->fetch_array();
				if (password_verify($password, $res['password'])) {
					$is_correct = true;
				}

				if($is_correct){
					if($res['status'] == 1){
						$allowed_session_keys = ['id', 'firstname', 'middlename', 'lastname', 'fullname', 'gender', 'contact', 'email', 'address', 'avatar'];
						foreach($res as $k => $v){
							if(in_array($k, $allowed_session_keys)){
								$this->settings->set_userdata($k,$v);
							}
						}
						$this->settings->set_userdata('login_type',2);
						$resp['status'] = 'success';
					}else{
						$resp['status'] = 'failed';
						$resp['msg'] = "Your Account is not verified yet.";
					}
				}else{
					$resp['status'] = 'failed';
					$resp['msg'] = "Invalid email or password.";
				}
			}else{
				$resp['status'] = 'failed';
				$resp['msg'] = "Invalid email or password.";
			}
		}
		return json_encode($resp);
	}
	public function setup_admin(){
		$username = isset($_POST['username']) ? $_POST['username'] : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		$firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
		$lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
		
		$chk = $this->conn->query("SELECT id FROM users")->num_rows;
		if($chk > 0){
			return json_encode(array('status'=>'failed','msg'=>'System already has an administrator.'));
		}

		$hashed = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $this->conn->prepare("INSERT INTO users (firstname, lastname, username, `password`, `type`, `status`) VALUES (?, ?, ?, ?, 1, 1)");
		$stmt->bind_param("ssss", $firstname, $lastname, $username, $hashed);
		$save = $stmt->execute();
		if($save){
			return json_encode(array('status'=>'success'));
		}else{
			return json_encode(array('status'=>'failed','msg'=>'An error occurred while saving the data. Error: '.$this->conn->error));
		}
	}
	public function client_logout(){
		if($this->settings->sess_des()){
			redirect('./');
		}
	}
}
$auth = new Login();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
	if(!$_settings->validate_csrf_token($token)){
		echo json_encode(['status'=>'failed', 'msg'=>'CSRF Token Validation Failed.']);
		exit;
	}
}

$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'login':
		echo $auth->login();
		break;
	case 'logout':
		echo $auth->logout();
		break;
	case 'client_login':
		echo $auth->client_login();
		break;
	case 'client_logout':
		echo $auth->client_logout();
		break;
	case 'setup_admin':
		echo $auth->setup_admin();
		break;
	default:
		echo $auth->index();
		break;
}

