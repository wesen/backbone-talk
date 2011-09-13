<?php

/*
 * Input validator class
 *
 * (c) April 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class ValidationException extends Exception {
};

/**
 * Validate a US phone number.
 **/
function validUSPhone($phone) {
  $pattern = '/^[\(]?(\d{0,3})[\)]?[\s]?[\-]?(\d{3})[\s]?[\-]?(\d{4})[\s]?[x]?(\d*)$/';
  if (preg_match($pattern, $phone, $matches)) {
    return true;
  } else {
    return false;
  }
}

/**
   Validate an email address.
   Provide email address (raw input)
   Returns true if the email address has the email 
   address format and the domain exists.
*/
function validEmail($email)
{
  $isValid = true;
  $atIndex = strrpos($email, "@");
  if (is_bool($atIndex) && !$atIndex) {
    $isValid = false;
  } else{
    $domain = substr($email, $atIndex+1);
    $local = substr($email, 0, $atIndex);
    $localLen = strlen($local);
    $domainLen = strlen($domain);
    if ($localLen < 1 || $localLen > 64) {
      // local part length exceeded
      $isValid = false;
    } else if ($domainLen < 1 || $domainLen > 255) {
      // domain part length exceeded
      $isValid = false;
    } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
      // local part starts or ends with '.'
      $isValid = false;
    } else if (preg_match('/\\.\\./', $local)) {
      // local part has two consecutive dots
      $isValid = false;
    } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
      // character not valid in domain part
      $isValid = false;
    } else if (preg_match('/\\.\\./', $domain)) {
      // domain part has two consecutive dots
      $isValid = false;
    } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                           str_replace("\\\\","",$local))) {
      // character not valid in local part unless 
      // local part is quoted
      if (!preg_match('/^"(\\\\"|[^"])+"$/',
                      str_replace("\\\\","",$local))) {
        $isValid = false;
      }
    }

    /*
    if ($isValid && !(checkdnsrr($domain,"MX") || 
                      checkdnsrr($domain,"A"))) {
      // domain not found in DNS
      $isValid = false;
    }
    */
  }
  return $isValid;
}

class FormValidator {
  var $_errorList;
  var $_data;

  function __construct($data = null) {
    $this->_data = $data;
  }
  
  function getValue($field) {
    return $this->_data[$field];
  }

  function addError($field, $value, $msg) {
    $this->_errorList[] = array("field" => $field,
                                "value" => $value,
                                "msg"   => $msg);
  }

  function isError() {
    return count($this->_errorList) > 0;
  }

  function getErrorList() {
    return $this->_errorList;
  }

  function resetErrorList() {
    $this->_errorList = array();
  }

  /***************************************************************************
   *
   * validation methods
   *
   ***************************************************************************/

  function isNotEmpty($field, $msg) {
    $value = $this->getValue($field);
    if (!is_string($value)) {
      return true;
    }
    if (trim($value) == "") {
      $this->addError($field, $value, $msg);
      return false;
    } else {
      return true;
    }
  }

  function isNumber($field, $msg) {
    $value = $this->getValue($field);
    if (!is_numeric($value)) {
      $this->addError($field, $value, $msg);
      return false;
    } else {
      return true;
    }
  }

  function isPositiveNumber($field, $msg) {
    $value = $this->getValue($field);
    if (!is_numeric($value) || ($value < 0)) {
      $this->addError($field, $value, $msg);
      return false;
    } else {
      return true;
    }
  }

  function isNotZero($field, $msg) {
    $value = $this->getValue($field);
    if (!is_numeric($value) || ($value == 0)) {
      $this->addError($field, $value, $msg);
      return false;
    } else {
      return true;
    }
  }

  function isWithinRange($field, $msg, $min, $max) {
    $value = $this->getValue($field);
    if (!is_numeric($value) || ($value < $min) || ($value > $max)) {
      $this->addError($field, $value, $msg);
      return false;
    } else {
      return true;
    }
  }

  function isAlpha($field, $msg) {
    $value = $this->getValue($field);
    $pattern = "/^[a-zA-Z]+$/";
    if (preg_match($pattern, $value)) {
      return true;
    } else {
      $this->addError($field, $value, $msg);
      return false;
    }
  }

  function isEmailAddress($field, $msg) {
    $value = $this->getValue($field);
    if (validEmail($value)) {
      return true;
    } else {
      $this->addError($field, $value, $msg);
      return false;
    }
  }

  function isEqual($field1, $field2, $msg) {
    $value1 = $this->getValue($field1);
    $value2 = $this->getValue($field2);
    if ($value1 != $value2) {
      $this->addError($field1, $value1, $msg);
      return false;
    } else {
      return true;
    }
  }

  function isPassword($field, $msg) {
    $value = $this->getValue($field);
    if ((strlen($value) >= 6) && (strlen($value) < 32)) {
      return true;
    } else {
      $this->addError($field, $value, $msg);
      return false;
    }
  }

  function isUSPhone($field, $msg) {
    $value = $this->getValue($field);
    if (validUSPhone($value)) {
      return true;
    } else {
      $this->addError($field, $value, $msg);
      return false;
    }
  }

  function isUSState($field, $msg) {
    $stateData = States::GetStatesData();
    $value = $this->getValue($field);
    if (!in_array(ucfirst(strtolower($value)), array_pluck("stateName", $stateData))) {
      $this->addError($field, $value, $msg);
      return false;
    } else {
      return true;
    }
  }
}

?>