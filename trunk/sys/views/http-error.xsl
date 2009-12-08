<?php
	$headerType = $model->xml->xPath->query( "//xmvc:error[ @xmvc:code = '" . $errorCode . "' ]/@xmvc:type" )->item( 0 )->nodeValue;
	header( "HTTP/1.0 " . $errorCode . " " . $headerType );
?>
<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
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

	<xsl:template match="/xmvc:root">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo( "en" ); ?>" lang="<?php echo( "en" ); ?>">
			<head>
				<title>Error <?php echo( $errorCode ); ?></title>
			</head>
			<body>
				<xsl:apply-templates />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="//xmvc:error" />

	<xsl:template match="//xmvc:error[ @xmvc:code = '<?php echo( $errorCode ); ?>' ]">
		<h1><xsl:value-of select="@xmvc:type" /> - <xsl:value-of select="@xmvc:code" /></h1>
		<p><xsl:apply-templates /></p>
		<p><em>Controller File: <?php echo( $controllerFile ); ?></em></p>
		<p><em>Method: <?php echo( $method ); ?></em></p>
	</xsl:template>

</xsl:stylesheet>