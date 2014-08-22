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

	
	
function isValidAccountNumber($acct) {
	return ereg("^(U|X)[0-9]{1,}$", $acct);
}

class ApiError {
	var $code;
	var $text;
	var $transfer;
	
	function ApiError($code, $text) {
		$this->code = $code;
		$this->text = $text;
	}
}

class Transfer {
	var $payerAcct;	
	var $payeeAcct;
	var $amount;
	var $memo = "";
	var $isPrivate = false;
	
	function Transfer($payerAcct, $payeeAcct, $amount, $memo = '') {

		$this->payerAcct = $payerAcct;
		$this->payeeAcct = $payeeAcct;
		$this->amount = $amount;		
		$this->memo = $memo;
		$this->isPrivate = false;
	}
	
	function toXml() {
		
		return 
		"<Transfer>".
			"<TransferType>transfer</TransferType>".
		  "<Payer>".$this->payerAcct."</Payer>".
			"<Payee>".$this->payeeAcct."</Payee>".
			"<CurrencyId>LRUSD</CurrencyId>".
			"<Amount>".$this->amount."</Amount>".
			"<Memo>".$this->memo."</Memo>".
			"<Anonymous>".($this->isPrivate ? "true" : "false")."</Anonymous>".
		"</Transfer>";

	}
}

class TransferReceipt {
	var $id = "";
	var $amount = 0;
	var $fee = 0;
	var $closingBalance = 0;
	
	var $transfer;
	
	function TransferReceipt($transfer) {
		$this->transfer = $transfer;
	}
}

class TransferRequest {
	var $id = "";

	var $apiName;
	var $authToken;
	
	var $transfers = array();
	
	function TransferRequest($apiName, $secWord) {
		$this->id = $this->generateId();
		$this->apiName = $apiName;
		$this->authToken = $this->createAuthToken($secWord);
	}
	
	function generateId() {
		return time().rand(0,9999);
	}
		
	function createAuthToken($secWord) {
		$datePart = gmdate("Ymd");
		$timePart = gmdate("H");	
		
		$authString = $secWord.":".$datePart.":".$timePart;
		
		//echo "<p>AuthString: ".$authString."</p>";  
		
		$sha256 = bin2hex(mhash(MHASH_SHA256, $authString));
		
		return strtoupper($sha256);
	}	
	
	function toXml() {
		$authPart = 
			"<Auth><ApiName>".$this->apiName."</ApiName><Token>".$this->authToken."</Token></Auth>";
			
		$transfersPart = "";
		
		$tranfersCount = count($this->transfers);
		
		for($i = 0; $i < $tranfersCount; $i++) {
			$transfersPart .= $this->transfers[$i]->toXml();
		}
		
		return "<TransferRequest id=\"".$this->id."\">".$authPart.$transfersPart."</TransferRequest>";		
	}
	
	function addTransfersFromText($payerAcct, $transferList, &$errors) {
		$lines = explode("\n", $transferList);
		
		for ($i = 0; $i < count($lines); $i++) {
		
			$line = $lines[$i];
			if (trim($line) == "") {
				continue;
			}
		
			$parts = explode(",", $line);
			
			if (count($parts) >=3  && 
					isValidAccountNumber(trim($parts[0])) && 
					is_numeric(trim($parts[1])) && 
					(trim($parts[2]) == "private" || trim($parts[2]) == "not-private")) {
			
				$trans = new Transfer($payerAcct, trim($parts[0]), trim($parts[1]));
				$trans->isPrivate = trim($parts[2]) == "private" ? true : false;
				if (count($parts) == 4) {
					$trans->memo = trim($parts[3]);
				}
				else if (count($parts) > 4) {
					$trans->memo = trim($parts[3]);
				
					for ($pi = 4; $pi < count($parts); $pi++) {
						$trans->memo .=  ",".$parts[$pi];
					}
				}
				
				$this->transfers[] = $trans;
			}
			else {
				$errors .= "Error at line ".($i + 1)."\n";
			}
		}
	}	
	
	function parseResponse($responseXml, &$errors) {
		$ver = explode(".", phpversion());

		if ($ver[0] == 4) {
			require("functions.parseResponse.php4.php");
		}
		else {
			require("functions.parseResponse.php5.php");
		}
		
		return TransferRequest_parseResponse($this, $responseXml, $errors);
	}
	
	function getResponse() {
		$url = "https://api.libertyreserve.com/xml/transfer.aspx?req=".urlencode($this->toXml());

		if(!function_exists('curl_init')) {
			die("Curl library not installed.");
			return "";
		}
		
		$handler=curl_init($url);
		
		ob_start();
		curl_exec($handler);
		$content=ob_get_contents();
		ob_end_clean();
		curl_close($handler);
		
		return $content;
	}
	
	function execute() {
		
		$content = $this->getResponse();
		if (trim($content) == "") {
			die("No response was received from the server.");
		}
		return $this->parseResponse($content);
	}
		
}
	

?>