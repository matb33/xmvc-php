<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:template match="xmvc:errors">

		<style type="text/css">

			#errors
			{
				font-family: Arial, Helvetica, sans-serif;
				font-size: 13px;
				margin:1em auto;
				width:80%;
				border: 1px dashed #ff3333;
				background-color: #ffeeee;
				padding: 12px;
			}

			#errors h1
			{
				font-size: 16px;
				font-weight: bold;
				margin: 0px;
				padding: 0px;
				padding-bottom: 12px;
			}

			#errors ul
			{
				padding: 0px;
				margin: 0px;
				margin-left: 16px;
				list-style-type: disc;
			}

			#errors ul li span.datetime
			{
				color: #777777;
			}

			#errors ul li span.errortype
			{
				font-weight: bold;
			}

			#errors ul li span.errormsg
			{
				color: #3333aa;
			}

			#errors ul li span.scriptlinenum
			{
				font-weight: bold;
			}

			#errors ul li span.scriptname
			{
				color: #33aa33;
			}

		</style>

		<script type="text/javascript">

			if( typeof( console ) != "undefined" )
			{
				if( window.loadFirebugConsole )
				{
					window.loadFirebugConsole();
				}

				<xsl:for-each select="xmvc:errorentry">
					console.error( "<xsl:value-of select="xmvc:errortype" />: <xsl:value-of select="xmvc:errormsg" />. \n\nLine <xsl:value-of select="xmvc:scriptlinenum" /> in <xsl:value-of select="xmvc:scriptname" /> \n[<xsl:value-of select="xmvc:datetime" />] " );
				</xsl:for-each>
			}

		</script>

		<div id="errors">

			<h1>PHP Errors</h1>

			<ul>

			<xsl:for-each select="xmvc:errorentry">
				<li><span class="datetime">[<xsl:value-of select="xmvc:datetime" />] </span><span class="errortype"><xsl:value-of select="xmvc:errortype" /></span>: <span class="errormsg"><xsl:value-of select="xmvc:errormsg" /></span>. Line <span class="scriptlinenum"><xsl:value-of select="xmvc:scriptlinenum" /></span> in <span class="scriptname"><xsl:value-of select="xmvc:scriptname" /></span></li>
			</xsl:for-each>

			</ul>

		</div>

	</xsl:template>

</xsl:stylesheet>