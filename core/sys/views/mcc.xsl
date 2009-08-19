<xsl:stylesheet version="1.0" 
	xmlns="http://www.w3.org/1999/xhtml" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:template match="*[@xmvc:mcc = 'true']">
	<html>
		<body>
			<style type="text/css">
				html
				{
					font-size: 14pt;
				}
				body
				{
					background-color: #ffffff;
					color: #000000;
					font-size: 75%;
					font-family: sans-serif;
					cursor: default;
				}
				img{
					-moz-user-select: none;
				}
				.lgt,
				.att,
				.node,
				.attval
				{
					font-family: monospace;
					-moz-user-select: none;
				}
				.lgt
				{
					color: #000088;
				}
				.att
				{
					margin-left: 5px;
					color: #000088;
				}
				.node
				{
					color: #0000ff;
				}
				.attval
				{
					color: #ff0000;
				}
				.note
				{
					background-color: #FFFFE1;
					border-style: solid;
					border-width: 1px;
					border-color: #000000;
					color: #000000;
				}
				.noteInline
				{
					display: inline;
					background-color: #FFFFE1;
					border-style: solid;
					border-width: 1px;
					border-color: #000000;
					color: #000000;
				}
				.example
				{
					border-style: solid;
					border-width: 1px;
					border-color: #000000;
					color: #000000;
				}
				.push
				{
					margin-left: 2em;
				}
				.push.highlight
				{
					background: #000088;
					color: #ffffff;
				}
				.PlusMinus
				{
					position:relative;
					float:left;
					left:-12px;
					margin-right:-12px;
				}
				.tag:hover
				{
					background-color:#FFFFE1;
					color:#0057Af;
				}
			</style>

			<script type="text/javascript"><![CDATA[
				flags = new Array( 0 );
				function flagCollapsedTag( tagId, direction )
				{
					done = "false";
					x = 0;
					while ( x <= flags.length )
					{
						if ( flags[ x ] == "" )
						{
							flags[ x ] = tagId + direction;
							done = "true";
							break;
						}
						x++;
					}
					if ( done == "false" )
					{
						flags[ flags.length ] = tagId + direction;
					}
				}
				function unflagCollapsedTag( tagId, direction )
				{
					x = 0;
					while ( x <= flags.length )
					{
						if ( flags[ x ] == tagId + direction )
						{
							flags[ x ] = "";
						}
						x++;
					}
				}
				function collapseTag( tagId, direction )
				{
					if ( direction == "tags" )
					{
						collapseContent = tagId + "-content";
					}
					else if( direction == "tag" )
					{
						collapseContent = tagId;
					}
					done = "false";
					x = 0;
					while ( x <= flags.length )
					{
						if ( flags[ x ] == tagId + direction )
						{
							document.getElementById( collapseContent ).style.display = "inline";
							if ( direction == "tag" )
							{
								document.getElementById( tagId + "-tag" ).style.fontStyle = "normal";
							}
							if ( direction == "tags" )
							{
								document.getElementById( tagId + "-PlusMinus-tags" ).innerHTML = "<img src='data:image/gif;base64,R0lGODlhCQAJAKUgAAAAAImmwMrCs8vDtMvDtczEts/IurzL2tfRxdnUydnUyt3Yz93Z0N7a0eHd1OLe1+Th2ejl3+jm3+nm4O7t6O/u6vDw6/Pz7vPz7/T08ff38/j49fj49vj49/39+/39/P///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAD8ALAAAAAAJAAkAAAY0wENgSAwIQcgkaPhpNj2bYafDqV4mQw1gu4UMMxhLhRJxDCWPBmOhQAwTBsJgICgYi8RDEAA7'/>";
							}
							done = "true";
							unflagCollapsedTag( tagId, direction );
							if ( done == "true" ) break;
						}
						x++;
					}
					if ( done == "false" )
					{
						document.getElementById( collapseContent ).style.display = "none";
						if ( direction == "tag" )
						{
							document.getElementById( tagId + "-tag" ).style.fontStyle = "oblique";
						}
						if ( direction == "tags" )
						{
							document.getElementById( tagId + "-PlusMinus-tags" ).innerHTML = "<img src='data:image/gif;base64,R0lGODlhCQAJAIQeAAAAAImmwMrCs8vDtMvDtczEts/IurzL2tfRxdnUydnUyt3Yz97a0eHd1OLe1+Th2ejl3+jm3+nm4O7t6PDw6/Pz7vPz7/T08ff38/j49fj49vj49/39+/39/P///////yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAB8ALAAAAAAJAAkAAAUy4BGMZCB6aOqNXQt0XDZumwZolTRiQN8/o4uFAphAGqOIgwFYKBCjhIEwGAgKphLpEAIAOw%3D%3D'/>";
						}
						flagCollapsedTag( tagId, direction );
					}
				}

				]]>
			</script>
			<xsl:apply-templates/>
		</body>
	</html>
	</xsl:template>
	<xsl:template match="*[@xmvc:mcc = 'true']//*">
		<div class="push">
			<xsl:choose>
				<xsl:when test=".//*">
					<div class="PlusMinus" id="{generate-id()}-PlusMinus-tags" onclick="collapseTag('{generate-id(.)}','tags')"><img src="data:image/gif;base64,R0lGODlhCQAJAKUgAAAAAImmwMrCs8vDtMvDtczEts/IurzL2tfRxdnUydnUyt3Yz93Z0N7a0eHd1OLe1+Th2ejl3+jm3+nm4O7t6O/u6vDw6/Pz7vPz7/T08ff38/j49fj49vj49/39+/39/P///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAD8ALAAAAAAJAAkAAAY0wENgSAwIQcgkaPhpNj2bYafDqV4mQw1gu4UMMxhLhRJxDCWPBmOhQAwTBsJgICgYi8RDEAA7"/></div>
				</xsl:when>
				<xsl:when test="text()">
					<div class="PlusMinus" id="{generate-id()}-PlusMinus-tags" onclick="collapseTag('{generate-id(.)}','tags')"><img src="data:image/gif;base64,R0lGODlhCQAJAKUgAAAAAImmwMrCs8vDtMvDtczEts/IurzL2tfRxdnUydnUyt3Yz93Z0N7a0eHd1OLe1+Th2ejl3+jm3+nm4O7t6O/u6vDw6/Pz7vPz7/T08ff38/j49fj49vj49/39+/39/P///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAD8ALAAAAAAJAAkAAAY0wENgSAwIQcgkaPhpNj2bYafDqV4mQw1gu4UMMxhLhRJxDCWPBmOhQAwTBsJgICgYi8RDEAA7"/></div>
				</xsl:when>
			</xsl:choose>
			<span class="lgt">&lt;</span>
			<xsl:choose>
				<xsl:when test="@*">
				<!--  id="{generate-id()}-tag" onclick="collapseTag('{generate-id(.)}','tags')"
				was removed from next element, becase I wanted to add element collapse of node list
				-->
					<span class="tag">
						<span class="node"><xsl:value-of select="name()"/></span>
						<span id="{generate-id(.)}">
							<xsl:for-each select="@*">
								<span class="att"><xsl:value-of select="name()"/>=</span>
								<span class="attval">&quot;<xsl:value-of select="."/>&quot;</span>
							</xsl:for-each>
						</span>
					</span>
				</xsl:when>
				<xsl:otherwise>
					<span class="node" onclick="collapseTag('{generate-id(.)}','tags')"><xsl:value-of select="name()"/></span>
					<xsl:for-each select="@*">
						<span class="att"><xsl:value-of select="name()"/>=</span>
						<span class="attval">&quot;<xsl:value-of select="."/>&quot;</span>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:choose>
				<xsl:when test=".//*">
					<span class="lgt">&gt;</span>
				</xsl:when>
				<xsl:when test="text()">
					<span class="lgt">&gt;</span>
				</xsl:when>
				<xsl:otherwise>
					<span class="lgt">/&gt;</span>
				</xsl:otherwise>
			</xsl:choose>
			<span id="{generate-id(.)}-content"><xsl:apply-templates/></span>
			<xsl:choose>
				<xsl:when test=".//*">
					<span class="lgt">&lt;</span>
					<span class="node">/<xsl:value-of select="name()"/></span>
					<span class="lgt">&gt;</span>
				</xsl:when>
				<xsl:when test="text()">
					<span class="lgt">&lt;</span>
					<span class="node">/<xsl:value-of select="name()"/></span>
					<span class="lgt">&gt;</span>
				</xsl:when>
			</xsl:choose>
		</div>
	</xsl:template>
</xsl:stylesheet>
