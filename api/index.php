<?php

// Starchat Class Web Wrapper (API) v0.9.0

// MIT License

// Copyright (c) 2018-2019 NekoBit

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.



require '../config.php';
require '../starchat/starchat.php';

// Usually for debugging, but also hide html warnings and errors if the users config is set up strangely
error_reporting(1); // Set to E_ALL for error reporting
ini_set('display_errors', 1);

// Set header for JSON results
header('Content-type: application/json');

use Starchat\StarchatApi;

$api = new StarchatApi($conn);

// Shortened GET variables
// Login info
if(isset($_GET["username"])) $username = $_GET["username"];
if(isset($_GET["password"])) $password = $_GET["password"];
if(isset($_GET["token"])) $token = $_GET["token"];

// API Usages
if(isset($_GET["getcontacts"])) $get_contacts = $_GET["getcontacts"];
if(isset($_GET["readmessages"])) $get_messages = $_GET["readmessages"];
if(isset($_GET["sendmessage"])) $send_message = $_GET["sendmessage"];
if(isset($_GET["sendmessageto"])) $send_message_to = $_GET["sendmessageto"];
if(isset($_GET["addcontact"])) $add_contact = $_GET["addcontact"];
if(isset($_GET["gentoken"])) $gen_token = $_GET["gentoken"];

if(isset($username) and isset($password)) {
    $api->set_login($username, $password);
}

if(isset($gen_token)) {
    echo $api->generate_token();
}

if(isset($token)) {
    $api->set_token($token);

    if (isset($get_contacts)) {
        die($api->get_contacts());
    }

    if (isset($get_messages)) {
        die($api->read_messages($get_messages));
    }

    if (isset($send_message)) {
        $api->send_message($send_message, $send_message_to);
    }

    if (isset($add_contact)) {
        $api->add_contact($add_contact);
    }
}
