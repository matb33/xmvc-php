<?php

class DB
{
	static private $link = null;

	public static function ExecutePreparedStatement( $sql, $parameters = null )
	{
		$rowList = null;

		switch( Config::$data[ "databaseType" ] )
		{
			case "mysql":

				$prepQueryName = "prep_query_" . substr( md5( rand( 10000, 99999 ) . date( "YmdHis" ) ), 0, 8 );

				$this->Query( "PREPARE " . $prepQueryName . " FROM \"" . $sql . "\"" );

				if( is_array( $parameters ) )
				{
					$params = array();

					foreach( $parameters as $key => $value )
					{
						$this->Query( "SET @param_" . $key . " = \"" . mysql_real_escape_string( $value ) . "\"" );

						$params[] = "@param_" . $key;
					}

					$result = $this->Query( "EXECUTE " . $prepQueryName . " USING " . implode( ", ", $params ) );
				}
				else
				{
					$result = $this->Query( "EXECUTE " . $prepQueryName );
				}

				if( is_resource( $result ) )
				{
					$rowList = array();

					while( $row = $this->FetchArray( $result, MYSQL_ASSOC ) )
					{
						$rowList[] = $row;
					}

					$returnValue = $rowList;
				}
				else
				{
					if( $result === true || $result === false )
					{
						$returnValue = $result;
					}
				}

				$this->Query( "DEALLOCATE PREPARE " . $prepQueryName );

			break;

			case "mysqli":

				if( $stmt = mysqli_prepare( self::$link, $sql ) )
				{
					if( is_array( $parameters ) )
					{
						$types		= array();
						$vars		= array();
						$boundVars	= array();

						foreach( $parameters as $key => $value )
						{
							if( is_integer( $value ) )
							{
								$type = "i";
							}
							else if( is_double( $value ) )
							{
								$type = "d";
							}
							else
							{
								$type = "s";
								$value = ( string )$value;
							}

							$varName	= "\$boundVars[ \"" . $key . "\" ]";

							$types[]	= $type;
							$vars[]		= $varName;

							$boundVars[ $key ] = $value;
						}

						$typesJoined	= implode( "", $types );
						$varsJoined		= ", " . implode( ", ", $vars );

						eval( "mysqli_stmt_bind_param( \$stmt, \$typesJoined" . $varsJoined . " );" );

						$success = mysqli_stmt_execute( $stmt );
					}
					else
					{
						$success = mysqli_stmt_execute( $stmt );
					}

					$metaData = mysqli_stmt_result_metadata( $stmt );

					if( $metaData !== false )
					{
						$fieldInfo = mysqli_fetch_fields( $metaData );

						$fieldNames		= array();
						$fieldVars		= array();
						$boundResult	= array();

						foreach( $fieldInfo as $index => $field )
						{
							$fieldNames[ $index ]	= $field->name;
							$fieldVars[ $index ]	= "\$boundResult[ \"" . $field->name . "\" ]";
						}

						$fieldVarsJoined = ", " . implode( ", ", $fieldVars );

						foreach( $fieldNames as $index => $fieldName )
						{
							eval( "mysqli_stmt_bind_result( \$stmt" . $fieldVarsJoined . " );" );
						}

						$rowList = array();

						while( mysqli_stmt_fetch( $stmt ) )
						{
							$row = array();

							foreach( $fieldNames as $fieldName )
							{
								$row[ $fieldName ] = $boundResult[ $fieldName ];
							}

							$rowList[] = $row;
						}

						$returnValue = $rowList;
					}
					else
					{
						$returnValue = $success;
					}

					mysqli_stmt_close( $stmt );
				}

			break;
		}

		return( $returnValue );
	}

	public static function ExecuteMultiQuery( $sql )
	{
		$rowLists = null;

		switch( Config::$data[ "databaseType" ] )
		{
			case "mysqli":

				if( mysqli_multi_query( self::$link, $sql ) )
				{
					$rowLists = array();

					for( ; ; )
					{
						$result = mysqli_use_result( self::$link );

						if( is_object( $result ) )
						{
							$rowList = array();

							while( $row = $this->FetchArray( $result, MYSQLI_ASSOC ) )
							{
								$rowList[] = $row;
							}

							mysqli_free_result( $result );
						}
						else
						{
							if( $result === true || $result === false )
							{
								$rowList = $result;
							}
						}

						$rowLists[] = $rowList;

						if( ! mysqli_more_results( self::$link ) )
						{
							break;
						}

						mysqli_next_result( self::$link );
					}
				}
				else
				{
					trigger_error( "Could not execute query " . $sql . ": " . mysqli_error( self::$link ), E_USER_ERROR );
				}

			break;
		}

		return( $rowLists );
	}

	public static function Connect()
	{
		switch( Config::$data[ "databaseType" ] )
		{
			case "mysql":

				self::$link = mysql_connect( Config::$data[ "databaseHost" ], Config::$data[ "databaseUser" ], Config::$data[ "databasePass" ] ) or trigger_error( "Could not connect: " . mysql_error(), E_USER_ERROR );

			break;

			case "mysqli":

				self::$link = mysqli_init();

				mysqli_real_connect( self::$link, Config::$data[ "databaseHost" ], Config::$data[ "databaseUser" ], Config::$data[ "databasePass" ], Config::$data[ "databaseName" ] );

				if( mysqli_connect_errno() )
				{
					trigger_error( "Connect failed: " . mysqli_connect_error(), E_USER_ERROR );
				}

			break;
		}
	}

	public static function SelectDB()
	{
		switch( Config::$data[ "databaseType" ] )
		{
			case "mysql":

				mysql_select_db( Config::$data[ "databaseName" ] ) or trigger_error( "Could not select database " . Config::$data[ "databaseName" ] . ": " . mysql_error(), E_USER_ERROR );

			break;

			case "mysqli":
			break;
		}
	}

	public static function Query( $sql )
	{
		switch( Config::$data[ "databaseType" ] )
		{
			case "mysql":

				$result = mysql_query( $sql ) or trigger_error( "Could not execute query " . $sql . ": " . mysql_error(), E_USER_ERROR );

			break;

			case "mysqli":

				$result = mysqli_query( self::$link, $sql ) or trigger_error( "Could not execute query " . $sql . ": " . mysqli_error( self::$link ), E_USER_ERROR );

			break;
		}

		return( $result );
	}

	public static function FetchArray( $result, $resultType = null )
	{
		switch( Config::$data[ "databaseType" ] )
		{
			case "mysql":

				$resultType = ( is_null( $resultType ) ? MYSQL_ASSOC : $resultType );

				$row = mysql_fetch_array( $result, $resultType );

			break;

			case "mysqli":

				$resultType = ( is_null( $resultType ) ? MYSQLI_ASSOC : $resultType );

				$row = mysqli_fetch_array( $result, $resultType );

			break;
		}

		return( $row );
	}

	public static function Close()
	{
		switch( Config::$data[ "databaseType" ] )
		{
			case "mysql":

				mysql_close( self::$link );

			break;

			case "mysqli":

				mysqli_close( self::$link );

			break;
		}
	}
}

?>