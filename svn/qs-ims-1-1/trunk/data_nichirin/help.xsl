<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="utf-8" />

	<xsl:template match="/menu">
				<div id="module-menu" hide="0">
					<ul id="treemenu" class="treeview">
							<xsl:apply-templates select="block" />
					</ul>
				</div>
	</xsl:template>

	<xsl:template match="block">
		<li>
			<span class="menu-title" onclick="loadHelper('{@code}')">
				<xsl:value-of select="@title" />
			</span>
			<ul>
				<xsl:apply-templates select="item" />
			</ul>
		</li>
	</xsl:template>

	<xsl:template match="item">
			<li class="menu-item" code="{@code}" path="{@href}">
				<xsl:choose>
				  <xsl:when test="@href">
					<a class="icon-16-cpanel" href="#{@code}" onclick="loadHelper('{@code}')">				
						<xsl:value-of select="@title" />
					</a>					
				  </xsl:when>
				  <xsl:otherwise>
					<a onclick="loadHelper('{@code}')">
						<xsl:value-of select="@title" />
					</a>					
				  </xsl:otherwise>
				</xsl:choose>
				<xsl:if test="count(item)">
					<ul>
						<xsl:apply-templates select="item" />
					</ul>
				</xsl:if>			
			</li>
	</xsl:template>
	
	<xsl:template name="main_img">
		<img src="images/menu_item_bg_out.gif" border="0" align="absmiddle" width="6" height="17" class="" />
		<img src="images/menu_item_bg_over.gif" border="0" align="absmiddle" width="6" height="17" class="hidden" />	
	</xsl:template>
	<xsl:template name="sub_img">
		<img src="images/submenu_item_bg_out.gif" border="0" align="absmiddle" width="6" height="17" class="" />
		<img src="images/submenu_item_bg_over.gif" border="0" align="absmiddle" width="6" height="17" class="hidden" />	
	</xsl:template>
	
</xsl:stylesheet>