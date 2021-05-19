<?php

//Initiate configuration tpl

//Dizendo pro namespace aonde essa a classe Hcode está
//Using namespace and saiyng where Hcode Class is
namespace Hcode;

//Rain\tpl == new tpl
use Rain\tpl;
//Criando a classe Page  Creating class Pass 

class Page{
	//Create a private variable $tpl for don´t show all time in pages
	private $tpl;
	//Creating private variable with array empty
	private $options=[];
	private $defaults =[
		//variable will pass in template
		"data"=>[]
	];
	//criando um método  construtor.
	//Creating method construct
	public function __construct($opts = array()){
        $this->options = array_merge($this->defaults,$opts);

		$config = array(

	"tpl_dir"=>$_SERVER["DOCUMENT_ROOT"]."/views/",
"cache_dir"     =>$_SERVER["DOCUMENT_ROOT"]."/views-cache/",
"debug"         => false // set to false to improve the speed
			);

	Tpl::configure( $config );
	$this->tpl = new Tpl;


    $this->setData($this->options["data"]);
    	
    	//Using this for call heads for html
    	$this->tpl->draw("header");
    
	}
//Create a private function for foreach
//Criando uma fuñção privada para o foreach
	private function setData($data = array())
	{
		//From here $this->optins=["data"] is not necessary and be will use $data
		//Por aqui $this->optins=["data"] não é mais necessário e será substituida por $data
	]	foreach ($data as $key => $value) {
    	$this->tpl->assign($key,$value);
    	//Aqui vai pegar os valores de $data um título=$key e o valor=$value
}
    	//Aqui vai pegar os valores de $data um título=$key e o valor=$value
    	 //Aqui pegaremos os dados do $data com chave($key) e valor($value) e fazer o assign(assign($key,$value)) de um por um
}
	public function setTpl($name,$data = array(),$returnHTML = false){
		//Puxando a função Data
		$this->setData($data);
		 return $this->tpl->draw($name,$returnHTML);
   
}


	

	//Creating magic method destruct
	public function __destruct(){
		//While class die or exit of php memory will add fotter
		//Quando a classe morrer ou siar da memória do php ira adicionar o roda pé 
		$this->tpl->draw("footer");

	}
}

//arraymerge()É uma função que mescla dois arrays no caso acima os dois parametros vão se combinar e serem armazenados dentro da variavel $options}


//Create a magic method construct with setTpl for body content in the page
//Criando um método construtor mágico com nome setTpl para o conteúdo do corpo(body do html)da pagina
//Usando o Rain\tpl (é parecido com namespace só serve para o microframework) e quando chamamos new tpl é do namespace Rain
	
?>