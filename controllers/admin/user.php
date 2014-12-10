<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();
		$f3->set('users',$users);
	}

	public function edit($f3) {	
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetchById($id);

		//check if it is a valid user and id is numeric
		//if not valid users or params is not numeric redirect back to /admin/user page.
		if(!is_numeric($id) || !$u){
			return $f3->reroute('/admin/user');
		}

		//stored the oldpw
		$pwhash = $u->password; //store the current password

		if($this->request->is('post')) {
			$u->copyfrom('POST');

			//check if the password field is empty
			if (strlen($_POST['password']) !== 0) {
				$u->password = bcrypthash($u->password);
			}else{
				$u->password = $pwhash;
			}

			$u->save();
			\StatusMessage::add('User updated succesfully','success');
			return $f3->reroute('/admin/user');
		}			
		$_POST = $u->cast();
		unset($_POST['password']); // clear the password variable.
		$f3->set('u',$u);
	}

	public function delete($f3) {
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($id);

		if($id == $this->Auth->user('id')) {
			\StatusMessage::add('You cannot remove yourself','danger');
			return $f3->reroute('/admin/user');
		}

		//Remove all posts and comments
		$posts = $this->Model->Posts->fetchAll(array('user_id' => $id));
		foreach($posts as $post) {
			$post_categories = $this->Model->Post_Categories->fetchAll(array('post_id' => $post->id));
			foreach($post_categories as $cat) {
				$cat->erase();
			}
			$post->erase();
		}
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $id));
		foreach($comments as $comment) {
			$comment->erase();
		}
		$u->erase();

		\StatusMessage::add('User has been removed','success');
		return $f3->reroute('/admin/user');
	}


}

?>
