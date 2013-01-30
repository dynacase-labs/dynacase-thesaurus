<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/
/*
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
*/
class _THLANGCONCEPT extends Doc
{
    /*
     * @end-method-ignore
    */
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
        $thc = $this->getRawValue("thcl_thconcept");
        if ($thc) {
            $th = new_doc($this->dbaccess, $thc);
            if ($th->isAlive()) {
                $th->refresh();
            }
        }
    }
    /*
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
    */
}
/*
 * @end-method-ignore
*/
?>
