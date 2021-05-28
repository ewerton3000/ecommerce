<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;



class User extends Model{
	//Criando uma constante com nome SESSION para fazer contanto com a classe User
  
const SESSION ="User";
  const SECRET = "HcodePhp7_Secret";

//Chave constante para o encrypt(para criptografar) na função getforgot
  

//Criando um método estático
	public static function login($login,$password)
	{
		$sql = new Sql();

		//Consultando o login no Sql
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN",array(":LOGIN"=>$login
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
	if(
		!isset($_SESSION[User::SESSION])
		||// ||=ou
		!$_SESSION[User::SESSION]
		||
		!(int)$_SESSION[User::SESSION]["iduser"] > 0
		||
		(bool)$_SESSION[User::SESSION]["inadmin"] !==$inadmin
	){
		header("location:/admin/login");
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
":despassword"=>$this->getdespassword(),
":desemail"=>$this->getdesemail(),
":nrphone"=>$this->getnrphone(),
":inadmin"=>$this->getinadmin()
));
$this->setData($results[0]);
}
//criando uma função para
public function get($iduser){

	$sql = new Sql();
	//Usando o INNER JOIN para juntar os dados de duas tabelas
	$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(
		":iduser"=>$iduser
	));
	$this->setData($results[0]);
}
public function update(){
$sql = new Sql();

//Chamando a procedure da tabela ecommerce
$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",array(
	":iduser"=>$this->getiduser(),
 ":desperson"=>$this->getdesperson(),
 ":deslogin"=>$this->getdeslogin(),
":despassword"=>$this->getdespassword(),
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
		$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		$code = openssl_encrypt($dataRecovery['idrecovery'],'aes-256-cbc',User::SECRET,0,$iv);
		$result= base64_encode($iv.$code);
		//Enviando o link do código por email(via get =?code=$code)
		if($inadmin === true){
		$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
}
else{
	$link ="http://www.hcodecommerce.com.br/forgot/reset?code=$result";
}
    //abaixo a ordem desemail,desperson,assunto,nome da página html		
		$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir Senhar da Hcode Store","forgot",array(
			"name"=>$data["desperson"],
			"link"=>$link
		));
		//Executando o send para enviar o email
		$mailer->send();
		//Caso os dados do usuarios forem recuperados e precise ir a outro lugar
		return $data;
	   } 
    }
   }

 }
//OBS: sempre que vc direcionar uma página use ni final o exit; porque senão entra em ciclo infinito como o for sem limites


//No if na parte do bool ele verifica se o usuario é um admin ou não


?>