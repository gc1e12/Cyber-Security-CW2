<?php

class File {

	public static function Upload($array) {
		$f3 = Base::instance();
		extract($array);
		$directory = getcwd() . '/uploads';
		$destination = $directory . '/' . $name;
		$webdest = '/uploads/' . $name;
		if (move_uploaded_file($tmp_name,$destination)) {
			return $webdest;
		} else {
			return false;
		}
	}

}

?>
