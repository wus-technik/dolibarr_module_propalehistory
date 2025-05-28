<?php

require_once __DIR__.'/../backport/v19/core/class/commonhookactions.class.php';
class ActionsPropalehistory extends \propalehistory\RetroCompatCommonHookActions
{
     /** Overloading the doActions function : replacing the parent's function with the one below
      *  @param      parameters  meta datas of the hook (context, etc...)
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
      *  @param      action             current action (if set). Generally create or edit or null
      *  @return       void
      */

    function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
      	global $conf,$langs,$db;
		$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];
        if(!defined('INC_FROM_DOLIBARR')) { define('INC_FROM_DOLIBARR', true);}

        dol_include_once("/propalehistory/config.php");
		dol_include_once("/comm/propal/class/propal.class.php");

		if (in_array('propalcard',explode(':',$parameters['context'])))
        {
            /**
             * @var Propal $object
             */
	        if($action != 'create' && $action != 'statut' && $action != 'presend') {
	    		dol_include_once("/propalehistory/class/propaleHist.class.php");

		    	$actionATM = GETPOST('actionATM');
		    	$url=DOL_URL_ROOT.'/comm/propal.php';
                if ((float) DOL_VERSION >= 4.0) {
                    $url=DOL_URL_ROOT.'/comm/propal/card.php';
                }
				if ($actionATM == 'viewVersion') {
                    $versionNumSelected = GETPOST('propalehistory_version_num_selected', 'int');
					?>
						<script type="text/javascript">
							$(document).ready(function() {

								$('div.tabsAction').html('<?php echo '<div class="inline-block divButAction"><a id="returnCurrent" href="'.$_SERVER['PHP_SELF'].'?id='.$_REQUEST['id'].'&token='.$newToken.'">'.$langs->trans('ReturnInitialVersion').'</a> <a id="butRestaurer" class="butAction" href="'.$url.'?id='.$_REQUEST['id'].'&actionATM=restaurer&idVersion='.$_REQUEST['idVersion'].'&token='.$newToken.'&versionNum='.$versionNumSelected.'">'.$langs->trans('Restaurer').'</a><a id="butSupprimer" class="butActionDelete" href="'.$url.'?id='.$_REQUEST['id'].'&actionATM=supprimer&idVersion='.$_REQUEST['idVersion'].'">'.$langs->trans('Delete').'</a></div>'?>');
								$('#butRestaurer').insertAfter('#voir');
								$('#butSupprimer').insertBefore('#voir');
								$('#builddoc_form').hide();
							})
						</script>

					<?php
				} elseif ($actionATM == '' && $object->statut == 1) {
                    // TODO Pourquoi c'est ici et pas dans un addMoreActionsButtons ?
					print '<div id="butNewVersion" class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$_REQUEST['id'].'&actionATM=createVersion&token='.$newToken.'">'.$langs->trans('PropaleHistoryArchiver').'</a></div>';
					?>
						<script type="text/javascript">
							$(document).ready(function() {
								$("#butNewVersion").appendTo('div.tabsAction');
							})
						</script>
					<?php
				}
                $versionNum = TPropaleHist::listeVersions($db, $object);

				if ($versionNum > 1 && !getDolGlobalInt('PROPALEHISTORY_HIDE_VERSION_ON_TABS')) {
					?>
						<script type="text/javascript">
							$("a#comm").first().append(" / v. <?php echo $versionNum; ?>");
						console.log($("a#comm").first());
						</script>
					<?php
				}
			}
		}

		return 0;
	}

	function afterPDFCreation($parameters, &$object, &$action, $hookmanager) {
		global $langs, $db, $user, $conf;

		if (getDolGlobalString('PROPALEHISTORY_SHOW_VERSION_PDF')) {
			$object_src = $parameters['object'];
			if (isset($object_src)) {
				$obj = $object_src;
			} else {
				$obj = $object;
			}

			if (
				property_exists($obj, 'element') &&
				$obj->element == 'propal' &&
				!empty($obj->context['propale_history']['original_ref'])
			) {
				$original_ref = $obj->context['propale_history']['original_ref'];

				// Restore ref
				$obj->ref = $original_ref;
				$obj->context['propale_history']['original_ref'] = null;
			}
		}

        return 0;
	}

	function beforePDFCreation($parameters, &$object, &$action, $hookmanager) {
      	global $langs, $db, $user, $conf;

      	if (getDolGlobalString('PROPALEHISTORY_SHOW_VERSION_PDF')) {
			$object_src = $parameters['object'];
			if (isset($object_src)) {
				$obj = $object_src;
			} else {
				$obj = $object;
			}

			if ($obj->element == 'propal' && empty($obj->context['propale_history']['original_ref'])) {
                if(!defined('INC_FROM_DOLIBARR')) { define('INC_FROM_DOLIBARR', true);}
				dol_include_once("/propalehistory/config.php");
				dol_include_once("/comm/propal/class/propal.class.php");
				dol_include_once('/propalehistory/class/propaleHist.class.php');

                $versionNum = TPropaleHist::getVersionNumFromProposalOrVersionList($db, $obj);

				// TODO voir pour trouver une autre méthode DANGER pour la création des PDF
				if ($versionNum > 1 && empty($object->context['docEditPdfGeneration'])) {
					$obj->context['propale_history'] = array('original_ref' => $obj->ref);
					$obj->ref .= '/' . ($versionNum);
				}
			}
		}

        return 0;
	}

	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		if (in_array('propalcard', explode(':', $parameters['context'])) && $action != 'confirm_validate')
		{
			// hack pour remettre la bonne ref pour le bloc showdocuments
            // on validate proposal object->ref was updated with new ref (automatic numbering) and old ref begins with (PROV)
			if (!empty($object->ref_old)) $object->ref = $object->ref_old;
		}
	}

	function formConfirm($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $langs, $db, $user;

		if (in_array('propalcard', explode(':', $parameters['context'])) && getDolGlobalString('PROPALEHISTORY_ARCHIVE_ON_MODIFY'))
		{

			// Ask if proposal archive wanted
			// $action peut être changé à 'modif' dans doActions() après l'affichage de la pop-in : on teste $_REQUEST['action'] à la place
			// CE code revient dans sa version antérieure dû à un bug provoqué dans attachment
			if ( array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'modif') {
				$formquestion = array(
					array('type' => 'checkbox', 'name' => 'archive_proposal', 'label' => $langs->trans("ArchiveProposalCheckboxLabel"), 'value' => 1),
					array('type' => 'date', 'name' => 'archive_proposal_date_', 'label' => $langs->trans("DatePropal"), 'value' => (getDolGlobalInt('PROPALEHISTORY_ARCHIVE_WITH_DATE_NOW') ? dol_now() : $object->date), 'datenow' => 1),
				);
				$form = new Form($db);
				$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ArchiveProposal'), $langs->trans('ConfirmModifyProposal', $object->ref), 'propalhistory_confirm_modify', $formquestion, 'yes', 1);

				$this->results = array();
				$this->resprints = $formconfirm;

				return 1; // replace standard code
			}
		}

        return 0;
	}


	function doActions($parameters, &$object, &$action, $hookmanager) {
		global $conf, $langs, $db, $user;

        if(!defined('INC_FROM_DOLIBARR')) { define('INC_FROM_DOLIBARR', true);}
		dol_include_once("/propalehistory/config.php");
		dol_include_once("/comm/propal/class/propal.class.php");
		dol_include_once('/propalehistory/class/propaleHist.class.php');


		if(isset($_REQUEST['mesg'])) {

			setEventMessage($_REQUEST['mesg']);

		}
		$ATMdb = new TPDOdb;

		if (in_array('propalcard', explode(':', $parameters['context'])) && getDolGlobalString('PROPALEHISTORY_ARCHIVE_ON_MODIFY'))
		{

			if ($action == 'modif') {
				return 1; // on saute l'action par défaut en retournant 1, puis on affiche la pop-in dans formConfirm()
			}

			// Ask if proposal archive wanted
			if ($action == 'propalhistory_confirm_modify') {

				// New version if wanted
				$archive_proposal = GETPOST('archive_proposal', 'alpha');
				if ($archive_proposal == 'on') {
					$proposalDate = dol_mktime(0, 0, 0, GETPOST('archive_proposal_date_month', 'int'), GETPOST('archive_proposal_date_day', 'int'), GETPOST('archive_proposal_date_year', 'int'));

					// hack pour stocker la bonne ref pour pouvoir la remettre avant le bloc showdocuments
					$object->ref_old = $object->ref;

					$result = TPropaleHist::archiverPropale($ATMdb, $object, $proposalDate);
                    if ($result < 0) {
                        setEventMessages($object->error, $object->errors, 'errors');
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $object->id);
                        exit();
                    }
				}
				$action = 'modif'; // On provoque le repassage-en brouillon

				return 0; // Do standard code
			}
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
			$result = TPropaleHist::archiverPropale($ATMdb, $object, (getDolGlobalInt('PROPALEHISTORY_ARCHIVE_WITH_DATE_NOW') ? dol_now() : ''));
            if ($result < 0) {
                setEventMessages($object->error, $object->errors, 'errors');
            } else {
                setEventMessage($langs->transnoentities('HistoryVersionSuccessfullArchived'), 'mesgs');
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $object->id);
            exit();
		} elseif($actionATM == 'restaurer') {
            if (getDolGlobalString('PROPALEHISTORY_RESTORE_KEEP_VERSION_NUM')) {
                $versionNumSelected = GETPOST('versionNum', 'int');

                // save current proposal version before restoring
                $currentProposal = new Propal($db);
                $currentProposal->fetch($object->id);
                $result = TPropaleHist::archiverPropale($ATMdb, $currentProposal);
                if ($result < 0) {
                    setEventMessages($currentProposal->error, $object->errors, 'errors');
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $object->id);
                    exit();
                }
            } else {
                $versionNumSelected = 0;
            }

			TPropaleHist::restaurerPropale($ATMdb, $object, $versionNumSelected);
		} elseif($actionATM == 'supprimer') {

			$version = new TPropaleHist;
			$version->load($ATMdb, $_REQUEST['idVersion']);
			$version->delete($ATMdb);

			?>
				<script language="javascript">
					document.location.href="<?php echo $_SERVER['PHP_SELF'] ?>?id=<?php echo $_REQUEST['id']?>&mesg=<?php echo $langs->transnoentities('HistoryVersionSuccessfullDelete') ?>";
				</script>
			<?php

            /* TODO J'ai essayé de rajouter un exit ici, ce qui serait complètement logique, mais ça a tout cassé...
             * Visiblement, le module est conçu pour que le script continue de s'exécuter. Dont acte, mais entre ça, les
             * redirections en JS plutôt que via header(), et les messages de retour utilisateur passés en paramètre
             * lors de la redirection, on est dans une méthodologie bien dégueulasse, il y a donc du refaisage à
             * entreprendre à mon sens - MdLL, 07/04/2020
             */
		}

		if (in_array('propalcard', explode(':', $parameters['context'])) && $action != 'confirm_validate')
		{
			// hack pour stocker la bonne ref pour pouvoir la remettre avant le bloc showdocuments
            // on validate proposal object->ref is still begin with (PROV) and after this hook it will change with a new ref (automatic numbering)
			$object->ref_old = $object->ref;
		}

		return 0;
	}

	/**
	 * Enables modules that use $object->ref to build a file path to get the original ref (without the trailing /[DIGITS])
	 *
	 * @param array        $parameters
	 * @param CommonObject $object  The object that holds the ref which PropaleHistory has modified
	 * @param string       $action
	 * @param HookManager  $hookmanager
	 * @return int
	 */
	function overrideRefForFileName($parameters, &$object, &$action, $hookmanager) {
		if (!isset($object->context['propale_history']['original_ref'])) {
			// the specified proposal doesn't have any history entries in llx_propale_history so we don't override ref
			return 0;
		} else {
			// override default
			$this->resprints = $object->context['propale_history']['original_ref'];
			return 1;
		}
	}
}
