<?php

/*
 * Search string conversion methods
 *
 * (c) July 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class SearchString {
  function SearchString($str) {
    /* cleanup boolean operators. */
    $str = preg_replace('/[\+\-<>\(\)~\*]/', '', $str);
    $str = preg_replace('/\'/', '"', $str);
    $str = preg_replace('/\s+/', ' ', $str);
    $this->tokens = str_getcsv($str, ' ', '"');
    $this->tokens = array_values(array_filter(array_map("trim", $this->tokens), function ($x) { return !empty($x); }));
  }

  function getBooleanQuery() {
    return join(' ', array_map(function ($x) { return "+\"$x\""; }, $this->tokens));
  }

  function getNaturalQuery() {
    return join(' ', $this->tokens);
  }
};

?>