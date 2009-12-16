<?php

namespace xMVC;

class SQLModelDriver extends ModelDriver implements ModelDriverInterface
{
	private $currentQueryName = null;
	private $currentParameters = null;
	private $queriesModel;

	public function __construct( $xmlModelName, $namespace = __NAMESPACE__, $data = null )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Core::$namespace, "xmvc:database" );
		$this->appendChild( $this->rootElement );

		DB::Connect();
		DB::SelectDB();

		$this->LoadSQLFromModel( $xmlModelName, $namespace, $data );
	}

	private function LoadSQLFromModel( $xmlModelName, $namespace, $data )
	{
		$this->queriesModel = new XMLModelDriver( $xmlModelName . ".sql", $namespace, $data );
	}

	public function SetQuery( $queryName )
	{
		$this->currentQueryName = $queryName;
	}

	public function SetParameters( $parameters = null )
	{
		$this->currentParameters = $parameters;
	}

	public function IsSuccessful()
	{
		$success = $this->xPath->query( "//xmvc:query[@xmvc:name='" . $this->currentQueryName . "']/xmvc:result/xmvc:success" )->item( 0 )->nodeValue == "true";

		return( $success );
	}

	public function GetSingleRowValue( $field )
	{
		$value = $this->xPath->query( "//xmvc:query[@xmvc:name='" . $this->currentQueryName . "']/xmvc:result/xmvc:row/xmvc:column[@xmvc:name='" . $field . "']" )->item( 0 )->nodeValue;

		return( $value );
	}

	private function GetSQL()
	{
		$sql = trim( $this->queriesModel->xPath->query( "//xmvc:query[@xmvc:name='" . $this->currentQueryName . "']/xmvc:sql" )->item( 0 )->nodeValue );

		return( $sql );
	}

	public function Execute( $parameters = null )
	{
		if( is_null( $parameters ) )
		{
			$parameters = $this->currentParameters;
		}

		$rowList = DB::ExecutePreparedStatement( $this->GetSQL(), $parameters );

		$this->TransformForeignToXML( $rowList );
	}

	public function TransformForeignToXML()
	{
		$rowList = func_get_arg( 0 );

		if( ! is_null( $this->currentQueryName ) )
		{
			$queryElement = $this->createElementNS( Core::$namespace, "xmvc:query" );
			$nameAttribute = $this->createAttributeNS( Core::$namespace, "xmvc:name" );
			$nameAttribute->value = $this->currentQueryName;
			$queryElement->appendChild( $nameAttribute );
			$this->rootElement->appendChild( $queryElement );

			if( ! is_null( $rowList ) )
			{
				$resultElement = $this->createElementNS( Core::$namespace, "xmvc:result" );
				$queryElement->appendChild( $resultElement );

				if( $rowList === true || $rowList === false )
				{
					$successElement = $this->createElementNS( Core::$namespace, "xmvc:success" );
					$valueNode = $this->createTextNode( $rowList ? "true" : "false" );
					$successElement->appendChild( $valueNode );
					$resultElement->appendChild( $successElement );
				}
				else
				{
					foreach( $rowList as $row )
					{
						$rowElement = $this->createElementNS( Core::$namespace, "xmvc:row" );
						$resultElement->appendChild( $rowElement );

						foreach( $row as $key => $value )
						{
							$columnElement = $this->createElementNS( Core::$namespace, "xmvc:column" );
							$nameAttribute = $this->createAttributeNS( Core::$namespace, "xmvc:name" );
							$valueNode = $this->createCDATASection( $value );
							$nameAttribute->value = $key;
							$columnElement->appendChild( $nameAttribute );
							$columnElement->appendChild( $valueNode );
							$rowElement->appendChild( $columnElement );
						}
					}
				}
			}

			parent::TransformForeignToXML();
		}
		else
		{
			trigger_error( "SQL query name not specified. Use the SetQuery method.", E_USER_ERROR );
		}
	}
}

?>