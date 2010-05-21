<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:doc="http://www.docbook.org/schemas/simplified"
	xmlns:php="http://php.net/xsl">

	<xsl:template name="head">
		<xsl:variable name="default-glue" select="' | '" />
		<xsl:variable name="default-sort-order" select="'ascending'" />
		<xsl:variable name="doc-title-set" select="//doc:title[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:if test="$doc-title-set">
			<title>
				<xsl:for-each select="$doc-title-set">

					<!-- TODO These two variables aren't working... -->
					<xsl:variable name="meta-title-glue" select="ancestor-or-self::wd:*[ ( preceding-sibling::wd:meta.title-glue | following-sibling::wd:meta.title-glue ) and ( starts-with( local-name(), 'meta' ) and ( substring( local-name(), 6 ) = 'title-glue' or @wd:name='title-glue' ) and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ) ][ last() ]" />
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
						<xsl:value-of select="$default-glue" />
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
	<xsl:template match="doc:title" />

</xsl:stylesheet>