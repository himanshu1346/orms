<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_message(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
		$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
		$email = isset($_POST['email']) ? trim($_POST['email']) : '';
		$message = isset($_POST['message']) ? trim($_POST['message']) : '';

		// Validation
		if(empty($fullname) || empty($contact) || empty($email) || empty($message)){
			return json_encode(['status' => 'failed', 'msg' => 'All fields are required.']);
		}
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return json_encode(['status' => 'failed', 'msg' => 'Invalid email format.']);
		}
		if(!preg_match("/^[a-zA-Z\s'.-]+$/", $fullname)){
			return json_encode(['status' => 'failed', 'msg' => 'Fullname contains invalid characters.']);
		}
		if(strlen($fullname) > 255) return json_encode(['status' => 'failed', 'msg' => 'Fullname is too long.']);
		if(!preg_match("/^[0-9+\s()-]+$/", $contact) || strlen($contact) < 10){
			return json_encode(['status' => 'failed', 'msg' => 'Please provide a valid contact number (at least 10 characters).']);
		}
		if(strlen($contact) > 50) return json_encode(['status' => 'failed', 'msg' => 'Contact number is too long.']);

		$data_fields = ["`fullname` = ?", "`contact` = ?", "`email` = ?", "`message` = ?"];
		$data_values = [$fullname, $contact, $email, $message];
		$types = "ssss";

		if(empty($id)){
			$sql = "INSERT INTO `message_list` SET " . implode(", ", $data_fields);
		}else{
			$sql = "UPDATE `message_list` SET " . implode(", ", $data_fields) . " WHERE id = ?";
			$data_values[] = $id;
			$types .= "i";
		}
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param($types, ...$data_values);
		$save = $stmt->execute();

		if($save){
			$resp['status'] = 'success';
			if(empty($id)) $resp['msg'] = "Your message has successfully sent.";
			else $resp['msg'] = "Message details has been updated successfully.";
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred.";
			$resp['err'] = $this->conn->error;
		}
		if($resp['status'] =='success' && !empty($id))
			$this->settings->set_flashdata('success',$resp['msg']);
		if($resp['status'] =='success' && empty($id))
			$this->settings->set_flashdata('pop_msg',$resp['msg']);
		return json_encode($resp);
	}
	function delete_message(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);
		$stmt = $this->conn->prepare("DELETE FROM `message_list` where id = ?");
		$stmt->bind_param("i", $id);
		$del = $stmt->execute();
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Message has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_room(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$name = isset($_POST['name']) ? trim($_POST['name']) : '';
		$type = isset($_POST['type']) ? trim($_POST['type']) : '';
		$description = isset($_POST['description']) ? htmlentities(trim($_POST['description'])) : '';
		$price = isset($_POST['price']) ? $_POST['price'] : 0;
		$status = isset($_POST['status']) ? $_POST['status'] : 1;

		// Validation
		if(empty($name) || empty($type) || empty($description)){
			return json_encode(['status' => 'failed', 'msg' => 'Name, Type and Description are required.']);
		}
		if(!is_numeric($price) || $price < 0){
			return json_encode(['status' => 'failed', 'msg' => 'Invalid price.']);
		}
		$chk_sql = "SELECT id FROM `room_list` WHERE `name` = ? " . (!empty($id) ? " AND id != ?" : "");
		$stmt = $this->conn->prepare($chk_sql);
		if(!empty($id)){
			$stmt->bind_param("si", $name, $id);
		} else {
			$stmt->bind_param("s", $name);
		}
		$stmt->execute();
		if($stmt->get_result()->num_rows > 0){
			return json_encode(['status' => 'failed', 'msg' => 'Room name already exists.']);
		}

		if(!preg_match("/^[a-zA-Z0-9\s'.-]+$/", $name)){
			return json_encode(['status' => 'failed', 'msg' => 'Room Name contains invalid characters.']);
		}
		if(is_numeric($name)) return json_encode(['status' => 'failed', 'msg' => 'Room Name cannot be only numeric.']);
		
		if(strlen($name) > 255) return json_encode(['status' => 'failed', 'msg' => 'Room name is too long.']);

		$data_fields = ["`name` = ?", "`type` = ?", "`description` = ?", "`price` = ?", "`status` = ?"];
		$data_values = [$name, $type, $description, $price, $status];
		$types = "sssdi"; // string, string, string, double, integer

		if(empty($id)){
			$sql = "INSERT INTO `room_list` SET " . implode(", ", $data_fields);
		}else{
			$sql = "UPDATE `room_list` SET " . implode(", ", $data_fields) . " WHERE id = ?";
			$data_values[] = $id;
			$types .= "i";
		}

		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param($types, ...$data_values);
		$save = $stmt->execute();

		if($save){
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['id'] = $rid;
			$resp['status'] = 'success';
			if(empty($id))
				$resp['msg'] = "Room has successfully added.";
			else
				$resp['msg'] = "Room details has been updated successfully.";
			if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
				if(!function_exists('imagecreatetruecolor')){
					$resp['msg'].=" Image upload skipped: PHP GD library is not enabled in your server configuration.";
				} else {
					if(!is_dir(base_app.'uploads/rooms'))
					mkdir(base_app.'uploads/rooms');

				$fname = 'uploads/rooms/'.$rid.'.png';
				$dir_path =base_app. $fname;
				$upload = $_FILES['img']['tmp_name'];
				$type_img = mime_content_type($upload);
				$allowed = array('image/png', 'image/jpeg');
				if(!in_array($type_img,$allowed)){
					$resp['msg'].=" But Image failed to upload due to invalid file type.";
				}else{
					$new_height = 400; 
					$new_width = 600; 
			
					list($width, $height) = getimagesize($upload);
					$t_image = imagecreatetruecolor($new_width, $new_height);
					imagealphablending( $t_image, false );
					imagesavealpha( $t_image, true );
					$gdImg = ($type_img == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
					imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
					if($gdImg){
							if(is_file($dir_path))
							unlink($dir_path);
							$uploaded_img = imagepng($t_image,$dir_path);
							imagedestroy($gdImg);
							imagedestroy($t_image);
					}else{
					$resp['msg'].=" But Image failed to upload due to unkown reason.";
					}
				}
					if(isset($uploaded_img) && $uploaded_img === true){
						$this->conn->query("UPDATE room_list set `image_path` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$rid}' ");
					}
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred.";
			$resp['err'] = $this->conn->error;
		}
		if($resp['status'] =='success')
			$this->settings->set_flashdata('success',$resp['msg']);
		return json_encode($resp);
	}
	function delete_room(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);
		$stmt = $this->conn->prepare("UPDATE `room_list` set delete_flag = 1 where id = ?");
		$stmt->bind_param("i", $id);
		$del = $stmt->execute();
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Room has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_service(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$name = isset($_POST['name']) ? trim($_POST['name']) : '';
		$description = isset($_POST['description']) ? trim($_POST['description']) : '';
		$price = isset($_POST['price']) ? $_POST['price'] : 0;
		$status = isset($_POST['status']) ? $_POST['status'] : 1;
		$room_ids = isset($_POST['room_ids']) ? implode(',',$_POST['room_ids']) : '';

		// Validation
		if(empty($name) || empty($description)){
			return json_encode(['status' => 'failed', 'msg' => 'Name and Description are required.']);
		}
		if(!preg_match("/^[a-zA-Z0-9\s'.-]+$/", $name)){
			return json_encode(['status' => 'failed', 'msg' => 'Service Name contains invalid characters.']);
		}
		if(is_numeric($name)) return json_encode(['status' => 'failed', 'msg' => 'Service Name cannot be only numeric.']);

		if(!is_numeric($price) || $price < 0){
			return json_encode(['status' => 'failed', 'msg' => 'Invalid price.']);
		}

		$data_fields = ["`name` = ?", "`description` = ?", "`price` = ?", "`status` = ?", "`room_ids` = ?"];
		$data_values = [$name, $description, $price, $status, $room_ids];
		$types = "ssdis";

		$check_sql = "SELECT id FROM `service_list` where `name` = ? and room_ids = ? and delete_flag = 0 ".(!empty($id) ? " and id != ? " : "");
		$stmt_check = $this->conn->prepare($check_sql);
		if(!empty($id)){
			$stmt_check->bind_param("ssi", $name, $room_ids, $id);
		} else {
			$stmt_check->bind_param("ss", $name, $room_ids);
		}
		$stmt_check->execute();
		$check = $stmt_check->get_result()->num_rows;

		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Service already exists for these rooms.";
		}else{
			if(empty($id)){
				$sql = "INSERT INTO `service_list` SET " . implode(", ", $data_fields);
			}else{
				$sql = "UPDATE `service_list` SET " . implode(", ", $data_fields) . " WHERE id = ?";
				$data_values[] = $id;
				$types .= "i";
			}
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param($types, ...$data_values);
			$save = $stmt->execute();
			if($save){
				$resp['status'] = 'success';
				if(empty($id))
					$resp['msg'] = "Service has successfully added.";
				else
					$resp['msg'] = "Service has been updated successfully.";
			}else{
				$resp['status'] = 'failed';
				$resp['msg'] = "An error occurred.";
				$resp['err'] = $this->conn->error;
			}
			if($resp['status'] =='success')
				$this->settings->set_flashdata('success',$resp['msg']);
		}
		return json_encode($resp);
	}
	function delete_service(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);
		$stmt = $this->conn->prepare("UPDATE `service_list` set delete_flag = 1 where id = ?");
		$stmt->bind_param("i", $id);
		$del = $stmt->execute();
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Service has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_reservation(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$room_id = isset($_POST['room_id']) ? $_POST['room_id'] : '';
		$check_in = isset($_POST['check_in']) ? $_POST['check_in'] : '';
		$check_out = isset($_POST['check_out']) ? $_POST['check_out'] : '';
		$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
		$email = isset($_POST['email']) ? trim($_POST['email']) : '';
		$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
		$address = isset($_POST['address']) ? trim($_POST['address']) : '';
		$status = isset($_POST['status']) ? $_POST['status'] : 0;
		
		// Validation
		if(empty($room_id) || empty($check_in) || empty($check_out) || empty($fullname) || empty($email) || empty($contact)){
			return json_encode(['status' => 'failed', 'msg' => 'Required fields are missing.']);
		}
		if(!preg_match("/^[a-zA-Z\s'.-]+$/", $fullname)){
			return json_encode(['status' => 'failed', 'msg' => 'Full Name contains invalid characters.']);
		}
		if(is_numeric($address) || (!empty($address) && strlen($address) < 10)){
			return json_encode(['status' => 'failed', 'msg' => 'Please provide a valid and descriptive address.']);
		}
		if(!preg_match("/^[0-9+\s()-]+$/", $contact) || strlen($contact) < 10){
			return json_encode(['status' => 'failed', 'msg' => 'Please provide a valid contact number (at least 10 characters).']);
		}
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return json_encode(['status' => 'failed', 'msg' => 'Invalid email format.']);
		}
		if(strtotime($check_in) >= strtotime($check_out)){
			return json_encode(['status' => 'failed', 'msg' => 'Check-out date must be after check-in date.']);
		}
		if(strtotime($check_in) < strtotime(date('Y-m-d'))){
			return json_encode(['status' => 'failed', 'msg' => 'Check-in date cannot be in the past.']);
		}

		if(empty($id)){
			$prefix = date("Ym")."-";
			$stmt = $this->conn->prepare("SELECT code FROM `reservation_list` WHERE `code` LIKE ? ORDER BY `code` DESC LIMIT 1");
			$like_prefix = $prefix."%";
			$stmt->bind_param("s", $like_prefix);
			$stmt->execute();
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				$last_code = $result->fetch_assoc()['code'];
				$code_num = (int)substr($last_code, -4);
				$code = sprintf("%'.04d", $code_num + 1);
			}else{
				$code = sprintf("%'.04d", 1);
			}
			$code = $prefix.$code;
		} else {
			$code = isset($_POST['code']) ? $_POST['code'] : '';
		}

		// Prepared Statement Overlap Logic
		$overlap_sql = "SELECT id FROM `reservation_list` 
						WHERE room_id = ? 
						AND `status` IN (0,1) 
						AND (
							(? < check_out AND ? > check_in)
						) " . (!empty($id) ? " AND id != ?" : "");
		
		$stmt = $this->conn->prepare($overlap_sql);
		if(!empty($id)){
			$stmt->bind_param("sssi", $room_id, $check_in, $check_out, $id);
		} else {
			$stmt->bind_param("sss", $room_id, $check_in, $check_out);
		}
		$stmt->execute();
		$check = $stmt->get_result()->num_rows;

		if($check > 0){
			$resp['status'] = "failed";
			$resp['msg'] = "Your Date of Reservation for this room complicates with other reservations.";
			return json_encode($resp);
		}

		$allowed_fields = ['client_id', 'code', 'room_id', 'check_in', 'check_out', 'fullname', 'contact', 'email', 'address', 'status'];
		if($this->settings->userdata('id') > 0 && $this->settings->userdata('login_type') == 2){
			$_POST['client_id'] = $this->settings->userdata('id');
		}

		$data_fields = [];
		$data_values = [];
		$types = "";
		
		foreach($allowed_fields as $field){
			if(isset($_POST[$field]) || $field == 'code'){
				$val = ($field == 'code') ? $code : $_POST[$field];
				$data_fields[] = "`{$field}` = ?";
				$data_values[] = $val;
				$types .= (is_numeric($val) && $field != 'contact' && $field != 'code') ? "i" : "s";
			}
		}

		if(empty($id)){
			$sql = "INSERT INTO `reservation_list` SET " . implode(", ", $data_fields);
		}else{
			$sql = "UPDATE `reservation_list` SET " . implode(", ", $data_fields) . " WHERE id = ?";
			$data_values[] = $id;
			$types .= "i";
		}
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param($types, ...$data_values);
		$save = $stmt->execute();

		if($save){
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['id'] = $rid;
			$resp['status'] = 'success';
			if(empty($id))
				$resp['msg'] = "Room Reservation has successfully submitted.";
			else
				$resp['msg'] = "Room Reservation details has been updated successfully.";
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred.";
			$resp['err'] = $this->conn->error;
		}
		
		if($resp['status'] =='success')
			$this->settings->set_flashdata('success',$resp['msg']);
		return json_encode($resp);
	}
	function delete_reservation(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);
		$stmt = $this->conn->prepare("DELETE FROM `reservation_list` where id = ?");
		$stmt->bind_param("i", $id);
		$del = $stmt->execute();
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Reservation Details has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function update_reservation_status(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$status = isset($_POST['status']) ? $_POST['status'] : '';
		$stmt = $this->conn->prepare("UPDATE `reservation_list` set `status` = ? where id = ?");
		$stmt->bind_param("ii", $status, $id);
		$del = $stmt->execute();
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Reservation status has been updated successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_activity(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$name = isset($_POST['name']) ? trim($_POST['name']) : '';
		$description = isset($_POST['description']) ? htmlentities(trim($_POST['description'])) : '';
		$status = isset($_POST['status']) ? $_POST['status'] : 1;

		// Validation
		if(empty($name) || empty($description)){
			return json_encode(['status' => 'failed', 'msg' => 'Name and Description are required.']);
		}
		if(strlen($name) > 255) return json_encode(['status' => 'failed', 'msg' => 'Activity name is too long.']);

		$data_fields = ["`name` = ?", "`description` = ?", "`status` = ?"];
		$data_values = [$name, $description, $status];
		$types = "ssi";

		if(!preg_match("/^[a-zA-Z0-9\s'.-]+$/", $name)){
			return json_encode(['status' => 'failed', 'msg' => 'Activity Name contains invalid characters.']);
		}
		if(is_numeric($name)) return json_encode(['status' => 'failed', 'msg' => 'Activity Name cannot be only numeric.']);

		$chk_sql = "SELECT id FROM `activity_list` WHERE `name` = ? " . (!empty($id) ? " AND id != ?" : "");
		$stmt = $this->conn->prepare($chk_sql);
		if(!empty($id)){
			$stmt->bind_param("si", $name, $id);
		} else {
			$stmt->bind_param("s", $name);
		}
		$stmt->execute();
		if($stmt->get_result()->num_rows > 0){
			return json_encode(['status' => 'failed', 'msg' => 'Activity name already exists.']);
		}

		if(empty($id)){
			$sql = "INSERT INTO `activity_list` set " . implode(", ", $data_fields);
		}else{
			$sql = "UPDATE `activity_list` set " . implode(", ", $data_fields) . " where id = ?";
			$data_values[] = $id;
			$types .= "i";
		}
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param($types, ...$data_values);
		$save = $stmt->execute();
		if($save){
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['id'] = $rid;
			$resp['status'] = 'success';
			if(empty($id))
				$resp['msg'] = "Activity has successfully added.";
			else
				$resp['msg'] = "Activity details has been updated successfully.";
			if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
				if(!function_exists('imagecreatetruecolor')){
					$resp['msg'].=" Image upload skipped: PHP GD library is not enabled in your server configuration.";
				} else {
					if(!is_dir(base_app.'uploads/activitys'))
						mkdir(base_app.'uploads/activitys');

					$fname = 'uploads/activitys/'.$rid.'.png';
					$dir_path =base_app. $fname;
					$upload = $_FILES['img']['tmp_name'];
					$type_img = mime_content_type($upload);
					$allowed = array('image/png', 'image/jpeg');
					if(!in_array($type_img,$allowed)){
						$resp['msg'].=" But Image failed to upload due to invalid file type.";
					}else{
						$new_height = 400; 
						$new_width = 600; 
				
						list($width, $height) = getimagesize($upload);
						$t_image = imagecreatetruecolor($new_width, $new_height);
						imagealphablending( $t_image, false );
						imagesavealpha( $t_image, true );
						$gdImg = ($type_img == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
						imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
						if($gdImg){
								if(is_file($dir_path))
								unlink($dir_path);
								$uploaded_img = imagepng($t_image,$dir_path);
								imagedestroy($gdImg);
								imagedestroy($t_image);
						}else{
							$resp['msg'].=" But Image failed to upload due to unkown reason.";
						}
					}
					if(isset($uploaded_img) && $uploaded_img === true){
						$this->conn->query("UPDATE activity_list set `image_path` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$rid}' ");
					}
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred.";
			$resp['err'] = $this->conn->error;
		}
		if($resp['status'] =='success')
			$this->settings->set_flashdata('success',$resp['msg']);
		return json_encode($resp);
	}
	function delete_activity(){
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if(empty($id)) return json_encode(['status'=>'failed', 'msg'=>'ID is required']);
		$stmt = $this->conn->prepare("UPDATE `activity_list` set delete_flag = 1 where id = ?");
		$stmt->bind_param("i", $id);
		$del = $stmt->execute();
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Activity has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
}

$master = new Master();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
	if(!$_settings->validate_csrf_token($token)){
		echo json_encode(['status'=>'failed', 'msg'=>'CSRF Token Validation Failed.']);
		exit;
	}
}

$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'save_message':
		echo $master->save_message();
	break;
	case 'delete_message':
		echo $master->delete_message();
	break;
	case 'save_room':
		echo $master->save_room();
	break;
	case 'delete_room':
		echo $master->delete_room();
	break;
	case 'save_service':
		echo $master->save_service();
	break;
	case 'delete_service':
		echo $master->delete_service();
	break;
	case 'save_reservation':
		echo $master->save_reservation();
	break;
	case 'delete_reservation':
		echo $master->delete_reservation();
	break;
	case 'update_reservation_status':
		echo $master->update_reservation_status();
	break;
	case 'save_activity':
		echo $master->save_activity();
	break;
	case 'delete_activity':
		echo $master->delete_activity();
	break;
	default:
		// echo $sysset->index();
		break;
}