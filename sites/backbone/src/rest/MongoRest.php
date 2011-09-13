<?php

class MongoRest {
  public function __construct() {
    $this->m = new Mongo();
    $this->db = $this->m->portfolio;
  }

  public function filterObj(&$obj) {
    if ($obj && isset($obj["_id"])) {
      $obj["id"] = (string)$obj["_id"];
      unset($obj["_id"]);
    }
    return $obj;
  }

  public function getCollection($name, $filter = array()) {
    $collection = $this->db->$name;
    $arr = iterator_to_array($collection->find($filter));
    $res = array();
    foreach ($arr as $k => &$a) {
      $res[] = $this->filterObj($a);
    }
    return $res;
  }

  public function getSingle($name, $filter = array()) {
    $collection = $this->db->$name;
    $res = $collection->findOne($filter);
    return $this->filterObj($res);
  }

  public function createSingle($name, $data = array()) {
    $collection = $this->db->$name;
    $res = $collection->insert($data);
    if ($res) {
      return $this->filterObj($data);
    } else {
      return null;
    }
  }

  public function updateItem($name, $filter = array(), $update = array()) {
    unset($update["id"]);
    $collection = $this->db->$name;
    $res = $collection->update($filter, array('$set' => $update));
    return $res;
  }

  public function deleteItem($name, $filter = array()) {
    $collection = $this->db->$name;
    return $collection->remove($filter);
  }
};


?>
