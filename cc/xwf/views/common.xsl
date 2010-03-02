<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc xhtml"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:include href="../../../sys/views/error.xsl" />

	<xsl:template match="xmvc:*" />

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