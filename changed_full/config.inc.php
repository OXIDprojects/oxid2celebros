<?php

// Von hier ab kopieren und an das Ende der shopeigenen config.inc.php anfügen
    // --------------------------
	// CELEBROS QWISER v4
	// --------------------------

    // QWISER specific

    // Question config
    $this->iQuiser_max_lead_answers = 3;
    $this->blQuiser_show_full_lead_answers = true;
    $this->iQuiser_max_non_lead_questions = -1;
    $this->iQuiser_max_non_lead_answers = 5;

    // Sorting compatability qwiser<=>oxid
    // needs to be modified if more sorting fiels are used
    $this->aQuiser_SortingFields = array("oxtitle"=>"title", "oxprice"=>"price");
    $this->aQuiser_SortingIsNumeric = array("oxtitle"=>"False", "oxprice"=>"True");
    $this->aQuiser_SortingOrder = array("asc"=>"true", "desc"=>"false");

    //Default values for advanced search
    $this->sQuiser_DefaultSearchProfile = "QwiserDefaultSearchProfile";
    $this->sQuiser_DefaultAnswerId = "";
    $this->sQuiser_DefaultEffectOnSearchPath  = "2";
    $this->sQuiser_DefaultPriceColum = "";
    $this->iQuiser_DefaultPageSize  = "10";
    $this->sQuiser_DefaultSortingfield = "";
    $this->bQuiser_DefaultNumericsort = "false";
    $this->bQuiser_DefaultAscending = "false";
    
// ############ Konfiguration #######################################################
    $this->celebros_sitekey = "[Hier den sitekey eintragen]";		// sitekey
    $this->celebros_serviceurl = "[Hier die Celebros Service-URL samt Port eintragen]";	// service-url
// ##################################################################################