<?php
namespace dwApiLib\token;

use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\Project;

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
      $item_class_name = "dwApiLib\\token\\" . ucfirst($token_type) . "Token";

      if (!class_exists($item_class_name)) {
        throw new DwapiException("Token type '" . $token_type . "' unknown.", DwapiException::DW_TOKEN_TYPE_UNKNOWN);
      } else {
        return new $item_class_name(Project::getInstance()->project, $token);
      }
    }
  }
}