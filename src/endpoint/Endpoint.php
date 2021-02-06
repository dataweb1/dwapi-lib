<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\Response;
use dwApiLib\token\JwtToken;
use dwApiLib\dwApiLib;
use dwApiLib\query\QueryFactory;
use dwApiLib\query\QueryInterface;
use dwApiLib\query\UserQueryInterface;
use Hashids\Hashids;


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
   * @var QueryInterface|UserQueryInterface;
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
   * @var bool
   */
  public $sent_mail = false;


  /**
   * Endpoint constructor.
   * @param dwApiLib $api
   * @throws DwapiException
   */
  public function __construct(dwApiLib $api) {
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();

    /**
     * create Query instance according to the endpoint parameter in the Request
     */
    if ($this->request->entity != "") {
      $this->query = QueryFactory::create($this->request->entity, $this->logged_in_user);
    }

    $this->current_token = $api->getCurrentToken();
    $this->logged_in_user = $api->getLoggedInUser();
  }

  /**
   * execute.
   * @param $method
   * @throws DwapiException
   */
  public function execute($method) {

    if (!method_exists(get_class($this), $method)) {
      throw new DwapiException('Class method "'.$method.'" does not (yet) exist.', DwapiException::DW_INVALID_ACTION);
    }

    $this->$method();

    $response = Response::getInstance();
    $response->http_response_code = $this->http_response_code;
    $response->result = $this->result;
    $response->debug = $this->debug;
  }

  /**
   * getIdFromHash.
   * @param $hash
   * @return mixed
   */
  protected function getIdFromHash($hash) {
    $hashids = new Hashids('dwApi', 50);
    return $hashids->decode($this->query->hash)[0];
  }
}