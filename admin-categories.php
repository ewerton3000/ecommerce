<?php
use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Category;

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
		'products'=>[]//transformando products em array
	]);
});
?>