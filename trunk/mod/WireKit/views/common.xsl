<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form interact"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:doc="http://www.docbook.org/schemas/simplified"
	xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form" xmlns:loc="urn:wirekit:loc" xmlns:interact="urn:wirekit:interact" xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">

	<xsl:include href="../../../sys/views/error.xsl" />

	<!-- Global utility templates -->

	<xsl:template match="xmvc:*" />

	<!-- Creates a variable containing a relative path modifier to get to the root of the web folder, using xmvc:strings/xmvc:uri -->

	<xsl:variable name="relative-path-modifier">
		
	</xsl:variable>

	<!-- Strip namespaces from XHTML using an identity template -->

	<xsl:template match="*[ ./xhtml:* ]">
		<xsl:apply-templates select="node()" />
	</xsl:template>

	<xsl:template match="*[ ./xhtml:* ]//xhtml:*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="@* | node()" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="*[ ./xhtml:* ]//@*">
		<xsl:attribute name="{ local-name() }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

	<!-- String replace function -->

	<xsl:template name="string-replace-all">
		<xsl:param name="text" />
		<xsl:param name="replace" />
		<xsl:param name="by" />
		<xsl:choose>
			<xsl:when test="contains($text, $replace)">
				<xsl:value-of select="substring-before($text,$replace)" />
				<xsl:value-of select="$by" />
				<xsl:call-template name="string-replace-all">
					<xsl:with-param name="text" select="substring-after($text,$replace)" />
					<xsl:with-param name="replace" select="$replace" />
					<xsl:with-param name="by" select="$by" />
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$text" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>