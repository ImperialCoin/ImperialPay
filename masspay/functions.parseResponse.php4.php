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
		
		if (!$doc = domxml_open_mem($responseXml)) {
			$error = "Can't parse server's XML response.";
			return $response;
		}
		
		$root = $doc->document_element();



		$responseId = $root->get_attribute("id");
		
		if ($responseId != $req->id) {
			$error = "ResponseId does not match RequestId.";
			return $response;
		}

		
		echo "<p>".$responseId."</p>";

		$counter = 0;
		
		foreach($root->child_nodes() as $node) {
		
			if ($node->node_type() == XML_ELEMENT_NODE && ($node->node_name() == "Receipt" || $node->node_name() == "Error")) {
				
				if ($node->node_name() == "Receipt") {
				
					$receipt = new TransferReceipt($req->transfers[$counter]);
					
					$idElems = $node->get_elements_by_tagname("ReceiptId");
					$idElem = $idElems[0];
					$receipt->id = trim($idElem->get_content());
					
					$amountElems = $node->get_elements_by_tagname("Amount");
					$amountElem = $amountElems[0];
					$receipt->amount = trim($amountElem->get_content());
					
					$feeElems = $node->get_elements_by_tagname("Fee");
					$feeElem = $feeElems[0];
					$receipt->fee = trim($feeElem->get_content());
					
					$closingBalanceElems = $node->get_elements_by_tagname("ClosingBalance");
					$closingBalanceElem = $closingBalanceElems[0];
					$receipt->closingBalance = trim($closingBalanceElem->get_content());					
					
					$response[] = $receipt;

				}
				else if ($node->node_name() == "Error") {
					
					$errorCodeElems = $node->get_elements_by_tagname("Code");
					$errorCodeElem = $errorCodeElems[0];
					$errorCode = trim($errorCodeElem->get_content());

					$errorTextElems = $node->get_elements_by_tagname("Text");
					$errorTextElem = $errorTextElems[0];
					$errorText = trim($errorTextElem->get_content());
					
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