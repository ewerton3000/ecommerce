<?php
use\Hcode\Page;
use\Hcode\Model\Product;
use\Hcode\Model\Category;
use\Hcode\Model\Cart;
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
//Criando uma rota para a categoria aparacer e inserindo as páginas
$app->get("/categories/:idcategory",function($idcategory){
    //Condição:Se está passando por outra página senão vai pra página 1
    $page =(isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	$category = new Category();
   //carrega os dados da categoria com $idcategory
	$category->get((int)$idcategory);
    //usando $page como parametro
   $pagination = $category->getProductsPage($page);
    
    $pages = [];

    //usando o for
    //Se  for maior ou igual ao total de páginas($pagination['pages'])
    for ($i=1; $i <=$pagination['pages'] ; $i++) { 
    	//Usando um array com o caminho que o usuario vai quando clicar na página('link')
    	array_push($pages,[
    		'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
    		'page'=>$i
    	]);
    }
	$page = new Page();

//Colocando o caminho da página category
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		//Chamando o método checklist da classe Product
		'products'=>$pagination["data"],//transformando products em array
		'pages'=>$pages
	]);
});

//Criando a rota para detalhes do produto
$app->get("/products/:desurl",function($desurl){
$product = new Product();

$product->getFromURL($desurl);

$page = new Page();

$page->setTpl("product-detail",[
"product"=>$product->getValues(),
"categories"=>$product->getCategories()
]);

});


//criando a rota para a página carrinho
$app->get("/cart",function(){
$cart = Cart::getFromSession();	
$page = new Page();
//selecionando o TPL do carrinho
$page->setTpl("cart");
});
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  

//().'?page=':Esta interrogação(?) é feita para manda r as variaveis de query string 

?>