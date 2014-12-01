<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();
		$f3->set('users',$users);
	}

	public function edit($f3) {	
		$bcrypt = \Bcrypt::instance();	
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($id);
		//stored the oldpw
		$oldpw = $u->password;

		if($this->request->is('post')) {
			$u->copyfrom('POST');

			//1) Check if the password is changed  ---- AND ---- the old password hash is not equal to the newpassword hash
			if ($this->request->data['password'] !== $oldpw && $oldpw !== $bcrypt->hash($this->request->data['password'],null, 10)) {
				$u->password = $bcrypt->hash($u->password,null, 10);
				
			}else{ // if new password hash === old password hash, then do not change the password (set it back to the oldpw hash)
				$u->password = $oldpw;
			}

			$u->save();
			\StatusMessage::add('User updated succesfully','success');
			return $f3->reroute('/admin/user');
		}			
		$_POST = $u->cast();
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
