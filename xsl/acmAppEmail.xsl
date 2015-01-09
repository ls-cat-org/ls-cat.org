<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
  
  <xsl:template match="/acmApp">
                 Given Name: <xsl:value-of select="givenName"/>
                Family Name: <xsl:value-of select="familyName"/>
                Institution: <xsl:value-of select="institution"/>
                    Address:
           <xsl:value-of select="address1"/>
           <xsl:value-of select="address2"/>
           <xsl:value-of select="city"/>, <xsl:value-of select="state"/>  <xsl:value-of select="postalCode"/>
           <xsl:value-of select="country"/>

                      Phone: <xsl:value-of select="voice"/>
                        Fax: <xsl:value-of select="fax"/>

              Email Address: <xsl:value-of select="email"/>

             Expected Usage: <xsl:value-of select="usage"/>
                    Purpose: <xsl:value-of select="purpose"/>
Need for LS-CAT and the APS: <xsl:value-of select="need"/>

  </xsl:template>

</xsl:stylesheet>
