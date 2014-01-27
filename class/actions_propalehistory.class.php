<?php
class ActionsPropalehistory
{ 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */
      
    function formObjectOptions($parameters, &$object, &$action, $hookmanager) 
    {
      	global $langs,$db;
		define('INC_FROM_DOLIBARR', true);
		dol_include_once("/propalehistory/config.php");
		require_once("propaleHist.class.php");
		
		if (in_array('propalcard',explode(':',$parameters['context']))) 
        {
			$ATMdb = new TPDOdb;
        	isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action = "";
			if($action == 'createVersion') {
	        	$newVersionPropale = new TPropaleHist;
				$newVersionPropale->serialized_parent_propale = serialize($object);
				$newVersionPropale->date_version = date("Y-m-d h:i:s");
				$newVersionPropale->fk_propale = $object->id;
				$newVersionPropale->save($ATMdb);
				setEventMessage('Version sauvegardée avec succès !', 'mesgs');
			}
			
			if($object->statut == 1) {
				print '<a id="butNewVersion" class="butAction" href="'.DOL_URL_ROOT.'/comm/propal.php?id='.$_REQUEST['id'].'&action=createVersion">Historiser propale</a>';
				?>
					<script type="text/javascript">
						$(document).ready(function() {
							$("#butNewVersion").appendTo('div.tabsAction');
						})
					</script>
				<?
			}
		}
		
		return 0;
	}
     
    function formEditProductOptions($parameters, &$object, &$action, $hookmanager) 
    {
		
    	if (in_array('invoicecard',explode(':',$parameters['context'])))
        {
        	
        }
		
        return 0;
    }

	function formAddObjectLine ($parameters, &$object, &$action, $hookmanager) {
		
		global $db;
		
		if (in_array('ordercard',explode(':',$parameters['context'])) || in_array('invoicecard',explode(':',$parameters['context']))) 
        {
        	
        }

		return 0;
	}

	function printObjectLine ($parameters, &$object, &$action, $hookmanager){
		
		global $db;
		
		if (in_array('ordercard',explode(':',$parameters['context'])) || in_array('invoicecard',explode(':',$parameters['context']))) 
        {
        	
        }

		return 0;
	}
}