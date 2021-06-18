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
$page->setTpl("cart",[
"cart"=>$cart->getValues(),
"products"=>$cart->getProducts(),
"error"=>Cart::getMsgError()
]);
});

//Criando a rota para adicionar o produto
$app->get("/cart/:idproduct/add",function($idproduct){
 $product = new Product();
//Puxando a id do produto
 $product->get((int)$idproduct);
 //Instanciando a classe Cart e usando o método getFromsession para aprovar a sessão do carrinho
 $cart = Cart::getFromSession();
 //Se colocar mais de um ok senão vai ser só um produto msm(conectando com  a quantidade de produtos puxando pelo QTD do html do products details.html )
 $qtd = (isset($_GET["qtd"])) ?(int)$_GET["qtd"]:1;
//Usando o for como contador de produtos com o método 
 for ($i=1; $i < $qtd ; $i++) { 
 	$cart->addProduct($product);//Aqui ele adiciona o produto
 }
 //adicionando o produto com o método addProduct
 $cart->addProduct($product);
 //Redirecionando para a página do carrinho
 header("Location:/cart");

 exit;
});

//Criando a rota para remover UM produto
$app->get("/cart/:idproduct/minus",function($idproduct){
 $product = new Product();
//Puxando a id do produto
 $product->get((int)$idproduct);
 //Instanciando a classe Cart e usando o método getFromsession para aprovar a sessão do carrinho
 $cart = Cart::getFromSession();
 //Removendo o produto com o método removeProduct
 $cart->removeProduct($product);
 //Redirecionando para a página do carrinho
 header("Location:/cart");
 exit;
});


//Criando a rota para remover TODOS  OS PRODUTOS
$app->get("/cart/:idproduct/remove",function($idproduct){
 $product = new Product();
//Puxando a id do produto
 $product->get((int)$idproduct);
 //Instanciando a classe Cart e usando o método getFromsession para aprovar a sessão do carrinho
 $cart = Cart::getFromSession();
 //Removendo o produto com o método removeProduct e usando true para bater com  o $all do método que é false
 $cart->removeProduct($product,true);
 //Redirecionando para a página do carrinho
 header("Location:/cart");
 exit;
});

//Criando a rota para enviar o cep digitado no formulario via post
$app->post("/cart/freight",function(){

$cart = Cart::getFromSession();

$cart->setFreight($_POST["zipcode"]);

header("Location:/cart");

exit;


});
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  

//().'?page=':Esta interrogação(?) é feita para manda r as variaveis de query string 

//Repare que na rota de remover todos os produtos  o true é usado para ativar a query da variavel $all que está como false no método removeProduct no arquivo cart.PHP
?>