<?php
use\Hcode\Page;
use\Hcode\Model\Product;

use\Hcode\Model\Category;
//Caminho de home do site
$app->get('/', function() {
	//Instance Class Page Instanciando a classe Page
	//No codigo abaixo el irá passar o codigo do html 

	//listando os produtos que estão na home
	$products = Product::listAll();

$page = new Page();

//Chamando a página index.html
$page->setTpl("index",[
	//usando o array para mostrar os produtos
"products"=>Product::checkList($products)
]);  
});
//Criando uma rota para a categoria aparacere=
$app->get("/categories/:idcategory",function($idcategory){
	//verificar a senha

	$category = new Category();
   //carrega os dados da categoria com $idcategory
	$category->get((int)$idcategory);

	$page = new Page();

//Colocando o caminho da página category
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		//Chamando o método checklist da classe Product
		'products'=>Product::checkList($category->getProducts())//transformando products em array
	]);
});

//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  

?>