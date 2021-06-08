<?php 
//Este Arquivo ele direciona a rotas de todas a s páginas do site

//iniciando a sessão
session_start();
//Usando o autoload.php e a classe Page 
require_once("vendor/autoload.php");
//Slin e o Hcode são namespaces 

use\Slim\Slim;
use\Hcode\Page;
use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Category;
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

$app->get("/admin/users",function(){
	User::verifyLogin();
	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users",array(
		"users"=>$users));
});
$app->get('/admin/users/create',function(){
	//Verificando se o login está ativo
   User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create");
//run():É uma função para rodar tudo que está ligado a variavel $app(que tem as classes html e tals)
});
//Rota para deletar um usuario do banco de dados
$app->get("/admin/users/:iduser/delete",function($iduser){
		User::verifyLogin();
 
 //Carregar o usuario escolhido(pra tbm se ele existe no banco)
		$user = new User();

	//Passando a id do usuario
	$user->get((int)$iduser);
	//Deletando o usuario com a função
	$user->delete();
	header("Location:/admin/users");
	//finalizando a ação!
    exit;
	
	});
	$app->get("/admin/users/:iduser",function($iduser){
	//Verificando se o login está ativo
   User::verifyLogin();
   $user = new User();
   $user->get((int)$iduser);
	$page = new PageAdmin();
	$page->setTpl("users-update",array(
		"user"=>$user->getvalues()
	));

   });
	
	//Rota para criar um usuario
	$app->post("/admin/users/create" ,function(){
		User::verifyLogin();
		$user = new User();

		$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
		$user->setData($_POST);
		$user->save();
		
		header("Location:/admin/users");
		exit;
	});

	//Rota para salvar as informações do usuario no banco de dados como insert into e update
	$app->post("/admin/users/:iduser" ,function($iduser){
		User::verifyLogin();
		$user = new User();
		$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
		$user->get((int)$iduser);
		$user->setData($_POST);
		$user->update();
		header("Location:/admin/users");
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
   //caminho para a página categories
   $app->get("/admin/categories",function(){
   	//Verificar se o usuário esta logado com User::verifyLogin();
   	User::verifyLogin();
//Usando a classe Category e o método listAll()
$categories = Category::listAll();
$page = new PageAdmin();
 //selecionando o template categories
	$page->setTpl("categories",[
//usando a variavel que está no template categories
		"categories"=>$categories]);
});
	
//Criando a rota categories create
   $app->get("/admin/categories/create",function(){
   	$page = new PageAdmin();

   	$page->setTpl("categories-create");
   });

   //Criando o método post na página categories create
    $app->post("/admin/categories/create",function(){
    	User::verifyLogin();
   	$category = new Category();
   	//Setando o método post
   	$category->setData($_POST);
   	//Salvando os dados com o método(ou função)Save
   	$category->save();

   	header('Location:/admin/categories');
   	exit;
   });
    //Criando o caminho para deletar uma ou mais categorias
    //OBS:o :idcategory no meio do link abaixo ele seta a posição do id dentro do SQL da categoria selecionada e deleta-la
$app->get("/admin/categories/:idcategory/delete",function($idcategory){
	User::verifyLogin();
	$category = new Category();

	$category->get((int)$idcategory);
//Usando o método delete 
	$category->delete();
//redirecionando para a tela principal da pagina categorias
	header('Location:/admin/categories');
   	exit;
});

//Caminho para editar as categorias
$app->get("/admin/categories/:idcategory",function($idcategory){
	User::verifyLogin();
      $category = new Category;

      $category->get((int)$idcategory);

      $page = new PageAdmin();

   	$page->setTpl("categories-update",[
   		"category"=>$category->getValues()
   	]);
   });
//Caminho para a pagina de formulário  para atualizar a categoria 
$app->post("/admin/categories/:idcategory",function($idcategory){
	User::verifyLogin();
      $category = new Category;
      //Puxando a id da categoria selecionada
      $category->get((int)$idcategory);

     //selecionando a informação do SQL
      $category->setData($_POST);
      //Salvando os dados no SQL
      $category->save();
      //redirecionando para a tela principal da pagina categorias
	header('Location:/admin/categories');
   	exit;
});	
//Criando uma rota para a categoria
$app->get("/categories/:idcategory",function($idcategory){
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

//Colocando o caminho da página category
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>[];//transformando products em array
	]);
});
//run():É uma função para rodar tudo que está ligado a variavel $app(que tem as classes html e tals)
$app->run();


//O delete tem que estar acima de qualquer ação do usuario(no caso do $app mais acima) porque o slim framework pode válidado se ele estiver na ultima parte do código e se isso não for feito ele pode tanto inserir quanto deletar na mesma opção

// na linha $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; 
//se o valor post foi definido o valor dele é 1 se não foi definido o valor dele é 0
 ?>