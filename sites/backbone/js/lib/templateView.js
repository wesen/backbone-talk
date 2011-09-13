/**
 * View bound to a model with some templates.
 **/
App.Views.templateView = Backbone.View.extend({
  templates: [],
  mainTemplate: undefined,

  sourceModel: null,

  initialize: function () {
    _.bindAll(this);

    if ((typeof this.sourceModel) == "function") {
      // resolving delayed source model
      this.sourceModel = this.sourceModel();
    }
    // bind change to the sourceModel
    if (this.sourceModel) {
      if (this.sourceModel instanceof Backbone.Collection) {
        this.sourceModel.bind("reset", this.refresh);
        this.sourceModel.bind("change", this.refresh);
        this.sourceModel.bind("add", this.refresh);
        this.sourceModel.bind("delete", this.refresh);
      } else {
        this.sourceModel.bind("change", this.refresh);
      }
    }
  },

  destroy: function () {
    if (this.sourceModel)
      if (this.sourceModel instanceof Backbone.Collection) {
        this.sourceModel.unbind("reset", this.refresh);
        this.sourceModel.unbind("change", this.refresh);
        this.sourceModel.unbind("add", this.refresh);
        this.sourceModel.unbind("delete", this.refresh);
      } else {
        this.sourceModel.unbind("change", this.refresh);
      }
  },

  refresh: function () {
    this.renderTemplate();
  },

  renderTemplate: function (ev) {
    if (this.rendering) {
      return;
    }
    this.rendering = true;
    var that = this;
    var data = this.getJSON();
    return renderTemplate(this.templates,
                          this.mainTemplate,
                          data)
      .then(function (html) {
        that.el.html(html);
        that.render();
        that.rendering = false;
      });
  },

  fancybox: function (elt, promise, options) {
    var that = this;

    options = options || {};
    elt.fancybox($.extend({}, 
                          {type: 'deferred',
                           promise: promise,
                           width: 'auto',
                           height: 'auto',
                           autoDimensions: true,
                           onCleanup: function () {
                             that.view.destroy();
                             delete that.view;
                           },
                           onComplete: function () {
                             that.view.delegateEvents();
                             $("#fancybox-content").css({width: "auto",
                                                         height: "auto"});
                             $.fancybox.resize();
                             $.fancybox.center();
                           },
                           autoDimensions: false
                          },
                         options));
  },

  /**
   * Return a hash for templating.
   **/
  getJSON: function () {
    if (this.sourceModel) {
      return this.sourceModel.toJSON();
    }
  },
});

var spinnerOpts = {
  lines: 10,
  length: 7,
  width: 3,
  radius: 3,
  trail: 40,
  speed: 1.0
};

$.fn.spin = function(opts) {
  this.each(function() {
    var $this = $(this),
    spinner = $this.data('spinner');

    if (spinner) spinner.stop();
    if (opts !== false) {
      opts = $.extend({color: $this.css('color')}, opts);
      spinner = new Spinner(opts).spin(this);
      $this.data('spinner', spinner);
    }
  });
  return this;
};
