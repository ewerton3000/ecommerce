<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;

class User extends Model{
	//Criando uma constante com nome SESSION para fazer contanto com a classe User
  const SESSION ="User";

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
}

//OBS: sempre que vc direcionar uma página use ni final o exit; porque senão entra em ciclo infinito como o for sem limites


//No if na parte do bool ele verifica se o usuario é um admin ou não


?>