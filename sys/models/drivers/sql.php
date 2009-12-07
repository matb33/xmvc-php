<?php

require_once( SYS_PATH . "libraries/db.php" );

class SqlModelDriver extends ModelDriver
{
	private $currentQueryName	= null;
	private $currentParameters	= null;

	private $DB;

	public function __construct()
	{
		parent::__construct();

		$this->DB = new DB();

		$this->DB->Connect();
		$this->DB->SelectDB();
	}

	public function Load( $xmlModelName, $data = null )
	{
		$xmlModelFile = Loader::Prioritize( "models/" . $xmlModelName . ".sql.xml" );

		$xmlData = $this->LoadModelXML( $xmlModelFile, $data );
		$this->SetXML( $xmlData );

		return( $xmlData );
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

		$query		= $result->xml->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:result/xmvc:success[1]" );
		$success	= ( $query->item( 0 )->nodeValue == "true" );

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

	public function Execute( $parameters = null, $model = "db.execute_prepared_stmt" )
	{
		$data = array(
			"DB"			=> $this->DB,
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
		$query	= $this->xPath->query( "//xmvc:query[@name='" . $this->currentQueryName . "']/xmvc:sql[1]" );
		$sql	= trim( $query->item( 0 )->nodeValue );

		return( $sql );
	}
}

?>