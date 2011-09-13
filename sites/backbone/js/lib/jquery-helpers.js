/*
 * Jquery helper functions
 *
 * (c) August 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

/**
 * Reset all password fields.
 **/
function clearPasswords() {
  $("input.password").val('').blur();
}

/**
 * Find closest element in parent form
 **/
$.fn.findInParent = function (selector) {
  var parentStatus = $(this).closest(selector);
  if (parentStatus.length == 0) {
    return $(this).parent().next(selector);
  } else {
    return parentStatus;
  }
};

/**
 * Find closest element in specific parent
 **/
$.fn.findInParentElt = function (parentSelector, selector) {
  var parents = $(this).parents(parentSelector);
  if (parents.length > 0) {
    return $(parents[0]).find(selector);
  } else {
    return $();
  }
};

/**
 * Replace all links with hash links
 **/
$.fn.replaceLinks = function () {
  if (SINGLE_PAGE_APP) {
    $(this).children('a')
      .each(function(idx, elt) {
        $(elt).attr("href", function (i, attr) {
          if (attr) {
            var res = attr.replace(tplBaseData.origSite, "#").replace(/\.php$/, "");
            return res;
          } else {
            return undefined;
          }
        });
      });
  }
};

/**
 * zebratable -> alternating row colors
 **/
$.fn.zebratable = function () {
  $('tr:visible:odd', this).css({'background-color': 'white'});
  $('tr:visible:even', this).css({'background-color': '#e9e9e9'});
};

/***************************************************************************
 *
 * Misc form helpers
 *
 ***************************************************************************/

/**
 * Fill a form out of a hash array.
 **/
$.fn.fillForm = function (data) {
  for (key in data) {
    $(this).find("[name=" + key + "]").val(data[key]);
  }
};

/**
 * Extract the value of all form fields specified in fields.
 **/
$.fn.getFormData = function (fields) {
  var res = {};
  for (var i = 0; i < fields.length; i++) {
    var field = fields[i];
    res[field] = $(this).find("[name=" + field + "]").val();
  }
  return res;
};

/**
 * Returns all the input fields of the form.
 **/
$.fn.getAllFormData = function () {
  var res = {};

  $(this).find("input, textarea, select, checkbox").each(function (idx, elt) {
    elt = $(elt);
    res[elt.attr("name")] = elt.val();
  });

  return res;
};

/***************************************************************************
 *
 * AJAX methods
 *
 ***************************************************************************/

jQuery.extend({
  postJSON: function( url, data, callback) {
    return jQuery.ajax({
      url: url,
      data: JSON.stringify(data),
      dataType: "json",
      type: "POST",
      success: callback});
  },

  putJSON: function (url, data, callback) {
    return jQuery.ajax({
      url: url,
      data: JSON.stringify(data),
      dataType: "json",
      type: "PUT",
      success: callback});
  },

  deleteJSON: function (url, callback) {
    return jQuery.ajax({
      url: url,
      dataType: "json",
      type: "DELETE",
      success: callback});
  }
});

/**
 * Load cached ajax resources, if a callback is passed, asynchronously, else synchronously.
 **/
loadCachedAjax = (function () {
  var cache = {};

  return function (url, callback) {
    if (callback) {
      if (cache[url] !== undefined) {
        callback(cache[url]);
      } else {
        $.ajax(url,
               {
                 success:
                 function (data) {
                   cache[url] = data;
                   callback(data);
                 }
               });
      }
      return undefined;
    } else {
      if (cache[url] != undefined) {
        return cache[url];
      }
      $.ajax(url,
             {
               async: false,
               success: function (data) {
                 cache[url] = data;
               }
             });
      return cache[url];
    }
  };
})();

/***************************************************************************
 *
 * Autocomplete fields
 *
 ***************************************************************************/

$.fn.autocompleteRest = function (url) {
  var that = this;
  return this.autocomplete({
    source: function (req, response) {
      $.ajax({url: getSiteUrl(url + req.term),
              dataType: "json",
              success: function (data, ts, jq) { response(data); },
              error: function () { response([]); }});
    }
  });
};

$.fn.autocompleteCachedRest = function (url) {
  var that = this;
  loadCachedAjax(getSiteUrl(url),
                 function (data) {
                   $(that).autocomplete({source: data});
                 });
  return this;
};

$.fn.autocompleteState = function () {
  var that = this;
  loadCachedAjax(getSiteUrl("rest/app/states"),
                 function (data) {
                   $(that).autocomplete({source: _.pluck(data, "stateName")});
                 });
  return this;
};

/***************************************************************************
 *
 * Input Default fields handling
 *
 ***************************************************************************/

var origJqueryVal = $.fn.val;

$.fn.origVal = origJqueryVal;

/**
 * Make an input element reset dynamically to a default value.
 **/
$.fn.setDefaultInput = function (val) {
  if ($(this).origVal() == "") {
    $(this).origVal(val);
  }
};

$.fn.makeInputDefault = function () {
  this.each(function () {
    if ($(this).data("defaultValue")) {
      /* avoid double input default handling */
      return;
    }
    var defaultVal = $(this).attr("default");
    if ((defaultVal == undefined) || (defaultVal === "")) {
      defaultVal = "";
    }
    $(this).data("defaultValue", defaultVal);
    $(this)
      .click(function() { if ($(this).origVal() == defaultVal) {
        $(this).origVal('');
      } else {
        $(this).select();
      }})
      .blur(function() { $(this).setDefaultInput(defaultVal); }).blur();
  });
};

$.fn.resetInputDefault = function () {
  this.each(function () {
    if ($(this).origVal() == $(this).data("defaultValue")) {
      $(this).origVal("");
    }
  });
};

$.fn.val = function (value) {
  if (value == undefined) {
    var origVal = this.origVal();
    var defaultValue = this.data("defaultValue");
    if (defaultValue && (defaultValue == origVal)) {
      return "";
    } else {
      return origVal;
    }
  } else {
    return this.origVal(value);
  }
};

/***************************************************************************
 *
 * Selector for all text fields
 *
 ***************************************************************************/
$.extend($.expr[':'],
         {
           "text-field": function (a) {
             var $this = $(a);
             return ($this.is("input") && ($.inArray($this.attr("type"),
                                                     ["text", "password", "email", "url", "number", "search"]) != -1))
               || $this.is("textarea");
           }
         });
$().map
/***************************************************************************
 *
 * Input toggles. Previous element is replaced with input box when clicked
 *
 ***************************************************************************/

/**
 * Update the previous element, trigger a toggle_changed event on the input field,
 * and hide it again.
 **/
$.fn.updateToggle = function () {
  for (var i = 0, j = this.length; i < j; i++) {
    var $this = $(this[i]);
    var $prev;
    if (!($prev = $this.data("inputToggled"))) {
      continue;
    }
    
    /* if it's a select list, use the text value of the selected option instead of the value. */
    var newVal = $this.val();
    if ($this.is("select")) {
      newVal = $this.find("option:selected").text();
    }
    $this.hide();
    $prev.text(newVal).show();
    $this.trigger($.Event("toggle_changed",
                          { val: newVal }));
  }
  return this;
};

/**
 * Revert the input value to the default text, and hide it again.
 **/
$.fn.cancelToggle = function () {
  for (var i = 0, j = this.length; i < j; i++) {
    var $this = $(this[i]);
    var $prev;
    
    if (!($prev = $this.data("inputToggled"))) {
      continue;
    }
    $this.hide();
    $prev.show();
    if ($this.is("select")) {
      $this.selectOption($prev.text());
    } else {
      $this.val($prev.text());
    }
  }
  return this;
};

$.fn.selectToggle = function () {
  for (var i = 0, j = this.length; i < j; i++) {
    var $this = $(this[i]);
    var $prev;
    
    if (!($prev = $this.data("inputToggled"))) {
      continue;
    }
    
    /* hide other input toggles */
    $(".inputToggle").not($this).cancelToggle();
    $prev.hide();
    /* show the following input field */
    $this.show().select().focus();
  }
  return this;
};


/**
 * Make an element to an input toggle. It actually has to have the class inputToggle.
 **/
$.fn.makeInputToggle = function () {
  this.each(function () {
    var $this = $(this);
    if ($this.data("inputToggled")) {
      /* avoid double input toggle handling */
      return;
    }

    var $prev = $this.prev();
    /* click on the previous text span or element */
    $this.hide();

    $prev.wrap('<a href="#"/></a>').click(function () {
      $this.selectToggle();
      return false;
    });

    /* cancel editing when losing focus */
    $this.focusout(function () {
      $this.cancelToggle();
      return false;
    });
    
    if ($this.is(":text-field")) {
      /* if input is a text input, check for enter/escape or tab key */
      $this.keydown(function (l) {
        switch (l.keyCode) {
        case 13: // enter, save the value
          $this.updateToggle();
          return false;

        case 27: // escape, revert the value
          $this.cancelToggle();
          return false;
        }

        return true;;
      });
    } 

    $this.change(function () {
      $this.updateToggle();
    });

    /* tab, cycle through inputToggle fields (shift+tab for invert cycle) */
    $this.keydown(function (l) {
      if (l.keyCode == 9) {
        $this.updateToggle();
        var idx = $(".inputToggle").index($this) + (l.shiftKey ? -1 : 1);
        var len = $(".inputToggle").length;
        if (idx >= len) {
          idx = 0;
        } else if (idx < 0) {
          idx = len - 1;
        }
        $(".inputToggle:eq(" + idx + ")").selectToggle();
        return false;
      }
      return true;
    });
    
    $this.data("inputToggled", $prev);
  });
};

/***************************************************************************
 *
 * post render select of options
 *
 ***************************************************************************/

$.fn.selectOption = function (text) {
  $(this).each(function () {
    var $select = $(this);
    if (!$select.is("select")) {
      return;
    }
    $select.find("option").each(function () {
      if (($(this).text() === text) ||
          ($(this).val() === text)) {
        $select.val($(this).val());
      }
    });
  });

  return this;
};

(function ($) {
  $.fn.postSelect = function () {
    this.each(function () {
      var $select = $(this);
      var selectValue;
      if (selectValue = $select.attr("selectValue")) {
        $select.val(selectValue);
        $select.removeAttr("selectValue");
      } else if (selectValue = $select.attr("selectName")) {
        $select.find("option").each(function () {
          if ($(this).text() === selectValue) {
            $select.val($(this).val());
            $select.removeAttr("selectName");
            return false;
          }
        });
      }
    });

    return this;
  }
})(jQuery);
