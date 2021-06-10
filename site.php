<?php
use\Hcode\Page;
use\Hcode\Model\Product;
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
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  
});
?>