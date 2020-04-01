<?php

use \Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;

$app->get("/admin/orders/:ID/status",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
    
    $order = new Order();

    $order->getOrder($id);

    $page = new PageAdmin();

    $page->setTpl("order-status", array(
        'order'=>$order->getValues(),
        'status'=>OrderStatus::listStatusOrders(),
        'msgSuccess'=>Order::getSuccess(),
        'msgError'=>Order::getError()
    ));

});

$app->post("/admin/orders/:ID/status",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
    
    if(!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
        Order::getError('Selecione o novo status');
        header("Location: /admin/orders/".$id."/status");
        exit;
    }

    $order = new Order();

    $order->getOrder($id);

    $order->setidstatus((int)$_POST['idstatus']);

    $order->createOrder();

    Order::setSuccess("Status Atualizado");
    header("Location: /admin/orders/".$id."/status");
    exit;

});



$app->get("/admin/orders/:ID/delete",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
    
    $order = new Order();

    $order->deleteOrder($id);

    header("Location: /admin/orders");

    exit;
});


$app->get("/admin/orders/:ID",function($id){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
    
    $order = new Order();

    $order->getOrder($id);

    $cart = $order->getCartByOrder();

    $page = new PageAdmin();

    $page->setTpl("order", array(
        'order'=>$order->getValues(),
        'cart'=>$cart->getValues(),
        'products'=>$cart->getProductsCart()
    )); 

});


$app->get("/admin/orders",function(){

    if (User::checkLogin() == false) {
        header("Location: /admin/login");
        exit;
    }
    
    $page = new PageAdmin();

    $page->setTpl("orders", array(
        'orders'=>Order::getOrders()
    )); 
});







