<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:date="http://exslt.org/dates-and-times"
    extension-element-prefixes="date">
  
  <xsl:output  method="html" indent="yes" version="4.0" />   
  
  <xsl:template match="/">

    <xsl:variable name="total" select="sum(auctions/auction[status='sold']/bids/bid/lastbid[not(. &lt; ../../bid/lastbid)]) * 3 div 100 + sum(auctions/auction[status='failed']/reserveprice) * 1 div 100"/>

    <xsl:choose>
        <xsl:when test="number($total) &gt; 0.00">
            <table>
                <tr>
                    <th>Item No</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Start Price</th>
                    <th>Reserve Price</th>
                    <th>Buy It Now Price</th>
                    <th>Seller</th>
                    <th>Last Bidder</th>
                    <th>Last Bid</th>
                    <th>Start Datetime</th>
                    <th>Expired Datetime</th>
                    <th>Status</th>
                </tr>

                <xsl:variable name="vLower" select= "'abcdefghijklmnopqrstuvwxyz'"/>
                <xsl:variable name="vUpper" select= "'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
                
                <xsl:for-each select="auctions/auction[status!='in_progress']">
                    <xsl:variable name="start" select="concat(startdate,'T',starttime)" /> 
                    <xsl:variable name="end" select="date:add($start, concat('PT', duration, 'S'))" /> 

                    <tr>              
                        <td><xsl:value-of select="auctionid"/></td>
                        <td><xsl:value-of select="itemname"/></td>
                        <td><xsl:value-of select="concat(translate(substring(category,1,1), $vLower, $vUpper), substring(category, 2))"/></td>
                        <td><xsl:value-of select="concat('$', startprice)"/></td>
                        <td><xsl:value-of select="concat('$', reserveprice)"/></td>
                        <td><xsl:value-of select="concat('$', buyitnowprice)"/></td>
                        <td><xsl:value-of select="sellerid"/></td>

                        <xsl:for-each select="bids/bid/lastbid">
                            <xsl:sort select="." data-type="number" order="descending"/>
                            <xsl:if test="position() = 1">
                                <td><xsl:value-of select="../bidderid"/></td>
                                <td><xsl:value-of select="concat('$', .)"/></td>
                            </xsl:if>
                        </xsl:for-each>

                        <td><xsl:value-of select="$start"/></td>
                        <td><xsl:value-of select="$end"/></td>
                        <td><xsl:value-of select="status"/></td>
                    </tr>                               
                </xsl:for-each>   
            </table>

            <div class="table-total">
                <span>Interest Rate (3% sold price - last bid from each sold item and 1% reserved price from each failed item)</span>
                <span>Total Revenue: <b>$<xsl:value-of select="$total"/></b></span>
            </div>  
        </xsl:when>
        <xsl:otherwise>
            <p>There are no auctions that are sold or failed.</p>
        </xsl:otherwise>
    </xsl:choose>
    
    
  </xsl:template>
</xsl:stylesheet>