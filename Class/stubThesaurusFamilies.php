<?php
namespace Dcp\Family {
	/** Thésaurus  */
	class Thesaurus extends \Dcp\Thesaurus\Thesaurus { const familyName="THESAURUS";}
	/** Descripteur  */
	class Thconcept extends \Dcp\Thesaurus\Thconcept { const familyName="THCONCEPT";}
	/** Descripteur localisé  */
	class Thlangconcept extends \Dcp\Thesaurus\Thlangconcept { const familyName="THLANGCONCEPT";}
}
namespace Dcp\AttributeIdentifiers {
	/** Thésaurus  */
	class Thesaurus {
		/** [frame] Identification */
		const thes_fr_info='thes_fr_info';
		/** [text] Référence */
		const thes_uri='thes_uri';
		/** [text] Titre */
		const thes_title='thes_title';
		/** [longtext] Description */
		const thes_desc='thes_desc';
		/** [enum] Langues supportées */
		const thes_lang='thes_lang';
		/** [menu] Recalcul des relations */
		const thes_refresh='thes_refresh';
		/** [menu] Arbres des concepts */
		const thes_thtree='thes_thtree';
		/** [menu] Importation SKOS */
		const thes_import='thes_import';
		/** [menu] Exportation SKOS */
		const thes_export='thes_export';
	}
	/** Descripteur  */
	class Thconcept {
		/** [frame] Identification */
		const thc_fr_ident='thc_fr_ident';
		/** [text] Identifiant */
		const thc_uri='thc_uri';
		/** [docid("THESAURUS")] Thésaurus */
		const thc_thesaurus='thc_thesaurus';
		/** [text] Nom du thésaurus */
		const thc_thestitle='thc_thestitle';
		/** [integer] Niveau */
		const thc_level='thc_level';
		/** [frame] Description */
		const thc_fr_desc='thc_fr_desc';
		/** [text] Titre */
		const thc_title='thc_title';
		/** [text] Libellé */
		const thc_preflabel='thc_preflabel';
		/** [text] LIbellé court */
		const thc_label='thc_label';
		/** [array] Libellés alternatifs */
		const thc_t_label='thc_t_label';
		/** [text] Employé pour */
		const thc_altlabel='thc_altlabel';
		/** [longtext] Définition */
		const thc_definition='thc_definition';
		/** [longtext] Note éditoriale */
		const thc_editorialnote='thc_editorialnote';
		/** [longtext] Exemple */
		const thc_example='thc_example';
		/** [longtext] Note d'historique */
		const thc_historynote='thc_historynote';
		/** [image] Symbole */
		const thc_symbol='thc_symbol';
		/** [longtext] Note d'application */
		const thc_scopenote='thc_scopenote';
		/** [longtext] Note générale */
		const thc_note='thc_note';
		/** [frame] Traductions */
		const thc_fr_lang='thc_fr_lang';
		/** [array] Internationalisation */
		const thc_t_lang='thc_t_lang';
		/** [docid("THLANGCONCEPT")] Localisation (ID) */
		const thc_idlang='thc_idlang';
		/** [enum] Localisation */
		const thc_lang='thc_lang';
		/** [text] Libellé */
		const thc_langlabel='thc_langlabel';
		/** [frame] Relations */
		const thc_fr_relation='thc_fr_relation';
		/** [array] Générique */
		const thc_t_broader='thc_t_broader';
		/** [text] URI */
		const thc_uribroader='thc_uribroader';
		/** [thesaurus("THC_THESAURUS")] Terme générique */
		const thc_broader='thc_broader';
		/** [thesaurus("THC_THESAURUS")] Termes spécifiques */
		const thc_narrower='thc_narrower';
		/** [array] Associés */
		const thc_t_related='thc_t_related';
		/** [text] URI */
		const thc_urirelated='thc_urirelated';
		/** [thesaurus("THC_THESAURUS")] Termes associés */
		const thc_related='thc_related';
		/** [menu] Ajouter une traduction */
		const thc_addlang='thc_addlang';
	}
	/** Descripteur localisé  */
	class Thlangconcept {
		/** [frame] Identification */
		const thcl_fr_ident='thcl_fr_ident';
		/** [docid("THESAURUS")] Thésaurus */
		const thcl_thesaurus='thcl_thesaurus';
		/** [docid("THCONCEPT")] Descripteur */
		const thcl_thconcept='thcl_thconcept';
		/** [enum] Langue */
		const thcl_lang='thcl_lang';
	}
}
