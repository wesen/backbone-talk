<?php

/*
 * Misc. functions (taken over from old site)
 *
 * (c) March 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

require_once('search.php');
require_once('ar_helpers.php');

/** Helper function to transform a name into an URL. **/
function urlify($name) {
  return str_replace(
                     array(" ", "/", "%"),
                     array("_", "#", "%25"),
                     $name);
}

/** Helper function to transform a URL back into a name. **/
function nameify($url) {
  return str_replace(array("_", "#", "%25"),
                     array(" ", "/", "%"),
                     $url);
}

function _myCmp($a, $b) {
  if ($a == $b) {
    return 0;
  }
  return ($a < $b) ? -1 : 1;
}

/* Get the value of $array[$key] if it exists, else $default. */
function array_get($array, $key, $default = null) {
  if (is_object($array)) {
    if (isset($array->$key)) {
      return $array->$key;
    } else {
      return $default;
    }
  }

  if (array_key_exists($key, $array) && $array[$key]) {
    return $array[$key];
  } else {
    return $default;
  }
}

function array_find_first($array, $f) {
  foreach ($array as $a) {
    if ($f($a)) {
      return $a;
    }
  }

  return null;
}

function array_find_by_key($haystack, $needle, $key) {
  foreach ($haystack as $a) {
    if (is_callable($key)) {
      if ($key($a) == $needle) {
        return $a;
      }
    } else if (array_get($a, $key) == $needle) {
      return $a;
    }
  }

  return null;
}


/** copy the specified fields out of $arr into a new array if they exist. **/
function array_copy_fields($arr, $fields) {
  $res = array();
  foreach ($fields as $field) {
    if (array_key_exists($field, $arr)) {
      $res[$field] = $arr[$field];
    }
  }
  return $res;
}

function array_clean($array, $allowed_keys) {
  foreach ($array as $k => $v) {
    if (!in_array($k, $allowed_keys)) {
      unset($array[$k]);
    }
  }
  return $array;
}

function object_set_options($obj, $options, $allowed_keys = array()) {
  $options = array_clean($options, $allowed_keys);

  foreach ($options as $k => $v) {
    $obj->$k = $v;
  }
}

/**
 * Sort an array by field alphabetically or numerically.
 **/
function sortArrayByField($arr, $field, $descending = false) {
  if ($descending) {
    $code = "return -1 * _myCmp(\$a['$field'], \$b['$field']);";
  } else {
    $code = "return _myCmp(\$a['$field'], \$b['$field']);";
  }
  usort($arr, create_function('$a,$b', $code));
  return $arr;
}

/**
 * Compare specific fields of two arrays.
 **/
function compareArrayFields($arr1, $arr2, $fields) {
  foreach ($fields as $field) {
    if ($arr1[$field] != $arr2[$field]) {
      return false;
    }
  }
  return true;
}

/**
 * Check if an associative array is all undefined.
 **/
function checkArrayEmpty($arr) {
  foreach ($arr as $key => $val) {
    if ($val != undefined) {
      return false;
    }
  }

  return true;
}

/**
 * Pluck a key from the $input array.
 **/
function array_pluck($key, $input) {
  if (is_array($key) || !is_array($input)) return array();
  $array = array();
  foreach($input as $v) {
    if(array_key_exists($key, $v)) $array[]=$v[$key];
  }
  return $array;
}

/**
 * Returns the formatted string (first letter of each word capitalized).
 **/
function getFormattedString($string)
{
  return ucwords(strtolower(trim($string)));
}

/**
 * Returns the formatted phone number.
 **/
function getFormattedPhone($phone = '', $convert = true, $trim = true)
{
  if (!function_exists('format_phone_us')) {
    // If we have not entered a phone number just return empty
    if (empty($phone)) {
      return false;
    }

    // Strip out any extra characters that we do not need only keep letters and numbers
    $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);
    // Keep original phone in case of problems later on but without special characters
    $OriginalPhone = $phone;

    // If we have a number longer than 11 digits cut the string down to only 11
    // This is also only ran if we want to limit only to 11 characters
    if ($trim == true && strlen($phone)>11) {
      $phone = substr($phone, 0, 11);
    }

    // Do we want to convert phone numbers with letters to their number equivalent?
    // Samples are: 1-800-TERMINIX, 1-800-FLOWERS, 1-800-Petmeds
    if ($convert == true && !is_numeric($phone)) {
      $replace = array('2'=>array('a','b','c'),
                       '3'=>array('d','e','f'),
                       '4'=>array('g','h','i'),
                       '5'=>array('j','k','l'),
                       '6'=>array('m','n','o'),
                       '7'=>array('p','q','r','s'),
                       '8'=>array('t','u','v'),
                       '9'=>array('w','x','y','z'));

      // Replace each letter with a number
      // Notice this is case insensitive with the str_ireplace instead of str_replace
      foreach($replace as $digit=>$letters) {
        $phone = str_ireplace($letters, $digit, $phone);
      }
    }

    $length = strlen($phone);
    // Perform phone number formatting here
    switch ($length) {
    case 7:
      // Format: xxx-xxxx
      return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
    case 10:
      // Format: (xxx) xxx-xxxx
      return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
    case 11:
      // Format: x(xxx) xxx-xxxx
      return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1($2) $3-$4", $phone);
    default:
      // Return original phone if not 7, 10 or 11 digits long
      return $OriginalPhone;
    }
  }
}

/**
 * Rewrite links in $text with links to new site (useful for static pages and others)
 **/
function rewriteLinks($text) {
  $text = preg_replace('/href="..\//', 'href="'.SUBDIR, $text);
  $text = preg_replace('/href=..\//', 'href='.SUBDIR, $text);
  $text = preg_replace('/href="http:\/\/www.goldeneaglecoin.com\//', 'href="'.SUBDIR, $text);
  $text = preg_replace('/href=http:\/\/www.goldeneaglecoin.com\//', 'href='.SUBDIR, $text);
  return $text;
}

$camelizeHash = array();

/**
 * Convert dash and underscore separated string into camelcase
 **/
function camelize($string) {
  global $camelizeHash;
  /*
  if (array_key_exists($string, $camelizeHash)) {
    return $camelizeHash[$string];
  }
  */

  $orig = $string;

  $string = str_replace(array('-', '_'), ' ', $string);
  $string = ucwords($string);
  $string = lcfirst(str_replace(' ', '', $string));

  $camelizeHash[$orig] = $string;

  return $string;
}

/**
 * Convert array with underscore-keys to camelized keys
 **/
function camelizeHash($hash) {
  //  return $hash;
  $res = array();
  foreach ($hash as $k => $v) {
    $res[camelize($k)] = $v;
  }
  return $res;
}

function modelToJSON($model, array $options = array()) {
  return $model->toJSON($options);
}

function arrayToJSON($arr, array $options = array()) {
  $cb = function ($mod) use ($options) {
    return $mod->toJSON($options);
  };
  $res = array();
  foreach ($arr as $a) {
    $res[] = $cb($a);
  }
  return $res;
}

function arrayClearDefaults($arr, array $defaults = array()) {
  foreach ($arr as $k => $v) {
    if ($defaults[$k] == $v) {
      unset($arr[$k]);
    }
  }
  return $arr;
}

/**
 * Constrain a numerical value between min and max.
 **/
function constrain($a, $min, $max) {
  return max($min, min($a, $max));
}

/**
 * Shorten text without cutting words
 **/
function shorten_text($text, $len) {
  $chars = $len;

  $orig_len = strlen($text);
  $text = $text." ";
  $text = substr($text, 0, $chars);
  $text = substr($text, 0, strrpos($text, ' '));
  if (strlen($text) < $orig_len) {
    $text = $text." ...";
  }

  return $text;
}

/** Require all the files in a directory. **/
function require_dir($directory) {
  foreach (glob($directory."/*.php") as $file) {
    require_once(realpath($file));
  }
}

function cleanupObj($obj, $fields) {
  foreach ($obj as $key => $val) {
    if (!in_array($key, $fields)) {
      unset($obj[$key]);
    }
  }
  return $obj;
}

function cleanupArray($arr, $fields) {
  for ($i = 0, $len = count($arr); $i < $len; $i++) {
    $arr[$i] = cleanupObj($arr[$i], $fields);
  }
  return $arr;
}

function make_path($path) {
  $len = strlen($path);
  if ($path[$len - 1] != '/') {
    return $path."/";
  } else {
    return $path;
  }
}

/**
 * Format active record validation errors.
 **/
function formatActiveRecordErrors($obj) {
  $res = "";
  foreach($obj->errors->to_array() as $k => $errorList) {
    $res .= "$k: (".$obj->$k.") ".implode(", ", $errorList).". ";
  }
  return $res;
}

/**
 * Get a random element out of an array
 **/
function random_elt($arr) {
  return $arr[rand(0, count($arr)-1)];
}

/**
 * Format a number in MB.
 **/
function formatBytes($bytes, $precision = 2) {
  $units = array('B', 'KB', 'MB', 'GB', 'TB');
  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);

  // Uncomment one of the following alternatives
  $bytes /= pow(1024, $pow);
  // $bytes /= (1 << (10 * $pow));

  return round($bytes, $precision) . ' ' . $units[$pow];
}

/***************************************************************************
 *
 * Check if a file was cached
 *
 ***************************************************************************/
function cacheFile($file, array $options = array()) {
  $defaults = array("contents" => null,
                    "maxAge" => 3600);
  $options = array_merge($defaults, $options);

  $lastModified = filemtime($file);

  header('Cache-Control: max-age='.$options["maxAge"]);
  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');

  $contents = $options["contents"];
  if (!$contents) {
    $contents = file_get_contents($file);
  }

  if ($contents) {
    $hash = md5($contents);

    header('Etag: '.$hash);

    $headers = apache_request_headers();
    $PageWasUpdated = !(isset($headers['If-Modified-Since']) and
                        strtotime($headers['If-Modified-Since']) == $lastModified);
    $DoIDsMatch = (isset($headers['If-None-Match']) and
                   preg_match($hash, $headers['If-None-Match']));

    if (!$PageWasUpdated or $DoIDsMatch){
      header('HTTP/1.1 304 Not Modified');
      header('Connection: close');
      return true;
    }
  }

  return false;
}

function cacheFiles($files, array $options = array()) {
  $defaults = array("maxAge" => 3600);
  $options = array_merge($defaults, $options);

  $lastModified = 0;
  foreach ($files as $file) {
    $time = filemtime($file);
    if ($time > $lastModified) {
      $lastModified = $time;
    }
  }

  header('Cache-Control: max-age='.$options["maxAge"]);
  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');
}

/**
 * Recursive glob
 **/
function rglob($pattern = '*', $path = '', $flags = 0)
{
  $paths = glob($path.'*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
  $files = glob($path.$pattern, $flags);
  foreach ($paths as $path) {
    $files = array_merge($files, rglob($pattern, $path, $flags));
  }
  return $files;
}

/**
* Generate password using alphabates and numbers   
**/
function generatePassword() {
    $CharPool='abcdfghjkmnpqrstvwxyz';
    $PassLength=10;
    $NewPass='';
    for($i=0;$i<$PassLength;$i++) {
        # Pick a random character from the pool
        $Char = substr($CharPool, mt_rand(0, strlen($CharPool)-1), 1);
        # Don't add if it's already in the password
        if (!strstr($NewPass, $Char)) $NewPass.= $Char;
        else $i=($i==0)?0:--$i;
    }
    return $NewPass;
}

?>
