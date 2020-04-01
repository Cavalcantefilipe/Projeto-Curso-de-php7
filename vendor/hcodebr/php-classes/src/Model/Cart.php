<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use Hcode\Model\User;

class Cart extends Model
{
    protected $fields = [
        "vlsubtotal", "MsgError", "vltotal", "nrqtd", "idcartproduct", "idcart", "dessessionid", "iduser", "deszipcode", "vlfreight", "nrdays", "idproduct", "dtremoved", "dtregister", "idproduct", "desproduct", "vlprice", "vlwidth", "vlheight", "vllength", "vlweight", "desurl", "dtregister", "idcategory", "desphoto"
    ];
    const SESSION = "Cart";
    const SESSION_ERROR = "CartErro";

    public static function getFromSession()
    {

        $cart = new Cart();

        if (isset($_SESSION[CART::SESSION]) && (int) $_SESSION[CART::SESSION]['idcart'] > 0) {

            $cart->getCart((int) $_SESSION[Cart::SESSION]['idcart']);
        } else {
            $cart->getFromSessionId();

            if (!(int) $cart->getidcart() > 0) {

                $data = [
                    "dessessionid" => session_id()
                ];

                if (User::checkLogin(false)) {
                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();
                }

                $cart->setData($data);

                $cart->createCart();

                $cart->setToSession();
            }
        }

        return $cart;
    }

    public function setToSession()
    {

        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function getFromSessionId()
    {

        $sql = new sql();

        $results = $sql->select(" SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", array(
            ":dessessionid" => session_id()
        ));

        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function getCart(int $idcart)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", array(
            ":idcart" => $idcart
        ));

        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }


    public function createCart()
    {

        $sql = new sql();

        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid,:iduser, :deszipcode,:vlfreight, :nrdays)", array(
            ":idcart" => $this->getidcart(),
            ":dessessionid" => $this->getdessessionid(),
            ":iduser" => $this->getiduser(),
            ":deszipcode" => $this->getdeszipcode(),
            ":vlfreight" => $this->getvlfreight(),
            ":nrdays" => $this->getnrdays()
        ));

        $this->setData($results[0]);
    }

    public function addProductToCart(Products $product)
    {
        $cart = $this->getProductsCartTotal();
        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart,idproduct)
        Values(:idcart, :idproduct)", array(
            ":idcart" => $this->getidcart(),
            ":idproduct" => $product->getidproduct()
        ));

        if ($cart['vlprice'] != NULL ) {
            $this->getCalculateTotal();
        }else{
            $this->setdeszipcode('');
            $this->setvlfreight('');
            $this->setnrdays('');
            $this->getCalculateTotal();

        }
    }

    public function removeProductToCart(Products $product, $all = false)
    {

        $sql = new Sql();
        if ($all) {

            $sql->query(
                "UPDATE tb_cartsproducts 
                SET dtremoved = now() 
                where idcart = :idcart 
                AND idproduct = :idproduct
                AND dtremoved is NULL",
                array(
                    ":idcart" => $this->getidcart(),
                    ":idproduct" => $product->getidproduct()
                )
            );
        } else {
            $sql->query(
                "UPDATE tb_cartsproducts 
                SET dtremoved = now() 
                where idcart = :idcart 
                AND idproduct = :idproduct
                AND dtremoved is NULL LIMIT 1",
                array(
                    ":idcart" => $this->getidcart(),
                    ":idproduct" => $product->getidproduct()
                )
            );
        }

        $cart = $this->getProductsCartTotal();
        if ($cart['vlprice'] > 0 && isset($cart['deszipcode'])) {
            $this->getCalculateTotal();
        }
        else{
            $this->setdeszipcode(0);
            $this->setvlfreight(0);
            $this->setnrdays(0);

        }
    }

    public function getProductsCart()
    {
        $sql = new Sql();

        return Products::checklist($sql->select(
            "SELECT b.idproduct, b.desproduct, b.vlprice,
            b.vlwidth, b.vlheight, 
            b.vllength, b.vlweight, b.desurl,
            COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
            FROM  tb_cartsproducts a 
            JOIN tb_products b ON a.idproduct = b.idproduct
            WHERE a.idcart = :idcart AND a.dtremoved iS NULL
            GROUP BY b.idproduct,b.desproduct, b.vlprice,
            b.vlwidth, b.vlheight, 
            b.vllength, b.vlweight, b.desurl
            order by b.desproduct;
            ",
            array(
                ":idcart" => $this->getidcart()
            )
        ));
    }

    public function getProductsCartTotal()
    {

        $sql = new sql();

        $results = $sql->select(
            "SELECT SUM(vlprice) as vlprice, SUM(vlwidth) AS vlwidth,
             SUM(vlheight) as vlheight,  SUM(vllength) as vllength,
             SUM(vlweight) as vlweight , COUNT(*) as nrqtd
             FROM tb_products a
             JOIN tb_cartsproducts b on a.idproduct = b.idproduct
             WHERE b.idcart = :idcart AND dtremoved is null;",
            array(
                ":idcart" => $this->getidcart()
            )
        );

        if (count($results) > 0) {
            return $results[0];
        } else {
            return array();
        }
    }


    public function setFreight($zipcode)
    {

        $nrzipcode = str_replace('-', '', $zipcode);

        $totals = $this->getProductsCartTotal();

        if ($totals['nrqtd'] > 0) {

            if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
            if ($totals['vllength'] < 16) $totals['vllength'] = 16;

            $qs = http_build_query([
                "nCdEmpresa" => '',
                "sDsSenha" => '',
                "nCdServico" => '04014',
                "sCepOrigem" => '08160540',
                "sCepDestino" => $nrzipcode,
                "nVlPeso" => $totals['vlweight'],
                "nCdFormato" => '1',
                "nVlComprimento" => $totals['vllength'],
                "nVlAltura" => $totals['vlheight'],
                "nVlLargura" => $totals['vlwidth'],
                "nVlDiametro" => '0',
                "sCdMaoPropria" => 'S',
                "nVlValorDeclarado" => $totals['vlprice'],
                "sCdAvisoRecebimento" => 'S',

            ]);

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs);

            $result = $xml->Servicos->cServico;

            if ($result->MsgErro != '') {

                Cart::setMsgError($result->MsgErro);
            } else {
                Cart::clearMsgError();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

            $this->createCart();
        } else {
        }
    }

    public static function formatValueToDecimal($value): float
    {


        $value = str_replace('.', '', $value);
        return  str_replace(',', '.', $value);
    }

    public static function setMsgError($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
        Cart::clearMsgError();
        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERROR] = null;
    }

    public function updateFreight()
    {

        if ($this->getdeszipcode() != '') {
            $this->setFreight($this->getdeszipcode());
        }
    }

    public function getValues()
    {
        $this->getCalculateTotal();

        return parent::getValues();
    }

    public function getCalculateTotal()
    {

        $this->updateFreight();

        if(!$this->getvlfreight())
        {
            $this->setvlfreight(0);
             $this->createCart();
        }

        $totals = $this->getProductsCartTotal();

        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] > 0  ? $totals['vlprice'] + $this->getvlfreight() : 0);
        $this->setdeszipcode($totals['vlprice'] == 0 ? null : $this->getdeszipcodet());
        $this->setvlfreight($totals['vlprice'] == 0 ? 0 : $this->getvlfreight());
        $this->setnrdays($totals['vlprice'] == 0 ? 0 : $this->getnrdays());
    }
}
