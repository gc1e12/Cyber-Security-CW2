<?php

//Start session
session_name("RobPress");
session_start();

//Load framework
$f3=require('lib/base.php');
$f3->config('config/config.cfg');
$f3->set('AUTOLOAD','controllers/; models/; helpers/; utility/;');

//Linuxproj home directory fix
if(stripos($_SERVER['REQUEST_URI'],'~') !== false) { 
	$f3->set('BASE',str_replace('%7E','~',$f3->get('BASE')));
	$f3->set('PATH',substr($f3->get('PATH'),strlen($f3->get('BASE'))));
}

//Load configuration
$f3->config('config/db.cfg');

//Load global functions
include_once("functions.php");

//Activate session
new Session();

//Define homepage 
$f3->route('GET /','Blog->index');

//Define admin routes
$f3->route('GET|POST /admin','Admin\Admin->index');
$f3->route('GET|POST /admin/@controller','Admin\@controller->index');
$f3->route('GET|POST /admin/@controller/@action','Admin\@controller->@action');
$f3->route('GET|POST /admin/@controller/@action/*','Admin\@controller->@action');

//Define default routes
$f3->route('GET|POST /@controller','@controller->index');
$f3->route('GET|POST /@controller/@action','@controller->@action');
$f3->route('GET|POST /@controller/@action/*','@controller->@action');

//Define API 
$f3->route('GET|POST /api/@model','API->display');
$f3->route('GET|POST /api/@model/@id','API->display');

//Run!
$f3->run();

?>
