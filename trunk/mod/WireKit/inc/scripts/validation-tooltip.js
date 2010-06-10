var ValidationTooltip = new function()
{
	this.input = null;

	this.Initialize = function()
	{
		$( "body" ).append( "<div id='validation-tooltip'><div class='validation-tooltip' /></div>" );

		this.BindInputEvents();
		this.BindConstraintEvents();
	};

	this.BindInputEvents = function()
	{
		$( "form :input" ).focus( function() { ValidationTooltip.OnFormInputFocus( this ) } );
		$( "form :input" ).blur( function() { ValidationTooltip.OnFormInputBlur( this ) } );
	};

	this.BindConstraintEvents = function()
	{
		$( "form :input" ).each( function()
		{
			$( this ).bind( "loadstart.validation", ValidationTooltip.OnConstraintLoadStart );
			$( this ).bind( "loadcomplete.validation", ValidationTooltip.OnConstraintLoadComplete );
			$( this ).bind( "pass.validation", ValidationTooltip.OnConstraintPass );
			$( this ).bind( "fail.validation", ValidationTooltip.OnConstraintFail );
			$( this ).bind( "reset.validation", ValidationTooltip.OnConstraintReset );
		});
	};

	this.OnFormInputFocus = function( input )
	{
		this.input = input;
		this.ShowTooltip();
	};

	this.OnFormInputBlur = function( input )
	{
		if( $( "#validation-tooltip:animated" ).length == 0 )
		{
			this.input = input;
			this.HideTooltip();
		}
	};

	this.ShowTooltip = function()
	{
		var infoMessages;
		var failMessages;
		var passMessages;

		if( $( "span.constraint" ).length > 0 )
		{
			var context = $( "span.constraint." + $( this.input ).attr( "name" ).stripBrackets() );
			infoMessages = $( "input[ class=info ]", context ).map( function() { return( $( this ).val() ); } ).get();
			failMessages = $( "input[ class=fail ]", context ).map( function() { return( $( this ).val() ); } ).get();
			passMessages = $( "input[ class=pass ]", context ).map( function() { return( $( this ).val() ); } ).get();
		}
		else
		{
			infoMessages = $( this.input ).closest( "label" ).find( "input[ class=info ]" ).map( function() { return( $( this ).val() ); } ).get();
			failMessages = $( this.input ).closest( "label" ).find( "input[ class=fail ]" ).map( function() { return( $( this ).val() ); } ).get();
			passMessages = $( this.input ).closest( "label" ).find( "input[ class=pass ]" ).map( function() { return( $( this ).val() ); } ).get();
		}

		if( passMessages.length > 0 && failMessages.length == 0 )
		{
			// Pass on all constraints, no reaction
			var messages = [];
			$( "#validation-tooltip" ).removeClass( "angry" );
		}
		else if( failMessages.length > 0 )
		{
			// One or more failed constraints, get angry
			var messages = failMessages;
			$( "#validation-tooltip" ).addClass( "angry" );
		}
		else if( infoMessages.length > 0 )
		{
			// Haven't communicated with server for messages or we've been reset, show info
			var messages = infoMessages;
			$( "#validation-tooltip" ).removeClass( "angry" );
		}
		else
		{
			// Same as above, except there was no info available, so hide tooltip
			var messages = [];
			this.HideTooltip();
		}
			
		if( messages.length > 0 )
		{
			$( "#validation-tooltip .validation-tooltip" ).html( this.GetHTMLMessage( messages ) );
			$( "#validation-tooltip" ).stop();
			$( "#validation-tooltip" ).show();
			$( "#validation-tooltip" ).fadeTo( "fast", 1.0 );
			$( "#validation-tooltip" ).offset( this.DetermineTooltipPosition() );
		}
	};

	this.GetHTMLMessage = function( messages )
	{
		if( messages.length == 1 )
		{
			var htmlMessage = $( "<p />" ).html( messages[ 0 ] );
		}
		else if( messages.length > 1 )
		{
			var htmlMessage = $( "<ul />" );

			for( var key in messages )
			{
				htmlMessage.append( $( "<li />" ).html( messages[ key ] ) );
			}
		}

		return( htmlMessage );
	};

	this.DetermineTooltipPosition = function()
	{
		var target;
		var leftOffset;
		var topOffset;

		if( $( this.input ).is( ":checkbox, :radio" ) )
		{
			target = $( this.input ).closest( "label" ).find( "span" );
			leftOffset = 55;
			topOffset = -20;

		}
		else
		{
			target = $( this.input );
			leftOffset = 0;
			topOffset = -10;
		}

		var pos = target.offset();
		
		pos.left += ( target.outerWidth() - $( "#validation-tooltip" ).width() + leftOffset );
		pos.top -= ( $( "#validation-tooltip" ).height() + topOffset );

		return( pos );
	};

	this.HideTooltip = function()
	{
		if( $( "#validation-tooltip:visible" ).length > 0 )
		{
			$( "#validation-tooltip" ).stop();
			$( "#validation-tooltip" ).fadeOut();
		}
	};

	this.OnConstraintLoadStart = function( data )
	{
	};

	this.OnConstraintLoadComplete = function( data )
	{
	};

	this.OnConstraintPass = function( data )
	{
		if( $( ValidationTooltip.input )[ 0 ] === $( data.target )[ 0 ] )
		{
			ValidationTooltip.HideTooltip();
		}
	};

	this.OnConstraintFail = function( data )
	{
		if( $( ValidationTooltip.input )[ 0 ] === $( data.target )[ 0 ] )
		{
			ValidationTooltip.ShowTooltip();
		}
		else
		{
			// Note that this won't work as expected if there is more than one checkbox on the page.
			// This should be fixed for a more long-term solution.
			if( $( data.target ).is( ":checkbox, :radio" ) )
			{
				ValidationTooltip.input = data.target;
				ValidationTooltip.ShowTooltip();
			}
		}
	};

	this.OnConstraintReset = function( data )
	{
		ValidationTooltip.ShowTooltip();
	};
};