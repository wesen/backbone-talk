<?php

/*
 * Pagination class
 *
 * (c) August 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class Pagination {
  public function __construct(array $options = array()) {
    $defaults = array(
                      "currentPage" => 1,
                      "perPage" => 10,
                      "maxPerPage" => 300,
                      "perPageOptions" => array("Default", 10, 20, 50, "All"),
                      "perPageDefault" => 10
                      );
    $options = array_merge($defaults, $options);
    object_set_options($this, $options, array_keys($defaults));
  }
  
  public function paginate($total) {
    $this->itemTotal = $total;
    $this->itemCount = $this->perPage;

    if ($this->perPage === "Default") {
      $this->itemCount = $this->perPageDefault;
    } else if ($this->perPage === "All") {
      $this->itemCount = min($this->maxPerPage, $this->itemTotal);
    } else if ($this->perPage != 0) {
      $this->itemCount = $this->perPage;
    }

    if ($this->itemTotal < $this->itemCount) {
      $this->itemCount = $total;
    }

    if ($this->itemCount == "") {
      /* default value. */
      $this->itemCount = 10;
    }

    if ($this->itemCount > 0) {
      $this->itemPagesCount = ceil($this->itemTotal / $this->itemCount);
    } else {
      $this->itemPagesCount = 0;
    }

    $this->currentPage = constrain($this->currentPage, 1, $this->itemPagesCount);
    
    $this->itemMin = (($this->currentPage - 1) * $this->itemCount);
    $this->itemMax = $this->itemMin + $this->itemCount;

    return array($this->itemMin, $this->itemMax);
  }

  public function getPaginationJSON(array $options = array()) {
    $selectPerPageOptions = array();
    foreach ($this->perPageOptions as $option) {
      $selectPerPageOptions[] = array("value" => $option,
                                            "name" => $option == "" ? "Default" : $option,
                                            "isSelected" => $this->perPage == $option);
    }

    $pagination = array("hasPrevious" => $this->currentPage > 1,
                        "currentPage" => $this->currentPage,
                        "hasNext" => $this->currentPage < $this->itemPagesCount,
                        "nextPage" => $this->currentPage  + 1,
                        "prevPage" => $this->currentPage  - 1,
                        "pageCount" => $this->itemPagesCount,
                        "itemMinInc" => $this->itemMin + 1,
                        "itemMin" => $this->itemMin,
                        "itemMax" => $this->itemMax,
                        "itemCount" => $this->itemCount,
                        "itemTotal" => $this->itemTotal,
                        "perPage" => $this->perPage,
                        "options" => $selectPerPageOptions,
                        "pages" => array());

    if (isset($options["additionalParameters"])) {
      $params = array();
      foreach ($options["additionalParameters"] as $k => $v) {
        if ($v) {
          $params[] = "$k=$v";
        }
      }
      $pagination["additionalParameters"] = implode("&", $params);
    }
    $pagination["pages"] = $this->groupPages();

    return $pagination;
  }

  public function groupPages() {
    $pages = array();

    $n = $this->itemPagesCount - 1;
    $i = constrain($this->currentPage - 1, 0, $n);
    
    if ($this->itemPagesCount > 9) {
      /*
        | i  | seq                                     |
        | 0  | *0*  1   2   3  ...   n3   n2   n1   n  |
        | 1  |  0  *1*  2   3  ...   n3   n2   n1   n  |
        | 2  |  0   1  *2*  3  ...   n3   n2   n1   n  |
        | 3  |  0   1   2  *3*  4   ...   n2   n1   n  |
        | 4  |  0   1   2   3  *4*   5   ...   n1   n  |
        | 5  |  0   1  ...  4  *5*   6   ...   n1   n  |
        | n  |  0   1   2   3  ...   n3   n2   n1  *n* |
        | n1 |  0   1   2   3  ...   n3   n2  *n1*  n  |
        | n2 |  0   1   2   3  ...   n3  *n2*  n1   n  |
        | n3 |  0   1   2  ...  n4  *n3*  n2   n1   n  |
        | n4 |  0   1  ...  n5 *n4*  n3   n2   n1   n  |
        | n5 |  0   1  ...  n6 *n5*  n4  ...   n1   n  |
      */

      
      $firstPage = 0;
      $firstEndPage = 1;
      $thirdPage = $n - 1;

      if ($i <= 4) {
        /* n = 0 .. 4 */
        $firstEndPage = max($i + 1, 3);
        $secondPage = max($n - 3, $n - (5 - $i));
        $secondEndPage = $n;
        $thirdPage = $n; // disable
      } else if ($i >= ($n - 4)) {
        /* n = n5 .. n */
        $secondPage = min($n - 3, $i - 1);
        $secondEndPage = $n;
        $thirdPage = $n; // disable
        $firstEndPage = min(3, 5 - ($n - $i));
      } else {
        $secondPage = $i - 1;
        $secondEndPage = $i + 1;
      }

      for ($j = $firstPage; $j <= $firstEndPage; $j++) {
        $page = array("num" => $j + 1,
                      "isDisabled" => $j == $i);
        $pages[] = $page;
      }
      if ($firstEndPage != $secondPage) {
        $page = array("isPlaceHolder" => true);
        $pages[] = $page;
      }
      
      for ($j = $secondPage; $j <= $secondEndPage; $j++) {
        $page = array("num" => $j + 1,
                      "isDisabled" => $j == $i);
        $pages[] = $page;
      }
      
      if ($secondEndPage != $thirdPage) {
        $page = array("isPlaceHolder" => true);
        $pages[] = $page;
      
        for ($j = $thirdPage; $j <= $n; $j++) {
          $page = array("num" => $j + 1,
                        "isDisabled" => $j == $i);
          $pages[] = $page;
        }
      }
    } else {
      for ($j = 0; $j < $this->itemPagesCount; $j++) {
        $page = array("num" => $j + 1,
                      "isDisabled" => $j == $i);
        $pages[] = $page;
      }
    }
    
    return $pages;
  }

};

?>