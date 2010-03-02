<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:include href="mod/cc/views/base-editor.xsl" />

	<xsl:template name="style">
		<style type="text/css">
			<xsl:comment>
				label
				{
					display: block;
				}

				fieldset
				{
					margin-bottom: 5px;
				}
			</xsl:comment>
		</style>
	</xsl:template>

	<xsl:template name="script">
		<script type="text/javascript">
			<xsl:comment>
				$( document ).ready( function()
				{
					$( "#write" ).click( function()
					{
						CCMS.Write( $( "#ccms > form" ) );
					});
				});
			</xsl:comment>
		</script>
	</xsl:template>

	<xsl:template match="cc:root">
		<form>
			<xsl:apply-templates />
			<input type="button" id="write" value="Save" />
		</form>
	</xsl:template>

	<xsl:template match="cc:navigation">
		<xsl:if test="@name">
			<input type="hidden" name="name" value="{ @name }" />
		</xsl:if>
		<ul class="navigation layout">
			<xsl:apply-templates select="cc:item" />
		</ul>
	</xsl:template>

	<xsl:template match="cc:item">
		<li class="layout">
			<fieldset>
				<label>
					<span>Page name: </span>
					<input type="text" name="page-name" value="{ cc:page-name }" value2="{ cc:page-name }" />
				</label>
				<xsl:for-each select="cc:caption/@lang">
					<label>
						<span>Caption (<xsl:value-of select="." />): </span>
						<input type="text" name="caption" class="{ . }" value="{ .. }" value2="{ .. }" />
					</label>
				</xsl:for-each>
				<xsl:for-each select="cc:title/@lang">
					<label>
						<span>Title (<xsl:value-of select="." />): </span>
						<input type="text" name="title" class="{ . }" value="{ .. }" value2="{ .. }" />
					</label>
				</xsl:for-each>
				<xsl:for-each select="cc:class/@lang">
					<label>
						<span>Class (<xsl:value-of select="." />): </span>
						<input type="text" name="class" class="{ . }" value="{ .. }" value2="{ .. }" />
					</label>
				</xsl:for-each>
				<xsl:for-each select="cc:lang-swap/@lang">
					<label>
						<span>Language Swap (<xsl:value-of select="." />): </span>
						<input type="text" name="lang-swap" class="{ . }" value="{ .. }" value2="{ .. }" />
						<input type="text" name="suffix" class="{ . }" value="{ ../@suffix }" value2="{ ../@suffix }" />
					</label>
				</xsl:for-each>
				<label>
					<span>Target: </span>
					<select name="target">
						<option value="">
							<xsl:if test="cc:target = ''">
								<xsl:attribute name="selected">true</xsl:attribute>
							</xsl:if>
							Self
						</option>
						<option value="new">
							<xsl:if test="cc:target = 'new'">
								<xsl:attribute name="selected">true</xsl:attribute>
							</xsl:if>
							New
						</option>
					</select>
				</label>
			</fieldset>
			<xsl:apply-templates select="cc:navigation" />
		</li>
	</xsl:template>

</xsl:stylesheet>