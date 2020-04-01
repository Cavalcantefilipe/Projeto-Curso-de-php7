<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class Order extends Model
{
    const SESSION_ERROR = "OrderErro";
    const ERROR = "OrderError";
	const SUCCESS = "sucess";

    protected $fields = [
        'idaddress', 'idcart', 'idstatus', 'idorder',
        'iduser', 'vltotal', 'dtregister', 'desstatus',
        'dtregister', 'dessessionid', 'iduser', 'deszipcode',
        'vlfreight', 'nrdays', 'dtregister', 'iduser',
        'idperson', 'deslogin', 'despassword', 'inadmin',
        'dtregister', 'idperson', 'desaddress', 'descomplement',
        'descity', 'desstate', 'descountry', 'deszipcode',
        'desdistrict', 'dtregister', 'idperson', 'desperson',
        'desemail', 'nrphone', 'dtregister'
    ];


    public function createOrder()
    {

        $sql = new Sql();

        if(isset($this->getidcart()['idcart'])){
        $this->setidcart($this->getidcart()['idcart']);
        }


        $results = $sql->select("CALL sp_orders_save(:idorder,:idcart,:iduser,:idstatus,:idaddress,:vltotal)", array(
            ":idorder" => $this->getidorder(),
            ":idcart" => $this->getidcart(),
            ":iduser" => $this->getiduser(),
            ":idstatus" => $this->getidstatus(),
            ":idaddress" => $this->getidaddress(),
            ":vltotal" => $this->getvltotal()
        ));

        if (count($results) > 0) {

            $this->setData($results[0]);
        }
    }


    public function getOrder($idorder)
    {
        $sql = new Sql();

        $results = $sql->select(
            "SELECT * 
        FROM tb_orders a 
        JOIN tb_ordersstatus b USING(idstatus)
        JOIN tb_carts c USING(idcart)
        JOIN tb_users d ON d.iduser = a.iduser
        JOIN tb_addresses e USING(idaddress)
        JOIN tb_persons f ON f.idperson = d.idperson
        where a.idorder = :idorder",
            array(
                ':idorder' => $idorder
            )
        );
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }


    public static function getOrders()
    {
        $sql = new Sql();

        return  $sql->select(
            "SELECT * 
        FROM tb_orders a 
        JOIN tb_ordersstatus b USING(idstatus)
        JOIN tb_carts c USING(idcart)
        JOIN tb_users d ON d.iduser = a.iduser
        JOIN tb_addresses e USING(idaddress)
        JOIN tb_persons f ON f.idperson = d.idperson");
    }



    public function deleteOrder($id){

        $sql = new Sql();

        $sql->query("DELETE FROM tb_orders WHERE idorder = :ID",array(
            ':ID'=>$id
        ));
    }

    public function getCartByOrder(){

        $cart = new Cart();

        $cart->getCart($this->getidcart());

        return $cart;
    }

    public static function setSuccess($msg)
	{
		$_SESSION[Order::SUCCESS] = $msg;
	}


	public static function getSuccess()
	{
		$msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';

		Order::clearSuccess();

		return $msg;
	}

	public static function clearSuccess()
	{
		$_SESSION[Order::SUCCESS] = NULL;
    }
    
    public static function setError($msg)
	{
		$_SESSION[Order::ERROR] = $msg;
	}


	public static function getError()
	{
		$msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : '';

		Order::clearError();

		return $msg;
	}

	public static function clearError()
	{
		$_SESSION[Order::ERROR] = NULL;
	}
}
