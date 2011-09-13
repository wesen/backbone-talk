<?php

/*
 * Base class for storing session variables
 *
 * (c) July 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class SessionClass {
  /**
   * Get session variable for this model
   **/
  public static function GetSessionVariable($name, $default = null) {
    return self::GetModelSessionVariable($name, get_called_class(), $default);
  }

  /**
   * Get a session variable from a specific model.
   **/
  public static function GetModelSessionVariable($name, $model, $default = null) {
    $varname = SUBDIR."_".$model."_".$name;

    if (session_id() === "") {
      throw new Exception("No session started");
    }

    if (isset($_SESSION[$varname])) {
      return $_SESSION[$varname];
    } else {
      if ($default != null) {
        $_SESSION[$varname] = $default;
        return $default;
      } else {
        return null;
      }
    }
  }

  /**
   * Set a session variable for this model.
   **/
  public static function SetSessionVariable($name, $value) {
    return self::SetModelSessionVariable($name, get_called_class(), $value);
  }

  /**
   * Set a session variable for a specific model.
   **/
  public static function SetModelSessionVariable($name, $model, $value) {
    if (session_id() === "") {
      throw new Exception("No session started");
    }

    $varname = SUBDIR."_".$model."_".$name;
    $_SESSION[$varname] = $value;
  }

  /**
   * Remove a session variable for this model.
   **/
  public static function RemoveSessionVariable($name) {
    return self::RemoveModelSessionVariable($name, get_called_class());
  }

  /**
   * Remove a session variable for a specific model.
   **/
  public static function RemoveModelSessionVariable($name, $model) {
    if (session_id() === "") {
      throw new Exception("No session started");
    }

    $varname = SUBDIR."_".$model."_".$name;
    unset($_SESSION[$varname]);
  }
};

?>
