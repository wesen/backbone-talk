/*
 * Javascript general methods for Golden app
 *
 * (c) May 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

/***************************************************************************
 *
 * Helpers
 *
 ***************************************************************************/

/**
 * Debug in other browsers than safari
 **/
function debugLog(log_txt, name) {
  if (window.console != undefined) {
    if (name) {
      console.log(name +": ");
    }
    console.log(log_txt);
  }
}

/**
 * Round a floating number to 2 decimal places.
 **/
function roundVal(val){
  var dec = 2;
  var result = Math.round(val*Math.pow(10,dec))/Math.pow(10,dec);
  return result;
}

/**
 * Add commas to a number.
 **/
function addCommas(nStr) {
  nStr = roundVal(nStr);
  nStr += '';
  x = nStr.split('.');
  x1 = x[0];
  x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
}

/**
 * downcase first character
 **/
String.prototype.lcfirst = function () {
  return this.charAt(0).toLowerCase() + this.slice(1);
};

/**
 * upcase first character
 **/
String.prototype.ucfirst = function () {
  return this.charAt(0).toUpperCase() + this.slice(1);
};

/**
 * Returns true if number is a positive number.
 **/
function isPositiveNumber(number) {
  if (isNaN(number)) {
    return false;
  }
  var a = Number(number);
  if (a < 0) {
    return false;
  }
  return true;
}

function redirectTo(url, params) {
  if (!params) {
    params = {};
  }
  return (window.location = encodeURI($.param.querystring(url, params)));
}

/***************************************************************************
 *
 * Backbone helpers
 *
 ***************************************************************************/

/**
 * Goto a subpage in backbone app
 **/
function gotoHash(url) {
  window.location.hash = '#' + encodeURI(url);
}

/* XXX this should be replace with extend */
function mergeMixin(view, mixin) {
  _.defaults(view.prototype, mixin);
  _.defaults(view.prototype.events, mixin.events);
  if (mixin.initialize !== undefined) {
    var oldInitialize = view.prototype.initialize;
    view.prototype.initialize = function () { mixin.initialize.apply(this); oldInitialize.apply(this); };
  }
}

/***************************************************************************
 *
 * URL functions for GEC site
 *
 ***************************************************************************/

/**
 * Get a page URL (for example for AJAX calls)
 */
function getSiteUrl(page, params) {
  var strHostName = window.location.hostname;
  var url = window.location.protocol + "//" + strHostName + site + page;
  if (params) {
    var first = true;
    for (var key in params) {
      if (params[key] != undefined) {
        url += first ? "?" : "&";
        url += key + "=" + params[key];
        first = false;
      }
    }
  }
  return url;
}

/**
 * Return the name of the current page.
 **/
function getCurrentPageName() {
  return getUrlPageName(window.location.pathname);
}

/**
 * Return the page name of an url.
 **/
function getUrlPageName(url) {
  var paths = url.split("/");
  return paths[paths.length - 1];
}

/***************************************************************************
 *
 * AJAX methods
 *
 ***************************************************************************/

/**
 * Load ICanHaz templates dynamically
 **/
function loadTemplates(templates, callback) {
  var dfd = $.Deferred();

  templates = _.reject(templates, function (template) {
    var name = template.replace(/\//g, "_");
    return ich[name] !== undefined;
  });
  if (templates.length == 0) {
    dfd.resolve();
  } else {
    $.get(getSiteUrl("templates/" + templates.join(",")))
      .then(function (html) {
        $("body").append(html);
        ich.allowReload = true;
        ich.grabTemplates();
        dfd.resolve();
      })
      .fail(function () {
        dfd.reject();
      });
  }
  return dfd.promise();
}

/**
 * render a template, replace links, and call inputDefault methods.
 **/
function renderTemplate(requiredTemplates, templateName, json) {
  var dfd = $.Deferred();

  loadTemplates(requiredTemplates)
    .then(function () {
      var data = $.extend(basePageData, tplBaseData,
                          {requestUri: window.location.hash },
                          json);
      var html = ich[templateName](data);
      dfd.resolve(html)
      $(html).replaceLinks();
      $(".inputDefault").makeInputDefault();
    })
    .fail(function () { dfd.reject() });

  return dfd.promise();
}

/**
 * Download a file using a hidden iframe.
 **/
var downloadURL = function(url) {
  var iframe;
  iframe = document.getElementById("hiddenDownloader");
  if (iframe === null) {
    iframe = document.createElement('iframe');
    iframe.id = "hiddenDownloader";
    iframe.style.visibility = 'hidden';
    document.body.appendChild(iframe);
  }
  iframe.src = url;
}

/**
 * Convert a mysql timestamp to javascript, taking the timezone into account.
 **/
function mysqlToDate(timestamp) {
  var months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  var regex = /^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])T([0-2][0-9]):([0-5][0-9]):([0-5][0-9])(.*)/;
  var parts = timestamp.replace(regex,"$2/ $3, $1 $4:$5:$6 GMT$7").split('/');
  var str = months[new Number(parts[0])] + parts[1];
  return new Date(str);
}

Date.prototype.toMysql = function () {
  return this.getUTCFullYear() + "-" + this.getUTCMonth().pad(2) + "-" + this.getUTCDate().pad(2) + "T" +
    this.getUTCHours().pad(2) + ":" + this.getUTCMinutes().pad(2) + ":" + this.getUTCSeconds().pad(2);
};

Number.prototype.pad = function (n) {
  var str = this.toString();
  for (; str.length < n; str = '0' + str)
    ;

  return str;
};

Number.prototype.addCommas = function () {
  return this.toString().addCommas();
};

String.prototype.addCommas = function () {
  var nStr = this;
  nStr += '';
  var x = nStr.split('.');
  var x1 = x[0];
  var x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
};

Number.prototype.toDollars = function () {
  return this.toFixed(2).addCommas();
};
