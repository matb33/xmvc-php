<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form interact" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form" xmlns:interact="urn:wirekit:interact">

	<!-- XHTML 1.0 Strict wiredoc templates -->

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
	<xsl:include href="nav.xsl" />
	<xsl:include href="interact.xsl" />

	<xsl:variable name="lang">
		<xsl:value-of select="//component:definition[ not( ancestor::component:definition ) ]/@xml:lang" />
	</xsl:variable>

	<xsl:template match="component:definition[ not( ancestor::component:definition ) ]">
		<xsl:if test="lang( $lang )">
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ $lang }" lang="{ $lang }">
				<head>
					<xsl:call-template name="meta" />
				</head>
				<body>
					<xsl:apply-templates />
				</body>
			</html>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>