$( document ).ready( function()
{
	$( "#topnav li" ).hover( 
		function() { $( this ).addClass( "over" ); },
		function() { $( this ).removeClass( "over" ); }
	);

	$( "#topnav li:first-child" ).addClass( "first-child" );
	$( "#topnav li:last-child" ).addClass( "last-child" );

	DropdownOpacity();

	$( "a[rel=contactbox]" ).click( function( e )
	{
		ContactBox();

		e.preventDefault();
	});

	$( "form input[name=submitbutton]" ).click( function( e )
	{
		ContactButton( $( this ) );
	});
});

function DropdownOpacity()
{
	if( msieversion() > 0 )
	{
		$( "#topnav > ul > li > ul" ).each( function()
		{
			var opacity = ( $( this ).css( "widows" ) != "" ? ( parseInt( $( this ).css( "widows" ) ) / 100 ) : $( this ).css( "opacity" ) );

			$( this ).css( "opacity", opacity );
			
		});
	}
}

function ContactBox()
{
	var inner = CreatePopup( "auto", "auto", "contactbox" );

	// Setup popup inside elements

	var iframe = $( "<iframe />" );

	iframe.attr( "id",			"contact-iframe" );
	iframe.attr( "src",			"/contact/" );
	iframe.attr( "frameBorder",	"0" );		// for IE's benefit, and capital B for IE6
	iframe.css( "display",		"block" );
	iframe.css( "position",		"absolute" );

	inner.append( iframe );
}

function ContactButton( thisObj )
{
	if( ValidateForm( thisObj.parent( "form" ) ) )
	{
		function Email()
		{
			var xml = "";

			xml += "<contact>\n";
			xml += "	<oi><![CDATA[" + thisObj.parent( "form" ).find( "input[name='oi']" ).val() + "]]></oi>\n";
			xml += "	<selected-campaign-id><![CDATA[" + thisObj.parent( "form" ).find( "input[name='selected-campaign-id']" ).val() + "]]></selected-campaign-id>\n";
			xml += "	<routing-rule-id><![CDATA[" + thisObj.parent( "form" ).find( "input[name='routing-rule-id']" ).val() + "]]></routing-rule-id>\n";
			xml += "	<custom-fields-24><![CDATA[" + thisObj.parent( "form" ).find( "input[name='custom-fields-24']" ).val() + "]]></custom-fields-24>\n";
			xml += "	<custom-fields-21><![CDATA[" + thisObj.parent( "form" ).find( "input[name='custom-fields-21']" ).val() + "]]></custom-fields-21>\n";
			xml += "	<first-name><![CDATA[" + thisObj.parent( "form" ).find( "input[name='firstname']" ).val() + "]]></first-name>\n";
			xml += "	<last-name><![CDATA[" + thisObj.parent( "form" ).find( "input[name='lastname']" ).val() + "]]></last-name>\n";
			xml += "	<email><![CDATA[" + thisObj.parent( "form" ).find( "input[name='email']" ).val() + "]]></email>\n";
			xml += "	<phone><![CDATA[" + thisObj.parent( "form" ).find( "input[name='phone']" ).val() + "]]></phone>\n";
			xml += "	<state><![CDATA[" + thisObj.parent( "form" ).find( "select[name='state']" ).val() + "]]></state>\n";
			xml += "	<account-name><![CDATA[" + thisObj.parent( "form" ).find( "input[name='company']" ).val() + "]]></account-name>\n";
			xml += "	<custom-fields-22><![CDATA[" + thisObj.parent( "form" ).find( "select[name='numemployees']" ).val() + "]]></custom-fields-22>\n";
			xml += "</contact>\n";

			thisObj.attr( "disabled", "true" );
			thisObj.val( thisObj.parent( "form" ).find( "input[name='submitpleasewait']" ).val() );

			$.ajax(
			{
				type: "POST",
				url: "/contact/send/",
				dataType: "xml",
				processData: false,
				data: "d=" + urlencode( ( xml.length > 0 ? base64_encode( xml ) : "" ) ),
				success: function( xmlResponse ) { EmailContinued( xmlResponse, thisObj ); }
			});
		}

		function EmailContinued( xmlResponse, emailButton )
		{
			var thanks = $( "<p class='thanks' />" );

			thanks.text( thisObj.parent( "form" ).find( "input[name='submitthanks']" ).val() );

			thanks.insertAfter( emailButton );

			emailButton.removeAttr( "disabled" );
			emailButton.val( thisObj.parent( "form" ).find( "input[name='submitnormal']" ).val() );
			emailButton.hide();

			var success	= $( "success", xmlResponse ).text();

			window.setTimeout( "$( 'p.thanks' ).fadeOut( 2000, function() { OnCloseClick( $( '#contactbox', parent.window.document ) ); } );", 5000 );
		}

		Email();
	}
}

//================================================================================
// Support functions
//================================================================================

function msieversion()
{
	var ua = window.navigator.userAgent;
	var msie = ua.indexOf( "MSIE " );

	if( msie > 0 )
	{
		// If Internet Explorer, return version number
		return( parseInt( ua.substring( msie + 5, ua.indexOf( ".", msie ) ) ) );
	}
	else
	{
		// If another browser, return 0
		return( 0 );
	}
}

function urlencode( str )
{
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: urlencode('Kevin van Zonneveld!');
    // *     returns 1: 'Kevin+van+Zonneveld%21'
                                     
    var ret = str;
    
    ret = ret.toString();
    ret = encodeURIComponent(ret);
    ret = ret.replace(/%20/g, '+');
 
    return ret;
}

function urldecode( str )
{
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: urldecode('Kevin+van+Zonneveld%21');
    // *     returns 1: 'Kevin van Zonneveld!'
    
    var ret = str;
       
    ret = ret.replace(/\+/g, '%20');
    ret = decodeURIComponent(ret);
    ret = ret.toString();
 
    return ret;
}

function base64_encode( data )
{
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Bayron Guevara
    // +   improved by: Thunder.m
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)        
    // -    depends on: utf8_encode
    // *     example 1: base64_encode('Kevin van Zonneveld');
    // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
 
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof window['atob'] == 'function') {
    //    return atob(data);
    //}
        
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = ac = 0, enc="", tmp_arr = [];
    data = utf8_encode(data);
    
    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);
 
        bits = o1<<16 | o2<<8 | o3;
 
        h1 = bits>>18 & 0x3f;
        h2 = bits>>12 & 0x3f;
        h3 = bits>>6 & 0x3f;
        h4 = bits & 0x3f;
 
        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);
    
    enc = tmp_arr.join('');
    
    switch( data.length % 3 ){
        case 1:
            enc = enc.slice(0, -2) + '==';
        break;
        case 2:
            enc = enc.slice(0, -1) + '=';
        break;
    }
 
    return enc;
}

function utf8_encode ( str_data )
{
    // http://kevin.vanzonneveld.net
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)        
    // *     example 1: utf8_encode('Kevin van Zonneveld');
    // *     returns 1: 'Kevin van Zonneveld'
 
    str_data = str_data.replace(/\r\n/g,"\n");
    var tmp_arr = [], ac = 0;
 
    for (var n = 0; n < str_data.length; n++) {
        var c = str_data.charCodeAt(n);
        if (c < 128) {
            tmp_arr[ac++] = String.fromCharCode(c);
        } else if((c > 127) && (c < 2048)) {
            tmp_arr[ac++] = String.fromCharCode((c >> 6) | 192);
            tmp_arr[ac++] = String.fromCharCode((c & 63) | 128);
        } else {
            tmp_arr[ac++] = String.fromCharCode((c >> 12) | 224);
            tmp_arr[ac++] = String.fromCharCode(((c >> 6) & 63) | 128);
            tmp_arr[ac++] = String.fromCharCode((c & 63) | 128);
        }
    }
    
    return tmp_arr.join('');
}

function inspect(obj)
{
	alert(obj);

	if(!obj)
	{
		ret = prompt("Enter object", "document");
		obj = eval(ret);
	}

	var temp = "";

	for(x in obj)
	{
		temp += x + ": " + obj[x] + "\n";

		if(temp.length > 700)
		{
			alert(temp);
			temp = "";
		}
	}

	alert(temp);
}