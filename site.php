<?php
use\Hcode\Page;
use\Hcode\Model\Product;
use\Hcode\Model\Category;
use\Hcode\Model\Cart;
use\Hcode\Model\Address;
use\Hcode\Model\User;

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

//Rota para o checkout(finalizar compra)
$app->get("/checkout",function(){
//Passando pra tela de login do usuario com false
User::verifyLogin(false);
$cart = Cart::getFromSession();
//variavel para o endereço
$address = new Address();
$page = new Page();
$page->setTpl("checkout",[
    'cart'=>$cart->getValues(),
    'address'=>$address->getValues()
      ]);
});

//Rota para entrar como usuário cliente
$app->get("/login",function(){
$page = new Page();
//Redirecionando para fazer login na página
$page->setTpl("login",[
    //Mostrando o erro do usuario na página
'error'=>User::getError(),
'errorRegister'=>User::getErrorRegister(),
//Condição se a sessão existir passa ela mesmo senão o nome,email e o telefone estiverem vazios passa um array vazio
'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']

]);
});

//Usando o post para o login e senha
$app->post("/login",function(){
    try{
User::login($_POST["login"],$_POST["password"]);

}
catch(Exception $e){
    //Puxando o erro da classe User e setando a mensagem do exception
    User::setError($e->getMessage());
}
header("Location: /checkout");
exit;
});
//Rota para Fazer logout de usuário
$app->get("/logout",function(){
User::logout();

header("Location: /login");
exit;
});

//Rota de post para registrar um usuário
$app->post("/register",function(){
//criando uma função registerValues para setar os campos
    $_SESSION['registerValues'] = $_POST;
   //Validação de dados.Se não tiver nome ou o campo estiver vazio
    if(!isset($_POST['name']) || $_POST['name'] == ''){
    User::setErrorRegister("Preencha o seu nome.");
    //redireciona pra tela de registro/login
    header("Location: /login");
    exit;
}
//Validação de dados.Se não tiver email ou o campo estiver vazio
    if(!isset($_POST['email']) || $_POST['email'] == ''){
    User::setErrorRegister("Preencha o seu e-mail.");
    //redireciona pra tela de registro/login
    header("Location: /login");
    exit;
}



//Validação de dados.Se não tiver senha ou o campo estiver vazio
    if(!isset($_POST['password']) || $_POST['password'] == ''){
    User::setErrorRegister("Preencha a senha.");
    //redireciona pra tela de registro/login
    header("Location: /login");
    exit;
}

//Se o login for igual a um login já registrado
if(user::checkLoginExist($_POST['email']) === true){
    User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
    header("Location: /login");
    exit;
}
    $user = new User();

//Selecionando os names do formulario HTML para inseri-lo no SQL 
    $user->setData([
        'inadmin'=>0,//0=false para não entrar ADMS pelo login comun
         'deslogin'=>$_POST['email'],
         'desperson'=>$_POST['name'],
        'desemail'=>$_POST['email'],
        'despassword'=>$_POST['password'],
        'nrphone'=>$_POST['phone']
    ]);
    $user->save();
    
    //Fazendo a autenticação com login e senha do site com SQL
    User::login($_POST['email'],$_POST['password']);

    //Caso login seja com sucesso redireciona
    header('location: /checkout');
    exit;


});

//Rota para a tela de esqueci a senha do usuario
    $app->get("/forgot",function(){

        $page = new Page();
     $page->setTpl("forgot");
    });

    //caminho de página para o formulario do email
    $app->post("/forgot", function(){
//Usando um método para o post do email

        $user = User::getForgot($_POST["email"], false);
        header("Location:/forgot/sent");
        exit;
    });

//Caminho da pagina de confirmação de envio de email do usuário
    $app->get("/forgot/sent",function(){
        $page = new Page();
     $page->setTpl("forgot-sent");
    });
//caminho de página para resetar a senha e altera-la
    $app->get("/forgot/reset",function(){
        //Confirmando com a chave secreta da Classe User
        $user = User::validForgotDecrypt($_GET["code"]);
        $page = new Page();
     $page->setTpl("forgot-reset",array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]

     ));

    });
    //Criando a rota para o post da senha
   $app->post("/forgot/reset",function(){
     //Verificando o código de novo para evitar brechas no sistema
    $forgot = User::validForgotDecrypt($_POST["code"]);
            //Passando o nome do usuário de novo para não dar Exception
    User::setFogotUsed($forgot["idrecovery"]);
    //Instanciando a classe User
      $user = new User();

      $user->get((int)$forgot["iduser"]);
      //usando o hash password para passar criptografado no SQL
      //Isso é para evitar de ter a senha visivel no SQL
      $password = password_hash($_POST["password"], PASSWORD_DEFAULT,[
        "cost"=>12 //Aqui é a memoria de custo da senha o padrão é 12
        //Se tiver 13 ou mais o servidor pode não aguentar a carga por isso teste com multiplas alterações de senha
      ]);
      $user->setPassword($password);
      
      $page = new Page();
     $page->setTpl("forgot-reset-success");
});
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  

//().'?page=':Esta interrogação(?) é feita para manda r as variaveis de query string 

//Repare que na rota de remover todos os produtos  o true é usado para ativar a query da variavel $all que está como false no método removeProduct no arquivo cart.PHP

//No caminho do checkout repare que o User::verifyLogin tem um false dentro do parenteses é pra quando uma pessoa logar dentro do site ela nao seja identificiada como administrador que no caso ficaria sem nada dentro do parentese
?>