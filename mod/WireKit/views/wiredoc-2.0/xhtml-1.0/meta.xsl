<xsl:stylesheet version="1.0"
	exclude-result-prefixes="php"
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

	<xsl:template name="title">
		<xsl:variable name="default-glue" select="' | '" />
		<xsl:variable name="default-sort-order" select="'ascending'" />
		<xsl:variable name="doc-title-set" select="//doc:title[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:if test="$doc-title-set">
			<title>
				<xsl:for-each select="$doc-title-set">

					<!-- TODO These two variables aren't working... -->
					<xsl:variable name="meta-title-glue" select="ancestor-or-self::meta:title-glue[ ( self::meta:title-glue | preceding-sibling::meta:title-glue | following-sibling::meta:title-glue ) and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ][ last() ]" />
					<xsl:variable name="meta-title-sort-order" select="ancestor-or-self::wd:*[ ( preceding-sibling::wd:meta.title-sort-order | following-sibling::wd:meta.title-sort-order ) and ( starts-with( local-name(), 'meta' ) and ( substring( local-name(), 6 ) = 'title-sort-order' or @wd:name='title-sort-order' ) and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ) ][ last() ]" />

					<xsl:variable name="glue">						
						<xsl:choose>
							<xsl:when test="$meta-title-glue"><xsl:value-of select="$meta-title-glue" /></xsl:when>
							<xsl:otherwise><xsl:value-of select="$default-glue" /></xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:variable name="sort-order">
						<xsl:choose>
							<xsl:when test="$meta-title-sort-order"><xsl:value-of select="$meta-title-sort-order" /></xsl:when>
							<xsl:otherwise><xsl:value-of select="$default-sort-order" /></xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<!-- TODO Sort also not working -->
					<!--<xsl:sort select="position()" data-type="number" order="{ $sort-order }" />-->
					<xsl:value-of select="." />
					<xsl:if test="position() != last()">
						<xsl:value-of select="$glue" />
					</xsl:if>
				</xsl:for-each>
			</title>
		</xsl:if>
	</xsl:template>

	<xsl:template name="meta">
		<xsl:for-each select="//meta:*[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]">
			<xsl:apply-templates select="." mode="override-meta" />
		</xsl:for-each>
		<xsl:call-template name="single-link-method" mode="override-meta" />
		<xsl:call-template name="single-script-method" mode="override-meta" />
	</xsl:template>

	<xsl:template name="single-link-method" mode="override-meta">
		<xsl:variable name="meta-link-nodes" select="//meta:link[ @type='text/css' and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:variable name="unique-medias" select="$meta-link-nodes/@media[ not( . = following::meta:link/@media ) ]" />
		<xsl:for-each select="$unique-medias">
			<xsl:variable name="current-media" select="." />
			<xsl:variable name="links-by-media" select="$meta-link-nodes[ @media = $current-media ]" />
			<xsl:variable name="link-href" select="php:function( 'xMVC\Mod\WireKit\Combiner::CombineStylesheetLinks', 'inc/cache/', string( $current-media ), $links-by-media )" />
			<link href="{ $link-href }" rel="stylesheet" type="text/css" media="{ $current-media }" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="single-script-method" mode="override-meta">
		<xsl:variable name="meta-script-nodes" select="//meta:script[ @type='text/javascript' and @src and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:variable name="script-src" select="php:function( 'xMVC\Mod\WireKit\Combiner::CombineJavaScripts', 'inc/cache/', $meta-script-nodes )" />
		<script type="text/javascript" src="{ $script-src }" />
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
