<?php
namespace dwApiLib\query;

/**
 * Interface BaseItemInterface
 * @package dwApiLib\query
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