<?php
use\Hcode\PageAdmin;
use\Hcode\Model\User;

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
?>