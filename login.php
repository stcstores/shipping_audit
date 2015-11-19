<?php
session_start();
require_once('login_functions.php');

$failedLoggin = false;

if (isLoggedIn()) {
    header('Location:/shipping_audit');
}

print_r($_POST);
if ((isset($_POST['username'])) && (isset($_POST['password']))) {
    if (login($_POST['username'], $_POST['password'])) {
        header('Location:/shipping_audit');
    } else {
        $failedLoggin = true;
    }
}
?>

<form id=login_form method=post>
        <h3>Please Log In</h3>
        <table>
            <tr>
                <td><label for="username">Username: </label></td>
                <td><input type=text size=25 name=username id=login_username class=login required <?php
                if (isset($_POST['login_username'])) {
                    $username = htmlspecialchars($_POST['login_username']);
                    echo 'value="' . $username . '" ';
                }
                ?>/></td>
            </tr>
            <tr>
                <td><label for="password" >Password: </label></td>
                <td><input type="password" size="25" name="password" id="login_password" class=login required /></td>
            </tr>
            <?php
            if ((isset($failedLoggin)) && ($failedLoggin == true)) {
                echo '<tr><td colspan="2" class="error" >Authentication Failed</td></tr>';
            }
            ?>
            <tr>
                <td colspan=2 ><input id=login_button type=submit value='Login' /></td>
            </tr>
        </table>
        <div class=errors id=login_errors>

        </div>
    </form>
