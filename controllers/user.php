<?php
class User extends Controller {
	
	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		$bcrypt = \Bcrypt::instance();

		if($this->request->is('post')) {
			extract($this->request->data);
			$check = $this->Model->Users->fetch(array('username' => $username));
			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
			} else if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
			} else {
				$user = $this->Model->Users;
				$user->copyfrom('POST');
				$user->created = mydate();
				$user->bio = '';
				$user->level = 1;
				if(empty($displayname)) {
					$user->displayname = $user->username;
				}
				// encrypt the password before storing it in the database
				$user->password = $bcrypt ->hash($user->password,null, 10); 
				$user->save();	
				StatusMessage::add('Registration complete','success');
				return $f3->reroute('/user/login');
			}
		}
	}

	public function login($f3) {
		if ($this->request->is('post')) {
			/*list($username,$password) = array($this->request->data['username'],$this->request->data['password']);

			if ($this->Auth->login($username,$password)) {
				StatusMessage::add('Logged in succesfully','success');
			
				if(isset($_GET['from'])) {
					$f3->reroute($_GET['from']);
				} else {
					$f3->reroute('/');	
				}
			} else {
				StatusMessage::add('Invalid username or password','danger');
			}*/

			list($username,$password, $captcha) = array($this->request->data['username'], $this->request->data['password'], $this->request->data['captcha']);
   			
   			if (trim($captcha) == ''){
   			  StatusMessage::add('You need to provide captcha as well','danger');
   			} elseif ($captcha == $_SESSION['captcha_code']){
   			 if ($this->Auth->login($username,$password)) {
   			  StatusMessage::add('Logged in succesfully','success');
   			 
   			  if(isset($_GET['from'])) {
   			   $f3->reroute($_GET['from']);
   			  } else {
   			   $f3->reroute('/'); 
   			  }
   			 } else {
   			  StatusMessage::add('Invalid username or password','danger');
   			 }
   			} else {
   			 StatusMessage::add('Invalid captcha','danger');
   			}
		}		
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');	
	}


	public function profile($f3) {
		$bcrypt = \Bcrypt::instance();	
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);
		//stored the oldpw
		$oldpw = $u->password;

		if($this->request->is('post')) {
			$u->copyfrom('POST');

			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
				$url = File::Upload($_FILES['avatar']);
				$u->avatar = $url;
			} else if(isset($reset)) {
				$u->avatar = '';
			}
			//1) Check if the password is changed  ---- AND ---- the old password hash is not equal to the newpassword hash
			if ($this->request->data['password'] !== $oldpw && $oldpw !== $bcrypt->hash($this->request->data['password'],null, 10)) {
				$u->password = $bcrypt->hash($u->password,null, 10);
				
			}else{ // if new password hash === old password hash, then do not change the password (set it back to the oldpw hash)
				$u->password = $oldpw;
				
			}
			
			$u->save();
			\StatusMessage::add('Profile updated succesfully','success');
			return $f3->reroute('/user/profile');
		}			
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}

}
?>
