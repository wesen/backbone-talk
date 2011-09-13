<?php

option('base_uri', SUBDIR);

class Backbone {
  public $title = "backbone-talk";

  public function __construct() {
    /***************************************************************************
     *
     * System specific dispatches
     *
     ***************************************************************************/

    dispatch_all('/rest/**', 'handle_rest_server');
    dispatch_static_dir('/resource/images', SITE_RESOURCE_DIR.'/images');
    dispatch_static_dir('/resource', Configuration::GetConfig('paths.assets_dir', SITE_DIR.'/assets'));
    dispatch_static_dir('/css', SITE_CSS_DIR);

    if (Configuration::GetConfig("sprocket.enable", false)) {
      dispatch_get('/js/**', 'handle_sprocket_js');
    } else {
      dispatch_static_dir("/js", SITE_JS_DIR);
    }

    dispatch_both('/', 'indexPage::Run');
  }

  /**
   * Set the HTML title for the page.
   **/
  public function setTitle($title) {
    $this->title = $title;
  }

  public function run() {
    run();
  }
};

global $app;
Tracer\Tracer::start("main");

function configure() {
  option("session", false);
}

$app = new Backbone();
$app->run();

Tracer\Tracer::end("main");
Tracer\Tracer::mark("end", array("memory" => memory_get_usage()));

if (Tracer\Tracer::$enabled) {
  echo $proust->renderTemplate("debugPanel", array_merge(RestServer::get("pages/base"),
                                                         array("traces" => json_encode(Tracer\Tracer::$traces),
                                                               "marks" => json_encode(Tracer\Tracer::$marks))));
}

?>
