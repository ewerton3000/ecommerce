<?php
use\Hcode\Model\User;

//Usando um método para converter valores dos produtos em decimais para ficarem certos na página
function formatPrice($vlprice){

    //se o valor não for maior que 0 ele será definido pra 0
    if(!$vlprice > 0) $vlprice = 0;
//convertendo o valor com virgula(,) e ponto(. pra milhares e milhoes)                        
	return number_format($vlprice,2,",",  ".");
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
?>