function ValidationVisuals()
{
	// private fields
	var that = this;

	// public methods
	this.reset = function( field )
	{
		var closestLabel = field.closest( "label" );

		closestLabel.removeClass( "validation-loading" );
		closestLabel.removeClass( "validation-success" );
		closestLabel.removeClass( "validation-fail" );

		if( $( "span.constraint" ).length > 0 )
		{
			var context = $( "span.constraint." + field.attr( "name" ).stripBrackets() );
			$( "input.fail", context ).removeClass( "fail" );
			$( "input.pass", context ).removeClass( "pass" );
		}
		else
		{
			$( "input[ class='fail' ], input[ class='pass' ]", closestLabel ).remove();
		}
	};

	this.onBeforeValidationCheck = function( fieldCollection )
	{
		fieldCollection.each( function()
		{
			that.reset( $( this ) );

			$( this ).closest( "label" ).addClass( "validation-loading" );
		});
	};

	this.onAfterValidationCheck = function( field, valid, failMessages, passMessages )
	{
		var closestLabel = field.closest( "label" );
		var context = $( "span.constraint." + field.attr( "name" ).stripBrackets() );
		var key = null;
		var box = null;

		closestLabel.removeClass( "validation-loading" );
		closestLabel.addClass( valid ? "validation-success" : "validation-fail" );

		for( key in failMessages )
		{
			if( $( "span.constraint" ).length > 0 )
			{
				if( !valid )
				{
					$( "input[value=" + failMessages[ key ].replace( "\"", "\\\"" ) + "]", context ).addClass( "fail" );
				}
			}
			else
			{
				if( $( "input[value=" + failMessages[ key ].replace( "\"", "\\\"" ) + "]", closestLabel ).length == 0 )
				{
					box = $( "<input class='fail' type='hidden' />" );
					box.val( failMessages[ key ] );
					closestLabel.append( box );
				}
			}
		}
		
		for( key in passMessages )
		{
			if( $( "span.constraint" ).length > 0 )
			{
				if( valid )
				{
					$( "input[value=" + passMessages[ key ].replace( "\"", "\\\"" ) + "]", context ).addClass( "pass" );
				}
			}
			else
			{
				if( $( "input[value=" + passMessages[ key ].replace( "\"", "\\\"" ) + "]", closestLabel ).length == 0 )
				{
					box = $( "<input class='pass' type='hidden' />" );
					box.val( passMessages[ key ] );
					closestLabel.append( box );
				}
			}
		}
	};

	this.isAngry = function( field )
	{
		return( field.closest( "label" ).hasClass( "validation-fail" ) );
	};
}