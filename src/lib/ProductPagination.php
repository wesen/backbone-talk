<?php

/*
 * Pagination for product pages
 *
 * (c) August 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class ProductPagination extends Pagination {
  public function __construct(array $options = array()) {
    $options = array_merge(array("perPageOptions" => array("Default", 10, 20, 50, "All")),
                           $options);
    parent::__construct($options);
  }
  
  public function getPaginationJSON(array $options = array()) {
    $defaults = array("sort" => PRODUCT_SORT_DEFAULT);
    $options = array_merge($defaults, $options);
    
    $sortOptions = array(PRODUCT_SORT_DEFAULT => "Default",
                         PRODUCT_SORT_ALPHABETICAL => "Alphabetical: A-Z",
                         PRODUCT_SORT_PRICE_HIGHEST_FIRST => "Price: Highest First",
                         PRODUCT_SORT_PRICE_LOWEST_FIRST => "Price: Lowest First");

    $cmbSortOptions = array();
    foreach ($sortOptions as $key => $val) {
      $cmbSortOptions[] = array("value" => $key,
                                "name" => $val,
                                "isSelected" => $options["sort"] == $key);
    }

    $pagination = parent::getPaginationJSON($options);
    $pagination["sortOptions"] = $cmbSortOptions;
    $pagination["sort"] = $options["sort"];

    return $pagination;
  }
  

};

?>