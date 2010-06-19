<xsl:stylesheet version="1.0"
	exclude-result-prefixes="meta php"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:meta="http://www.wiredoc.org/ns/metadoc/1.0"
	xmlns:php="http://php.net/xsl">

	<xsl:template name="override-meta-link">
		<xsl:variable name="meta-link-nodes" select="//meta:link[ @type='text/css' and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:variable name="unique-medias" select="$meta-link-nodes/@media[ not( . = following::meta:link/@media ) ]" />
		<xsl:for-each select="$unique-medias">
			<xsl:variable name="current-media" select="." />
			<xsl:variable name="links-by-media" select="$meta-link-nodes[ @media = $current-media ]" />
			<xsl:variable name="link-href" select="php:function( 'xMVC\Mod\Combiner\Combiner::CombineStylesheetLinks', string( $current-media ), $links-by-media )" />
			<link href="{ $link-href }" rel="stylesheet" type="text/css" media="{ $current-media }" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="override-meta-script">
		<xsl:variable name="meta-script-nodes" select="//meta:script[ @type='text/javascript' and @href and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:variable name="script-src" select="php:function( 'xMVC\Mod\Combiner\Combiner::CombineJavaScripts', $meta-script-nodes )" />
		<script type="text/javascript" src="{ $script-src }" />
	</xsl:template>

</xsl:stylesheet>