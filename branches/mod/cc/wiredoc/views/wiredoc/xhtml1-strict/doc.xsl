<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc instance meta container xcontainer group reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:instance="urn:cc:instance" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:xcontainer="urn:cc:xcontainer" xmlns:group="urn:cc:group" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

	<xsl:template match="doc:heading">
		<xsl:variable name="depth" select="count( ancestor::*[ preceding-sibling::doc:heading | following-sibling::doc:heading ] ) + 1" />
		<xsl:if test="lang( $lang )">
			<xsl:choose>
				<xsl:when test="$depth &gt; 6">
					<h6><xsl:apply-templates /></h6>
				</xsl:when>
				<xsl:otherwise>
					<xsl:element name="h{ $depth }"><xsl:apply-templates /></xsl:element>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading1">
		<xsl:if test="lang( $lang )">
			<h1><xsl:apply-templates /></h1>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading2">
		<xsl:if test="lang( $lang )">
			<h2><xsl:apply-templates /></h2>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading3">
		<xsl:if test="lang( $lang )">
			<h3><xsl:apply-templates /></h3>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading4">
		<xsl:if test="lang( $lang )">
			<h4><xsl:apply-templates /></h4>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading5">
		<xsl:if test="lang( $lang )">
			<h5><xsl:apply-templates /></h5>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading6">
		<xsl:if test="lang( $lang )">
			<h6><xsl:apply-templates /></h6>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:para">
		<xsl:if test="lang( $lang )">
			<p><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:apply-templates /></p>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:itemizedlist">
		<xsl:if test="lang( $lang )">
			<ul><xsl:apply-templates /></ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:orderedlist">
		<xsl:if test="lang( $lang )">
			<ol><xsl:apply-templates /></ol>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:listitem">
		<xsl:if test="lang( $lang )">
			<li><xsl:apply-templates /></li>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:span">
		<xsl:if test="lang( $lang )">
			<span><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:imagedata">
		<xsl:if test="lang( $lang )">
			<img>
				<xsl:if test="@fileref">
					<xsl:attribute name="src"><xsl:value-of select="@fileref" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@alt">
					<xsl:attribute name="alt"><xsl:value-of select="@alt" /></xsl:attribute>
				</xsl:if>
			</img>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:emphasis">
		<xsl:if test="lang( $lang )">
			<em><xsl:apply-templates /></em>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:strong">
		<xsl:if test="lang( $lang )">
			<strong><xsl:apply-templates /></strong>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:address">
		<xsl:if test="lang( $lang )">
			<address><xsl:apply-templates /></address>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:copyright">
		<xsl:if test="lang( $lang )">
			<span class="copyright"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:phone">
		<xsl:if test="lang( $lang )">
			<span class="phone"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:fax">
		<xsl:if test="lang( $lang )">
			<span class="fax"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:postcode">
		<xsl:if test="lang( $lang )">
			<span class="postcode"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:street">
		<xsl:if test="lang( $lang )">
			<span class="street"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:city">
		<xsl:if test="lang( $lang )">
			<span class="city"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:country">
		<xsl:if test="lang( $lang )">
			<span class="country"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:date">
		<xsl:if test="lang( $lang )">
			<span class="date"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:year">
		<xsl:if test="lang( $lang )">
			<span class="year"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:email">
		<xsl:if test="lang( $lang )">
			<span class="email"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:abbrev">
		<xsl:if test="lang( $lang )">
			<abbr><xsl:apply-templates /></abbr>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:acronym">
		<xsl:if test="lang( $lang )">
			<acronym><xsl:apply-templates /></acronym>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:quote">
		<xsl:if test="lang( $lang )">
			<q><xsl:apply-templates /></q>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:linebreak">
		<xsl:if test="lang( $lang )">
			<br />
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:ulink">
		<xsl:if test="lang( $lang )">
			<a>
				<xsl:attribute name="href">
					<xsl:choose>
						<xsl:when test="@url"><xsl:value-of select="@url" /></xsl:when>
						<xsl:otherwise><xsl:value-of select="@href" /></xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
				<xsl:apply-templates />
			</a>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>