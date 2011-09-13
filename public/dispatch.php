<?php

define('ROOT_DIR',           realpath(dirname(__FILE__)."/.."));
define('VENDOR_DIR',         ROOT_DIR."/src/vendor/");
define('SRC_DIR',            ROOT_DIR."/src/");
define('SITES_DIR',          ROOT_DIR."/sites");

$uri = "/";

/** Get the URI set by the htaccess handler **/
if (isset($_SERVER['REDIRECT_URI'])) {
  $uri = $_SERVER['REDIRECT_URI'];
} else {
  die("No such address");
}

define('SITE', 'backbone');
if (preg_match('(.*)', $uri, $matches)) {
  define('URI', $matches[0]);

  // XXX hack for limonade
  $_GET['uri'] = URI;

  define('SITE_DIR',           SITES_DIR.'/'.SITE);
  if (!file_exists(SITE_DIR)) {
    die("No such site");
  }
} else {
  die("No such site");
}


define('SITE_SRC_DIR',       SITE_DIR.'/src/');
define('SITE_PAGES_DIR',     SITE_DIR.'/src/pages/');
define('SITE_REST_DIR',      SITE_DIR.'/src/rest/');
define('SITE_VENDOR_DIR',      SITE_DIR.'/vendor/');

define('SITE_JS_DIR',        SITE_DIR.'/js/');
define('SITE_CSS_DIR',        SITE_DIR.'/css/');
define('SITE_RESOURCE_DIR',  SITE_DIR.'/resource/');
define('SITE_CONFIG_DIR',    SITE_DIR.'/config/');
define('SITE_TEMPLATES_DIR', SITE_DIR.'/templates/');

require_once(SRC_DIR.'/common.php');
require_once(SRC_DIR.'/lib/router.php');

require_once(SITE_SRC_DIR.'/router.php');

?>
