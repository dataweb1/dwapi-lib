<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Helper;
use dwApiLib\api\JwtToken;
use dwApiLib\dwApiLib;
use dwApiLib\token\AccessToken;


/**
 * Class User
 * @package dwApiLib\endpoint
 */
class User extends Endpoint {

  /**
   * Login user.
   * @throws DwapiException
   */
  public function login() {
    if ($this->query) {
      $this->query->email = $this->request->getParameters("formData", "email");
      $this->query->password = $this->request->getParameters("formData", "password");


      if ($this->query->login()) {
        $this->current_token->token_user = $this->query->getResult("item");
        $this->current_token->token = $this->current_token->create($this->query->getResult("id"));

        $this->result = $this->query->getResult();
        $this->result["token"] = $this->current_token->token;
      }
    }

    return true;
  }

  /**
   * logout.
   */
  public function logout() {
    if ($this->query) {
      if ($this->query->logout()) {
        $this->logged_in_user = NULL;
      }
    }
  }

  /**
   * activate_link (clicked).
   * @throws DwapiException
   */
  public function activate_link() {
    if ($this->query) {
      if (!isset($this->request->redirect["enabled"])) {
        $this->request->redirect["enabled"] = true;
      }

      $this->query->id = Helper::getIdFromHash($this->query->hash);
      $this->query->activate_link();
    }
  }

  /**
   * reset_password (mail)
   * @throws DwapiException
   */
  public function reset_password() {
    if ($this->query) {
      $this->query->email = $this->request->getParameters("query", "email");

      if ($this->query->reset_password()) {
        $temp_token = new JwtToken($this->request->project);
        $token = $temp_token->create(0, 1);

        $this->result["hash"] = $this->query->getResult("items")[0]["hash"];
        $this->result["temp_token"] = $token;

        if (!isset($this->request->mail["enabled"])) {
          $this->request->mail["enabled"] = true;
        }
      }
    }
  }

  /**
   * reset_password_link (clicked)
   * @throws DwapiException
   */
  public function reset_password_link() {
    if ($this->query) {
      // override redirect "enabled" to true if not given in parameter
      if (!isset($this->request->redirect["enabled"])) {
        $this->request->redirect["enabled"] = true;
      }

      $token = $this->request->getParameters("query", "temp_token");
      $temp_token = new JwtToken($this->request->project, $token);
      if ($temp_token->validate_token()) {
        $this->query->id = Helper::getIdFromHash($this->query->hash);

        $this->query->reset_password_link();
      } else {
        throw new DwapiException('Temp token invalid.', DwapiException::DW_VALID_TOKEN_REQUIRED);
      }
    }
  }

  /**
   * confirm_password.
   * @throws DwapiException
   */
  public function confirm_password() {
    if ($this->query) {
      $token = $this->request->getParameters("query", "temp_token");
      $temp_token = new JwtToken($this->request->project, $token);
      if ($temp_token->validate_token()) {
        $this->query->id = Helper::getIdFromHash($this->query->hash);

        $this->query->email = $this->request->getParameters("query", "email");
        $this->query->password = $this->request->getParameters("formData", "new_password");

        if ($this->result = $this->query->confirm_password()) {
          $this->result = $this->query->getResult();
          $this->debug = $this->query->getDebug();
        }

      } else {
        throw new DwapiException('Temp token invalid.', DwapiException::DW_VALID_TOKEN_REQUIRED);
      }
    }
  }

  /**
   * Register user, send activation mail.
   * @throws DwapiException
   */
  public function register() {
    if ($this->query) {
      $this->query->values = $this->request->getParameters("formData");

      if ($this->query->register()) {
        $this->result = $this->query->getResult();
        $this->debug = $this->query->getDebug();

        if (!isset($this->request->mail["enabled"])) {
          $this->request->mail["enabled"] = true;
        }
      } else {
        throw new DwapiException('User with this email already exists.', DwapiException::DW_USER_EXISTS);
      }
    }
  }

  /**
   * Validate token.
   * @return bool
   * @throws DwapiException
   */
  public function validate_token() {
    if ($this->current_token->validate_token()) {
      $this->result["token"] = $this->current_token->token;
      return true;
    }
    else {
      $this->http_response_code = 401;
      throw new DwapiException('Valid token is required.', DwapiException::DW_VALID_TOKEN_REQUIRED, null, 401);
    }
  }

  /**
   * Extend token.
   * @throws DwapiException
   */
  public function extend_token() {
    if ($this->current_token->validate_token()) {
      $this->current_token->extend_token();

      $this->result["token"] = $this->current_token->token;
      return;
    }
    throw new DwapiException('Valid token is required.', DwapiException::DW_VALID_TOKEN_REQUIRED);
  }

  /**
   * generate_access_token.
   */
  public function generate_access_token() {
    $id = $this->request->getParameters("formData", "id");
    $restrict_host = $this->request->getParameters("formData", "restrict_host");
    $restrict_ip  = $this->request->getParameters("formData", "restrict_ip");

    $access_token = new AccessToken($this->request->project);
    $this->result["access_token"] = $access_token->create($id, $restrict_host, $restrict_ip);

    return;
  }
}