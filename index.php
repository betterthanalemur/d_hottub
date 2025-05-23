<?php  
  
// Load DB credentials from JSON file in home directory
$db_cfg_path = getenv("HOME") . '/.d_hottub_db.json';
if (!file_exists($db_cfg_path)) {
    // Try Windows home directory
    $db_cfg_path = getenv("HOMEDRIVE") . getenv("HOMEPATH") . '\\.d_hottub_db.json';
}
$db_cfg_json = file_get_contents($db_cfg_path);
$db_cfg = json_decode($db_cfg_json, true);

$username = $db_cfg['user'];
$password = $db_cfg['password'];
$database = $db_cfg['database'];
$host = isset($db_cfg['host']) ? $db_cfg['host'] : '127.0.0.1';

$link = mysqli_connect($host, $username, $password, $database);
@mysqli_select_db($link, $database) or die("Unable to make it happen Cap'n");

$query = "SELECT unix_timestamp(entry_time)*1000, temperature FROM tub where entry_time > date_add(now(),interval -4 day)";
$result = mysqli_query($link, $query);
$dateTemp = array();
$index = 0;
while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
{
    $dateTemp[$index] = $row;
    $index++;
}

$query = "SELECT unix_timestamp(entry_time)*1000, temperature FROM ambient where entry_time > date_add(now(),interval -4 day)";
$result = mysqli_query($link, $query);
$dateTemp2 = array();
$index = 0;
while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
{
    $dateTemp2[$index] = $row;
    $index++;
}

$query = "SELECT unix_timestamp(entry_time)*1000, pressure FROM ambient where entry_time > date_add(now(),interval -4 day)";
$result = mysqli_query($link, $query);
$dateTemp3 = array();
$index = 0;
while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
{
    $dateTemp3[$index] = $row;
    $index++;
}

$query = "SELECT unix_timestamp(entry_time)*1000, humidity FROM ambient where entry_time > date_add(now(),interval -4 day)";
$result = mysqli_query($link, $query);
$dateTemp4 = array();
$index = 0;
while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
{
    $dateTemp4[$index] = $row;
    $index++;
}
//echo json_encode($dateTemp, JSON_NUMERIC_CHECK);  

$sql = "SELECT temperature from tub order by entry_number desc limit 1";
$tubtemp =  mysqli_query($link, $sql)->fetch_object()->temperature;
$sql = "SELECT entry_time from tub order by entry_number desc limit 1";
$tublastRead = mysqli_query($link, $sql)->fetch_object()->entry_time;

mysqli_close($link);  
  
?>  
  
<!DOCTYPE html>  
<html>  
<head>  
<title>TubTemp</title>  
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">  
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>  
<script src="https://code.highcharts.com/highcharts.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.13/moment-timezone-with-data-2012-2022.min.js"></script>  
  
</head>  
<body>  
<script type="text/javascript">  
$(function () {  
  
$('#container').highcharts({  
chart: {  
type: 'line'  
},  
time: {  
timezone: 'America/Los_Angeles'  
},  
title: {  
text: 'Tub Temperature vs Time'  
},  
xAxis: {  
title: {  
text: 'Time'  
},  
type: 'datetime',  
labels: {
            autoRotation: [-10, -20, -30, -40, -50, -60, -70, -80, -90]
        }
},  
yAxis: {  
title: {  
text: 'Tub Temperature'  
}  
},  
series: [{  
name: 'F',  
data: <?php echo json_encode($dateTemp, JSON_NUMERIC_CHECK);?>  
}]  
});  
});  


$(function () {

$('#container2').highcharts({
chart: {
type: 'line'
},
time: {
timezone: 'America/Los_Angeles'
},
title: {
text: 'Ambient Temperature vs Time'
},
xAxis: {
title: {
text: 'Time'
},
type: 'datetime',
labels: {
            autoRotation: [-10, -20, -30, -40, -50, -60, -70, -80, -90]
        }
},
yAxis: {
title: {
text: 'Ambient Temperature'
}
},
series: [{
name: 'F',
data: <?php echo json_encode($dateTemp2, JSON_NUMERIC_CHECK);?>
}]
});
});  

$(function () {
$('#container3').highcharts({
chart: {
type: 'line'
},
time: {
timezone: 'America/Los_Angeles'
},
title: {
text: 'Ambient Pressure vs Time'
},
xAxis: {
title: {
text: 'Time'
},
type: 'datetime',
labels: {
            autoRotation: [-10, -20, -30, -40, -50, -60, -70, -80, -90]
        }
},
yAxis: {
title: {
text: 'Ambient Pressure'
}
},
series: [{
name: 'mBar',
data: <?php echo json_encode($dateTemp3, JSON_NUMERIC_CHECK);?>
}]
});
});

$(function () {
$('#container4').highcharts({
chart: {
type: 'line'
},
time: {
timezone: 'America/Los_Angeles'
},
title: {
text: 'Ambient Humidity vs Time'
},
xAxis: {
title: {
text: 'Time'
},
type: 'datetime',
labels: {
            autoRotation: [-10, -20, -30, -40, -50, -60, -70, -80, -90]
        }
},
yAxis: {
title: {
text: 'Ambient Humidity'
}
},
series: [{
name: 'Percent',
data: <?php echo json_encode($dateTemp4, JSON_NUMERIC_CHECK);?>
}]
});
});

</script>  
<script src="charts/js/highcharts.js"></script>  
<script src="charts/js/modules/exporting.js"></script>  
  
<div class="container">  
<br/>  
<h2 class="text-center">Hot Tub - Temp vs. Time</h2>  
<div class="row">  
<div class="col-md-10 col-md-offset-1">  
<div class="panel panel-default">  
<div class="panel-heading">Current Temp Is <?php echo json_encode($tubtemp, JSON_NUMERIC_CHECK);?> at <?php echo substr(json_encode($tublastRead, JSON_NUMERIC_CHECK),1,-1);?></div> 
<div class="panel-body">  
<div id="container"></div>  
<div id="container2"></div>
<div id="container3"></div>
<div id="container4"></div>
</div>  
</div>  
</div>  
</div>  
</div>  
  
</body>  
</html>
