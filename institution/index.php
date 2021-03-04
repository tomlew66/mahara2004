<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', '');
define('SECTION_PLUGINTYPE', 'core');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('institution.php');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$inst = param_alphanum('institution');

if (is_isolated() && !in_array($inst, array_keys($USER->get('institutions'))) && !$USER->get('admin')) {
    throw new AccessDeniedException(get_string('notinstitutionmember', 'error'));
}

try {
    $institution = new Institution($inst);
}
catch (Exception $e) {
    throw new NotFoundException(get_string('institutionnotfound', 'mahara', $inst));
}

$admins = $institution->admins();
$staff = $institution->staff();
build_stafflist_html($admins, 'institution', 'admin', $inst);
build_stafflist_html($staff, 'institution', 'staff', $inst);

$displayname = $institution->name == 'mahara' ? get_config('sitename') : $institution->displayname;
define('TITLE', $displayname);

$smarty = smarty();
$smarty->assign('admins', $admins);
$smarty->assign('staff', $staff);
$smarty->assign('PAGEHEADING', get_string('institutioncontacts', 'mahara', TITLE));
$smarty->display('institution/staffadmin.tpl');
