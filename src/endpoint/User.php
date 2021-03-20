<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Helper;

/**
 * Class User
 * @package dwApiLib\endpoint
 */
class User extends Endpoint {

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
}