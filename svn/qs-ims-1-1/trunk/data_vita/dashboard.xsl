<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="utf-8" />


	<xsl:template match="block">
		<div class="dashboard-menu">
			<div class="dashboard-menu-title">
				<xsl:value-of select="@title" />
			</div>
			<ul>
				<xsl:apply-templates select="item" />
			</ul>
		</div>
	</xsl:template>

	<xsl:template match="item">
			<li code="{@code}" path="{@href}">
				<xsl:choose>
				  <xsl:when test="@href">
					<a href="#" onclick="openModule('{@code}')">				
						<xsl:value-of select="@title" />
					</a>					
				  </xsl:when>
				  <xsl:otherwise>
					<div class="dashboard-menu-item">
						<xsl:value-of select="@title" />
					</div>					
				  </xsl:otherwise>
				</xsl:choose>
				<xsl:if test="count(item)">
					<ul>
						<xsl:apply-templates select="item" />
					</ul>
				</xsl:if>			
			</li>
	</xsl:template>
	
	
</xsl:stylesheet>