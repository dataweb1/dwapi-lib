<?php
namespace dwApiLib\item;

/**
 * Interface ItemInterface
 */
interface ItemInterface extends BaseItemInterface {

  /**
   * Read.
   * @return mixed
   */
  public function read();


  /**
   * Single read.
   * @return mixed
   */
  public function single_read();


  /**
   * Update.
   * @return mixed
   */
  public function update();


  /**
   * Single update.
   * @return mixed
   */
  public function single_update();


  /**
   * Delete.
   * @return mixed
   */
  public function delete();


  /**
   * Single update.
   * @return mixed
   */
  public function single_delete();


  /**
   * Create.
   * @return mixed
   */
  public function create();
}