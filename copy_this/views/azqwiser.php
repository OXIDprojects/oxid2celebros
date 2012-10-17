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
class azQwiser extends oxUBase
{
	/**
	 * Current class template name.
	 * @var string
	 */
	public $sThisTemplate = "azqwiser.tpl";
	
	public $sThisAction = "azqwiser";

	/**
	 * Fetches such parameters as page number, search string and loads
	 * the shop articles.
	 */
	public function init()
	{

		parent :: init();

        $this->oAPI = oxNew( "azqwiserapi"); 
	}

	/**
	* Generates page navigation and returns the name of template file "search.tpl".
	* @return string
	*/
	public function render()
	{
		parent :: render();

        // default config options, can be owerrided in config.inc.php
        
            // Question config
            $iQuiser_max_lead_answers = 3;
            $blQuiser_show_full_lead_answers = true;
            $iQuiser_max_non_lead_questions = -1;
            $iQuiser_max_non_lead_answers = 5;
            
            //Default values for advanced search
            $sDefaultSearchProfile = "QwiserDefaultSearchProfile";
            $sDefaultAnswerId = "";
            $sDefaultEffectOnSearchPath  = "2";
            $sDefaultPriceColum = "";
            $iDefaultPageSize  = "10";
            $sDefaultSortingfield = "";
            $bDefaultNumericsort = "false";
            $bDefaultAscending = "false";
    
            // Sorting compatability qwiser<=>oxid
            // needs to be modified if more sorting fiels are used
            $aSortingFields = array("oxtitle"=>"title", "oxprice"=>"price");
            $aSortingIsNumeric = array("oxtitle"=>"False", "oxprice"=>"True");
            $aSortingOrder = array("asc"=>"true", "desc"=>"false");
            
            //include ( getShopBasePath()."modules/qwiser/config.inc.php");
            	$iQuiser_max_lead_answers = $this->getConfig()->getConfigParam('iQuiser_max_lead_answers');
                $blQuiser_show_full_lead_answers = $this->getConfig()->getConfigParam('blQuiser_show_full_lead_answers');
                $blQuiser_show_full_lead_answers = $this->getConfig()->getConfigParam('blQuiser_show_full_lead_answers');
                $iQuiser_max_non_lead_answers = $this->getConfig()->getConfigParam('iQuiser_max_non_lead_answers');        
                
                $sDefaultSearchProfile = $this->getConfig()->getConfigParam('sQuiser_DefaultSearchProfile');
                $sDefaultAnswerId = $this->getConfig()->getConfigParam('sQuiser_DefaultAnswerId');
                $sDefaultEffectOnSearchPath = $this->getConfig()->getConfigParam('sQuiser_DefaultEffectOnSearchPath');
                $sDefaultPriceColum = $this->getConfig()->getConfigParam('sQuiser_DefaultPriceColum');
                $iDefaultPageSize = $this->getConfig()->getConfigParam('iQuiser_DefaultPageSize');
                $sDefaultSortingfield = $this->getConfig()->getConfigParam('sQuiser_DefaultSortingfield');
                $bDefaultNumericsort = $this->getConfig()->getConfigParam('bQuiser_DefaultNumericsort');
                $bDefaultAscending = $this->getConfig()->getConfigParam('bQuiser_DefaultAscending');            
            
                $aSortingFields = $this->getConfig()->getConfigParam('aQuiser_SortingFields');
                $aSortingIsNumeric = $this->getConfig()->getConfigParam('aQuiser_SortingIsNumeric');
                $aSortingOrder = $this->getConfig()->getConfigParam('aQuiser_SortingOrder');
                   
        	

        $blShowSorting = $this->getConfig()->getConfigParam('blShowSorting');
        $aSortColumns = $this->getConfig()->getConfigParam('aSortCols');
        
        $sInitialSearchStr = $sSearchStr = rawurldecode($this->getConfig()->getParameter("searchparam"));
		if (!isset ($sInitialSearchStr)) // 2.1.3 compat.
        {
			$sInitialSearchStr = $sSearchStr = rawurldecode($this->getConfig()->getParameter("sSearchParam",true));
        }   
        // convert extended chars
        $sSearchStr = rawurlencode($this->ReplaceExtendedChars( $sSearchStr));
        
        $sSearchHandle = $this->oAPI->SearchHandle_decode($this->getConfig()->getParameter( "sQWSearchHandle"));
        
        $iAction = $this->getConfig()->getParameter( "iQWAction");
        if (!isset ($iAction))
            $iAction = 0;
        
        //if page size changes, and action is not 5
        if( isset($_REQUEST["_artperpage"]) && $iAction!=5)
        {
            $iAction = "5";
            $this->setRequestParameter( "iQWPageSize",$this->getConfig()->getParameter( "_artperpage"));
            
            $this->getSession()->setVar("_artperpage", $this->getConfig()->getParameter( "_artperpage") );
        }
        
        //if sorting changes and action is not 10
        if($blShowSorting && count($aSortColumns) > 0 && $iAction!=10)
        {
            if( isset($_REQUEST["listorderby"]) && isset($_REQUEST["listorder"]) )
            {
                $iAction = "10";
        
                $this->setRequestParameter( "sQWSortFieldName",strtr( $this->getConfig()->getParameter( "listorderby"),$aSortingFields));
                $this->setRequestParameter( "iQWSortAscending",strtr( $this->getConfig()->getParameter( "listorder"),$aSortingOrder));
                $this->setRequestParameter( "bQWNumericSort",strtr( $this->getConfig()->getParameter( "listorderby"),$aSortingIsNumeric));
                
                $this->getSession()->setVar("listorderby", $this->getConfig()->getParameter( "listorderby") );
                $this->getSession()->setVar("listorder", $this->getConfig()->getParameter( "listorder") );         
            }
        }
        
        //if we need to perform advanced search
        if ( $iAction == 0 && ( ($this->getConfig()->getParameter("listorderby") && $this->getConfig()->getParameter("listorder")) || $this->getConfig()->getParameter("_artperpage") ) )
        {
        	$iAction = "6";
            
            if( $this->getConfig()->getParameter("_artperpage"))
            {
                $this->setRequestParameter( "iQWPageSize",$this->getConfig()->getParameter( "_artperpage"));
            }
            
            if( $this->getConfig()->getParameter("listorderby") && $this->getConfig()->getParameter("listorder") )
            {
            	$this->setRequestParameter( "sQWSortFieldName",strtr( $this->getConfig()->getParameter( "listorderby"),$aSortingFields));
                $this->setRequestParameter( "iQWSortAscending",strtr( $this->getConfig()->getParameter( "listorder"),$aSortingOrder));
                $this->setRequestParameter( "bQWNumericSort",strtr( $this->getConfig()->getParameter( "listorderby"),$aSortingIsNumeric));
            }
            
            if(!$this->getConfig()->getParameter( "sQWSearchProfile"))       $this->setRequestParameter( "sQWSearchProfile",$sDefaultSearchProfile);
            if(!$this->getConfig()->getParameter( "sQWAnswerId"))            $this->setRequestParameter( "sQWAnswerId",$sDefaultAnswerId);
            if(!$this->getConfig()->getParameter( "sQWEffectOnSearchPath"))  $this->setRequestParameter( "sQWEffectOnSearchPath",$sDefaultEffectOnSearchPath);
            if(!$this->getConfig()->getParameter( "sQWPriceColum"))          $this->setRequestParameter( "sQWPriceColum",$sDefaultPriceColum);
            if(!$this->getConfig()->getParameter( "iQWPageSize"))            $this->setRequestParameter( "iQWPageSize",$iDefaultPageSize);
            if(!$this->getConfig()->getParameter( "sQWSortFieldName"))       $this->setRequestParameter( "sQWSortFieldName",$sDefaultSortingfield);
            if(!$this->getConfig()->getParameter( "iQWSortAscending"))       $this->setRequestParameter( "iQWSortAscending",$bDefaultAscending);
            if(!$this->getConfig()->getParameter( "bQWNumericSort"))         $this->setRequestParameter( "bQWNumericSort",$bDefaultNumericsort);
        }
            
		switch ($iAction)
		{
			case "1" : // Set page
                $iPage = $this->getConfig()->getParameter( "iQWPage");
				$qsr = $this->oAPI->MoveToPage($sSearchHandle, $iPage);
				break;
			case "2" : // Answer
                $sAnswerId = $this->getConfig()->getParameter( "sQWAnswerId");
				$qsr = $this->oAPI->AnswerQuestion($sSearchHandle, $sAnswerId, '1');
				break;
			case "3" : // Remove answers
                $iStartIndex = $this->getConfig()->getParameter( "iQWStartIndex");
				$qsr = $this->oAPI->RemoveAnswersFrom($sSearchHandle, $iStartIndex);
				break;
			case "4" : // First question
                $sQuestionId = $this->getConfig()->getParameter( "sQWQuestionId");
				$qsr = $this->oAPI->ForceQuestionAsFirst($sSearchHandle, $sQuestionId);
				break;
			case "5" : //Set Page Size
                $iPageSize = $this->getConfig()->getParameter( "iQWPageSize");
				$qsr = $this->oAPI->ChangePageSize($sSearchHandle, $iPageSize);
				break;
			case "6" : //Advanced Search
                $sSearchProfile = $this->getConfig()->getParameter( "sQWSearchProfile");
                $sAnswerId = $this->getConfig()->getParameter( "sQWAnswerId");
                $sEffectOnSearchPath  = $this->getConfig()->getParameter( "sQWEffectOnSearchPath");
                $sPriceColum = $this->getConfig()->getParameter( "sQWPriceColum");
                $iPageSize  = $this->getConfig()->getParameter( "iQWPageSize");
                $sSortingfield = $this->getConfig()->getParameter( "sQWSortFieldName");
                $bNumericsort = $this->getConfig()->getParameter( "iQWSortAscending");
                $bAscending = $this->getConfig()->getParameter( "bQWNumericSort");
                
				$qsr = $this->oAPI->SearchAdvance($sInitialSearchStr, $sSearchProfile,$sAnswerId,$sEffectOnSearchPath,$sPriceColumn,$iPageSize,$sSortingfield,$bNumericsort,$bAscending);
				break;
			case "7" : //Custom Results
                $sNewSearch = $this->getConfig()->getParameter( "sQWNewSearch");
                $sPreviousSearchHandle = $this->getConfig()->getParameter( "sQWPreviousSearchHandle");
				$qsr = $this->oAPI->GetCustomResults($sSearchHandle, $sNewSearch, $sPreviousSearchHandle);
				break;
			case "8" : //Change Price Colum
                $sPriceColum = $this->getConfig()->getParameter( "sQWPriceColum");
				$qsr = $this->oAPI->ChangePriceColumn($sSearchHandle, $sPriceColum);
				break;
			case "9" : //Activate Profile
                $sSearchProfile = $this->getConfig()->getParameter( "sQWSearchProfile");
				$qsr = $this->oAPI->ActivateProfile($sSearchHandle, $sSearchProfile);
				break;
			case "10" : //set sort by
                $sSortFieldName = $this->getConfig()->getParameter( "sQWSortFieldName");    
                $iSortAscending = $this->getConfig()->getParameter( "iQWSortAscending");
                $bNumericSort = $this->getConfig()->getParameter( "bQWNumericSort");
                
                $asc = $iSortAscending;
                if ($asc == "")$asc = "true";
                
				switch ($sSortFieldName)
				{
					case "Relevancy" : //SortByRelevancy
						$qsr = $this->oAPI->SortByRelevancy($sSearchHandle);
						break;
					case "Price" : //SortByPrice
						$qsr = $this->oAPI->SortByPrice($sSearchHandle, $asc);
						break;
					default : //SortByField
						$qsr = $this->oAPI->SortByField($sSearchHandle, $sSortFieldName, $bNumericSort, $asc);
						break;
				}
				break;                    
			default :
				$qsr = $this->oAPI->Search($sInitialSearchStr);
		}
        
        
        
        $this->_aViewData['searchparam']         = $sInitialSearchStr;
        $this->_aViewData['searchparamforhtml']  = $sInitialSearchStr;
        
        //Check if we had any Errors
        if (!$this->oAPI->blLastOperationSucceeded)
        {
            //die ("Qwiser: ".$this->oAPI->sLastOperationErrorMessage);
            $this->_aViewData['ErrorMessage'] = $this->oAPI->sLastOperationErrorMessage;
            return $this->sThisTemplate;
        }

        $sSearchHandle = $this->oAPI->SearchHandle_encode($qsr->SearchHandle);

        if(isset($qsr->SearchInformation->Query))
        {
        	//$this->_aViewData['searchparam'] = htmlentities($qsr->SearchInformation->Query);
            $this->_aViewData['searchparam']         = $qsr->SearchInformation->Query;
            $this->_aViewData['searchparamforhtml']  = $qsr->SearchInformation->Query;
        }
        
        $sSearchLink = "&listtype=qwiser";
        $sSearchLink .= "&searchparam=".$sSearchStr;
        $sSearchLink .= "&sQWSearchHandle=".$sSearchHandle;
        $sSearchLink .= "&pgNr=".$qsr->SearchInformation->CurrentPage;
        $this->_aViewData['searchlink'] = $sSearchLink;
        $this->_aViewData['sListType']  = "qwiser";
        
        $this->_aViewData['SearchHandle'] = $sSearchHandle;
        
        $sHiddenSid = $this->_aViewData['shop']->hiddensid;
        
        // additional parameters (first check and remove existing)
	    if (strstr($sHiddenSid,'qwiser_parameters')){
            $sHiddenSid = preg_replace('/<input.*name="qwiser_parameters.*\/>/', '', $sHiddenSid);
        }
        $sHiddenSid.= '<input type="hidden" name="qwiser_parameters[sQWSearchHandle]" value="'.$sSearchHandle.'" />';
        $sHiddenSid.= '<input type="hidden" name="qwiser_parameters[iQWPage]" value="'.$qsr->SearchInformation->CurrentPage.'" />';
        $sHiddenSid.= '<input type="hidden" name="qwiser_parameters[iQWAction]" value="1" />';
        $this->_aViewData['shop']->hiddensid=$sHiddenSid; 
        
        // logout link       
        $this->_aViewData['shop']->logoutlink.='&qwiser_parameters[iQWPage]='.$qsr->SearchInformation->CurrentPage.'&qwiser_parameters[iQWAction]=1&qwiser_parameters[sQWSearchHandle]='.$sSearchHandle; 
                
        // recomended message
        $sRecommendedMessage = $qsr->RecommendedMessage;
        $sRecommendedMessage = str_replace( array( "#%", "%#"), array( "<b>", "</b>"), $sRecommendedMessage );
        $this->_aViewData['RecommendedMessage'] = $sRecommendedMessage;
        
        if($qsr->SpellerInformation->SpellingErrorDetected)
        {
           $this->_aViewData['aAdditionalSuggestions'] = $qsr->SpellerInformation->AddtionalSuggestions;
        }
        
        // Results Message
        //print_r($qsr->SearchPath);
        $this->_aViewData['aSearchPath'] = $qsr->SearchPath;
        
        // Related Searches
        //print_r($qsr->RelatedSearches);
        
        // RenderROBox
        //print_r($qsr->Questions);
             
        if($qsr->Questions->Count >=1)
        {
            $oLeadQuestion = $qsr->Questions->Items[0];
            
            if(!is_array ($oLeadQuestion->ExtraAnswers->Items))
            {
            	$oLeadQuestion->ExtraAnswers->Items = array();
                $oLeadQuestion->ExtraAnswers->Count = 0;
            }
            
            // Transforming Answers if needed
            if( ($oLeadQuestion->Answers->Count + $oLeadQuestion->ExtraAnswers->Count) > $iQuiser_max_lead_answers )
            {
                $aAnswers = array();
            	$aAnswers = array_merge($oLeadQuestion->Answers->Items,$oLeadQuestion->ExtraAnswers->Items);
            	
            	$oLeadQuestion->Answers->Items = array_slice($aAnswers, 0, $iQuiser_max_lead_answers);
            	$oLeadQuestion->Answers->Count = count($oLeadQuestion->Answers->Items);
            	
            	$oLeadQuestion->ExtraAnswers->Items = array_slice($aAnswers, $iQuiser_max_lead_answers);
            	$oLeadQuestion->ExtraAnswers->Count = count($oLeadQuestion->ExtraAnswers->Items);
            	
            	if($oLeadQuestion->ExtraAnswers->Count > 0 )
            		$oLeadQuestion->HasExtraAnswers = 1;
            	else
            		$oLeadQuestion->HasExtraAnswers = 0;  
            	
            }
            
            if( !$blQuiser_show_full_lead_answers && $oLeadQuestion->HasExtraAnswers)
            {
            	$oLeadQuestion->HasExtraAnswers = 0;
            	$oLeadQuestion->ExtraAnswers->Items = array();
            	$oLeadQuestion->ExtraAnswers->Count = 0;
            }
            
            $this->_aViewData['LeadQuestion'] = $oLeadQuestion;
            
            unset($qsr->Questions->Items[0]);
            $qsr->Questions->Count --;
            
            // remove questions if needed
            if( ($iQuiser_max_non_lead_questions >=0) && ($qsr->Questions->Count > $iQuiser_max_non_lead_questions) )
            {
            	$qsr->Questions->Items = array_slice($qsr->Questions->Items, 0, $iQuiser_max_non_lead_questions);
            	$qsr->Questions->Count = count($qsr->Questions->Items);
            }
            
            foreach( $qsr->Questions->Items as $index => $Question)
            {
            	if(!is_array ($Question->ExtraAnswers->Items))
                {
                    $Question->ExtraAnswers->Items = array();
                    $Question->ExtraAnswers->Count = 0;
                }
                
                // Transforming Answers if needed
            	if( ($Question->Answers->Count + $Question->ExtraAnswers->Count) > $iQuiser_max_non_lead_answers )
            	{
            		$aAnswers = array();
            		$aAnswers = array_merge($Question->Answers->Items,$Question->ExtraAnswers->Items);
            	
            		$Question->Answers->Items = array_slice($aAnswers, 0, $iQuiser_max_non_lead_answers);
            		$Question->Answers->Count = count($Question->Answers->Items);
            	
            		$Question->ExtraAnswers->Items = array_slice($aAnswers, $iQuiser_max_non_lead_answers);
            		$Question->ExtraAnswers->Count = count($Question->ExtraAnswers->Items);
            	
            		if($Question->ExtraAnswers->Count > 0 )
            			$Question->HasExtraAnswers = 1;
            		else
            			$oLeadQuestion->HasExtraAnswers = 0;
            		 
            		$qsr->Questions->Items[$index] = $Question;
            	}
            }
            
            
            $this->_aViewData['aMoreQuestions'] = $qsr->Questions;
        }
        else
        {
        	$this->_aViewData['LeadQuestion'] = false;
            $this->_aViewData['aMoreQuestions'] = $qsr->Questions;
        }
        
        
        
        /* load articles */
        $aID = array();
        $aQwiserID = array();
        if( $qsr->Products->Count > 0)
        {
        	$i = 1;
            foreach( $qsr->Products->Items as $oProduct)
        	{
        		$aID[] = "'".$oProduct->Sku."'";
                $aQwiserID[$i] = $oProduct->Sku;
                $i++;
        	}
        }
        
        // Cache ID's to session
        $aQwiserCachedIDs = array( "articlecount"=> $qsr->RelevantProductsCount, "pagecount"=> $qsr->SearchInformation->NumberOfPages,"pagesize"=> $qsr->SearchInformation->PageSize, $qsr->SearchInformation->CurrentPage => array("SearchHandle" => $qsr->SearchHandle , "aID" =>$aQwiserID ));
        $this->getSession()-> setVar("aQwiserCachedIDs",$aQwiserCachedIDs);
         
        $sID = implode(",",$aID);
        
        $sArticleViewName = getViewName('oxarticles');
        $oArticle = oxNew("oxarticle");
        
        $sSelect =  "select $sArticleViewName.* from $sArticleViewName where ";
		$sSelect .= $oArticle->getSqlActiveSnippet()."  and $sArticleViewName.oxissearch = 1 and $sArticleViewName.oxparentid = '' ";
		$sSelect .= " and oxid in ( $sID )";
		
		
		
	    $sListOrderBy = $this->getConfig()->getParameter( "listorderby");
        $sListOrder = $this->getConfig()->getParameter( "listorder");
        
        if( $sListOrderBy && isValidFieldName($sListOrderBy) && $sListOrder && isValidAlpha($sListOrder) )
        {
            $sSelect .= " order by ".$sListOrderBy." ".$sListOrder;
        }
		
		//echo $sSelect."<br>";
		
		
		$oArtList = oxNew( "oxarticlelist");
		$oArtList->selectString( $sSelect);
		
		//dumpVar($oArtList);
		
        
		if( count( $oArtList->aList))
        {
        	$this->_aViewData['articlelist']  = $oArtList->aList;
        }
        
        //$this->_aViewData['aProducts'] = $qsr->Products;
        //dumpVar($qsr);
        
        // generate the page navigation
        $pageNavigation = new stdClass();
        $pageNavigation->iArtCnt    = $qsr->RelevantProductsCount;//$qsr->SearchInformation->PageSize;
        $pageNavigation->NrOfPages  = $qsr->SearchInformation->NumberOfPages;
        $pageNavigation->actPage    = $qsr->SearchInformation->CurrentPage+1;
        
        if( $pageNavigation->actPage > 1)   
            $pageNavigation->previousPage = $this->getConfig()->getShopHomeURL()."cl=".$this->sThisAction."&iQWPage=".($pageNavigation->actPage-2)."&searchparam=$sSearchStr&sQWSearchHandle={$sSearchHandle}&iQWAction=1";
        else
            $pageNavigation->previousPage = null;

            if( $pageNavigation->actPage < $pageNavigation->NrOfPages)
            $pageNavigation->nextPage = $this->getConfig()->getShopHomeURL()."cl=".$this->sThisAction."&iQWPage=".($pageNavigation->actPage)."&searchparam=$sSearchStr&sQWSearchHandle={$sSearchHandle}&iQWAction=1";
        else
            $pageNavigation->nextPage = null;
        
        $sClass = $this->sThisAction;
        if( $pageNavigation->NrOfPages > 1)
        {
            for ($i=1; $i < $pageNavigation->NrOfPages + 1; $i++)
            {   
                $page = new stdClass();
                $page->url = $this->getConfig()->getShopHomeURL()."cl=".$this->sThisAction."&iQWPage=".($i-1)."&searchparam=$sSearchStr&sQWSearchHandle={$sSearchHandle}&iQWAction=1";
                $page->selected = 0;
                if( $i == $pageNavigation->actPage)
                    $page->selected = 1;
                $pageNavigation->changePage[$i] = $page;
            }
            // first/last one
            $pageNavigation->firstpage = $this->getConfig()->getShopHomeURL()."cl=".$this->sThisAction."&iQWPage=0&searchparam=$sSearchStr&sQWSearchHandle={$sSearchHandle}&iQWAction=1";
            $iLast =  $pageNavigation->NrOfPages - 1;
            $pageNavigation->lastpage = $this->getConfig()->getShopHomeURL()."cl=".$this->sThisAction."&iQWPage=".$iLast."&searchparam=$sSearchStr&sQWSearchHandle={$sSearchHandle}&iQWAction=1";
        }
        
        // additional parameters for locator
        if ( !isset($this->_aViewData['additionalparams'])) $this->_aViewData['additionalparams'] = "";
        {
            $this->_aViewData['additionalparams'] .= "&cl={$this->sThisAction}&searchparam={$sSearchStr}&sQWSearchHandle={$sSearchHandle}&iQWAction=1";
        }        
        
        $this->_aViewData['pageNavigation'] = $pageNavigation;
        //DumpVar($pageNavigation);
        
        $this->_aViewData['showsorting'] = $blShowSorting;
        $this->_aViewData['allsortcolumns'] = $aSortColumns;
        
        
        if ( $qsr->SearchInformation->SortingOptions->FieldName )
        {
            $this->_aViewData['listorderby'] = strtr($qsr->SearchInformation->SortingOptions->FieldName,array_flip($aSortingFields));
            $this->_aViewData['listorder'] =  strtr($qsr->SearchInformation->SortingOptions->Ascending,array_flip($aSortingOrder));
        }
        /*

        if(count( $oArtList->aList)==1 && $pageNavigation->NrOfPages == 1 && !$oLeadQuestion)
        {
            // if only one article found, redirect to details
            $oOneArticle = array_pop($oArtList->aList);
            if ( $oOneArticle->isVisible() ){
                $sDetailsLink = $this->getConfig()->getShopHomeURL()."cl=details";
                $sDetailsLink.= "&anid=".$oOneArticle->sOXID;
                $sDetailsLink.= "&listtype=qwiser";
                $sDetailsLink.= "&searchparam=".$sSearchStr;
                $sDetailsLink.= "&sQWSearchHandle=".$sSearchHandle;
                
                if($qsr->SearchPath->Count > 1){
                    $sDetailsLink.= "&iQWBackIndex=".($qsr->SearchPath->Count-1);
                }
                
                $this->getSession()->setVar("aQwiserCachedIDs",false);
                header("location:".$sDetailsLink);
                exit ();
            }
        }
        */

        return $this->sThisTemplate;
	}
    
    public function setRequestParameter($sParam, $sValue)
    {
    	@$_GET[$sParam] = $sValue; 
        @$_POST[$sParam] = $sValue; 
    }
    
    public function ReplaceExtendedChars( $sValue, $blReverse = false)
    {   // we need to replace this for compatibility with XHTML
        // as this function causes a lot of trouble with editor
        // we switch it off, even if this means that fields do not validate through xhtml
        // return $sValue;

        // we need to replace this for compatibility with XHTML
        //$aReplace = array( "&" => "&amp;", "ä" => "&auml;", "ö" => "&ouml;", "ü" => "&uuml;", "Ü" => "&Uuml;", "Ä" => "&Auml;", "Ö" => "&Ouml;", "ß" => "&szlig;", "©" => "&copy", "€" => "&euro;");
        //(experiment if we don't)
        $aReplace = array( "©" => "&copy", "€" => "&euro;", "\"" => "&quot;", "'" => "&#039;");

        /*
        if( !$blReverse)
        {   // check if we do have already htmlentities inside
            foreach( $aReplace as $key => $sReplace)
                if( strpos( $sValue, $sReplace) !== false)
                    return $sValue;

            // replace now
            foreach( $aReplace as $key => $sReplace)
                $sValue = str_replace( $key, $sReplace, $sValue);
        }
        */

        // #899C reverse html entities and references transformation is used in invoicepdf module
        // so this part must be enabled. Now it works with html references like &#123;
        if($blReverse)
        {   // replace now
            $aTransTbl = get_html_translation_table (HTML_ENTITIES);
            $aTransTbl = array_flip ($aTransTbl) + array_flip ($aReplace);
            $sValue = strtr ($sValue, $aTransTbl);
            $sValue = preg_replace('/\&\#([0-9]+)\;/me', "chr('\\1')",$sValue);
        }

        return $sValue;
    }
    
    public function getPageNavigation()
    {
        if ( $this->_oPageNavigation === null ) {
            $this->_oPageNavigation = false;
            $this->_oPageNavigation = $this->generatePageNavigation();
        }
        return $this->_oPageNavigation;
    }
    
    public function generatePageNavigationUrl( )
    {
        if ( ( oxUtils::getInstance()->seoIsActive() && ( $oCategory = $this->getActCategory() ) ) ) {
            return $oCategory->getLink();
        } else {
            return parent::generatePageNavigationUrl( );
        }
    }
}
?>