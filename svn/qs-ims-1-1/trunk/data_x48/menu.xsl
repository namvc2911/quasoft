<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="utf-8" />

	<xsl:template match="block">
		<li>
			<a class='default {@icon} level1 dropdown'>
				<xsl:value-of select="@title" />
				<span class="downarrowclass" style="display: block;">&#160;</span>
			</a>
			<ul>
					<xsl:apply-templates select="item" />
			</ul>
		</li>
	</xsl:template>

	<xsl:template match="item">
		<xsl:choose>
		<xsl:when test="count(item)">
			<li fid="{@fid}" code="{@code}" path="{@href}" >
				<xsl:choose>
				  <xsl:when test="@href">
					<a href="#" onclick="openModule('{@code}')" class="dropdown">				
						<xsl:value-of select="@title" />
					</a>					
				  </xsl:when>
				  <xsl:otherwise>
				  		<a class="dropdown">
						<xsl:value-of select="@title" />
                         <span class="downarrowclass" style="display: block;">&#160;</span>                       
					</a>
				  </xsl:otherwise>
				</xsl:choose>
				<ul>
					<xsl:apply-templates select="item" />
				</ul>
			</li>
		 </xsl:when>
		<xsl:otherwise>
			<li fid="{@fid}" code="{@code}" path="{@href}">
				<xsl:choose>
				  <xsl:when test="@href">
					<a href="#" onclick="openModule('{@code}')">				
						<xsl:value-of select="@title" />
					</a>					
				  </xsl:when>
				  <xsl:otherwise>
				  		<a>
						<xsl:value-of select="@title" />
                                                
					</a>
				  </xsl:otherwise>
				</xsl:choose>
			</li>
		</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	
</xsl:stylesheet>