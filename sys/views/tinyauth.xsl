<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc db dbfield fs err"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/error.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />

	<xsl:template match="xmvc:query" />

	<xsl:template match="/xmvc:root">

		<html>

			<head>
				<title>Authentication Required</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<meta name="robots" content="index, follow" />
				<meta name="generator" content="xMVC.org" />

				<style type="text/css">

					html
					{
						width: 100%;
						height: 0px;
					}

					body,
					input
					{
						font-family: "Lucida Grande", "Lucida Sans Unicode", Tahoma, Verdana, Arial, sans-serif;
						font-size: 16px;
						font-weight: normal;
					}

					img.logo
					{
						position: absolute;
						top: 16px;
						left: 16px;
					}

					form
					{
						width: 300px;
						padding: 32px;
						margin: 64px auto 0px auto;

						border: 1px solid #000000;
						background-color: #f8f8f8;

						text-align: right;
					}

					input
					{
						display: block;
						margin-bottom: 16px;
					}

					input#login,
					input#password
					{
						width: 296px;
						font-size: 22px;
					}

					input#loginbutton
					{
						display: inline;
						font-size: 18px;
					}

					label
					{
						display: block;
						font-size: 13px;
						text-align: left;
						margin-bottom: 4px;
					}

					p
					{
						display: block;
						text-align: center;
						border: 1px solid #333333;
						padding: 6px;
						margin: 0px;
					}

					p.normal
					{
						background-color: #ffffff;
						color: #000000;
					}

					p.error
					{
						background-color: #dd3333;
						color: #ffffff;
					}

					div.footer
					{
						padding-top: 16px;
					}

					p.auth-info,
					p.help
					{
						font-size: 11px;
						border: 0px;
						padding: 0px;
						margin: 8px 0px 0px 0px;
					}

					p.help a
					{
						color: #666666;
					}

				</style>
			</head>

			<body>
				<?php

				if( Config::$data[ "logoSrc" ] )
				{
					?><img src="<?php echo( Config::$data[ "logoSrc" ] ); ?>" class="logo" border="0" />
					<?php
				}

				?><form method="post" action="<?php echo( count( $_GET ) ? "?" . http_build_query( $_GET, "", "&amp;" ) : "" ); ?>">
					<label for="login">Username: </label>
					<input type="text" name="login" id="login" value="<?php echo( $login ); ?>" />
					<label for="password">Password: </label>
					<input type="password" name="password" id="password" value="" />
					<input type="submit" name="loginbutton" id="loginbutton" value="Login »" />
					<?php

					foreach( $_POST as $key => $value )
					{
						if( ! in_array( $key, array( "login", "password", "loginbutton" ) ) )
						{
							?><input type="hidden" name="<?php echo( $key ); ?>" value="<?php echo( $value ); ?>" />
							<?php
						}
					}

					if( $incorrectLogin )
					{
						?><p class="error">Incorrect username and/or password</p>
						<?php
					}
					else
					{
						?><p class="normal">Please enter your username and password</p>
						<?php
					}

					if( Config::$data[ "version" ] || Config::$data[ "releaseDate" ] || Config::$data[ "administratorEmail" ] )
					{
						?><div class="footer">
							<?php

							if( Config::$data[ "version" ] || Config::$data[ "releaseDate" ] )
							{
								?><p class="auth-info"><?php

								if( Config::$data[ "version" ] )
								{
									?>Version <?php

									echo( Config::$data[ "version" ] );

									if( Config::$data[ "releaseDate" ] )
									{
										?> — <?php
									}
								}

								if( Config::$data[ "releaseDate" ] )
								{
									?>Released <?php

									echo( Config::$data[ "releaseDate" ] );
								}

								?></p>
								<?php
							}

							if( Config::$data[ "administratorEmail" ] )
							{
								?><p class="help">Comments? Suggestions? Need assistance?<br />Contact the <a href="mailto:<?php echo( Config::$data[ "administratorEmail" ] ); ?>">Administrator</a>.</p>
								<?php
							}

							?>
						</div>
						<?php
					}
					?>

				</form>
			</body>

		</html>

	</xsl:template>

</xsl:stylesheet>