<?php
namespace dwApiLib\item;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Project;
use dwApiLib\DwApiLib;

/**
 * Class ItemFactory
 * @package dwApiLib\item
 */
class ItemFactory {
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
      $to_check_classes[] = "dwApiLib\\item\\".Project::getInstance()->type."\\".ucfirst(str_replace("-", "_", $entity_type));
    }
    $to_check_classes[] = "dwApiLib\\item\\".Project::getInstance()->type."\\Item";

    $api_class = get_class(DwApiLib::getInstance());
    $api_ns = substr($api_class, 0, strrpos($api_class, '\\'));
    if ($api_ns != "dwApiLib") {
      array_unshift($to_check_classes, $api_ns."\\item\\".Project::getInstance()->type."\\Item");
      if ($entity_type != "") {
        array_unshift($to_check_classes, $api_ns."\\item\\".Project::getInstance()->type."\\".ucfirst(str_replace("-", "_", $entity_type)));
      }
    }

    foreach ($to_check_classes as $class) {
      if (class_exists($class)) {
        return new $class($entity_type, $logged_in_user);
      }
    }

    throw new DwapiException("Project type '".Project::getInstance()->type."' unknown.", DwapiException::DW_PROJECT_TYPE_UNKNOWN);
  }
}