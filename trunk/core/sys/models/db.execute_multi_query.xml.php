<?php

if( ! is_null( $queryName ) )
{
	?><xmvc:database><?php

	$rowLists = $DB->ExecuteMultiQuery( $sqlQuery );

	?><xmvc:query name="<?php echo( $queryName ); ?>"><?php

	if( ! is_null( $rowLists ) )
	{
		foreach( $rowLists as $key => $rowList )
		{
			?><xmvc:result index="<?php echo( $key ); ?>"><?php

			if( $rowList === true || $rowList === false )
			{
				?><xmvc:success><?php echo( $rowList ? "true" : "false" ); ?></xmvc:success><?php
			}
			else
			{
				foreach( $rowList as $row )
				{
					?><xmvc:row><?php

					foreach( $row as $key => $value )
					{
						?><xmvc:<?php echo( $key ); ?>><![CDATA[<?php echo( $value ); ?>]]></xmvc:<?php echo( $key ); ?>><?php
					}

					?></xmvc:row><?php
				}
			}

			?></xmvc:result><?php
		}
	}

	?></xmvc:query><?php

	?></xmvc:database><?php
}
else
{
	trigger_error( "Can not execute without query name.", E_USER_ERROR );
}

?>