<?php
namespace dwApiLib\api;

use dwApiLib\DwApiLib;

/**
 * Class Response
 * @package dwApiLib\api
 */
class Response {

  /**
   * @var Request|null
   */
  private $request;

  /**
   * @var int
   */
  public $http_response_code = 200;

  /**
   * @var array
   */
  public $result;

  /**
   * @var array
   */
  public $debug;

  /**
   * @var \Exception|DwapiException
   */
  public $error;

  /**
   * @var array
   */
  public $variables = [];

  /**
   * @var Response|null
   */
  private static $instance = null;

  /**
   * Response constructor.
   */
  public function __construct() {

  }


  /**
   * getInstance.
   * @return Response|null
   */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new Response();
    }

    return self::$instance;
  }

  /**
   * getTwigVariables.
   * @return mixed
   * @throws DwapiException
   */
  public function getTwigVariables() {

    if ($this->error == NULL) {
      $this->variables["status"]["success"] = true;
    }
    else {
      $this->variables["status"]["success"] = false;
      $this->variables["status"]["error_code"] = $this->error->getCode();
      $this->variables["status"]["message"] = $this->error->getMessage();
    }
    if (!is_null($this->result)) {
      $this->variables["result"] = $this->result;
    }
    $this->variables["settings"] = Project::getInstance()->site;
    $this->variables["settings"]["api_path"] = DwApiLib::$settings->api_path;

    $this->variables["parameters"] = Request::getInstance()->getParameters();;

    return Helper::maskValue($this->variables);
  }

  /**
   * getJsonVariables.
   * @return mixed
   * @throws DwapiException
   */
  public function getJsonVariables() {

    if ($this->error != NULL) {
      $this->variables["status"]["success"] = false;
      $this->variables["status"]["response_code"] = $this->http_response_code;
      $this->variables["status"]["error_code"] = $this->error->getCode();
      $this->variables["status"]["message"] = $this->error->getMessage();
    }
    else {
      $this->variables["status"]["success"] = true;

      if (!is_null($this->result)) {
        $this->variables["result"] = $this->result;
      }
      if (Request::getInstance()->debug == true) {
        $this->variables["debug"] = $this->debug;
        $this->variables["parameters"] = Request::getInstance()->getParameters();;
      }
    }

    return $this->variables;
  }

}