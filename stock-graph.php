<?php
include('../simple-cache/cache.inc.php');

/*
	Add Stock Quote Graphs to WordPress With Shortcode
	http://www.beliefmedia.com/stock-quote-graph-wordpress
*/

/* Basic usage */
//echo beliefmedia_alphavantage_quotes($symbol = 'F');

function beliefmedia_alphavantage_quotes($symbol, $args = '') {

  $atts = array(
    'width' => '600',
    'height' => '410',
    'time' => '2',
    'number' => '90',
    'size' => 'compac', /* compac or full */
    'interval' => '60', /* 1min, 5min, 15min, 30min, 60min */
    'apikey' => 'X2YNJWH7QSF0OY2C',
    'cache' => 3600
  );

 /* Merge $args with $atts */
 $atts = (empty($args)) ? $atts : array_merge($atts, $args);

 $transient = 'alpha_vantage_stock_' . md5(serialize($atts) . $symbol);
 $cachedposts = beliefmedia_get_transient($transient, $atts['cache']);

 if ($cachedposts !== false) {
  return $cachedposts;

 } else {

    switch ($atts['time']) {
        case 1:
            $series = 'TIME_SERIES_INTRADAY';
            $series_name = 'Time Series (' . $atts['interval'] . 'min)';
            break;
        case 2:
            $series = 'TIME_SERIES_DAILY';
            $series_name = 'Time Series (Daily)';
            break;
        case 3:
            $series = 'TIME_SERIES_DAILY_ADJUSTED';
            $series_name = 'Time Series (Daily)';
            break;
        case 4:
            $series = 'TIME_SERIES_WEEKLY';
            $series_name = 'Weekly Time Series';
            break;
        case 5:
            $series = 'TIME_SERIES_MONTHLY';
            $series_name = 'Monthly Time Series';
            break;
        default:
            $series = 'Time Series (Daily)';
            break;
    }

    /* Get Stock data */
    $data = @file_get_contents('https://www.alphavantage.co/query?function=' . $series . '&symbol=' . $symbol . '&interval=' . $atts['interval'] . 'min&apikey=' . $atts['apikey'] . '&interval=' . $atts['interval'] . 'min&outputsize=' . $atts['size']);
    if ($data === false) return '<p>Data currently unavailable.</p>';
    $data = json_decode($data, true);
    $data = $data[$series_name];

    /* Return portion of results & reverse */ 
    if ($atts['number'] != '') $data = array_slice($data, 0, $atts['number'], true);
    $data = array_reverse($data, true);

    foreach ($data AS $key => $value) {
      $chart .= ',[new Date(' . str_replace(array('-', ' ', ':'), ',', $key) . '), ' . $value['4. close'] . ']';
    }

    $chart = ltrim($chart, ',');

   /* Build chart with fresh data */
   $return = "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <script type='text/javascript'>
      google.charts.load('current', {packages: ['corechart', 'line']});
      google.charts.setOnLoadCallback(drawTrendlines);

    function drawTrendlines() {
      var data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        data.addColumn('number', 'Close');

      data.addRows([
        $chart
      ]);

      var options = {
        hAxis: {
          title: 'Date'
        },
        backgroundColor: 'transparent',
        vAxis: {
          title: 'Stock Price'
        },
        colors: ['#AB0D06'],
        trendlines: {
          // 0: {type: 'exponential', color: '#333', opacity: 1},
          // 1: {type: 'linear', color: '#111', opacity: .3}
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_div_$interval'));
      chart.draw(data, options);
    }
    </script>";
 
    /* Chart container */
    $return .= '<div id="chart_div_' . $interval . '" style="width: ' . $atts['width'] . 'px; height: ' . $atts['height'] . 'px;"></div>';

   /* Set transient chart data */
   beliefmedia_set_transient($transient, $return, $atts['cache']);
   return $return;
 }
}

