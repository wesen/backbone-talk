<?php

/*
 * Simple Sql Builder
 *
 * (c) August 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

/**
 * Simple SQL builder, only selects for now.
 **/
class SimpleSqlBuilder {
  public $conditions = array();
  public $conditionVars = array();
  public $limit = null;
  public $offset = null;
  public $orders = array();
  public $joins = array();
  public $selects = array();
  public $froms = array();

  public function __construct($select, $from, $conditions = array()) {
    $this->addSelect($select);
    $this->addFrom($from);
    $this->addConditions($conditions);
  }
  
  public function to_s() {
    $fieldStr = implode(", ", $this->selects);
    $fromStr = implode(", ", $this->froms);
    $joinStr = implode(" ", $this->joins);
    $conditionStr = implode(" AND ", $this->conditions);
    $orderStr = implode(", ", $this->orders);

    $res = "SELECT $fieldStr FROM $fromStr $joinStr";
    if ($conditionStr != "") {
      $res .= " WHERE $conditionStr";
    }
    if ($orderStr != "") {
      $res .= " ORDER BY $orderStr";
    }
    if ($this->limit) {
      $res .= " LIMIT $this->limit";
    }
    if ($this->offset) {
      $res .= " OFFSET $this->offset";
    }
    return "$res;";
  }

  public function addConditions($hash) {
    if (is_string($hash)) {
      $this->conditions[] = $hash;
      return;
    }
    
    foreach ($hash as $k => $v) {
      if (is_numeric($k)) {
        $this->conditions[] = $v;
      } else {
        $this->conditions[] = $k;
        if (is_array($v)) {
          foreach ($v as $val) {
            $this->conditionVars[] = $val;
          }
        } else {
          $this->conditionVars[] = $v;
        }
      }
    }
  }

  public function addSelect($field) {
    $this->selects[] = $field;
  }

  public function addJoin($join) {
    $this->joins[] = $join;
  }

  public function addOrder($order) {
    $this->orders[] = $order;
  }

  public function setLimit($limit) {
    $this->limit = $limit;
  }

  public function setOffset($offset) {
    $this->offset = $offset;
  }

  public function addFrom($from) {
    $this->froms[] = $from;
  }
};

?>