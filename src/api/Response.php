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
    $variables = [];

    if ($this->error == NULL) {
      $variables["status"] = array("success" => true);
    }
    else {
      $variables["status"] = array(
        "success" => false,
        "error_code" => $this->error->getCode(),
        "message" => $this->error->getMessage());
    }
    if (!is_null($this->result)) {
      $variables["result"] = $this->result;
    }
    $variables["settings"] = Project::getInstance()->site;
    $variables["settings"]["api_path"] = DwApiLib::$settings->api_path;

    $parameters = Request::getInstance()->getParameters();
    $variables["parameters"] = $parameters;

    return Helper::maskValue($variables);
  }

  /**
   * getJsonVariables.
   * @return mixed
   * @throws DwapiException
   */
  public function getJsonVariables() {
    $variables = [];

    if ($this->error != NULL) {
      $variables["status"] = array(
        "success" => false,
        "response_code" => $this->http_response_code,
        "error_code" => $this->error->getCode(),
        "message" => $this->error->getMessage());
    }
    else {
      $variables["status"] = array(
        "success" => true);

      if (!is_null($this->result)) {
        $variables["result"] = $this->result;
      }
      if (Request::getInstance()->debug == true) {
        $variables["debug"] = $this->debug;

        $parameters = Request::getInstance()->getParameters();
        $variables["parameters"] = $parameters;
      }
    }

    return $variables;
  }

}