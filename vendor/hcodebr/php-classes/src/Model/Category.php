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
//puxando o método updatefile()
	Category::updateFile();
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
	//puxando o método updatefile()
	Category::updateFile();
}
public static function updateFile(){

$categories = Category::listAll();



	$categories = Category::listAll();
		//Usando um array para puxar os nomes da tabela categories
	$html = []; //<= Transformando a variavel $html em array
	foreach ($categories as $row) {
	array_push($html,'<li><a href="/categories/'.$row['idcategory'].'
		">'.$row['descategory'].'</a></li>');
}

//Salvando o arquivo,direcionando para a página categories-menu e usando implode pra tranformar o array em string
file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html",implode('',$html));
}



}
//OBS:O explode()é usado pra transforma uma string em array e  o implode()é ao contrario!
?>