<?php

class SqlModelDriver extends ModelDriver implements ModelDriverInterface
{
	private $currentQueryName = null;
	private $currentParameters = null;

	public function __construct()
	{
		parent::__construct();

		DB::Connect();
		DB::SelectDB();
	}

	public function Load( $xmlModelName, $data = null )
	{
		if( ( $xmlModelFile = Loader::Prioritize( "models/" . $xmlModelName . ".sql.xml" ) ) !== false )
		{
			$xmlData = $this->LoadModelXML( $xmlModelFile, $data );

			$this->SetXML( $xmlData );

			return( $xmlData );
		}
		else
		{
			trigger_error( "SQL XML model [" . $xmlModelName . "] not found", E_USER_ERROR );
		}
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
		$result = new Model( "xml" );
		$result->xml->Load( $this->GetXML() );

		$query = $result->xml->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:result/xmvc:success[1]" );
		$success = ( $query->item( 0 )->nodeValue == "true" );

		unset( $result );

		return( $success );
	}

	public function GetSingleRowValue( $field )
	{
		$result = new Model( "xml" );
		$result->xml->Load( $this->GetXML() );

		$query = $result->xml->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:result/xmvc:row/xmvc:" . $field . "[1]" );
		$value = $query->item( 0 )->nodeValue;

		unset( $result );

		return( $value );
	}

	public function Execute( $parameters = null, $model = "db-execute-prepared-stmt" )
	{
		$data = array(
			"queryName"		=> $this->currentQueryName,
			"sqlQuery"		=> $this->GetSQL(),
			"parameters"	=> ( is_null( $parameters ) ? $this->currentParameters : $parameters )
		);

		$query = new Model( "xml" );
		$query->xml->Load( $model, $data );

		// Instead of passing our XML through an XSL view, we are returning the results of the query
		// as-is, in its current XML form.  In order to do this, we fetch the results directly from
		// the model object using the GetXML method:

		$xmlData = $query->xml->GetXML( true );
		$this->SetXML( $this->GetXML( true ) . $xmlData );

		return( $xmlData );
	}

	private function GetSQL()
	{
		$query = $this->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:sql[1]" );
		$sql = trim( $query->item( 0 )->nodeValue );

		return( $sql );
	}
}

?>