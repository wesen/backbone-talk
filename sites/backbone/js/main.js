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
    "index": "index",
    "blog": "blog",
    "blog/category/:category": "blog",
    "services": "services",
    "about": "about",
    "contact": "contact"
  },

  initialize: function () {
    tplBaseData.origSite = tplBaseData.site;
    _.bindAll(this);

    App.Collections.testimonials = new Testimonials();
    App.Collections.blogs = new Blogs();
  },

  setView: function (view) {
    if (this.view) {
//      this.view.remove();
      this.view.destroy();
      this.view = undefined;
    }

    this.view = view;
  },

  index: function () {
    this.setView(new App.Views.mainView({el: $("body")}));
  },

  blog: function (category) {
    this.setView(new App.Views.blogView({el: $("body"),
                                         category: category}));
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
  Backbone.history.start({root: "/backbone/"});
});


function populateTestimonials() {
  var t = App.Collections.testimonials.create(
    {text: "Wicked site",
     author: "John Henry",
     city: "Berlin"});
  t = App.Collections.testimonials.create(
    {text: "Crazy Stuff",
     author: "Macademia Wilbert",
     city: "Cuxhaven"});
  t = App.Collections.testimonials.create(
    {text: "I barely managed to keep it together",
     author: "Quincy Van De Waals",
     city: "Amsterdam"});
}

function populateBlogs() {
  var b = App.Collections.blogs.create(
    {title: "Today I did something",
     category: "Hardcore",
     text: "And it was actually quite fine. No really, you wouldn't believe. It was fun. Barely. But still.",
     date: new Date()
    });
  b = App.Collections.blogs.create(
    {title: "Yesterday I did something too",
     category: "Web Design",
     text: "Add a model (or an array of models) to the collection. Fires an 'add' event, which you can pass {silent: true} to suppress. If a model property is defined, you may also pass raw attributes objects, and have them be vivified as instances of the model. Pass {at: index} to splice the model into the collection at the specified index.",
     date: new Date()});
}
