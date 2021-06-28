<?php
use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Product;

//caminho da página produto
$app->get("/admin/products",function(){
User::verifyLogin();

$search = (isset($_GET['search'])) ? $_GET['search'] : "";

//Se for definido na url o page então o inteiro será page
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
 
 //Se a procurar for diferente de vazio   
if($search != ''){

$pagination = Product::getPageSearch($search , $page , 1);
}
//Senão
else{
$pagination = Product::getPage($page);
}
    //Puxando o método pela classe User
    //User::getPage($page,2):Aqui vc pode controlar quantos usuarios vc quer por página
    //Exemplo: uma pessoa por página User::getPage($page,1) duas pessoas User::getPage($page,2)
	
	$pages = [];

	for($x = 0;$x < $pagination['pages'];$x++){
        //Controlando as páginas com for
		array_push($pages,[
			'href'=>'/admin/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

		
	}

$products = Product::listAll();
$page = new PageAdmin();
$page->setTpl("products",[
"products"=>$pagination['data'],
	    "search"=>$search,
	    "pages"=>$pages
	]);
});

//Caminho da página de cadastro
$app->get("/admin/products/create",function(){
	User::verifyLogin();
	$page = new PageAdmin();

	$page->setTpl("products-create");
});

//Caminho da página para criar o produto
$app->post("/admin/products/create",function(){
	User::verifyLogin();
	
	$product = new Product();

	$product->setData($_POST);

	$product->save();
     
    header("Location:/admin/products");
    var_dump($product);
    exit;
});

$app->get("/admin/products/:idproduct",function($idproduct){
	User::verifyLogin();

    $product = new Product();
    //Puxando o valor a idproduct como valor inteiro
    $product->get((int)$idproduct);

	$page = new PageAdmin();
    //puxando para a página produtos
	$page->setTpl("products-update",[
		"product"=>$product->getvalues()
	]);
});

//
$app->post("/admin/products/:idproduct",function($idproduct){
	User::verifyLogin();

    $product = new Product();
    //Puxando o valor a idproduct como valor inteiro
    $product->get((int)$idproduct);
    
	$product->setData($_POST);

	$product->save();
    

    //Puxando a foto escolhida em 
	$product->setPhoto($_FILES["file"]);

	header('location:/admin/products');
	exit;

});
//Criando o caminho para retirar o produto da categoria
$app->get("/admin/products/:idproduct/delete",function($idproduct){
	User::verifyLogin();

    $product = new Product();
    //Puxando o valor a idproduct como valor inteiro
    $product->get((int)$idproduct);
    
    $product->delete();

   header('location:/admin/products');
	exit;
});
?>