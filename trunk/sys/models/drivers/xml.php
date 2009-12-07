<?php

class XmlModelDriver extends ModelDriver
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Load( $parameter, $data = null )
	{
		if( is_a( $parameter, "Model" ) )
		{
			// Treat parameter as an instance of a model

			$driver		= &$parameter->GetDriverInstance();
			$xmlData	= $driver->GetXML( true );
		}
		else
		{
			if( strpos( $parameter, "</" ) !== false )
			{
				// Treat parameter as raw XML.  However, we must strip out the xml declaration and xmvc:root tag if present.
				// Otherwise this model won't play nice with other models.

				$xmlData = $parameter;

				$xmlData = $this->StripRootTags( $xmlData );
			}
			else
			{
				// Treat parameter as XML model name

				$modelPaths = array();

				$modelPaths[ 0 ] = "models/" . $parameter . ".xml";
				$modelPaths[ 1 ] = "models/" . $parameter . ".xml.php";

				foreach( $modelPaths as $modelPath )
				{
					$xmlModelFile = Loader::Prioritize( $modelPath );

					if( ! is_null( $xmlModelFile ) )
					{
						break;
					}
				}

				$xmlData = $this->LoadModelXML( $xmlModelFile, $data );
			}
		}

		$this->SetXML( $xmlData );

		return( $xmlData );
	}
}

?>