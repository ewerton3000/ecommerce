<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;
use\Hcode\Mailer;



class User extends Model{
	//Criando uma constante com nome SESSION para fazer contanto com a classe User
  
const SESSION ="User";
  const SECRET = "HcodePhp7_Secret";
  const SECRET_IV ="HcodePhp7_Secret";
  const ERROR = "UserError";//Constante para armazenar o erro
  const ERROR_REGISTER ="UserErrorRegister";//Constante para armazenar o registro de erro 
  const SUCCESS = "UserSucess";

//Chave constante para o encrypt(para criptografar) na função getforgot
  


//criando um método para identificar o usuario que está usando o carrinho
public static function getFromSession(){
	$user = new User();
	//Se o id do usuario for maior que 0 é valido
	if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"]> 0) {
		$user->setData($_SESSION[User::SESSION]);
	}
	return $user;
}

//Criando um método para checar se a sessão está ativa ou não
//Usando um parâmetro $inadmin para verificar se é um administrador ou não
public static function checkLogin($inadmin = true){
	//Se a sessão do usuario está definida ok.Se não estiver definida estará vazia.Se a sessão está definida mas não é maior que 0
	if (
	     !isset($_SESSION[User::SESSION])
	     ||
	     !$_SESSION[User::SESSION]
	     ||
	     !(int)$_SESSION[User::SESSION]["iduser"]> 0
	      ) {
		//Não está logado então retorna false
		return false;
	}
	else{
        
        //Se o usuario for ADM na rota da ADM ok
        if ($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {
        	return true;
        }
        //Se ele não for administrador não acessa areas administrativas
        elseif($inadmin === false){
         
         return true;
        }
        //Se nenhuma das duas condições acima baterem ele não está loga e retornará false
        else{
        	return false;
        }
	}
}

//Criando um método estático
	public static function login($login,$password)
	{
		$sql = new Sql();

		//Consultando o login no Sql
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN",array(":LOGIN"=>$login
		));
		//Condição Se não tiver login registrado
if(count($results) === 0)
{
	throw new \Exception("Usuario inexistente ou senha inválida!");
}
$data = $results[0];

//Condição caso a senha seja identico a true ok senão
if(password_verify($password, $data["despassword"])=== true){
	$user = new User();
	//Acertando nomes com acento com UTF8(utf8_encode)
	$data['desperson'] = utf8_encode($data['desperson']);
	//Usando uma função para passar o array inteiro
	$user->setData($data);
//Criando uma sessão 
    $_SESSION[User::SESSION]= $user->getValues();
	return $user;
	}
	else{
		throw new \Exception("Usuario inexistente ou senha inválida!");
	}
}
//criando uma função publica para validar a sessão
public static function verifyLogin($inadmin = true){

   //Se a sessão foi definida ok senão volta pro login
	//Usando o método checklogin para checar se o login é ADM ou não
	if(!User::checkLogin($inadmin))
	{
		if($inadmin){
		header("location:/admin/login");
		exit;
}
	
	else{
		header("location: /login");
	}
	exit;
}
}

//Criando uma função estática para fazer o logout da sessão
public static function logout(){

//Na hora que clicar em sair ele dará a sessão como valor nulo(0)
	$_SESSION[User::SESSION]= NULL;
}
//criando uma função para listar os dados do banco de dados 
public static function listAll(){

	$sql = new Sql();

	return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
}
//Criando uma função para Fazer o INSERT dentro do banco
public function save(){
$sql = new Sql();
/*pdesperson VARCHAR(64), 
pdeslogin VARCHAR(64), 
pdespassword VARCHAR(256), 
pdesemail VARCHAR(128), 
pnrphone BIGINT, 
pinadmin TINYINT*/
//Chamando a procedure da tabela ecommerce
$results = $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",array(
	//
  ":desperson"=>$this->getdesperson(),
 ":deslogin"=>$this->getdeslogin(),
":despassword"=>User::getPasswordHash($this->getdespassword()),//usando o método para criptografar a senha e salva-la no SQL
":desemail"=>$this->getdesemail(),
":nrphone"=>$this->getnrphone(),
":inadmin"=>$this->getinadmin()
));

$this->setData($results[0]);
}
//criando uma função para puxar dados do SQL
public function get($iduser){

	$sql = new Sql();
	//Usando o INNER JOIN para juntar os dados de duas tabelas
	$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(
		":iduser"=>$iduser
	));
	$data = $results[0];

	$data['desperson'] = utf8_encode($data['desperson']);

	$this->setData($data);
}
public function update(){
$sql = new Sql();


//Chamando a procedure da tabela ecommerce
$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",array(
":iduser"=>$this->getiduser(),
":desperson"=>utf8_decode($this->getdesperson()),
":deslogin"=>$this->getdeslogin(),
":despassword"=>User::getPasswordHash($this->getdespassword()),
":desemail"=>$this->getdesemail(),
":nrphone"=>$this->getnrphone(),
":inadmin"=>$this->getinadmin()
));
$this->setData($results[0]);
}
//Criando a função delete
public function delete(){
	$sql = new Sql();
//Chamando a procedure sp_users_delete do SQL
	$sql->query("CALL sp_users_delete(:iduser)",array(":iduser"=>$this->getiduser()
));
}
//Criando uma função para o e-mail


public function getForgot($email,$inadmin = true){
$sql = new Sql();

$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;",array(
":email"=>$email));
if (count($results) === 0 ) { 
	throw new \Exception("Não foi possível recuperar a sua senha");
	
}
else{
		$data = $results[0];
		//Usando a procedure sp_userspasswordsrecoveries_create pelo SQL
	$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",array(
		":iduser"=>$data["iduser"],
		":desip"=>$_SERVER["REMOTE_ADDR"]
	));



	//Validar se encontrou o email no SQL
	if (count($results2[0]) === 0) 
	{
	throw new \Exception("Não foi possível recuperar a senha!" );
	
	}
	//Se conseguiu encontrar o email no SQL
	else{
		$dataRecovery = $results2[0];
     //Criptografando a senha pela id do email escolhido
		$code = openssl_encrypt($dataRecovery['idrecovery'],'aes-256-cbc',pack("a16",User::SECRET),0,pack("a16",User::SECRET_IV));
		$code = base64_encode($code);
		//Enviando o link do código por email(via get =?code=$code)
		//Se for admin troca a senha do admin se for cliente troca a senha do cliente
		if($inadmin === true){
		$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
}
else{
	$link ="http://www.hcodecommerce.com.br/forgot/reset?code=$code";
}
    //abaixo a ordem desemail,desperson,assunto,nome da página html		
		$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir Senhar da Hcode Store","forgot",array(
			"name"=>$data["desperson"],
			"link"=>$link
		));
		//Executando o send para enviar o email
		$mailer->send();
		//Caso os dados do usuarios forem recuperados e precise ir a outro lugar
		return $link;
	   } 
    }
   }
//Criando um método estático para descriptografar a senha
   public static function validForgotDecrypt($code){
   	//Tirando a criptografia do cógido
   	$code = base64_decode($code);
   	$idrecovery = openssl_decrypt($code,'aes-256-cbc',pack("a16",User::SECRET),0,pack("a16",User::SECRET_IV));
   	$sql = new Sql();
   	$results = $sql->select("
   		SELECT * FROM tb_userspasswordsrecoveries a
inner join tb_users b USING(iduser)
inner join tb_persons c USING(idperson)
where
a.idrecovery = :idrecovery
and
a.dtrecovery is null
and
date_add(a.dtregister,interval 1 hour)>= now();" ,array(
":idrecovery" => $idrecovery
));                          
   	if (count($results) === 0){
   		throw new \Exception("Não foi possível recuperar sua senha");
   	}
   	else{
   		return $results[0];
   	}
   }
   public static function setFogotUsed($idrecovery){
   	$sql = new Sql();

   	$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery =:idrecovery",array(
   		":idrecovery"=>$idrecovery
   	));
   }
   public function setPassword($password){
   	$sql = new Sql();

   	$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser=:iduser",array(
   		":password"=>$password,
   		":iduser"=>$this->getiduser()
   	));
   }

   //criando um método para mostrar o erro de Usuário
   public static function setError($msg){
   
   	$_SESSION[User::ERROR]= $msg;

   }
   //Método para pegar o conteúdo do erro
   public static function getError(){

   	$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
   	User::clearError();
   	return $msg;
   }

   //Método para limpar o erro
   public static function clearError(){

   	$_SESSION[User::ERROR] = NULL;


   }

   //criando um método para mostrar o erro de Usuário
   public static function setSuccess($msg){
   
   	$_SESSION[User::SUCCESS]= $msg;

   }
   //Método para pegar o conteúdo do erro
   public static function getSuccess(){

   	$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
   	User::clearSUCCESS();
   	return $msg;
   }

   //Método para limpar o erro
   public static function clearSuccess(){

   	$_SESSION[User::SUCCESS] = NULL;


   }
  

//Método para fazer a criptografia da senha e usa-la antes de salvar no SQL
public static function getPasswordHash($password){
	return password_hash($password, PASSWORD_DEFAULT,[
		'cost'=>12
	]);
 }

 //Método para mostrar o erro de cadastro 
 public static function setErrorRegister($msg){

 	$_SESSION[User::ERROR_REGISTER] = $msg;
 }


 //
 public static function getErrorRegister(){

 	//Se o erro já está definido e verifica se o erro não é vazio  e se ele existir retorna vazia
 	$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
 	User::clearErrorRegister();
 	return $msg;
 } 

 //Método para limpar o erro de registro
 public static function clearErrorRegister(){

 	$_SESSION[User::ERROR_REGISTER] = NULL;

 }

 //Método para checar se existi dois logins iguais registrados no SQL
 public static function checkLoginExist($login){

$sql = new Sql();

//Selecionando toda a tabela tb_users onde deslogin é igual a deslogin(login)

$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin",[
   ":deslogin"=>$login
   ]);
//Se for maior que 0 ele mostrará e se não for retorna false
return (count($results) > 0);
 }

 //
 public function getOrders(){

 	 $sql = new Sql();

      $results = $sql->select("
      	SELECT * 
      	FROM tb_orders a 
      	INNER JOIN tb_ordersstatus b USING(idstatus)
      	INNER JOIN tb_carts c USING(idcart)
      	INNER JOIN tb_users d ON d.iduser = a.iduser
      	INNER JOIN tb_addresses e USING(idaddress)
      	INNER JOIN tb_persons f ON f.idperson = d.idperson
      	WHERE a.iduser = :iduser
      	",[
      		':iduser'=>$this->getiduser()
      	]);
      return $results;
 }
}
//OBS: sempre que vc direcionar uma página use ni final o exit; porque senão entra em ciclo infinito como o for sem limites


//No if na parte do bool ele verifica se o usuario é um admin ou não


?>