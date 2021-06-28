<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;


//criando a classe Address(Endereço)
class Address extends Model{
       const SESSION_ERROR ="AddressError";

	public  static function getCEP($nrcep){

        //Deixando o cep com o traço com str_replace()
		$nrcep = str_replace("-","",$nrcep);

		//https://viacep.com.br/ws/01001000/json/

		$ch =curl_init();
        
        //Inserindo o cep na url com a variavel $nrcep para mostrar o endereço
		curl_setopt($ch ,CURLOPT_URL,"https://viacep.com.br/ws/$nrcep/json/");

		//Exigindo o tipo de autenticação ssl
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        

        $data = json_decode(curl_exec($ch),true);
        //Fechando a ação do curl
		curl_close($ch);

		return $data;


	}
	

	public  function loadFromCEP($nrcep){

     $data = Address::getCEP($nrcep);

      //Se o data recebeu o logradouro e se o data está vazio
     
     if(isset($data['logradouro']) && $data['logradouro']){
      //Setando os componentes do endereço
     
      $this->setdesaddress($data['logradouro']);
      $this->setdescomplement($data['complemento']);
      $this->setdesdistrict($data['bairro']);
      $this->setdescity($data['localidade']);
      $this->setdesstate($data['uf']);
      $this->setdescountry('Brasil');
      $this->setdeszipcode($nrcep);

     }

     
	}

	//Método para salvar os dados digitados(Post) no SQL
	public function save(){
     
     $sql = new Sql();

     //Chamando a procedure e salvando as linhas da tabela tb_Address
     $results = $sql->select("CALL sp_addresses_save(:idaddress,:idperson,:desaddress,:desnumber,:descomplement,:descity,:desstate,:descountry,:deszipcode,:desdistrict)",[
            ':idaddress'=>$this->getidaddress(),
            ':idperson'=>$this->getidperson(),
            ':desaddress'=>$this->getdesaddress(),
            ':desnumber'=>$this->getdesnumber(),
            ':descomplement'=>$this->getdescomplement(),
            ':descity'=>$this->getdescity(),
            ':desstate'=>$this->getdesstate(),
            ':descountry'=>$this->getdescountry(),
            ':deszipcode'=>$this->getdeszipcode(),
            ':desdistrict'=>$this->getdesdistrict()

            ]);
      //Se o $results for maior que zero os dados serão listados
     if(count($results) > 0){
     	$this->setData($results[0]);//Mostrando results na posição 0
     }
	}

//Criando um método para mostrar a linha do o erro
 public static function setMsgError($msg){

$_SESSION[Address::SESSION_ERROR] = $msg;
 }

 public static function getMsgError(){
       //Se tiver definido retorna o carrinho senão não retorna nada na tela
       $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR]: "";
       Address::clearMsgError();
       return $msg;
 }
//Criando um método para Limpar o carrinho
 public static function clearMsgError(){
       $_SESSION[Address::SESSION_ERROR] = NULL;
 }

}

?>