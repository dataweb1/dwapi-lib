<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\JwtToken;
use dwApiLib\DwApiLib;
use dwApiLib\reference\Reference;


/**
 * Class EndpointFactory
 * @package dwApiLib\endpoint
 */
class EndpointFactory {

  /**
   * Return a Endpoint instance according to the $endpoint parameter in the Request
   * @param $endpoint
   * @return Endpoint
   * @throws DwapiException
   */
  public static function create($endpoint) {
    $to_check_classes = ["dwApiLib\\endpoint\\" . ucfirst($endpoint)];

    $api_class = get_class(DwApiLib::getInstance());
    $api_ns = substr($api_class, 0, strrpos($api_class, '\\'));
    if ($api_ns != "dwApiLib") {
      array_unshift($to_check_classes, $api_ns."\\endpoint\\" . ucfirst($endpoint));
    }

    foreach ($to_check_classes as $class) {
      if (class_exists($class)) {
        return new $class();
      }
    }

    throw new DwapiException('Endpoint not valid', DwapiException::DW_INVALID_ENDPOINT);

  }
}