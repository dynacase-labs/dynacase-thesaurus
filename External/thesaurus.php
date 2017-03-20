<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package THESAURUS
*/

function getThConcept($dbaccess, $thesid, $name = '')
{
    $th = new_doc($dbaccess, $thesid);
    if (!$th->isAlive()) {
        return sprintf(___("Thesaurus \"%s\" not exists", "thesaurus"), $thesid);
    }

    $s = new SearchDoc($dbaccess, "THCONCEPT");
    $s->addFilter("thc_thesaurus='%d'", $th->initid);
    if ($name) $s->addFilter("title ~* '%s' or thc_uribroader ~* '%s'", $name, $name);
    $s->setObjectReturn(true);
    $s->setOrder("thc_uribroader nulls first, title");
    //$s->setSlice($limit);
    $t = $s->search()->getDocumentList();
    $tr = array();
    foreach ($t as $k => $v) {
        if ($v) {
            if ($v->getRawValue("thc_uribroader")) {
                $display = sprintf("<b><i>%s</i></b>/ %s", htmlspecialchars($v->getRawValue("thc_uribroader")) , htmlspecialchars($v->getTitle()));
            } else {
                $display = sprintf("<b>%s</b>", htmlspecialchars($v->getTitle()));
            }
            /**
             * @var \Doc  $v
             */
            $tr[] = array(
                $display,
                $v->initid,
                $v->getTitle()
            );
        }
    }

    

    return $tr;
}
