<?php

class XMLModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Load( $parameter, $data = null )
	{
		$this->TransformForeignToXML( $parameter, $data );
	}

	public function TransformForeignToXML()
	{
		$parameter = func_get_arg( 0 );
		$data = func_get_arg( 1 );

		if( is_a( $parameter, "Model" ) )
		{
			// Treat parameter as an instance of a model

			$xmlData = $parameter->GetXMLForStacking();
		}
		else
		{
			if( strpos( $parameter, "</" ) !== false )
			{
				// Treat parameter as raw XML.  However, we must strip out the xml declaration and xmvc:root tag if present.
				// Otherwise this model won't play nice with other models.

				$xmlData = $this->StripRootTags( $parameter );
			}
			else
			{
				// Treat parameter as XML model name

				if( ( $xmlModelFile = Loader::Prioritize( "models/" . $parameter . ".xml" ) ) !== false )
				{
					$xmlData = $this->LoadModelXML( $xmlModelFile, $data );
				}
				else
				{
					trigger_error( "XML model [" . $parameter . "] not found", E_USER_ERROR );
				}
			}
		}

		$this->SetXML( $xmlData );

		return( $xmlData );
	}
}

?>