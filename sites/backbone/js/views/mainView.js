App.Views.mainView = App.Views.templateView.extend({
  el: $("body"),

  templates: ["main"],
  mainTemplate: ["main"],

  events: {
  },

  initialize: function () {
    App.Views.templateView.prototype.initialize.call(this);
  },

  render: function () {
    $('#slider').loopedSlider({
      autoStart: 6000,
      restart: 5000
    });

    this.delegateEvents();
  }

});
