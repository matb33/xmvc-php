<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:meta="http://www.wiredoc.org/ns/metadoc/1.0"
	xmlns:php="http://php.net/xsl">

	<xsl:include href="../common.xsl" />

	<!-- Global wiredoc templates for xMVC -->

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="wd:component" mode="lang-check" />
	</xsl:template>

	<xsl:template match="*" mode="lang-check">
		<xsl:if test="php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] )">
			<xsl:apply-templates select="." mode="override">
				<xsl:with-param name="position" select="position()" />
				<xsl:with-param name="last" select="last()" />
			</xsl:apply-templates>
		</xsl:if>
	</xsl:template>

	<xsl:template match="*" mode="override">
		<xsl:param name="position" select="position()" />
		<xsl:param name="last" select="last()" />
		<xsl:apply-templates select=".">
			<xsl:with-param name="position" select="$position" />
			<xsl:with-param name="last" select="$last" />
		</xsl:apply-templates>
	</xsl:template>

	<xsl:template match="meta:*" mode="override-meta">
		<xsl:param name="position" select="position()" />
		<xsl:param name="last" select="last()" />
		<xsl:apply-templates select="." mode="meta">
			<xsl:with-param name="position" select="$position" />
			<xsl:with-param name="last" select="$last" />
		</xsl:apply-templates>
	</xsl:template>

	<xsl:template match="wd:component">
		<xsl:apply-templates mode="lang-check" />
	</xsl:template>

</xsl:stylesheet>