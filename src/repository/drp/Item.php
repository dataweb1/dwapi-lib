<?php
namespace dwApiLib\repository\drp;
use dwApiLib\api\DwapiException;
use dwApiLib\repository\BaseItem;
use dwApiLib\repository\ItemInterface;
use dwApiLib\storage\Drp;



/**
 * Class Item
 * @package dwApiLib\repository\drp
 */
class Item extends BaseItem implements ItemInterface {

  /* item parameters */
  public $values = NULL;
  public $filter = NULL;
  public $property = NULL;
  public $sort = NULL;
  public $hash = NULL;
  public $id = NULL;
  public $paging = NULL;
  public $relation = NULL;


  /**
   * Query constructor.
   * @param string $entity
   * @param null $logged_in_user
   */
  public function __construct($entity = "", $logged_in_user = NULL) {
    parent::__construct($logged_in_user);

    $this->storage = Drp::load();
    $this->storage->setPostValue("api_host", $_SERVER["HTTP_HOST"]);
    $this->storage->setPostValue("project", $this->request->project);
    if ($entity == "user") {
      $entity = "user-user";
    }
    $this->storage->setPostValue("entity", $entity);
  }


  /**
   * single_read
   * @return bool|mixed
   * @throws DwapiException
   */
  public function single_read() {
    $this->storage->setPostValue("id", $this->id);
    $this->storage->setPostValue("relation", $this->relation);
    $this->storage->setPostValue("property", $this->property);
    $this->result = $this->storage->execute("Item", "single_read");

    return true;
  }


  /**
   * read.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function read() {
    $this->storage->setPostValue("filter", $this->filter);
    $this->storage->setPostValue("sort", $this->sort);
    $this->storage->setPostValue("paging", $this->paging);
    $this->storage->setPostValue("relation", $this->relation);
    $this->storage->setPostValue("property", $this->property);
    $this->result = $this->storage->execute("Item", "read");

    return true;
  }


  /**
   * create.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function create()
  {
    $this->values["uid"] = $this->logged_in_user->id;
    $this->storage->setPostValue("values", $this->values);
    $this->result = $this->storage->execute("Item", "create");

    return true;
  }


  /**
   * single_update.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function single_update()
  {
    $this->filter = [["entity_id", "=", $this->id]];
    $this->update();

    return true;
  }


  /**
   * update.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function update() {
    $this->storage->setPostValue("filter", $this->filter);
    $this->storage->setPostValue("values", $this->values);
    $this->result = $this->storage->execute("Item", "update");

    return true;
  }


  /**
   * single_delete.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function single_delete()
  {
    $this->filter = [["entity_id", "=", $this->id]];
    $this->delete();

    return false;
  }


  /**
   * delete.
   * @return bool|mixed
   * @throws DwapiException
   */
  public function delete()
  {
    $this->storage->setPostValue("filter", $this->filter);
    $this->result = $this->storage->execute("Item", "delete");

    return true;
  }
}