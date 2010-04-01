<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container xcontainer group reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:cc:component" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:xcontainer="urn:cc:xcontainer" xmlns:group="urn:cc:group" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

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

	<xsl:variable name="lang">
		<xsl:value-of select="//component:*[ not( ancestor::component:* ) ]/@xml:lang" />
	</xsl:variable>

	<xsl:template match="component:*[ not( ancestor::component:* ) ]">
		<xsl:if test="lang( $lang )">
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ $lang }" lang="{ $lang }">
				<xsl:apply-templates />
			</html>
		</xsl:if>
	</xsl:template>

	<xsl:template match="component:*[ preceding-sibling::meta:head ]">
		<body>
			<xsl:apply-templates />
		</body>
	</xsl:template>

</xsl:stylesheet>