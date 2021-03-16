<?php
namespace dwApiLib\item;

/**
 * Interface EntityTypeInterface
 * @package dwApiLib\item
 */
interface EntityTypeInterface {
  /**
   * load.
   * @param $entity
   * @return mixed
   */
  public function load($entity);


  /**
   * getProperties.
   * @return mixed
   */
  public function getProperties();


  /**
   * getPrimaryKey.
   * @return mixed
   */
  public function getPrimaryKey();


  /**
   * isPropertyRequired.
   * @param $property
   * @return mixed
   */
  public function isPropertyRequired($property);


  /**
   * defaultValue.
   * @param $property
   * @return mixed
   */
  public function getPropertyDefaultValue($property);
}