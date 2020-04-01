<?php

use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Products;

$app->get("/admin/products",function(){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
	$product = new Products();

	$produts = $product->getProducts();
	$page = new PageAdmin();

	$page->setTpl("products",array(
		"products" => $produts
	));
});

$app->get("/admin/products/create",function(){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
	$page = new PageAdmin();

	$page->setTpl("products-create");
});

$app->post("/admin/products/create",function(){
    
    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
	$product = new Products();

    $product->setData($_POST);


	$product->createProduct();

    header('Location: /admin/produts');	
    exit;
});

$app->get("/admin/products/:ID",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }

    $product = new Products();

    $product->getProduct((int)$id);

	$page = new PageAdmin();

	$page->setTpl("products-update",array(
		"product" => $product->getValues()
    ));
});

$app->post("/admin/products/:ID",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }

    $product = new Products();

    $product->getProduct((int)$id);

    $product->setData($_POST);

    $product->updateProduct($id);

    $product->setPhoto($_FILES["file"]);

    header('Location: /admin/products');	
    exit;

});

$app->get("/admin/products/:ID/delete",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }

    $product = new Products();

    $product->deleteProduct((int)$id);

    header('Location: /admin/products');	
    exit;


});

