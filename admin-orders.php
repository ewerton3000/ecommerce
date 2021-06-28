<?php

use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Order;
use\Hcode\Model\OrderStatus;


//Rota para editar o status do pedido

$app->get("/admin/orders/:idorder/status" ,function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();


//
	$page->setTpl("order-status",[
		'order'=>$order->getValues(),//puxando o pedido
		'status'=>OrderStatus::listAll(),//puxando os status
		'msgSuccess'=>Order::getSuccess(),//Mensagem de sucesso
		'msgError'=>Order::getError()//Mensagem de erro
	]);
});

$app->post("/admin/orders/:idorder/status",function($idorder){

//Se o idstatus não estiver escolhido ou o número de id não for maior que 0
if(!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
	Order::setError("Informe o status atual.");//Mesagem de erro
	header("Location: /admin/orders/".$idorder."/status");
	exit;
}
	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);
	//Recebendo o post do idstatus
	$order->setidstatus((int)$_POST['idstatus']);
    
    //Salvando o pedido
	$order->save();
    
    //Mensagem de COnfirmação
	Order::setSuccess("Status atualizado com sucesso.");
	header("Location: /admin/orders/".$idorder."/status");
	exit;
});


//Rota para deletar um pedido
$app->get("/admin/orders/:idorder/delete",function($idorder){
 
 //Verificando se o ADM está logado
	User::verifyLogin();
	//Instanciando a classe Order
	$order = new Order();
	//Pegando a id do pedido(order)
	$order->get((int)$idorder);
	//Usando o método para deletar o pedido
	$order->delete();
	//Redirecionando para a página de ADM pedidos
	header("Location: /admin/orders");
	//finalizando a ação
	exit;
});



//Rota para os detalhes do pedido
$app->get("/admin/orders/:idorder",function($idorder){
	User::VerifyLogin();


	$order = new Order();

	$order->get((int)$idorder);

   
 
    $cart = $order->getCart();



	$page = new PageAdmin();

//
	$page->setTpl("order",[
		'order'=>$order->getValues(),//puxando o pedido
		'cart'=>$cart->getValues(),//puxando o carrinho
		'products'=>$cart->getProducts()//puxando os produtos dentro do carrinho
		
	]);

});

//Rota para a administração de pedidos do ADM
$app->get("/admin/orders",function(){

	User::verifyLogin();


$search = (isset($_GET['search'])) ? $_GET['search'] : "";

//Se for definido na url o page então o inteiro será page
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
 
 //Se a procurar for diferente de vazio   
if($search != ''){

$pagination = Order::getPageSearch($search , $page , 1);
}
//Senão
else{
$pagination = Order::getPage($page);
}
    //Puxando o método pela classe User
    //User::getPage($page,2):Aqui vc pode controlar quantos usuarios vc quer por página
    //Exemplo: uma pessoa por página User::getPage($page,1) duas pessoas User::getPage($page,2)
	
	$pages = [];

	for($x = 0;$x < $pagination['pages'];$x++){
        //Controlando as páginas com for
		array_push($pages,[
			'href'=>'/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

		
	}



	$page = new PageAdmin();
    
    //Puxando a lista de pedidos na página
	$page->setTpl("orders",[
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);


});




//OBS:Note que as rotas tem que estar bem ordenadas por cuasa de suas ações como o delete ele pode sobrescrever so´de estar em baixo de outra rota  e a rota de detalhes está emcima da´página principal dos pedidos  as vezes rotas maiores vem antes das outras rotas menores

?>