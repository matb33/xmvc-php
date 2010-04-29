<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form">

	<xsl:template match="doc:heading">
		<xsl:variable name="depth" select="count( ancestor::*[ preceding-sibling::doc:heading | following-sibling::doc:heading ] ) + 1" />
		<xsl:if test="lang( $lang )">
			<xsl:choose>
				<xsl:when test="$depth &gt; 6">
					<h6>
					<xsl:if test="@id">
						<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
					</xsl:if>
						<xsl:apply-templates />
					</h6>
				</xsl:when>
				<xsl:otherwise>
					<xsl:element name="h{ $depth }">
						<xsl:if test="@id">
							<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
						</xsl:if>
						<xsl:apply-templates />
					</xsl:element>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading1">
		<xsl:if test="lang( $lang )">
			<h1>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</h1>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading2">
		<xsl:if test="lang( $lang )">
			<h2>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</h2>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading3">
		<xsl:if test="lang( $lang )">
			<h3>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</h3>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading4">
		<xsl:if test="lang( $lang )">
			<h4>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</h4>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading5">
		<xsl:if test="lang( $lang )">
			<h5>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</h5>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading6">
		<xsl:if test="lang( $lang )">
			<h6>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</h6>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:para">
		<xsl:if test="lang( $lang )">
			<p>
				<xsl:if test="@class">
					<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</p>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:itemizedlist">
		<xsl:if test="lang( $lang )">
			<ul>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:orderedlist">
		<xsl:if test="lang( $lang )">
			<ol>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</ol>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:listitem">
		<xsl:if test="lang( $lang )">
			<li>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</li>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:span">
		<xsl:if test="lang( $lang )">
			<span>
				<xsl:if test="@class">
					<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:imagedata">
		<xsl:if test="lang( $lang )">
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
			</img>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:emphasis">
		<xsl:if test="lang( $lang )">
			<em>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</em>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:strong">
		<xsl:if test="lang( $lang )">
			<strong>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</strong>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:address">
		<xsl:if test="lang( $lang )">
			<address>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</address>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:copyright">
		<xsl:if test="lang( $lang )">
			<span class="copyright">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:phone">
		<xsl:if test="lang( $lang )">
			<span class="phone">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:fax">
		<xsl:if test="lang( $lang )">
			<span class="fax">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:postcode">
		<xsl:if test="lang( $lang )">
			<span class="postcode">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:street">
		<xsl:if test="lang( $lang )">
			<span class="street">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:city">
		<xsl:if test="lang( $lang )">
			<span class="city">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:country">
		<xsl:if test="lang( $lang )">
			<span class="country">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:date">
		<xsl:if test="lang( $lang )">
			<span class="date">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:year">
		<xsl:if test="lang( $lang )">
			<span class="year">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:email">
		<xsl:if test="lang( $lang )">
			<span class="email">
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:abbrev">
		<xsl:if test="lang( $lang )">
			<abbr>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</abbr>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:acronym">
		<xsl:if test="lang( $lang )">
			<acronym>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</acronym>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:quote">
		<xsl:if test="lang( $lang )">
			<q>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</q>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:linebreak">
		<xsl:if test="lang( $lang )">
			<br>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
			</br>
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
				<xsl:if test="@target">
					<xsl:attribute name="rel">
						<xsl:value-of select="@target" />
					</xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</a>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:informaltable">
		<xsl:if test="lang( $lang )">
			<table>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="doc:*" />
			</table>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:informaltable/doc:thead">
		<xsl:if test="lang( $lang )">
			<thead>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="doc:row" />
			</thead>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:informaltable/doc:tbody">
		<xsl:if test="lang( $lang )">
			<tbody>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="doc:row" />
			</tbody>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:row">
		<xsl:if test="lang( $lang )">
			<tr>
				<xsl:attribute name="class">
					<xsl:choose>
						<xsl:when test="last() = 1">first-child last-child</xsl:when>
						<xsl:when test="position() = 1">first-child</xsl:when>
						<xsl:when test="position() = last()">last-child</xsl:when>
						<xsl:otherwise>middle-child</xsl:otherwise>
					</xsl:choose>
					<xsl:text> item-</xsl:text><xsl:value-of select="position()" />
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="position() mod 2 = 1">even</xsl:when>
						<xsl:otherwise>odd</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="doc:entry" />
			</tr>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:row/doc:entry">
		<xsl:if test="lang( $lang )">
			<xsl:variable name="cell-name">
				<xsl:choose>
					<xsl:when test="../../../doc:thead">th</xsl:when>
					<xsl:otherwise>td</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:element name="{ $cell-name }">
				<xsl:attribute name="class">
					<xsl:choose>
						<xsl:when test="last() = 1">first-child last-child</xsl:when>
						<xsl:when test="position() = 1">first-child</xsl:when>
						<xsl:when test="position() = last()">last-child</xsl:when>
						<xsl:otherwise>middle-child</xsl:otherwise>
					</xsl:choose>
					<xsl:text> item-</xsl:text><xsl:value-of select="position()" />
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="position() mod 2 = 1">even</xsl:when>
						<xsl:otherwise>odd</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</xsl:element>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>