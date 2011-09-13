App.Views.blogView = App.Views.templateView.extend({
  el: $("body"),

  templates: ["blog", "footer", "header"],
  mainTemplate: "blog",

  events: {
    "toggle_changed input, textarea": "saveBlogPost"
  },

  initialize: function () {
    var that = this;
    App.Views.templateView.prototype.initialize.call(this);

    debugLog(this.options);

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

  saveBlogPost: function (ev) {
    var $target = $(ev.target);
    var $form = $target.parents("form:first");
    var id = $form.find("input[name=blogId]").val();
    var blog = App.Collections.blogs.get(id);
    var name = $target.attr("name");
    var val = ev.val;
    var data = {};
    data[name] = val;
    blog.set(data);
    blog.save();
    return false;
  },

  getJSON: function () {
    var blogs;

    if (category = this.options.category) {
      var category;
      blogs = App.Collections.blogs.filter(function (b) { debugLog(b.attributes.category, "cat");
                                                          var res = b.attributes.category === category;
                                                          debugLog(category, "cat2");
                                                          debugLog(res, "res");
                                                          return res;
                                                        });
      blogs = _.map(blogs, function (x) { return x.toJSON(); });
    } else {
      blogs = App.Collections.blogs.toJSON();
    }

    return {blogs: blogs,
            testimonials: App.Collections.testimonials.toJSON(),
            categories: App.Collections.blogs.getCategories()
           };
  },

  render: function () {
    $("body").addClass("normalpage");
    $(".inputToggle").makeInputToggle();

    $('#slider').loopedSlider({
      autoStart: 6000,
      restart: 5000
    });

    this.delegateEvents();
  }

});
