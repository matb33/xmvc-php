<?php

class ErrorHandler extends Root
{
	var $oldErrorHandler;
	var $errorReporting;
	var $errorTypes;
	var $errors;

	function ErrorHandler()
	{
		parent::Root();

		$this->oldErrorHandler	= set_error_handler( array( $this, "ErrorHandlerXML" ) );
		$this->errorReporting	= error_reporting();

		$this->errors			= "";

		$this->errorTypes = array(

			E_ERROR				=> "Error",
			E_WARNING			=> "Warning",
			E_PARSE				=> "Parsing Error",
			E_NOTICE			=> "Notice",
			E_CORE_ERROR		=> "Core Error",
			E_CORE_WARNING		=> "Core Warning",
			E_COMPILE_ERROR		=> "Compile Error",
			E_COMPILE_WARNING	=> "Compile Warning",
			E_USER_ERROR		=> "User Error",
			E_USER_WARNING		=> "User Warning",
			E_USER_NOTICE		=> "User Notice",
			E_STRICT			=> "Runtime Notice",
			E_RECOVERABLE_ERROR	=> "Catchable Fatal Error"

		);
	}

	function ErrorHandlerXML( $errorNumber, $errorMessage, $filename, $lineNum, $vars )
	{
		$err = "";

		if( ( $errorNumber & $this->errorReporting ) == $errorNumber )
		{
			$err .= "<xmvc:errorentry>\n";
			$err .= "\t<xmvc:datetime><![CDATA[" . date( "Y-m-d H:i:s (T)" ) . "]]></xmvc:datetime>\n";
			$err .= "\t<xmvc:errornum><![CDATA[" . $errorNumber . "]]></xmvc:errornum>\n";
			$err .= "\t<xmvc:errortype><![CDATA[" . $this->errorTypes[ $errorNumber ] . "]]></xmvc:errortype>\n";
			$err .= "\t<xmvc:errormsg><![CDATA[" . $errorMessage . "]]></xmvc:errormsg>\n";
			$err .= "\t<xmvc:scriptname><![CDATA[" . $filename . "]]></xmvc:scriptname>\n";
			$err .= "\t<xmvc:scriptlinenum><![CDATA[" . $lineNum . "]]></xmvc:scriptlinenum>\n";
			//$err .= "\t<xmvc:vars><![CDATA[" . serialize( $vars ) . "]]></xmvc:vars>\n";
			$err .= "</xmvc:errorentry>\n\n";

		}

		$this->errors .= $err;

		return( true );
	}

	function GetErrorsXML()
	{
		$errors = trim( $this->errors );

		if( strlen( $errors ) > 0 )
		{
			$errors = "<xmvc:errors>" . $errors . "</xmvc:errors>";
		}

		return( $errors );
	}
}

?>