<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class OrderStatus extends Model
{
    const  EM_ABERTO = 1;
    Const AGUARDANDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;

    protected $fields = ['idstatus', 'desstatus', 'dtregister'];
    public static function listStatusOrders()
    {
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_ordersstatus");
    }

}