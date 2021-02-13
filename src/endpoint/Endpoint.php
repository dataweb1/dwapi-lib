<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\Response;
use dwApiLib\token\JwtToken;
use dwApiLib\dwApiLib;
use dwApiLib\query\QueryFactory;
use dwApiLib\query\ItemInterface;
use dwApiLib\query\UserInterface;


/**
 * Class Endpoint
 * @package dwApiLib\endpoint
 */
abstract class Endpoint
{
  /**
   * @var Request|null
   */
  protected $request;

  /**
   * @var Response|null
   */
  protected $response;

  /**
   * @var JwtToken
   */
  protected $current_token;

  /**
   * @var mixed
   */
  protected $logged_in_user;

  /**
   * @var ItemInterface|UserInterface;
   */
  public $query;

  /**
   * @var int
   */
  public $http_response_code = 200;

  /**
   * @var array\null
   */
  public $result = NULL;

  /**
   * @var array\null
   */
  public $debug = NULL;

  /**
   * @var \stdClass|null
   */
  public $hook_parameters = NULL;


  /**
   * Endpoint constructor.
   * @throws DwapiException
   */
  public function __construct() {
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();
    $this->current_token = dwApiLib::getInstance()->getCurrentToken();
    $this->logged_in_user = dwApiLib::getInstance()->getLoggedInUser();

    /**
     * create Query instance according to the endpoint parameter in the Request
     */
    if ($this->request->entity != "") {
      $this->query = QueryFactory::create($this->request->entity, $this->logged_in_user);
    }
  }

  /**
   * execute.
   * @param $action
   * @throws DwapiException
   */
  public function execute($action) {

    if (!method_exists(get_class($this), $action)) {
      throw new DwapiException('Class method "'.$action.'" is not yet defined.', DwapiException::DW_INVALID_ACTION);
    }

    $this->$action();

    $this->response->http_response_code = $this->http_response_code;
    $this->response->result = $this->result;
    $this->response->debug = $this->debug;
  }

}