<?php
use\Hcode\PageAdmin;
use\Hcode\Model\User;

//Rota para alterar a senha dos usuarios pelo pelo menu do ADM
$app->get("/admin/users/:iduser/password" ,function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();
    
	$page->setTpl("users-password",[
	 "user"=>$user->getValues(),
	 "msgError"=>User::getError(),//Mensagem de erro para o usuário
	 "msgSuccess"=>User::getSuccess()// Mensagem de Feito com sucesso
	]);

});

//Rota para alterar a senha dos usuarios pelo pelo menu do ADM
$app->post("/admin/users/:iduser/password" ,function($iduser){

	User::verifyLogin();
     
     //Se o campo da senha nãoestiver definido ou estiver vazio
    if(!isset($_POST['despassword']) || $_POST['despassword'] ===''){
        
    	User::setError("Preencha a nova senha");
    	header("Location: /admin/users/$iduser/password");//Redirecionando
    	exit;
    }


if(!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] ===''){
        
    	User::setError("Preencha a confirmação da  nova senha");
    	header("Location: /admin/users/$iduser/password");//Redirecionando
    	exit;
    }

    if($_POST['despassword'] !== $_POST['despassword-confirm']){
        
    	User::setError("Confirme corretamente as senhas");
    	header("Location: /admin/users/$iduser/password");//Redirecionando
    	exit;
    }

	$user = new User();

	$user->get((int)$iduser);
    
    //Passando a senha , criptografando com getPassword hash e salvando SQL
    $user->setPassword(User::getPasswordHash($_POST['despassword']));
	
	User::setSuccess("Senha alterada com sucesso!");//Confirmando a senha com mensagem
    	header("Location: /admin/users/$iduser/password");//Redirecionando
    	exit;

});

$app->get("/admin/users",function(){
	User::verifyLogin();

//Se a procura
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

//Se for definido na url o page então o inteiro será page
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
 
 //Se a procurar for diferente de vazio   
if($search != ''){

$pagination = User::getPageSearch($search , $page , 1);
}
//Senão
else{
$pagination = User::getPage($page);
}
    //Puxando o método pela classe User
    //User::getPage($page,2):Aqui vc pode controlar quantos usuarios vc quer por página
    //Exemplo: uma pessoa por página User::getPage($page,1) duas pessoas User::getPage($page,2)
	
	$pages = [];

	for($x = 0;$x < $pagination['pages'];$x++){
        //Controlando as páginas com for
		array_push($pages,[
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

		
	}
	$page = new PageAdmin();
	$page->setTpl("users",array(
		"users"=>$pagination['data'],
	    "search"=>$search,
	    "pages"=>$pages
	));
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