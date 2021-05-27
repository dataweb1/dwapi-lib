<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\Response;
use dwApiLib\token\JwtToken;
use dwApiLib\DwApiLib;
use dwApiLib\repository\RepositoryFactory;
use dwApiLib\repository\ItemInterface;
use dwApiLib\repository\UserInterface;


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
  public $repository;

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
    $this->current_token = DwApiLib::getInstance()->getCurrentToken();
    $this->logged_in_user = DwApiLib::getInstance()->getLoggedInUser();

    /**
     * create Item instance according to the endpoint parameter in the Request
     */
    if ($this->request->entity != "") {
      $this->repository = RepositoryFactory::create($this->request->entity, $this->logged_in_user);
    }
  }

  /**
   * execute.
   * @param $to_execute_method
   * @throws DwapiException
   */
  public function execute($to_execute_method) {

    if (!method_exists(get_class($this), $to_execute_method)) {
      throw new DwapiException('Class method "'.$to_execute_method.'" is not yet defined.', DwapiException::DW_INVALID_ACTION);
    }

    $this->$to_execute_method();

    $this->response->http_response_code = $this->http_response_code;
    $this->response->result = $this->result;
    $this->response->debug = $this->debug;
  }

}