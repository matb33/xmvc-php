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

		$this->transformForeignToXML( $parameter, $namespace, $data );
	}

	public function transformForeignToXML()
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
			if( $this->isInstanceOfModelDriver( $parameter ) )
			{
				$xmlData = $parameter->getXMLForAggregation();
			}
			elseif( $this->isDOMNode( $parameter ) )
			{
				$xmlData = $this->exportXMLFromDOMNode( $parameter );
			}
			else
			{
				if( $this->isURL( $parameter ) )
				{
					if( $this->isPOSTRequest( $parameter2 ) )
					{
						$parameter = $this->POSTRequest( $parameter, $parameter2 );
					}
					else
					{
						$parameter = FileSystem::fileGetContentsUTF8( $parameter );
					}
				}
				elseif( $this->isFileOnFileSystem( $parameter ) )
				{
					$parameter = FileSystem::fileGetContentsUTF8( $parameter );
				}

				if( $this->isRawXML( $parameter ) )
				{
					$xmlData = Normalize::stripXMLRootTags( $parameter );
				}
				else
				{
					$xmlData = $this->loadXMLFromModel( $parameter, $parameter2, $parameter3 );
				}
			}
		}

		$this->pushDebugInformation( "xmlData", htmlentities( $xmlData ) );

		$this->setXML( $xmlData );

		return $xmlData;
	}

	private function isInstanceOfModelDriver( $parameter )
	{
		return $parameter instanceof ModelDriver;
	}

	private function isDOMNode( $parameter )
	{
		return $parameter instanceof DOMNode;
	}

	private function isRawXML( $parameter )
	{
		return strpos( $parameter, "</" ) !== false || strpos( $parameter, "/>" ) !== false;
	}

	private function isURL( $parameter )
	{
		 return preg_match( '/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i', $parameter );
	}

	private function isPOSTRequest( $parameter2 )
	{
		return !is_null( $parameter2 );
	}

	private function isFileOnFileSystem( $parameter )
	{
		if( ! $this->isRawXML( $parameter ) )
		{
			return file_exists( Normalize::filename( $parameter ) );
		}

		return false;
	}

	private function loadXMLFromModel( $modelName, $namespace, $data )
	{
		$modelName = Loader::assignDefaultNamespace( $modelName, $namespace, Loader::modelFolder );

		if( ( $xmlModelFile = Loader::resolve( Loader::modelFolder, $modelName, Loader::modelExtension ) ) !== false )
		{
			$xmlData = $this->loadModelXML( $xmlModelFile, $data );
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

	private function exportXMLFromDOMNode( $node )
	{
		return $node->ownerDocument->saveXML( $node );
	}

	public static function exists( $modelName, $extension = Loader::modelExtension )
	{
		return Loader::resolve( Loader::modelFolder, $modelName, $extension ) !== false;
	}
}