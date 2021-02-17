<?php
namespace dwApiLib\query;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Project;
use dwApiLib\dwApiLib;

/**
 * Class QueryFactory
 * @package dwApiLib\query
 */
class QueryFactory {
  /**
   * create.
   * @param string $entity_type
   * @param null $logged_in_user
   * @return ItemInterface|UserInterface
   * @throws DwapiException
   */
  public static function create($entity_type = "", $logged_in_user = NULL) {
    $to_check_classes = [];
    if ($entity_type != "") {
      $to_check_classes[] = "dwApiLib\\query\\".Project::getInstance()->type."\\".ucfirst(str_replace("-", "_", $entity_type));
    }
    $to_check_classes[] = "dwApiLib\\query\\".Project::getInstance()->type."\\Item";

    $api_class = get_class(dwApiLib::getInstance());
    $api_ns = substr($api_class, 0, strrpos($api_class, '\\'));
    if ($api_ns != "dwApiLib") {
      if ($entity_type != "") {
        array_unshift($to_check_classes, $api_ns."\\query\\".Project::getInstance()->type."\\".ucfirst(str_replace("-", "_", $entity_type)));
      }
      array_unshift($to_check_classes, $api_ns."\\query\\".Project::getInstance()->type."\\Item");
    }

    foreach ($to_check_classes as $class) {
      if (class_exists($class)) {
        return new $class($entity_type, $logged_in_user);
      }
    }

    throw new DwapiException("Project type '".Project::getInstance()->type."' unknown.", DwapiException::DW_PROJECT_TYPE_UNKNOWN);
  }
}