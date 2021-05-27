<?php
namespace dwApiLib\api;
use dwApiLib\DwApiLib;
use Symfony\Component\Yaml\Yaml;
use PHPMailer\PHPMailer\PHPMailer;


/**
 * Class Mail
 * @package dwApiLib\api
 */
class Mail{
  /**
   * @var array|bool|mixed|null
   */
  private $mail_parameters;

  /**
   * @var Request|null
   */
  private $request;

  /**
   * @var Response|null
   */
  private $response;

  /**
   * @var array|null
   */
  private $smtp = NULL;

  public function __construct($mail_parameters = [])
  {
    $this->smtp = DwApiLib::$settings->smtp;
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();
    $this->mail_parameters = $mail_parameters;
    Project::getInstance();
  }

  /**
   * getElement.
   * @param $element
   * @return bool|mixed
   * @throws DwapiException
   */
  private function getElement($element) {
    if ($this->mail_parameters[$element] != "") {
      return Template::renderTwigString($this->mail_parameters[$element], $this->response->getTwigVariables());
    }
    else {
      $template = Template::pickTemplate(
        $this->request->project,
        $element,
        $this->request->endpoint,
        $this->request->action,
        $this->request->method,
        $this->request->getParameters("query", "entity"));
      if ($template != "") {
        return Template::renderTwigFile($template, $this->response->getTwigVariables());
      }
    }

    return false;
  }

  /**
   * send.
   * @throws DwapiException
   * @throws \PHPMailer\PHPMailer\Exception
   */
  public function send() {
    $mail = new PHPMailer(true); //Argument true in constructor enables exceptions

    if (!is_null($this->smtp)) {
      $mail->IsSMTP();
			//$mail->Debugoutput = 'html';
			$mail->SMTPAuth = true;
			$mail->Host = $this->smtp["host"];
			$mail->Port = $this->smtp["port"];
			$mail->Username = $this->smtp["username"];
			$mail->Password = $this->smtp["password"];

    }

    //From email address and name
    $mail->From = $this->getElement("from_email");
    $mail->FromName = $this->getElement("from_name");

    $mail->addAddress( $this->getElement("to_email"), $this->getElement("to_name"));

    $reply_email = $this->getElement("reply_email");
    if ($reply_email) {
      $mail->addReplyTo($reply_email, $this->getElement("reply_name"));
    }

    $bcc_email = $this->getElement("bcc_email");
    if ($bcc_email) {
      $mail->addBcc($bcc_email);
    }


    //Send HTML or Plain Text email
    $mail->isHTML(true);

    $mail->Subject = $this->getElement("subject");
    $mail->Body = $this->getElement("body");
    //$mail->AltBody = "This is the plain text version of the email content";

    try {
      $mail->send();
    } catch (DwapiException $e) {
      throw new DwapiException($mail->ErrorInfo, DwapiException::DW_MAIL_ERROR);
    }
  }
}