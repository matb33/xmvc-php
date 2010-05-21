<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd meta"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<!-- XHTML 1.0 Strict Wiredoc 2.0 templates -->

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:include href="../common.xsl" />
	<xsl:include href="doc.xsl" />
	<xsl:include href="container.xsl" />
	<xsl:include href="group.xsl" />
	<xsl:include href="form.xsl" />
	<xsl:include href="meta.xsl" />
	<xsl:include href="navigation.xsl" />
	<xsl:include href="navigate.xsl" />
	<xsl:include href="action.xsl" />

	<xsl:variable name="lang">
		<xsl:value-of select="//wd:component[ not( ancestor::wd:component ) ]/@xml:lang" />
	</xsl:variable>

	<xsl:template match="wd:component[ not( ancestor::wd:component ) ]">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ $lang }" lang="{ $lang }">
			<head>
				<xsl:call-template name="head" />
			</head>
			<body>
				<xsl:apply-templates mode="lang-check" />
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>