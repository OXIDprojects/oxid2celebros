#! /usr/local/php5/bin/php -q

<?
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
// ############### Konfiguration #####################################
$dbHost         = "[Datenbankserver]";		// database host name
$dbName         = "[Datenbankname]";		// database name
$dbUser         = "[Datenbank-User]";		// database user name
$dbPwd          = "[Datenbank-Passwort]";	// database password  
// ####################################################################
	
$conn = @mysql_connect($dbHost, $dbUser, $dbPwd);
if($conn) {
	$select = mysql_select_db($dbName, $conn);
} else {
	die("Datenbank konnte nicht connected werden.");
}


function do_export ($table, $header) {
	global $conn;
	$PATH=".";
   
	$FILE=$PATH."/$table".".csv";
	
	$sql = "show columns from $table";
	$rs = mysql_query($sql, $conn);
	while($row = mysql_fetch_assoc($rs)) {
		$aFields[] = $row['Field'];
	}
	
	$aHeader = explode("|", $header);
	
	foreach($aHeader as $field) {
		
		if(in_array($field, $aFields)) {
			$aHeaderFields[] = $field;
			$aNewFields[] = "$table.$field";
		}
			
		if($table == "oxarticles" && $field == "OXLONGDESC") {
			$aNewFields[] = "oxartextends.oxlongdesc";
			$aHeaderFields[] = $field;
		}
	}
	
	$header = implode("|", $aHeaderFields);
	
	$outfile = $header."\r\n";
	
	// $aMCMCats = getMCMCats();
	
	// modifiy some values from oxarticles
	foreach ($aNewFields as $key=>$val) {
		if($val == "OXWIDTH" || $val == "OXHEIGHT") {
			$aNewFields[$key] = "concat($val, 'cm')";
		} elseif($val == "OXWEIGHT") {
			$aNewFields[$key] = "concat($val, 'kg')";
		}
		
		
	}
	
	$fields = implode(", ", $aNewFields);
	
	
	$sql =  "select $fields from $table ";
	
	if($table == "oxarticles") {
		$sql .= ", oxartextends ";
	}
	$sql .= " where 1 ";
	
	if($table == "oxarticles") {
		$sql .= "and oxarticles.oxid = oxartextends.oxid and oxarticles.oxissearch = '1' and oxarticles.oxactive = '1' ";
	}
	
	//if($table == "oxcategories")
	//	die($sql);
	
	$rs = mysql_query($sql, $conn);
	while($row = mysql_fetch_assoc($rs)) {
		$aNewRow = array();
		
		foreach($row as $key=>$val) {
			$aNewRow[$key] = str_replace("|", "", $val);
		}
		
		
		
		
		$line = implode("|", $aNewRow);
		$line = str_replace("</<>", "", $line);
		$line = str_replace("<br<>", "<br>", $line);
		$line = str_replace("\r\n", " ", $line);
		$line = strip_tags($line);
		$line .= "\r\n";
		$outfile .= $line;
		
		if(count($aNewFields) != count($aNewRow)) {
			$error_file .= $line."\r\n";
		}
	}
	
	$fp = fopen($FILE, "w");
	fwrite($fp, $outfile);
	fclose($fp);
	
	$fp = fopen("error_file.csv", "w");
	fwrite($fp, $error_file);
	fclose($fp);
	
}



$rs = do_export("oxarticles", "OXID|OXSHOPID|OXPARENTID|OXACTIVE|OXACTIVEFROM|OXACTIVETO|OXARTNUM|OXTITLE|OXSHORTDESC|OXLONGDESC|OXPRICE|OXBLFIXEDPRICE|OXPRICEA|OXPRICEB|OXPRICEC|OXPRICED|OXPRICEE|OXPRICEF|OXPRICEG|OXPRICEH|OXBPRICE|OXTPRICE|OXUNITNAME|OXUNITQUANTITY|OXEXTURL|OXURLDESC|OXURLIMG|OXVAT|OXTHUMB|OXICON|OXPIC1|OXPIC2|OXPIC3|OXPIC4|OXPIC5|OXPIC6|OXPIC7|OXPIC8|OXPIC9|OXPIC10|OXPIC11|OXPIC12|OXZOOM1|OXZOOM2|OXZOOM3|OXZOOM4|OXWEIGHT|OXSTOCK|OXSTOCKFLAG|OXSTOCKTEXT|OXNOSTOCKTEXT|OXDELIVERY|OXINSERT|OXTIMESTAMP|OXLENGTH|OXWIDTH|OXHEIGHT|OXAKTION|OXFILE|OXFILE2|OXFILE3|OXSEARCHKEYS|OXTEMPLATE|OXQUESTIONEMAIL|OXISSEARCH|OXVARNAME|OXVARSELECT|OXVARNAME_1|OXVARSELECT_1|OXVARNAME_2|OXVARSELECT_2|OXVARNAME_3|OXVARSELECT_3|OXTITLE_1|OXSHORTDESC_1|OXLONGDESC_1|OXURLDESC_1|OXSEARCHKEYS_1|OXTITLE_2|OXSHORTDESC_2|OXLONGDESC_2|OXURLDESC_2|OXSEARCHKEYS_2|OXTITLE_3|OXSHORTDESC_3|OXLONGDESC_3|OXURLDESC_3|OXSEARCHKEYS_3|OXBUNDLEID|OXFOLDER|OXSUBCLASS|OXSTOCKTEXT_1|OXSTOCKTEXT_2|OXSTOCKTEXT_3|OXNOSTOCKTEXT_1|OXNOSTOCKTEXT_2|OXNOSTOCKTEXT_3|OXSORT|OXSOLDAMOUNT|OXNONMATERIAL|OXFREESHIPPING|OXREMINDACTIV|OXREMINDAMOUNT|OXAMITEMID|OXAMTASKID|OXVENDORID|OXVPE|OXEAN|OXDISTEAN");
$rs = do_export("oxcategories", "OXID|OXTYPE|OXPARENTID|OXLEFT|OXRIGHT|OXROOTID|OXORDER|OXACTIVE|OXHIDDEN|OXSHOPID|OXTITLE|OXDESC|OXLONGDESC|OXTHUMB|OXEXTLINK|OXTEMPLATE|OXDEFSORT|OXDEFSORTMODE|OXPRICEFROM|OXPRICETO|OXACTIVE_1|OXTITLE_1|OXDESC_1|OXLONGDESC_1|OXACTIVE_2|OXTITLE_2|OXDESC_2|OXLONGDESC_2|OXACTIVE_3|OXTITLE_3|OXDESC_3|OXLONGDESC_3|OXICON|OXNROFARTICLES|OXFILE1|OXFILE2|OXFILE3");
$rs = do_export("oxobject2category", "OXID|OXOBJECTID|OXCATNID|OXPOS|OXTIME");
$rs = do_export("oxvendor", "OXID|OXSHOPID|OXACTIVE|OXICON|OXTITLE|OXSHORTDESC|OXTITLE_1|OXSHORTDESC_1|OXTITLE_2|OXSHORTDESC_2|OXTITLE_3|OXSHORTDESC_3|OXNROFARTICLES");
$rs = do_export("oxattribute", "OXID|OXSHOPID|OXSHOPINCL|OXSHOPEXCL|OXTITLE|OXTITLE_1|OXTITLE_2|OXTITLE_3|OXPOS");
$rs = do_export("oxobject2attribute", "OXID|OXOBJECTID|OXATTRID|OXVALUE|OXPOS|OXVALUE_1|OXVALUE_2|OXVALUE_3");
?>