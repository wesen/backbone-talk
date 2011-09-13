/*
 * Javascript for the main page
 *
 * (c) April 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

var basePageData = {};

var SINGLE_PAGE_APP = false;

var App = {
  Views: {
    Member: {},
    Admin: {
      Elements: {
      },
      Popups: {
      }
    },
    Misc: {},
    Cart: {},
    Checkout: {}
  },
  Collections: {},
  Controllers: {},
  Data: {},

  /***************************************************************************
   *
   * Notifications
   *
   ***************************************************************************/

  notifyUser: function (type, data) {
    var templateName = "notification/" + type;
    loadTemplates([templateName])
      .then(function () {
        alert("notification for " + type + ": " + ich["notification_" + type](_.defaults(data, tplBaseData), true));
      });
  },

  notifyMessage: function (message) {
    App.notifyUser("message", {"msg": message});
  },

  notifyErrors: function (errors) {
    App.notifyUser("error", { "errors": errors });
  },

  redirect: function (url, params) {
    redirectTo(getSiteUrl(url), params);
  },


  /***************************************************************************
   *
   * JSON requests to REST server
   *
   ***************************************************************************/

  /**
   * Do a GET request to the app RestServer.
   **/
  GET: function (url, params) {
    var dfd = $.Deferred();
    $.getJSON(getSiteUrl(url, params))
      .then(function (res) { dfd.resolve(res); })
      .fail(function () { dfd.reject(["Network error"]); });
    return dfd.promise();

  },

  /**
   * Do a POST request to the app RestServer.
   **/
  POST: function (url, data) {
    var dfd = $.Deferred();
    $.postJSON(getSiteUrl(url), data)
      .then(function (res) {
        parseJSONResults(res, dfd);
      })
      .fail(function () {
        dfd.reject({status: "error",
                    errors: [{field: "network",
                              msg: "Network error"}]});
      });
    return dfd.promise();
  },

  /**
   * Do a PUT request to the app RestServer.
   **/
  PUT: function (url, data) {
    var dfd = $.Deferred();
    $.putJSON(getSiteUrl(url), data)
      .then(function (res) {
        parseJSONResults(res, dfd);
      })
      .fail(function () {
        dfd.reject({status: "error",
                    errors: [{field: "network",
                              msg: "Network error"}]});
      });
    return dfd.promise();
  },

  /**
   * Do a DELETE request to the app RestServer.
   **/
  DELETE: function (url) {
    var dfd = $.Deferred();
    $.deleteJSON(getSiteUrl(url))
      .then(function (res) {
        parseJSONResults(res, dfd);
      })
      .fail(function () {
        dfd.reject({status: "error",
                    errors: [{field: "network",
                              msg: "Network error"}]});
      });
    return dfd.promise();
  }
};


/**
 * Parse the JSON results, rejecting the dfd if status == error, resolving when status == success.
 **/
function parseJSONResults(res, dfd) {
  if (res && res.status == "error") {
    dfd.reject(res);
  } else if (res && res.status == "success") {
    dfd.resolve(res);
  } else {
    dfd.reject({status: "error",
                errors: [{field: "json",
                          msg: "Unknown JSON answer"}]});
  }
}

