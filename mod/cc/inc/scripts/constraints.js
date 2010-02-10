var Constraints = new function()
{
	this.ajaxURL = null;
	this.allowOnNextSubmitEvent = false;

	this.Initialize = function( ajaxURL, form )
	{
		this.ajaxURL = ajaxURL;
		this.form = form;	// not active -- investigate why this.form.find() barely ever works

		this.BindFieldEvents();
	};

	this.BindFieldEvents = function()
	{
		$( "form input[ type='text' ], input[ type='password' ], textarea, select" ).change( function()
		{
			Constraints.AskServer( $( this ) );
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

	this.AskServer = function( field )
	{
		ConstraintVisuals.OnAskServer( this.GetFieldCollection( field ) );

		$.ajax( {
			url: this.ajaxURL,
			type: "POST",
			async: true,
			data: this.GetParameters( this.GetUniquelyNamedFieldCollection( field ) ),
			dataType: "xml",
			success: function( data, textStatus ) { Constraints.OnResponseFromServer( data, textStatus, field ) }
		});
	};

	this.OnResponseFromServer = function( data, textStatus, eventField )
	{
		var receivedProperResponse = ( textStatus == "success" );

		if( receivedProperResponse )
		{
			var rootElement = $( "cc\\:root", data );
			var fullSuccess = rootElement.attr( "success" ) == "true";

			$( "cc\\:field", data ).each( function()
			{
				var name = $( this ).attr( "name" );
				var fieldSuccess = $( this ).attr( "success" ) == "true";
				var field = $( "form *[ name='" + Constraints.EscapeName( name ) + "' ], form *[ name='" + Constraints.EscapeName( name ) + "\\[\\]' ]" );

				ConstraintVisuals.OnFieldConstraintResult( field, fieldSuccess );
			});

			if( eventField.is( "form" ) )
			{
				this.allowOnNextSubmitEvent = fullSuccess;

				if( fullSuccess )
				{
					$( "form" ).submit();
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

			if( lookup.indexOf( name ) != -1 )
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
		return( name.replace( "[", "\\[" ).replace( "]", "\\]" ) );
	};

	this.StripBrackets = function( name )
	{
		return( name.replace( "[", "" ).replace( "]", "" ).replace( "\\[", "" ).replace( "\\]", "" ) );
	}
}

var ConstraintVisuals = new function()
{
	this.OnAskServer = function( fieldCollection )
	{
		fieldCollection.each( function()
		{
			var closestLabel = $( this ).closest( "label" );

			closestLabel.addClass( "constraint-loading" );
			closestLabel.removeClass( "constraint-success" );
			closestLabel.removeClass( "constraint-fail" );
		});
	};

	this.OnFieldConstraintResult = function( field, valid )
	{
		var closestLabel = field.closest( "label" );

		closestLabel.removeClass( "constraint-loading" );

		if( valid )
		{
			closestLabel.addClass( "constraint-success" );
		}
		else
		{
			closestLabel.addClass( "constraint-fail" );
		}
	};
}