<?php
use\Hcode\Page;
//Caminho de home do site
$app->get('/', function() {
	//Instance Class Page Instanciando a classe Page
	//No codigo abaixo el irá passar o codigo do html 
$page = new Page();

//Chamando a página index.html
$page->setTpl("index");  
//Aqui nesta linha o php vai limpar a memória e ira colocar o rodapé(footer) do html na pagina  
});
?>