<?php

/*
 * Configuration file for the application
 *
 * (c) February 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class Configuration {
  public static $config;

  static function GetConfig($str, $default = '__raise') {
    $path = explode(".", $str);
    $arr = static::$config;
    foreach ($path as $dir) {
      if (!isset($arr[$dir])) {
        if ($default == '__raise') {
          throw new Exception("Could not find configuration value: $str");
        } else {
          return $default;
        }
      }
      $arr = $arr[$dir];
    }

    return $arr;
  }

  static function ParseConfig($filename) {
    if (isset(static::$config)) {
      die('Already parsed config!');
    }
    static::$config = parse_ini_file($filename, true);
  }

  static function ParseAddConfig($filename) {
    static::$config = array_merge(static::$config, parse_ini_file($filename, true));
  }

  static function SetConfig($name, $value) {
    $path = explode(".", $name);
    $dir = $path[0];
    if (!isset(static::$config[$dir])) {
      static::$config[$dir] = array();
    }
    static::$config[$dir][$path[1]] = $value;
  }

  static function RemoveConfig($name) {
    $path = explode(".", $name);
    $dir = $path[0];
    if (!isset(static::$config[$dir])) {
      return;
    }
    unset(static::$config[$dir][$path[1]]);
  }

  static function GetDBString($name = "db") {
    $type = static::GetConfig("$name.backend");
    $server = static::GetConfig("$name.server");
    $db_name = static::GetConfig("$name.name");
    $username = static::GetConfig("$name.username");
    $password = static::GetConfig("$name.password");

    return "$type:host=$server;dbname=$db_name";
  }

  static function GetDBUrl($name = "db") {
    $type = static::GetConfig("$name.backend");
    $server = static::GetConfig("$name.server");
    $db_name = static::GetConfig("$name.name");
    $username = static::GetConfig("$name.username");
    $password = static::GetConfig("$name.password");
    return "$type://$username:$password@$server/$db_name";
  }

  static function GetMysqlCommandLine($name = "db") {
    $type = static::GetConfig("$name.backend");
    if ($type != "mysql") {
      throw new Exception("can't use mysql to connect to a db of type $type");
    }
    $server = static::GetConfig("$name.server");
    $db_name = static::GetConfig("$name.name");
    $username = static::GetConfig("$name.username");
    $password = static::GetConfig("$name.password");
    return "mysql -u '$username' --password='$password' '$db_name'";
  }

  static function GetAssetPath($str, $default = '__raise') {
    $name = static::GetConfig($str, $default);
    $assetDir = static::GetConfig("paths.assets_dir", $default);
    return make_path($assetDir.$name);
  }
  
  static function GetPath($str, $default = '__raise') {
    $name = static::GetConfig($str, $default);
    return make_path(ROOT_DIR."/$name");
  }

  static function LoadConfig($name) {
    Configuration::ParseConfig(dirname(__FILE__)."/../../config/$name.ini");
  }

  static function LoadAddConfig($name) {
    Configuration::ParseConfig(dirname(__FILE__)."/../../config/$name.ini");
  }

  static function GetRelativePath($str, $default = '__raise') {
    return make_path(SUBDIR.static::GetConfig("paths.".$str, $default));
  }

  static function SaveConfig($filename, $sections = array()) {
    if (count($sections) == 0) {
      foreach (self::$config as $k => $v) {
        $sections[] = $k;
      }
    }

    $res = "";

    foreach ($sections as $section) {
      if (!isset(self::$config[$section])) {
        throw new Exception("No such section: $section");
      }
      $res .= "[$section]\n";
      $cfg = self::$config[$section];
      foreach ($cfg as $k => $v) {
        if (is_bool($v)) {
          $res .= "$k = ".($v ? 'true' : 'false')."\n";
        }
        $res .= "$k = \"$v\"\n";
      }
      $res .= "\n";
    }

    return file_put_contents($filename, $res);
  }
}

?>
