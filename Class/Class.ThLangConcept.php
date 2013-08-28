<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/
namespace Dcp\Thesaurus;
use \Dcp\AttributeIdentifiers as Attributes;
use \Dcp\AttributeIdentifiers\Thlangconcept as MyAttributes;
use \Dcp\Family as Family;
class Thlangconcept extends Family\Document
{

    function postStore()
    {
        $this->refreshConcept();
    }
    function postDelete()
    {
        $this->refreshConcept();
    }
    /**
     * refresh parent tu recompute translation array
     */
    function refreshConcept()
    {
        $thc = $this->getRawValue(MyAttributes::thcl_thconcept);
        if ($thc) {
            $th = new_doc($this->dbaccess, $thc);
            if ($th->isAlive()) {
                $th->refresh();
            }
        }
    }

}