<?php

use Hcode\Model\Cart;
use Hcode\Model\User;

function formatPrice($vlprice)
{
	$vlprice=(float)$vlprice;
	if($vlprice){
		return number_format($vlprice,2,",",".");
	}else{
		return 0;
	}

}

function formatDate($date)
{
	
	return date('d/m/Y', strtotime($date));

}

function checkLogin($inadmin = true)
{
	return User::checkLogin($inadmin);
}

function getUserName()
{
	$user = User::getFromSession();

	return $user->getdesperson();

}


function getCartNrQtd(){

	$cart = Cart::getFromSession();

	$totals= $cart->getProductsCartTotal();

	return $totals['nrqtd'];

}

function getCartVlsubtotal(){

	$cart = Cart::getFromSession();

	$totals= $cart->getProductsCartTotal();

	return formatPrice($totals['vlprice']);

}



function post($key)
{
	return str_replace("'", "", $_POST[$key]);
}
function get($key)
{
	return str_replace("'", "", $_GET[$key]);
}

 ?>