<?php
namespace dwApiLib\output;
use dwApiLib\DwApiLib;
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
   * @throws \dwApiLib\api\DwapiException
   */
  public function __construct()
  {
    $this->request = Request::getInstance();
    $this->response = Response::getInstance();
    $this->redirect_parameters = $this->request->getParameters("query", "redirect");
  }


  /**
   * renderRedirectUrl.
   * Render redirect URL from TwigString or TwigFile
   * @return bool|string
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\RuntimeError
   * @throws \Twig\Error\SyntaxError
   * @throws \dwApiLib\api\DwapiException
   */
  private function renderRedirectUrl() {
    if ($this->redirect_parameters["redirect_url"] != "") {
      return Template::renderTwigString($this->redirect_parameters["redirect_url"], $this->response->getTwigVariables());
    }
    else {
      $template = Template::pickTemplate(
        $this->request->project,
        "redirect_url",
        $this->request->endpoint,
        $this->request->action,
        $this->request->method,
        $this->request->getParameters("query", "entity"));
      if ($template != "") {
        return Template::renderTwigFile($template, $this->response->getTwigVariables());
      }
    }
    return false;
  }


  /**
   * render.
   * Do redirect.
   * @throws \dwApiLib\api\DwapiException
   */
  public function render() {
    $redirect_url = $this->renderRedirectUrl();
    if ($redirect_url != "") {
      header("location: ".$redirect_url);
      print_r("Redirecting...");
    }
    else {
      print_r("No website to redirect to...");
    }
  }
}