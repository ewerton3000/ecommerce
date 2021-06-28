<?php

namespace Hcode\Model;

use\Hcode\DB\Sql;
use\Hcode\Model;
use\Hcode\Model\Cart;

//Classe Order

class Order extends Model{

 const SUCCESS = "Order-Success";
 const ERROR = "Order-Error";
//Método para salvar os dados da ordem de pagamento
	public function save(){

     $sql = new Sql();

     $results = $sql->select("CALL sp_orders_save(:idorder,:idcart,:iduser,:idstatus,:idaddress,:vltotal)",[
         ":idorder"=>$this->getidorder(),
         ":idcart"=>$this->getidcart(),
         ":iduser"=>$this->getiduser(),
         ":idstatus"=>$this->getidstatus(),
         ":idaddress"=>$this->getidaddress(),
         ":vltotal"=>$this->getvltotal()
     ]);
     if(count($results)> 0){
     	$this->setData($results[0]);
     }

	}
//Método para puxar os detalhes do pedido
	public function get($idorder){

      $sql = new Sql();

      $results = $sql->select("
      	SELECT * 
      	FROM tb_orders a 
      	INNER JOIN tb_ordersstatus b USING(idstatus)
      	INNER JOIN tb_carts c USING(idcart)
      	INNER JOIN tb_users d ON d.iduser = a.iduser
      	INNER JOIN tb_addresses e USING(idaddress)
      	INNER JOIN tb_persons f ON f.idperson = d.idperson
      	WHERE a.idorder = :idorder
      	",[
      		':idorder'=>$idorder
      	]);
      if (count($results)> 0) {
      	$this->setData($results[0]);
        $this->setdesperson(utf8_encode($this->getdesperson()));
      }


	}

    //Método para Listar os pedidos
    public static function listAll(){

        $sql = new Sql();

//Retornando os dados do SQL na tela(página)
       return $sql->select("
            SELECT * 
        FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus)
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        ORDER BY a.dtregister DESC
        ");
    }

    //Método para deletar um pedido
    public function delete(){

        $sql = new Sql();

        $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder",[
            ':idorder'=>$this->getidorder()
        ]);
    }

    //Método para buscar o carrinho e instanciando a classe Cart
    public function getCart():Cart{
     //Instanciando a classe Cart

        $cart = new Cart();
        //Pegando o id do carrinho
        $cart->get((int)$this->getidcart());
        //Retornando os dados  do carrinho
        return $cart;
    }

    //criando um método para mostrar o erro de Usuário
   public static function setError($msg){
   
    $_SESSION[Order::ERROR]= $msg;

   }
   //Método para pegar o conteúdo do erro
   public static function getError(){

    $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : '';
    Order::clearError();
    return $msg;
   }

   //Método para limpar o erro
   public static function clearError(){

    $_SESSION[Order::ERROR] = NULL;


   }

   //criando um método para mostrar o erro de Usuário
   public static function setSuccess($msg){
   
    $_SESSION[Order::SUCCESS]= $msg;

   }
   //Método para pegar o conteúdo do erro
   public static function getSuccess(){

    $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';
    Order::clearSUCCESS();
    return $msg;
   }

   //Método para limpar o erro
   public static function clearSuccess(){

    $_SESSION[Order::SUCCESS] = NULL;


   }

   public static function getPage($page = 1,$itemsPerPage = 10){
//Usando a variavel Start para iniciar na página 0 na lista de usuários
//OBS:o $page começa na pagina 1 então ele terá que ser 0 pra começar do inicio ou seja ele começa como posição de array por issose faz $page -1 multiplicado com $itemsPerPage
 $start = ($page -1) * $itemsPerPage;
 $sql= new Sql();
 $results =$sql->select("
    SELECT SQL_CALC_FOUND_ROWS* 
    FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus)
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        ORDER BY a.dtregister DESC
        LIMIT $start , $itemsPerPage;
                  ");

 //Segunda consulta para contar linhas na tabela
$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
//Usando array no return
return [
    "data"=>$results,//Mostrando os dados do produto
    "total"=>(int)$resultTotal[0]["nrtotal"],//Mostrando os registros começando da posição 0 com nrtotal(rode o codigo do sql do $results no SQL e usando int para garantir que vai ser um número )
    "pages"=>ceil($resultTotal[0]["nrtotal"]/ $itemsPerPage)//Usando o cell pra criar outra página
];
}

//
 public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus)
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        WHERE a.idorder = :id OR f.desperson LIKE :search
        ORDER BY a.dtregister DESC
        LIMIT $start, $itemsPerPage;
        ", [
            ':search'=>'%'.$search.'%',
            ':id'=>$search
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];

    }
}
?>