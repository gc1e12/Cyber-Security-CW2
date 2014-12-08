<?php
class User extends Controller {
	
	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);

		if ($u === false){
			return $f3->reroute('/');
		}

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {

		if($this->request->is('post')) {

			extract($this->request->data);

			$validation = true; // status of the validation.

			if ($captcha === $_SESSION['captcha_code']){
				//check if username is empty
				if (strlen($username)!== 0 && strlen($displayname)!== 0 && strlen($password) !== 0 && strlen($password2) !== 0 && strlen($password) !== 0){

					//check if username existed
					$check = $this->Model->Users->fetch(array('username' => $username));
					if (!empty($check)) { //print error message if user existed
						StatusMessage::add('User already exists','danger');
						$validation = false;
					} else if($password != $password2 || strlen($password)<10) { //print error message if passwords do not matched or $password is too short.
						StatusMessage::add('Passwords must match AND must be at least 10 character long','danger');
						$validation = false;
					} else if($validation === true){ // process the form if all field requirement are fulfill
						$user = $this->Model->Users;
						$user->copyfrom('POST');
						$user->created = mydate();
						$user->bio = '';
						$user->level = 1;
						if(empty($displayname)) {
							$user->displayname = $user->username;
						}
						// encrypt the password before storing it in the database
						$user->password = bcrypthash($user->password);
						$user->save();	
						StatusMessage::add('Registration complete','success');
						return $f3->reroute('/user/login');
					}

				}else{
					StatusMessage::add('All field is mandatory.','danger');
				}
			}else{ // if incorrect captcha provided.
   				StatusMessage::add('Please enter a valid captcha code','danger');
			}

			
		}
	}

	public function login($f3) {
		if ($this->request->is('post')) {

			list($username,$password, $captcha) = array($this->request->data['username'], $this->request->data['password'], $this->request->data['captcha']);
   			
   			// check if captcha code is valid before processing the login request.
   			if ($captcha === $_SESSION['captcha_code']){

   				if ($this->Auth->login($username,$password)) { // validation process for user login.
   			 		StatusMessage::add('Logged in succesfully','success');
   			 		
	   			 	if(isset($_GET['from'])) {
	   			 		if($_GET['from'] != "/user/login"){
	   			 			$f3->reroute($_GET['from']);
	   			 		}
	   			 		$f3->reroute('/');
	   			 	}

	   			} else {
	   			  StatusMessage::add('Invalid username or password','danger');
	   			}

   			} else { // if incorrect captcha provided.
   			 StatusMessage::add('Please enter a valid captcha code','danger');
   			}
		}		
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');	
	}


	public function profile($f3) {
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);

		$pwhash = $u->password; //store the current password

		if($this->request->is('post')) {
			$u->copyfrom('POST');

			// accepted upload extension.
			$whiteList = array(
				'gif' => 'image/gif',
				'png' => 'image/png',
				'jpeg' =>'image/jpeg',
				'jpg' => 'image/jpeg',
				'bmp' => 'image/bmp'
			);
			
			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['error'] == false) {

				$filename = basename($_FILES['avatar']['name']);
				$getLastFileExtension = strtolower((new SplFileInfo($filename))->getExtension());
				
				//check if it is a valid extension
				if(array_key_exists($getLastFileExtension, $whiteList) === true && ($_FILES['avatar']['type'])=== $whiteList[$getLastFileExtension]){ 
					$url = File::Upload($_FILES['avatar']);
					$u->avatar = $url;
				}else{
					$u->avatar = '';
					\StatusMessage::add('Invalid file extension','danger');
				}

			} else if(isset($reset)) {
				$u->avatar = '';
			}
			
			//check if the password field is empty
			if (strlen($_POST['password']) !== 0) {
				$u->password = bcrypthash($u->password);
			}else{
				$u->password = $pwhash;
			}
			
			$u->save();
			\StatusMessage::add('Profile updated succesfully','success');
			return $f3->reroute('/user/profile');
		}			
		$_POST = $u->cast();
		unset($_POST['password']); // do not display the password variable

		$f3->set('u',$u);
	}

}
?>
