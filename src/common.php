<?php

/*
 * common inclusion, setting up db connection and php environment
 *
 * (c) February 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

if (!defined('ROOT_DIR')) {
  define('ROOT_DIR',           realpath(dirname(__FILE__)."/.."));
  define('VENDOR_DIR',         ROOT_DIR."/src/vendor/");
  define('SRC_DIR',            ROOT_DIR."/src/");
  define('SITES_DIR',          ROOT_DIR."/sites");
}

require_once(SRC_DIR.'lib/tracer.php');
require_once(SRC_DIR."lib/functions.php");
require_once(SRC_DIR."lib/validation.php");
require_once(SRC_DIR."lib/Configuration.php");

require_once(VENDOR_DIR."php-restserver/RestServer.php");


/**
 * Enable output buffering for firephp and other purposes.
 **/
if (!defined('STDIN')) {
  ob_start();
}

if (!defined('CONFIG_NAME')) {
  define('CONFIG_NAME', "config");
}

/**
 * Include the site configuration.
 **/
if (defined('CONFIG_FILE')) {
  Configuration::ParseConfig(CONFIG_FILE);
} else {
  Configuration::LoadConfig(CONFIG_NAME);
}

/**
 * Site specific config and include paths
 **/
if (defined('SITE')) {
  foreach (array(SITE_SRC_DIR, SITE_PAGES_DIR, SITE_REST_DIR) as $subdir) {
    set_include_path(get_include_path().PATH_SEPARATOR.$subdir);
  }

  if (file_exists(SITE_CONFIG_DIR."config.ini")) {
    Configuration::ParseConfigAdd(SITE_CONFIG_DIR."config.ini");
  }
}

/**
 * autoloading
 **/
foreach (array(SRC_DIR."pages", SRC_DIR."models", SRC_DIR."lib", SRC_DIR."rest") as $subdir) {
  set_include_path(get_include_path().PATH_SEPARATOR.$subdir);
}

function my_autoload($name) {
  // handle namespaces
  $name = preg_replace('/\\\/', "/", $name);

  foreach (explode(PATH_SEPARATOR, get_include_path()) as $dir) {
    if (file_exists($dir."/".$name.".php")) {
      require($dir."/".$name.".php");
      return;
    }
  }

  throw new Exception("Could not find class $name, path: ".get_include_path());
}

spl_autoload_register('my_autoload');

/**
 * Enable the different site modules
 **/
if (Configuration::GetConfig("app.use_firephp") && !defined('STDIN')) {
  require_once(VENDOR_DIR.'FirePHPCore/fb.php');
}

if (Configuration::GetConfig("app.profile", false)) {
  apd_set_pprof_trace();
}

if (Configuration::GetConfig("app.enable_tracer", false)) {
  Tracer\Tracer::init();
  Tracer\Tracer::mark("start");
}

ini_set('xdebug.show_exception_trace',
        Configuration::GetConfig("app.xdebug_trace_all", false) ? 1 : 0);


/**
 * Web and REST stuff
 **/
if (defined('SITE')) {
  $sites = preg_split('/,/', Configuration::GetConfig("app.sites", ""));
  if (!in_array(SITE, $sites)) {
    die("No such site: ".SITE);
  }

  /**
   * Configure subdir
   **/
  $subdir = Configuration::GetConfig("app.subdir");

  if ($subdir[0] != '/') {
    $subdir = '/'.$subdir;
  }
  if ($subdir[strlen($subdir) - 1] != '/') {
    $subdir .= '/';
  }
  define ('SUBDIR', $subdir.SITE.'/');

  $ret = putenv("TZ=".Configuration::GetConfig("app.timezone", "Europe/Berlin"));

} else {
  define ('SUBDIR', '');
}


global $restServer;
/**
 * Create rest server
 **/

$mode = Configuration::GetConfig("rest.mode", "production");

$restServer = new REST\Server(array("mode" => $mode,
                                    "enableCache" => Configuration::GetConfig("rest.enableCache", false),
                                    "isCLI" => defined('STDIN')));

$restServer = Tracer\TraceWrapper::wrap($restServer, array("get", "post", "put", "delete"));

foreach (rglob('*.php', SRC_DIR."rest") as $path) {
  $info = pathinfo($path);
  $className = $info["filename"];
  $restServer->addHandler($className);
}

if (defined('SITE')) {
  foreach (rglob('*.php', SITE_REST_DIR) as $path) {
    $info = pathinfo($path);
    $className = $info["filename"];
    $restServer->addHandler($className);
  }
}


/** Make rest server globally accessible **/
class RestServer {
  public static function get($path, $params = array()) {
    global $restServer;
    $res = $restServer->get($path, $params);
    if ($res == "Not Found") {
      return null;
    }
    return $res;
  }

  public static function post($path, $data = null, $params = array()) {
    global $restServer;
    return $restServer->post($path, $data, $params);
  }

  public static function put($path, $data = null, $params = array()) {
    global $restServer;
    return $restServer->put($path, $data, $params);
  }

  public static function delete($path) {
    global $restServer;
    return $restServer->delete($path);
  }
};

?>
