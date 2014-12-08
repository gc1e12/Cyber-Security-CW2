<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);

			//verify captcha code
			if ($captcha === $_SESSION['captcha_code']){

				//check if subject and message is not empty
				if ($from !== '' && $subject !== '' && $message !== ''){
					
					$from = "From: $from";
					$to = "root@localhost";
					mail($to,$subject,$message,$from);

					StatusMessage::add('Thank you for contacting us');

					unset($_SESSION['captcha_code']); // clear the session variable.

					return $f3->reroute('/');
				}else{
					StatusMessage::add('Please fill in all the fields.','danger');
				}
				


			}else{ // if incorrect captcha provided.
   				StatusMessage::add('Please enter a valid captcha code','danger');
			}

				
		}	
	}

}

?>
