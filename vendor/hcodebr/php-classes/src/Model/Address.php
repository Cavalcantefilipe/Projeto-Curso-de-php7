<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use Hcode\Model\User;

class Address extends Model
{
    const SESSION_ERROR = "AddressErro";
    protected $fields = [
       "idaddress","idperson","desaddress","descomplement","descity","desstate","descountry","deszipcode","desdistrict"
    ];

    public static function getCEP($nrcep){

        $nrcep = str_replace('-','',$nrcep);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, "http://viacep.com.br/ws/$nrcep/json/");

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER ,false);

        $data = json_decode(curl_exec($ch),true);
        curl_close($ch);

        return $data;
    }

    public function loadFromCEP($nrcep){
        $data = Address::getCEP($nrcep);

        if(isset($data['logradouro']) && $data['logradouro']){

            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($nrcep);
        }
    }

    public function createAddress(){

        $sql = new Sql();


        $result = $sql->select("CALL sp_addresses_save(:pidaddress, :pidperson,
        :pdesaddress,:pdescomplement,
        :pdescity,:pdesstate,
        :pdescountry,:pdeszipcode,
        :pdesdistrict)",array(
            ":pidaddress"=>     $this->getidaddress(),
            ":pidperson"=>      $this->getidperson(),
            ":pdesaddress"=>    $this->getdesaddress(),
            ":pdescomplement"=> $this->getdescomplement(),
            ":pdescity"=>       $this->getdescity(),
            ":pdesstate"=>      $this->getdesstate(),
            ":pdescountry"=>    $this->getdescountry(),
            ":pdeszipcode"=>    $this->getdeszipcode(),
            ":pdesdistrict"=>   $this->getdesdistrict()
        ));

        if(count($result)> 0){

            $this->setData($result[0]);
        }

    }

    public static function setMsgError($msg)
    {
        $_SESSION[Address::SESSION_ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";
        Address::clearMsgError();
        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Address::SESSION_ERROR] = null;
    }

}
