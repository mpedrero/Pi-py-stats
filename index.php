<?php
#?ip=xx.xx.xx.xx&port=xxxx&from=0&to=10
$correct_all = False;
$options = array(
    'options' => array(
                      'min_range' => 0,
                      'max_range' => 10000,
                      )
);
if (filter_var($_GET["ip"], FILTER_VALIDATE_IP))
{
  if (filter_var($_GET["port"], FILTER_VALIDATE_INT, $options) !== FALSE)
  {
    $correct_all = True;
    if (isset($_GET["from"]))
    {
      if (filter_var($_GET["from"], FILTER_VALIDATE_INT) !== FALSE)
      {
        $from = $_GET["from"];
      }
    }
    else
    {
      $from = 0;
    }
    if (isset($_GET["to"]))
    {
      if (filter_var($_GET["to"], FILTER_VALIDATE_INT) !== FALSE)
      {
        $to = $_GET["to"];
      }
    }
    else
    {
      $to = 0;
    }
  }
}
if ($correct_all == True)
{
  exec('/usr/bin/python client.py '.$_GET["ip"].' '.$_GET["port"], $return);

  #$contents = file_get_contents("/var/www/temp/blabla.json"); 
  $str_data = file_get_contents($return[0]);
  $data = json_decode('{'.$str_data.'}',true);


  unlink($return[0]);
  $network = "";
  $cpu_use = "";
  $swap = "";
  $ram = "";
  $hdd = "";
  $temperature = "";
  $count = 0;
  if ($to == 0)
  {
    $to = count($data);
  }
  foreach($data as $fecha => $datos)
  {
    if ($fecha == "data")
    {
      $ram_ava = $datos['ava'];
      $swap_ava = $datos['swap_ava'];
    }
    else
    {
      if ($from < $count and $to > $count)
      {
        $network .= "['".$fecha."',".$datos['network_down'].", ".$datos['network_up'].",".$datos['network_avg_down'].", ".$datos['network_avg_up']."],";
        $cpu_use .= "['".$fecha."',".$datos['cpu_use']."],";
        $swap .= "['".$fecha."',".$datos['swap_used']."],";
        $ram .= "['".$fecha."',".$datos['cache'].",".$datos['buffer'].",".$datos['used']."],";
        $hdd .= "['".$fecha."',".$datos['hdd_use_'].",".$datos['hdd_use_home']."],";
        $temperature .= "['".$fecha."',".$datos['temp']."],";
      }
    }
    $count ++;
  }
  ?>
  <html>
    <head>
      <script type="text/javascript" src="https://www.google.com/jsapi"></script>
      <script type="text/javascript">
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawChart);
        function drawChart() {
          var data = google.visualization.arrayToDataTable([
            ['date',  'down', 'up', 'avg_down', 'avg_up'],
            <?php
              echo $network;
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
            ['date',  'cpu_use'],
            <?php
              echo $cpu_use;
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
            ['date',  'swap_used'],
            <?php
              echo $swap;
            ?>
          ]);

          var options = {
            title: 'swap',
            hAxis: {title: 'swap: (max size: <?php echo $swap_ava;?>)',  titleTextStyle: {color: 'red'}}
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
            ['date',  'cache', 'buffer', 'used'],
            <?php
              echo $ram;
            ?>
          ]);

          var options = {
            title: 'ram',
            hAxis: {title: 'ram: (max size: <?php echo $ram_ava;?>)',  titleTextStyle: {color: 'red'}}
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
            ['date',  '/', '/home'],
            <?php
              echo $hdd;
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
            ['date',  'temp'],
            <?php
              echo $temperature;
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
      <div style="position: relative; clear: both;text-align: center"><h1>Pi-py-stats</h1></div>
      <div style="position: relative; clear: both;text-align: center">Data receive: <?php echo count($data);?></div>
      <div id="chart_div" style="width: 620px; height: 400px;float: left"></div>
      <div id="chart_div-2" style="width: 620px; height: 400px;float: left"></div>
      <div id="chart_div-3" style="width: 620px; height: 400px;float: left"></div>
      <div id="chart_div-4" style="width: 620px; height: 400px;float: left"></div>
      <div id="chart_div-5" style="width: 620px; height: 400px;float: left"></div>
      <div id="chart_div-6" style="width: 620px; height: 400px;float: left"></div>
    </body>
  </html>
<?php
}
?>