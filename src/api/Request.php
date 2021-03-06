<?php
namespace dwApiLib\api;
use dwApiLib\reference\PathDefinition;
use dwApiLib\reference\Reference;


/**
 * Class Request
 * @package dwApiLib\api
 */
class Request
{
  /**
   * @var string
   */
  public $path;

  /**
   * @var array
   */
  public $path_elements;

  /**
   * @var PathDefinition
   */
  public $path_definition;

  /**
   * @var string
   */
  public $method;

  /**
   * @var string
   */
  public $endpoint;

  /**
   * @var string
   */
  public $action;

  /**
   * @var string|null
   */
  public $project;

  /**
   * @var string|null
   */
  public $entity;

  /**
   * @var bool|null
   */
  public $token_required;

  /**
   * @var string|null
   */
  public $hash;

  /**
   * @var array
   */
  public $parameters = [];

  /**
   * @var bool
   */
  public $debug = false;

  /**
   * @var string|null
   */
  public $token = NULL;

  /**
   * @var string|null
   */
  public $token_type = NULL;

  /**
   * @var array|bool|mixed|null
   */
  public $mail;

  /**
   * @var array|bool|mixed|null
   */
  public $redirect;

  /**
   * @var Request|null
   */
  private static $instance = null;

  /**
   * Request constructor.
   */
  public function __construct() {

  }

  public function initParameters() {

    $this->project = $this->getParameters("query", "project");
    $this->entity = $this->getParameters("query", "entity");
    $this->token_required = $this->getParameters("query", "token_required");
    $this->hash = $this->getParameters("query", "hash");
    $this->debug = boolval($this->getParameters("query", "debug"));
    $this->redirect = $this->getParameters("query", "redirect");
    $this->mail = $this->getParameters("query", "mail");

    list($this->token_type, $this->token) = $this->getToken();
  }

  /**
   * @param $allowed_paths
   * @return bool
   * @throws DwapiException
   */
  public function initPath($allowed_paths) {

    $this->path = explode('?', $_SERVER["REQUEST_URI"], 2)[0];
    $this->path_elements = preg_split('@/@', $this->path, NULL, PREG_SPLIT_NO_EMPTY);
    $this->method = strtolower(getenv('REQUEST_METHOD'));
    $this->path_definition = Reference::getInstance()->getPathDefinition($this->path, $this->method);

    // allowed path?
    if ($this->matchPathWithAllowedPaths($this->path, $allowed_paths) == true) {
      $this->endpoint = (string)$this->path_elements[0];
      $this->action = (string)$this->path_elements[1];
      return true;
    }

    // path in reference?
    if ($this->path_definition) {
      $this->endpoint = (string)$this->path_definition->getBasePathElement(0);
      $this->action = (string)$this->path_definition->getBasePathElement(1);
      return true;
    }

    throw new DwapiException('Path/method not valid.', DwapiException::DW_INVALID_PATH);
  }

  /**
   * matchPathWithAllowedPaths.
   * @param $path
   * @param $allowed_paths
   * @return bool
   */
  private function matchPathWithAllowedPaths($path, $allowed_paths) {
    foreach ($allowed_paths as $allowed_path => $methods) {
      $preg_ready = '/^'.str_replace("*", ".*", trim(json_encode($allowed_path), '"')).'$/i';
      if (preg_match($preg_ready, $path)) {

        if ($methods == ["*"] || in_array($this->method, $methods)) {

          return true;
        }
      }
    }
    return false;
  }

  /**
   * Read body from request (data and file part)
   */
  private function _parsePut() {
    global $_PUT;

    /* PUT data comes in on the stdin stream */
    $putdata = fopen("php://input", "r");

    /* Open a file for writing */
    // $fp = fopen("myputfile.ext", "w");

    $raw_data = '';

    /* Read the data 1 KB at a time
       and write to the file */
    while ($chunk = fread($putdata, 1024))
      $raw_data .= $chunk;

    /* Close the streams */
    fclose($putdata);

    // Fetch content and determine boundary
    $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));
    if(empty($boundary)){
      //parse_str($raw_data,$data);
      //$data["values"] = $raw_data;

      $GLOBALS[ '_PUT' ] = $raw_data;
      return;
    }

    // Fetch each part
    $parts = array_slice(explode($boundary, $raw_data), 1);
    $data = array();

    foreach ($parts as $part) {
      // If this is the last part, break
      if ($part == "--\r\n") break;

      // Separate content from headers
      $part = ltrim($part, "\r\n");
      list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

      // Parse the headers list
      $raw_headers = explode("\r\n", $raw_headers);
      $headers = array();
      foreach ($raw_headers as $header) {
        list($name, $value) = explode(':', $header);
        $headers[strtolower($name)] = ltrim($value, ' ');
      }

      // Parse the Content-Disposition to get the field name, etc.
      if (isset($headers['content-disposition'])) {
        $filename = null;
        $tmp_name = null;
        preg_match(
          '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
          $headers['content-disposition'],
          $matches
        );
        list(, $type, $name) = $matches;

        //Parse File
        if( isset($matches[4]) )
        {
          //if labeled the same as previous, skip
          if( isset( $_FILES[ $matches[ 2 ] ] ) )
          {
            continue;
          }

          //get filename
          $filename = $matches[4];

          //get tmp name
          $filename_parts = pathinfo( $filename );
          $tmp_name = tempnam( ini_get('upload_tmp_dir'), $filename_parts['filename']);

          //populate $_FILES with information, size may be off in multibyte situation
          $_FILES[ $matches[ 2 ] ] = array(
            'error'=>0,
            'name'=>$filename,
            'tmp_name'=>$tmp_name,
            'size'=>strlen( $body ),
            'type'=>$value
          );

          //place in temporary directory
          file_put_contents($tmp_name, $body);
        }
        //Parse Field
        else
        {
          $data[$name] = substr($body, 0, strlen($body) - 2);
        }
      }

    }
    $GLOBALS[ '_PUT' ] = $data;
    return;
  }

  /**
   * getAuthorizationHeader.
   * @return string|null
   */
  private function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
      $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
      $requestHeaders = apache_request_headers();
      // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
      $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
      if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
      }
    }
    return $headers;
  }

  /**
   * processParameters.
   * @param $items
   * @return array
   */
  private function processQueryParameters($items) {
    $parameters = [];
    if (is_array($items)) {
      foreach ($items as $key => $item) {
        //$item = addslashes(strip_tags(htmlspecialchars($item)));
        if (Helper::isJson($item)) {
          $parameters[$key] = json_decode($item, true);
        } else {
          $parameters[$key] = $item;
        }
      }
    }
    return $parameters;
  }

  /**
   * processPostPutParameters
   * @param $body
   * @return mixed
   */
  private function processFormDataParameters($body) {
    //$body = "{ \"ok\":\"ok\", \"test\": \"test_def\", \"test1\": \"test1_def\", \"test2\": { \"test2_1\": \"string\", \"test2_2\": \"string\" } }";
    if (Helper::isJson($body)) {
      $parameters = json_decode($body, true);
    } else {
      $parameters = $body;
    }
    return $parameters;
  }


  /**
   * processPathParameters.
   * @return array
   */
  private function processPathParameters() {
    $parameters = [];

    foreach ($this->path_definition->getPathParameters() as $parameter) {
      $parameters[$parameter["name"]] = $this->path_elements[$parameter["key"]];
    }

    return $parameters;
  }

  /**
   * getJWTToken.
   * @return mixed|null
   */
  public function getToken() {
    $headers = $this->getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        $this->token_type = "jwt";
        $this->token = $matches[1];
        return array("jwt", $matches[1]);
      }

      if (preg_match('/Basic\s(\S+)/', $headers, $matches)) {
        $this->token_type = "access";
        $this->token = $matches[1];
        return array("access", $matches[1]);
      }
    }
    return NULL;
  }

  /**
   * getParameters.
   * @param null $type
   * @param null $key
   * @param bool $array_expected
   * @param bool $multi_array_expected
   * @param bool $parameters_required
   * @return array|bool|mixed|null
   * @throws DwapiException
   */
  public function getParameters($type = NULL, $key = NULL, $array_expected = false, $multi_array_expected = false, $parameters_required = false) {

    if ($type != NULL) {
      if ($type == "query" && $_GET) {
        $this->parameters["query"] = $this->processQueryParameters($_GET);
      }
      if ($type == "formData" || $type == "body") {
        if ( $this->method == "delete" || $this->method == "get") {
          $body = file_get_contents('php://input');
          $this->parameters[$type] = $this->processFormDataParameters($body);
        }
        if ($this->method == "post") {
          $body = $_POST;
          if ((array)$body == []) {
            $body = file_get_contents('php://input');
          }
          //$body = $_POST;

          // if Content-Type: application/json in header then $_POST is valid
          // if not data is in first key of $_POST

          /*
          $first_key = array_key_first($body);
          if (Helper::isJson($first_key)) {
            $body = $first_key;
          }
          */

          $this->parameters[$type] = $this->processFormDataParameters($body);
        }
        if ($this->method == "put") {
          $this->_parsePut();//_parsePut
          $body = $GLOBALS["_PUT"];//["body"];
          //$body = array_key_first($GLOBALS["_PUT"]);
          //$body = $GLOBALS["_PUT"]);
          /*
          $GLOBALS["_PUT"] = array_key_first($GLOBALS["_PUT"]);
          $body = $GLOBALS["_PUT"];
          */


          $this->parameters["body"] = $this->processFormDataParameters($body);
        }
      }
      if ($type == "files" && $_FILES) {
        $this->parameters["files"] = $_FILES;
      }

      if ($type == "path") {
        $this->parameters["path"] = $this->processPathParameters();
      }
    }

    if ($array_expected) {
      if ($key == NULL) {
        if ($this->isParameterSyntaxCorrect("value", $this->parameters[$type], $parameters_required)) {
          $this->sanitizeParameterArray($this->parameters[$type], $multi_array_expected);
        }
      }
      else {
        if ($this->isParameterSyntaxCorrect($key, $this->parameters[$type][$key], $parameters_required)) {
          $this->sanitizeParameterArray($this->parameters[$type][$key], $multi_array_expected);
        }
      }
    }

    if ($type == NULL) {
      return $this->parameters;
    }

    if ($key == NULL) {
      if (isset($this->parameters[$type]) && is_array($this->parameters[$type])) {
        return $this->parameters[$type];
      }
    }

    if (isset($this->parameters[$type][$key])) {
      return $this->parameters[$type][$key];
    }
    else {
      return NULL;
    }

    return false;
  }

  /**
   * setParameter.
   * @param $type
   * @param $key
   * @param $value
   */
  public function setParameter($type, $key, $value) {
    if (!is_array($this->parameters[$type])) { $this->parameters[$type] = array(); }
    if (!is_array($this->parameters[$type][$key])) { $this->parameters[$type][$key] = array(); }
    $this->parameters[$type][$key] = $value;
  }

  /**
   * processFiles.
   * @param $values
   * @throws DwapiException
   */
  public function processFiles(&$values) {

    if ($files = $this->getParameters("files")) {
      foreach ($files as $field => $file) {
        $target_dir = $_SERVER["DOCUMENT_ROOT"] . "/files/" . $this->getParameters("query", "project") . "/";
        if (!file_exists($target_dir)) {
          mkdir($target_dir);
        }
        $target_dir = $_SERVER["DOCUMENT_ROOT"] . "/files/" . $this->getParameters("query", "project") . "/" . $this->getParameters("query", "entity") . "/";
        if (!file_exists($target_dir)) {
          mkdir($target_dir);
        }

        $target_file = $target_dir .  basename($file["name"]);

        if (!copy($file["tmp_name"],$target_file)) {
          //if (!move_uploaded_file($file["tmp_name"], $target_file)) {
          throw new DwapiException('Error uploading file(s) - '.$target_file, DwapiException::DW_UPLOAD_ERROR);
        } else {
          //$processed_files[$field] = $file;
          $values[$field] = json_encode(array("type" => explode("/", $file["type"]), "name" => $file["name"], "size" => $file["size"]));
        }
      }
    }
  }

  /**
   * sanitizeParameterArray.
   * @param $array
   * @param $multi_array_expected
   * @return mixed
   */
  public function sanitizeParameterArray(&$array, $multi_array_expected = true) {
    if (is_array($array) && $multi_array_expected) {
      /** ["id", "=", "1"] instead of [["id", "=", "1"]] **/
      if (array_key_exists(0, $array) && !is_array($array[0])) {
        $a[0] = $array;
        $array = $a;
      }
      else {
        /** {"field": "id", "operator": "=", "value": "1"} instead of [{"field": "id", "operator": "=", "value": "1"}] **/
        if (!array_key_exists(0, $array)) {
          $a[0] = $array;
          $array = $a;
        }
      }
    }
  }

  /**
   * @param $verb
   * @param $parameter
   * @param bool $parameter_required
   * @return bool
   * @throws DwapiException
   */
  public function isParameterSyntaxCorrect($verb, $parameter, $parameter_required = true) {
    if ($parameter_required) {
      if (!$parameter) {
        throw new DwapiException(ucfirst($verb) . " is missing. At least one is needed.", DwapiException::DW_SYNTAX_ERROR);
      }
      else {
        if (!is_array($parameter)) {
          throw new DwapiException(ucfirst($verb) . " syntax not correct.", DwapiException::DW_SYNTAX_ERROR);
        }
      }
    }
    else {
      if ($parameter != "") {
        if (!is_array($parameter)) {
          throw new DwapiException(ucfirst($verb) . " syntax not correct.", DwapiException::DW_SYNTAX_ERROR);
        }
      }
    }

    return true;
  }

  /**
   * isTokenRequired.
   * @return bool
   */
  public function isTokenRequired() {
    $token_required = $this->token_required;
    if (is_null($token_required)) {
      if ($this->path_definition && $this->path_definition->isParameterRequired("header_authorization")) {
        $token_required = true;
      } else {
        $token_required = false;
      }
    }

    if (Request::getInstance()->entity == "user") {
      $token_required = true;
    }
    return $token_required;
  }

  /**
   * validateParametersAgainstReference.
   * @throws DwapiException
   */
  public function validateParameters() {
    $types = ["query", "formData", "body", "path"];

    foreach($types as $type) {
      $this->referenceDefaultValues($type);
      $this->referenceRequiredValues($type);
    }
  }

  /**
   * referenceDefaultValues.
   * @param $type
   * @throws DwapiException
   */
  public function referenceDefaultValues($type) {
    foreach ($this->path_definition->getParameters() as $key => $ref_parameter) {
      list($key_type, $key_parameter) = $this->path_definition->breakParameterKeyIntoElements($key);
      if ($key_type == $type) {
        if ($this->getParameters($key_type)) {
          $value = $this->getParameters($key_type)[$key_parameter];
        }
        if (!isset($value) || $value == "") {
          //echo "**".$type."**".$key_elements[1]."<br>";
          $this->setParameter($type, $key_elements[1], $ref_parameter["default"]);
        }
      }
    }
  }

  /**
   * referenceRequiredValues.
   * @param $type
   * @return bool
   * @throws DwapiException
   */
  public function referenceRequiredValues($type) {
    foreach ($this->path_definition->getRequiredParameters() as $key => $ref_parameter) {
      list($key_type, $key_parameter) = $this->path_definition->breakParameterKeyIntoElements($key);
      if ($key_type == $type) {
        if ($this->getParameters($key_type)[$key_parameter] == "") {
          throw new DwapiException(ucfirst($key_parameter) . " is required.", DwapiException::DW_VALUE_REQUIRED);
        }
      }

    }
    return true;
  }

  /**
   * getInstance.
   * @return Request|null
   */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new Request();
    }

    return self::$instance;
  }

}