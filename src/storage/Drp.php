<?php
namespace dwApiLib\storage;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Project;

/**
 * Class Drp
 * @package dwApiLib\storage
 */
class Drp
{
  private static $instance = null;
  private $host;
  private $post_values = [];

  public function __construct()
  {
      $credentials = Project::getInstance()->credentials;
      $this->host = $credentials["host"];

      $this->setPostValue("remote_ip", $_SERVER['REMOTE_ADDR']);
      $this->setPostValue("remote_host", $_SERVER['REMOTE_HOST']);

  }

  /**
   * load.
   * @return Drp|null
   */
  public static function load()
  {
    if (self::$instance == null)
    {
      self::$instance = new Drp();
    }

    return self::$instance;
  }


  /**
   * setPostValue.
   * @param $key
   * @param $value
   */
  public function setPostValue($key, $value) {
    $this->post_values[$key] =  $value;
  }


  public function getSuccess() {
    return $this->response["success"];
  }

  public function getResult() {
    return $this->response["result"];
  }

  public function getError() {
    return $this->response["error"];
  }

  /**
   * execute.
   * @param $class
   * @param $method
   * @return bool
   * @throws DwapiException
   */
  public function execute($class, $method)
  {
    if ($method != "") {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->host . "/dwapi/" . $class . "/" . $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS => json_encode(
          $this->post_values
        ),
        CURLOPT_CUSTOMREQUEST => "POST",
      ));

      $response = json_decode(curl_exec($curl), true);
      $err = curl_error($curl);

      curl_close($curl);



      if ($err) {
        return false;
      } else {


/*
        print_r("--> ".$class . "/" . $method);
        echo "<pre>";
        print_r($this->post_values);
        echo "</pre>";



*/

        //print_r("--> ".$class . "/" . $method);
 

        return $response;

        /*
        if ($response["success"] == false) {
          if ($response["message"] != "") {
            $response["error"] = json_decode($response["message"]);



            return $response;
            //throw new DwapiException($response["message"], $response["error_code"]);
          }  
          else {
            return NULL;
          }
        }
        else {
          return $response["output"];
        }
        */
      }
    }
  }
}