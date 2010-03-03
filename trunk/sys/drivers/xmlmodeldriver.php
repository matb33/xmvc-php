<?php

namespace xMVC\Sys;

class XMLModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct( $parameter = null, $namespace = null, $data = null )
	{
		parent::__construct();

		$this->TransformForeignToXML( $parameter, $namespace, $data );
	}

	public function TransformForeignToXML()
	{
		$parameter = func_get_arg( 0 );
		$namespace = func_get_arg( 1 );
		$data = func_get_arg( 2 );

		if( is_null( $parameter ) )
		{
			$xmlData = "";
		}
		else
		{
			if( is_a( $parameter, "ModelDriver" ) )
			{
				// Treat parameter as an instance of a model

				$xmlData = $parameter->GetXMLForStacking();
			}
			else
			{
				if( strpos( $parameter, "</" ) === false )
				{
					if( preg_match( '/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i', $parameter ) )
					{
						// Treat parameter as a URL that we attempt to read as raw XML
						$parameter = $this->file_get_contents_utf8( $parameter );
					}
					elseif( file_exists( $parameter ) )
					{
						// Treat parameter as a file on the file system
						$parameter = $this->file_get_contents_utf8( $parameter );
					}
				}

				if( strpos( $parameter, "</" ) !== false )
				{
					// Treat parameter as raw XML.  However, we must strip out the xml declaration and xmvc:root tag if present.
					// Otherwise this model won't play nice with other models when stacked.

					$xmlData = Normalize::StripXMLRootTags( $parameter );
				}
				else
				{
					// Treat parameter as XML model name

					$parameter = Loader::AssignDefaultNamespace( $parameter, $namespace );

					if( ( $xmlModelFile = Loader::Resolve( Loader::modelFolder, $parameter, Loader::modelExtension ) ) !== false )
					{
						$xmlData = $this->LoadModelXML( $xmlModelFile, $data );
					}
					else
					{
						trigger_error( "XML model [" . $parameter . "] not found", E_USER_ERROR );
					}
				}
			}
		}

		$this->SetXML( $xmlData );

		return( $xmlData );
	}

	private function file_get_contents_utf8( $filename )
	{
		$contents = file_get_contents( $filename );

		return( mb_convert_encoding( $contents, "UTF-8", mb_detect_encoding( $contents, "UTF-8, ISO-8859-1", true ) ) );
	}
}

?>