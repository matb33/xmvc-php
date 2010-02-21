<xsl:stylesheet version="1.0"
	exclude-result-prefixes="str"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:str="http://www.xmvc.org/ns/str/1.0">

	<xsl:include href="common/xhtml.xsl" />
	<xsl:include href="header.xsl" />
	<xsl:include href="footer.xsl" />

	<xsl:template name="title">
		<title><xsl:value-of select="//str:title" /></title>
	</xsl:template>

	<xsl:template name="metatags">
	</xsl:template>

	<xsl:template name="css">
	</xsl:template>

	<xsl:template name="styles">
	</xsl:template>

	<xsl:template name="scripts">
	</xsl:template>

	<xsl:template name="body">

		<div id="page">
			<xsl:call-template name="header" />

			<xsl:choose>
				<xsl:when test="//xmvc:strings/xmvc:type = 'thanks'">
					<p><xsl:value-of select="//str:thanks" /></p>
				</xsl:when>
				<xsl:when test="//xmvc:strings/xmvc:type = 'error'">
					<p><xsl:value-of select="//str:error" /></p>
				</xsl:when>
				<xsl:otherwise>
					<form method="post" action="send">
						<label for="firstname">
							<xsl:value-of select="//str:labels/str:firstname" />
							<input type="text" id="firstname" name="firstname" />
						</label>
						<label for="lastname">
							<xsl:value-of select="//str:labels/str:lastname" />
							<input type="text" id="lastname" name="lastname" />
						</label>
						<label for="email">
							<xsl:value-of select="//str:labels/str:email" />
							<input type="text" id="email" name="email" />
						</label>
						<input type="submit" id="contact-button" value="{ //str:labels/str:contact-button }" />
					</form>
				</xsl:otherwise>
			</xsl:choose>

			<xsl:call-template name="footer" />
		</div>

	</xsl:template>

	<xsl:template match="str:*" />
	<xsl:template match="xmvc:*" />

</xsl:stylesheet>