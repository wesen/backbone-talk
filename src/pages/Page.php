<?php

/*
 * Page renderer
 *
 * (c) February 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

/**
 * This is the main class for pages. This object is passed to the
 * mustache renderer, and thus provides a number of variables and
 * methods that can be accessed directly out of the template. These
 * include path configuration variables, the doctype, access to site
 * and requeest uris, as well as the list of included css and js
 * files.
 **/
class Page extends SessionClass {
  /** A simple cache for template files. */
  public $templates;

  /**
   * Misc html helper variables
   **/

  /**
   * Template data
   **/

  /* misc script data */
  public $pageName;
  public $params;
  public $scriptName;

  static $templatePage = "index";

  /***************************************************************************
   *
   * Constructor
   *
   ***************************************************************************/

  public function __construct() {
    session_cache_expire(0);
    session_cache_limiter("private");
    if (session_id() == "") {
      session_start();
    }

    $this->templates = array();

    /* parse page name */
    $this->pageName = "";
    $paths = explode("?", $_SERVER["REQUEST_URI"]);
    $this->request_uri = $paths[0];
    $this->path = $paths[0];
    $this->params = explode("/", $paths[0]);
    $this->subdir = SUBDIR;

    $this->filename = end($this->params);
    $this->FORM_VALUE = array_merge($_POST, $_GET);
  }

  public static function Run() {
    $page = new static();
    Tracer\Tracer::start("handleForm");
    $page->handleForm();
    Tracer\Tracer::end("handleForm");

    Tracer\Tracer::start("getJSON");
    $json = $page->getJSON();
    Tracer\Tracer::end("getJSON");

    Tracer\Tracer::start("render");
    $res = $page->render(static::$templatePage, $json);
    Tracer\Tracer::end("render");
    return $res;
  }

  /***************************************************************************
   *
   * Methods that need to be overriden by subclasses
   *
   ***************************************************************************/

  public function requireJsFile($require) {
    $jsFiles = array();

    $app = file_get_contents(SITE_JS_DIR."requires/$require.js");
    $lines = preg_split("/\r?\n/", $app);
    foreach ($lines as $line) {
      $matches = array();
      if (preg_match('/^\/\/= require \"(.*)\"/', $line, $matches)) {
        $file = $matches[1];
        if (!Configuration::GetConfig("app.use_minified_js", false) && preg_match('/.min$/', $file)) {
          $tmpFile = preg_replace('/.min$/', '', $file);
          if (file_exists(SITE_JS_DIR."$tmpFile.js")) {
            $file = $tmpFile;
          }
        }
        $jsFiles[] = $file;
      }
    }

    return $jsFiles;
  }

  public function getJSON() {
    return array("js" => $this->requireJsFile("common"),
                 "site"                => SUBDIR,
                 "httpSite"            => "https://".$_SERVER['HTTP_HOST'].SUBDIR,

                 "http_protocol"       => $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://",

                 "subdir"              => SUBDIR,
                 "request_uri"         => $_SERVER['REQUEST_URI'],
                 "scriptName"          => basename($_SERVER['PHP_SELF']),

                 "enableMinify"        => (bool) Configuration::GetConfig("app.use_minified_js", false),

                 "copyrightDate"       => date('F Y'),

                 "path_js"              => Configuration::GetRelativePath("js", "js"),
                 "path_css"             => Configuration::GetRelativePath("css", "css"),
                 "path_images"          => Configuration::GetRelativePath("images", "resource/images"),

                 /* ugly hack to pass json to javascript to show cart update on loading a new page*/
                 "sprocket" => Configuration::GetConfig("sprocket.enable", false));
  }

  /**
   * Handle a form and do the necessary steps.
   **/
  public function handleForm() {
  }

  /***************************************************************************
   *
   * General purpose methods for various settings.
   *
   ***************************************************************************/

  public function GetFormValue($name, $default = null) {
    if (isset($this->FORM_VALUE[$name]) &&
        ($this->FORM_VALUE[$name] != "")) {
      return $this->FORM_VALUE[$name];
    } else {
      return $default;
    }
  }

  public function GetAllFormValues() {
    return $this->FORM_VALUE;
  }

  public function GetFormValues($names) {
    $res = array();
    foreach ($names as $name) {
      $data = $this->GetFormValue($name);
      if ($data != null) {
        $res[$name] = $data;
      }
    }
    return $res;
  }

  /***************************************************************************
   *
   * Browser helper methods for templating
   *
   ***************************************************************************/
  public function http_protocol() {
    if($_SERVER['SERVER_PORT'] == 443) {
      return "https://";
    } else {
      return "http://";
    }
  }

  public function redirect($location) {
    header("Location: $location");
    die();
  }

  public function redirect_303($location) {
    header("HTTP/1.1 303 See Other");
    header("Location: $location");
  }

  /***************************************************************************
   *
   * Template functions.
   *
   ***************************************************************************/

  /**
   * Read out a template, and cache them.
   * Templates need to be stored in the templates/ directory and have the file ending .mustache.
   **/
  public function GetTemplate($templateName) {
    global $proust;
    return $proust->getPartial($templateName);
  }

  /**
   * Render an object using the specified template.
   **/
  public function render($templateName, $obj = array()) {
    global $proust;

    $res = $proust->renderTemplate($templateName, $obj);
    return $res;
  }

};

?>
