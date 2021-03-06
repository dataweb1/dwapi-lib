<?php
namespace dwApiLib\repository\drp;

use dwApiLib\api\DwapiException;
use dwApiLib\repository\UserInterface;

/**
 * Class User
 * @package dwApiLib\repository\drp
 */
class User extends Item implements UserInterface {

  /* user parameters */
  public $email = NULL;
  public $password = NULL;

  public $restrict_host = NULL;
  public $restrict_ip = NULL;

  /**
   * login.
   * @return mixed
   */
  public function login() {
    $this->storage->setPostValue("email", $this->email);
    $this->storage->setPostValue("password", $this->password);
    $this->processResponse($this->storage->execute("User", "login"));

    if (intval($this->result["id"]) > 0) {
      return true;
    }

    return false;
  }


  /**
   * logout.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function logout()
  {
    // TODO: Implement logout() method.
    throw new DwapiException('Logout not yet implemented.', DwapiException::DW_NOT_IMPLEMENTED);
  }

  /**
   * login_by_id.
   * @return mixed
   */
  public function login_by_id()
  {
    $this->storage->setPostValue("id", $this->id);
    $this->processResponse($this->storage->execute("User", "login_by_id"));

    if (intval($this->result["id"]) > 0) {
      return true;
    }

    return false;
  }

  /**
   * login_by_access_token.
   * @return mixed|void
   */
  public function login_by_access_token() {
    $this->storage->setPostValue("id", $this->id);
    $this->processResponse($this->storage->execute("User", "login_by_access_token"));

    if (intval($this->result["id"]) > 0) {
      return true;
    }

    return false;
  }

  /**
   * confirm_password.
   * @return mixed|void
   * @throws DwapiException
   */
  public function confirm_password()
  {
    // TODO: Implement activate_link() method.
    throw new DwapiException('Confirm password not yet implemented.', DwapiException::DW_NOT_IMPLEMENTED);
  }


  /**
   * register.
   * @return mixed|void
   * @throws DwapiException
   */
  public function register()
  {
    // TODO: Implement register() method.
    throw new DwapiException('Register not yet implemented.', DwapiException::DW_NOT_IMPLEMENTED);
  }


  /**
   * activate_link.
   * @return mixed|void
   * @throws DwapiException
   */
  public function activate_link()
  {
    // TODO: Implement activate_link() method.
    throw new DwapiException('Activate link not yet implemented.', DwapiException::DW_NOT_IMPLEMENTED);
  }


  /**
   * reset_password.
   * @return mixed|void
   * @throws DwapiException
   */
  public function reset_password()
  {
    // TODO: Implement reset_password() method.
    throw new DwapiException('Method not yet implemented.', DwapiException::DW_NOT_IMPLEMENTED);
  }

  /**
   * reset_password_link.
   * @return mixed
   * @throws DwapiException
   */
  public function reset_password_link()
  {
    // TODO: Implement reset_password_link() method.
    throw new DwapiException('Method not yet implemented.', DwapiException::DW_NOT_IMPLEMENTED);
  }

}