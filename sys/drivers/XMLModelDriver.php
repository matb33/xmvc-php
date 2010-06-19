<?php

namespace System\Drivers;

use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\FileSystem;
use System\Libraries\Normalize;
use System\Libraries\Loader;

class XMLModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct( $parameter = null, $namespace = null, $data = null )
	{
		parent::__construct();

		$this->pushDebugInformation( "parameter", $parameter );
		$this->pushDebugInformation( "namespace", $namespace );
		$this->pushDebugInformation( "data", $data );

		$this->TransformForeignToXML( $parameter, $namespace, $data );
	}

	public function TransformForeignToXML()
	{
		$parameter = func_get_arg( 0 );
		$parameter2 = func_get_arg( 1 );
		$parameter3 = func_get_arg( 2 );

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
			elseif( $this->IsDOMNode( $parameter ) )
			{
				$xmlData = $this->ExportXMLFromDOMNode( $parameter );
			}
			else
			{
				if( $this->IsURL( $parameter ) )
				{
					if( $this->IsPOSTRequest( $parameter2 ) )
					{
						$parameter = $this->POSTRequest( $parameter, $parameter2 );
					}
					else
					{
						$parameter = FileSystem::FileGetContentsUTF8( $parameter );
					}
				}
				elseif( $this->IsFileOnFileSystem( $parameter ) )
				{
					$parameter = FileSystem::FileGetContentsUTF8( $parameter );
				}

				if( $this->IsRawXML( $parameter ) )
				{
					$xmlData = Normalize::StripXMLRootTags( $parameter );
				}
				else
				{
					$xmlData = $this->LoadXMLFromModel( $parameter, $parameter2, $parameter3 );
				}
			}
		}

		$this->pushDebugInformation( "xmlData", htmlentities( $xmlData ) );

		$this->SetXML( $xmlData );

		return $xmlData;
	}

	private function IsInstanceOfModelDriver( $parameter )
	{
		return is_a( $parameter, "ModelDriver" );
	}

	private function IsDOMNode( $parameter )
	{
		return is_a( $parameter, "DOMNode" );
	}

	private function IsRawXML( $parameter )
	{
		return strpos( $parameter, "</" ) !== false;
	}

	private function IsURL( $parameter )
	{
		 return preg_match( '/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i', $parameter );
	}

	private function IsPOSTRequest( $parameter2 )
	{
		return !is_null( $parameter2 );
	}

	private function IsFileOnFileSystem( $parameter )
	{
		if( ! $this->IsRawXML( $parameter ) )
		{
			return file_exists( Normalize::Filename( $parameter ) );
		}

		return false;
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

		return $xmlData;
	}

	private function POSTRequest( $url, $data )
	{
		$ch = curl_init( $url );

		if( is_array( $data ) )
		{
			$post = array();

			foreach( $data as $key => $value )
			{
				$post[ $key ] = utf8_encode( $value );
			}

			$data = http_build_query( $post );
		}

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );

		$result = curl_exec( $ch );
		curl_close( $ch );

		return $result;
	}

	private function ExportXMLFromDOMNode( $node )
	{
		return $node->ownerDocument->saveXML( $node );
	}

	public static function Exists( $modelName, $extension = Loader::modelExtension )
	{
		return Loader::Resolve( Loader::modelFolder, $modelName, $extension ) !== false;
	}
}