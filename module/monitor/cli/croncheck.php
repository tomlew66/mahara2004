#!/usr/bin/env php
<?php
/**
 * Run the cron check to ensure there are no stuck/locked processes.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * This is run as a CLI. It conforms to the Nagios plugin standard.
 *
 * See also:
 *  - http://nagios.sourceforge.net/docs/3_0/pluginapi.html
 *  - https://nagios-plugins.org/doc/guidelines.html#PLUGOUTPUT
 */

define('CLI', 1);
define('INTERNAL',  1);

$MAHARA_ROOT = dirname(dirname(dirname(dirname(__FILE__)))) . '/';

require($MAHARA_ROOT . '/init.php');
require(get_config('libroot') . 'cli.php');
require_once(get_config('docroot') . '/module/monitor/lib.php');
require_once(get_config('docroot') . '/module/monitor/type/MonitorType_processes.php');

$cli = get_cli();
if (!PluginModuleMonitor::is_active()) {
    $cli->cli_exit(get_string('monitormodulenotactive', 'module.monitor'), 2);
}

$options = array();
$options['okmessagedisabled'] = new stdClass();
$options['okmessagedisabled']->description = get_string('okmessagedisabled', 'module.monitor');
$options['okmessagedisabled']->shortoptions = array('d');
$options['okmessagedisabled']->required = false;

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('croncheckhelp', 'module.monitor');
$cli->setup($settings);

$processes = MonitorType_processes::get_long_running_cron_processes();

$totalProcesses = count($processes);
if ($totalProcesses > 0) {
    $longprocessesarray = MonitorType_processes::extract_processes($processes);
    $longprocesses = implode(',', $longprocessesarray);

    $cli->cli_exit(get_string('checkingcronprocessesfail', 'module.monitor', $totalProcesses, get_config('sitename'), $longprocesses, $totalProcesses), 2);
}

if (!$cli->get_cli_param('okmessagedisabled')) {
    $cli->cli_exit(get_string('checkingcronprocessessucceed', 'module.monitor', get_config('sitename')), 0);
}

$cli->cli_exit(null, 0);
