App.Views.mainView = App.Views.templateView.extend({
  el: $("body"),

  templates: ["main", "footer", "header"],
  mainTemplate: "main",

  events: {
  },

  initialize: function () {
    var that = this;
    App.Views.templateView.prototype.initialize.call(this);
    App.Collections.testimonials.fetch().pipe(function () {
      return App.Collections.blogs.fetch();
    })
      .then(function () {
        App.Collections.testimonials.bind("all", that.refresh);
        App.Collections.blogs.bind("all", that.refresh);
        that.refresh();
      });


  },

  destroy: function () {
    App.Collections.testimonials.unbind("all", this.refresh);
    App.Collections.blogs.unbind("all", this.refresh);
  },

  getJSON: function () {
    return {testimonials: App.Collections.testimonials.toJSON(),
            blogs: App.Collections.blogs.toJSON() };
  },

  render: function () {
    debugLog("render");
    $("body").removeClass("normalpage");

    $('#slider').loopedSlider({
      autoStart: 6000,
      restart: 5000
    });

    this.delegateEvents();
  }

});
