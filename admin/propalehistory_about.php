<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/about.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example about page
 * 				Put some comments here
 */

// Dolibarr environment
require '../config.php';

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/doc2project.lib.php';

// Translations
$langs->load("doc2project@doc2project");

// Access control
if (! $user->admin) {
    accessforbidden();
}

/*
 * View
 */
$page_name = "Doc2ProjectAbout";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = Doc2ProjectAdminPrepareHead();
dol_fiche_head(
    $head,
    'about',
    $langs->trans("Module104250Name"),
    0,
    'project'
);

// About page goes here
print '<div style="float: left;"><img src="../img/Dolibarr_Preferred_Partner_logo.png" /></div>';
print '<div>'.$langs->trans('ATMAbout').'</div>';

dol_fiche_end();

print '<br><center>';
print '<a href="http://www.atm-consulting.fr" target="_blank"><img src="../img/ATM_logo.jpg" /></a>';
print '</center>';

llxFooter();

$db->close();