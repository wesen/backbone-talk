var Blog = Backbone.Model.extend({
  defaults: {
  },

  urlRoot: site + "rest/blogs",

  initialize: function () {
    _.bindAll(this);
  },

});

var Blogs = Backbone.Collection.extend({
  model: Blog,
  url: site + "rest/blogs",

  getCategories: function () {
    return _.uniq(this.map(function (blog) { return blog.attributes.category; }));
  }

});
