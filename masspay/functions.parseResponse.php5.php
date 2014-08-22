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



	function TransferRequest_parseResponse($req, $responseXml, &$errors) {
		
		$response = array();		
		
		$doc = new DOMDocument();	
		if (!$doc->loadXML($responseXml)) {
			$error = "Can't parse server's XML response.";
			return $response;
		}
		
		$rootElem = $doc->getElementsByTagName("TransferResponse")->item(0);
		$responseId = $rootElem->getAttribute("id");
		
		echo "<p>Response id: ".$responseId."</p>";
		
		if ($responseId != $req->id) {
			$error = "ResponseId does not match RequestId.";
			return $response;
		}		
		
		$counter = 0;
		
		$childElems = $rootElem->getElementsByTagName("*");
		
		for ($ci = 0; $ci < $childElems->length;  $ci++) {
			$elem = $childElems->item($ci);
		
			if ($elem->tagName == "Receipt" || $elem->tagName == "Error") {
				
				if ($elem->tagName == "Receipt") {
				
					$receipt = new TransferReceipt($req->transfers[$counter]);
					
					$receipt->id = trim($elem->getElementsByTagName("ReceiptId")->item(0)->textContent);
					$receipt->amount = trim($elem->getElementsByTagName("Amount")->item(0)->textContent);
					$receipt->fee = trim($elem->getElementsByTagName("Fee")->item(0)->textContent);
					$receipt->closingBalance = trim($elem->getElementsByTagName("ClosingBalance")->item(0)->textContent);					
					
					$response[] = $receipt;

				}
				else if ($elem->tagName == "Error") {

					$errorCode = trim($elem->getElementsByTagName("Code")->item(0)->textContent);
					$errorText = trim($elem->getElementsByTagName("Text")->item(0)->textContent);
					
					$apiError = new ApiError($errorCode, $errorText);
					$apiError->transfer = $req->transfers[$counter];
					
					$response[] = $apiError;
				}
				

				$counter++;				
			}
		}
		
		return $response;		
	}
?>		