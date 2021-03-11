<?php
namespace dwApiLib\output;
use dwApiLib\api\Response;

/**
 * Class Json
 * @package dwApiLib\output
 */
class Json {
  private $response;

  public function __construct()
  {
    $this->response = Response::getInstance();
  }

  public function setHeaders(){
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      // return only the headers and not the content
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept-Language');
      header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
      exit;
    }

    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization, Accept-Language');

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
  }

  public function render(){
    $this->setHeaders();

    if ($this->response->error == NULL) {
      http_response_code($this->response->http_response_code);
    } else {
      if (get_class($this->response->error) == "DwapiException" && $this->response->error->getResponseCode() != NULL) {
        $this->response->http_response_code = $this->response->error->getResponseCode();
      }
      else {
        $error_code = strval($this->response->error->getCode());
        if (strlen($error_code) > 3) {
          $this->response->http_response_code = 400;
        } else {
          $first_number = intval(substr($error_code, 0, 1));
          if ($first_number == 0 || $first_number > 5) {
            $this->response->http_response_code = 400;
          }
        }
      }
      http_response_code($this->response->http_response_code);

    }

    echo json_encode($this->response->getJsonVariables(), JSON_PRETTY_PRINT);
  }

}