/*
 * Form validation from scratch code, inspired by
 * http://webcloud.se/log/Form-validation-with-jQuery-from-scratch/
 *
 * (c) March 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

$(function() {
  /***************************************************************************
   *
   * Validation class
   *
   ***************************************************************************/
  var validation = function () {
    var rules = {
      email: {
        check: function(value) {
          if (value) {
            return testPattern(value, "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])");
          }
          return true;
        },
        msg: "Please Enter A Valid E-Mail Address."
      },

      usPhone: {
        check: function(value) {
          if (value) {
            return testPattern(value, /[\(]?(\d{0,3})[\)]?[\s]?[\-]?(\d{3})[\s]?[\-]?(\d{4})[\s]?[x]?(\d*)/);
          }
          return true;
        },
        msg: "Please Enter A Valid US Phone Number."
      },

      required: {
        check: function (value) {
          if (value) {
            return true;
          } else {
            return false;
          }
        },
        msg: "This Field Is Required."
      },

      password: {
        check: function (value, field, args) {
          if ((value.length < 10)) {
            return false;
          } else {
            return true;
          }
        },
        msg: "Password should be minimum 10 characters."
      },

      passwordDuplicate: {
        check: function (value, field, args) {
          var origField = $("input[name=" + args[0] + "]");
          if (origField.val() != value) {
            return false;
          } else {
            return true;
          }
        },
        msg: "Password and Re-typed password does not match."
      },

      numeric: {
        check: function (value, field, args) {
          var numbersCheckRegExp = /[^\d]/;

          return !numbersCheckRegExp.test(value);
        },
        msg: "Value should be a numeric value."
      },

      positive: {
        check : function (value, field, args) {
          if (value <= 0) {
            return false;
          } else {
            return true;
          }
        },
        msg: "Value should be a positive numeric value"
      },

      usState: {
        check: function (value, field, args) {
          var states = loadCachedAjax(getSiteUrl("rest/app/states"));
          if (_.indexOf(_.pluck(states, "stateName"), value) == -1) {
            return false;
          } else {
            return true;
          }
        },
        msg: "Value should be a valid US state"
      }
    };

    /* private methods */
    var testPattern = function (value, pattern) {
      var regExp = new RegExp(pattern);
      return regExp.test(value);
    };

    /* public methods */
    return {
      addRule: function (name, rule) {
        rules[name] = rule;
      },
      getRule : function (name) {
        return rules[name];
      }
    };

  };

  jQuery.validation = new validation();

  /* adding custom validation rules. */
  $.validation.addRule("test", {
    check: function(value) {
      if (value != "test") {
        return false;
      }
      return true;
    },
    msg: "Must be equal to the word test."
  });

  /***************************************************************************
   *
   * Form class
   *
   ***************************************************************************/
  var Form = function (form, errorHandler, msgMap) {
    var fields = [];
    /* Get all input elements in form */
    if (form.length == 0) {
      return;
    }

    $(form[0].elements).each(function () {
      var field = $(this);
      /* we're only interested in fields with a validation attribute. */
      if (field.attr('validation') !== undefined) {
        fields.push(new Field(field, errorHandler, msgMap));
      }
    });

    this.fields = fields;
    this.errorHandler = errorHandler;
    this.msgMap = msgMap;
  };

  Form.prototype = {
    validate : function () {
      for (field in this.fields) {
        this.fields[field].validate();
      }
    },

    isValid : function () {
      for (field in this.fields) {
        if (!this.fields[field].valid) {
          /* focus the first field that contains an error to let user fix it. */
          this.fields[field].field.focus();

          /* as soon as one field is invalid we can return false right away. */
          return false;
        }
      }

      return true;
    }
  };

  /***************************************************************************
   *
   * Field class
   *
   ***************************************************************************/
  var Field = function (field, errorHandler, msgMap) {
    this.field = field;
    this.valid = false;
    this.errorHandler = errorHandler;
    this.msgMap = msgMap;
    this.attach("change");
    this.attach("keyup");
  };

  $.fn.validateField = function (errorHandler) {
    /* Create an internal reference to the field object. */
    var $this = this,                                                 
    obj       = {},                                                         
    parent    = $this.parent(),                                          
    /* a field can have multiple values to the validation attributes, separated by spaces. */
    types = [],
    argRegexp = new RegExp(/(\w+)\((.+)\)/),                           
    errors    = [];

    if (!$this.attr("validation")) {
      return;
    }

    types = $this.attr("validation").split(" ");
    
    /* check if field is optional, and skip validation. */
    if (($this.val() === "") &&
        (_.indexOf(types, "optional") >= 0)) {
      obj.valid = true;
      return;
    }

    /* iterate over validation types. */
    for (var type in types) {
      /* get the rule from our Validation object */
      var argsMatch = argRegexp.exec(types[type]);
      var rule = undefined;
      var args = [];
      if (argsMatch) {
        rule = $.validation.getRule(argsMatch[1]);
        args = argsMatch[2].split(/\s*,\s*/);
      } else {
        rule = $.validation.getRule(types[type]);
      }

      if (!rule) {
        //          debugLog("No rule for " + types[type]);
      } else {
        if (!rule.check($this.val(), $this, args)) {
          var msg = rule.msg;
          var msgKey = $this.attr("name") + "_" + types[type];

          if (this.msgMap) {
            if (this.msgMap[msgKey]) {
              msg = this.msgMap[msgKey];
            } else if (this.msgMap[types[type]]) {
              msg = this.msgMap[types[type]];
            }
          }

          errors.push({ errorType: type,
                        field: $this[0],
                        msg: msg});
        }
      }
    }

    obj.validationErrors = errors;

    if (errors.length) {
        if (errorHandler) {
          errorHandler(errors, $this);
        }
        obj.valid = false;
    } else {
      if (errorHandler) {
        errorHandler([], $this);
      }
      /* no errors. */
      obj.valid = true;
    }
    
    return {valid: obj.valid,
            errors: errors,
            field: $this[0]};
  };

  /* attach methods for field prototype to avoid duplication of the actual function object. */
  Field.prototype = {
    /* method used to attach different type of events to the field object. */
    attach: function (event) {
      var obj = this;
      if (event == "change") {
        obj.field.bind("change", function () {
          return obj.validate();
        });
      }
      if (event == "keyup") {
        obj.field.bind("keyup", function(e) {
          return obj.validate();
        });
      }
    },

    /* method that runs validation on a field. */
    validate : function () {
      var res = $(this.field).validateField(this.errorHandler);
      $(this.field).trigger($.Event("validated", res));
    }
  };

  /***************************************************************************
   *
   * Jquery plugin methods
   *
   ***************************************************************************/
  $.fn.validation = function(errorHandler, msgMap) {
    if ($(this).length == 0) {
      return this;
    }
    var validator = new Form($(this), errorHandler, msgMap);
    $.data($(this)[0], 'validator', validator);

    return this;
  };

  $.fn.validate = function (errorHandler, msgMap) {
    var validator = $.data($(this)[0], 'validator');
    validator = new Form($(this), errorHandler, msgMap);
    $.data($(this)[0], "validator", validator);
    validator.validate();
    return validator.isValid();
  };
});

function showValidationErrors(errorList) {
  if (errorList.length > 0) {
    alert(errorList[0].msg);
  }
}

/***************************************************************************
 *
 * Form validation
 *
 ***************************************************************************/

$.fn.validateForm = function (errorHandler) {
  errorHandler = errorHandler || displayStatusErrorHandler;
  var errors = [];
  var result = $(this).validate(function (err, field) {
    errorHandler(err, field);
    if (err.length > 0) {
      errors.push({msg: _.pluck(err, "msg").join(""), field: field});
    }
  });

  var res = {};
  if (result) {
    return [];
  } else {
    return errors;
  }
};

function strongInvalid(msg) {
  return '<strong class="validation-message invalid">' + msg + "</strong>";
}

function displayStatusErrorHandler(errorList, validField) {
  if (validField) {
    var displayStatus = $(validField).findInParent("p.displayStatus");
    displayStatus.children().remove("span.invalid").remove("span.valid");
  }
  for (var i = 0; i < errorList.length; i++) {
    var error = errorList[i];
    var displayStatus = $(error.field).findInParent("p.displayStatus");
    displayStatus.children().remove("span.invalid").remove("span.valid");
    displayStatus.append('<span class="invalid">' + error.msg + '</span>');
  }
}
