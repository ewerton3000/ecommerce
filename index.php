<?php 
//iniciando a sessão
session_start();
//Usando o autoload.php e a classe Page 
require_once("vendor/autoload.php");
//Slin e o Hcode são namespaces 
use\Slim\Slim;
use\Hcode\Page;
use\Hcode\PageAdmin;
use\Hcode\Model\User;
$app = new Slim();

//Aqui constroi o codigo html com head body e footer 
$app->config('debug',true);

$app->get('/', function() {
	//Instance Class Page Instanciando a classe Page
	//No codigo abaixo el irá passar o codigo do html 
$page = new Page();

//Chamando a página index.html
$page->setTpl("index");  
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  
});

//link do tpl para a paasta admin
$app->get('/admin', function() {
	//Instance Class Page Instanciando a classe Page
	//No codigo abaixo el irá passar o codigo do html 

//Criando um método estático para validar a sessão
	User::verifyLogin();

$page = new PageAdmin();

//Chamando a página index.html
$page->setTpl("index");  
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  
});

//Criando caminho do html pra pagina
$app->get('/admin/login',function(){
	$page = new PageAdmin([
		//Criando um array para não chamar o header e footer de usuarios
		"header"=>false,
	    "footer"=>false
	]);
	$page->setTpl("login");
});
$app->post('/admin/login',function(){
	//Validando o login do admin
	User::login($_POST["login"],$_POST["password"]);
	header("Location:/admin");
	exit;
});


$app->get('/admin/logout',function(){
	//Puxando a função logout
User::logout();
//Encaminhado para a página de login
header("location: /admin/login");
exit;
});

//run():É uma função para rodar tudo que está ligado a variavel $app(que tem as classes html e tals)
$app->run();

 ?>