<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd meta xmvc doc php"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:meta="http://www.wiredoc.org/ns/metadoc/1.0"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:doc="http://www.docbook.org/schemas/simplified"
	xmlns:php="http://php.net/xsl">

	<xsl:template name="head">
		<xsl:call-template name="title" />
		<xsl:call-template name="meta" />
	</xsl:template>

	<xsl:template name="override-head">
		<xsl:call-template name="head" />
	</xsl:template>

	<xsl:template name="title">
		<xsl:variable name="default-glue" select="' | '" />
		<xsl:variable name="default-sort-order" select="'ascending'" />
		<xsl:variable name="doc-title-set" select="//doc:title[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:if test="$doc-title-set">
			<xsl:variable name="sort-order">
				<xsl:choose>
					<xsl:when test="$doc-title-set/@meta:sort-order"><xsl:value-of select="$doc-title-set/@meta:sort-order[ 1 ]" /></xsl:when>
					<xsl:otherwise><xsl:value-of select="$default-sort-order" /></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<title>
				<xsl:for-each select="$doc-title-set">
					<xsl:sort select="position()" data-type="number" order="{ $sort-order }" />
					<xsl:apply-templates mode="lang-check" />
					<xsl:if test="position() != last()">
						<xsl:choose>
							<xsl:when test="@meta:glue"><xsl:value-of select="@meta:glue" /></xsl:when>
							<xsl:otherwise><xsl:value-of select="$default-glue" /></xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
			</title>
		</xsl:if>

	</xsl:template>

	<xsl:template name="meta">
		<xsl:apply-templates select="//meta:*[ local-name() != 'script' and local-name() != 'link' ]" mode="lang-check-meta" />
		<xsl:call-template name="override-meta-link" />
		<xsl:call-template name="override-meta-script" />
	</xsl:template>

	<xsl:template name="override-meta-link">
		<xsl:call-template name="meta-link" />
	</xsl:template>

	<xsl:template name="override-meta-script">
		<xsl:call-template name="meta-script" />
	</xsl:template>

	<xsl:template name="meta-link">
		<xsl:apply-templates select="//meta:link" mode="lang-check-meta" />
	</xsl:template>

	<xsl:template name="meta-script">
		<xsl:apply-templates select="//meta:script" mode="lang-check-meta" />
	</xsl:template>

	<xsl:template match="meta:link" mode="meta">
		<xsl:element name="link">
			<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' and namespace-uri() != 'http://www.wiredoc.org/ns/metadoc/1.0' ]" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:script" mode="meta">
		<xsl:element name="script">
			<xsl:attribute name="src">
				<xsl:value-of select="@href" />
			</xsl:attribute>
			<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' and namespace-uri() != 'http://www.wiredoc.org/ns/metadoc/1.0' and name() != 'href' ]" />
			<xsl:comment>
				<xsl:apply-templates />
			</xsl:comment>
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:meta" mode="meta">
		<xsl:element name="meta">
			<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' and namespace-uri() != 'http://www.wiredoc.org/ns/metadoc/1.0' ]" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:style" mode="meta">
		<xsl:element name="style">
			<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' and namespace-uri() != 'http://www.wiredoc.org/ns/metadoc/1.0' ]" />
			<xsl:comment>
				<xsl:apply-templates />
			</xsl:comment>
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:*" mode="meta" />
	<xsl:template match="meta:*" />
	<xsl:template match="doc:title" />

</xsl:stylesheet>
