<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output  method="html" indent="yes" version="4.0" />   
  <xsl:template match="/">
    <xsl:for-each select="customers/customer[@status='in_progress']">
        <div class="list-container-item">
            <div class="item-content">
                <table>
                    <tr>
                        <th>Item No:</th>
                        <td><xsl:value-of select="auctionid"/></td>
                    </tr>
                    <tr>
                        <th>Item Name:</th>
                        <td><xsl:value-of select="itemname"/></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><xsl:value-of select="category"/></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td><xsl:value-of select="desc"/></td>
                    </tr>
                    <tr>
                        <th>Buy It Now Price:</th>
                        <td><xsl:value-of select="buyitnowprice"/></td>
                    </tr>
                    <tr>
                        <th>Bid Price:</th>
                        <td><xsl:value-of select="lastbid"/></td>
                    </tr>
                    <tr>
                        <th>Remaining Time:</th>
                        <td><xsl:value-of select="duration"/></td>
                    </tr>
                </table>
            </div>
            <div class="item-button">
                <input class="button-secondary button-mid" id="" type="button" value="Place Bid"/>
                <input class="button-primary button-mid" id="" type="button" value="Buy It Now"/>
            </div>
        </div>
    </xsl:for-each>    
  </xsl:template>
</xsl:stylesheet>