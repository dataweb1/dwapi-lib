<?php
namespace dwApiLib\token;

use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;

/**
 * Class TokenFactory
 * @package dwApiLib\token
 */
class TokenFactory
{
  /**
   * create.
   * @param $token_type
   * @param $token
   * @return AccessToken|JwtToken
   * @throws DwapiException
   */
  public static function create($token_type, $token) {
    if ($token_type != "") {
      $query_class_name = "dwApiLib\\token\\" . ucfirst($token_type) . "Token";

      if (!class_exists($query_class_name)) {
        throw new DwapiException("Token type '" . $token_type . "' unknown.", DwapiException::DW_TOKEN_TYPE_UNKNOWN);
      } else {
        return new $query_class_name(Request::getInstance()->project, $token);
      }
    }
  }
}