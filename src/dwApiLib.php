<?php
namespace dwApiLib;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Mail;
use dwApiLib\api\Project;
use dwApiLib\api\Request;
use dwApiLib\token\JwtToken;
use dwApiLib\endpoint\Endpoint;
use dwApiLib\query\QueryFactory;
use dwApiLib\endpoint\EndpointFactory;
use dwApiLib\api\Response;
use dwApiLib\token\TokenFactory;


/**
 * Class dwApi
 * @package dwApi
 */
class dwApiLib
{
  /**
   * @var \stdClass|null
   */
  static $settings = NULL;

  /**
   * @var Request
   */
  private $request;

  /**
   * @var Project
   */
  private $project;

  /**
   * @var JwtToken
   */
  private $current_token;

  /**
   * @var Endpoint;
   */
  private $endpoint;

  private $response = NULL;
  private $logged_in_user = NULL;

  private $allowed_paths = [];

  /**
   * @var dwApiLib|null
   */
  private static $instance = null;

  /**
   * Api constructor.
   * @param $settings
   */
  public function __construct($settings) {
    self::$settings = $settings;
    self::$instance = $this;
  }

  /**
   * @param $path
   * @param array $method
   */
  public function allowPath($path, $method = ["*"]) {
    $this->allowed_paths[$path] = $method;
  }

  /**
   * processCall.
   */
  public function processCall() {
    try {
      $this->request = Request::getInstance();
      $this->response = Response::getInstance();
      $this->project = Project::getInstance();

      if ($this->request->initPath($this->allowed_paths)) {

        $this->current_token = TokenFactory::create($this->request->token_type, $this->request->token);
        if ($this->current_token->valid) {
          $this->logged_in_user = array(
            "id" => $this->current_token->data["user_id"],
            "item" => $this->current_token->token_user);
        }

        if ($this->request->isTokenRequired()) {
          if ($this->current_token->valid == false) {
            if ($this->request->entity == "user") {
              throw new DwapiException('For a user query a valid token is always required', DwapiException::DW_VALID_TOKEN_REQUIRED);
            }
            else {
              throw new DwapiException('Valid token is required', DwapiException::DW_VALID_TOKEN_REQUIRED);
            }
          }
        }

        /* create Endpoint instance according to the endpoint parameter in the Request */
        $this->endpoint = EndpointFactory::create();
        $this->endpoint->execute($this->request->action);
      }

      if (
        (!is_null($this->request->mail) && $this->request->mail["enabled"] == true) ||
        (!is_null($this->endpoint->hook_parameters->mail) && $this->endpoint->hook_parameters->mail["enabled"] == true)) {

        $mail_parameters = $this->request->getParameters("get", "mail");
        if (!is_null($this->endpoint->hook_parameters->mail)) {
          $mail_parameters = $this->endpoint->hook_parameters->mail;
        }
        $mail = new Mail($mail_parameters);
        $mail->send();
      }

    } catch (\Exception $error) {
      $this->response->error = $error;
    }
  }

  /**
   * @return mixed
   */
  public function getCurrentToken() {
    return $this->current_token;
  }

  /**
   * @return mixed
   */
  public function getLoggedInUser() {
    return $this->logged_in_user;
  }

  /**
   * @return dwApiLib|null
   */
  public static function getInstance() {
    return self::$instance;
  }
}