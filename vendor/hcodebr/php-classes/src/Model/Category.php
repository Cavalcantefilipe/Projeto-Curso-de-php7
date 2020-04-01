<?php

namespace Hcode\Model;

use ErrorException;
use Exception;
use \Hcode\Model;
use \Hcode\DB\Sql;
use Hcode\Mailer;

class Category extends Model
{
    protected $fields = [
        "idcategory", "descategory", "dtregister", "idproduct"
    ];

    public static function getCategories()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories ");
    }


    public  function createCategory()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save(:IDCATEGORY, :DESCATEGORY)", array(
            ":IDCATEGORY" => $this->getidcategory(),
            ":DESCATEGORY" => $this->getdescategory()
        ));

        $this->setData($results[0]);

        Category::updateFile();
    }

    public  function deleteCategory($id)
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories where idcategory = :ID", array(
            ":ID" => $id
        ));

        Category::updateFile();
    }

    public  function getCategory($id)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories where idcategory = :ID", array(
            ":ID" => $id
        ));

        Category::setData($results[0]);
    }

    public  function updateCategory($id)
    {
        $sql = new Sql();

        $sql->select("UPDATE tb_categories set descategory = :DESCAT where idcategory = :ID", array(
            ":DESCAT" => $this->getdescategory(),
            ":ID" => $id
        ));

        Category::updateFile();
    }

    public function getProductsbyCategory($related = true)
    {

        $sql = new Sql();

        if ($related === true) {

            return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
            SELECT a.idproduct FROM tb_products a 
            JOIN tb_productscategories b on a.idproduct = b.idproduct
            WHERE b.idcategory = :ID);", array(
                ":ID" => $this->getidcategory()
            ));
        } else {
            return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
                SELECT a.idproduct FROM tb_products a 
            JOIN tb_productscategories b on a.idproduct = b.idproduct
            WHERE b.idcategory = :ID);", array(
                ":ID" => $this->getidcategory()
            ));
        }
    }

    public function addProductToCategory(Products $product)
    {
        $sql = new Sql();

        $sql->query("INSERT INTO tb_productscategories
         values(:idcategory, :idproduct)", array(
            ":idcategory" => $this->getidcategory(),
            ":idproduct" => $product->getidproduct()
        ));
    }

    public function removeProductToCategory(Products $product)
    {
        $sql = new Sql();

        $sql->query("DELETE FROM  tb_productscategories
         Where idcategory = :idcategory and idproduct = :idproduct", array(
            ":idcategory" => $this->getidcategory(),
            ":idproduct" => $product->getidproduct()
        ));
    }

    public function getProductsPage($page = 1, $itemsPerPage = 3)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new sql();

        $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
        FROM tb_products a
        JOIN tb_productscategories b on a.idproduct = b.idproduct
        JOIN tb_categories c on c.idcategory = b.idcategory
        WHERE c.idcategory = :ID
        LIMIT $start,$itemsPerPage;
        ", array(
            ":ID" => $this->getidcategory()
        ));

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data' => Products::checkList($results),
            'total' => (int) $resultTotal[0]["nrtotal"],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }

    public static function updateFile()
    {

        $categories = Category::getCategories();

        $html = [];

        foreach ($categories as $category) {
            array_push($html, '<li><a href="/category/' . $category['idcategory'] . '">' . $category['descategory'] . '</a></li>');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }
}
