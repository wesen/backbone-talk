<?php

/*
 * ActiveRecord helper functions
 *
 * (c) July 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

function ar_add_condition(&$options, $conditionStr, array $conditionVars = array()) {
  if (isset($options['conditions']) && is_array($options['conditions'])) {
    $options['conditions'][0] = $options['conditions'][0]." AND $conditionStr";
  } else {
    $options['conditions'] = array($conditionStr);
  }
  $options['conditions'] = array_merge($options['conditions'], $conditionVars);
}

function ar_add_join(&$options, $joinStr) {
  if (isset($options['joins'])) {
    $options['joins'] = $options['joins']." ".$joinStr;
  } else {
    $options['joins'] = $joinStr;
  }
}

function ar_add_sort(&$options, $sortStr) {
  if (isset($options['order'])) {
    $options['order'] = $options['order'].",".$sortStr;
  } else {
    $options['order'] = $sortStr;
  }
}

function ar_set_limit(&$options, $min, $max) {
  if ($min) {
    $options["offset"] = $min;
  } else {
    $min = 0;
  }
  if ($max) {
    $options["limit"] = ($max-$min);
  }
}

function ar_get_id($obj_or_id) {
  if (is_numeric($obj_or_id)) {
    return $obj_or_id;
  } else {
    return $obj_or_id->id;
  }
}

function ar_get_obj($class, $obj_or_id) {
  if (is_a($obj_or_id, $class)) {
    return $obj_or_id;
  } else if (is_numeric($obj_or_id)) {
    return $class::first($obj_or_id);
  } else {
    throw new Exception ("obj_or_id is not an instance of $class or an id");
  }
}

function ar_has_column($class, $name) {
  $table = $class::table();
  $res = null;
  $table->conn->query_and_fetch("SHOW COLUMNS FROM `".$table->table."` LIKE '".$name."'",
                                function ($_res) use (&$res) {
                                  $res = $_res;
                                });

  return $res;
}

function ar_add_column($class, $name, $type) {
  $table = $class::table();
  $res = ar_has_column($class, $name);

  if ($res) {
    return false;
  } else {
    $table->conn->query("ALTER TABLE `".$table->table."` ADD COLUMN `".$name."` $type");
    return true;
  }
}

function ar_modify_column($class, $name, $type) {
  $table = $class::table();
  $res = ar_has_column($class, $name);

  echo $res["type"]." ".$type."\n";
  if ($res["type"] == $type) {
    return false;
  } else {
    $table->conn->query("ALTER TABLE `".$table->table."` MODIFY COLUMN `".$name."` $type");
    return true;
  }
}


function ar_drop_column($class, $name) {
  $table = $class::table();

  $res = ar_has_column($class, $name);
  if (!$res) {
    return false;
  } else {
    $fkey = ar_get_foreign_key($class, $name);
    if ($fkey) {
      $table->conn->query("ALTER TABLE `".$table->table."` DROP FOREIGN KEY $fkey");
    }

    $table->conn->query("ALTER TABLE `".$table->table."` DROP COLUMN $name");
    return true;
  }
}

function ar_get_foreign_key($class, $column) {
  $table = $class::table();
  $res = null;
  $table->conn->query_and_fetch("SHOW CREATE TABLE `".$table->table."`",
                                function ($_res) use (&$res) {
                                  $res = $_res;
                                });
  $lines = explode("\n", $res["create table"]);
  $foreign_key = null;
  foreach ($lines as $line) {
    if (preg_match('/FOREIGN KEY \(`'.$column.'`\)/', $line)) {
      $foreign_key = $line;
      break;
    }
  }
  
  $matches = array();
  if (preg_match('/CONSTRAINT `(.*)` FOREIGN/', $foreign_key, $matches)) {
    $name = $matches[1];
    return $name;
  }

  return null;
}

function ar_do_block($class, $callback, $options = array(), $blockSize = 50) {
  $finished = false;
  for ($i = 0; !$finished; $i += $blockSize) {
    $models = $class::all(array_merge($options, array('limit' => $blockSize,
                                                      'offset' => $i)));

    if (count($models) == 0) {
      $finished = true;
    }

    foreach ($models as $model) {
      $callback($model);
    }
  }
}

?>
