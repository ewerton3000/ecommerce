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
}

//OBS: sempre que vc direcionar uma página use ni final o exit; porque senão entra em ciclo infinito como o for sem limites


//No if na parte do bool ele verifica se o usuario é um admin ou não


?>