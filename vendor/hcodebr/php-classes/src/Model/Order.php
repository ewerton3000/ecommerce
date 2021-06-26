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
}
?>