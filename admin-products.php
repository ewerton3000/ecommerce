<?php
use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Product;

//caminho da página produto
$app->get("/admin/products",function(){
User::verifyLogin();
$products = Product::listAll();
$page = new PageAdmin();
$page->setTpl("products",[
"products"=>$products
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