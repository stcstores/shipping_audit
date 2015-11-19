<?php
require_once(dirname($_SERVER['DOCUMENT_ROOT']) . '/private/config.php');
require_once($CONFIG['include']);
checkLogin();
require_once($CONFIG['header']);

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

$api = new LinnworksAPI\LinnworksAPI('stcstores@yahoo.com', 'cosworth');
$database = new STCAdmin\Database();

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

?>
<div class=pagebox >
    <form method=post>
        <table>
            <tr>
                <td><label for=sent >Sent: </label></td>
                <td><input id=sent type="date" name=sent required /></td>
            </tr>
            <tr>
                <td><label for=recieved >Recieved: </label></td>
                <td><input id=recieved type="date" name=recieved required /></td>
            </tr>
            <tr>
                <td><label for=courier >Courier: </label></td>
                <td>
                    <select id=courier name=courier>
                        <?php
                            $allPostageServices = $api->get_postage_service_names();
                            $ignoreFields = array('24 UNASSIGNED', '48 UNASSIGNED', 'Air Mail RM', 'Default', 'Manifested International Sign For');
                            $postageServices = array_diff($allPostageServices, $ignoreFields);
                            foreach ($postageServices as $method) {
                                if (!(in_array($method, $ignoreFields))) {
                                    echo "<option value='" . $method . "' ";
                                    echo ">" . $method . "</option>" . $method . "\n";

                                }
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for=order_id >Order Id: </label></td>
                <td><input name=order_id id=order_id required /></td>
            </tr>
            <tr>
                <td colspan=2 ><input type=submit value='Submit' /></td>
            </tr>
        </table>
    </form>
    <p><?php echo count($all_records); ?> records stored.</p>
    <table>
        <?php
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
            ?>
            <tr>
                <td class=align_right><?php echo $method; ?></td>
                <td><input value='<?php echo $number; ?>' size=3 readonly /></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <br />
    <div class=charts style="text-align: center;">
        <?php

        shell_exec('python ' . dirname($_SERVER['DOCUMENT_ROOT']) . '/public/shipping_audit/shipping_audit_chart.py');

        $files = scandir(dirname($_SERVER['DOCUMENT_ROOT']) . '/public/shipping_audit/charts');

        foreach($method_numbers as $method => $count) {
            if (in_array($method . '.png', $files)) {
                ?>
                <a href="charts/<?php echo $method; ?>.png" target=blank ><img style="margin: 10px;" class=chart src="charts/<?php echo $method; ?>.png" height=350 /></a>
                <?php
            }
        }

        ?>
    </div>
</div>
