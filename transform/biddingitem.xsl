<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:date="http://exslt.org/dates-and-times"
    extension-element-prefixes="date">
  
  <xsl:output  method="html" indent="yes" version="4.0" />   
  
  <xsl:template match="/">
    <xsl:for-each select="auctions/auction">
        <div class="list-container-item">

            <xsl:variable name="start" select="concat(startdate,'T',starttime)" /> 
            <xsl:variable name="now" select="date:date-time()" /> 
            <xsl:variable name="timediff" select="date:seconds(date:difference($start, $now))" /> 

            <xsl:variable name="remain" select="duration - $timediff" /> 

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

                        <xsl:variable name="vLower" select= "'abcdefghijklmnopqrstuvwxyz'"/>

                        <xsl:variable name="vUpper" select= "'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
                        
                        <td><xsl:value-of select="concat(translate(substring(category,1,1), $vLower, $vUpper), substring(category, 2))"/></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <xsl:choose>
                            <xsl:when test="string-length(desc) > 30">
                                <td><xsl:value-of select="concat(substring(desc,1,30), '...')"/></td>
                            </xsl:when>
                            <xsl:otherwise>
                                <td><xsl:value-of select="desc"/></td>
                            </xsl:otherwise>
                        </xsl:choose>
                    </tr>
                    <tr>
                        <th>Buy It Now Price:</th>
                        <td><xsl:value-of select="concat('$', buyitnowprice)"/></td>
                    </tr>
                    <tr>
                        <th>Bid Price:</th>
                        <td><xsl:value-of select="concat('$', lastbid)"/></td>
                    </tr>
                    <tr>
                        <th>Status:</th>

                        <xsl:choose>
                            <xsl:when test="status='sold'">
                                <td>Sold</td>   
                            </xsl:when>

                            <xsl:when test="$remain &gt; 0 and status='in_progress'">
                                <xsl:variable name="day" select="floor(($remain) div 86400)" /> 
                                <xsl:variable name="hour" select="floor((($remain) mod 86400) div 3600)" /> 
                                <xsl:variable name="min" select="floor(((($remain) mod 86400) mod 3600) div 60)" /> 
                                <xsl:variable name="sec" select="floor(((($remain) mod 86400) mod 3600) mod 60)" /> 

                                <xsl:variable name="days">
                                    <xsl:choose>
                                        <xsl:when test="number($day) = 1">
                                            <xsl:value-of select="concat($day, ' day ')"/>
                                        </xsl:when>
                                        <xsl:when test="number($day) &gt; 1">
                                            <xsl:value-of select="concat($day, ' days ')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="''"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:variable>

                                <xsl:variable name="hours">
                                    <xsl:choose>
                                        <xsl:when test="number($hour) = 1">
                                            <xsl:value-of select="concat($days, $hour, ' hour ')"/>
                                        </xsl:when>
                                        <xsl:when test="number($hour) &gt; 1">
                                            <xsl:value-of select="concat($days, $hour, ' hours ')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="$days"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:variable>

                                <xsl:variable name="minutes">
                                    <xsl:choose>
                                        <xsl:when test="number($min) = 1">
                                            <xsl:value-of select="concat($hours, $min, ' minute ')"/>
                                        </xsl:when>
                                        <xsl:when test="number($min) &gt; 1">
                                            <xsl:value-of select="concat($hours, $min, ' minutes ')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="$hours"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:variable>

                                <xsl:variable name="seconds">
                                    <xsl:choose>
                                        <xsl:when test="number($sec) = 1">
                                            <xsl:value-of select="concat($minutes, $sec, ' second ')"/>
                                        </xsl:when>
                                        <xsl:when test="number($sec) &gt; 1">
                                            <xsl:value-of select="concat($minutes, $sec, ' seconds ')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="$minutes"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:variable>

                                <td><xsl:value-of select="concat($seconds, ' remaining')"/></td>
                            </xsl:when>

                            <xsl:otherwise>
                                <td>Time expired</td>   
                            </xsl:otherwise>
                        </xsl:choose>                        
                    </tr>
                </table>
            </div>
            <xsl:choose>
                <xsl:when test="$remain &gt; 0 and status='in_progress'">
                    <div class="item-button">
                        <input class="button-secondary button-mid" id="" onclick="bidItem({auctionid})" type="button" value="Place Bid"/>
                        <input class="button-primary button-mid" id="" onclick="buyItem({auctionid})" type="button" value="Buy It Now"/>
                    </div>
                </xsl:when>
            </xsl:choose>                                   
        </div>
    </xsl:for-each>    
  </xsl:template>
</xsl:stylesheet>