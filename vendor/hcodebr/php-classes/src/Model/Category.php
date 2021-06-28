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

//Criando um método para trazer todos os produtos
//OBS:o parametro $related será usado como booleano true ou false caso o produto não estiver na categoria será  false
public function getProducts($related = true){

$sql = new Sql();
if($related === true){
	//Puxando o os produtos que estão na categoria escolhida
	//OBS:Usando o return para mostrar os produtos e categorias 
  return	$sql->select("SELECT * FROM tb_products WHERE idproduct IN(
				SELECT a.idproduct 
				 FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				WHERE b.idcategory =:idcategory
				);
				",[
					':idcategory'=>$this->getidcategory()
				]);
}
else{
	//Mostrando os produtos que estão sem categoria
	//OBS:Mostarndo os produtos que não estão em categorias
 return $sql->select("
 	SELECT * FROM tb_products WHERE idproduct NOT  IN(
	SELECT a.idproduct 
	 FROM tb_products a
	INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
	WHERE b.idcategory =:idcategory
);
 	",[
		':idcategory'=>$this->getidcategory()
	]);
}
}

//criando um método para controlar os produtos em uma página(paginação)
//OBS:$page=Qual é a página que nós temos e $itemsPerPage=é quantos items por página nós queremos!
public function getProductsPage($page = 1,$itemsPerPage = 3){
//Usando a variavel Start para iniciar na página 0(como array)
//OBS:o $page começa na pagina 1 então ele terá que ser 0 pra começar do inicio ou seja ele começa como posição de array por issose faz $page -1 multiplicado com $itemsPerPage
 $start = ($page -1) * $itemsPerPage;
 $sql= new Sql();
 $results =$sql->select("SELECT SQL_CALC_FOUND_ROWS* 
				FROM tb_products a 
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				INNER JOIN tb_categories c ON c.idcategory = b.idcategory
				WHERE c.idcategory = :idcategory
				LIMIT $start,$itemsPerPage;
				  ",[
				  	":idcategory"=>$this->getidcategory()
				  ]);

 //Segunda consulta para contar linhas na tabela
$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
//Usando array no return
return [
	"data"=>Product::checklist($results),//Mostrando os dados do produto
	"total"=>(int)$resultTotal[0]["nrtotal"],//Mostrando os registros começando da posição 0 com nrtotal(rode o codigo do sql do $results no SQL e usando int para garantir que vai ser um número )
	"pages"=>ceil($resultTotal[0]["nrtotal"]/ $itemsPerPage)//Usando o cell pra criar outra página
];
}

//Criando o método para adicionar o produto
//OBS:Nos parâmetros tem o Product que é a classe e o $product que é o parametro  isso significa que para passar pelo parâmetro tem que ser chamada a classe antes dele
public function addProduct(Product $product){

$sql = new Sql();

$sql->query("INSERT INTO tb_productscategories (idcategory,idproduct) VALUES(:idcategory,:idproduct)",[
	//Passando as informações para um array
	':idcategory'=>$this->getidcategory(),
	':idproduct'=>$product->getidproduct()
]);
}

//Criando um método para deletar um produto da categoria 
public function removeProduct(Product $product){

$sql = new Sql();

$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",[
	//Passando as informações para um array
	':idcategory'=>$this->getidcategory(),
	':idproduct'=>$product->getidproduct()
]);
}

public static function getPage($page = 1,$itemsPerPage = 10){
//Usando a variavel Start para iniciar na página 0 na lista de usuários
//OBS:o $page começa na pagina 1 então ele terá que ser 0 pra começar do inicio ou seja ele começa como posição de array por issose faz $page -1 multiplicado com $itemsPerPage
 $start = ($page -1) * $itemsPerPage;
 $sql= new Sql();
 $results =$sql->select("
 	SELECT SQL_CALC_FOUND_ROWS* 
 	FROM tb_categories 
	ORDER BY descategory
	LIMIT $start , $itemsPerPage;
				  ");

 //Segunda consulta para contar linhas na tabela
$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
//Usando array no return
return [
	"data"=>$results,//Mostrando os dados do produto
	"total"=>(int)$resultTotal[0]["nrtotal"],//Mostrando os registros começando da posição 0 com nrtotal(rode o codigo do sql do $results no SQL e usando int para garantir que vai ser um número )
	"pages"=>ceil($resultTotal[0]["nrtotal"]/ $itemsPerPage)//Usando o cell pra criar outra página
];
}

//
 public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_categories a 
            WHERE descategory LIKE :search 
            ORDER BY descategory
            LIMIT $start, $itemsPerPage;
        ", [
            ':search'=>'%'.$search.'%'
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];

    }

}
//OBS:O explode()é usado pra transforma uma string em array e  o implode()é ao contrario!

//ceil():É uma função od php que redireciona uma linha da tabela do SQL para uma segunda página por exemplo vc tem 10 produtos e uma página está cheia então está função cira uma segunda página colocado o 11° produto e assim por diante
?>