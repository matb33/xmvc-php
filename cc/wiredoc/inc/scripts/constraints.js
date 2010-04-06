function Constraints( ajaxURL, context )
{
	this.context = context;
	this.ajaxURL = ajaxURL;
	this.allowOnNextSubmitEvent = false;
	this.inputKeyUpDelay = 750;

	this.Initialize = function()
	{
		this.BindFieldEvents();
	};

	this.BindFieldEvents = function()
	{
		var thisConstraints = this;

		var eventName = $.browser.msie ? "blur" : "change";

		$( "input[ type='text' ], input[ type='password' ], textarea, select", this.context ).bind( eventName, function()
		{
			window.clearTimeout( $.data( document.body, "timeout" ) );
			thisConstraints.AskServer( $( this ) );
		});

		$( "input[ type='text' ], input[ type='password' ], textarea", this.context ).keydown( function()
		{
			var field = $( this );
			window.clearTimeout( $.data( document.body, "timeout" ) );
			$.data( document.body, "timeout", window.setTimeout( function() { thisConstraints.AskServer( $( field ) ); }, thisConstraints.inputKeyUpDelay ) );

			if( ConstraintVisuals.IsAngry( field ) )
			{
				ConstraintVisuals.Reset( field );
				thisConstraints.TriggerResetEvent( field );
			}

		});

		$( "input[ type='radio' ], input[ type='checkbox' ]", this.context ).click( function()
		{
			window.clearTimeout( $.data( document.body, "timeout" ) );
			thisConstraints.AskServer( $( this ) );
		});

		this.context.submit( function()
		{
			if( thisConstraints.allowOnNextSubmitEvent )
			{
				return( true );
			}
			else
			{
				window.clearTimeout( $.data( document.body, "timeout" ) );
				thisConstraints.AskServer( $( this ) );
				return( false );
			}
		});
	};

	this.AskServer = function( field, submitCallback )
	{
		var affectedFields = this.GetFieldCollection( field );

		ConstraintVisuals.OnAskServer( affectedFields );
		this.TriggerLoadEvents( affectedFields );

		var thisConstraints = this;

		$.ajax( {
			url: this.ajaxURL,
			type: "POST",
			async: true,
			data: this.GetParameters( this.GetUniquelyNamedFieldCollection( field ) ),
			dataType: "xml",
			success: function( data, textStatus ) { thisConstraints.OnResponseFromServer( data, textStatus, field, submitCallback ) }
		});
	};

	this.OnResponseFromServer = function( data, textStatus, eventField, submitCallback )
	{
		var receivedProperResponse = ( textStatus == "success" );
		var thisConstraints = this;

		if( receivedProperResponse )
		{
			var nsPrefix = "";
			var rootElement = $( nsPrefix + "constraint-results", data );
			var fullSuccess = rootElement.attr( "success" ) == "true";

			$( nsPrefix + "field", data ).each( function()
			{
				var name = $( this ).attr( "name" );
				var fieldSuccess = $( this ).attr( "success" ) == "true";
				var field = $( "form *[ name='" + thisConstraints.EscapeName( name ) + "' ], form *[ name='" + thisConstraints.EscapeName( name + "[]" ) + "' ]" );
				var failMessages = [];
				var passMessages = [];

				$( nsPrefix + "constraint-result", this ).each( function()
				{
					if( $( this ).attr( "success" ) == "false" )
					{
						failMessages.push( $( this ).text() );
					}
					else
					{
						passMessages.push( $( this ).text() );
					}
				});

				ConstraintVisuals.OnFieldConstraintResult( field, fieldSuccess, failMessages, passMessages );
				thisConstraints.TriggerResponseEvents( field, fieldSuccess );
			});

			if( submitCallback )
			{
				submitCallback( fullSuccess );
			}
			else
			{
				if( eventField.is( "form" ) )
				{
					this.allowOnNextSubmitEvent = fullSuccess;

					if( fullSuccess )
					{
						this.context.submit();
					}
				}
			}
		}
	};

	this.GetParameters = function( fieldCollection )
	{
		var thisConstraints = this;
		var fieldList = {};

		fieldCollection.each( function()
		{
			fieldList[ thisConstraints.StripBrackets( this.name ) ] = thisConstraints.GetValue( $( this ) );

			thisConstraints.GetDependencyFieldCollection( this.name ).each( function()
			{
				fieldList[ thisConstraints.StripBrackets( this.name ) ] = thisConstraints.GetValue( $( this ) );
			});
		});

		return( fieldList );
	};

	this.GetUniquelyNamedFieldCollection = function( field )
	{
		var fieldCollection = this.GetFieldCollection( field );
		var lookup = [];

		var uniqueFieldCollection = fieldCollection.filter( function()
		{
			var name = $( this ).attr( "name" );

			if( $.inArray( name, lookup ) != -1 )
			{
				return( false );
			}
			else
			{
				lookup.push( name );
				return( true );
			}
		});

		return( uniqueFieldCollection );
	};

	this.GetFieldCollection = function( field )
	{
		if( field.is( ":submit" ) || field.is( "form" ) )
		{
			var fieldCollection = $( "input[ type != 'hidden' ], form textarea, form select", this.context );
		}
		else
		{
			var fieldCollection = $( "*[ name='" + this.EscapeName( field.attr( "name" ) ) + "' ]", this.context );
		}

		return( fieldCollection );
	};

	this.GetDependencyFieldCollection = function( name )
	{
		var thisConstraints = this;

		var dependencyFieldCollection = $( "input[ name='" + this.EscapeName( this.StripBrackets( name ) + "--dependency[]" ) + "' ]", this.context ).map( function()
		{
			return( $( "*[ name='" + thisConstraints.EscapeName( this.value ) + "' ]", thisConstraints.context ).get() );
		});

		return( dependencyFieldCollection );
	};

	this.GetValue = function( field )
	{
		if( field.is( ":radio" ) || field.is( ":checkbox" ) )
		{
			var checkedFields = $( "input[ name='" + this.EscapeName( field.attr( "name" ) ) + "' ]:checked", this.context );

			if( checkedFields.length > 0 )
			{
				var val = checkedFields.map( function() { return( $( this ).val() ); }).get();
			}
			else
			{
				var val = "NULL";
			}
		}
		else if( field.is( "select[ multiple='true' ]" ) )
		{
			var val = field.val();

			if( val == null || val == undefined )
			{
				val = "NULL";
			}
		}
		else
		{
			var val = field.val();
		}

		if( val == null || val == undefined )
		{
			val = "";
		}

		return( val );
	};

	this.EscapeName = function( name )
	{
		if( ! $.browser.msie )
		{
			if( name != null )
			{
				name = name.replace( "[", "\\[" ).replace( "]", "\\]" );
			}
		}

		return( name );
	};

	this.StripBrackets = function( name )
	{
		return( name.replace( "[", "" ).replace( "]", "" ).replace( "\\[", "" ).replace( "\\]", "" ) );
	};

	this.TriggerLoadEvents = function( fieldCollection )
	{
		fieldCollection.each( function()
		{
			$( this ).trigger( "loadstart.constraints" );
		});
	};

	this.TriggerResponseEvents = function( field, success )
	{
		field.trigger( "loadcomplete.constraints" );
		field.trigger( success ? "pass.constraints" : "fail.constraints" );
	};

	this.TriggerResetEvent = function( field )
	{
		field.trigger( "reset.constraints" );
	};

	this.Initialize();
}

var ConstraintVisuals = new function()
{
	this.Reset = function( field )
	{
		var closestLabel = field.closest( "label" );

		closestLabel.removeClass( "constraint-loading" );
		closestLabel.removeClass( "constraint-success" );
		closestLabel.removeClass( "constraint-fail" );

		$( "input[ class='fail' ], input[ class='pass' ]", closestLabel ).remove();
	};

	this.OnAskServer = function( fieldCollection )
	{
		fieldCollection.each( function()
		{
			ConstraintVisuals.Reset( $( this ) );

			$( this ).closest( "label" ).addClass( "constraint-loading" );
		});
	};

	this.OnFieldConstraintResult = function( field, valid, failMessages, passMessages )
	{
		var closestLabel = field.closest( "label" );

		closestLabel.removeClass( "constraint-loading" );
		closestLabel.addClass( valid ? "constraint-success" : "constraint-fail" );

		for( var key in failMessages )
		{
			if( $( "input[ value=" + failMessages[ key ].replace( "\"", "\\\"" ) + " ]", closestLabel ).length == 0 )
			{
				var box = $( "<input class='fail' type='hidden' />" );
				box.val( failMessages[ key ] );
				closestLabel.append( box );
			}
		}

		for( var key in passMessages )
		{
			if( $( "input[ value=" + passMessages[ key ].replace( "\"", "\\\"" ) + " ]", closestLabel ).length == 0 )
			{
				var box = $( "<input class='pass' type='hidden' />" );
				box.val( passMessages[ key ] );
				closestLabel.append( box );
			}
		}
	};

	this.IsAngry = function( field )
	{
		return( field.closest( "label" ).hasClass( "constraint-fail" ) );
	};
}