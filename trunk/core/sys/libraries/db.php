<?php

class DB
{
	var $dbConfig	= array();
	var $link		= null;

	function DB()
	{
		$this->dbConfig[ "databaseHost" ] = Config::Value( "databaseHost" );
		$this->dbConfig[ "databaseName" ] = Config::Value( "databaseName" );
		$this->dbConfig[ "databaseUser" ] = Config::Value( "databaseUser" );
		$this->dbConfig[ "databasePass" ] = Config::Value( "databasePass" );
		$this->dbConfig[ "databaseType" ] = Config::Value( "databaseType" );
	}

	function ExecutePreparedStatement( $sql, $parameters = null )
	{
		$rowList = null;

		switch( $this->dbConfig[ "databaseType" ] )
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

				if( $stmt = mysqli_prepare( $this->link, $sql ) )
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

	function ExecuteMultiQuery( $sql )
	{
		$rowLists = null;

		switch( $this->dbConfig[ "databaseType" ] )
		{
			case "mysqli":

				if( mysqli_multi_query( $this->link, $sql ) )
				{
					$rowLists = array();

					for( ; ; )
					{
						$result = mysqli_use_result( $this->link );

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

						if( ! mysqli_more_results( $this->link ) )
						{
							break;
						}

						mysqli_next_result( $this->link );
					}
				}
				else
				{
					trigger_error( "Could not execute query " . $sql . ": " . mysqli_error( $this->link ), E_USER_ERROR );
				}

			break;
		}

		return( $rowLists );
	}

	function Connect()
	{
		switch( $this->dbConfig[ "databaseType" ] )
		{
			case "mysql":

				$this->link = mysql_connect( $this->dbConfig[ "databaseHost" ], $this->dbConfig[ "databaseUser" ], $this->dbConfig[ "databasePass" ] ) or trigger_error( "Could not connect: " . mysql_error(), E_USER_ERROR );

			break;

			case "mysqli":

				$this->link = mysqli_init();

				//mysqli_options( $this->link, MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=0" );
				//mysqli_options( $this->link, MYSQLI_OPT_CONNECT_TIMEOUT, 5 );

				mysqli_real_connect( $this->link, $this->dbConfig[ "databaseHost" ], $this->dbConfig[ "databaseUser" ], $this->dbConfig[ "databasePass" ], $this->dbConfig[ "databaseName" ] );

				if( mysqli_connect_errno() )
				{
					trigger_error( "Connect failed: " . mysqli_connect_error(), E_USER_ERROR );
				}

			break;
		}
	}

	function SelectDB()
	{
		switch( $this->dbConfig[ "databaseType" ] )
		{
			case "mysql":

				mysql_select_db( $this->dbConfig[ "databaseName" ] ) or trigger_error( "Could not select database " . $this->dbConfig[ "databaseName" ] . ": " . mysql_error(), E_USER_ERROR );

			break;

			case "mysqli":
			break;
		}
	}

	function Query( $sql )
	{
		switch( $this->dbConfig[ "databaseType" ] )
		{
			case "mysql":

				$result = mysql_query( $sql ) or trigger_error( "Could not execute query " . $sql . ": " . mysql_error(), E_USER_ERROR );

			break;

			case "mysqli":

				$result = mysqli_query( $this->link, $sql ) or trigger_error( "Could not execute query " . $sql . ": " . mysqli_error( $this->link ), E_USER_ERROR );

			break;
		}

		return( $result );
	}

	function FetchArray( $result, $resultType = null )
	{
		switch( $this->dbConfig[ "databaseType" ] )
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

	function Close()
	{
		switch( $this->dbConfig[ "databaseType" ] )
		{
			case "mysql":

				mysql_close( $this->link );

			break;

			case "mysqli":

				mysqli_close( $this->link );

			break;
		}
	}
}

?>