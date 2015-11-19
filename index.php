<?php
require_once('/lib/LSPHP/LSPHP.inc.php');
require_once('/lib/LinnworksAPI/LinnworksAPI.inc.php');
session_start();
include('/html/header.php');
?>
<script type="text/javascript">
    var datefield=document.createElement("input")
    datefield.setAttribute("type", "date")
    if (datefield.type!="date"){ //if browser doesn't support input type="date", load files for jQuery UI Date Picker
        document.write('<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />\n')
        document.write('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"><\/script>\n')
        document.write('<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"><\/script>\n')
    }
</script>

<script>
if (datefield.type!="date"){ //if browser doesn't support input type="date", initialize date picker widget:
    jQuery(function($){ //on document.ready
        $('#sent').datepicker();
        $('#recieved').datepicker();
    })
}
</script>

<?php
function date_convert($old_date)
{
    $date_array = explode('/', $old_date);
    $new_date = $date_array[2] . '-' . $date_array[0] . '-' . $date_array[1];
    return $new_date;
}

$api = new LinnworksAPI\LinnworksAPI($_SESSION['username'], $_SESSION['password']);
$database = new LSPHP\DatabaseTable(
    "mysql.stcadmin.stcstores.co.uk",
    "seatontrading",
    "seatontrading",
    "Cosworth1",
    "shipping_audit"
);

if (isset($_POST['sent'])) {
    $sent = $_POST['sent'];
    $recieved = $_POST['recieved'];
    $courier = $_POST['courier'];
    $orderid = $_POST['order_id'];

    $sent = date_convert($sent);
    $recieved = date_convert($recieved);

    $insert_query = "INSERT INTO shipping_audit (sent, recieved, courier, order_id) VALUES ('{$sent}', '{$recieved}', '{$courier}', {$orderid});";
    $database -> insertQuery($insert_query);
    sleep(3);
}

$select_query = "SELECT * FROM shipping_audit;";
$all_records = $database->selectQuery($select_query);

echo "<div class='pagebox'>\n";
echo "\t<form method=post>\n";
echo "\t\t<table>\n";
echo "\t\t\t<tr>\n";
echo "\t\t\t\t<td><label for=sent >Sent: </label></td>\n";
echo "\t\t\t\t<td><input id=sent type='date' name=sent required /></td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t\t<tr>\n";
echo "\t\t\t\t<td><label for=recieved >Recieved: </label></td>\n";
echo "\t\t\t\t<td><input id=recieved type='date' name=recieved required /></td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t\t<tr>\n";
echo "\t\t\t\t<td><label for=courier >Courier: </label></td>\n";
echo "\t\t\t\t<td>\n";
echo "\t\t\t\t\t<select id=courier name=courier>\n";
$allPostageServices = $api->get_postage_service_names();
$ignoreFields = array(
    '24 UNASSIGNED',
    '48 UNASSIGNED',
    'Air Mail RM',
    'Default',
    'Manifested International Sign For'
);
$postageServices = array_diff($allPostageServices, $ignoreFields);
foreach ($postageServices as $method) {
    if (!(in_array($method, $ignoreFields))) {
        echo "\t\t\t\t\t\t<option value='" . $method . "' ";
        echo ">" . $method . "</option>" . $method . "\n";
    }
}
echo "\t\t\t\t\t</select>\n";
echo "\t\t\t\t</td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t\t<tr>\n";
echo "\t\t\t\t<td><label for=order_id >Order Id: </label></td>\n";
echo "\t\t\t\t<td><input name=order_id id=order_id required /></td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t\t<tr>\n";
echo "\t\t\t\t<td colspan=2 ><input type=submit value='Submit' /></td>\n";
echo "\t\t\t</tr>\n";
echo "\t\t</table>\n";
echo "\t</form>\n";
echo "\t<p>";
echo count($all_records);
echo " records stored.</p>\n";
echo "\t<table>\n";
$method_numbers = array();
foreach ($postageServices as $method) {
    $recordsForMethod = 0;
    foreach ($all_records as $record) {
        if ($record['courier'] == $method) {
            $recordsForMethod++;
        }
    }
    $method_numbers[$method] = $recordsForMethod;
}
arsort($method_numbers);
foreach ($method_numbers as $method => $number) {
    echo "\t\t<tr>\n";
    echo "\t\t\t<td class=align_right>";
    echo $method;
    echo "</td>\n";
    echo "\t\t\t<td><input value='";
    echo $number;
    echo "' size=3 readonly /></td>\n";
    echo "\t\t</tr>\n";
}
echo "\t</table>\n";
echo "\t<br />\n";
echo "\t<div class=charts style='text-align: center;'>\n";
shell_exec('python ' . $_SERVER['DOCUMENT_ROOT'] . '/shipping_audit/shipping_audit_chart.py');
$files = scandir($_SERVER['DOCUMENT_ROOT'] . '/shipping_audit/charts/');

foreach ($method_numbers as $method => $count) {
    if (in_array($method . '.png', $files)) {
        echo "\t<a href='charts/";
        echo $method;
        echo ".png' target=blank ><img style='margin: 10px;' class=chart src='charts/";
        echo $method;
        echo ".png' height=350 /></a>\n";
    }
}
echo "</div>";
