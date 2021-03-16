<?php
namespace dwApiLib\api;


use dwApiLib\dwApiLib;
use dwApiLib\item\drp\User;

/**
 * Class Project
 * @package dwApiLib\api
 */
class Project {

  /**
   * @var string|null
   */
  public $project;
  public $type;
  public $credentials;
  public $site;

  /**
   * @var Project|null
   */
  private static $instance = null;


  /**
   * Project constructor.
   * @throws DwapiException
   */
  public function __construct()
  {

  }

  public function initProject() {
    $this->project = Request::getInstance()->project;
    if (!is_null(dwApiLib::$settings->project)) {
      $this->project = dwApiLib::$settings->project;
    }

    if ($this->project == "") {
      throw new DwapiException('Project key is required', DwapiException::DW_PROJECT_REQUIRED);
    }

    // read project from project.yml
    if ($project = Helper::readYaml($_SERVER["DOCUMENT_ROOT"].'/settings/projects.yml', $this->project)) {
      $this->type = $project["type"];
      $this->credentials = $project["credentials"];
      $this->site = $project["site"];
    } else {
      throw new DwapiException('Project "' . $this->project . '" not found', DwapiException::DW_PROJECT_NOT_FOUND);
    }
  }

  /**
   * getInstance.
   * @return Project|null
   * @throws DwapiException
   */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new Project();
    }

    return self::$instance;
  }

}