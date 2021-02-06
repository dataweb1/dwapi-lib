<?php
namespace dwApiLib\query;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Project;
use dwApiLib\api\Request;

/**
 * Class QueryFactory
 * @package dwApiLib\query
 */
class QueryFactory {
  /**
   * create.
   * @param string $entity_type
   * @param null $logged_in_user
   * @return QueryInterface|UserQueryInterface
   * @throws DwapiException
   */
  public static function create($entity_type = "", $logged_in_user = NULL) {
    /*
    if ($entity_type == "") {
      $entity_type = Request::getInstance()->endpoint;
    }
    */
    if ($entity_type != "") {
      $query_class_name = "dwApiLib\\query\\".Project::getInstance()->type."\\".ucfirst($entity_type)."Query";
      if (class_exists($query_class_name)) {
        return new $query_class_name($entity_type, $logged_in_user);
      }
    }

    $query_class_name = "dwApiLib\\query\\".Project::getInstance()->type."\\Query";
    if (!class_exists($query_class_name)) {
      throw new DwapiException("Project type '".Project::getInstance()->type."' unknown.", DwapiException::DW_PROJECT_TYPE_UNKNOWN);
    }
    else {
      return new $query_class_name($entity_type, $logged_in_user);
    }
  }
}