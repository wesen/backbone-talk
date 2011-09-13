<?php

/*
 * Debug tracer
 *
 * (c) August 2011 - Debug tracer
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

namespace Tracer;

class Tracer {
  public static $stack = array();
  public static $marks = array();
  public static $traces = array();
  public static $enabled = false;
  public static $startTime = 0;

  public static function init() {
    static::$stack = array();
    static::$marks = array();
    static::$traces = array();
    static::$enabled = true;
    static::$startTime = microtime(true);
  }
  
  public static function getResults() {
    // cleanup stack
    return array("traces" => static::$traces,
                 "marks" => static::$marks);
  }

  public static function start($name) {
    if (!static::$enabled) {
      return;
    }
    
    array_push(static::$stack, array("name" => $name,
                                     "startTime" => microtime(true) - static::$startTime));
  }

  public static function end($name) {
    if (!static::$enabled) {
      return;
    }

    $top = array_pop(static::$stack);
    if ($top["name"] != $name) {
      throw new \Exception("badly nested trace!: $name");
    }

    $top["endTime"] = microtime(true) - static::$startTime;
    $top["duration"] = $top["endTime"] - $top["startTime"];
    array_push(static::$traces, $top);
  }

  public static function mark($name, $data = null) {
    if (!static::$enabled) {
      return;
    }
    
    array_push(static::$marks, array("name" => $name,
                                     "time" => microtime(true) - static::$startTime,
                                     "data" => $data));
  }

  // XXX do stats too
  public static function getTrace($name) {
    foreach (static::$traces as $trace) {
      if ($trace["name"] == $name) {
        return $trace["duration"];
      }
    }
  }
}

class TraceWrapper {
  public $obj;
  public $traced;

  public static function wrap($obj, $traced = array()) {
    if (Tracer::$enabled) {
      return new TraceWrapper($obj, $traced);
    } else {
      return $obj;
    }
  }
  
  public function __construct($obj, $traced = array()) {
    $this->obj = $obj;
    $this->traced = $traced;
  }

  public function __call($name, $arguments) {
    if (($this->traced == "all") || in_array($name, $this->traced)) {
      $arg = "";
      if (count($arguments > 0) && !is_array($arguments[0])) {
        $arg = (string) $arguments[0];
      }
      $traceName = get_class($this->obj)."__".$name."_".$arg;

      try {
        Tracer::start($traceName);
        $res = call_user_func_array(array($this->obj, $name), $arguments);
        Tracer::end($traceName);
        return $res;
      } catch (Exception $e) {
        Tracer::end($traceName);
        throw $e;
      }
    } else {
      return call_user_func_array(array($this->obj, $name), $arguments);
    }
  }

  public function __get($variable) {
    echo "var: $variable<br/>\n";
    $obj = $this->obj;
    return $obj->{$variable};
  }

  public function __set($variable, $value) {
    $obj = $this->obj;
    return $obj->{$variable} = $value;
  }
}

?>