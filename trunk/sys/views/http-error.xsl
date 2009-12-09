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
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title>Error <xsl:value-of select="//xmvc:strings/xmvc:error-code" /></title>
			</head>
			<body>
				<xsl:apply-templates />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="//xmvc:error" />
	<xsl:template match="//xmvc:strings" />

	<xsl:template match="//xmvc:error[ @xmvc:code = //xmvc:strings/xmvc:error-code ]">
		<h1><xsl:value-of select="@xmvc:type" /> - <xsl:value-of select="@xmvc:code" /></h1>
		<p><xsl:apply-templates /></p>
		<p><em>Controller File: <xsl:value-of select="//xmvc:strings/xmvc:controller-file" /></em></p>
		<p><em>Method: <xsl:value-of select="//xmvc:strings/xmvc:method" /></em></p>
	</xsl:template>

</xsl:stylesheet>