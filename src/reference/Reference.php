<?php
namespace dwApiLib\reference;

use dwApiLib\api\DwapiException;
use dwApiLib\dwApiLib;
use dwApiLib\api\Helper;

/**
 * Class Reference
 * @package dwApiLib\reference
 */
class Reference {
  private $reference;
  private static $instance = NULL;


  /**
   * Reference constructor.
   * @throws DwapiException
   */
  public function __construct()
  {
    if (!$this->reference = Helper::readJson(dwApiLib::$settings->reference_path)) {
      throw new DwapiException('OpenAPI reference not found.', DwapiException::DW_PROJECT_NOT_FOUND);
    }
  }


  /**
   * @param null $request_path
   * @param null $request_method
   * @return bool|PathDefinition
   */
  public function getPathDefinition($request_path = NULL, $request_method = NULL) {
    foreach($this->reference["paths"] as $spec_path => $path_definition) {
      $pattern = '#^' . preg_replace('#{[^}]+}#', '[^/]+', $spec_path) . '/?$#';

      if (preg_match($pattern, $request_path)) {

        if (isset($path_definition[$request_method])) {
          return new PathDefinition($spec_path, $path_definition, $request_method);
        }

      }
    }

    return false;

  }



  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new Reference();
    }

    return self::$instance;
  }

}