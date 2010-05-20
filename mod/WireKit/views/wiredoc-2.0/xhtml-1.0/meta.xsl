<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:php="http://php.net/xsl">

	<xsl:template name="head">
		<xsl:variable name="meta-title-set" select="//wd:*[ starts-with( local-name(), 'meta' ) and ( substring( local-name(), 6 ) = 'title' or @wd:name='title' ) and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:if test="$meta-title-set">
			<xsl:variable name="default-glue" select="' | '" />
			<xsl:variable name="sort-order">
				<xsl:choose>
					<xsl:when test="$meta-title-set/@sort-order"><xsl:value-of select="$meta-title-set/@sort-order[ 1 ]" /></xsl:when>
					<xsl:otherwise>ascending</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<title>
				<xsl:for-each select="$meta-title-set">
					<xsl:sort select="position()" data-type="number" order="{ $sort-order }" />
					<xsl:value-of select="." />
					<xsl:if test="position() != last()">
						<xsl:choose>
							<xsl:when test="@glue"><xsl:value-of select="@glue" /></xsl:when>
							<xsl:otherwise><xsl:value-of select="$default-glue" /></xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
			</title>
		</xsl:if>
		<xsl:for-each select="//wd:*[ starts-with( local-name(), 'meta' ) and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]">
			<xsl:variable name="meta-name">
				<xsl:choose>
					<xsl:when test="@wd:name">
						<xsl:value-of select="@wd:name" />
					</xsl:when>
					<xsl:when test="starts-with( local-name(), 'meta.' )">
						<xsl:value-of select="substring( local-name(), 6 )" />
					</xsl:when>
					<xsl:otherwise />
				</xsl:choose>
			</xsl:variable>
			<xsl:choose>
				<xsl:when test="$meta-name = 'link'">
					<xsl:element name="link">
						<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
					</xsl:element>
				</xsl:when>
				<xsl:when test="$meta-name = 'meta'">
					<xsl:element name="meta">
						<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
					</xsl:element>
				</xsl:when>
				<xsl:when test="$meta-name = 'script'">
					<xsl:element name="script">
						<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
						<xsl:comment>
							<xsl:apply-templates />
						</xsl:comment>
					</xsl:element>
				</xsl:when>
				<xsl:when test="$meta-name = 'style'">
					<xsl:element name="style">
						<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
						<xsl:comment>
							<xsl:apply-templates />
						</xsl:comment>
					</xsl:element>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="wd:*[ starts-with( local-name(), 'meta' ) ]" />

</xsl:stylesheet>