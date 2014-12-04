<?php

class Page extends Controller {

	function display($f3) {
		$pagename = urldecode($f3->get('PARAMS.3'));
		$page = $this->Model->Pages->fetch($pagename);

		if($page === false){
			StatusMessage::add('Invalid Page','danger');
			return $f3->reroute('/');
		}

		$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
		
	}

}

?>
