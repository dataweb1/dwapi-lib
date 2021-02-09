<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Helper;

/**
 * Class Item
 * @package dwApiLib\endpoint
 */
class Item extends Endpoint {

  /**
   * Read item.
   * @throws DwapiException
   */
  public function get() {

    $this->query->property = $this->request->getParameters("get", "property", true, true, false);
    $this->query->relation = $this->request->getParameters("get", "relation", true, true, false);
    $this->query->hash = $this->request->getParameters("path", "hash");
    if (!is_null($this->query->hash)) {
      $this->query->id = Helper::getIdFromHash($this->query->hash);

      $this->query->single_read();
    }
    else {
      $this->query->filter = $this->request->getParameters("get", "filter", true, true, false);
      $this->query->paging = $this->request->getParameters("get", "paging", false, false, false);
      $this->query->sort = $this->request->getParameters("get", "sort", true, true, false);

      $this->query->read();
    }

    $this->result = $this->query->getResult();
    if ($this->current_token && $this->current_token->token_type == "jwt") {
      $this->result["extended_token"] = $this->current_token->extend_token();
    }

    $this->debug = $this->query->getDebug();
  }

  /**
   * Put item.
   * @throws DwapiException
   */
  public function put() {

    $this->query->hash = $this->request->getParameters("path", "hash");
    $this->query->values = $this->request->getParameters("put", NULL, true, false, true);

    $this->request->processFiles($this->query->values);

    if (!is_null($this->query->hash)) {
      $this->query->id = Helper::getIdFromHash($this->query->hash);
      $this->query->single_update();

    }
    else {
      $this->query->filter = $this->request->getParameters("get", "filter", true, true, true);
      $this->query->update();
    }

    $this->result = $this->query->getResult();
    if ($this->current_token && $this->current_token->token_type == "jwt") {
      $this->result["extended_token"] = $this->current_token->extend_token();
    }

    $this->debug = $this->query->getDebug();
  }

  /**
   * Post item.
   * @throws DwapiException
   */
  public function post()
  {
    $this->query->values = $this->request->getParameters("post", NULL, true, false, true);
    $this->request->processFiles($this->query->values);

    if ($this->query->create()) {
      $this->http_response_code = 201;
      $this->result = $this->query->getResult();
      if ($this->current_token && $this->current_token->token_type == "jwt") {
        $this->result["extended_token"] = $this->current_token->extend_token();
      }

      $this->debug = $this->query->getDebug();
      return;
    }
    else {
      $this->result = array("id" => NULL);
    }

  }

  /**
   * delete item(s).
   * @throws DwapiException
   */
  public function delete() {
    $this->query->hash = $this->request->getParameters("path", "hash");
    if (!is_null($this->query->hash)) {
      $this->query->id = Helper::getIdFromHash($this->query->hash);

      $this->query->single_delete();

    }
    else {
      $this->query->filter = $this->request->getParameters("delete", "filter", true, false, true);
      $this->query->delete();
    }

    $this->result = $this->query->getResult();
    if ($this->current_token && $this->current_token->token_type == "jwt") {
      $this->result["extended_token"] = $this->current_token->extend_token();
    }

    $this->debug = $this->query->getDebug();
  }
}