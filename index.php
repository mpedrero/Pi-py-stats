<?php
exec('/usr/bin/python client.py 192.168.1.237 8002', $return);

#$contents = file_get_contents("/var/www/temp/blabla.json"); 
$str_data = file_get_contents($return[0]);
$data = json_decode('{'.$str_data.'}',true);


unlink($return[0]);

?>
<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['date',	'down', 'up'],
          <?php
	          foreach($data as $fecha => $datos)
	          {
	          	echo "['".$fecha."',".$datos['network_down'].", ".$datos['network_up']."],";
	          	
	          }
          ?>
        ]);

        var options = {
          title: 'Network',
          hAxis: {title: 'Network',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['date',	'cpu_use'],
          <?php
	          foreach($data as $fecha => $datos)
	          {
	          	echo "['".$fecha."',".$datos['cpu_use']."],";
	          	
	          }
          ?>
        ]);

        var options = {
          title: 'cpu_use',
          hAxis: {title: 'cpu_use',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div-2'));
        chart.draw(data, options);
      }
    </script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['date',	'swap_used'],
          <?php
	          foreach($data as $fecha => $datos)
	          {
	          	echo "['".$fecha."',".$datos['swap_used']."],";
	          	
	          }
          ?>
        ]);

        var options = {
          title: 'swap',
          hAxis: {title: 'swap',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div-3'));
        chart.draw(data, options);
      }
    </script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['date',	'cache', 'buffer', 'used'],
          <?php
	          foreach($data as $fecha => $datos)
	          {
	          	echo "['".$fecha."',".$datos['cache'].",".$datos['buffer'].",".$datos['used']."],";
	          	
	          }
          ?>
        ]);

        var options = {
          title: 'ram',
          hAxis: {title: 'ram',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div-4'));
        chart.draw(data, options);
      }
    </script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['date',	'/', '/home'],
          <?php
	          foreach($data as $fecha => $datos)
	          {
	          	echo "['".$fecha."',".$datos['hdd_use_'].",".$datos['hdd_use_home']."],";
	          	
	          }
          ?>
        ]);

        var options = {
          title: 'hdd',
          hAxis: {title: 'hdd',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div-5'));
        chart.draw(data, options);
      }
    </script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['date',	'temp'],
          <?php
	          foreach($data as $fecha => $datos)
	          {
	          	echo "['".$fecha."',".$datos['temp']."],";
	          	
	          }
          ?>
        ]);

        var options = {
          title: 'temperature',
          hAxis: {title: 'temperature',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div-6'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="chart_div" style="width: 620px; height: 400px;float: left"></div>
    <div id="chart_div-2" style="width: 620px; height: 400px;float: left"></div>
    <div id="chart_div-3" style="width: 620px; height: 400px;float: left"></div>
    <div id="chart_div-4" style="width: 620px; height: 400px;float: left"></div>
    <div id="chart_div-5" style="width: 620px; height: 400px;float: left"></div>
    <div id="chart_div-6" style="width: 620px; height: 400px;float: left"></div>
  </body>
</html>