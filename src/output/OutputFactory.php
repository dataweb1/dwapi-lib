<?php
namespace dwApiLib\output;
use dwApiLib\api\Request;
use dwApiLib\output\Json;
use dwApiLib\output\Redirect;

/**
 * Class OutputFactory
 * @package dwApiLib\output
 */
class OutputFactory
{
  /**
   * Return a Output Repository instance based on $redirect parameter
   * @param $api
   * @return mixed
   */
  public static function create() {
    $request = Request::getInstance();
    if (!is_null($request->redirect) && $request->redirect["enabled"] == true) {
      return new Redirect();
    }
    else {
      return new Json();
    }
  }
}