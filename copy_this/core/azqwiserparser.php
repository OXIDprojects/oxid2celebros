<?php
/**
 *    This module is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This module is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.anzido.com
 * @copyright © OXID eSales AG, anzido GmbH 2009
 */
 
 // Celebros Qwiser v4 for OXID eShop EE2.7

class azQwiserParser
{

	// Qwiser XMLservice data
	var $oParser;
	var $sRequest;

	var $oResult;

	var $sCurTag = '$this->oResult';
    var $aCurTags = array();
	var $sCurAttr = '';
	var $sCurCdata = '';

    //encoding related
	var $sSourceEnconing;
	var $sTargetEncoding;
    var $sEncodingMethod; 

	var $aCounters = array ();
	var $aCounterReseters = array ();
	var $aAttributesAsAssocArray = array ();
	var $aIgnoredTags = array ();
	var $sReplaceArrayNamesWith = '';
	var $sArrayCountPropertyName = '';

	// Error state
	var $sLastOperationErrorMessage;
	var $blLastOperationSucceeded;
    
    // HtmlEntities specific var
    var $aRestoreHtmlEntities = array();

	function azQwiserParser($sRequest)
	{
		$this->sRequest = $sRequest;
		$this->blLastOperationSucceeded = 1;
        $this->sLastOperationErrorMessage = '';

		$this->aCounters = array (
			"Question" => 0,
			"Answer" => 0,
			"Product" => 0,
			"Value" => 0,
			"Concept" => 0,
            "Entry" =>0,
            "Value" => 0,
            "SiteStatus" => 0,
            "QwiserError" => 0
		);
        
		$this->aCounterReseters = array (
			"Questios" => "Question",
			"Answers" => "Answer",
            "ExtraAnswers" => "Answer",
			"Products" => "Product",
			"AddtionalSuggestions" => "Value",
			"SpecialCasesDetectedInThisSearch" => "Value",
			"QueryConcepts" => "Concept",
            "SearchPath" =>"Entry",
            "AddtionalSuggestions" => "Value",
            "SpecialCasesDetectedInThisSession" => "Value",
            "SearchEngineStatus" => "SiteStatus",
            "Last5Errors" => "QwiserError"
		);
        
		$this->aAttributesAsAssocArray = array (
			"name",
			"value"
		);
        
		$this->sReplaceArrayNamesWith = "Items";
		$this->sArrayCountPropertyName = "Count";
        
        $this->oResult = new stdClass();
        
        $this->aCurTags = array();
        
        $this->iEncodingMethod = 0;
        $this->sSourceEnconing = 0;
        $this->sTargetEncoding = 0;
	}
    
    // 0=disabled, 1=xml_parser, 2=htmlentities, 3=iconv, 4=mb, 2=recode
    function set_enconig_converter($sSourceEnconing , $sTargetEncoding , $iEncodingMethod)
    {
    	$this->sSourceEnconing = $sSourceEnconing;
        $this->sTargetEncoding = $sTargetEncoding;
        $this->iEncodingMethod = $iEncodingMethod;
        
        switch ($this->iEncodingMethod) 
        {
            case 2:
            {
                if(!function_exists("htmlentities"))
                {
                	$this->sLastOperationErrorMessage = "Could not set encoding converter method ".$this->iEncodingMethod;
                    $this->blLastOperationSucceeded = 0;
                }
                else
                {
                    // we need this to restore HTML tags from entities in strings ; 
                    $this->aRestoreHtmlEntities = array_flip(get_html_translation_table(HTML_SPECIALCHARS,ENT_COMPAT));
                }
                
                break;
            }
            
            case 3:
            {
                if(!function_exists("iconv"))
                {
                    $this->sLastOperationErrorMessage = "Could not set encoding converter method ".$this->iEncodingMethod;
                    $this->blLastOperationSucceeded = 0;
                }
                break;
            }
            
            case 4:
            {
                if(!function_exists("mb_convert_encoding"))
                {
                    $this->sLastOperationErrorMessage = "Could not set encoding converter method ".$this->iEncodingMethod;
                    $this->blLastOperationSucceeded = 0;
                } 
                break;
            }  
            
            case 5:
            {
                if(!function_exists("recode_string"))
                {
                    $this->sLastOperationErrorMessage = "Could not set encoding converter method ".$this->iEncodingMethod;
                    $this->blLastOperationSucceeded = 0;
                } 
            }
            
        }
    }
    
    function convert_encoding($sInput)
    {
    	switch ($this->iEncodingMethod) 
        {
    		
			case 2:
            {
				return strtr(htmlentities($sInput, ENT_COMPAT, $this->sSourceEnconing),$this->aRestoreHtmlEntities);
				break;
            }
            case 3:
            {
                return iconv($this->sSourceEnconing, $this->sTargetEncoding, $sInput); 
                break;
            }
            case 4:
            {
            	if($this->sSourceEnconing)
                    return mb_convert_encoding ( $sInput, $this->sTargetEncoding , $this->sSourceEnconing );
                else
                    return mb_convert_encoding ( $sInput, $this->sTargetEncoding ); 
                break;
            }  
            case 5:
            {
                return recode_string($this->sSourceEnconing."..".$this->sTargetEncoding , $sInput);
                break;
            } 
		
			default:
            {
                return $sInput; 
				break;
            }
		}
    }

	function run()
	{
		//echo "<li>XML source: <a  terget='_blank' href='{$this->sRequest}'>{$this->sRequest}</a> [{$this->sSourceEnconing}|{$this->sTargetEncoding}]</li>";
        
        // this aproach is used, because functio fopen throws 2 warnings if url not fount
        $fp = @fopen($this->sRequest, "r");
        if ( $fp === false )
		{
			$this->sLastOperationErrorMessage = "could not open XML input";
			$this->blLastOperationSucceeded = 0;

			return;
		}

		//setting Source Encoding
		if ($this->iEncodingMethod === 1 && $this->sSourceEnconing)
		{
			$this->oParser = xml_parser_create($this->sSourceEnconing);
		} 
        else
		{
			$this->oParser = xml_parser_create();
		}

		//setting Target Encoding
		if ($this->iEncodingMethod === 1 && $this->sTargetEncoding)
		{
			xml_parser_set_option($this->oParser, XML_OPTION_TARGET_ENCODING, $this->sTargetEncoding);
		}

		xml_parser_set_option($this->oParser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->oParser, XML_OPTION_SKIP_WHITE, 1);

		xml_set_object($this->oParser, $this);
		xml_set_element_handler($this->oParser, 'startElementHandler', 'endElementHandler');
		xml_set_character_data_handler($this->oParser, 'characterDataHandler');

		while ($data = fread($fp, 4096))
		{
			if (!xml_parse($this->oParser, $data, feof($fp)))
			{
				$this->sLastOperationErrorMessage = sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($this->oParser)), xml_get_current_line_number($this->oParser));
				$this->blLastOperationSucceeded = 0;
				return;
			}
		}

		xml_parser_free($this->oParser);
        
        return $this->oResult;
	}

	function startElementHandler($oParser, $sName, $aAttributes)
	{
		//array_push($this->aCurTags,$sName);
        //echo "<li>".implode("/",$this->aCurTags)."</li>"; 
        
        if (array_key_exists($sName, $this->aCounterReseters))
		{
			$this->aCounters[$this->aCounterReseters[$sName]] = 0;
		}

		if (array_key_exists($sName, $this->aCounters))
		{

			if (!empty ($this->sArrayCountPropertyName))
			{
				$sCounterNode = $this->sCurTag . '->' . $this->sArrayCountPropertyName . '=' . ($this->aCounters[$sName] + 1) . ';';
				eval ($sCounterNode);
			}

			if ($this->sReplaceArrayNamesWith)
			{
				$this->sCurTag .= '->' . $this->sReplaceArrayNamesWith;
			} 
            else
			{
				$this->sCurTag .= '->' . $sName;
			}

			$this->sCurTag .= '[' . $this->aCounters[$sName] . ']';
			$this->aCounters[$sName]++;
		} 
        else
		{
			$this->sCurTag .= '->' . $sName;
		}

		$iAttributesCount = count($aAttributes);

		if ($iAttributesCount)
		{
            $blAttributerAsAssocArray = ($iAttributesCount == 2 && count(array_diff(array_keys($aAttributes), $this->aAttributesAsAssocArray)) == 0) ? true : false;

			if ($blAttributerAsAssocArray)
			{
				$sNode = $this->sCurTag . '["' . $aAttributes[$this->aAttributesAsAssocArray[0]] . '"]="' . addslashes($this->convert_encoding($aAttributes[$this->aAttributesAsAssocArray[1]])) . '";';
				eval ($sNode);
			} 
            else
			{
				foreach ($aAttributes as $name => $value)
				{
					$sNode = $this->sCurTag . '->' . $name . '="' . addslashes($this->convert_encoding($value)) . '";';
					eval ($sNode);
				}

			}
		}
        
	}

	function endElementHandler($oParser, $sName)
	{
		//array_pop($this->aCurTags);
        
        if (!empty ($this->sCurCdata))
		{
			$sNode = $this->sCurTag . "='".addslashes($this->convert_encoding($this->sCurCdata))."';";
			eval ($sNode);
		}
        
		$this->sCurTag = substr($this->sCurTag, 0, strrpos($this->sCurTag, '->'));
		$this->sCurCdata = '';
	}

	function characterDataHandler($oParser, $sData)
	{
		$sData = str_replace(rawurldecode("%09"), "", $sData);
		if (($this->sCurCdata == '') && (str_replace("\n", '', $sData) == ''))
		{
			$sData = str_replace("\n", '', $sData);
		}
		if ($sData != '')
		{
			$this->sCurCdata .= $sData;
		}

	}

}
?>