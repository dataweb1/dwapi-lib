<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Helper;
use dwApiLib\api\Request;
use dwApiLib\api\Response;
use dwApiLib\DwApiLib;
use dwApiLib\repository\RepositoryFactory;
use dwApiLib\token\TokenFactory;
use Hashids\Hashids;

/**
 * Class User
 * @package dwApiLib\endpoint
 */
class User extends Endpoint {

  public function __construct()
  {
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();
    $this->current_token = DwApiLib::getInstance()->getCurrentToken();
    $this->logged_in_user = DwApiLib::getInstance()->getLoggedInUser();

    /**
     * create Item instance according to the endpoint parameter in the Request
     */

    $this->repository = RepositoryFactory::create("user", $this->logged_in_user);
  }

  /**
   * Read item.
   * @throws DwapiException
   */
  public function get() {
    if ($this->repository) {
      $this->repository->property = $this->request->getParameters("query", "property", true, true, false);
      $this->repository->relation = $this->request->getParameters("query", "relation", true, true, false);
      $this->repository->hash = $this->request->getParameters("path", "hash");
      if (!is_null($this->repository->hash)) {
        $this->repository->id = Helper::getIdFromHash($this->repository->hash);

        $this->repository->single_read();
      } else {
        $this->repository->filter = $this->request->getParameters("query", "filter", true, true, false);
        $this->repository->paging = $this->request->getParameters("query", "paging", false, false, false);
        $this->repository->sort = $this->request->getParameters("query", "sort", true, true, false);

        $this->repository->read();
      }

      $this->result = $this->repository->getResult();
      if ($this->current_token && $this->current_token->token_type == "jwt") {
        $this->result["extended_token"] = $this->current_token->extend_token();
      }

      $this->debug = $this->repository->getDebug();
    }
  }

  /**
   * Put item.
   * @throws DwapiException
   */
  public function put() {
    if ($this->repository) {
      $this->repository->hash = $this->request->getParameters("path", "hash");
      $this->repository->values = $this->request->getParameters("body", NULL, true, false, true);

      $this->request->processFiles($this->repository->values);

      if (!is_null($this->repository->hash)) {
        $this->repository->id = Helper::getIdFromHash($this->repository->hash);
        $this->repository->single_update();

      } else {
        $this->repository->filter = $this->request->getParameters("query", "filter", true, true, true);
        $this->repository->update();
      }

      $this->result = $this->repository->getResult();
      if ($this->current_token && $this->current_token->token_type == "jwt") {
        $this->result["extended_token"] = $this->current_token->extend_token();
      }

      $this->debug = $this->repository->getDebug();
    }
  }

  /**
   * Post item.
   * @throws DwapiException
   */
  public function post()
  {
    if ($this->repository) {
      $this->repository->values = $this->request->getParameters("body", NULL, true, false, true);
      $this->request->processFiles($this->repository->values);

      if ($this->repository->create()) {
        $this->http_response_code = 201;
        $this->result = $this->repository->getResult();
        if ($this->current_token && $this->current_token->token_type == "jwt") {
          $this->result["extended_token"] = $this->current_token->extend_token();
        }

        $this->debug = $this->repository->getDebug();
        return;
      } else {
        $this->result = array("id" => NULL);
      }
    }
  }

  /**
   * delete item(s).
   * @throws DwapiException
   */
  public function delete() {
    if ($this->repository) {
      $this->repository->hash = $this->request->getParameters("path", "hash");
      if (!is_null($this->repository->hash)) {
        $this->repository->id = Helper::getIdFromHash($this->repository->hash);

        $this->repository->single_delete();

      } else {
        $this->repository->filter = $this->request->getParameters("formData", "filter", true, false, true);
        $this->repository->delete();
      }

      $this->result = $this->repository->getResult();
      if ($this->current_token && $this->current_token->token_type == "jwt") {
        $this->result["extended_token"] = $this->current_token->extend_token();
      }

      $this->debug = $this->repository->getDebug();
    }
  }


  /**
   * Do login on $query based on $email and $password parameter.
   * @throws DwapiException
   */
  public function login() {
    if ($this->repository) {
      $this->repository->email = $this->request->getParameters("body", "email");
      $this->repository->password = $this->request->getParameters("body", "password");


      if ($this->repository->login()) {
        if (!$this->current_token) {
          $this->current_token = TokenFactory::create("jwt");
        }

        $id = $this->repository->getResult("id");
        $this->current_token->create($id);
        $this->logged_in_user = RepositoryFactory::create("user");
        $this->logged_in_user->id = $id;
        $this->logged_in_user->single_read();

        $this->result = $this->logged_in_user->getResult();
        $this->result["token"] = $this->current_token->token;
      } else {
        $this->http_response_code = 400;
        throw new DwapiException('Active user with this e-mail/password not found.', DwapiException::DW_USER_NOT_FOUND);
      }

      return;
    }
  }


  /**
   * Logout on $query based on $logged_in_user id.
   */
  public function logout() {
    $success = $this->repository->logout($this->logged_in_user->id);
    if ($success == true) {
      $this->logged_in_user = NULL;
    }
  }


  /**
   * Activate link clicked.
   * @throws DwapiException
   */
  public function activate_link() {

    if (!isset($this->request->redirect["enabled"])) {
      $this->request->redirect["enabled"] = true;
    }

    $hashids = new Hashids('dwApi', 50);
    $this->repository->id = $hashids->decode($this->request->hash)[0];
    if (intval($this->repository->id) > 0) {
      if ($this->repository->single_read()) {
        if ($this->repository->getResult("item")["active"] == 0) {
          $this->repository->values = array("active" => 1);
          if ($this->repository->single_update()) {
            $this->result = $this->repository->getResult();
            $this->debug = $this->repository->getDebug();
          }
        }
        else {
          $this->http_response_code = 400;
          throw new DwapiException('User is activated already.', DwapiException::DW_USER_ACTIVATED);
        }
      } else {
        $this->http_response_code = 400;
        throw new DwapiException('User not found.', DwapiException::DW_USER_NOT_FOUND);
      }
    }
    else {
      $this->http_response_code = 400;
      throw new DwapiException('User does not exist.', DwapiException::DW_USER_NOT_FOUND);
    }
  }


  /**
   * Deactivate/logout user.
   * Prepare for sending reset password mail.
   * @return bool
   * @throws DwapiException
   */
  public function reset_password() {
    $email = $this->request->getParameters("query", "email");
    $this->repository->filter = [["email", "=", $email]];
    if ($this->repository->read()) {

      if ($this->repository->getResult("item_count") > 0) {
        $this->repository->setResult("item", $this->repository->getResult("items")[0]);
        $this->repository->id = $this->repository->getResult("items")[0]["user_id"];
        $this->repository->values = array("active" => 0, "force_login" => 1);

        if ($this->repository->single_update()) {

          $temp_token = TokenFactory::create("jwt");
          $token = $temp_token->create(0, 1);

          $this->result["hash"] = $this->repository->getResult("items")[0]["user_id_hash"];
          $this->result["temp_token"] = $token;

          if (!isset($this->request->mail["enabled"])) {
            $this->request->mail["enabled"] = true;
          }

          return true;
        }
      }
      else {
        throw new DwapiException('User not found.', DwapiException::DW_USER_NOT_FOUND);
      }
    }
  }


  /**
   * Reset link clicked.
   * @return bool
   * @throws DwapiException
   */
  public function reset_link() {
    // override redirect "enabled" to true if not given in parameter
    if (!isset($this->request->redirect["enabled"])) {
      $this->request->redirect["enabled"] = true;
    }

    $token = $this->request->getParameters("query", "temp_token");
    $temp_token = TokenFactory::create("jwt", $token);
    //$temp_token = new Token($this->request->project, $token);
    if ($temp_token->validate_token()) {
      $hashids = new Hashids('dwApi', 50);
      $this->repository->id = $hashids->decode($this->request->hash)[0];
      if ($this->repository->single_read()) {
        $this->repository->values = array("active" => 0, "force_login" => 1);
        if ($this->repository->single_update()) {
          return true;
        }
      }
      else {
        throw new DwapiException('User hash is invalid', DwapiException::DW_INVALID_HASH);
      }
    }
    else {
      throw new DwapiException('Link invalid.', DwapiException::DW_INVALID_LINK);
    }
  }


  /**
   * Reset password with $new_password parameter
   */
  public function confirm_password() {

    $token = $this->request->getParameters("query", "temp_token");
    //$temp_token = new Token($this->request->project, $token);
    $temp_token = TokenFactory::create("jwt", $token);
    if ($temp_token->validate_token()) {
      $hashids = new Hashids('dwApi', 50);
      $this->repository->id = $hashids->decode($this->request->hash)[0];
      if ($this->repository->single_read()) {
        $email = $this->request->getParameters("query", "email");
        $new_password = $this->request->getParameters("body", "new_password");
        $array_to_check = array(
          "email" => $email,
          "password" => $new_password);

        //if ($this->checkRequiredValues($array_to_check)) {
        $this->repository->values = array("email" => $email, "password" => $new_password, "active" => 1, "force_login" => 1);
        if ($this->repository->reset_password()) {
          $this->result = $this->repository->getResult();
          return true;
        }
        else {
          throw new DwapiException('User with e-mail not found.', DwapiException::DW_USER_NOT_FOUND);
        }
        //}
      }
      else {
        throw new DwapiException('User hash is invalid.', DwapiException::DW_INVALID_HASH);
      }
    }
    else {
      throw new DwapiException('Link invalid.', DwapiException::DW_INVALID_LINK);
    }
  }


  /**
   * register.
   * @throws DwapiException
   */
  public function register() {
    //$this->repository->values = $this->request->getParameters("body", "values");
    $this->repository->values = $this->request->getParameters("body", NULL, true, false, true);

    $array_to_check = array(
      "email" => $this->repository->values["email"],
      "password" => $this->repository->values["password"]);

    //if ($this->repository->checkRequiredValues($array_to_check)) {
    if ($this->repository->register()) {

      $this->result = $this->repository->getResult();
      $this->debug = $this->repository->getDebug();


      if (!isset($this->request->mail["enabled"])) {
        //$this->request->mail["to_email"] = $this->repository->values["email"];
        $this->hook_parameters = new \stdClass();
        $this->hook_parameters->mail["to_email"] = $this->repository->values["email"];
        $this->request->mail["enabled"] = true;
      }

      return;
    } else {
      throw new DwapiException('User with this email already exists.', DwapiException::DW_USER_EXISTS);
    }
    //}
  }

  /**
   * Validate $current_token.
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
      throw new DwapiException('Valid token is required.', DwapiException::DW_VALID_TOKEN_REQUIRED);
    }
  }

  /**
   * Extend $current_token.
   * @throws DwapiException
   */
  public function extend_token() {
    if ($this->current_token->validate_token()) {
      $this->current_token->extend_token();

      $this->result["token"] = $this->current_token->token;
      return;
    }
    $this->http_response_code = 401;
    throw new DwapiException('Valid token is required.', DwapiException::DW_VALID_TOKEN_REQUIRED);
  }

}