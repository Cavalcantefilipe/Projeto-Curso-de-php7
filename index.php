<?php 
session_start();
require_once("vendor/autoload.php");
require_once("functions.php");

use Hcode\Model\User;
use Hcode\PageAdmin;

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Hcode\Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {
    
	User::verifyLogin();

	$page = new Hcode\PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function() {

	User::login(post('deslogin'), post('despassword'));

	header("Location: /admin");
	exit;

});

$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;

});

$app->get('/admin/users', function() {

	User::verifyLogin();

	$users = User::listAll();
	$page = new PageAdmin();

	$page->setTpl("users",array(
		"users"=>$users
	));

});

$app->get('/admin/users/create', function() {

	User::verifyLogin();
	$page = new PageAdmin();

	$page->setTpl("users-create");
	
});

$app->get('/admin/users/:iduser/delete', function($idUser) {

	User::verifyLogin();
	$user = new user();
	
	$user->deleteUser($idUser);

	header("Location: /admin/users");
	exit;
	
});

$app->get('/admin/users/:iduser', function($idUser) {

	User::verifyLogin();
	$user=new user();

	$user->getUser((int)$idUser);

	$page = new PageAdmin();

	$page->setTpl("users-update",array(
		"user"=>$user->getValues()
	));
	
});

$app->post('/admin/users/create', function() {

	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setData($_POST);

	$user->createUser();

	header("Location: /admin/users");
	exit;
	
});

$app->post('/admin/users/:iduser', function($idUser) {

	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->getUser((int)$idUser);
	$user->setData($_POST);

	$user->updateUser();

	header("Location: /admin/users");
	exit;
	
});


$app->get('/admin/forgot', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");

});

$app->post('/admin/forgot', function() {

	$user = new User();
	$user->getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

$app->get('/admin/forgot/sent',function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset",function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
	
});

$app->post("/admin/forgot/reset",function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->getUser((int)$forgot["iduser"]);

	$user->setPassword($_POST["password"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success", array(
		"name"=>$forgot["desperson"],
		"code"=>$_POST["code"]
	));
	
});


$app->run();

 ?>