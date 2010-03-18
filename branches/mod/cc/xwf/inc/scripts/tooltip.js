var Tooltip = new function()
{
	this.input = null;

	this.Initialize = function()
	{
		$( "body" ).append( "<div id='tooltip'><div class='tooltip' /></div>" );

		this.BindInputEvents();
		this.BindConstraintEvents();
	};

	this.BindInputEvents = function()
	{
		$( "form :input" ).focus( function() { Tooltip.OnFormInputFocus( this ) } );
		$( "form :input" ).blur( function() { Tooltip.OnFormInputBlur( this ) } );
	};

	this.BindConstraintEvents = function()
	{
		$( "form :input" ).each( function()
		{
			$( this ).bind( "loadstart.constraints", Tooltip.OnConstraintLoadStart );
			$( this ).bind( "loadcomplete.constraints", Tooltip.OnConstraintLoadComplete );
			$( this ).bind( "pass.constraints", Tooltip.OnConstraintPass );
			$( this ).bind( "fail.constraints", Tooltip.OnConstraintFail );
			$( this ).bind( "reset.constraints", Tooltip.OnConstraintReset );
		});
	};

	this.OnFormInputFocus = function( input )
	{
		this.input = input;
		this.ShowTooltip();
	};

	this.OnFormInputBlur = function( input )
	{
		if( $( "#tooltip:animated" ).length == 0 )
		{
			this.input = input;
			this.HideTooltip();
		}
	};

	this.ShowTooltip = function()
	{
		var infoMessages = $( this.input ).closest( "label" ).find( "input[ class='info' ]" ).map( function() { return( $( this ).val() ); } ).get();
		var failMessages = $( this.input ).closest( "label" ).find( "input[ class='fail' ]" ).map( function() { return( $( this ).val() ); } ).get();
		var passMessages = $( this.input ).closest( "label" ).find( "input[ class='pass' ]" ).map( function() { return( $( this ).val() ); } ).get();
		
		if( passMessages.length > 0 && failMessages.length == 0 )
		{
			// Pass on all constraints, no reaction
			var messages = [];
			$( "#tooltip" ).removeClass( "angry" );
		}
		else if( failMessages.length > 0 )
		{
			// One or more failed constraints, get angry
			var messages = failMessages;
			$( "#tooltip" ).addClass( "angry" );
		}
		else if( infoMessages.length > 0 )
		{
			// Haven't communicated with server for messages or we've been reset, show info
			var messages = infoMessages;
			$( "#tooltip" ).removeClass( "angry" );
		}
		else
		{
			// Same as above, except there was no info available, so hide tooltip
			var messages = [];
			this.HideTooltip();
		}
			
		if( messages.length > 0 )
		{
			$( "#tooltip .tooltip" ).html( this.GetHTMLMessage( messages ) );
			$( "#tooltip" ).stop();
			$( "#tooltip" ).show();
			$( "#tooltip" ).fadeTo( "fast", 1.0 );
			$( "#tooltip" ).offset( this.DetermineTooltipPosition() );
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
		var inputPos = $( this.input ).offset();
		inputPos.left += ( $( this.input ).width() - $( "#tooltip" ).width() + 55 );
		inputPos.top -= ( $( "#tooltip" ).height() );

		return( inputPos );
	};

	this.HideTooltip = function()
	{
		$( "#tooltip" ).fadeOut();
	};

	this.OnConstraintLoadStart = function( data )
	{
	};

	this.OnConstraintLoadComplete = function( data )
	{
	};

	this.OnConstraintPass = function( data )
	{
		if( $( Tooltip.input )[ 0 ] == $( data.target )[ 0 ] )
		{
			Tooltip.HideTooltip();
		}
	};

	this.OnConstraintFail = function( data )
	{
		if( $( Tooltip.input )[ 0 ] == $( data.target )[ 0 ] )
		{
			Tooltip.ShowTooltip();
		}

		// Note that this won't work as expected if there is more than one checkbox on the page.
		// This should be fixed for a more long-term solution.
		if( $( data.target ).is( ":checkbox" ) )
		{
			Tooltip.input = data.target;
			Tooltip.ShowTooltip();
		}
	};

	this.OnConstraintReset = function( data )
	{
		Tooltip.ShowTooltip();
	};
}