$(document).ready(function () {

  var $pc_height = 365;
  
  var window_w                    = $(window).width();
  var window_h                    = $(window).height();
  //the main panel div
  var $pc_panel = $('#pc_panel');
  //the wrapper and the content divs
  var $pc_wrapper                 = $('#pc_wrapper');
  var $pc_content                 = $('#pc_content');
  //the slider / slider div
  var $pc_slider                  = $('#pc_slider');

  $("#footer").append($pc_panel);
  buildPanel();

  traceNames = _.pluck(__traces, "name");
  var count = 0;
  series = _.map(__traces, function (trace) {
    res = {name: trace.name,
           type: 'line',
           data: [{name: trace.name,
                   x: trace.startTime * 1000,
                   y: count},
                  [trace.endTime * 1000, count]]};
    count++;
    return res;
  });
  
  chart1 = new Highcharts.Chart({
    chart: {
      renderTo: 'debugChart',
      type:'scatter',
      height: $pc_height - 40,
      width: '800'
    },
    title: {
      text: 'Time graph'
    },
    xAxis: {
      minPadding: 0.2,
      maxPadding: 0.2,
      title: {
        text: "ms"
      }
    },

    yAxis: {
      min : -1,
      max : count,
      showFirstLabel: false,
      showLastLabel: false,
      categories: traceNames,
      title: {
        text: 'Traces'
      }
    },

    series: series,

    plotOptions: {
      series: {lineWidth: 1}
    }
  });
  
  function buildPanel() {
    $pc_panel.css({'height': window_h + 'px'});
    hidePanel();
    /*
    $pc_panel.css({
      'right': -window_w + 'px',
      'top': window_h - $pc_height + 'px'
    }).show();
    */
  }

  function hidePanel() {
    $pc_panel.css({
      'right': -window_w + 'px',
      'top': window_h - 20 + 'px'
    }).show();
    $pc_panel.find('.collapse').addClass('expand').removeClass('collapse');
  }

  $(window).bind('resize', function () {
    window_w = $(window).width();
    window_h = $(window).height();
    buildPanel();
  });

  $pc_panel.find('.expand').bind('click', function() {
    var $this = $(this);
    $pc_wrapper.hide();
    $pc_panel.stop().animate({'top': window_h - $pc_height + 'px'}, 500, function() {
      $pc_wrapper.show();
      $this.addClass('collapse').removeClass('expand');
    });
  });

  $pc_panel.find('.collapse').live('click', function() {
    var $this = $(this);
    $pc_wrapper.hide();
    $pc_panel.stop().animate({'top': window_h - 20 + 'px'}, 500, function() {
      $pc_wrapper.show();
      $this.addClass('expand').removeClass('collapse');
    });
  });

  $pc_panel.find('.close').bind('click', function() {
    $pc_panel.remove();
  });
  
  $pc_panel.stop().animate({'right':'0px'},300);
});