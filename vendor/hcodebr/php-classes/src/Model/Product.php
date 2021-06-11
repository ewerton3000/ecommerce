<?php
namespace Hcode\Model;
//Usando o namespace da classe Sql
use Hcode\DB\Sql;
use\Hcode\Model;
use\Hcode\Mailer;



class Product extends Model{
	//Listando a  tabela categories
public static function listAll(){

	$sql = new Sql();

	return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
}

Public static function checkList($list){
foreach ($list as &$row) {
	$p = new Product();
	$p->setData($row);
	//Puxando a imagem e outros dados do produto
	$row = $p->getValues();
}
return $list;
}
//criando o método save
public function save(){
	$sql = new Sql();
$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",array(
	//Salvando as informações nos campos da tabela categories
 ":idproduct"=>$this->getidproduct(),
 ":desproduct"=>$this->getdesproduct(),
 ":vlprice"=>$this->getvlprice(),
 ":vlwidth"=>$this->getvlwidth(),
 ":vlheight"=>$this->getvlheight(),
 ":vllength"=>$this->getvllength(),
 ":vlweight"=>$this->getvlweight(),
 ":desurl"=>$this->getdesurl()
));
//Executando e mostrando os dados na posição zero!
$this->setData($results[0]);

	
}
//Criando o método get
public function get($idproduct){
	$sql = new Sql();
//Puxando a id que será deletada
	$results = $sql->select("SELECT * FROM tb_products WHERE idproduct =:idproduct ",[":idproduct"=>$idproduct
	]);
	$this->setData($results[0]);
}
//Criando o método delete
public function delete(){
	$sql = new Sql();
	//Procurand a id para ser deletada do SQL
	$sql->query("DELETE FROM tb_products WHERE idproduct =:idproduct ",[
		":idproduct"=>$this->getidproduct()
	]);
	//puxando o método updatefile()
	
}
//criando um método para checar a foto
public function checkPhoto(){
//Usando o file_exists para checar a foto foi escolhida ou não e passando pelas pastas com DIRECTORY_SEPARATOR para chegar na imagem com id correspondente
	
if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg"))
{
	//retornando a foto escolhida pela id(getidproduct)
	$url ="/res/site/img/products/".$this->getidproduct().".jpg";
}
//Caso não volte mostrará a imagem cinza dizendo q não tem produto com está id
else{

	$url = "/res/site/img/product.jpg";
}
return $this->setdesphoto($url);
}
public function getValues(){
//puuxando o método pra ver se a foto foi escolhida
	$this->checkPhoto();
//Puxando o get values da classe pai
	$values = parent::getValues();
 

	return $values;
}

public function setPhoto($file){
//Pegando o nome do arquivo onde tem ponto('.') e fez um array dele
	$extension = explode('.',$file['name']);
	//Aqui el vai pegar as ultimas letras depois do ponto no arquivo
	$extension = end($extension);

//Usando o switch para converter as imagens com a biblioteca GD
	switch ($extension) {
		case "jpg":
		case "jpeg":                                         
		$image =imagecreatefromjpeg($file["tmp_name"]);
	   break;

	   case "gif":
	   $image = imagecreatefromgif($file["tmp_name"]);
	   break;

	   case"png":
	   $image=imagecreatefrompng($file["tmp_name"]);
	   break;
	}
	//Buscando a imagem para ser convertida
	$dist = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg";
		imagejpeg($image,$dist);

		imagedestroy($image);
      
     
}
//Método para puxar a url do produto
public function getFromURL($desurl){
	$sql = new Sql();
 //Consultando a tabela products e usando o LIMIT para mostrar apenas uma linha
	$rows=$sql->select("SELECT * FROM tb_products WHERE desurl =:desurl LIMIT 1",[
		"desurl"=>$desurl
	]);

	//puxando a linha como método setData na posição 0
	$this->setData($rows[0]);
}

//criando um método para mostrar a categoria nos detalhes do produto
public function getCategories(){
	$sql = new Sql();
//Usando o inner join para relacionar as tabelas e mostrara categoria do produto!
	return $sql->select("
		SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct
		",[
			":idproduct"=>$this->getidproduct()
		]);
}
}
?>