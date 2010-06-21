<?php

namespace System\Drivers;

use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\View;
use System\Libraries\DB;

class SQLModelDriver extends ModelDriver implements IModelDriver
{
	private $currentQueryName = null;
	private $currentParameters = null;
	private $queriesModel;

	public function __construct( $xmlModelName, $namespace = null, $data = null )
	{
		parent::__construct();

		$this->pushDebugInformation( "xmlModelName", $xmlModelName );
		$this->pushDebugInformation( "namespace", $namespace );
		$this->pushDebugInformation( "data", $data );

		$this->rootElement = $this->createElementNS( View::namespaceXML, "xmvc:database" );
		$this->appendChild( $this->rootElement );

		DB::connect();
		DB::selectDB();

		$this->loadSQLFromModel( $xmlModelName, $namespace, $data );
	}

	private function loadSQLFromModel( $xmlModelName, $namespace, $data )
	{
		$this->queriesModel = new XMLModelDriver( $xmlModelName . ".sql", $namespace, $data );
	}

	public function useQuery( $queryName )
	{
		$this->currentQueryName = $queryName;
	}

	public function setParameters( $parameters = null )
	{
		$this->currentParameters = $parameters;
	}

	public function addParameter( $parameter )
	{
		if( is_null( $this->currentParameters ) )
		{
			$this->currentParameters = array();
		}

		$this->currentParameters[] = $parameter;
	}

	public function isSuccessful()
	{
		$success = $this->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:result/xmvc:success" )->item( 0 )->nodeValue == "true";

		return $success;
	}

	public function getSingleRowValue( $field )
	{
		$value = $this->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:result/xmvc:row/xmvc:column[@name='" . $field . "']" )->item( 0 )->nodeValue;

		return $value;
	}

	private function getSQL()
	{
		$sql = trim( $this->queriesModel->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:sql" )->item( 0 )->nodeValue );

		return $sql;
	}

	public function execute( $parameters = null )
	{
		if( is_null( $parameters ) )
		{
			$parameters = $this->currentParameters;
		}

		$rowList = DB::executePreparedStatement( $this->getSQL(), $parameters );

		$this->transformForeignToXML( $rowList );
	}

	public function transformForeignToXML()
	{
		$rowList = func_get_arg( 0 );

		if( ! is_null( $this->currentQueryName ) )
		{
			$queryElement = $this->createElementNS( View::namespaceXML, "xmvc:query" );
			$nameAttribute = $this->createAttribute( "name" );
			$nameAttribute->value = $this->currentQueryName;
			$queryElement->appendChild( $nameAttribute );
			$this->rootElement->appendChild( $queryElement );

			if( ! is_null( $rowList ) )
			{
				$resultElement = $this->createElementNS( View::namespaceXML, "xmvc:result" );
				$queryElement->appendChild( $resultElement );

				if( $rowList === true || $rowList === false )
				{
					$successElement = $this->createElementNS( View::namespaceXML, "xmvc:success" );
					$valueNode = $this->createTextNode( $rowList ? "true" : "false" );
					$successElement->appendChild( $valueNode );
					$resultElement->appendChild( $successElement );
				}
				else
				{
					foreach( $rowList as $row )
					{
						$rowElement = $this->createElementNS( View::namespaceXML, "xmvc:row" );
						$resultElement->appendChild( $rowElement );

						foreach( $row as $key => $value )
						{
							$columnElement = $this->createElementNS( View::namespaceXML, "xmvc:column" );
							$nameAttribute = $this->createAttribute( "name" );
							$valueNode = $this->createCDATASection( $value );
							$nameAttribute->value = $key;
							$columnElement->appendChild( $nameAttribute );
							$columnElement->appendChild( $valueNode );
							$rowElement->appendChild( $columnElement );
						}
					}
				}
			}

			parent::transformForeignToXML();
		}
		else
		{
			trigger_error( "SQL query name not specified. Use the UseQuery method.", E_USER_ERROR );
		}
	}
}