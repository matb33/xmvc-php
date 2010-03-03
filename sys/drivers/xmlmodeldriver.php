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
			if( $this->IsInstanceOfModelDriver( $parameter ) )
			{
				$xmlData = $parameter->GetXMLForStacking();
			}
			else
			{
				if( $this->IsURL( $parameter ) )
				{
					$parameter = $this->file_get_contents_utf8( $parameter );
				}
				elseif( $this->IsFileOnFileSystem( $parameter ) )
				{
					$parameter = $this->file_get_contents_utf8( $parameter );
				}

				if( $this->IsRawXML( $parameter ) )
				{
					$xmlData = Normalize::StripXMLRootTags( $parameter );
				}
				else
				{
					$xmlData = $this->LoadXMLFromModel( $parameter, $namespace, $data );
				}
			}
		}

		$this->SetXML( $xmlData );

		return( $xmlData );
	}

	private function IsInstanceOfModelDriver( $parameter )
	{
		return( is_a( $parameter, "ModelDriver" ) );
	}

	private function IsRawXML( $parameter )
	{
		return( strpos( $parameter, "</" ) !== false );
	}

	private function IsURL( $parameter )
	{
		 return( preg_match( '/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i', $parameter ) );
	}

	private function IsFileOnFileSystem( $parameter )
	{
		if( ! $this->IsRawXML( $parameter ) )
		{
			return( file_exists( $parameter ) );
		}

		return( false );
	}

	private function LoadXMLFromModel( $modelName, $namespace, $data )
	{
		$modelName = Loader::AssignDefaultNamespace( $modelName, $namespace );

		if( ( $xmlModelFile = Loader::Resolve( Loader::modelFolder, $modelName, Loader::modelExtension ) ) !== false )
		{
			$xmlData = $this->LoadModelXML( $xmlModelFile, $data );
		}
		else
		{
			trigger_error( "XML model [" . $modelName . "] not found", E_USER_ERROR );
		}

		return( $xmlData );
	}

	private function file_get_contents_utf8( $filename )
	{
		$contents = file_get_contents( $filename );

		return( mb_convert_encoding( $contents, "UTF-8", mb_detect_encoding( $contents, "UTF-8, ISO-8859-1", true ) ) );
	}
}

?>