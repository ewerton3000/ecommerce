<?php
use\Hcode\Page;
use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Category;
use\Hcode\Model\Product;

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
//Criando o caminho para os produtos e categorias
$app->get("/admin/categories/:idcategory/products",function($idcategory){
User::verifyLogin();

$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

  

	$page->setTpl("categories-products",[
		//Puxando os dados do sql e passa-los pro html(site)  
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
		//transformando products em array
		//OBS:procure estes nomes no categories-products.html
	]);
});


//Caminho para adicionar a categoria e o produto
$app->get("/admin/categories/:idcategory/products/:idproduct/add",function($idcategory,$idproduct){
User::verifyLogin();
//Instanciando a classe category para usar a variavel $category
$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();
	//pegabdo a id do produto
	$product->get((int)$idproduct);
	//Adicionando a categoria escolhida
	$category->addProduct($product);

	//Redirecionando para a página dos produtos e categorias
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
   

  

});



//Criando o caminho para remover o produto da categoria
$app->get("/admin/categories/:idcategory/products/:idproduct/remove",function($idcategory,$idproduct){
User::verifyLogin();
//Instanciando a classe category para usar a variavel $category
$category = new Category();

	$category->get((int)$idcategory);

	
  
	$product = new Product();
	//pegabdo a id do produto
	$product->get((int)$idproduct);
	//Adicionando a categoria escolhida
	$category->removeProduct($product);

	//Redirecionando para a página dos produtos e categorias
	header("Location: /admin/categories/".$idcategory."/products");
	exit;

	
});
?>