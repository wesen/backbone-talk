/*
 * Main backbone app
 *
 * (c) September 2011
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

var SINGLE_PAGE_APP = true;
var PortfolioApp = Backbone.Router.extend({
  routes: {
    "": "index",
    "blog": "blog",
    "services": "services",
    "about": "about",
    "contact": "contact"
  },

  initialize: function () {
    tplBaseData.origSite = tplBaseData.site;
    _.bindAll(this);
    debugLog("main");
    App.Collections.testimonials = new Testimonials();
  },

  setView: function (view) {
    if (this.view) {
      this.view.remove();
      this.view.destroy();
      this.view = undefined;
    }

    this.view = view;
    this.view.renderTemplate();
  },

  index: function () {
    this.setView(new App.Views.mainView());
  },

  blog: function () {
  },

  services: function () {
  },

  about: function () {
  },

  contact: function () {
  }
});

$(function () {
  var app = new PortfolioApp();
  window.app = app;
  Backbone.history.start({pushState: true, root: "/backbone/"});
});
