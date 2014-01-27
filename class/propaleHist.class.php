<?
	class TPropaleHist extends TObjetStd {
		function __construct() {
			parent::set_table(MAIN_DB_PREFIX.'propale_history');
			parent::add_champs('serialized_parent_propale','type=text;index');
			parent::add_champs('fk_propale','type=entier;index');
			parent::add_champs('date_version','type=date;');
			parent::start();
			parent::_init_vars();
		}
	}