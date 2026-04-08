<?php
require_once('../config.php');
Class Users extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function save_users(){
		if(!isset($_POST['status']) && $this->settings->userdata('login_type') == 1){
			$_POST['status'] = 1;
		}
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
		$lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
		$username = isset($_POST['username']) ? trim($_POST['username']) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		$oldpassword = isset($_POST['oldpassword']) ? $_POST['oldpassword'] : '';

		// Validation
		if(empty($firstname) || empty($lastname) || empty($username)){
			return json_encode(['status' => 'failed', 'msg' => 'Required fields are missing.']);
		}
		if(!preg_match("/^[a-zA-Z\s'.-]+$/", $firstname)){
			return json_encode(['status' => 'failed', 'msg' => 'First Name contains invalid characters.']);
		}
		if(!preg_match("/^[a-zA-Z\s'.-]+$/", $lastname)){
			return json_encode(['status' => 'failed', 'msg' => 'Last Name contains invalid characters.']);
		}
		if(strlen($firstname) > 255 || strlen($lastname) > 255 || strlen($username) > 255){
			return json_encode(['status' => 'failed', 'msg' => 'Length exceeded.']);
		}

		if(!empty($oldpassword)){
			if(!password_verify($oldpassword, $this->settings->userdata('password'))){
				return json_encode(['status' => 'failed', 'msg' => 'Incorrect Current Password.']);
			}
		}

		$chk_sql = "SELECT id FROM `users` WHERE username = ? " . (!empty($id) ? " AND id != ?" : "");
		$stmt = $this->conn->prepare($chk_sql);
		if(!empty($id)){
			$stmt->bind_param("si", $username, $id);
		} else {
			$stmt->bind_param("s", $username);
		}
		$stmt->execute();
		if($stmt->get_result()->num_rows > 0){
			return json_encode(['status' => 'failed', 'msg' => 'Username already exists.']);
		}

		$allowed = ['firstname', 'middlename', 'lastname', 'username', 'type', 'status'];
		$data_fields = [];
		$data_values = [];
		$types = "";

		foreach($allowed as $field){
			if(isset($_POST[$field])){
				$data_fields[] = "`$field` = ?";
				$data_values[] = trim($_POST[$field]);
				$types .= (is_numeric($_POST[$field]) && $field != 'username') ? "i" : "s";
			}
		}

		if(!empty($password)){
			if(strlen($password) < 8) return json_encode(['status' => 'failed', 'msg' => 'Password must be at least 8 characters long.']);
			$data_fields[] = "`password` = ?";
			$data_values[] = password_hash($password, PASSWORD_BCRYPT);
			$types .= "s";
		}

		if(empty($id)){
			$sql = "INSERT INTO `users` SET " . implode(", ", $data_fields);
		} else {
			$sql = "UPDATE `users` SET " . implode(", ", $data_fields) . " WHERE id = ?";
			$data_values[] = $id;
			$types .= "i";
		}

		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param($types, ...$data_values);
		$save = $stmt->execute();

		if($save){
			$this_id = empty($id) ? $this->conn->insert_id : $id;
			$this->settings->set_flashdata('success','User Details successfully saved.');
			$resp['status'] = 'success';
			
			if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
				$fname = 'uploads/avatar-'.$this_id.'.png';
				$dir_path = base_app. $fname;
				$upload = $_FILES['img']['tmp_name'];
				$type = mime_content_type($upload);
				$allowed_types = array('image/png','image/jpeg');
				if(!function_exists('imagecreatetruecolor')){
					// GD library not enabled
				} else if(in_array($type, $allowed_types)){
					$new_height = 200; 
					$new_width = 200; 
					list($width, $height) = getimagesize($upload);
					$t_image = imagecreatetruecolor($new_width, $new_height);
					imagealphablending($t_image, false);
					imagesavealpha($t_image, true);
					$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
					imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
					if($gdImg){
						if(is_file($dir_path)) unlink($dir_path);
						imagepng($t_image,$dir_path);
						imagedestroy($gdImg);
						imagedestroy($t_image);
						$this->conn->query("UPDATE users SET `avatar` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) WHERE id = '{$this_id}'");
						if($this_id == $this->settings->userdata('id')){
							$this->settings->set_userdata('avatar',$fname);
						}
					}
				}
			}

			if($this_id == $this->settings->userdata('id')){
				foreach($_POST as $k => $v){
					if(in_array($k, ['firstname', 'middlename', 'lastname', 'username', 'type'])){
						$this->settings->set_userdata($k,$v);
					}
				}
			}
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = 'An error occurred while saving user details.';
		}
		return json_encode($resp);
	}

	public function delete_users(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);
		
		$stmt = $this->conn->prepare("SELECT avatar FROM users WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$avatar = ($result->num_rows > 0) ? $result->fetch_array()['avatar'] : '';
		
		$stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
		$stmt->bind_param("i", $id);
		$qry = $stmt->execute();
		if($qry){
			if(!empty($avatar)){
				$avatar = explode("?", $avatar)[0];
				if(is_file(base_app.$avatar)) unlink(base_app.$avatar);
			}
			$this->settings->set_flashdata('success','User Details successfully deleted.');
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}

	public function save_client(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
		$lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
		$email = isset($_POST['email']) ? trim($_POST['email']) : '';
		$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
		$address = isset($_POST['address']) ? trim($_POST['address']) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		$oldpassword = isset($_POST['oldpassword']) ? $_POST['oldpassword'] : '';
		$resp = ['status' => 'failed', 'msg' => ''];

		// Validation
		if(empty($firstname) || empty($lastname) || empty($email) || empty($contact) || empty($address)){
			return json_encode(['status'=>'failed', 'msg'=>'All fields except middle name are required.']);
		}
		if(!preg_match("/^[a-zA-Z\s'.-]+$/", $firstname)){
			return json_encode(['status' => 'failed', 'msg' => 'First Name contains invalid characters.']);
		}
		if(!preg_match("/^[a-zA-Z\s'.-]+$/", $lastname)){
			return json_encode(['status' => 'failed', 'msg' => 'Last Name contains invalid characters.']);
		}
		if(!preg_match("/^[0-9+\s()-]+$/", $contact) || strlen($contact) < 10){
			return json_encode(['status' => 'failed', 'msg' => 'Please provide a valid contact number (at least 10 characters).']);
		}
		if(strlen($address) < 10 || is_numeric($address)){
			return json_encode(['status' => 'failed', 'msg' => 'Please provide a valid and descriptive address.']);
		}
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return json_encode(['status'=>'failed', 'msg'=>'Invalid email format.']);
		}

		if(!empty($id)){
			$stmt = $this->conn->prepare("SELECT id, `password` FROM `client_list` WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				$res = $result->fetch_array();
				if(!empty($oldpassword) && !password_verify($oldpassword, $res['password'])){
					return json_encode(['status'=>'failed', 'msg'=>'Incorrect Current Password.']);
				}
			}
		}

		$chk_sql = "SELECT id FROM `client_list` WHERE email = ? " . (!empty($id) ? " AND id != ?" : "");
		$stmt = $this->conn->prepare($chk_sql);
		if(!empty($id)){
			$stmt->bind_param("si", $email, $id);
		} else {
			$stmt->bind_param("s", $email);
		}
		$stmt->execute();
		if($stmt->get_result()->num_rows > 0){
			return json_encode(['status'=>'failed', 'msg'=>'Email already exists.']);
		}

		$allowed = ['firstname', 'middlename', 'lastname', 'gender', 'contact', 'address', 'email', 'status'];
		$data_fields = [];
		$data_values = [];
		$types = "";

		foreach($allowed as $field){
			if(isset($_POST[$field])){
				$data_fields[] = "`$field` = ?";
				$data_values[] = trim($_POST[$field]);
				$types .= "s";
			}
		}

		if(!empty($password)){
			if(strlen($password) < 8) return json_encode(['status'=>'failed', 'msg'=>'Password must be at least 8 characters long.']);
			$data_fields[] = "`password` = ?";
			$data_values[] = password_hash($password, PASSWORD_BCRYPT);
			$types .= "s";
		}

		if(empty($id)){
			$sql = "INSERT INTO `client_list` SET " . implode(", ", $data_fields);
		} else {
			$sql = "UPDATE `client_list` SET " . implode(", ", $data_fields) . " WHERE id = ?";
			$data_values[] = $id;
			$types .= "i";
		}

		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param($types, ...$data_values);
		$save = $stmt->execute();

		if($save){
			$this_id = empty($id) ? $this->conn->insert_id : $id;
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success', 'User Details successfully saved.');

			if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
				$fname = 'uploads/client-'.$this_id.'.png';
				$dir_path = base_app. $fname;
				$upload = $_FILES['img']['tmp_name'];
				$type = mime_content_type($upload);
				$allowed_types = array('image/png','image/jpeg');
				if(!function_exists('imagecreatetruecolor')){
					// GD library not enabled
				} else if(in_array($type, $allowed_types)){
					$new_height = 200; 
					$new_width = 200; 
					list($width, $height) = getimagesize($upload);
					$t_image = imagecreatetruecolor($new_width, $new_height);
					imagealphablending($t_image, false);
					imagesavealpha($t_image, true);
					$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
					imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
					if($gdImg){
						if(is_file($dir_path)) unlink($dir_path);
						imagepng($t_image,$dir_path);
						imagedestroy($gdImg);
						imagedestroy($t_image);
						$this->conn->query("UPDATE client_list SET `avatar` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) WHERE id = '{$this_id}'");
						if($this_id == $this->settings->userdata('id') && $this->settings->userdata('login_type') == 2){
							$this->settings->set_userdata('avatar',$fname);
						}
					}
				}
			}

			if($this_id == $this->settings->userdata('id') && $this->settings->userdata('login_type') == 2){
				foreach($allowed as $k){
					if(isset($_POST[$k])) $this->settings->set_userdata($k, $_POST[$k]);
				}
			}
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}

	public function delete_client(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);

		$stmt = $this->conn->prepare("SELECT avatar FROM client_list WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$avatar = $stmt->get_result()->fetch_array()['avatar'] ?? '';

		$stmt = $this->conn->prepare("DELETE FROM client_list WHERE id = ?");
		$stmt->bind_param("i", $id);
		if($stmt->execute()){
			if(!empty($avatar)){
				$avatar = explode("?", $avatar)[0];
				if(is_file(base_app.$avatar)) unlink(base_app.$avatar);
			}
			$this->settings->set_flashdata('success','User Details successfully deleted.');
			return json_encode(['status'=>'success']);
		}
		return json_encode(['status'=>'failed']);
	}

	public function verify_client(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);

		$stmt = $this->conn->prepare("UPDATE `client_list` SET `status` = 1 WHERE id = ?");
		$stmt->bind_param("i", $id);
		if($stmt->execute()){
			$this->settings->set_flashdata('success','Client Account has verified successfully.');
			return json_encode(['status'=>'success']);
		}
		return json_encode(['status'=>'failed']);
	}
}

$users = new Users();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
	if(!$_settings->validate_csrf_token($token)){
		echo json_encode(['status'=>'failed', 'msg'=>'CSRF Token Validation Failed.']);
		exit;
	}
}

$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'save': echo $users->save_users(); break;
	case 'delete': echo $users->delete_users(); break;
	case 'save_client': echo $users->save_client(); break;
	case 'delete_client': echo $users->delete_client(); break;
	case 'verify_client': echo $users->verify_client(); break;
	default: break;
}