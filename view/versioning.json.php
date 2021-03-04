<?php
/**
 *@package    mahara
* @subpackage core
* @author     Catalyst IT Ltd
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
* @copyright  For copyright information on Mahara, please see the README file distributed with this software.
*
*/

define('INTERNAL', 1);
define('JSON', 1);


require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'institution.php');

$fromdate  = param_variable('fromdate', null);
$todate   = param_variable('todate', null);
$viewid = param_integer('view', 0);
if (!can_view_view($viewid)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
if ($fromdate || $todate ) {
    $versions = View::get_versions($viewid, $fromdate, $todate);
}
else {
    $versions = View::get_versions($viewid);
}

$data = array();
if ($versions->count > 0) {
    end($versions->data);
    $lastkey = key($versions->data);
    reset($versions->data);
    $language = current_language();
    if ($language != 'en.utf8') {
        $langmonths = explode(',', get_string('element.date.monthnames', 'pieforms'));
        $engmonths = explode(',', get_string_from_language('en.utf8', 'element.date.monthnames', 'pieforms'));
        $months = array_combine($engmonths, $langmonths);
    }
    foreach ($versions->data as $key => $value) {
        $view = new View($viewid);
        $value->blockdata_formatted = $view->format_versioning_data($value->blockdata, $key);
        $datestr = strftime('%e %B', strtotime($value->ctime));
        if ($language != 'en.utf8') {
            if (preg_match('/(\w+)$/', $datestr, $matches)) {
                $datestr = str_replace($matches[1], $months[$matches[1]], $datestr);
            }
        }

        $data[$key] = array(
            "isSelected" => ($key == $lastkey ? true : false),
            "taskTitle" => (isset($value->blockdata_formatted->title) ? $value->blockdata_formatted->title : $value->viewname),
            "taskSubTitle" => $view->display_author() . ', ' . format_date(strtotime($value->ctime)),
            "assignDate" => date('d/m/Y\TH:i', strtotime($value->ctime)),
            "assignID" => $value->id,
            "taskShortDate" => $datestr,
            "taskDetails" => $value->blockdata_formatted->html,
            "gridlayout" => isset($value->blockdata_formatted->newlayout),
        );
    }
}

json_reply(false, array(
    'message' => null,
    'data' => $data
));
