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
    $endpoint_class_name = "dwApi\\endpoint\\".ucfirst($endpoint);
    if (!class_exists($endpoint_class_name)) {
      throw new DwapiException('Endpoint "'.$endpoint_class_name.'" not valid', DwapiException::DW_INVALID_ENDPOINT);
    }

    return new $endpoint_class_name($api);
  }
}