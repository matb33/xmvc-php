<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\View;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Core;
use xMVC\Sys\OutputHeaders;

use xMVC\Mod\Language\Language;

class Ccms_admin extends Ccms_root
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$this->Select();
	}

	public function Select()
	{
		$editableInstances = new EditableInstancesModelDriver();

		$view = new View( __NAMESPACE__ . "\\instance-selection" );
		$view->PushModel( $editableInstances );
		$view->RenderAsHTML();
	}

	public function Edit( $container, $content )
	{
		CCMS::$container = $container;
		CCMS::$content = $content;

		$model = new XMLModelDriver( CCMS::GetCCXFile() );

		$definedLangs = Language::GetDefinedLangs();

		$data = new StringsModelDriver();
		$data->Add( "container", $container );
		$data->Add( "content", $content );
		$data->Add( "proportion", round( 95 / count( $definedLangs ) ) );
		$data->Add( "lang", Language::GetLang() );

		foreach( $definedLangs as $lang )
		{
			$data->Add( "defined-lang", $lang );
		}

		$view = new View( Core::namespaceApp . $container . ".edit" );
		$view->PushModel( $model );
		$view->PushModel( $data );
		$view->RenderAsHTML();
	}

	public function Write( $container, $content )
	{
		CCMS::SetContainer( $container );
		CCMS::SetContent( $content );

		$data = strlen( $_POST[ "d" ] ) > 0 ? base64_decode( $_POST[ "d" ] ) : null;

		if( ! is_null( $data ) )
		{
			$transformedModel = CCMS::TransformHTML( $data );
			$contentModel = CCMS::ImportTransformation( $transformedModel, $contentModel );

			echo( CCMS::SaveCCX( $contentModel ) . " bytes written" );

			//OutputHeaders::XML();
			//echo( $contentModel->saveXML() );
		}
	}
}

?>