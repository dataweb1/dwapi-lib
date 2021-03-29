<?php
namespace dwApiLib\repository;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Project;
use dwApiLib\DwApiLib;

/**
 * Class RepositoryFactory
 * @package dwApiLib\repository
 */
class RepositoryFactory {
  /**
   * create.
   * @param string $entity_type
   * @param null $logged_in_user
   * @return ItemInterface|UserInterface
   * @throws DwapiException
   */
  public static function create($entity_type = "", $logged_in_user = NULL) {
    $api_class = get_class(DwApiLib::getInstance());
    $api_ns = substr($api_class, 0, strrpos($api_class, '\\'));

    $to_check_classes = [];
    if ($entity_type != "") {
      $to_check_classes[] = $api_ns."\\repository\\".Project::getInstance()->type."\\".str_replace('_', '', str_replace('-', '', ucwords($entity_type, '-_')));
      $to_check_classes[] = "dwApiLib\\repository\\".Project::getInstance()->type."\\".str_replace('_', '', str_replace('-', '', ucwords($entity_type, '-_')));
    }
    $to_check_classes[] = $api_ns."\\repository\\".Project::getInstance()->type."\\Item";
    $to_check_classes[] = "dwApiLib\\repository\\".Project::getInstance()->type."\\Item";

    foreach ($to_check_classes as $class) {
      if (class_exists($class)) {
        return new $class($entity_type, $logged_in_user);
      }
    }

    throw new DwapiException("Project type '".Project::getInstance()->type."' unknown.", DwapiException::DW_PROJECT_TYPE_UNKNOWN);
  }
}