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
// ######## Konfiguration ######################
$ftp_server		= "[FTP Server]";
$ftp_user_name	= '[FTP User]';
$ftp_user_pass	= '[FTP Passwort]';
// #############################################


$aDestFiles[]	= "oxarticles.csv";
$aDestFiles[]	= "oxcategories.csv";
$aDestFiles[]	= "oxobject2category.csv";
$aDestFiles[]	= "oxvendor.csv";
$aDestFiles[]	= "oxattribute.csv";
$aDestFiles[]	= "oxobject2attribute.csv";


foreach($aDestFiles as $file) {

	// Verbindungsaufbau
	$conn_id = ftp_connect($ftp_server); 
	
	// Login mit Username und Passwort
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
	
	// Verbindung überprüfen
	if ((!$conn_id) || (!$login_result)) { 
	       echo "FTP Verbindung ist fehlgeschlagen!\n";
	       echo "Verbindungasufbau zu $ftp_server mit Username $ftp_user_name versucht.\n"; 
	       exit; 
	   } else {
	       echo "Verbunden zu $ftp_server mit Username $ftp_user_name\n";
	   }
	
	// Datei hochladen
	$upload = ftp_put($conn_id, $file, $file, FTP_BINARY); 
	
	// Upload überprüfen
	if (!$upload) { 
	       echo "FTP-Upload ist fehlgeschlagen!\n\n";
	   } else {
	       echo "Datei $file auf Server $ftp_server als $destination_file hochgeladen\n\n";
	   }
	
	// FTP Verbidung schließen
	ftp_close($conn_id); 
}
?>