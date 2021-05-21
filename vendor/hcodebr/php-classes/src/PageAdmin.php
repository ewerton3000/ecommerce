<?php
//usando o namespace Hcode
namespace Hcode;
//Criando uma classe chamada AdminPage que será classe filha de Page
class PageAdmin extends Page{
	//Criando uma função publicca construtora para encaminhar o layout da pagina admin
	public function __construct($opts = array(),$tpl_dir ="/views/admin/"){

		//Usando parents para puxar o método construtor
		parent::__construct($opts,$tpl_dir);
	}

}
?>