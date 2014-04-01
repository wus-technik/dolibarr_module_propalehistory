<?php
	class TPropaleHist extends TObjetStd {
		
		function __construct() {
			parent::set_table(MAIN_DB_PREFIX.'propale_history');
			parent::add_champs('serialized_parent_propale','type=text;index');
			parent::add_champs('fk_propale','type=entier;index');
			parent::add_champs('date_version','type=date;');
			parent::start();
			parent::_init_vars();
		}
		
		function save($db) {
			parent::save($db);
		}
		
		function load(&$ATMdb,$idVersion){
			parent::load($ATMdb,$idVersion);
		}
		
		function delete(&$ATMdb) {
			parent::delete($ATMdb);
		}
				
	}	

	class PropalHist extends Propal {
		
		  function __construct($db, $socid="")
		    {
		        global $conf,$langs;
		
		        $this->db = $db;
		        $this->socid = $socid;
		        
		        $this->products = array();
		        $this->remise = 0;
		        $this->remise_percent = 0;
		        $this->remise_absolue = 0;
		
		        $this->duree_validite=$conf->global->PROPALE_VALIDITY_DURATION;
		
		        $langs->load("propal");
		        $this->labelstatut[0]=(! empty($conf->global->PROPAL_STATUS_DRAFT_LABEL) ? $conf->global->PROPAL_STATUS_DRAFT_LABEL : $langs->trans("PropalStatusDraft"));
		        $this->labelstatut[1]=(! empty($conf->global->PROPAL_STATUS_VALIDATED_LABEL) ? $conf->global->PROPAL_STATUS_VALIDATED_LABEL : $langs->trans("PropalStatusValidated"));
		        $this->labelstatut[2]=(! empty($conf->global->PROPAL_STATUS_SIGNED_LABEL) ? $conf->global->PROPAL_STATUS_SIGNED_LABEL : $langs->trans("PropalStatusSigned"));
		        $this->labelstatut[3]=(! empty($conf->global->PROPAL_STATUS_NOTSIGNED_LABEL) ? $conf->global->PROPAL_STATUS_NOTSIGNED_LABEL : $langs->trans("PropalStatusNotSigned"));
		        $this->labelstatut[4]=(! empty($conf->global->PROPAL_STATUS_BILLED_LABEL) ? $conf->global->PROPAL_STATUS_BILLED_LABEL : $langs->trans("PropalStatusBilled"));
		        $this->labelstatut_short[0]=(! empty($conf->global->PROPAL_STATUS_DRAFTSHORT_LABEL) ? $conf->global->PROPAL_STATUS_DRAFTSHORT_LABEL : $langs->trans("PropalStatusDraftShort"));
		        $this->labelstatut_short[1]=(! empty($conf->global->PROPAL_STATUS_VALIDATEDSHORT_LABEL) ? $conf->global->PROPAL_STATUS_VALIDATEDSHORT_LABEL : $langs->trans("Opened"));
		        $this->labelstatut_short[2]=(! empty($conf->global->PROPAL_STATUS_SIGNEDSHORT_LABEL) ? $conf->global->PROPAL_STATUS_SIGNEDSHORT_LABEL : $langs->trans("PropalStatusSignedShort"));
		        $this->labelstatut_short[3]=(! empty($conf->global->PROPAL_STATUS_NOTSIGNEDSHORT_LABEL) ? $conf->global->PROPAL_STATUS_NOTSIGNEDSHORT_LABEL : $langs->trans("PropalStatusNotSignedShort"));
		        $this->labelstatut_short[4]=(! empty($conf->global->PROPAL_STATUS_BILLEDSHORT_LABEL) ? $conf->global->PROPAL_STATUS_BILLEDSHORT_LABEL : $langs->trans("PropalStatusBilledShort"));
		    }
				
			function getLinesArray()
    		{
    			null;	
			}
		
	}
