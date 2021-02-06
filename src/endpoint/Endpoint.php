<?php
namespace dwApiLib\endpoint;
use dwApiLib\api\DwapiException;
use dwApiLib\api\Request;
use dwApiLib\api\Response;
use dwApiLib\api\JwtToken;
use dwApiLib\dwApiLib;
use dwApiLib\query\QueryInterface;
use dwApiLib\query\UserQueryInterface;
use Hashids\Hashids;


/**
 * Class Endpoint
 * @package dwApi\endpoint
 */
abstract class Endpoint
{
  protected $request;
  protected $response;

  /**
   * @var JwtToken
   */
  protected $current_token;
  protected $logged_in_user;

  /**
   * @var QueryInterface|UserQueryInterface;
   */
  public $query;

  /**
   * Endpoint constructor.
   * @param dwApiLib $api
   */
  public function __construct(dwApiLib $api) {
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();

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
      throw new DwapiException('Method does not (yet) exist.', DwapiException::DW_INVALID_ACTION);
    }

    $this->$method();
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