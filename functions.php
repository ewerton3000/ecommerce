<?php
use\Hcode\Model\User;
use\Hcode\Model\Cart;


//Usando um método para converter valores dos produtos em decimais para ficarem certos na página
function formatPrice($vlprice){

    //se o valor não for maior que 0 ele será definido pra 0
    if(!$vlprice > 0) $vlprice = 0;
//convertendo o valor com virgula(,) e ponto(. pra milhares e milhoes)                        
	return number_format($vlprice,2,",",  ".");
}

//Método para mostrar a data nos  detalhes do pedido
function formatDate($date){
    return date('d/m/Y', strtotime($date));
    //strtotime:Função para inverter os dados  exemplo: d/m/Y para Y/m/d
}
//Método para checar o login
function checkLogin($inadmin = true){
	return User::checkLogin($inadmin);
}
//método para Pegar o Nome do usuário
function getUserName()
 {
 
     $user = User::getFromSession();
 
        
     return $user->getdesperson();
 
  }

//Método para Mostrar o valor total no ícone do carrinho
  function getCartNrQtd(){
//Pegando a id do carrinho
    $cart = Cart::getFromSession();
    
    //Jogando o preços total do pedido em $totals
    $totals =$cart->getProductsTotals();

    //Retornando o valor de $totals usando o nrqtd do TPL
    return $totals['nrqtd'];
  }

  //Método para Mostrar o valor total dos produtos sem a soma do frete
  function getCartVlSubTotal(){
//Pegando a id do carrinho
    $cart = Cart::getFromSession();
    
    //Jogando o preços total do pedido em $totals
    $totals =$cart->getProductsTotals();

    //Retornando o valor de $totals usando o vlprice do TPL
    return formatPrice($totals['vlprice']);
  }
?>