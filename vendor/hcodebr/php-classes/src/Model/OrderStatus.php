<?php

namespace Hcode\Model;

use\Hcode\DB\Sql;
use\Hcode\Model;

//Classe OrderStatus


class OrderStatus extends Model{

//Constantes para dizer o status do pedido
const EM_ABERTO = 1;
const AGUARDANDO_PAGAMENTO = 2;
const PAGO = 3;
const ENTREGUE = 4 ;

}
?>