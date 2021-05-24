<?php
namespace Hcode;

class Model{
private $values = [];

public function __call($name,$args)
{
	$method = substr($name,0,3);
	$fieldName = substr($name,3,strlen($name));
	//var_dump($method,$fieldName);
	//exit para não recomeçar a ação de novo
	//exit;
	switch($method){
		case"get":
		return $this->values[$fieldName];
		break;

		case"set":
		$this->values[$fieldName]=$args=[0];
		break;
	}

}
public function setData($data = array()){


	foreach ($data as $key => $value) {
		//Criando um $this dinamico
		$this->{"set".$key}($value);
	}

}
//Criando uma função para pega o valor da sessão em User.php
public function getValues(){
	return $this->values;
}

}
//No foreach para criar um valor dinamico colocando com chaves(no caso o $this->{"set".$key}($value);)ele entra no lugar set para não virar um valor estático e quando este codigo é executado ele mostra todos os campos da tabela escolhida sem necessidade de criar um set enorme
?>