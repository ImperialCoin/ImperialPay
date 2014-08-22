function translateTo(dest){ //this can be declared in the global scope too if you need it somewhere else
	if(!dest)
		var dest='English';
	$('body').translate( 'en', { //revert to english first
	not: '.jq-translate-ui,#rate_star',
	complete: function(){configLightBox();}
	})


	/*	var myval=$(.jq-translate-ui).val(); 
		alert(myval);*/
	
	if(dest!='English')
	{
		$('body').translate( 'en', dest, {   //translate from english to the selected language
		  not: '.jq-translate-ui,#rate_star',  //by default the generated element has this className
		  complete: function(){configLightBox();},
		  fromOriginal:true   //always translate from english (even after the page has been translated)
		});
	}
	$.cookie('destLang', dest);
	$(".jq-translate-ui").val(dest);
	if(dest=='English')
	{
	var nt=document.getElementById("buy").innerHTML;
	var tn=document.getElementById("faq").innerHTML;
/*	alert(tn);*/
	if(nt.search('Buy')==-1){
		document.getElementById("buy").innerHTML="Buy&amp;Sell iPayGold";
		location.reload();
	}
	if(tn.search('Faq')==-1){
		document.getElementById("faq").innerHTML="Faq";
	}
	}


}   

$(document).ready(function() {
						   	
	if( $.cookie('destLang')!=null)
		var destLang = $.cookie('destLang');
	else
		var destLang ='English'; //when the script is loaded
		
	    $.translate(function(){ //when the Google Language API is loaded
		$.translate.ui('select', 'option') //generate dropdown
		
		.change(function(){
			//alert($(this).val());
			//when selecting another language
			translateTo($(this).val());
		})
	
		.val(destLang)
		.appendTo('#language')
		.css({'color':'#000000', 'background-color':'white','font-size':'11px'})
		.find(' option')
		.css('cursor','pointer'); //insert the dropdown to the page
	});
	translateTo(destLang);

	/*if(destLang=='English')
	{
	 // location.reload();	
	 //return false;
	 alert(destLang);
	}*/
}); //end of Google Language API loaded
