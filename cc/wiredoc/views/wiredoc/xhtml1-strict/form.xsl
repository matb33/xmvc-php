<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc instance meta container group reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:instance="urn:cc:instance" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:group="urn:cc:group" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

	<xsl:template match="form:form" priority="0">
		<form>
			<xsl:if test="@href">
				<xsl:attribute name="action"><xsl:value-of select="@href" /></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="method">
				<xsl:choose>
					<xsl:when test="@method"><xsl:value-of select="@method" /></xsl:when>
					<xsl:otherwise>post</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@enctype">
				<xsl:attribute name="enctype"><xsl:value-of select="@enctype" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates />
		</form>
	</xsl:template>

	<xsl:template match="form:field[ @type = 'text' or @type = 'password' or @type = 'file' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<input type="{ @type }" id="{ @name }" name="{ @name }">
				<xsl:choose>
					<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]">
						<xsl:attribute name="value"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:attribute>
					</xsl:when>
					<xsl:when test="form:value">
						<xsl:attribute name="value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:attribute>
					</xsl:when>
				</xsl:choose>
			</input>
			<xsl:apply-templates select="form:info" />
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
		</label>
		<xsl:for-each select=".//form:math-captcha">
			<input type="hidden" id="{ @name }" name="{ @name }" value="{ @answer-md5 }" />
		</xsl:for-each>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'textarea' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<textarea id="{ @name }" name="{ @name }"><xsl:choose>
				<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:when>
				<xsl:when test="form:value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:when>
			</xsl:choose></textarea>
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
			<xsl:apply-templates select="form:info" />
		</label>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'submit' or @type = 'reset' or @type = 'button' ]" priority="0">
		<input type="{ @type }" name="{ @name }" id="{ @name }" class="{ @name }">
			<xsl:if test="form:label[ lang( $lang ) ]">
				<xsl:attribute name="value"><xsl:value-of select="form:label[ lang( $lang ) ]" /></xsl:attribute>
			</xsl:if>
		</input>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'checkbox' or @type = 'radio' ]" priority="0">
		<input type="hidden" name="{ @name }" />
		<xsl:apply-templates select="form:option" />
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field/form:option[ ancestor::form:field[1]/@type = 'checkbox' or ancestor::form:field[1]/@type = 'radio' ]" priority="0">
		<xsl:variable name="name" select="ancestor::form:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::form:field[1]/@type" />
		<label for="{ $name }-{ position() }" class="{ $name }">
			<xsl:apply-templates select="form:label[ @position = 'before' ]" />
			<input type="{ $type }" id="{ $name }-{ position() }" name="{ $name }[]">
				<xsl:if test="form:value">
					<xsl:attribute name="value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:attribute>
					<xsl:if test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', form:value[ lang( $lang ) ], '|' ) )">
						<xsl:attribute name="checked">true</xsl:attribute>
					</xsl:if>
				</xsl:if>
			</input>
			<xsl:apply-templates select="form:info" />
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'after' ]" />
		</label>
	</xsl:template>

	<xsl:template match="form:field[ @type = 'select' ]" priority="0">
		<label for="{ @name }" class="{ @name }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<select name="{ @name }" id="{ @name }">
				<xsl:apply-templates select="*[ name() = 'form:option' or name() = 'form:group' ]" />
			</select>
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
			<xsl:apply-templates select="form:info" />
		</label>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'multi-select' ]" priority="0">
		<label for="{ @name }" class="{ @name }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<select name="{ @name }[]" id="{ @name }" multiple="true">
				<xsl:apply-templates select="*[ name() = 'form:option' or name() = 'form:group' ]" />
			</select>
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
			<xsl:apply-templates select="form:info" />
		</label>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field//form:option[ ancestor::form:field[1]/@type = 'select' or ancestor::form:field[1]/@type = 'multi-select' ]" priority="0">
		<xsl:variable name="name" select="ancestor::form:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::form:field[1]/@type" />
		<option>
			<xsl:apply-templates select="form:label[ @position = 'before' ]" />
			<xsl:if test="form:value">
				<xsl:attribute name="value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:attribute>
				<xsl:choose>
					<xsl:when test="$type = 'select'">
						<xsl:if test="//xmvc:strings/xmvc:*[ @key = $name ] = form:value[ lang( $lang ) ]">
							<xsl:attribute name="selected">true</xsl:attribute>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', form:value[ lang( $lang ) ], '|' ) )">
							<xsl:attribute name="selected">true</xsl:attribute>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'after' ]" />
		</option>
	</xsl:template>

	<xsl:template match="form:field//form:group" priority="0">
		<optgroup>
			<xsl:if test="form:label[ lang( $lang ) ]">
				<xsl:attribute name="label"><xsl:value-of select="form:label[ lang( $lang ) ]" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[ name() = 'form:option' or name() = 'form:group' ]" />
		</optgroup>
	</xsl:template>

	<xsl:template match="form:field//form:label" priority="1">
		<xsl:if test="lang( $lang )">
			<span><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:field//form:info" priority="1">
		<xsl:if test="lang( $lang )">
			<input type="hidden" class="info" value="{ text() }" />
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:fieldset" priority="0">
		<fieldset id="{ @name }">
			<xsl:apply-templates />
		</fieldset>
	</xsl:template>

	<xsl:template match="form:fieldset/form:legend" priority="1">
		<xsl:if test="lang( $lang )">
			<legend><xsl:apply-templates /></legend>
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:field/form:constraint" priority="0">
		<xsl:if test="substring( @type, 1, 6 ) = 'match-'">
			<input type="hidden" name="{ ../@name }--dependency[]" value="{ @against }" />
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>