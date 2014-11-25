<?php

//Load framework
chdir("..");
$f3=require('lib/base.php');
$f3->config('/config/config.cfg');
$f3->set('AUTOLOAD','controllers/; models/; helpers/; utility/;');

//Linuxproj home directory fix
if(stripos($_SERVER['REQUEST_URI'],'~') !== false) { 
	$f3->set('BASE',str_replace('%7E','~',$f3->get('BASE')));
	$f3->set('PATH',substr($f3->get('PATH'),strlen($f3->get('BASE'))));
}

//Get editor details
$CKEditor = $_GET['CKEditor'] ;
$funcNum = $_GET['CKEditorFuncNum'] ;

//Initialise return values
$url = '';
$message = '';

//Process uploaded file
if (isset($_FILES['upload'])) {
	$url = $f3->get('BASE') . '/../' . File::Upload($_FILES['upload']); 
	if(!$url) {
		$message = 'The upload failed';
	} 
} else {
	$message = 'No file was uploaded';
}

//Return to CKEditor
echo "<script type='text/javascript'> window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message')</script>";

?>
