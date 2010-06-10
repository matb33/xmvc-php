function Field( name, value )
{
	var constraints = [];

	this.getName = function() { return name; };
	this.getValue = function() { return value; };

	this.addConstraint = function( type, against, min, max )
	{
		var c = new Constraint( type, name, value );
		c.setAgainst( against );
		c.setMin( min );
		c.setMax( max );

		constraints.push( c );
	}

	this.isValid = function()
	{
		for( var c in constraints )
		{
			var constraint = constraints[ c ];
			if( !constraint.isValid() )
			{
				return false;
			}
		}

		return true;
	}
}