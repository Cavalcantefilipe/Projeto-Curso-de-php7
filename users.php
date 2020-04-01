<?php

use Hcode\PageAdmin;
use Hcode\Model\User;


$app->get('/admin/users', function() {

	if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }

	$search = (isset($_GET['search'])) ? $_GET['search'] : '';

	$users = User::listAll($search);
	$page = new PageAdmin();

	$page->setTpl("users",array(
		"users"=>$users,
		'search'=>$search
	));

});

$app->get('/admin/users/create', function() {

	if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
	$page = new PageAdmin();

	$page->setTpl("users-create");
	
});

$app->get('/admin/users/:iduser/delete', function($idUser) {

	if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
	$user = new user();
	
	$user->deleteUser($idUser);

	header("Location: /admin/users");
	exit;
	
});

$app->get('/admin/users/:iduser', function($idUser) {

	if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
	$user=new user();

	$user->getUser((int)$idUser);

	$page = new PageAdmin();

	$page->setTpl("users-update",array(
		"user"=>$user->getValues()
	));
	
});

$app->post('/admin/users/create', function() {

	if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }

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

	if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->getUser((int)$idUser);
	$user->setData($_POST);

	$user->updateUser();

	header("Location: /admin/users");
	exit;
	
});
