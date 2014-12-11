<?php

	class AuthHelper {

		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}

		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();				

			//verify the session
			if($f3->exists('SESSION.id')) {
				$sessionId = $f3->get('SESSION.id');
				$user = $this->controller->db->query("SELECT * FROM users WHERE session = '$sessionId'"); // retrieve the user row from the database with the uniqid()

				if (empty($user)){ // invalid
					$this->logout();
					//$f3->clear('SESSION'); // destroy the session
					$f3->reroute('/'); // if it is empty then, reroute to hompage.

				}else{ //ignore if it is a valid 
					return; //ignore
				}
				
			} 

			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {

				$usercookievalue = $f3->get('COOKIE.RobPress_User'); //get the cookie value
				$user = $this->controller->db->query("SELECT * FROM users WHERE cookie = '$usercookievalue'"); // retrieve the user row from the database with the uniqid()
				if (!empty($user)){
					$this->forceLogin($user[0]); // force login using the first item in the array
				}else{
					$f3->reroute('/'); // if it is empty then, reroute to hompage.
				}
				
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
			$f3->clear('SESSION'); // destroy the session

			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}

		/** Set up the session for the current user */
		public function setupSession($user) {
			//Remove previous session
			session_destroy();

			$currentUserid = $user['id']; // user id to store the data.

			$userSession = uniqid(rand(),true); //generate a uniquid, storing it in the cookie.
			//Setup new session
			$f3 = Base::instance();
			$f3->set("SESSION.id", $userSession); //set up a session variable
			//store the session in the database
			$update = $this->controller->db->query("UPDATE users SET session = '$userSession' WHERE id = $currentUserid");	


			
			$usercookie = uniqid(rand(),true); //generate a uniquid, storing it in the cookie.
			setcookie('RobPress_User',$usercookie,time()+3600*24*30,'/'); //Setup cookie for storing user details and for relogging in
			//store the cookie in the database
			$update = $this->controller->db->query("UPDATE users SET cookie = '$usercookie' WHERE id = $currentUserid");			

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
