<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;
use\Hcode\Mailer;



class Category extends Model{
	//Listando a  tabela categories
public static function listAll(){

	$sql = new Sql();

	return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
}
//criando o método save
public function save(){
	$sql = new Sql();
$results = $sql->select("CALL sp_categories_save(:idcategory,:descategory)",array(
	//Salvando as informações nos campos da tabela categories
 ":idcategory"=>$this->getidcategory(),
 ":descategory"=>$this->getdescategory()
));
//Executando e mostrando os dados na posição zero!
$this->setData($results[0]);
}
//Criando o método get
public function get($idcategory){
	$sql = new Sql();
//Puxando a id que será deletada
	$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory =:idcategory ",[":idcategory"=>$idcategory
	]);
	$this->setData($results[0]);
}
//Criando o método delete
public function delete(){
	$sql = new Sql();
	//Procurand a id para ser deletada do SQL
	$sql->query("DELETE FROM tb_categories WHERE idcategory =:idcategory ",[
		":idcategory"=>$this->getidcategory()
	]);
}
}
?>