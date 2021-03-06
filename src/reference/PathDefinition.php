<?php
namespace dwApiLib\reference;

/**
 * Class PathDefinition
 * @package dwApiLib\reference
 */
class PathDefinition {
  private $spec_path;
  private $spec_path_elements;
  private $base_path;
  private $base_path_elements;
  private $parameters;


  /**
   * Path constructor.
   * @param $spec_path
   * @param $path_definition
   * @param $request_method
   */
  public function __construct($spec_path, $path_definition, $request_method) {

    $this->parameters = [];
    foreach($path_definition[$request_method] as $item => $value){
      if ($item == "parameters") {
        foreach((array)$value as $parameter) {
          $in = $parameter["in"];
          /*
          if ($in == "formData") {
            $in = $request_method;
          }
          */
          $this->parameters[$in."_".$parameter["name"]] = $parameter;
        }
      }
      else {
        $this->{$item} = $value;
      }
    }

    foreach((array)$path_definition["parameters"] as $parameter) {
      $this->parameters[$parameter["in"]."_".$parameter["name"]] = $parameter;
    }

    $this->spec_path = $spec_path;
    $this->spec_path_elements = explode("/", $this->spec_path);
    array_shift($this->spec_path_elements);

    $this->base_path = $this->getBasePath();
    $this->base_path_elements = explode("/", $this->base_path);
    array_shift($this->base_path_elements);

    $this->setPathParametersIndex();
  }


  /**
   * @return mixed
   */
  public function getBasePath() {
    $base_path = $this->spec_path;

    $path_parameters = $this->getPathParameters();
    foreach ($path_parameters as $parameter_key => $parameter) {
      $base_path = str_replace("/{" . $parameter["name"] . "}", "", $base_path);
    }

    return $base_path;
  }


  /**
   * @param $index
   * @return mixed
   */
  public function getBasePathElement($index) {
    return $this->base_path_elements[$index];
  }


  /**
   * @return array
   */
  public function getPathParameters() {
    $path_parameters = [];
    foreach ($this->parameters as $parameter_key => $parameter) {
      if (substr($parameter_key, 0, 5) == "path_") {
        $path_parameters[] = $parameter;
      }
    }
    return $path_parameters;
  }

  /**
   * @return array
   */
  public function getQueryParameters() {
    $path_parameters = [];
    foreach ($this->parameters as $parameter_key => $parameter) {
      if (substr($parameter_key, 0, 6) == "query_") {
        $path_parameters[] = $parameter;
      }
    }
    return $path_parameters;
  }

  /**
   * getBodyParameters.
   * @param $type
   * @return array
   */
  public function getBodyParameters($type) {
    $path_parameters = [];
    foreach ($this->parameters as $parameter_key => $parameter) {
      if (substr($parameter_key, 0, 9) == $type."_") {
        $path_parameters[] = $parameter;
      }
    }
    return $path_parameters;
  }

  /**
   * @param $key
   * @return bool|mixed
   */
  public function getParameter($key) {
    if (array_key_exists($key, $this->parameters)) {
      return $this->parameters[$key];
    }
    return false;
  }


  /**
   * @param $parameter
   * @return bool
   */
  public function isParameterRequired($parameter) {
    $parameters = $this->getRequiredParameters();
    foreach($parameters as $key => $p) {
      if (strtolower($key) == strtolower($parameter)) {
        return true;
      }
    }
    return false;
  }

  /**
   * @return array
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * @param $parameter
   * @return bool
   */
  public function getParameterDefaultValue($parameter) {
    if (array_key_exists("default", $parameter)) {
      return $parameter["default"];
    }
  }

  /**
   * @return array
   */
  public function getRequiredParameters() {
    $required_parameters = [];
    $parameters = $this->getParameters();
    foreach ($parameters as $key => $parameter) {
      if ($parameter["required"] == 1) {
        $required_parameters[$key] = $parameter;
      }
    }

    return $required_parameters;
  }

  /**
   *
   */
  private function setPathParametersIndex() {
    foreach($this->spec_path_elements as $element_key => $element) {
      if (substr($element, 0, 1) == "{" && substr($element, strlen($element)-1, 1) == "}") {
        $name = substr($element, 1, strlen($element)-2);
        $this->parameters["path_".$name]["key"] = $element_key;
      }
    }
  }

  /**
   * getInstance.
   * @return PathDefinition
   */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new PathDefinition();
    }

    return self::$instance;
  }

  /**
   * breakParameterKeyIntoElements.
   * @param $key
   * @return array
   */
  public function breakParameterKeyIntoElements($key) {
    $key_elements = explode("_", $key);
    $key_type = $key_elements[0];
    $key_parameter = "";
    foreach($key_elements as $i => $key_element) {
      if ($i > 0) {
        if ($i == 1) {
          $key_parameter .= $key_element;
        } else {
          $key_parameter .= "_" . $key_element;
        }
      }
    }

    return array($key_type, $key_parameter);
  }

}