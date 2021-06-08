<?php 
//Este Arquivo ele direciona a rotas de todas a s páginas do site

//iniciando a sessão
session_start();
//Usando o autoload.php e a classe Page 
require_once("vendor/autoload.php");
//Slin e o Hcode são namespaces 

use\Slim\Slim;
$app = new Slim();


//Aqui constroi o codigo html com head body e footer 
$app->config('debug',true);

//Usando o require once para puxar os caminhos de tpl e métodos dos arquivos php
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");




	
//run():É uma função para rodar tudo que está ligado a variavel $app(que tem as classes html e tals)
$app->run();


//O delete tem que estar acima de qualquer ação do usuario(no caso do $app mais acima) porque o slim framework pode válidado se ele estiver na ultima parte do código e se isso não for feito ele pode tanto inserir quanto deletar na mesma opção

// na linha $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; 
//se o valor post foi definido o valor dele é 1 se não foi definido o valor dele é 0
 ?>