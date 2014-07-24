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
        	
	        if($action != 'create' && $action != 'statut') {	
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
					
					$this->listeVersions($db, $object);
				} elseif($actionATM == 'createVersion') {
					$this->listeVersions($db, $object);
				} elseif($actionATM == '' && $object->statut == 1) {
					print '<a id="butNewVersion" class="butAction" href="'.dol_buildpath('/comm/propal.php',1).'?id='.$_REQUEST['id'].'&actionATM=createVersion">Archiver</a>';
					?>
						<script type="text/javascript">
							$(document).ready(function() {
								$("#butNewVersion").appendTo('div.tabsAction');
							})
						</script>
					<?php
					$this->listeVersions($db, $object);
				}
				else {
				
					$this->listeVersions($db, $object);
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

			$propal = unserialize($version->serialized_parent_propale);

			$object = new PropalHist($db, $object->socid);
			foreach($propal as $k=>$v) $object->{$k} = $v;
			
			/*$object = $tmp;
			$object->__construct($db, $object->socid);*/
			$object->id = $_REQUEST['id'];

		} elseif($actionATM == 'createVersion') {
			
			$this->archiverPropale($ATMdb, $object);

		} elseif($actionATM == 'restaurer') {
			
			$this->restaurerPropale($ATMdb, $object);

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

	function archiverPropale(&$ATMdb, &$object)
	{
		global $langs;

		$newVersionPropale = new TPropaleHist;

		$newVersionPropale->serialized_parent_propale = serialize($object);
		$newVersionPropale->date_version = dol_now();
		$newVersionPropale->fk_propale = $object->id;
		$newVersionPropale->total = $object->total_ht;

		$newVersionPropale->save($ATMdb);
		?>
			<script language="javascript">
				document.location.href="<?php echo dirname($_SERVER['PHP_SELF'])?>/propal.php?id=<?php echo $_REQUEST['id']?>&mesg=<?php echo $langs->transnoentities('HistoryVersionSuccessfullArchived') ?>";
			</script>
		<?php
		
		/*if($_REQUEST['actionATM'] == 'createVersion') {
			setEventMessage('Version sauvegardée avec succès.', 'mesgs');
		}*/

	}
	
	function restaurerPropale(&$ATMdb, &$object) {
		
		global $db, $user,$langs;

		$versionPropale = new TPropaleHist;
		$versionPropale->load($ATMdb, $_REQUEST['idVersion']);
		$propale = unserialize($versionPropale->serialized_parent_propale);
		$propale->statut = 0;
		$object->statut = 0;

		foreach($object->lines as $line) {

			$object->deleteline($line->rowid)."<br />";

		}	

		foreach($propale->lines as $line) {
			
			$object->addline(
				$line->desc,
				$line->subprice,
				$line->qty,
				$line->tva_tx,
				$line->localtax1_tx,
				$line->localtax2_tx,
				$line->fk_product,
				$line->remise_percent,
				'HT',
				$line->subprice,
				$line->info_bits,
				$line->product_type,
				$line->rang,
				$line->special_code,
				$line->fk_parent_line,
				$line->fk_fournprice,
				$line->pa_ht,
				$line->label,
				$line->date_start,
				$line->date_end,
				$line->array_option
			);

		}
		
		$object->set_draft($user); // Pour pouvoir modifier les dates, le statut doit être à 0
		$object->set_availability($user, $propale->availability_id);
		$object->set_date($user, $propale->date);
		$object->set_date_livraison($user, $propale->date_livraison);
		$object->set_echeance($user, $propale->fin_validite);
		$object->set_ref_client($user, $propale->ref_client);
		$object->set_demand_reason($user, $propale->demand_reason_id);
		$object->setPaymentMethods($propale->mode_reglement_id);
		$object->setPaymentTerms($propale->cond_reglement_id);
		$object->valid($user);

		header('Location: '.dol_buildpath('/comm/propal.php?id='.$_REQUEST['id'].'&mesg='.$langs->transnoentities('HistoryVersionSuccessfullRestored'), 1));

	}
	
	function listeVersions(&$db, $object) {

		$sql.= " SELECT rowid, date_version, date_cre, total";
		$sql.= " FROM ".MAIN_DB_PREFIX."propale_history";
		$sql.= " WHERE fk_propale = ".$object->id;
		$sql.= " ORDER BY rowid ASC";
		$resql = $db->query($sql);

if(isset($_REQUEST['DEBUG'])) print $sql;

		if($resql) {

			$num = $db->num_rows($resql);

if(isset($_REQUEST['DEBUG'])) var_dump($db, $resql);
	
			if($num>0) {
				
				print '<div id="formListe" style="clear:both; margin-top:15px">';
				print '<form name="formVoirPropale" method="POST" action="'.dol_buildpath('/comm/propal.php',1).'?id='.GETPOST('id','int').'">';
				print '<input type="hidden" name="actionATM" value="viewVersion" />';
				print '<input type="hidden" name="socid" value="'.$object->socid.'" />';
				print '<select name="idVersion">';
				$i = 1;
	
				while($row = $db->fetch_object()) {
					
					if(isset($_REQUEST['idVersion']) && $_REQUEST['idVersion'] == $row->rowid){
						$selected = 'selected="selected"';
					} else {
						$selected = "";
					}
					echo $selected;
					print '<option id="'.$row->rowid.'" value="'.$row->rowid.'" '.$selected.'>Version n° '.$i.' de '.price($row->total).'&euro; du '.date_format(date_create($row->date_cre), "d/m/Y").'</option>';
	
					$i++;
	
				}
				
				print '</select>';
				print '<input class="butAction" id="voir" value="Visualiser" type="SUBMIT" />';
				print '</form>';
				print '</div>';
				
				?>
				<script type="text/javascript">
					$(document).ready(function(){
						$("#formListe").appendTo('div.tabsAction');
					})
				</script>
				<?php
				
			}
			else{
				
				null;
			}
			
			
		}
	}
}
