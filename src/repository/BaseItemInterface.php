<?php
namespace dwApiLib\repository;

/**
 * Interface BaseItemInterface
 * @package dwApiLib\repository
 */
interface BaseItemInterface {

  /**
   * setResult.
   * @param $element
   * @param $value
   * @return mixed
   */
  public function setResult($element, $value);

  /**
   * getResult.
   * @param null $element
   * @return mixed
   */
  public function getResult($element = NULL);


  /**
   * getDebug.
   * @return mixed
   */
  public function getDebug();


  /**
   * getEntityType.
   * @return mixed
   */
  public function getEntityType();

}