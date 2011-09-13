var Testimonial = Backbone.Model.extend({
  defaults: {
  },

  urlRoot: site + "rest/testimonials",

  initialize: function () {
    _.bindAll(this);
  },

});

var Testimonials = Backbone.Collection.extend({
  model: Testimonial,
  url: site + "rest/testimonials"
});
