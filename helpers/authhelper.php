<?php

	class AuthHelper {

		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}

		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();				

			//Ignore if already running session	
			if($f3->exists('SESSION.user.id')) return;

			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {
				$user = unserialize(base64_decode($f3->get('COOKIE.RobPress_User')));
				$this->forceLogin($user);
			}
		}		

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			$f3=Base::instance();
			$crypt = \Bcrypt::instance();						
			$db = $this->controller->db;
			
			$results = $this->controller->Model->users->fetch(array('username' => $username)); // use the find function to retrive a single SQL mapped object.

			if ($results) { // check if the object not empty
				$results = $results->cast(); // convert the obejct into an array.
				//check if the hashpassword is identical with the stored password. (string pw, hash pw)
				if($crypt->verify ($password, $results['password'])===true) {
					$user = $results;	
					$this->setupSession($user);
					return $this->forceLogin($user);
				}				
			} 
			return false;
		}

		/** Log user out of system */
		public function logout() {
			$f3=Base::instance();							

			//Kill the session
			session_destroy();

			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}

		/** Set up the session for the current user */
		public function setupSession($user) {
			//Remove previous session
			session_destroy();

			//Setup new session
			session_id(md5($user['id']));

			//store the session in the database
			//$db = $this->controller->db;
			//$this->$db->exec('UPDATE users SET `session` = :session WHERE `id` = :id', 
			//						array('session' => 'test', 'id' => $user['id']));

			//Setup cookie for storing user details and for relogging in
			setcookie('RobPress_User',base64_encode(serialize($user)),time()+3600*24*30,'/');

			//And begin!
			new Session();
		}

		/** Not used anywhere in the code, for debugging only */
		public function specialLogin($username) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3 = Base::instance();
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			$array = $user->cast();
			return $this->forceLogin($array);
		}

		/** Force a user to log in and set up their details */
		public function forceLogin($user) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3=Base::instance();						
			$f3->set('SESSION.user',$user);
			return $user;
		}

		/** Get information about the current user */
		public function user($element=null) {
			$f3=Base::instance();
			if(!$f3->exists('SESSION.user')) { return false; }
			if(empty($element)) { return $f3->get('SESSION.user'); }
			else { return $f3->get('SESSION.user.'.$element); }
		}

	}

?>
