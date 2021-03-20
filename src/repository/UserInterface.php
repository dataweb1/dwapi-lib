<?php
namespace dwApiLib\repository;

use dwApiLib\repository\mysql\EntityType as MysqlEntityType;
use dwApiLib\repository\drp7\EntityType as Drp7EntityType;

/**
 * Interface UserInterface
 */
interface UserInterface extends ItemInterface {

  /**
   * login.
   * @return mixed
   */
  public function login();


  /**
   * logout.
   * @return mixed
   */
  public function logout();


  /**
   * login_by_id.
   * @return mixed
   */
  public function login_by_id();


  /**
   * login_by_access_token.
   * @return mixed
   */
  public function login_by_access_token();

  /**
   * confirm.
   * @return mixed
   */
  public function confirm_password();


  /**
   * register.
   * @return mixed
   */
  public function register();


  /**
   * activate_link.
   * @return mixed
   */
  public function activate_link();


  /**
   * reset_password.
   * @return mixed
   */
  public function reset_password();


  /**
   * reset_password_link.
   * @return mixed
   */
  public function reset_password_link();


}