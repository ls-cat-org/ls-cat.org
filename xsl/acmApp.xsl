<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="UTF-8" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" indent="yes" media-type="text/html"/>
  
  <xsl:template match="/acmApp">
      <style media="all" type="text/css">
	<![CDATA[
* {
  margin: 0;
  padding: 0;
}

form {
  margin: 0;
  padding: 0;
  font-size: 100%;
  min-width: 660px;
  max-width: 720px;
  width: 690px;
}
form fieldset {
  clear: both;
  font-size: 100%;
  border-color: #000000;
  border-width: 1px 0 0 0;
  border-style: solid none none none;
  padding: 10px;
  margin: 0 0 0 0;
}

form fieldset legend {
  font-size: 150%;
  font-weight: normal;
  color: #000000;
  margin: 0 0 0 0;
  padding: 0 5px;
}

label {
  font-size: 100%;
}

label u {
  font-style: normal;
  text-decoration: underline;
}

input, select, textarea {
  font-family: Tahoma, Arial, sans-serif;
  font-size: 100%;
  color: #000000;
}

textarea {
  overflow: auto;
}

form div {
  clear: left;
  display: block;
  width: 454px;
  zoom: 1;
  margin: 5px 0 0 0;
  padding: 1px 3px;
}

form fieldset div.notes {
  float: right;
  width: 158px;
  height: auto;
  margin: 0 0 10px 10px;
  padding: 5px;
  border: 1px solid #666666;
  background-color: #ffffe1;
  color: #666666;
  font-size: 88%;
}

form fieldset div.notes h4 {
  background-image: url(/icon_info.gif);
  background-repeat: no-repeat;
  background-position: top left;
  padding: 3px 0 3px 27px;
  border-width: 0 0 1px 0;
  border-style: solid;
  border-color: #666666;
  color: #666666;
  font-size: 110%;
}

form fieldset div.notes p {
  margin: 0em 0em 1.2em 0em;
  color: #666666;
}

form fieldset div.notes p.last {
  margin: 0em;
}

form div fieldset {
  clear: none;
  border-width: 1px;
  border-style: solid;
  border-color: #666666;
  margin: 0 0 0 144px;
  padding: 0 5px 5px 5px;
  width: 297px;
}

form div fieldset legend {
  font-size: 100%;
  padding: 0 3px 0 9px;
}

form div.required fieldset legend {
  font-weight: bold;
}

form div label {
  display: block;
  float: left;
  width: 130px;
  padding: 3px 5px;
  margin: 0 0 5px 0;
  text-align: right;
}

form div.optional label, label.optional {
  font-weight: normal;
}

form div.required label, label.required {
  font-weight: bold;
}

form div label.labelCheckbox, form div label.labelRadio {
  float: none;
  display: block;
  width: 300px;
  zoom: 1;
  padding: 0;
  margin: 0 0 5px 142px;
  text-align: left;
}

form div fieldset label.labelCheckbox, form div fieldset label.labelRadio {
  margin: 0 0 5px 0;
  width: 270px;
}

form div img {
  border: 1px solid #000000;
}

p.error {
  background-color: #ff0000;
  background-image: url(/icon_error.gif);
  background-repeat: no-repeat;
  background-position: 3px 3px;
  color: #ffffff;
  padding: 3px 3px 5px 27px;
  border: 1px solid #000000;
  margin: auto 100px;
}

form div.error {
  background-color: #ffffe1;
  background-image: url(/required_bg.gif);
  background-repeat: no-repeat;
  background-position: top left;
  color: #666666;
  border: 1px solid #ff0000;
}

form div.error p.error {
  background-image: url(/icon_error.gif);
  background-position: top left;
  background-color: transparent;
  border-style: none;
  font-size: 88%;
  font-weight: bold;
  margin: 0 0 0 118px;
  width: 300px;
  color: #ff0000;
}

form div select, form div textarea {
  width: 300px;
  padding: 1px 3px;
  margin: 0 0 0 0;
}

form div input.inputText, form div input.inputPassword {
  width: 300px;
  padding: 1px 3px;
  margin: 0 0 0 0;
}

form div input.inputFile {
  width: 311px;
}

form div select.selectOne, form div select.selectMultiple {
  width: 311px;
  padding: 1px 3px;
}

form div input.inputCheckbox, form div input.inputRadio, input.inputCheckbox, input.inputRadio {
  display: inline;
  height: auto;
  width: auto;
  background-color: transparent;
  border-width: 0;
  padding: 0;
  margin: 0 0 0 140px;
}

form div.submit {
  width: 314px;
  padding: 0 0 0 146px;
}

form div.submit div {
  display: inline;
  float: left;
  text-align: left;
  width: auto;
  padding: 0;
  margin: 0;
}

form div input.inputSubmit, form div input.inputButton, input.inputSubmit, input.inputButton {
  background-color: #cccccc;
  color: #000000;
  width: auto;
  padding: 0 6px;
  margin: 0;
}

form div.submit div input.inputSubmit, form div.submit div input.inputButton {
  float: right;
  margin: 0 0 0 5px;
}

form div small {
  display: block;
  margin: 0 0 5px 142px;
  padding: 1px 3px;
  font-size: 88%;
  zoom: 1;
}

        ]]>
      </style>
    <html>
      <body>
	<form action="acmApp.php" method="post">
	  <fieldset><legend>Contact Information</legend>
	    <div class="notes">
	      <h4>Contact Information</h4>
	      <p class="last">Please let us know whom to contact about this request</p>
	    </div>
	    <div>
	      <xsl:choose>
		<xsl:when test="//givenName=''">
		  <xsl:attribute name="class">required error</xsl:attribute>
		</xsl:when>
		<xsl:otherwise>
		  <xsl:attribute name="class">required</xsl:attribute>
		</xsl:otherwise>
	      </xsl:choose>
	      <label for="givenName">Given Name:</label>
	      <input type="text" name="givenName" id="givenName" class="inputText" size="10" maxlength="100" value="{givenName}"/>
	    </div>
	    <div class="required">
	      <label for="familyName">Family Name:</label>
	      <input type="text" name="familyName" id="familyName" class="inputText" size="10" maxlength="100" value="{familyName}"/>
	    </div>
	    <div class="required">
	      <label for="institution">Institution:</label>
	      <input type="text" name="institution" id="institution" class="inputText" size="10" maxlength="100" value="{institution}"/>
	    </div>
	    <div class="optional">
	      <label for="address1">Address:</label>
	      <input type="text" name="address1" id="address1" class="inputText" size="10" maxlength="100" value="{address1}"/>
	      <input type="text" name="address2" id="address2" class="inputText" size="10" maxlength="100" value="{address2}"/>
	    </div>
	    <div class="optional">
	      <label for="city">City:</label>
	      <input type="text" name="city" id="city" class="inputText" size="10" maxlength="100" value="{city}"/>
	    </div>
	    <div class="optional">
              <label for="state">State:</label>
	      <input type="text" name="state" id="state" class="inputText" size="10" maxlength="50" value="{state}"/>
	    </div>
	    <div class="optional">
              <label for="postalCode">Zip/Postal Code:</label>
              <input type="text" name="postalCode" id="postalCode" class="inputText" size="10" maxlength="50" value="{postalCode}" />
	    </div>
	    <div class="optional">
              <label for="country">Country:</label>
	      <input type="text" name="country" id="country" class="inputText" size="20" maxlength="50" value="{country}" />
	    </div>
	    <div class="required">
	      <label for="voice">Voice Phone:</label>
	      <input type="text" name="voice" id="voice" class="inputText" maxlength="50" value="{voice}"/>
	    </div>
	    <div class="optional">
	      <label for="fax">Fax Phone:</label>
	      <input type="text" name="fax" id="fax" class="inputText" maxlength="50" value="{fax}"/>
	    </div>
	    <div class="required">
	      <label for="email">Email Address:</label>
	      <input type="text" name="email" id="email" class="inputText" maxlength="100" value="{email}"/>
	    </div>
	  </fieldset>
	  <fieldset><legend>Scientific Needs</legend>
	    <div class="notes">
	      <h4>Purpose and Justification</h4>
	      <p>The need for LS-CAT beamline must be demonstrated and justified as we do not allocate beamtime otherwise.</p>
	      <p class="last">For non-proprietary use of LS-CAT please select "Academic or Educational Use" box.</p>
	    </div>
	    <div class="required">
	      <label for="usage">Expected Usage:</label>
	      <select name="usage" id="usage" class="selectOne">
		<option value="proprietary">
		  <xsl:if test="//usage='proprietary'">
		    <xsl:attribute name="selected">selected</xsl:attribute>
		  </xsl:if>
		  Proprietary Work
		</option>
		<option value="acOrEd">
		  <xsl:if test="//usage = 'acOrEd'">
		    <xsl:attribute name="selected">selected</xsl:attribute>
		  </xsl:if>
		  Acedemic or Educational Use
		</option>
	      </select>
	    </div>
	    <div class="required wide">
	      <label for="purpose">Purpose:</label>
	      <textarea name="purpose" id="purpose" class="inputTextarea" rows="10" cols="21"><xsl:value-of select="purpose"/></textarea>
	      <small>Please explain generally what you intend to use the beamtime for.</small>
	    </div>
	    <div class="required wide">
	      <label for="need">Need for LS-CAT and the APS:</label>
	      <textarea name="need" id="need" class="inputTextarea" rows="10" cols="21"><xsl:value-of select="need"/></textarea>
	    </div>
	  </fieldset>
	  <!--
	  <fieldset><legend>Account Information</legend>
	    <div class="notes">
	      <h4>Authentication</h4>
	      <p>You'll use this to procure the beamtime you need.</p>
	      <p class="last">Your email address is your user name.</p>
	    </div>
	    <div class="required">
	      <label for="password1">Password:</label>
	      <input type="password" name="password1" id="password1" class="inputPassword" size="10" maxlength="25" value="{password1}"/>
	    </div>
	    <div class="required">
	      <label for="password2">Again:</label>
	      <input type="password" name="password2" id="password2" class="inputPassword" size="10" maxlength="25" value="{password2}"/>
	    </div>
	  </fieldset>
	  -->
	  <fieldset>
	    <div class="submit">
	      <input name="subbieInput" id="subbieInput" type="submit" class="inputSubmit" value="Submit &#x2794;"/>
	    </div>
	  </fieldset>
	</form>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>

