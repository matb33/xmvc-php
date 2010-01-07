<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:template match="/xmvc:root">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title>Error <xsl:value-of select="//xmvc:strings/xmvc:error-code" /></title>
			</head>
			<body>
				<xsl:apply-templates />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="//xmvc:error" />
	<xsl:template match="//xmvc:strings" />

	<xsl:template match="//xmvc:error[ @code = //xmvc:strings/xmvc:error-code ]">
		<h1><xsl:value-of select="@type" /> - <xsl:value-of select="@code" /></h1>
		<p><xsl:apply-templates /></p>
		<p>
			<em>Controller File:</em>
			<pre><xsl:value-of select="//xmvc:strings/xmvc:controller-file" /></pre>
		</p>
		<p>
			<em>Method:</em>
			<pre><xsl:value-of select="//xmvc:strings/xmvc:method" /></pre>
		</p>
		<xsl:apply-templates select="xmvc:errors" />
	</xsl:template>

	<xsl:template match="xmvc:errors">
		<h2>PHP Errors</h2>
		<ul>
			<xsl:for-each select="xmvc:errorentry">
				<li>
					<span class="datetime">[<xsl:value-of select="xmvc:datetime" />] </span><span class="errortype"><xsl:value-of select="xmvc:errortype" /></span>: <span class="errormsg"><xsl:value-of select="xmvc:errormsg" /></span>. Line <span class="scriptlinenum"><xsl:value-of select="xmvc:scriptlinenum" /></span> in <span class="scriptname"><xsl:value-of select="xmvc:scriptname" /></span><br />
					<pre class="stack-trace"><xsl:value-of select="xmvc:stack-trace" /></pre>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>