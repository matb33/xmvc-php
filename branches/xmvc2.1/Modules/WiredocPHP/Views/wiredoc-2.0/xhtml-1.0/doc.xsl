<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd doc"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:doc="http://www.docbook.org/schemas/simplified"
	xmlns:php="http://php.net/xsl">

	<xsl:template match="doc:heading">
		<xsl:variable name="headingsWithDepth" select="ancestor::*/preceding-sibling::doc:heading[ @depth and php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" />
		<xsl:variable name="closestHeadingWithDepth" select="$headingsWithDepth[ last() ]" />
		<xsl:variable name="myDistanceFromRoot" select="count( ancestor::*/preceding-sibling::doc:heading[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ] )" />
		<xsl:variable name="CHWDDistanceFromRoot" select="count( $closestHeadingWithDepth/ancestor::*/preceding-sibling::doc:heading[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ] )" />
		<xsl:variable name="distanceBetweenMeAndCHWD" select="$myDistanceFromRoot - $CHWDDistanceFromRoot" />
		<xsl:variable name="depth">
			<xsl:choose>
				<xsl:when test="@depth">
					<xsl:value-of select="@depth" />
				</xsl:when>
				<xsl:when test="count( $headingsWithDepth ) = 0">
					<xsl:value-of select="$myDistanceFromRoot + 1" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$distanceBetweenMeAndCHWD + $closestHeadingWithDepth/@depth" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="normalized-depth">
			<xsl:choose>
				<xsl:when test="$depth &gt; 6">6</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$depth"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:element name="h{ $normalized-depth }">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="doc:heading1">
		<h1>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</h1>
	</xsl:template>

	<xsl:template match="doc:heading2">
		<h2>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</h2>
	</xsl:template>

	<xsl:template match="doc:heading3">
		<h3>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</h3>
	</xsl:template>

	<xsl:template match="doc:heading4">
		<h4>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</h4>
	</xsl:template>

	<xsl:template match="doc:heading5">
		<h5>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</h5>
	</xsl:template>

	<xsl:template match="doc:heading6">
		<h6>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</h6>
	</xsl:template>

	<xsl:template match="doc:para">
		<p>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</p>
	</xsl:template>

	<xsl:template match="doc:itemizedlist">
		<ul>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</ul>
	</xsl:template>

	<xsl:template match="doc:orderedlist">
		<ol>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</ol>
	</xsl:template>

	<xsl:template match="doc:listitem">
		<li>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</li>
	</xsl:template>

	<xsl:template match="doc:span">
		<span>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:imagedata">
		<img>
			<xsl:attribute name="src">
				<xsl:choose>
					<xsl:when test="@fileref"><xsl:value-of select="@fileref" /></xsl:when>
					<xsl:otherwise><xsl:value-of select="@href" /></xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:attribute name="alt">
				<xsl:choose>
					<xsl:when test="@alt">
						<xsl:value-of select="@alt" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="." />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
		</img>
	</xsl:template>

	<xsl:template match="doc:emphasis">
		<em>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</em>
	</xsl:template>

	<xsl:template match="doc:strong">
		<strong>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</strong>
	</xsl:template>

	<xsl:template match="doc:address">
		<address>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</address>
	</xsl:template>

	<xsl:template match="doc:copyright">
		<span class="copyright">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:phone">
		<a class="phone">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="href">tel:<xsl:value-of select="text()" /></xsl:attribute>
			<xsl:apply-templates mode="lang-check" />
		</a>
	</xsl:template>

	<xsl:template match="doc:fax">
		<a class="fax">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="href">fax:<xsl:value-of select="text()" /></xsl:attribute>
			<xsl:apply-templates mode="lang-check" />
		</a>
	</xsl:template>

	<xsl:template match="doc:postcode">
		<span class="postcode">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:street">
		<span class="street">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:city">
		<span class="city">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:country">
		<span class="country">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:date">
		<span class="date">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:year">
		<span class="year">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:email">
		<span class="email">
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</span>
	</xsl:template>

	<xsl:template match="doc:abbrev">
		<abbr>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</abbr>
	</xsl:template>

	<xsl:template match="doc:acronym">
		<acronym>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</acronym>
	</xsl:template>

	<xsl:template match="doc:quote">
		<q>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</q>
	</xsl:template>

	<xsl:template match="doc:blockquote">
		<blockquote>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[ name() != 'doc:attribution' ]" mode="lang-check" />
			<xsl:apply-templates select="doc:attribution" mode="lang-check" />
		</blockquote>
	</xsl:template>

	<xsl:template match="doc:blockquote//doc:para/text()|doc:quote/text()">
		“<xsl:value-of select="." />”
	</xsl:template>

	<xsl:template match="doc:blockquote/doc:attribution">
		<p>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="class">
				<xsl:value-of select="local-name()" />
				<xsl:if test="@wd:name">
					<xsl:text> </xsl:text><xsl:value-of select="@wd:name" />
				</xsl:if>
			</xsl:attribute>
			<xsl:text>— </xsl:text><xsl:apply-templates mode="lang-check" />
		</p>
	</xsl:template>

	<xsl:template match="doc:linebreak">
		<br>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
		</br>
	</xsl:template>

	<xsl:template match="doc:ulink">
		<a>
			<xsl:attribute name="href">
				<xsl:choose>
					<xsl:when test="@url"><xsl:value-of select="@url" /></xsl:when>
					<xsl:otherwise><xsl:value-of select="@href" /></xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="@rel">
					<xsl:attribute name="rel"><xsl:value-of select="@rel" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="@target">
					<xsl:attribute name="rel"><xsl:value-of select="@target" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</a>
	</xsl:template>

	<xsl:template match="doc:informaltable">
		<table>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="doc:*" mode="lang-check" />
		</table>
	</xsl:template>

	<xsl:template match="doc:informaltable/doc:thead">
		<thead>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select=".//doc:row" mode="lang-check" />
		</thead>
	</xsl:template>

	<xsl:template match="doc:informaltable/doc:tbody">
		<tbody>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select=".//doc:row" mode="lang-check" />
		</tbody>
	</xsl:template>

	<xsl:template match="doc:row">
		<xsl:param name="position" select="position()" />
		<xsl:param name="last" select="last()" />
		<tr>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="$last = 1">first-child last-child</xsl:when>
					<xsl:when test="$position = 1">first-child</xsl:when>
					<xsl:when test="$position = $last">last-child</xsl:when>
					<xsl:otherwise>middle-child</xsl:otherwise>
				</xsl:choose>
				<xsl:text> item-</xsl:text><xsl:value-of select="$position" />
				<xsl:text> </xsl:text>
				<xsl:choose>
					<xsl:when test="$position mod 2 = 1">even</xsl:when>
					<xsl:otherwise>odd</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select=".//doc:entry" mode="lang-check" />
		</tr>
	</xsl:template>

	<xsl:template match="doc:row//doc:entry">
		<xsl:param name="position" select="position()" />
		<xsl:param name="last" select="last()" />
		<xsl:variable name="cell-name">
			<xsl:choose>
				<xsl:when test="ancestor::doc:thead">th</xsl:when>
				<xsl:otherwise>td</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:element name="{ $cell-name }">
			<xsl:attribute name="class">
				<xsl:if test="@wd:name"><xsl:value-of select="@wd:name" /><xsl:text> </xsl:text></xsl:if>
				<xsl:choose>
					<xsl:when test="$last = 1">first-child last-child</xsl:when>
					<xsl:when test="$position = 1">first-child</xsl:when>
					<xsl:when test="$position = $last">last-child</xsl:when>
					<xsl:otherwise>middle-child</xsl:otherwise>
				</xsl:choose>
				<xsl:text> item-</xsl:text><xsl:value-of select="$position" />
				<xsl:text> </xsl:text>
				<xsl:choose>
					<xsl:when test="$position mod 2 = 1">even</xsl:when>
					<xsl:otherwise>odd</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="doc:preformatted">
		<pre>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</pre>
	</xsl:template>

	<xsl:template match="doc:superscript">
		<sup>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</sup>
	</xsl:template>

	<xsl:template match="doc:subscript">
		<sub>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@wd:name">
				<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</sub>
	</xsl:template>

</xsl:stylesheet>