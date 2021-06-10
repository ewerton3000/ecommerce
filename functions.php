<?php
//Usando um método para converter valores dos produtos em decimais para ficarem certos na página
function formatPrice(float $vlprice){
//convertendo o valor com virgula(,) e ponto(. pra milhares e milhoes)                        
	return number_format($vlprice,2,",",  ".");
}
?>