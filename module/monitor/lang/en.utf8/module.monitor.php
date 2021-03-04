<?php
/**
 * A language strings file.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['monitor'] = 'Monitor';

// Configuration settings.
$string['monitoringchecks'] = 'Monitoring checks';
$string['cronlockhours'] = 'Cron lock hours';
$string['cronlockhoursdescription'] = 'The maximum number of hours a cron process should run.';
$string['hourstoconsiderelasticsearchrecordold'] = 'Hours to consider elasticsearch queue old';
$string['hourstoconsiderelasticsearchrecordolddescription'] = 'Number of hours that elasticsearch record can remain unprocessed before drawing attention to it';
$string['monitormodulenotactive'] = 'The monitor plugin is not active. Please go to the "Administration menu" → "Extensions" → "Plugin administration" page to install or activate the plugin.';
$string['allowedips'] = 'Allowed IPs';
$string['allowedipsdescription'] = 'Enter safe IP addresses, one per line, for the monitor to respond to. If left blank then the monitor will be restricted by $cfg->urlsecret if that is set.';
$string['accessdeniednotvalidip'] = 'Your IP "%s" is not in the allowed IP list and you will be blocked from checking the monitor.';

// cron processes check
$string['croncheckhelp'] = 'Identify which cron processes are long running:

croncheck.php [options] mahara_path

Options:
-h, --help          Print out this help

Example:
sudo -u www-data /usr/bin/php croncheck.php /var/www/mymaharaproject
';
$string['okmessagedisabled'] = 'If set then no OK message will be displayed.';
$string['checkingcronprocesses'] = 'Checking cron processes';
$string['checkingcronprocessessucceed'] = 'OK: There are no long running cron processes for %s.';
$string['checkingcronprocessesfail'] = array(
    'CRITICAL: There is %s long running cron process for %s: %s',
    'CRITICAL: There are %s long running cron processes for %s: %s',
);
$string['displaydatetimeformat'] = 'd/m/Y H:i:s';

// Monitor
$string['processes'] = 'Cron processes';
$string['ldaplookup'] = 'LDAP lookup';
$string['elasticsearch'] = 'Elasticsearch';

$string['longrunningprocesses'] = 'Long running processes';
$string['processname'] = 'Process';
$string['datestarted'] = 'Time started';
$string['exportresultscsv'] = 'Export results in CSV format';

// Elasticsearch check
$string['queuestatus'] = 'Queue status';
$string['failedqueuesize'] = 'Number of failed records for more than 1 hour';
$string['queuehasolditems'] = array(
    'Unprocessed items in elasticsearch queue older than %s hour',
    'Unprocessed items in elasticsearch queue older than %s hours'
);
$string['unprocessedqueuesize'] = 'Total number of unprocessed records';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['checkingelasticsearcholdunprocesessedfail'] = array(
    'CRITICAL: There are unprocessed records in Elasticsearch queue older than %s hour',
    'CRITICAL: There are unprocessed records in Elasticsearch queue older than %s hours',
);
$string['checkingelasticsearchfailedrecords'] = 'CRITICAL: There are failed records in Elasticsearch queue older than 1 hour';
$string['checkingelasticsearchsucceed'] = 'OK: There are no unprocessed or failed records in Elasticsearch queue';
$string['elasticsearchcheckhelp'] = 'Check elasticsearch processing queue:

elasticsearchcheck.php [options] mahara_path

Options:
-h, --help          Print out this help

Example:
sudo -u www-data /usr/bin/php elasticsearchcheck.php /var/www/mymaharaproject
';

// LDAP check
$string['ldapstatussuccess'] = 'LDAP check is successful.';
$string['ldapstatusfail'] = 'LDAP check has failed with the following error: %d';
$string['ldapcheckhelp'] = 'Identify which LDAP instances are not working:

ldaplookupcheck.php [options] mahara_path

Options:
-h, --help          Print out this help

Example:
sudo -u www-data /usr/bin/php ldaplookupcheck.php /var/www/mymaharaproject
';
$string['checkingldapinstancessucceed'] = 'OK: There are no invalid LDAP connections for %s.';
$string['checkingldapinstancesfail'] = array(
    'CRITICAL: There is %s invalid LDAP connection for %s: %s',
    'CRITICAL: There are %s invalid LDAP connections for %s: %s',
);
$string['institution'] = 'Institution';
$string['ldapauthority'] = 'LDAP authority name';
$string['ldapstatus'] = 'Status';
$string['ldapstatusmessage'] = 'Status details';
$string['ldapstatustabletitle'] = 'LDAP status';
$string['statussuccess'] = 'Ok';
$string['statusfail'] = 'Failed';

// LDAP suspended users
$string['ldapsuspendedusers'] = 'LDAP suspended users';
$string['ldapsuspendeduserstabletitle'] = 'Percentage of LDAP users suspended by LDAP user sync since midnight';
$string['ldapsuspendeduserspercentage'] = 'LDAP suspended users percentage';
$string['ldapsuspendeduserspercentagedescription'] = 'The maximum percentage of users suspended by the LDAP user sync since midnight before flagging it as an issue.';
$string['item'] = 'Item';
$string['status'] = 'Status';
$string['details'] = 'Details';
$string['ldapsuspendeduserscheckhelp'] = 'Check for large volumes of LDAP users getting suspended in the LDAP user sync process

ldapsuspendeduserscheck.php [options] mahara_path

Options:
-h, --help          Print out this help

Example:
sudo -u www-data /usr/bin/php ldapsuspendeduserscheck.php /var/www/mymaharaproject
';
$string['checkingldapsuspendedusersssucceed'] = 'OK: There are no LDAP instances for %s that have surpassed the suspended users warning threshold.';
$string['checkingldapsuspendedusersfail'] = array(
    'CRITICAL: There is %s LDAP instance for %s that has surpassed the suspended users warning threshold: %s',
    'CRITICAL: There are %s LDAP instances for %s that have surpassed the suspended users warning threshold: %s',
);
