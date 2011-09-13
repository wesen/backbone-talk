<?php

require_once(VENDOR_DIR."php-sprockets/sprocket.php");
require_once(VENDOR_DIR.'limonade/lib/limonade.php');
require_once(VENDOR_DIR."Proust/Proust.php");

/**
 * Initialize proust
 **/
ini_set('xdebug.max_nesting_level', 512);

$proust = new Proust\Proust(array("enableCache" => Configuration::GetConfig("proust.caching", true),
                                  "templatePath" => SITE_TEMPLATES_DIR,
                                  "cacheDir" => SITE_TEMPLATES_DIR.".proust.cache",
                                  "disableObjects" => true,
                                  "compilerOptions" => array(
                                                             "disableLambdas" => true,
                                                             "disableIndentation" => true,
                                                             "includePartialCode" => Configuration::GetConfig("proust.include_partials", false)
                                                             )));

function dispatch_both($url, $page) {
  dispatch_get($url, $page);
  dispatch_post($url, $page);
}

function dispatch_all($url, $page) {
  dispatch_get($url, $page);
  dispatch_post($url, $page);
  dispatch_put($url, $page);
  dispatch_delete($url, $page);
}


function redirect_both($url, $new_url) {
  $fn = function () use ($url, $new_url) {
    header("Location: ".SUBDIR."$new_url");
    die();
  };
  dispatch_get($url, $fn);
  dispatch_post($url, $fn);
}

function dispatch_static_dir($url, $dir) {
  dispatch_get("$url/**", function () use($dir) {
      $filename = $dir."/".params(0);

      if (!cacheFile($filename)) {
        render_file($filename);
      }
      die();
    });
}

function not_found($errno, $errstr, $errfile = null, $errline = null) {
  return ErrorPage::Run();
}

function server_error($errno, $errstr, $errfile = null, $errline = null) {
  return ErrorPage::Run();
}

/***************************************************************************
 *
 * Rest server
 *
 ***************************************************************************/

function handle_rest_server() {
  global $restServer;

  session_cache_expire(0);
  session_cache_limiter("private");
  if (session_id() == "") {
    session_start();
  }

  $res = $restServer->handle(params(0));
  $restServer->sendResult($res);
  die();
}

/***************************************************************************
 *
 * Templates
 *
 ***************************************************************************/

function handle_templates() {
  global $proust;

  $files = explode(",", params(0));
  header("Content-Type: text/html");

  $realFiles = array();
  foreach ($files as $file) {
    $file = preg_replace("/\.\./", "", $file);
    $name = preg_replace("/\//", "_", preg_replace("/.mustache$/", "", $file));
    $filename = $proust->getPartialFilename($file);
    if ($filename) {
      $realFiles[] = $filename;
    }
    $partial = $proust->getPartial($file);

    echo "<script type=\"text/html\" id=\"$name\">$partial</script>";
  }
  cacheFiles($realFiles);
  die();
}

function handle_sprocket_js() {
  $sprocket = new Sprocket(SUBDIR."js/".params(0),
                           array('baseUri' => SUBDIR,
                                 'debugMode' => Configuration::GetConfig("sprocket.debug", false),
                                 'minify' => Configuration::GetConfig("sprocket.minify", false),
                                 'contentType' => 'application/javascript',
                                 'baseFolder' => SITE_JS_DIR));

  $sprocket->render();
  die();
}

function handle_page($dir = "") {
  $name = params('name')."Page";
  if (file_exists(SITE_PAGES_DIR."$dir/$name.php")) {
    require_once(SITE_PAGES_DIR."$dir/$name.php");
    $res = $name::Run();
    return $res;
  } else {
    return false;
  }
}

?>
