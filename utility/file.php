<?php

class File {

	public static function Upload($array) {
		$f3 = Base::instance();
		extract($array);
		$directory = getcwd() . '/uploads';
		$name = uniqid() . $name;
		$destination = $directory . '/' . $name;
		$webdest = '/uploads/' . $name;
		if (move_uploaded_file($tmp_name,$destination)) {
			chmod($destination,0644);
			return $webdest;
		} else {
			return false;
		}
	}

}
//chmod --> change file permission. change so everyone can read the file.
?>
