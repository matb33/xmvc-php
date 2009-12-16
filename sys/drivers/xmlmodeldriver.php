<?php

namespace xMVC;

class XMLModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct( $parameter, $namespace = __NAMESPACE__, $data = null )
	{
		parent::__construct();

		$this->TransformForeignToXML( $parameter, $namespace, $data );
	}

	public function TransformForeignToXML()
	{
		$parameter = func_get_arg( 0 );
		$namespace = func_get_arg( 1 );
		$data = func_get_arg( 2 );

		if( is_a( $parameter, "ModelDriver" ) )
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

				if( ( $xmlModelFile = Loader::Prioritize( "models", $namespace . "\\" . $parameter, "xml" ) ) !== false )
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