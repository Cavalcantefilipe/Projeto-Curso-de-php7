<?php

namespace Hcode\Model;

use ErrorException;
use Exception;
use \Hcode\Model;
use \Hcode\DB\Sql;

class Products extends Model
{


    protected $fields = [
        "vltotal","nrqtd","idcartproduct", "idcart", "dessessionid", "iduser", "deszipcode", "vlfreight", "nrdays", "idproduct", "dtremoved", "dtregister","idproduct", "desproduct", "vlprice", "vlwidth", "vlheight", "vllength", "vlweight", "desurl", "dtregister", "idcategory", "desphoto"
    ];

    public static function getProducts()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_products");
    }

    public static function checklist($list)
    {
        foreach ($list as &$row) {
            $p = new Products();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }


    public  function createProduct()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:pidproduct, :pdesproduct ,:pvlprice,:pvlwidth,:pvlheight,:pvllength,:pvlweight,:pdesurl)", array(
            ":pidproduct" => $this->getidproduct(),
            ":pdesproduct" => $this->getdesproduct(),
            ":pvlprice" => $this->getvlprice(),
            ":pvlwidth" => $this->getvlwidth(),
            ":pvlheight" => $this->getvlheight(),
            ":pvllength" => $this->getvllength(),
            ":pvlweight" => $this->getvlweight(),
            ":pdesurl" => $this->getdesurl()
        ));

        $this->setData($results[0]);
    }

    public  function deleteProduct($id)
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_products where idproduct = :ID", array(
            ":ID" => $id
        ));
    }

    public  function getProduct($id)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products where idproduct = :ID", array(
            ":ID" => $id
        ));

        Products::setData($results[0]);
    }

    public  function updateProduct($id)
    {
        $sql = new Sql();

        $sql->query("UPDATE tb_products set 
        desproduct = :desproduct, 
        vlprice = :vlprice,
        vlwidth =:vlwidth,
        vlheight = :vlheight,
        vllength = :vllength, 
        vlweight = :vlweight, 
        desurl = :desurl 
        where idcategory = :ID", array(
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllength" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl(),
            ":ID" => $id
        ));
    }

    public function checkPhoto()
    {
        if (file_exists(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
                "res" . DIRECTORY_SEPARATOR .
                "site" . DIRECTORY_SEPARATOR .
                "img" . DIRECTORY_SEPARATOR .
                "products" . DIRECTORY_SEPARATOR .
                $this->getidproduct() . ".jpg"
        )) {
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {

            $url = "/res/site/img/products/product.jpg";
        }

        return $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file)
    {
        if (file_exists(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
                "res" . DIRECTORY_SEPARATOR .
                "site" . DIRECTORY_SEPARATOR .
                "img" . DIRECTORY_SEPARATOR .
                "products" . DIRECTORY_SEPARATOR .
                $this->getidproduct() . ".jpg"
        )) {
            header('Location: /admin/products');
            exit;
        } else {
            $extension = explode('.', $file["name"]);
            $extension = end($extension);

            switch ($extension) {
                case "jpg";
                case "jpeg";
                    $img = imagecreatefromjpeg($file["tmp_name"]);
                    break;

                case "gif";
                    $img = imagecreatefromgif($file["tmp_name"]);
                    break;

                case "png";
                    $img = imagecreatefrompng($file["tmp_name"]);
                    break;
            }

            $dest =  $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
                "res" . DIRECTORY_SEPARATOR .
                "site" . DIRECTORY_SEPARATOR .
                "img" . DIRECTORY_SEPARATOR .
                "products" . DIRECTORY_SEPARATOR .
                $this->getidproduct() . ".jpg";

            imagejpeg($img, $dest);

            imagedestroy($img);

            $this->checkPhoto();
        }
    }

    public function getFromURL($desurl)
    {

        $sql = new Sql();

        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl",
        [
            ":desurl"=>$desurl
        ]);

        $this->setData($rows[0]);
    }


    public function getCategoryByProduct()
    {
        $sql = new sql();

        return $sql->select("SELECT * FROM tb_categories a
        Join tb_productscategories b on a.idcategory = b.idcategory
        WHERE b.idproduct = :idproduct", array(
            ":idproduct" => $this->getidproduct()
        ));
    }
}
