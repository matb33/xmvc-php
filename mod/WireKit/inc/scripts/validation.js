jQuery.fn.validator = function( ajaxURL )
{
	// private fields
	var allowOnNextSubmitEvent = false;
	var inputKeyUpDelay = 750;
	var validationVisuals = new ValidationVisuals();
	var context = this;

	// private methods
	var constructor = function()
	{
		if( $( this ).length > 0 )
		{
			bindFieldEvents();
		}
	};

	var bindFieldEvents = function()
	{
		$( "input[ type=text ], input[ type=password ], textarea", context ).keydown( function()
		{
			var field = $( this );
			window.clearTimeout( $.data( document.body, "timeout" ) );
			$.data( document.body, "timeout", window.setTimeout( function()
			{
				validate( $( field ) );
			}, inputKeyUpDelay ) );

			if( validationVisuals.isAngry( field ) )
			{
				validationVisuals.reset( field );
				triggerResetEvent( field );
			}

		});

		$( ":checkbox, :radio", context ).click( function()
		{
			window.clearTimeout( $.data( document.body, "timeout" ) );
			validate( $( this ) );
		});

		$( context ).submit( function()
		{
			if( allowOnNextSubmitEvent )
			{
				return true;
			}
			else
			{
				window.clearTimeout( $.data( document.body, "timeout" ) );
				validate( $( this ) );
				return false;
			}
		});
	};

	var validate = function( field, submitCallback )
	{
		if( isClientSide() )
		{
			askClient( field, submitCallback );
		}
		else
		{
			askServer( field, submitCallback );
		}
	};

	var askServer = function( field, submitCallback )
	{
		var affectedFields = getFieldCollection( field );

		validationVisuals.onBeforeValidationCheck( affectedFields );
		triggerLoadEvents( affectedFields );

		$.ajax( {
			url: ajaxURL,
			type: "POST",
			async: true,
			data: getParameters( getUniquelyNamedFieldCollection( field ) ),
			dataType: "xml",
			success: function( data, textStatus ) {
				onResponseFromServer( data, textStatus, field, submitCallback )
			}
		});
	};

	var askClient = function( eventField, submitCallback )
	{
		var fullSuccess = true;
		var affectedFields = getUniquelyNamedFieldCollection( eventField );

		validationVisuals.onBeforeValidationCheck( affectedFields );
		triggerLoadEvents( affectedFields );

		affectedFields.each( function()
		{
			var field = $( this );

			var name = field.attr( "name" );
			var value = getValue( field );

			var f = new Field( name, value );

			var failMessages = [];
			var passMessages = [];

			$( "span.constraint." + name.stripBrackets(), context ).each( function()
			{
				var type	= $( "input[name=type]", this ).val();
				var against = $( "input[name=against]", this ).val();
				var min		= $( "input[name=min]", this ).val();
				var max		= $( "input[name=max]", this ).val();
				var fail	= $( "input[name=fail]", this ).val();
				var pass	= $( "input[name=pass]", this ).val();

				f.addConstraint(type, against, min, max);

				if( fail !== undefined )
				{
					failMessages.push( fail );
				}

				if( pass !== undefined )
				{
					passMessages.push( pass );
				}
			});

			var fieldSuccess = f.isValid();
			fullSuccess = fieldSuccess && fullSuccess;
			
			validationVisuals.onAfterValidationCheck( field, fieldSuccess, failMessages, passMessages );
			triggerResponseEvents( field, fieldSuccess );
		});

		processSubmit( submitCallback, fullSuccess, eventField );
	};

	var isClientSide = function()
	{
		return ajaxURL === undefined;
	};

	var onResponseFromServer = function( data, textStatus, eventField, submitCallback )
	{
		var receivedProperResponse = ( textStatus == "success" );

		if( receivedProperResponse )
		{
			var nsPrefix = "";
			var rootElement = $( nsPrefix + "constraint-results", data );
			var fullSuccess = rootElement.attr( "success" ) == "true";

			$( nsPrefix + "field", data ).each( function()
			{
				var name = $( this ).attr( "name" );
				var fieldSuccess = $( this ).attr( "success" ) == "true";
				var field = $( "form *[ name=" + name.escapeName() + " ], form *[ name=" + ( name + "[]" ).escapeName() + " ]" );
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

				validationVisuals.onAfterValidationCheck( field, fieldSuccess, failMessages, passMessages );
				triggerResponseEvents( field, fieldSuccess );
			});

			processSubmit( submitCallback, fullSuccess, eventField );
		}
	};

	var processSubmit = function( submitCallback, fullSuccess, eventField )
	{
		if( submitCallback )
		{
			submitCallback( fullSuccess );
		}
		else
		{
			if( eventField.is( "form" ) )
			{
				allowOnNextSubmitEvent = fullSuccess;

				if( fullSuccess )
				{
					$( context ).submit();
				}
			}
		}
	}

	var getParameters = function( fieldCollection )
	{
		var fieldList = {};

		fieldCollection.each( function()
		{
			fieldList[ this.name.stripBrackets() ] = getValue( $( this ) );

			getDependencyFieldCollection( this.name ).each( function()
			{
				fieldList[ this.name.stripBrackets() ] = getValue( $( this ) );
			});
		});

		return( fieldList );
	};

	var getUniquelyNamedFieldCollection = function( field )
	{
		var fieldCollection = getFieldCollection( field );
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

	var getFieldCollection = function( field )
	{
		var fieldCollection = null;
		if( field.is( ":submit" ) || field.is( "form" ) )
		{
			fieldCollection = $( "input[ type != 'hidden' ], form textarea, form select", context );
		}
		else
		{
			fieldCollection = $( "*[ name=" + field.attr( "name" ) + " ]", context );
		}

		return( fieldCollection );
	};

	var getDependencyFieldCollection = function( name )
	{
		var dependencyFieldCollection = $( "input[ name=" + ( name.stripBrackets() + "--dependency[]" ).escapeName() + " ]", context ).map( function()
		{
			return( $( "*[ name=" + this.value.escapeName() + " ]", context ).get() );
		});

		return( dependencyFieldCollection );
	};

	var getValue = function( field )
	{
		var val = "NULL";
		if( field.is( ":radio" ) || field.is( ":checkbox" ) )
		{
			//var checkedFields = $( "input[ name=" + field.attr( "name" ).escapeName() + " ]:checked", context );
			// escapeName was removed for the match to occur
			var checkedFields = $( "input[ name=" + field.attr( "name" ) + " ]:checked", context );

			if( checkedFields.length > 0 )
			{
				val = checkedFields.map( function() {
					return( $( this ).val() );
				}).get();
			}
			else
			{
				val = "NULL";
			}
		}
		else if( field.is( "select[ multiple=true ]" ) )
		{
			val = field.val();
			if( val == null || val == undefined )
			{
				val = "NULL";
			}
		}
		else
		{
			val = field.val();
		}

		if( val == null || val == undefined )
		{
			val = "";
		}

		return( val );
	};

	var triggerLoadEvents = function( fieldCollection )
	{
		fieldCollection.each( function()
		{
			$( this ).trigger( "loadstart.validation" );
		});
	};

	var triggerResponseEvents = function( field, success )
	{
		field.trigger( "loadcomplete.validation" );
		field.trigger( success ? "pass.validation" : "fail.validation" );
	};

	var triggerResetEvent = function( field )
	{
		field.trigger( "reset.validation" );
	};

	constructor();
}