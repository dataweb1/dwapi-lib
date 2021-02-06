<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\JwtToken;
use dwApiLib\dwApiLib;
use dwApiLib\reference\Reference;


/**
 * Class EndpointFactory
 * @package dwApi\endpoint
 */
class EndpointFactory {

  /**
   * Return a Endpoint instance according to the $endpoint parameter in the Request
   * @param dwApiLib $api
   * @return Endpoint
   * @throws DwapiException
   */
  public static function create(dwApiLib $api) {
    $endpoint = Request::getInstance()->endpoint;

    $to_check_namespace = ["dwApiLib"];

    $api_class = get_class($api);
    $api_ns = substr($api_class, 0, strrpos($api_class, '\\'));
    if ($api_ns != "dwApiLib") {
      array_unshift($to_check_namespace, $api_ns);
    }

    foreach ($to_check_namespace as $namespace) {
      $endpoint_class_name = $namespace."\\endpoint\\" . ucfirst($endpoint);
      if (class_exists($endpoint_class_name)) {
        return new $endpoint_class_name($api);
      }
    }

    throw new DwapiException('Endpoint not valid', DwapiException::DW_INVALID_ENDPOINT);

  }
}