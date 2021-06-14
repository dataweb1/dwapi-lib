<?php
namespace dwApiLib;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Mail;
use dwApiLib\api\Project;
use dwApiLib\api\Request;
use dwApiLib\token\JwtToken;
use dwApiLib\endpoint\Endpoint;
use dwApiLib\repository\RepositoryFactory;
use dwApiLib\endpoint\EndpointFactory;
use dwApiLib\api\Response;
use dwApiLib\token\TokenFactory;


/**
 * Class DwApiLib
 * @package DwApiLib
 */
class DwApiLib
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
   * @var DwApiLib|null
   */
  private static $instance = null;

  /**
   * Api constructor.
   * @param $settings
   */
  public function __construct($settings) {
    self::$settings = $settings;
    self::$instance = $this;

    $this->response = Response::getInstance();
    $this->request = Request::getInstance();
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

      if ($this->request->initPath($this->allowed_paths)) {
        $this->request->initParameters();

        if ($this->request->path_definition) {
          $this->request->validateParameters();
        }

        $this->project = Project::getInstance();
        $this->project->initProject();

        $this->current_token = TokenFactory::create($this->request->token_type, $this->request->token);
        if ($this->current_token->valid) {
          $this->logged_in_user = $this->current_token->token_user;
        }

        if ($this->request->isTokenRequired()) {
          if ($this->current_token->valid == false) {
            if ($this->request->entity == "user") {
              throw new DwapiException('For a user query a valid token is always required', DwapiException::DW_VALID_TOKEN_REQUIRED);
            } else {
              throw new DwapiException('Valid token is required', DwapiException::DW_VALID_TOKEN_REQUIRED);
            }
          }
        }

        /* create Endpoint instance according to the endpoint parameter in the Request */
        $this->endpoint = EndpointFactory::create($this->request->endpoint);
        $to_execute_method = $this->request->action;
        if ($to_execute_method == "") {
          $to_execute_method = $this->request->method;
        }
        $this->endpoint->execute($to_execute_method);
      }

      if (
        (!is_null($this->request->mail) && $this->request->mail["enabled"] == true) ||
        (!is_null($this->endpoint->hook_parameters->mail) && $this->endpoint->hook_parameters->mail["enabled"] == true)) {

        $mail_parameters = $this->request->getParameters("query", "mail");
        if (!is_null($this->endpoint->hook_parameters->mail)) {
          $mail_parameters = $this->endpoint->hook_parameters->mail;
        }
        $mail = new Mail($mail_parameters);
        $mail->send();
      }
    } catch (\Throwable $error) {
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
   * getInstance.
   * @return DwApiLib|null
   */
  public static function getInstance() {
    return self::$instance;
  }
}