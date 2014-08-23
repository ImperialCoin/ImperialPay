<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

// generate random tran code string
$t_code = bitcoin::randomString(26);

// check what currency we are using
$currency = (empty($_GET['c'])) ? 'btc' : strtolower($_GET['c']);
  
// set cookies for recovering transaction
setcookie('tcode', $t_code, time()+172800, '/');
setcookie('tcurr', $currency, time()+172800, '/');

// start the session
session_start();
  
if (empty($_GET['donate']) || !is_numeric($_GET['donate'])) {
  
  die('invalid input');
  
} else {
  
  // unset old session data (KEEP THIS)
  unset($_SESSION['tranHash']);
  unset($_SESSION['confirmed']);
  unset($_SESSION['total_price']);
  unset($_SESSION['ip_hash']);
  
  // generate a new key pair
  switch ($currency) {
    case 'btc': $keySet = bitcoin::getNewKeySet(); break;
	case 'ltc': $keySet = litecoin::getNewKeySet(); break;
  }
  
  if (empty($keySet['pubAdd']) || empty($keySet['privWIF'])) {
      die("<p>There was an error generating the payment address. Please go back and try again.</p>");
  }
  
  // form encrypted key data
  $encWIF = bin2hex(bitsci::rsa_encrypt($keySet['privWIF'], $pub_rsa_key));
  $key_data = $encWIF . ':' . $keySet['pubAdd'];
  
  // set up sci variables
  $price = $_GET['donate'];
  $item = $_GET['item'];
  $quantity = 1;
  $note = 'null';
  $baggage = 'null';
  $cancel_url = $site_url.'example.php?result=cancel';
  $success_url = $site_url.'example.php?result=success';
  
  // generate transaction file name hash
  if ($_POST['client'] == 'tcon') {
    $_SESSION['client_type'] = 'torcon';
	$file_hash = hash('sha256', $t_code);
  } else {
    $_SESSION['client_type'] = 'normal';
	$file_hash = hash('sha256', get_ip_hash().$t_code);
  }
	
  // encrypt transaction data and save to file
  $t_data = bitsci::build_pay_query($keySet['pubAdd'], $price, $quantity, $item, $seller, $success_url, $cancel_url, $note, $baggage);
  
  if (file_put_contents('t_data/'.$file_hash, $t_data) !== false) {
	  chmod('t_data/'.$file_hash, 0600);
  } else {
    die("<p class='error_txt'>There was an error creating the transaction. Please go back and try again.</p>");
  }

  // build the URL for the bitcoin payment gateway
  $payment_gateway = $site_url.$bitsci_url.'payment.php?t='.$t_code.'&c='.$currency;
  
  // save encrypted private WIF key to file (along with address).
  // you might want to save these keys to a database instead.
  $fp=fopen(dirname(__FILE__)."/wif-keys.csv","a");
  if ($fp) {
    if (flock($fp, LOCK_EX)) {
      @fwrite($fp, $key_data.",\n");
      flock($fp, LOCK_UN);
    }
    fclose($fp);
  }

  // go to payment gateway
  redirect($payment_gateway);

}
?>