<?php
namespace dwApiLib\api;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use voku\helper\URLify;


/**
 * Class Template
 * @package dwApiLib\api
 */
class Template {

  const TEMPLATE_PATH = __DIR__ . '/../../../templates';

  /**
   * renderTwigString.
   * @param $string
   * @param $variables
   * @return string
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\RuntimeError
   * @throws \Twig\Error\SyntaxError
   */
  public static function renderTwigString($string, $variables) {
    $loader = new \Twig\Loader\ArrayLoader();
    $twig = new Environment($loader);
    return $twig->render(
      $twig->createTemplate($string),
      $variables
    );
  }


  /**
   * renderTwigFile.
   * @param $template
   * @param $variables
   * @return string
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\RuntimeError
   * @throws \Twig\Error\SyntaxError
   */
  public static function renderTwigFile($template, $variables) {
    $loader = new FilesystemLoader(self::TEMPLATE_PATH);
    $twig = new Environment($loader,[
      'debug' => true]);
    $twig->addExtension(new \Twig\Extension\DebugExtension());

    return $twig->render(
      $template.".html.twig",
      $variables);
  }

  /**
   * templateArray.
   * @param $project
   * @param $element
   * @param $endpoint
   * @param $action
   * @param string $entity
   * @return array
   */
  public static function templateArray($project, $element, $endpoint, $action, $entity = "") {
    return array(
      $project."/".URLify::filter($element)."--".URLify::filter($endpoint)."--".URLify::filter($action)."--".URLify::filter(strval($entity)),
      $project."/".URLify::filter($element)."--".URLify::filter($endpoint)."--".URLify::filter($action),
      $project."/".URLify::filter($element)."--".URLify::filter($endpoint),
      $project."/".URLify::filter($element),
      URLify::filter($element)."--".URLify::filter($endpoint)."--".URLify::filter($action)."--".URLify::filter(strval($entity)),
      URLify::filter($element)."--".URLify::filter($endpoint)."--".URLify::filter($action),
      URLify::filter($element)."--".URLify::filter($endpoint),
      URLify::filter($element));
  }

  /**
   * pickTemplate.
   * @param $project
   * @param $element
   * @param $endpoint
   * @param $action
   * @param $entity
   * @return bool|mixed
   */
  public static function pickTemplate($project, $element, $endpoint, $action, $entity) {
    $template_array = self::templateArray( $project, $element, $endpoint, $action, $entity);
    foreach($template_array as $template) {
      if (file_exists(self::TEMPLATE_PATH."/".$template.".html.twig")) {
        return $template;
      }
    }
    return false;
  }
}