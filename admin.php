<?php
use\Hcode\PageAdmin;
use\Hcode\Model\User;
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


//Rota para a tela de esqueci a senha
	$app->get("/admin/forgot",function(){

		$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);
     $page->setTpl("forgot");
	});

	//caminho de página para o formulario do email
	$app->post("/admin/forgot", function(){
//Usando um método para o post do email

		$user = User::getForgot($_POST["email"]);
		header("Location:/admin/forgot/sent");
		exit;
	});

//Caminho da pagina de confirmação de envio de email
	$app->get("/admin/forgot/sent",function(){
		$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);
     $page->setTpl("forgot-sent");
	});
//caminho de página para resetar a senha e altera-la
	$app->get("/admin/forgot/reset",function(){
		//Confirmando com a chave secreta da Classe User
		$user = User::validForgotDecrypt($_GET["code"]);
		$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);
     $page->setTpl("forgot-reset",array(
     	"name"=>$user["desperson"],
        "code"=>$_GET["code"]

     ));

	});
	//Criando a rota para o post da senha
   $app->post("/admin/forgot/reset",function(){
   	 //Verificando o código de novo para evitar brechas no sistema
   	$forgot = User::validForgotDecrypt($_POST["code"]);
        	//Passando o nome do usuário de novo para não dar Exception
   	User::setFogotUsed($forgot["idrecovery"]);
   	//Instanciando a classe User
      $user = new User();

      $user->get((int)$forgot["iduser"]);
      //usando o hash password para passar criptografado no SQL
      //Isso é para evitar de ter a senha visivel no SQL
      $password = password_hash($_POST["password"], PASSWORD_DEFAULT,[
      	"cost"=>12 //Aqui é a memoria de custo da senha o padrão é 12
      	//Se tiver 13 ou mais o servidor pode não aguentar a carga por isso teste com multiplas alterações de senha
      ]);
      $user->setPassword($password);
      
      $page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);
     $page->setTpl("forgot-reset-success");
});
?>