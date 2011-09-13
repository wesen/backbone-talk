<?php

class indexPage extends Page {
  static $templatePage = "index";

  public function getJSON() {
    $data = array();
    $json = array_merge(parent::getJSON(), $data);
    fb($json, "json");
    return $json;
  }
};

?>
