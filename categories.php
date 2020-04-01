<?php

use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;
use Hcode\Model\Products;
use Hcode\Page;

$app->get("/admin/categories",function(){

	User::checkLogin();
	
	$page = new PageAdmin();

	$categories = Category::getCategories();
	$page->setTpl("categories", array(
		"categories"=> $categories
	));
	
});

$app->get("/admin/categories/create",function(){

	User::checkLogin();
	$page = new PageAdmin();

	$page->setTpl("categories-create");
	
});

$app->post("/admin/categories/create",function(){

	User::checkLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->createCategory();

	header('Location: /admin/categories');	

	exit;
});


$app->get("/admin/categories/:id/delete",function($id){

	User::checkLogin();

	$category = new Category();

	$category->deleteCategory((int)$id);

	header('Location: /admin/categories');	

	exit;
});

$app->get("/admin/categories/:id",function($id){

	User::checkLogin();

	$category = new Category();

	$category->getCategory((int)$id);

	$page = new PageAdmin();

	$page->setTpl("categories-update",array(
		"category" => $category->getValues()
	));
});

$app->post("/admin/categories/:id",function($id){

	User::checkLogin();
	
	$category = new Category();

	$category->setData($_POST);

	$category->updateCategory($id);

	header('Location: /admin/categories');	
	exit;

});

$app->get("/admin/categories/:idcategory/products",function($idcategory)
{
	User::checkLogin();

	$category = new Category();

	$category->getCategory((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products",[
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProductsbyCategory(),
		"productsNotRelated"=>$category->getProductsbyCategory(false)
	]);
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add",
function($idcategory,$idproduct)
{
	User::checkLogin();

	$category = new Category();

	$category->getCategory((int)$idcategory);

	$product = new Products();

	$product->getProduct((int)$idproduct);

	$category->addProductToCategory($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove",
function($idcategory,$idproduct)
{
	User::checkLogin();

	$category = new Category();

	$category->getCategory((int)$idcategory);

	$product = new Products();

	$product->getProduct((int)$idproduct);

	$category->removeProductToCategory($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});
