<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_css.php,v 1.1 2004/11/26 18:52:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_css(&$action) {
   header("Cache-Control: private, max-age=3600");
   header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600)); 
   header("Pragma: ");
   header("Content-type: text/css");
}
?>
