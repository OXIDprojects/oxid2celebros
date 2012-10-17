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

// Celebros Qwiser for OXID eShop 4

class azQwiserAPI extends oxSuperCfg
{

	// Qwiser v4 webservice data
	var $sWebServiceUrl;
	var $sSiteKey;

	var $sSearchHandle;
	var $oSearchResponce;

	// Qwiser state
	var $sLastOperationErrorMessage;
	var $blLastOperationSucceeded;

	function azQwiserAPI()
	{
		global $myConfig;
        
        $this->sSiteKey = $this->getConfig()->getConfigParam('celebros_sitekey');
		$this->sWebServiceUrl = $this->getConfig()->getConfigParam('celebros_serviceurl');
		$this->blLastOperationSucceeded = 1;
        $this->LastOperationErrorMessage = '';
	}

    //Gets the results for the specified search term.
	function Search($sQuery)
	{
		$sQuery = rawurlencode(utf8_encode($sQuery));
		$sRequestUrl = "Query=" . $sQuery;
		return $this->GetResult($sRequestUrl,__FUNCTION__);
	}

    //Gets the results for the specified search term under the specified search profile and the answer which Id was specified. 
    function SearchAdvance($sQuery,$sSearchProfile,$iAnswerId,$sEffectOnSearchPath,$sPriceColumn,$iPageSize,$sSortingfield,$bNumericsort,$bAscending)
    {
        $sQuery = rawurlencode(utf8_encode($sQuery));
        $sSearchProfile = urlencode($sSearchProfile);
        $sSortingfield = urlencode($sSortingfield);
        $sPriceColumn = urlencode($sPriceColumn);
        $sRequestUrl = "Query=".$sQuery."&SearchProfile=".$sSearchProfile."&AnswerId=".$iAnswerId."&EffectOnSearchPath=".$sEffectOnSearchPath."&PriceColumn=".$sPriceColumn."&PageSize=".$iPageSize."&Sortingfield=".$sSortingfield."&Numericsort=".$bNumericsort."&Ascending=".$bAscending;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Activate serach Profile
    function ActivateProfile($sSearchHandle,$sSearchProfile)
    {
        $sSearchProfile = urlencode($sSearchProfile);
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&SearchProfile=".$sSearchProfile;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Answer Question
    function AnswerQuestion($sSearchHandle,$iAnswerId,$sEffectOnSearchPath)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&answerId=".$iAnswerId."&EffectOnSearchPath=".$sEffectOnSearchPath;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Change Number of Products in Page
    function ChangePageSize($sSearchHandle,$iPageSize)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&pageSize=".$iPageSize;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Change the search default price 
    function ChangePriceColumn($sSearchHandle,$sPriceColumn)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&PriceColumn=".$sPriceColumn;
        return $this->GetResult($sRequestUrl,__FUNCTION__); 
    }
    
    //Deactivate Search Profile
    function DeactivateProfile($sSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Moves to the first page of the results
    function FirstPage($sSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Forces the BQF to allow the specified question to appear first
    function ForceQuestionAsFirst($sSearchHandle,$iQuestionId)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&QuestionId=".$iQuestionId;
        return $this->GetResult($sRequestUrl,__FUNCTION__); 
    }
    
    //Get all the product fields
    function GetAllProductFields()
    {
        return $this->GetResult("",__FUNCTION__); 
    }

    //Return all the questions
    function GetAllQuestions()
    {
        return $this->GetResult("",__FUNCTION__); 
    }

    //Return all search profiles 
    function GetAllSearchProfiles()
    {
        return $this->GetResult("",__FUNCTION__);    
    }
    
    //Gets the results for the specified search handle
    function GetCustomResults($sSearchHandle,$bNewSearch,$sPreviousSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&NewSearch=".$bNewSearch."&PreviousSearchHandle=".$sPreviousSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__); 
    }
    
    //Gets Engine Status
    function GetEngineStatus()
    {
        return $this->GetResult("",__FUNCTION__);  
    }
    
    //Gets all the answers that a product exists in
    function GetProductAnswers($sSku)
    {
        $sSku = urlencode($sSku);
        $sRequestUrl = "Sku=".$sSku;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Gets the full path to the best answer for this product under the selected question for the “View All” feature (in the SPD). 
    function GetProductSearchPath($sSku)
    {
        $sSku = urlencode($sSku);
        $sRequestUrl = "Sku=".$sSku;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Returns the answers for a specific question
    function GetQuestionAnswers($iQuestionId)
    {
        $sRequestUrl = "QuestionId=".$iQuestionId;
        return $this->GetResult($sRequestUrl,__FUNCTION__);   
    }
    
    //return all the question ampped to the given search profile
    function GetSearchProfileQuestions($sSearchProfile)
    {
        $sSearchProfile = urlencode($sSearchProfile);
        $sRequestUrl = "SearchProfile=".$sSearchProfile;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Gets all the answers a collection of products exist in. 
    function GetSeveralProductsAnswers($sSkus)
    {
        $sRequestUrl = "Skus=".$sSkus;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Return the LastPage.
    function LastPage($sSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Moves to the specified page of the results
    function MoveToPage($sSearchHandle,$iPage)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&Page=".$iPage;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Moves to the previous page of the results 
    function PreviousPage($sSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Moves to the next page of the results 
    function NextPage($sSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Removes the specified answer from the list of answered answers in this session. 
    function RemoveAnswer($sSearchHandle,$iAnswerId)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&AnswerId=".$iAnswerId;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Removes the specified answers from the list of answered answers in this session. 
    function RemoveAnswerAt($sSearchHandle,$iAnswerIndex)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&AnswerIndex=".$iAnswerIndex;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }

    //Removes the specified answers from the list of answered answers in this session. 
    function RemoveAnswers($sSearchHandle,$sAnswerIds)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&AnswerIds=".$sAnswerIds;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Remove the all the answer from the search information form the given index
    function RemoveAnswersFrom($sSearchHandle,$iStartIndex)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&StartIndex=".$iStartIndex;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Marks a product as out of stock.
    function RemoveProductFromStock($sSku)
    {
        $sSku = urlencode($sSku);
        $sRequestUrl = "Sku=".$sSku;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Marks a product as in stock.
    function RestoreProductToStock($sSku)
    {
        $sSku = urlencode($sSku);
        $sRequestUrl = "Sku=".$sSku;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Changes the sorting of the results to display products by the value of the specified field, and whether to perform a numeric sort on that field, in the specified sorting direction. 
    function SortByField($sSearchHandle,$sFieldName,$bNumericSort,$bAscending)
    {
        $sFieldName = urlencode($sFieldName);
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&FieldName=".$sFieldName."&NumericSort=".$bNumericSort."&Ascending=".$bAscending;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Changes the sorting of the results to display products by their price in the specified sorting direction 
    function SortByPrice($sSearchHandle,$bAscending)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle."&Ascending=".$bAscending;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    
    //Changes the sorting of the results to display products by relevancy in descending order.
    function SortByRelevancy($sSearchHandle)
    {
        $sRequestUrl = "SearchHandle=".$sSearchHandle;
        return $this->GetResult($sRequestUrl,__FUNCTION__);
    }
    

	function GetResult($sRequestUrl,$sReturnValue)
	{
		$sRequest = $this->sWebServiceUrl . '/' . $sReturnValue.'?';
        
        if(!empty($sRequestUrl))
        {
            $sRequest .= $sRequestUrl.'&';
        }

        $sRequest .= 'Sitekey='.$this->sSiteKey;
        
        $oQwiserParser = &oxNew( "azqwiserparser", $sRequest); 
        
        $oQwiserParser->set_enconig_converter('UTF-8','ISO-8859-1',2);
		$this->oSearchResponce = $oQwiserParser->run();
		
        // "__FUNCTION__":
        // The function name. (Added in PHP 4.3.0) As of PHP 5 this constant returns the function name 
        // as it was declared (case-sensitive). In PHP 4 its value is always lowercased. 
        $sReturnValue = strtolower($sReturnValue);
        
        $oResult  = false;
        
        switch ($sReturnValue) 
        {
			//GetAllProductFields
            case "getallproductfields":
            {
            	$oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->ProductFields;
                break;
            }
            
            //GetProductAnswers,GetSeveralProductsAnswers
            case "getproductanswers":
            case "getseveralproductsanswers":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->ProductAnswers;
                break;
            }
            
            //GetAllQuestions
            case "getallquestions":
            {
            	$oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->Questions;
                break;
            }
             
            //GetProductSearchPath            
            case "getproductsearchpath":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->SearchPath;
                break;
            }
            
            //GetQuestionAnswers
            case "getquestionanswers":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->Answers;
                break;
            }
            
            //GetSearchProfileQuestions
            case "getsearchprofilequestions":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->Questions;
                break;
            }
            
            //GetEngineStatus
            case "getenginestatus":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->SearchEngineStatus;
                break;
            }
            
            //RemoveProductFromStock,RestoreProductToStock
            case "removeproductfromstock":
            case "restoreproducttostock":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper;
                break;
            }
            
            //GetAllSearchProfiles
            case "getallsearchprofiles":
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->QwiserSimpleStringCollection;
                break;
            }
            
            //Search,SearchAdvance,ActivateProfile,AnswerQuestion,ChangePageSize,ChangePriceColumn,
            //DeactivateProfile,FirstPage,LastPage,MoveToPage,PreviousPage,NextPage,GetCustomResults,
            //RemoveAnswer,RemoveAnswerAt,RemoveAnswers,RemoveAnswersFrom,SortByField,SortByPrice,SortByRelevancy
			default:
            {
                $oResult = $this->oSearchResponce->QwiserSearchFacadeWrapper->ReturnValue->QwiserSearchResults;
				break;
            }
		}
        $this->blLastOperationSucceeded = $oQwiserParser->blLastOperationSucceeded;
        $this->sLastOperationErrorMessage = $oQwiserParser->sLastOperationErrorMessage;
        
        return $oResult;
	}
    
    function SearchHandle_encode( $sSearchHandle )
    {
    	$sEncodedSearchHandle = base64_encode($sSearchHandle);
        $sEncodedSearchHandle = str_replace("=","-",$sEncodedSearchHandle);
        return $sEncodedSearchHandle; 
    }
    
    function SearchHandle_decode( $sEncodedSearchHandle )
    {
        $sEncodedSearchHandle = str_replace("-","=",$sEncodedSearchHandle);
        $sSearchHandle = base64_decode($sEncodedSearchHandle);
        return $sSearchHandle; 
    }
}
?>