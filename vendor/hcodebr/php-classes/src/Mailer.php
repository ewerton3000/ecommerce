<?php
namespace Hcode;

        
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Rain\Tpl;
        
class Mailer{
	    const USERNAME="coloque o seu email aqui";
	    //por algum motivo só aceitou com a senha
		const PASSWORD="<?password?>";
		const NAME_FROM ='Hcode Store';

		private $mail;
	//Nesta função colocaremos o endereço de email=$toAdress,o nome da pessoa=$toName,o assunto do email=$subject,o nome do aruivo de template que vai ser enviado na mensagem=$tplName  e as variáveis que vamos passar pelo template caso não passe nada manda um array
	public function __construct($toAdress,$toName,$subject,$tplName,$data = array()){
		$config = array(

	"tpl_dir"        =>$_SERVER["DOCUMENT_ROOT"]."/views/email/",
   "cache_dir"       =>$_SERVER["DOCUMENT_ROOT"]."/views-cache/",
   "debug"           =>false // set to false to improve the speed
			);

	Tpl::configure( $config );
	$tpl = new Tpl;

	//Passando os dados do template email 
	foreach ($data as $key => $value) {
		//Para criar as variavies dentro do template
		$tpl->assign($key,$value);
	}
	//Usando uma variavel para executa-la dentro do TPL e não mostra-la na tela com true
	$html = $tpl->draw($tplName,true);
	
		
//Create a new PHPMailer instance
$this->mail = new \PHPMailer();

$this->mail->CharSet ='UTF-8';
//Tell PHPMailer to use SMTP
$this->mail->isSMTP();

//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages


$this->mail->SMTPOptions = array( 'ssl'=>array(
'verify_peer'=>false,
'verify_peer_name'=> false,
'allow_self_signed'=>true)
);
$this->mail->SMTPDebug = 0;
//Set the hostname of the mail server

$this->mail->Host = 'smtp.gmail.com';
//Use `$this->mail->Host = gethostbyname('smtp.gmail.com');`
//if your network does not support SMTP over IPv6,
//though this may cause issues with TLS

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$this->mail->Port = 587;

//Set the encryption mechanism to use - STARTTLS or SMTPS
$this->mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
$this->mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$this->mail->Username = Mailer::USERNAME;

//Password to use for SMTP authentication
$this->mail->Password = Mailer::PASSWORD;

//Set who the message is to be sent from
$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

//Set an alternative reply-to address
//$this->mail->addReplyTo('replyto@example.com', 'First Last');

//Set who the message is to be sent to
$this->mail->addAddress($toAdress, $toName);

//Set the subject line
$this->mail->Subject = $subject;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$this->mail->msgHTML($html);

//Replace the plain text body with one created manually
$this->mail->AltBody = 'Ocorreu um erro no envio da mensagem';

//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors

}
//Função para enviar o email
public function send(){

	return $this->mail->send();
}
}
?>