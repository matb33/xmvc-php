function Constraint( type, name, value )
{
	var against = null;
	var min = null;
	var max = null;

	this.getType = function() { return type; };
	this.getName = function() { return name; };
	this.getValue = function() { return value; };

	this.setAgainst = function( value ) { against = value; };
	this.setMin = function( value ) { min = value; };
	this.setMax = function( value ) { max = value; };

	this.isValid = function()
	{
		switch( type )
		{
			case "regexp":
				return this.regExp();
			break;
			case "match":
				return this.match();
			break;
			case "match-field":
				return this.matchField();
			break;
			case "selected-count":
				return this.selectedCount();
			break;
			case "range":
				return this.range();
			break;
			case "email":
				return this.email();
			break;
			default:
				return false;
		}
	};

	this.email = function()
	{
		var result = /^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/.test( value );

		return result;
	};

	this.regExp = function()
	{
		return new RegExp(against).test( value );
	};

	this.match = function()
	{
		return value === against;
	};

	this.matchField = function()
	{
		// matchField does the same as match because we are comparing two values
		return match();
	};

	this.matchFieldMD5 = function()
	{
		// unsupported because user will never enter an MD5 value
		return true;
	};

	this.selectedCount = function()
	{
		if( value === "NULL" )
		{
			value = [];
		}

		return this.withinRange( value.length, min, max );
	};

	this.range = function()
	{
		return this.withinRange( value, min, max );
	};

	this.withinRange = function( value, min, max )
	{
		if( ( min === undefined ) && ( max !== undefined ) )
		{
			return value <= max;
		}
		else if( ( min !== undefined ) && ( max === undefined ) )
		{
			return value >= min;
		}
		else
		{
			return value >= min && value <= max;
		}
	};
}