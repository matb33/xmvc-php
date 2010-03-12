var Constraints = new function()
{
	this.ajaxURL = null;
	this.allowOnNextSubmitEvent = false;
	this.inputKeyUpDelay = 1000;

	this.Initialize = function( ajaxURL, form )
	{
		this.ajaxURL = ajaxURL;
		this.form = form;	// not active -- investigate why this.form.find() barely ever works (NOTE: probably should use $( "whatever", form ) syntax)

		this.BindFieldEvents();
	};

	this.BindFieldEvents = function()
	{
		if( $.browser.msie )
		{
			$( "form input[ type='text' ], input[ type='password' ], textarea, select" ).blur( function()
			{
				window.clearTimeout( $.data( this, "timeout" ) );
				Constraints.AskServer( $( this ) );
			});
		}
		else
		{
			$( "form input[ type='text' ], input[ type='password' ], textarea, select" ).change( function()
			{
				window.clearTimeout( $.data( this, "timeout" ) );
				Constraints.AskServer( $( this ) );
			});
		}

		$( "form input[ type='text' ], input[ type='password' ], textarea" ).keydown( function()
		{
			var field = $( this );
			window.clearTimeout( $.data( this, "timeout" ) );
			$.data( this, "timeout", window.setTimeout( function() { Constraints.AskServer( field ); }, Constraints.inputKeyUpDelay ) );

			if( ConstraintVisuals.IsAngry( field ) )
			{
				ConstraintVisuals.Reset( field );
				Constraints.TriggerResetEvent( field );
			}

		});

		$( "form input[ type='radio' ], input[ type='checkbox' ]" ).click( function()
		{
			Constraints.AskServer( $( this ) );
		});

		$( "form" ).submit( function()
		{
			if( Constraints.allowOnNextSubmitEvent )
			{
				return( true );
			}
			else
			{
				Constraints.AskServer( $( this ) );
				return( false );
			}
		});
	};

	this.AskServer = function( field, submitCallback )
	{
		var affectedFields = this.GetFieldCollection( field );

		ConstraintVisuals.OnAskServer( affectedFields );
		this.TriggerLoadEvents( affectedFields );

		$.ajax( {
			url: this.ajaxURL,
			type: "POST",
			async: true,
			data: this.GetParameters( this.GetUniquelyNamedFieldCollection( field ) ),
			dataType: "xml",
			success: function( data, textStatus ) { Constraints.OnResponseFromServer( data, textStatus, field, submitCallback ) }
		});
	};

	this.OnResponseFromServer = function( data, textStatus, eventField, submitCallback )
	{
		var receivedProperResponse = ( textStatus == "success" );

		if( receivedProperResponse )
		{
			var nsPrefix = $( "c\\:constraint-results", data ).attr( "success" ) == null ? "" : "c\\:";
			var rootElement = $( nsPrefix + "constraint-results", data );
			var fullSuccess = rootElement.attr( "success" ) == "true";

			$( nsPrefix + "field", data ).each( function()
			{
				var name = $( this ).attr( "name" );
				var fieldSuccess = $( this ).attr( "success" ) == "true";
				var field = $( "form *[ name='" + Constraints.EscapeName( name ) + "' ], form *[ name='" + Constraints.EscapeName( name + "[]" ) + "' ]" );
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
				Constraints.TriggerResponseEvents( field, fieldSuccess );
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
						$( "form" ).submit();
					}
				}
			}
		}
	};

	this.GetParameters = function( fieldCollection )
	{
		var fieldList = {};

		fieldCollection.each( function()
		{
			fieldList[ Constraints.StripBrackets( this.name ) ] = Constraints.GetValue( $( this ) );

			Constraints.GetDependencyFieldCollection( this.name ).each( function()
			{
				fieldList[ Constraints.StripBrackets( this.name ) ] = Constraints.GetValue( $( this ) );
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
			var fieldCollection = $( "form input[ type != 'hidden' ], form textarea, form select" );
		}
		else
		{
			var fieldCollection = $( "form *[ name='" + Constraints.EscapeName( field.attr( "name" ) ) + "' ]" );
		}

		return( fieldCollection );
	};

	this.GetDependencyFieldCollection = function( name )
	{
		var dependencyFieldCollection = $( "input[ name='" + Constraints.EscapeName( this.StripBrackets( name ) + "--dependency[]" ) + "' ]" ).map( function()
		{
			return( $( "form *[ name='" + Constraints.EscapeName( this.value ) + "' ]" ).get() );
		});

		return( dependencyFieldCollection );
	};

	this.GetValue = function( field )
	{
		if( field.is( ":radio" ) || field.is( ":checkbox" ) )
		{
			var checkedFields = $( "form input[ name='" + Constraints.EscapeName( field.attr( "name" ) ) + "' ]:checked" );

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
			name = name.replace( "[", "\\[" ).replace( "]", "\\]" );
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
}

var ConstraintVisuals = new function()
{
	this.Reset = function( field )
	{
		var closestLabel = field.closest( "label" );

		closestLabel.removeClass( "constraint-loading" );
		closestLabel.removeClass( "constraint-success" );
		closestLabel.removeClass( "constraint-fail" );

		closestLabel.find( "input[ class='fail' ], input[ class='pass' ]" ).remove();
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
			var box = $( "<input class='fail' type='hidden' />" );
			box.val( failMessages[ key ] );
			closestLabel.append( box );
		}

		for( var key in passMessages )
		{
			var box = $( "<input class='pass' type='hidden' />" );
			box.val( passMessages[ key ] );
			closestLabel.append( box );
		}
	};

	this.IsAngry = function( field )
	{
		return( field.closest( "label" ).hasClass( "constraint-fail" ) );
	};
}