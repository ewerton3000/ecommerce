<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;
use\Hcode\Mailer;
use\Hcode\Model\User;

//criando a classe cart(carrinho)
class Cart extends Model{
	//criando uma constante para o carrinho
	const SESSION ="Cart";


	//criando um método para inserir um carrinho novo ou criar carrinho vazio ou saiu da sessão e verifica pelo sessionid para puxar o carrinho certo
	public static function getFromSession(){
        
		$cart = new Cart();
        //Condição:Caso o carrinho esteja na sessão ok senão
        //Usando a constante Cart abaixo
if(isset($_SESSION[Cart::SESSION])&&(int)$_SESSION[Cart::SESSION]['idcart']> 0){

	$cart->get((int)$_SESSION[Cart::SESSION]["idcart"]);
}
else{
	//Aqui caso não tenha o carrinho passa pra próxima condição abaixo
	$cart->getFromSessionID();
     
     //Se o getFromSessionID carregar o carrinho com id maior que 0 ok senão vai criar um carrinho novo
	if (!(int)$cart->getidcart()>0) {
		$data=[
			'dessessionid'=>session_id()
		];
		//Se o check login for true ele irá criar o carrinho com o nome do usuario pelo iduser
		if (User::checkLogin(false)) {
			//Puxando o Método da classe User
		$user = User::getFromSession();

		$data["iduser"] = $user->getiduser();
		}
		
		//inserindo os dados dentro da varivavel $cart
		$cart->setData($data);
		//salvando no SQL
		$cart->save();
		//usando o método setToSession
		$cart->setToSession();
	}
}
return $cart;
	}


//criando um método para colocar o carrinho na sessão
	public function setToSession(){

		$_SESSION[Cart::SESSION]=  $this->getValues();

	}
	public function getFromSessionID(){
	$sql = new Sql();
    
    //Mostra o carrinho do usuario pelo dessessionid
	$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
		":dessessionid"=>session_id()
			]);
    //Se a variavel $results for maior q 0 mostrará o valor do array  na posição 0
	if(count($results)>0){
		$this->setData($results[0]);
	}
}
//criando um método para pegar o id do carrinho
	public function get(int $idcart){
		$sql = new Sql();
        
        //puxando no SQL o id do carrinho
		$results = $sql->select("SELECT *FROM tb_carts WHERE idcart = :idcart",[
			':idcart'=>$idcart
		]);
         
         //Se o id do carrinho é maior do que 0 mostra o carrinho
		if(count($results)>0){
		$this->setData($results[0]);
	}
	}


	//criando o método paar salvar as informações do carrinho
 public function save(){

$sql = new  Sql();

//Usando a procedure sp_carts_save para salvar os dados do carrinho
$results =$sql->select("CALL sp_carts_save(:idcart,:dessessionid,:iduser,:deszipcode,:vlfreight,:nrdays)",[
    ':idcart'=>$this->getidcart(),
    ':dessessionid'=>$this->getdessessionid(),
    'iduser'=>$this->getiduser(),
    ':deszipcode'=>$this->getdeszipcode(),
    ':vlfreight'=>$this->getvlfreight(),
    ':nrdays'=>$this->getnrdays()
    ]);
  
  $this->setData($results[0]);
 }

//Criando um método para adicionar o produto
//Instanciando a classe e usando um parametro com o nome Product
 public function addProduct(Product $product){
 	$sql =new Sql();

 	$sql->query("INSERT INTO tb_cartsproducts(idcart,idproduct) VALUES(:idcart,:idproduct)",[
 		':idcart'=>$this->getidcart(),
 	    ':idproduct'=>$product->getidproduct()
 	]);

 }

//Criando um método para remover o produto
 //Instanciando a classe Product e usando $all para remove todos os produtos do carrinho
 public function removeProduct(Product $product,$all = false){

$sql = new Sql();

//Removendo todos os produtos
if ($all) {
	$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL",[
		':idcart'=>$this->getidcart(),
	    ':idproduct'=>$product->getidproduct()
	]);
}
//Caso queira excluir um produto  
//OBS:a query é parecida mas o LIMIT 1 faz com que só um produto seja excluido
else{
	$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1",[
		':idcart'=>$this->getidcart(),
	    ':idproduct'=>$product->getidproduct()
	]);
}

 }

 //Criando um método para listar os produtos 
 public function getProducts(){

 	$sql = new Sql();

//Criando uma query para puxando a tabela tb_products  com INNER JOIN e retorna-lo na página!
 	$rows = $sql->select("
 		SELECT b.idproduct,b.desproduct,b.vlprice,b.vlwidth,b.vlheight,b.vllength,b.vlweight,b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
 		FROM tb_cartsproducts a 
 		INNER JOIN tb_products b ON a.idproduct = b.idproduct 
 		WHERE a.idcart = :idcart AND a.dtremoved IS NULL
 		GROUP BY b.idproduct,b.desproduct,b.vlprice,b.vlwidth,b.vlheight,b.vllength,b.vlweight,b.desurl
 	        ORDER BY b.desproduct;
 	        ",[
 	        	":idcart"=>$this->getidcart()
 	        ]);
 	return Product::checkList($rows);
 }

}

//Aqui neste arquivo vc controla os dados dos carrinhos do site 

?>