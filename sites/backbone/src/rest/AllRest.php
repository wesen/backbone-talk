<?php

class AllRest extends MongoRest {
  /**
   * Get a collection
   *
   * @url GET /$collection
   **/
  public function getAllItems($collection) {
    return $this->getCollection($collection);
  }

  /**
   * Get a collection
   *
   * @url POST /$collection
   **/
  public function createSingleItem($collection, $__data = array()) {
    $res = $this->createSingle($collection, $__data);
    if (!$res) {
      throw new REST\Exception('500');
    } else {
      return $res;
    }
  }

  /**
   * Get a single item
   *
   * @url GET /$collection/$id
   **/
  public function getSingleItem($collection, $id) {
    return $this->getSingle($collection, array('_id' => new MongoId($id)));
  }

  /**
   * Save a single item
   *
   * @url PUT /$collection/$id
   **/
  public function saveSingleItem($collection, $id, $__data = array()) {
    $res = $this->updateItem($collection, array('_id' => new MongoId($id)), $__data);
    if ($res) {
      return $this->getSingle($id);
    } else {
      throw new REST\Exception('500');
    }
  }

  /**
   * Delete a single item
   *
   * @url DELETE /$collection/$id
   **/
  public function deleteSingleItem($collection, $id, $__data = array()) {
    $res = $this->deleteItem($collection, array('_id' => new MongoId($id)));
    if ($res) {
      return true;
    } else {
      throw new REST\Exception('500');
    }
  }
};

?>
