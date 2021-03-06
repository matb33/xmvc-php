<?php

namespace xMVC\Sys;

class XSL
{
	private static $processor = null;
	private static $XMLDocument = null;
	private static $XSLDocument = null;
	private static $originalWorkingFolder = null;

	public static function Transform( $xmlData, $xslData, $workingFolder = null )
	{
		self::$processor = self::GetProcessor();

		self::SetupWorkingFolder( $workingFolder );

		self::$XMLDocument->loadXML( $xmlData );
		self::$XSLDocument->loadXML( $xslData );

		self::$processor->importStyleSheet( self::$XSLDocument );

		$result = self::$processor->transformToXML( self::$XMLDocument );

		self::RestoreWorkingFolder();

		if( empty( $result ) )
		{
			trigger_error( self::DumpErrors(), E_USER_ERROR );
		}

		return $result;
	}

	private static function GetProcessor()
	{
		if( is_null( self::$processor ) )
		{
			self::$processor = new \XSLTProcessor();

			libxml_use_internal_errors( true );

			self::SetupPHPFunctions();
			self::SetupProfiling();

			self::$XMLDocument = new \DOMDocument( "1.0", "UTF-8" );
			self::$XSLDocument = new \DOMDocument( "1.0", "UTF-8" );

			self::$XMLDocument->preserveWhiteSpace = Config::$data[ "modelDriverPreserveWhiteSpace" ];
			self::$XSLDocument->preserveWhiteSpace = Config::$data[ "modelDriverPreserveWhiteSpace" ];

			self::$XMLDocument->formatOutput = Config::$data[ "modelDriverFormatOutput" ];
			self::$XSLDocument->formatOutput = Config::$data[ "modelDriverFormatOutput" ];
		}

		return( self::$processor );
	}

	public static function ResetProcessor()
	{
		self::$processor = null;
	}

	private static function SetupPHPFunctions()
	{
		if( isset( Config::$data[ "enableXSLTPHPFunctions" ] ) )
		{
			if( Config::$data[ "enableXSLTPHPFunctions" ] )
			{
				if( isset( Config::$data[ "restrictXSLTPHPFunctions" ] ) && is_array( Config::$data[ "restrictXSLTPHPFunctions" ] ) && count( Config::$data[ "restrictXSLTPHPFunctions" ] ) )
				{
					self::XSLTPHPFunctionInvocationHack();
					self::$processor->registerPHPFunctions( Config::$data[ "restrictXSLTPHPFunctions" ] );
				}
				else
				{
					self::$processor->registerPHPFunctions();
				}
			}
		}
	}

	private static function SetupProfiling()
	{
		if( isset( Config::$data[ "enableXSLTProfiling" ] ) )
		{
			if( Config::$data[ "enableXSLTProfiling" ] )
			{
				if( isset( Config::$data[ "XSLTProfilingFilename" ] ) )
				{
					if( strlen( trim( Config::$data[ "XSLTProfilingFilename" ] ) ) > 0 )
					{
						self::$processor->setProfiling( Config::$data[ "XSLTProfilingFilename" ] );
					}
				}
			}
		}
	}

	private static function SetupWorkingFolder( $workingFolder )
	{
		self::$originalWorkingFolder = getcwd();

		if( ! is_null( $workingFolder ) )
		{
			chdir( $workingFolder );
		}
	}

	private static function RestoreWorkingFolder()
	{
		chdir( self::$originalWorkingFolder );
	}

	private static function DumpErrors()
	{
		// TO-DO: Clean this up

		$err = "";

		$errors = libxml_get_errors();

		if( ! empty( $errors ) )
		{
			foreach( $errors as $error )
			{
				switch( $error->level )
				{
					case LIBXML_ERR_WARNING:
						$err .= "<strong>Warning [" . $error->code . "]</strong>: ";
					break;

					case LIBXML_ERR_ERROR:
						$err .= "<strong>Error [" . $error->code . "]</strong>: ";
					break;

					case LIBXML_ERR_FATAL:
						$err .= "<strong>Fatal Error [" . $error->code . "]</strong>: ";
					break;

					default:
						$err .= "<strong>Unknown Error [" . $error->code . "]</strong>: ";
				}

				$err .= trim( $error->message ) . "<br/ >";
				$err .= "Line: [" . $error->line . "]<br/ >";
				$err .= "Column: [" . $error->column . "]<br/ >";

				if( $error->file )
				{
					$err .= "File: [" . $error->file . "]<br />";
				}

				$err .=  "<hr />";
			}
		}

		echo "<h1>XSL::DumpErrors</h1><pre>";
		print_r( $err );
		echo "</pre>";

		return $err;
	}

	public static function XSLTPHPFunctionInvocationHack()
	{
		// This method is here as a hack to have PHP load this class, because calling php:function in XSLT without
		// first having this class loaded won't work, despite autoloading mechanisms being in place.
		foreach( Config::$data[ "restrictXSLTPHPFunctions" ] as $functionName )
		{
			if( !function_exists( $functionName ) )
			{
				$functionName = strstr( $functionName, '::', true );
				$tempInstance = new $functionName;
				unset( $tempInstance, $functionName );
			}
		}
	}
}