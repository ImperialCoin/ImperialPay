<?
/*///////////////////////////////////////////////////////////////////////////

Description: 			Liberty Reserve Mass Payment Tool. Free (GPL) Package
Release Date: 		Sep 5, 2007
Release Version: 	1.0b 

Designed and Developed by Liberty Reserve development team.
Copyright (c) 2007 Liberty Reserve.
Website: http://www.libertyreserve.com
Contact email: tech@libertyreserve.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3, or (at your option)
any later version. 

This program is distributed in the hope that it will be useful but 
WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  

See the GNU General Public License for more details.

///////////////////////////////////////////////////////////////////////////*/


###############################################################################
$data['PageName']='ACCOUNT OVERVIEW';
$data['PageFile']='index';
###############################################################################
include('../config.htm');
###############################################################################
if(!$_SESSION['login']){
	header("Location:{$data['Host']}/masspay/index.php");
	echo('ACCESS DENIED.');
	exit;
}
require("functions.php");

function showHeader() {
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Liberty Reserve Mass Pay</title>
</head>
<body>

<style type="text/css">
  root, body {
    background: #ffffff;

		margin: 0;
  }

	body, td, th {
    font-family: "Tahoma", Arial, Helvetica, sans-serif;
    font-size: 10pt;
		color:#333333;
	}
  
  .underline-hint {
    color:#666666;
    font-size: 9pt;
  }
  
  h1, h2, h3 {
    color: #333333;
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	}
	
	h1 {	
    font-size: 13pt;
	}
  
	h2 {	
    font-size: 12pt;
	}

	h3 {	
    font-size: 10pt;
	}
	
	table.form td {
		vertical-align: top;
	}
	
	.form .field-name,
	.form .field-value {
		padding-top: 5px;
		padding-bottom: 5px;
		padding-left: 0;		
		padding-right: 10px;
	}
	
	pre.code {	
		padding: 8pt;
		background:#eeeeee;
	}
	
	.content h1,
	.content h2,	
	.content h3,	
	.content p, 
	.content table {
		margin: 0.5em 0 0.5em 0;
	}
	
	div.success,
	div.error {
		font-size: 8pt;
		padding: 3px;
	}
	
	div.success {
		background-color: #339933;
		color: #FFFFFF;
	}
	
	div.error {
		background-color: #CC0033;
		color: #FFFFFF;
	}

	table.transfers th {
		font-size: 9pt;
		text-align: left;
	}
	
	table.transfers th,
	table.transfers td {
		padding: 3px 5px 3px 0;
	}
	
	table.transfers td {
		vertical-align: top;
	}
	
</style>

<table style="width: 100%" cellspacing="0">
	<tr>
		<td style="vertical-align: top; padding: 10px 10px 10px 30px; background: #CCCC99;">
			
      <h1>
        <a href="http://www.libertyreserve.com" style="font-size: 120%; color:#CE0701; text-decoration: none;">Liberty Reserve</a> Mass Payment Tool
      </h1>

		</td>
		<td style="vertical-align: top; padding: 10px 30px 10px 10px; background: #CCCC99;">&nbsp;
			
		</td>
	</tr>
	<tr class="content">
	
    <td style="vertical-align: top; padding: 10px 0 10px 30px">


<?		
}
	
function showFooter() {
?>


    </td>
		<td style="width: 45%; vertical-align: top; padding: 10px 30px 10px 30px;">
			<h2>
				Purpose
			</h2>
			<p>
				This tool allows you to send one or more payments from your Liberty Reserve account to other Liberty Reserve members. 
			</p>
			<h2>
				Installation
			</h2>
			<p>
				Please refer to install.txt, which was provided with this package, for detailed installation instructions. 
			</p>
			<h2>Form fields definitions</h2>
			<p>
				Account Number &#151; this is your account number.<br />
				API Name &#151; must match the API name in the API entry inside your account.<br />
				Security Word &#151; security word that you entered for this API entry inside your account.<br />
			</p>
			<h3>Transfer List Format</h3>
			<p>
				You need to fill &quot;Transfer List&quot; text area with lines in the following format:
		  </p>
			<pre class="code">&lt;target account&gt;, &lt;amount&gt;, private|not-private, &lt;memo&gt;</pre>
			<p>
				Where:
			</p>
			<p>
				<code>&lt;target account&gt;</code> &#151; Destination account (has to start with a letter).<br />
				<code>&lt;amount&gt;</code> &#151; amount of the transfer.<br />
				<code>private|not-private</code> &#151; choose if your payment is private or not.<br />
				<code>&lt;memo&gt;</code> &#151; memo that will be seen by the recipient. Memo is optional and can contain additional commas.<br />
			</p>
			<p>
        Example: 
			</p>
			<pre class="code">U1234567, 10.25, private, First memo
U7654321, 99.99, not-private, Second, memo
X9871234, 205, not-private,</pre>
		</td>
	</tr>
</table>

</body>
</html>


<?		
}


$action = $HTTP_POST_VARS[action];

$canDisplayForm = true;

$wasError = false;

$payerAcct = "";
$payerAcctError = "";

$securityWord = "";
$securityWordError = "";

$transferList = "";
$transferListError = "";

$apiName = "";
$apiNameError = "";


if ($action == "pay") {
	$payerAcct = $HTTP_POST_VARS[payerAcct];
	$securityWord = stripslashes($HTTP_POST_VARS[secWord]);
	$transferList = stripslashes($HTTP_POST_VARS[transferList]);
	$apiName = stripslashes($HTTP_POST_VARS[apiName]);
	
	$canDisplayForm = false;
	$canDisplayResult = true;
	
	if (is_null($payerAcct) || trim($payerAcct) == "") {
		$wasError = true;
		$payerAcctError = "Payer account number can't be empty.";
	}
	else if (!isValidAccountNumber($payerAcct)) {
		$wasError = true;
		$payerAcctError = "Invalid payer account number format.";
	}
	
	if (is_null($securityWord) || trim($securityWord) == '') {
		$wasError = true;
		$securityWordError = "Security Word can't be empty.";
	}

	if (is_null($apiName) || trim($apiName) == '') {
		$wasError = true;
		$apiNameError = "Api Name can't be empty.";
	}
	
	
	
	$request = new TransferRequest($apiName, $securityWord);
	$request->addTransfersFromText($payerAcct, $transferList, $transferListError);	
	
	$transferListError = str_replace("\n","<br />", $transferListError);
	if ($transferListError != "") {
		$wasError = true;
	}
	
}

if ($canDisplayForm || $wasError) { 

	showHeader();

?>			
      <form method="post">
      <input type="hidden" name="action" value="pay"/>
      <table cellpadding="0" cellspacing="0" border="0" class="form">
        <tr>
          <td class="field-name">
            Account Number: 
          </td>
          <td class="field-value">
            <input type="text" name="payerAcct" value="<?=htmlspecialchars($payerAcct)?>"/>
						<? if ($payerAcctError != "") { ?>
						<div class="error"><?=$payerAcctError?></div>
						<? } ?>
          </td>
          <td class="field-name">
            Api Name: 
          </td>
          <td class="field-value">
            <input type="text" name="apiName" value="<?=htmlspecialchars($apiName)?>"/>
						<? if ($apiNameError != "") { ?>						
						<div class="error"><?=$apiNameError?></div>
						<? } ?>						
          </td>
        </tr>
			  <tr>
          <td class="field-name">
            Security Word: 
          </td>
          <td class="field-value">
            <input type="password" name="secWord" />
						<? if ($securityWordError != "") { ?>						
						<div class="error"><?=$securityWordError?></div>
						<? } ?>						
          </td>
        </tr>
			</table>
			<div>
			Transfer List:
			</div>
	
			<? if ($transferListError != "") { ?>
			<div class="error"><?=$transferListError?></div>
			<? } ?>			
			
			<textarea name="transferList" style="width: 100%; height: 200px"><?=htmlspecialchars($transferList)?></textarea>
	
			<div style="padding: 15px 0 15px 0; ">
				<input type="submit" value="Pay!" style="width: 10em"/>&nbsp;&nbsp;
				<input type="reset" value="Clear Form" style="width: 10em"/>
			</div>
      </form>
<? 

	showFooter();
} 
else if ($canDisplayResult) {

	showHeader();


	
?>					

<!--			
<?
	$responseContent = $request->getResponse();
?>
-->

	<p> 
<?
	$responseParseErrors = "";
	$responseObjectsCount = 0;
	if (trim($responseContent) != "") {
		$responseObjects = $request->parseResponse($responseContent, $responseParseErrors);
		$responseObjectsCount = count($responseObjects);
	}
	else {
		$responseParseErrors = "Server returned an empty response.";
	}
?>

	</p>


	<p>
		Payer Account: <strong><?=$payerAcct?></strong>
	</p>
<?

	if ($responseParseErrors != "") {
?>	
		<div class="error">
			<b>Could not pay via API. Intercommunication error.</b><br />
			Check your Liberty Reserve transaction history to ensure 
			that no transfers were made before trying to repeat the same transfers.<br />
			<b>Details:</b><br />
			<?=$responseParseErrors?>
		</div>
<?		
	}
	if ($responseObjectsCount > 0) {

?>	
			
			<table class="transfers" cellspacing="0" width="100%">
				<thead>
				<tr>
					<th>
						
					</th>
					<th>
						Payee Acct
					</th>
					<th>
						Amount
					</th>
					<th>
						Fee
					</th>
					<th>
						Closing Balance
					</th>
					<th>
						Id
					</th>
					<th>
						Memo
					</th>
				</tr>
				</thead>
				<tbody>
<?
		for ($i = 0; $i < $responseObjectsCount; $i++) {
			$ro = $responseObjects[$i];
?>					
					<tr>
						<td style="text-align: center">
							<?=(method_exists($ro, "TransferReceipt") ?  "<div class=\"success\">Success</div>" : "<div class=\"error\">Error</div>")?>
						</td>	
						<td>
							<?=$ro->transfer->payeeAcct?>
						</td>	
						<td>
							<?=$ro->transfer->amount?>
						</td>	
						<td>
							<?=(method_exists($ro, "TransferReceipt") ?  $ro->fee : "")?>
						</td>	
						<td>
							<?=(method_exists($ro, "TransferReceipt") ?  $ro->closingBalance : "")?>
						</td>	
						<td>
							<?=(method_exists($ro, "TransferReceipt") ? $ro->id : "")?>
						</td>
						<td>
							<?=$ro->transfer->memo?>
						</td>	
					</tr>
					
<?
			if (method_exists($ro, "ApiError")) {
?>					
					<tr>
						<td colspan="3" class="underline-hint">
							Info: <?=$ro->code?>: <?=$ro->text?>
						</td>
					</tr>
					
<?
			}
		}
?>
				</tbody>
			</table>	
<?		
	}
	else {
?>
		&nbsp;
<?
			
	}
	
	showFooter();
}

?>		
