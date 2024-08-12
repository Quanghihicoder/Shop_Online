<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output  method="html" indent="yes" version="4.0" />   
  <xsl:template match="/">
    <xsl:for-each select="books/book/title[@type='fiction' and ../price &lt; 30]">
        <div>
            <span class="title">Title: <xsl:value-of select="../title"/>;</span>
            <span class="author">Authors: 
                <xsl:choose>
                    <xsl:when test="count(../authors/author) &gt; 1">
                        <xsl:value-of select="concat(../authors/author[1], ' et al.')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="../authors/author"/>
                    </xsl:otherwise>
                </xsl:choose>;
            </span>
            <span class="price">Price: <xsl:value-of select="../price"/>;</span>
        </div>
    </xsl:for-each>

    <br />
    
    <p class="total-cost">Total cost: <xsl:value-of select="sum(books/book/title[@type='fiction' and ../price &lt; 30]/../price)"/></p>
  </xsl:template>
</xsl:stylesheet>