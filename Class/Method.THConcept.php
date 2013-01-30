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
class _THCONCEPT extends Doc
{
    /*
     * @end-method-ignore
    */
    function preRefresh()
    {
        // $err= $this->recomputeNarrower();
        $this->retrieveLangLabel();
    }
    function postStore()
    {
        $this->recomputeRelations();
        
        $this->setValue("thc_level", $this->getLevel());
        $this->setValue("thc_title", $this->getLangTitle());
        $this->modify();
    }
    
    function getCustomTitle()
    {
        return $this->getLangTitle();
    }
    /**
     * recompute narrower of father
     */
    function recomputeNarrower()
    {
        include_once ("FDL/Class.SearchDoc.php");
        $s = new SearchDoc($this->dbaccess, "THCONCEPT");
        $s->addFilter("thc_thesaurus='" . intval($this->getRawValue("thc_thesaurus")) . "'");
        $s->addFilter("thc_broader='" . $this->id . "'"); // $s->addFilter("thc_broader ~ '\\\\y$id\\\\y'); // if many
        $t = $s->search();
        $tid = array();
        foreach ($t as $k => $v) {
            $tid[] = $v["initid"];
        }
        $this->setValue("thc_narrower", $tid);
        $err = $this->modify();
        return $err;
    }
    
    function recomputeRelations()
    {
        $oldtg = $this->getOldRawValue("thc_broader");
        $tg = $this->getRawValue("thc_broader");
        if ($oldtg != $tg) {
            /**
             * @var _THCONCEPT $d
             */
            $d = new_doc($this->dbaccess, $oldtg);
            if ($d->isAlive()) $d->recomputeNarrower(); // update old
            $d = new_doc($this->dbaccess, $tg);
            if ($d->isAlive()) $d->recomputeNarrower(); // update new
            
        }
    }
    
    function refreshFromURI()
    {
        include_once ("THESAURUS/Lib.Thesaurus.php");
        $broaduri = $this->getMultipleRawValues("thc_uribroader");
        $broad = $this->getMultipleRawValues("thc_broader");
        
        foreach ($broaduri as $k => $v) {
            if (!$broad[$k]) {
                $broad[$k] = getConceptIdFromURI($this->dbaccess, $v);
            }
        }
        $this->setValue("thc_broader", $broad);
    }
    
    function retrieveLangLabel()
    {
        include_once ("THESAURUS/Lib.Thesaurus.php");
        $langs = getLangConcepts($this->dbaccess, $this->initid);
        $tlang = $tlangid = $tlanglabel = array();
        foreach ($langs as $k => $v) {
            $tlang[] = $v["thcl_lang"];
            $tlangid[] = $v["initid"];
            $tlanglabel[] = $v["thc_preflabel"];
        }
        $this->setValue("thc_lang", $tlang);
        $this->setValue("thc_idlang", $tlangid);
        $this->setValue("thc_langlabel", $tlanglabel);
    }
    /**
     * @return bool|_THCONCEPT
     */
    function getParentConcept()
    {
        $gen = $this->getRawValue("thc_broader");
        if ($gen) {
            $d = new_doc($this->dbaccess, $gen);
            if ($d->isAlive()) return $d;
        }
        return false;
    }
    
    function getLevel()
    {
        $level = 0;
        $father = $this->getParentConcept();
        while ($father) {
            $level++;
            $father = $father->getParentConcept();
            if ($level > 100) break;
        }
        return $level;
    }
    /**
     * return recursive narrower
     */
    function getRNarrowers()
    {
        $pid = array();
        $nrs = $this->getMultipleRawValues("thc_narrower");
        $nrsdoc = getDocsFromIds($this->dbaccess, $nrs, 1);
        
        foreach ($nrsdoc as $k => $nr) {
            $nrs = array_merge($nrs, $this->_getRNarrowers(Doc::rawValueToArray($nr['thc_narrower'])));
        }
        return $nrs;
    }
    /**
     * return recursive narrower
     */
    private function _getRNarrowers($nrs)
    {
        $_nrs = $nrs;
        
        foreach ($nrs as $k => $nr) {
            $t = getTDoc($this->dbaccess, $nr);
            $_nrs1 = Doc::rawValueToArray($t['thc_narrower']);
            $_nrs2 = $this->_getRNarrowers($_nrs1);
            $_nrs = array_merge($_nrs, $_nrs1, $_nrs2);
        }
        return $_nrs;
    }
    /**
     * return localized label
     * @param string $lang languqge : simple notation like en,fr,ru,es
     */
    function getLabelLang($lang = false)
    {
        if ($lang === false) $lang = strtolower(strtok(getParam("CORE_LANG") , '_'));
        $tlang = $this->getMultipleRawValues("thc_lang");
        $tll = $this->getMultipleRawValues("thc_langlabel");
        
        $kgood = - 1;
        
        foreach ($tlang as $k => $v) {
            if ($tlang[$k] == $lang) {
                $kgood = $k;
                break;
            }
        }
        
        return (isset($tll[$kgood])) ? $tll[$kgood] : $tll[0];
    }
    /**
     * return localized title
     */
    function getLangTitle($lang = false)
    {
        return $this->getRawValue("thc_label") . ' ' . $this->getLabelLang($lang);
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
