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
		dol_include_once("/comm/propal/class/propal.class.php");
		
		if (in_array('propalcard',explode(':',$parameters['context']))) 
        {
        	
	        if($action != 'create' && $action != 'statut' && $action != 'presend') {	
	    		dol_include_once("/propalehistory/class/propaleHist.class.php");
				$ATMdb = new TPDOdb;
			
		
		    	$actionATM = GETPOST('actionATM');
				if($actionATM == 'viewVersion') {
					?>
						<script type="text/javascript">
							$(document).ready(function() {
								$('div.tabsAction').html('<?php echo '<div><a id="returnCurrent" href="'.dol_buildpath('/comm/propal.php',1).'?id='.$_REQUEST['id'].'">Retour version courante</a> <a id="butRestaurer" class="butAction" href="'.DOL_URL_ROOT.'/comm/propal.php?id='.$_REQUEST['id'].'&actionATM=restaurer&idVersion='.$_REQUEST['idVersion'].'">Restaurer</a><a id="butSupprimer" class="butAction" href="'.DOL_URL_ROOT.'/comm/propal.php?id='.$_REQUEST['id'].'&actionATM=supprimer&idVersion='.$_REQUEST['idVersion'].'">Supprimer</a></div>'?>');
								$('#butRestaurer').insertAfter('#voir');
								$('#butSupprimer').insertBefore('#voir');
								$('#builddoc_form').hide();
							})
						</script>
					
					<?php
					
					TPropaleHist::listeVersions($db, $object);
				} elseif($actionATM == 'createVersion') {
					TPropaleHist::listeVersions($db, $object);
				} elseif($actionATM == '' && $object->statut == 1) {
					print '<a id="butNewVersion" class="butAction" href="'.dol_buildpath('/comm/propal.php',1).'?id='.$_REQUEST['id'].'&actionATM=createVersion">Archiver</a>';
					?>
						<script type="text/javascript">
							$(document).ready(function() {
								$("#butNewVersion").appendTo('div.tabsAction');
							})
						</script>
					<?php
					TPropaleHist::listeVersions($db, $object);
				}
				else {
				
					TPropaleHist::listeVersions($db, $object);
				}
			}

		}
		
		return 0;
	}

	function doActions($parameters, &$object, &$action, $hookmanager) {
      	global $langs,$db, $user;

		define('INC_FROM_DOLIBARR', true);
		dol_include_once("/propalehistory/config.php");
		dol_include_once("/comm/propal/class/propal.class.php");
		dol_include_once('/propalehistory/class/propaleHist.class.php');

		if(isset($_REQUEST['mesg'])) {
		
			setEventMessage($_REQUEST['mesg']);

		}
		
		$ATMdb = new TPDOdb;

		if($_REQUEST['action'] == 'delete'){
			
			global $db;

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."propale_history";
			$sql.= " WHERE fk_propale = ".$_REQUEST['id'];

			$resql = $db->query($sql);

		}
		
		if(isset($_REQUEST['actionATM'])) {
			$actionATM = $_REQUEST['actionATM'];
		} else {
			$actionATM = '';
		}
		
		if($actionATM == 'viewVersion') {
			
			$version = new TPropaleHist;
			$version->load($ATMdb, $_REQUEST['idVersion']);

			$propal = $version->getObject();

			$object = new PropalHist($db, $object->socid);
			foreach($propal as $k=>$v) $object->{$k} = $v;
			
			foreach($object->lines as &$line) {
				$line->description  = $line->desc;
			}
			
			$object->id = $_REQUEST['id'];
			$object->db = $db;
		} elseif($actionATM == 'createVersion') {
			
			TPropaleHist::archiverPropale($ATMdb, $object);

		} elseif($actionATM == 'restaurer') {
			
			TPropaleHist::restaurerPropale($ATMdb, $object);

		} elseif($actionATM == 'supprimer') {
			
			$version = new TPropaleHist;	
			$version->load($ATMdb, $_REQUEST['idVersion']);
			$version->delete($ATMdb);

			?>
				<script language="javascript">
					document.location.href="<?php echo dirname($_SERVER['PHP_SELF'])?>/propal.php?id=<?php echo $_REQUEST['id']?>&mesg=<?php echo $langs->transnoentities('HistoryVersionSuccessfullDelete') ?>";
				</script>
			<?php
					
		}
	}
	
	
}
