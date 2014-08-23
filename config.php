<?php
// business name
$seller = 'Your Business';

// full website url (with slash on end)
$site_url = 'http://localhost/';

// location of bitcoin sci folder from root
$bitsci_url = 'sci/';

// number of confirmations needed (can't be 0)
$confirm_num = 1;

// amount of time between each refresh (in seconds)
$refresh_time = 15;

// amount the progress bar increases with each refresh
$prog_inc = 5;

// payment precision (allow a bit of wiggle room)
$p_variance = 0.000001;

// bitcoin price thousands separator
$t_separator = ',';

// should you receive an email upon confirmation?
$send_email = true;

// email for receiving confirmation notices
$contact_email = 'your_email@mail.com';

// admin control panel password
$admin_pass = 'CHANGETHISSTRING';

// security string used for encryption (16 chars)
$sec_str = 'CHANGETHISSTRING';

// public RSA key used to encrypt private keys
$pub_rsa_key = 'CHANGETHISSTRING';

/////////////////////////////////////
/* IGNORE ANYTHING UNDER THIS LINE */
/////////////////////////////////////
define('CONF_NUM', $confirm_num);
define('SEC_STR', $sec_str);
define('SEP_STR', $t_separator);

// turn on/off error reporting
ini_set('display_errors', 1); 
error_reporting(0);

$app_version = '0.5.4';
?>