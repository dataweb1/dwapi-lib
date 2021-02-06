<?php
namespace dwApiLib\output;
use dwApiLib\dwApiLib;
use dwApiLib\api\Request;
use dwApiLib\api\Response;
use dwApiLib\api\Template;

/**
 * Class Redirect
 * @package dwApiLib\output
 */
class Redirect
{
  private $request;
  private $response;
  private $redirect_parameters;


  /**
   * Redirect constructor.
   * @param $api
   */
  public function __construct()
  {
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();
    $this->redirect_parameters = $this->request->getParameters("get", "redirect");
  }


  /**
   * Render redirect URL from TwigString or TwigFile
   * @return bool
   */
  private function renderRedirectUrl() {
    if ($this->redirect_parameters["redirect_url"] != "") {
      return Template::renderTwigString($this->redirect_parameters["redirect_url"], $this->response->getTwigVariables());
    }
    else {
      $template = Template::pickTemplate(
        "redirect_url",
        $this->request->project,
        $this->request->action,
        $this->request->getParameters("get", "entity"));
      if ($template != "") {
        return Template::renderTwigFile($template, $this->response->getTwigVariables());
      }
    }
    return false;
  }


  /**
   * Do redirect
   */
  public function render() {
    $redirect_url = $this->renderRedirectUrl();
    if ($redirect_url != "") {
      header("location: ".$redirect_url);
      print_r("Redirecting...");
    }
  }
}