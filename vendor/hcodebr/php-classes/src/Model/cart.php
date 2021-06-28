<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;
use\Hcode\Mailer;
use\Hcode\Model\User;

//criando a classe cart(carrinho)
class Cart extends Model{
	//criando as constantes para o carrinho
	const SESSION ="Cart";
	const SESSION_ERROR ="CartError";


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
 	$this->getCalculateTotal();

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
$this->getCalculateTotal();


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


 //Criando um método para somar todos os itens do produto(preço,tamanho,peso,altura)
 public function getProductsTotals(){
 	$sql = new Sql();

 	$results = $sql->select("
 		SELECT SUM(vlprice) AS vlprice,SUM(vlwidth) AS vlwidth,SUM(vlheight) AS vlheight,SUM(vllength) AS vllength,SUM(vlweight) AS vlweight,COUNT(*) AS nrqtd
			FROM tb_products a 
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL",[
			         ":idcart"=>$this->getidcart()
     ]);
 	if (count($results)> 0) {
 		return $results[0];
 	}
 	else{
 		return[];
 	}
 }

 //Criando um método para selecionar o CEP digitado
 public function setFreight($nrzipcode){

 	$nrzipcode = str_replace('-', '', $nrzipcode);

 	$totals = $this->getProductsTotals();
    //se a altura for menor que 2 ela será igual a 2 por causa da regras de négocio do serviço(correio)
 	if($totals['vlheight']< 2) $totals['vlheight'] = 2;
    //se o comprimento for menor que 16 ela será igual a 16 por causa da regras de négocio do serviço(correio)
 	if($totals['vllength']< 16) $totals['vllength'] = 16;

 	if ($totals['nrqtd'] > 0) {
       //Passando a s informações numa query string
       $qs = http_build_query([
       			'nCdEmpresa' => '',
                'sDsSenha' => '',
                'nCdServico' => '40010',
                'sCepOrigem' => '09853120',
                'sCepDestino' => $nrzipcode,
                'nVlPeso' => $totals['vlweight'],
                'nCdFormato' => '1',
                'nVlComprimento' => $totals['vllength'],
                'nVlAltura' => $totals['vlheight'],
                'nVlLargura' => $totals['vlwidth'],
                'nVlDiametro' => '0',
                'sCdMaoPropria' => 'S',
                'nVlValorDeclarado' => $totals['vlprice'],
                'sCdAvisoRecebimento' => 'S',
       ]);

 	 	//executando uma consulta pela url abaixo para puxar os dados de endereço pelos correios
 	 	$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

        $result =$xml->Servicos->cServico;

        //Caso a mensagem for diferente de vazio
        if ($result->MsgErro != '') {
        	//Passando a mensagem de erro
        Cart::setMsgError($result->MsgErro);


        }
        
        else{
        	//Limpando a mensagem de erro
        	Cart::clearMsgError();
        }

        $this->setnrdays($result->PrazoEntrega);
        $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
        $this->setdeszipcode($nrzipcode);
 	    //Salvando as informações pro banco
 	    $this->save();
 	    //retornando para a tela os dados 
 	    return $result;
 	 	
 	 }
 	 else{

 	 } 
 }
 //Criando o método para converter os valores quando aparecerem na tela
 public static function formatValueToDecimal($value):float
 {
 	//tirando o ponto por ponto vazio
 	$value = str_replace('.','',$value);
 	//trocando o ponto vazio por vírgula
 	return str_replace(',', '.',$value);

 }
//Criando um método para 
 public static function setMsgError($msg){

$_SESSION[Cart::SESSION_ERROR]=$msg;
 }

 public static function getMsgError(){
 	//Se tiver definido retorna o carrinho senão não retorna nada na tela
 	$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR]: "";
 	Cart::clearMsgError();
 	return $msg;
 }
//Criando um método para Limpar o carrinho
 public static function clearMsgError(){
 	$_SESSION[Cart::SESSION_ERROR] = NULL;
 }
 //Criando um método para atualizar o valor do cep se tiver mais de um produto no carrinho
  public function updateFreight()
    {

        if ($this->getdeszipcode() !=''){

            $this->setFreight($this->getdeszipcode());

        }

    }
    //Criando um método para somar os valores do produtos
    public function getValues(){
     
     $this->getCalculateTotal();

     return parent::getValues();  }
 
//criando um método para pegar o valor total somado dos produtos
 public function getCalculateTotal(){
//Atualizando o valor do frete
 	$this->updateFreight();

$totals = $this->getProductsTotals();

$this->setvlsubtotal($totals["vlprice"]);
//Somando o valor total com valor do frete
$this->setvltotal($totals['vlprice'] + $this->getvlfreight());

 }


}

//Aqui neste arquivo vc controla os dados dos carrinhos do site 

?>