<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc xhtml cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:include href="../../../sys/views/error.xsl" />

	<xsl:template match="xmvc:*" />

	<!-- Strip namespaces from XHTML using an identity template -->

	<xsl:template match="*[ ./xhtml:* ]">
		<xsl:apply-templates select="node()" />
	</xsl:template>

	<xsl:template match="*[ ./xhtml:* ]//xhtml:*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="@* | node()" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="*[ ./xhtml:* ]//@*">
		<xsl:attribute name="{ local-name() }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

	<!-- Generic form fields.  To override, match the same xpath but specify a higher priority number in your template -->

	<xsl:template match="cc:field[ @type = 'text' or @type = 'password' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name }">
			<xsl:if test="cc:label[ @lang = //xmvc:lang ]">
				<span><xsl:value-of select="cc:label[ @lang = //xmvc:lang ]" /></span>
			</xsl:if>
			<input type="{ @type }" id="{ @name }" name="{ @name }">
				<xsl:choose>
					<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]">
						<xsl:attribute name="value"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:attribute>
					</xsl:when>
					<xsl:when test="cc:value">
						<xsl:attribute name="value"><xsl:value-of select="cc:value[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
					</xsl:when>
				</xsl:choose>
			</input>
		</label>
		<xsl:for-each select=".//cc:math-captcha">
			<input type="hidden" id="{ @name }" name="{ @name }" value="{ @answer-md5 }" />
		</xsl:for-each>
		<xsl:apply-templates select="cc:constraint" />
	</xsl:template>

	<xsl:template match="cc:field[ @type = 'textarea' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name }">
			<xsl:if test="cc:label[ @lang = //xmvc:lang ]">
				<span><xsl:value-of select="cc:label[ @lang = //xmvc:lang ]" /></span>
			</xsl:if>
			<textarea id="{ @name }" name="{ @name }"><xsl:choose>
				<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:when>
				<xsl:when test="cc:value"><xsl:value-of select="cc:value[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:when>
			</xsl:choose></textarea>
		</label>
		<xsl:apply-templates select="cc:constraint" />
	</xsl:template>

	<xsl:template match="cc:field[ @type = 'submit' or @type = 'reset' or @type = 'button' ]" priority="0">
		<input type="{ @type }" name="{ @name }" id="{ @name }" class="{ @name }">
			<xsl:if test="cc:label[ not( @lang ) or @lang = //xmvc:lang ]">
				<xsl:attribute name="value"><xsl:value-of select="cc:label[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
			</xsl:if>
		</input>
		<xsl:apply-templates select="cc:constraint" />
	</xsl:template>

	<xsl:template match="cc:field[ @type = 'checkbox' or @type = 'radio' ]" priority="0">
		<input type="hidden" name="{ @name }" />
		<xsl:apply-templates select="cc:option" />
		<xsl:apply-templates select="cc:constraint" />
	</xsl:template>

	<xsl:template match="cc:field/cc:option[ ancestor::cc:field[1]/@type = 'checkbox' or ancestor::cc:field[1]/@type = 'radio' ]" priority="0">
		<xsl:variable name="name" select="ancestor::cc:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::cc:field[1]/@type" />
		<label for="{ $name }-{ position() }" class="{ $name }">
			<input type="{ $type }" id="{ $name }-{ position() }" name="{ $name }[]">
				<xsl:if test="cc:value">
					<xsl:attribute name="value"><xsl:value-of select="cc:value[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
					<xsl:if test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', cc:value[ not( @lang ) or @lang = //xmvc:lang ], '|' ) )">
						<xsl:attribute name="checked">true</xsl:attribute>
					</xsl:if>
				</xsl:if>
			</input>
			<xsl:if test="cc:label[ @lang = //xmvc:lang ]">
				<span><xsl:value-of select="cc:label[ @lang = //xmvc:lang ]" /></span>
			</xsl:if>
		</label>
	</xsl:template>

	<xsl:template match="cc:field[ @type = 'select' ]" priority="0">
		<select name="{ @name }" id="{ @name }">
			<xsl:apply-templates select="*[ name() = 'cc:option' or name() = 'cc:group' ]" />
		</select>
		<xsl:apply-templates select="cc:constraint" />
	</xsl:template>

	<xsl:template match="cc:field[ @type = 'multi-select' ]" priority="0">
		<input type="hidden" name="{ @name }" />
		<select name="{ @name }[]" id="{ @name }" multiple="true">
			<xsl:apply-templates select="*[ name() = 'cc:option' or name() = 'cc:group' ]" />
		</select>
		<xsl:apply-templates select="cc:constraint" />
	</xsl:template>

	<xsl:template match="cc:field//cc:option[ ancestor::cc:field[1]/@type = 'select' or ancestor::cc:field[1]/@type = 'multi-select' ]" priority="0">
		<xsl:variable name="name" select="ancestor::cc:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::cc:field[1]/@type" />
		<option>
			<xsl:if test="cc:value">
				<xsl:attribute name="value"><xsl:value-of select="cc:value[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
				<xsl:choose>
					<xsl:when test="$type = 'select'">
						<xsl:if test="//xmvc:strings/xmvc:*[ @key = $name ] = cc:value[ not( @lang ) or @lang = //xmvc:lang ]">
							<xsl:attribute name="selected">true</xsl:attribute>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', cc:value[ not( @lang ) or @lang = //xmvc:lang ], '|' ) )">
							<xsl:attribute name="selected">true</xsl:attribute>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:value-of select="cc:label[ @lang = //xmvc:lang ]" />
		</option>
	</xsl:template>

	<xsl:template match="cc:field//cc:group" priority="0">
		<optgroup>
			<xsl:if test="cc:label[ @lang = //xmvc:lang ]">
				<xsl:attribute name="label"><xsl:value-of select="cc:label[ @lang = //xmvc:lang ]" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[ name() = 'cc:option' or name() = 'cc:group' ]" />
		</optgroup>
	</xsl:template>

	<xsl:template match="cc:field/cc:constraint">
		<xsl:if test="substring( @type, 1, 6 ) = 'match-'">
			<input type="hidden" name="{ ../@name }--dependency[]" value="{ @against }" />
		</xsl:if>
	</xsl:template>

	<!-- String replace function -->

	<xsl:template name="string-replace-all">
		<xsl:param name="text" />
		<xsl:param name="replace" />
		<xsl:param name="by" />
		<xsl:choose>
			<xsl:when test="contains($text, $replace)">
				<xsl:value-of select="substring-before($text,$replace)" />
				<xsl:value-of select="$by" />
				<xsl:call-template name="string-replace-all">
					<xsl:with-param name="text" select="substring-after($text,$replace)" />
					<xsl:with-param name="replace" select="$replace" />
					<xsl:with-param name="by" select="$by" />
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$text" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>