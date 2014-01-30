<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */
define('INC_FROM_CRON_SCRIPT', true);

require('../config.php');
require('../class/propaleHist.class.php');
dol_include_once('/comm/propal/propal.class.php');

$PDOdb=new TPDOdb;
$PDOdb->db->debug=true;

$o=new TPropaleHist($db);
$o->init_db_by_vars($PDOdb);