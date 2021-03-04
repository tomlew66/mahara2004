<?php
/**
 * Run the cron check to ensure there are no stuck/locked processes.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */


define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminhome/monitor');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . '/module/monitor/lib.php');
if (!PluginModuleMonitor::is_active()) {
    throw new AccessDeniedException(get_string('monitormodulenotactive', 'module.monitor'));
}
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_processes.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_ldaplookup.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_ldapsuspendedusers.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_elasticsearch.php');

define('TITLE', get_string('monitor', 'module.monitor'));

$type = param_alpha('type', PluginModuleMonitor::type_default);
define('SUBSECTIONHEADING', get_string($type, 'module.monitor'));
$subpages = PluginModuleMonitor::get_list_of_types();
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

if (!in_array($type, $subpages)) {
    $type = PluginModuleMonitor::type_default;
}

switch ($type) {
case PluginModuleMonitor::type_ldaplookup:
    $results = MonitorType_ldaplookup::get_ldap_instances();
    $data = MonitorType_ldaplookup::format_for_display($results, $limit, $offset);
    break;
case PluginModuleMonitor::type_ldapsuspendedusers:
    $results = MonitorType_ldapsuspendedusers::get_ldap_suspendedusers();
    $data = MonitorType_ldapsuspendedusers::format_for_display($results, $limit, $offset);
    break;
case PluginModuleMonitor::type_elasticsearch:
    $params = array();
    $params[] = MonitorType_elasticsearch::get_failed_queue_size();
    $params[] = MonitorType_elasticsearch::is_queue_older_than();
    $params[] = MonitorType_elasticsearch::get_unprocessed_queue_size();
    $data = MonitorType_elasticsearch::format_for_display($params);
    break;
case PluginModuleMonitor::type_processes:
default:
    $results = MonitorType_processes::get_long_running_cron_processes();
    $data = MonitorType_processes::format_for_display($results, $limit, $offset);
    break;
}

$js = '';
if (!empty($data['table']) && !empty($data['table']['pagination_js'])) {
    $js .= 'jQuery(function() {' . $data['table']['pagination_js'] . '});';
}
$subnav = array('subnav' => array('class' => 'monitor'));
foreach ($subpages as $k => $page) {
    $subnav[$page] = array('path' => 'adminhome/monitor',
                           'url' => 'module/monitor/monitor.php?type=' . $page,
                           'title' => get_string($page, 'module.monitor'),
                           'weight' => ($k * 10) + 10,
                           );
    if ($page == $type) {
        $subnav[$page]['selected'] = 1;
    }
}

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-binoculars');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('SUBPAGENAV', $subnav);
$smarty->assign('PAGEHEADING', get_string('monitor', 'module.monitor'));
$smarty->assign('subpages', $subpages);
$smarty->assign('subpagedata', $data);
$smarty->assign('type', $type);
$smarty->display('module:monitor:monitor.tpl');
