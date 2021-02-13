<?php
namespace dwApiLib\api;

use dwApiLib\dwApiLib;

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


  // The object is created from within the class itself
  // only if the class has no instance.
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
    $variables["result"] = $this->result;
    $variables["settings"] = Project::getInstance()->site;
    $variables["settings"]["api_path"] = dwApiLib::$settings->api_path;
    $variables["parameters"] = Request::getInstance()->getParameters();

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
        "error_code" => $this->error->getCode(),
        "message" => $this->error->getMessage());
    }
    else {
      $variables["status"] = array(
        "success" => true);

      $variables["result"] = $this->result;
      if (Request::getInstance()->debug == true) {
        $variables["debug"] = $this->debug;
      }

      $variables["parameters"] = Request::getInstance()->getParameters();
    }

    return Helper::maskValue($variables);
  }

}