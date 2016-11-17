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
		    	$url=DOL_URL_ROOT.'/comm/propal.php';
                if ((float) DOL_VERSION >= 4.0) {
                    $url=DOL_URL_ROOT.'/comm/propal/card.php';
                }
				if($actionATM == 'viewVersion') {
					?>
						<script type="text/javascript">
							$(document).ready(function() {
								$('div.tabsAction').html('<?php echo '<div><a id="returnCurrent" href="'.$_SERVER['PHP_SELF'].'?id='.$_REQUEST['id'].'">Retour version courante</a> <a id="butRestaurer" class="butAction" href="'.$url.'?id='.$_REQUEST['id'].'&actionATM=restaurer&idVersion='.$_REQUEST['idVersion'].'">Restaurer</a><a id="butSupprimer" class="butAction" href="'.$url.'?id='.$_REQUEST['id'].'&actionATM=supprimer&idVersion='.$_REQUEST['idVersion'].'">Supprimer</a></div>'?>');
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
					print '<a id="butNewVersion" class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$_REQUEST['id'].'&actionATM=createVersion">Archiver</a>';
					?>
						<script type="text/javascript">
							$(document).ready(function() {
								$("#butNewVersion").appendTo('div.tabsAction');
							})
						</script>
					<?php
					$num = TPropaleHist::listeVersions($db, $object);
				}
				else {
				
					$num = TPropaleHist::listeVersions($db, $object);
					
					
				}
				
				if(!empty($num)) {
					?>
						<script type="text/javascript">
							$("a#comm").first().append(" / v. <?php echo $num +1 ?>");
						console.log($("a#comm").first());
						</script>
					<?php
					
				}
				
			}

		}
		
		return 0;
	}

	function beforePDFCreation($parameters, &$object, &$action, $hookmanager) {
      	global $langs,$db, $user,$conf;

		if(!empty($conf->global->PROPALEHISTORY_SHOW_VERSION_PDF)) {
			//var_dump($object);exit;
			
			define('INC_FROM_DOLIBARR', true);
			dol_include_once("/propalehistory/config.php");
			dol_include_once("/comm/propal/class/propal.class.php");
			dol_include_once('/propalehistory/class/propaleHist.class.php');
				
			$TVersion = TPropaleHist::getVersions($db, $object->id);
			$num = count($TVersion);
			if($num>0) {
				$object->ref .='/'.($num+1);	
			} 
			 
		}

		

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
			//pre($propal,true);

			$object = new PropalHist($db, $object->socid);
			foreach($propal as $k=>$v) $object->{$k} = $v;
			
			foreach($object->lines as &$line) {
				$line->description  = $line->desc;
				$line->db =  $db;
				//$line->fetch_optionals();
			}
			
			//pre($object,true);
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
					document.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?id=<?php echo $_REQUEST['id']?>&mesg=<?php echo $langs->transnoentities('HistoryVersionSuccessfullDelete') ?>";
				</script>
			<?php
					
		}
	}
	
	
}
