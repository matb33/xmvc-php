<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns="http://www.w3.org/1999/xhtml">

	<xsl:include href="../../../System/Views/error.xsl" />

	<!-- Global utility templates -->

	<xsl:template match="xmvc:*" />

	<!-- Copy XHTML as is, if any. Note that xhtml namespace declarations for both "xhtml" and "" (blank) prefixes is necessary -->

	<xsl:template match="xhtml:*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="node()|@*" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="@*[ parent::xhtml:* ]">
		<xsl:attribute name="{ name() }">
			<xsl:value-of select="." />
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