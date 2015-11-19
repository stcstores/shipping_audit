<?php

function checkLogin()
{
    $userID = getUserId($_SESSION['username'], $_SESSION['password']);
    if ($userID == '8aa976fb-f6aa-4899-a1da-07662ab5ba56') {
        return true;
        if ($_SESSION['timeout'] > time()) {
            return true;
        }
    }
    //header('location:/logout.php');
    //exit();
}

function login($username, $password)
{
    $userID = getUserId($username, $password);
    if ($userID == '8aa976fb-f6aa-4899-a1da-07662ab5ba56') {
        createLoginSession($username, $password);
        return true;
    }
    return false;
}

function isLoggedIn()
{
    if (isset($_SESSION['username'])) {
        if (isset($_SESSION['password'])) {
            return true;
        }
    }
    return false;
}

function getUserId($username, $password)
{
    $loginURL = 'https://api.linnworks.net/api/Auth/Multilogin';
    $authURL = 'https://api.linnworks.net/api/Auth/Authorize';
    $data = array('userName' => $username, 'password' => $password);
    $multiLogin = make_request($loginURL, $data);
    $userID = $multiLogin[0]['Id'];
    return $userID;
}

function make_request($url, $data)
{
    echo $url;
    $curl = curl_init();
    $headers = array(
        'Content-Type: application/json',
    );
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt(
        $curl,
        CURLOPT_CAINFO,
        $_SERVER['DOCUMENT_ROOT'] . '/shipping_audit/certificates/thawtePrimaryRootCA.crt'
    );
    $dataString = http_build_query($data);
    //echo $dataString;
    curl_setopt($curl, CURLOPT_URL, $url . '?' . $dataString);
    $response = curl_exec($curl);
    echo curl_error($curl);
    $response = json_decode($response, true);
    return $response;
}

function createLoginSession($username, $password)
{
    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    $_SESSION['timeout'] = time() + 60*60*2;
    return true;
}

function getCurrentUsername()
{
    return $_SESSION['username'];
}

function userExists($username)
{
    if (in_array($username, getUsernames())) {
        return true;
    }
    return false;
}
