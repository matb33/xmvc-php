$( document ).ready( function()
{
	$( ".clab" ).click( function( e )
	{
		var ctrlPressed		= e.ctrlKey;
		var altPressed		= e.altKey;
		var shiftPressed	= e.shiftKey;

		if( ctrlPressed && altPressed )
		{
			var element = new ContentLABElement( $( this ) );
			
			element.MakeEditable( e );
		}
		else
		{
			e.preventDefault();
		}
	});

	$( ".clab" ).hover(

		function( e )
		{
			var ctrlPressed		= e.ctrlKey;
			var altPressed		= e.altKey;
			var shiftPressed	= e.shiftKey;

			if( ctrlPressed && altPressed )
			{
				$( this ).addClass( "clab-editable" );
			}
		},
		function( e )
		{
			$( this ).removeClass( "clab-editable" );
		}

	);
});

var ContentLAB = Base.extend( {

	constructor: function()
	{
		this.definition = "";
		this.instanceName = "";
	}

});

var ContentLABElement = Base.extend( {

	constructor: function( obj )
	{
		this.obj = obj;
		this.originalContents = "";
	},

	MakeEditable: function( e )
	{
		var gthis = this;

		this.obj.get( 0 ).contentEditable = true;
		this.obj.addClass( "clab-editing" );
		this.obj.removeClass( "clab-editable" );
		this.obj.focus();

		this.StoreOriginal();

		e.preventDefault();

		this.obj.blur( function()
		{
			gthis.SaveChanges();
		});

		this.obj.keydown( function( e )
		{
			switch( e.keyCode )
			{
				case 27:
					// Escape undos
					gthis.RevertChanges();
					gthis.StopEditing();
					//gthis.obj.blur();
				break;
			}
		});
	},

	StoreOriginal: function()
	{
		this.originalContents = this.obj.get( 0 ).innerHTML;
	},

	SaveChanges: function()
	{
		var gthis = this;

		this.StopEditing();

		var matches = this.obj.attr( "class" ).toString().match( /([\w_-]+)\/([\w_-]+)/i );

		if( matches != null )
		{
			var definition		= matches[ 1 ];
			var instanceName	= matches[ 2 ];

			var xhtml			= $( "<" + this.obj[ 0 ].localName + "/>" ).append( this.obj.clone() ).remove().html();
			var content			= this.CleanUp( xhtml );
			var contentEncoded	= urlencode( ( content.length > 0 ? base64_encode( content ) : "" ) );

			$.ajax(
			{
				type: "POST",
				url: "/clab/request/write/" + definition + "/" + instanceName + "/",
				dataType: "xml",
				processData: false,
				data: "d=" + contentEncoded,
				success: function( xmlResponse ) { gthis.SaveChangesContinued( xmlResponse ); }
			});
		}
	},

	SaveChangesContinued: function( xmlResponse )
	{
	},

	RevertChanges: function()
	{
		this.obj.get( 0 ).innerHTML = this.originalContents;
	},

	StopEditing: function()
	{
		this.obj.get( 0 ).contentEditable = false;
		this.obj.removeAttr( "contenteditable" );
		this.obj.removeClass( "clab-editing" );
		this.obj.unbind( "blur" );
		this.obj.unbind( "keydown" );
	},

	CleanUp: function( content )
	{
		// Perhaps later do this server-side with an HTMLTidy implementation.

		// Replace &nbsp; with spaces
		content = content.replace( /&nbsp;/g, " " );

		// Replace <br> with <br />
		content = content.replace( /<br>/g, "<br />" );

		// Replace MS Word single quote to regular quote (should I be doing this? It's more for consistency than anything else)
		content = content.replace( /’/g, "'" );

		if( msieversion() > 0 )
		{
			// IE does not surround attribute values with quotes.  Use a regular expression to fix. This regular expression is far from perfect.
			content = content.replace( /(\w+)(=+)([^"<>\' ]+)(\s+|\/|>)/g, "$1$2\"$3\"$4" );

			// Lowercase element names
			content = content.replace( /<(\/?[A-Z][^>]*)>/g, function( match ) { return match.toLowerCase(); } );

			// Remove sizcache and sizset attributes
			content = content.replace( / (sizcache|sizset)="[0-9]+"/g, "" );
		}

		return( content );
	}

});