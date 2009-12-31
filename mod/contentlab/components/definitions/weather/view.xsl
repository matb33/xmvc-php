<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:template match="//clab:instance[@clab:definition='weather']">
		<div id="weather">
			<ul>
				<xsl:for-each select="//xml_api_reply/weather/forecast_conditions">
					<li>
						<strong><xsl:value-of select="day_of_week/@data" /></strong>: <xsl:value-of select="low/@data" />–<xsl:value-of select="high/@data" />
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>

</xsl:stylesheet>