<?php

if( ! function_exists( "RecursiveListing" ) )
{
	function RecursiveListing( $listing )
	{
		foreach( array_keys( $listing ) as $folder )
		{
			?><xmvc:folder name="<?php echo( $folder ); ?>"><?php

				foreach( $listing[ $folder ] as $name => $data )
				{
					if( $name != ":FOLDERS:" && $name != ":FILES:" )
					{
						?><xmvc:meta name="<?php echo( $name ); ?>"><![CDATA[<?php echo( ( string )$data ); ?>]]></xmvc:meta><?php
					}
				}

				if( isset( $listing[ $folder ][ ":FOLDERS:" ] ) && count( $listing[ $folder ][ ":FOLDERS:" ] ) )
				{
					RecursiveListing( $listing[ $folder ][ ":FOLDERS:" ] );
				}

				if( isset( $listing[ $folder ][ ":FILES:" ] ) && count( $listing[ $folder ][ ":FILES:" ] ) )
				{
					foreach( $listing[ $folder ][ ":FILES:" ] as $filename => $meta )
					{
						?><xmvc:file name="<?php echo( $filename ); ?>"><?php

							foreach( $meta as $name => $data )
							{
								?><xmvc:meta name="<?php echo( $name ); ?>"><![CDATA[<?php echo( ( string )$data ); ?>]]></xmvc:meta><?php
							}

						?></xmvc:file><?php
					}
				}

			?></xmvc:folder><?php
		}
	}
}

if( ! is_null( $listing ) )
{
	?><xmvc:filesystem><?php

	RecursiveListing( $listing );

	?></xmvc:filesystem><?php
}
else
{
	trigger_error( "Can not continue without non-null filesystem listing.", E_USER_ERROR );
}

?>