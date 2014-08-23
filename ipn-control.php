<?php
  /**
  * Bitcoin Payment Gateway
  *
  * @author Jacob Bruce
  * www.bitfreak.info
  */

  // This script will be called once the payment is confirmed.
  // Here you can use the following variables to access details
  // about the transaction and/or update the status of an order
  // (typically the order details should be stored in a database)
  // 
  // $pubAdd : bitcoin address holding funds
  // $tranHash : a unique hash for each transaction
  // $price : the cost of each item (in BTC)
  // $quantity : the number of items purchased
  // $total : the total cost of the order
  // $item : the name/id of the item purchased
  // $note : note/description possibly attached to transaction
  // $baggage : extra data possibly attached to transaction
  
  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/../lib/common.lib.php');
  
  // remove recovery cookie at this point
  setcookie('tcode', '', time()-1000, '/');
  
  // start the session
  session_id($_GET['sid']);
  session_start();
  
  if (!empty($_SESSION['t_data']) || empty($_SESSION['ip_hash'])) {
  
    // protect against session hijacks
    if (($_SESSION['client_type'] != 'torcon') && ($_SESSION['ip_hash'] !== get_ip_hash())) {
      die('It looks like your IP has changed. Contact the admin to verify your order.');
    }
  
    // save the transaction data to individual variables
    list($pubAdd, $price, $quantity, $item, $seller, $success_url, $cancel_url, $note, $baggage) = explode('|', $_SESSION['t_data']);
  
    // ensure the transaction has been confirmed
    if (!empty($_SESSION['tranHash']) && ($_SESSION['confirmed'] === $pubAdd.':confirmed')) {
	
	  // save some other data to vars
	  $currency = $_SESSION['currency'];
	  $total = $_SESSION['total_price'];
	  $tranHash = $_SESSION['tranHash'];
	  $confirm_date = date('Y-m-d H:i:s');
	  
	  // !!!!!!!!!!!!!!!!!!!!!!!! //
	  // YOUR CODE SHOULD GO HERE //
	  // !!!!!!!!!!!!!!!!!!!!!!!! //

      if ($send_email) {
		  
		// create an email to alert admin of confirmation
		$to = $contact_email;
	    $subject="You have a new order!";
		
		// form body of email message
		$body = "A new transaction has been confirmed: \n\n".
		"Item: $item \n".
		"Qnty: $quantity \n".
        "Total: $total BTC \n".
		"Date: $confirm_date \n".
		"Sent to: $pubAdd \n\n".
		"Note: $note";	
		
		// form email headers
		$headers = "From: noreply@".$_SERVER['SERVER_NAME']." \r\n";
		$headers .= "Reply-To: noreply@".$_SERVER['SERVER_NAME']." \r\n";
		$headers .= 'X-Mailer: PHP/'.phpversion();
		
		// send email to admin
		mail($to, $subject, $body, $headers);
	  }
  
      // log the transaction data
      $ts = "Address: ".$pubAdd."\nHash: ".$tranHash."\nPrice(BTC): ".
	      $price."\nTotal: ".$total."\nItem: ".$item."\nQnty: ".
          $quantity."\nDate: ".$confirm_date."\nNote: ".$note."\nBaggage: ".$baggage;
      $fp=fopen(dirname(__FILE__)."/ipn-control.log","a");
      if ($fp) {
        if (flock($fp,LOCK_EX)) {
          @fwrite($fp,$ts."\n\n");
          flock($fp,LOCK_UN);
         }
        fclose($fp);
		chmod(dirname(__FILE__)."/ipn-control.log", 0600);
      }
		  
      // go to success page
      redirect($success_url);
	  exit;

    } else {
      die("An error occured. Go back and try again.");
    }
  } else {
    die("An error occured. Go back and try again.");
  }
?>