<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 *
 * @package    mahara
 * @subpackage blocktype-openbadgedisplayer
 * @author     Discendum Oy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Discedum Oy http://discendum.com
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */


defined('INTERNAL') || die();

class PluginBlocktypeOpenbadgedisplayer extends SystemBlocktype {

    private static $source = null;
    private static $deprecatedhosts = array('backpack');

    public static function single_only() {
        return false;
    }

    public static function single_artefact_per_block() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.openbadgedisplayer');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.openbadgedisplayer');
    }

    public static function get_categories() {
        return array('external');
    }

    public static function get_css_icon($blocktypename) {
        return 'shield-alt';
    }

    public static function get_viewtypes() {
        return array('portfolio', 'profile');
    }

    public static function is_deprecated() {
        return get_string('componentdeprecated', 'admin', 'Mozilla backpack');
    }

    public static function get_backpack_source() {
        if (is_null(self::$source)) {
            $source = get_config('openbadgedisplayer_source');

            if (!empty($source)) {
                $source = (array) $source;
            }
            else {
                return false;
            }

            self::$source = $source;
        }
        // remove deprecated hosts
        foreach (self::$deprecatedhosts as $deprecatedhost) {
            if (isset(self::$source[$deprecatedhost])) {
                unset(self::$source[$deprecatedhost]);
            }
        }

        return self::$source;
    }

    public static function get_blocktype_type_content_types() {
        return array('openbadgedisplayer' => array('media'));
    }

    public static function app_tabs() {
        return array(
            'badgr' => array(
                'path' => 'settings/badgr',
                'url' => 'blocktype/openbadgedisplayer/badgrtoken.php',
                'title' => get_string('badgrtokentitle', 'blocktype.openbadgedisplayer'),
                'weight' => 20,
                'iconclass' => 'flag'
            ),
            'apps' => array (
                'path' => 'settings/apps',
                'url' => '/account/apps.php',
                'title' => get_string('overview'),
                'weight' => 5,
                'iconclass' => 'flag'
            )
        );
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata');
        if (empty($configdata) || !isset($configdata['badgegroup']) || !get_config('openbadgedisplayer_source')) {
            return;
        }

        $host = 'passport';
        $badgegroups = $configdata['badgegroup'];
        $html = '';

        // Support the legacy format (a string, not an array).
        if (is_string($badgegroups)) {
            $badgegroups = array($badgegroups);
        }
        // add warning message if the block contains badges from deprecated host
        $deprecatedmsg = array();
        foreach ($badgegroups as $selectedbadgegroup) {
            list($host, $uid, $selectedgroupid) = explode(':', $selectedbadgegroup);
            // Display 'Host was deprecated' message
            if (in_array($host, self::$deprecatedhosts)) {
                $deprecatedmsg[] = get_string('title_' . $host, 'blocktype.openbadgedisplayer');
            }
        }
        if ($deprecatedmsg) {
            $html .= get_string('deprecatedhost', 'blocktype.openbadgedisplayer', implode(', ', $deprecatedmsg));
        }

        if ($editing) {
            $items = array();
            foreach ($badgegroups as $selectedbadgegroup) {
                list($host, $uid, $selectedgroupid) = explode(':', $selectedbadgegroup);
                if (!in_array($host, self::$deprecatedhosts)) {
                    $allbadgegroups = self::get_badgegroupnames($host, $uid);
                    if (!empty($allbadgegroups)) {
                        foreach ($allbadgegroups as $badgegroupid => $name) {
                            if ((int) $selectedgroupid === (int) $badgegroupid) {
                                $items[] = $name;
                            }
                        }
                    }
                }
            }

            if (count($items) > 0) {
                $html .= '<ul>' . implode('', array_map(function ($item) { return "<li>{$item}</li>"; }, $items)) . '</ul>';
            }
            else {
                $html .= '<p class="editor-description">' . get_string('nobadgesselectone', 'blocktype.openbadgedisplayer') . '</p>';
            }

            return $html;
        }
        else {
            $smarty = smarty_core();
            $smarty->left_delimiter = '{{';
            $smarty->right_delimiter = '}}';
            $smarty->assign('id', $instance->get('id'));
            $smarty->assign('badgehtml', self::get_badges_html($badgegroups));

            $html .= $smarty->fetch('blocktype:openbadgedisplayer:openbadgedisplayer.tpl');
        }

        return $html;
    }

    /**
     * Returns html code for badge in a group
     * @param string $group in format <host>:<uid>:<badgegroupid>
     * @param bool $fromcache if true the info will be fetched from database first
     * @return string HTML code
     */
    private static function get_badge_html($group, $fromcache=false) {
        if (!isset($group) && !is_string($group)) {
            return '';
        }

        $parts = explode(':', $group);

        if (count($parts) < 3) {
            return '';
        }

        $host = $parts[0];
        $uid = $parts[1];
        $badgegroupid = $parts[2];

        // Display 'Mozilla backpack was deprecated' message
        if (in_array($host, self::$deprecatedhosts)) {
            // return get_string('deprecatedhost', 'blocktype.openbadgedisplayer', get_string('title_' . $host, 'blocktype.openbadgedisplayer'));
            return '';
        }

        // Try to get the badge html from database first
        // Get badge group html using uid (backpackid)
        if ($fromcache && $badgegroup = get_record_select('blocktype_openbadgedisplayer_data',
                'host = ? AND uid = ? AND badgegroupid = ? AND lastupdate > ?',
                array($host, $uid, $badgegroupid, db_format_timestamp(strtotime('-1 day'))),
                'html')) {
            if (isset($badgegroup->html)) {
                return $badgegroup->html;
            }
        }

        $html = '';
        $existing = array();
        $backpack_url = self::get_backpack_url($host);
        if ($host == 'badgr') {
            $url = $backpack_url . 'v2/backpack/collections/' . $badgegroupid;
            $res = mahara_http_request(
                    array(
                        CURLOPT_URL        => $url,
                        CURLOPT_HTTPHEADER => array('Authorization: Token ' . $uid),
                    )
            );
        }
        else {
            $url = $backpack_url . 'displayer/' . $uid . '/group/' . $badgegroupid . '.json';
            $res = mahara_http_request(array(CURLOPT_URL => $url));
        }

        if ($res->info['http_code'] != 200) {
            return '';
        }

        $json = json_decode($res->data);
        if (isset($json->status) && $json->status->success) {
            foreach ($json->result as $collection) {
                foreach ($collection->assertions as $assertion) {
                    // Currently I can't see a way to fetch the badge/assertion/issuer info
                    // as one json blob/one curl request
                    $url = $backpack_url . 'v2/backpack/assertions/' . $assertion;
                    $res2 = mahara_http_request(
                        array(
                            CURLOPT_URL        => $url,
                            CURLOPT_HTTPHEADER => array('Authorization: Token ' . $uid),
                        )
                    );
                    $badge = json_decode($res2->data);
                    $res3 = mahara_http_request(
                        array(
                            CURLOPT_URL        => $badge->result[0]->badgeclassOpenBadgeId,
                            CURLOPT_HTTPHEADER => array('accept: application/json'),
                        )
                    );
                    $badgeinfo = json_decode($res3->data);
                    $res4 = mahara_http_request(
                        array(
                            CURLOPT_URL        => $badgeinfo->issuer,
                            CURLOPT_HTTPHEADER => array('accept: application/json'),
                        )
                    );
                    if (!empty($badgeinfo->id)) {
                        $criteria = $badgeinfo->id;
                    }
                    else if (is_array($badgeinfo->criteria)) {
                        $criteria = $badgeinfo->criteria->id;
                    }
                    else if (is_string($badgeinfo->criteria)) {
                        $criteria = $badgeinfo->criteria;
                    }
                    else {
                        $criteria = '';
                    }
                    $issuer = json_decode($res4->data);
                    $data_assertion = $badge->result[0];
                    $data_assertion->issued_on = strtotime($data_assertion->issuedOn);
                    $data_assertion->expires = strtotime($data_assertion->expires);
                    $data_assertion->badge = new stdClass();
                    $data_assertion->badge->name = hsc($badgeinfo->name);
                    $data_assertion->badge->description = hsc($badgeinfo->description);
                    $data_assertion->badge->criteria = $criteria;
                    $data_assertion->badge->_location = $data_assertion->openBadgeId;
                    $data_assertion->badge->image = $data_assertion->image;
                    $data_assertion->badge->issuer = new stdClass();
                    $data_assertion->badge->issuer->origin = $issuer->url;
                    $data_assertion->badge->issuer->name = $issuer->name;
                    $data_assertion->badge->issuer->org = $issuer->email;

                    $html .= '<img tabindex="0" id="' . (preg_replace('/\:/', '_', $group)) . '" '
                          . 'src="' . $badge->result[0]->image . '" '
                          . 'title="' . $badge->result[0]->entityId . '" '
                          . 'data-assertion="' . htmlentities(json_encode($data_assertion)) . '" />';
                }
            }
        }
        else if (isset($json->badges) && is_array($json->badges)) {

            foreach ($json->badges as $badge) {
                $b = $badge->assertion->badge;

                // TODO: Simple check for unique badges, improve me!
                if (array_key_exists($b->name, $existing) && strcmp($existing[$b->name], $b->description) === 0) {
                    continue;
                }

                if (self::assertion_has_expired($badge->assertion)) {
                    continue;
                }

                $html .= '<img tabindex="0" id="' . (preg_replace('/\:/', '_', $group)) . '" '
                            . 'src="' . $b->image . '" '
                            . 'title="' . $b->name . '" '
                            . 'data-assertion="' . htmlentities(json_encode($badge->assertion)) . '" />';

                $existing[$b->name] = $b->description;
            }
        }

        // Caching badge info into database for better performance
        if ($fromcache) {
            ensure_record_exists('blocktype_openbadgedisplayer_data',
                (object) array(
                    'host' => $host,
                    'uid' => $uid,
                    'badgegroupid' => $badgegroupid,
                ),
                (object) array(
                    'host' => $host,
                    'uid' => $uid,
                    'badgegroupid' => $badgegroupid,
                    'html' => $html,
                    'lastupdate' => db_format_timestamp(time())
                )
            );
        }

        return $html;
    }

    private static function get_badges_html($groups) {
        $html = '';

        foreach ($groups as $group) {
            $html .= self::get_badge_html($group);
        }

        if (empty($html)) {
            $html = get_string('nobadgegroups', 'blocktype.openbadgedisplayer');
        }
        return $html;
    }

    private static function assertion_has_expired($assertion) {
        if (!isset($assertion->expires)) {
            return false;
        }

        // Unix timestamp
        if (preg_match('/^[0-9]+$/', $assertion->expires)) {
            return ($assertion->expires * 1000) < time();
        }

        // Formatted date
        return strtotime($assertion->expires) < time();
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        $owner = $instance->get_view()->get('owner');
        $sources = self::get_backpack_source();
        if ($sources === false) {
            $fields = array(
                'message' => array(
                    'type' => 'html',
                    'class' => '',
                    'value' => '<div class="alert alert-warning" role="alert">' . get_string('missingbadgesources', 'blocktype.openbadgedisplayer') . '</div>'
                ),
            );
            return $fields;
        }

        $configdata = $instance->get('configdata');
        $addresses = array();
        if ($owner) {
            $addresses = get_column('artefact_internal_profile_email', 'email', 'owner', $USER->id, 'verified', 1);
        }
        $current_values = array();

        if (isset($configdata['badgegroup'])) {
            $current_values = $configdata['badgegroup'];

            // Support the legacy format (a string, not an array).
            if (is_string($current_values)) {
                $current_values = array($current_values);
            }

            foreach ($current_values as &$current_value) {
                if (substr_count($current_value, ':') == 1) {
                    // Legacy value, prepend host
                    $current_value = 'backpack:' . $current_value;
                }
            }
        }

        $sourcelinks = array();

        foreach ($sources as $source => $url) {
            if (!empty($url)) {
                $title = get_string('title_' . $source, 'blocktype.openbadgedisplayer');
                // the url for the user should not be the api url
                if ($url == 'https://api.badgr.io/') {
                    $url = 'https://badgr.com';
                }
                $sourcelinks[] = '<a href="' . $url . '">' . $title . '</a>';
            }
        }
        $fields['message'] = array(
            'type' => 'html',
            'class' => '',
            'value' => '<p class="message">'. get_string('confighelp', 'blocktype.openbadgedisplayer', implode(', ', $sourcelinks)) .'</p>'
        );
        if ($owner) {
            $fields['badgegroups'] = array(
                'type' => 'container',
                'class' => '',
                'elements' => array(
                    'loadinginfo' => array(
                        'type' => 'html',
                        'class' => '',
                        'value' => '<p class="loading-box alert alert-info">'. get_string('fetchingbadges', 'blocktype.openbadgedisplayer') .'</p>' .
                                    '<div></div>',
                    ),
                    'hosts' => array(
                        'type' => 'hidden',
                        'value' => json_encode(array_keys($sources)),
                    ),
                    'emails' => array(
                        'type' => 'hidden',
                        'value' => json_encode($addresses),
                    ),
                    'selectedbadgegroups' => array(
                        'type' => 'hidden',
                        'value' => json_encode($current_values),
                    ),
                )
            );
        }
        else {
            $fields['blocktemplatehtml'] = array(
                'type' => 'html',
                'value' => get_string('blockinstanceconfigownerchange', 'mahara'),
            );
            $fields['blocktemplate'] = array(
                'type'    => 'hidden',
                'value'   => 1,
            );
        }
        $fields['tags'] = array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescblock'),
            'defaultvalue' => $instance->get('tags'),
            'help'         => false,
        );

        return $fields;
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array(
            'js/configform.js',
        );
    }

    private static function get_form_fields($host, $addresses) {
        if ( ! $host) {
            return array();
        }

        $backpackid = array();
        foreach ($addresses AS $email) {
            $backpackid[] = self::get_backpack_id($host, $email);
        }
        $backpackid = array_filter($backpackid);

        $opt = array();
        foreach ($backpackid AS $uid) {
            $opt += self::get_group_opt($host, $uid);
        }

        return $opt;

    }


    public static function get_backpack_id($host, $email) {
        static $backpackids = array();
        $backpack_url = self::get_backpack_url($host);

        if (isset($backpackids[$host][$email])) {
            return $backpackids[$host][$email];
        }

        if ($backpack_url !== false) {
            if ($backpack_url == 'https://api.badgr.io/') {
                $userid = get_field('artefact_internal_profile_email', 'owner', 'email', $email);
                $token = get_field('usr_account_preference', 'value', 'field', 'badgr_token', 'usr', $userid);
                if ($token) {
                    $res = mahara_http_request(
                        array(
                            CURLOPT_URL        => $backpack_url . 'v2/users/self',
                            CURLOPT_HTTPHEADER => array('Authorization: Token ' . $token),
                        )
                    );
                    $res = json_decode($res->data);
                    if (isset($res->status) && $res->status->success) {
                        $backpackids[$host][$email] = $token;
                        return $token;
                    }
                }
            }
            else {
                $res = mahara_http_request(
                    array(
                        CURLOPT_URL        => $backpack_url . 'displayer/convert/email',
                        CURLOPT_POST       => 1,
                        CURLOPT_POSTFIELDS => 'email=' . urlencode($email)
                    )
                );
                $res = json_decode($res->data);
                if (isset($res->userId)) {
                    $backpackids[$host][$email] = $res->userId;
                    return $res->userId;
                }
            }
        }
        return null;
    }

    /**
     * Returns all backpack IDs of current logged-in user
     *
     * @return array of backpack IDs:
     *      array(
     *          <host> => array (
     *              <email> => <backpackid>
     *          )
     *      )
     */
    public static function get_user_backpack_ids() {
        global $USER;

        if (!$USER->is_logged_in()) {
            return array();
        }

        $sources = self::get_backpack_source();
        $addresses = get_column('artefact_internal_profile_email', 'email', 'owner', $USER->get('id'), 'verified', 1);
        $userbackpackids = array();

        if (!empty($sources) && !empty($addresses)) {
            foreach ($sources as $h => $url) {
                $userbackpackids[$h] = array();
                foreach ($addresses as $e) {
                    $userbackpackids[$h][$e] = self::get_backpack_id($h, $e);
                }
            }
        }

        return $userbackpackids;
    }

    /**
     * Return name of badge groups for a given host and backpackid
     * @param $host
     * @param $uid backpack ID attached to an email on the host
     * @param $usedbcache if true, the badge groups will be fetched from database first
     * @return array
     *      <badgegroupid> => <badgegroupname>
     * )
     */
    public static function get_badgegroupnames($host, $uid, $usedbcache=false) {
        static $badgegroupnames = array();

        if (!isset($host) || !isset($uid)) {
            return array();
        }

        if (isset($badgegroupnames[$host][$uid])) {
            return $badgegroupnames[$host][$uid];
        }

        // Get badge group names using uid (backpackid) from database
        if ($usedbcache && $badgegroups = get_records_select_array('blocktype_openbadgedisplayer_data',
            'host = ? AND uid = ? AND lastupdate > ?', array($host, $uid, db_format_timestamp(strtotime('-1 day'))),
            '', 'badgegroupid, name')) {
            foreach ($badgegroups as $badgegroup) {
                $badgegroupnames[$host][$uid][$badgegroup->badgegroupid] = $badgegroup->name;
            }
            return $badgegroupnames[$host][$uid];
        }

        $badgegroupnames[$host][$uid] = array();
        $backpack_url = self::get_backpack_url($host);
        if ($backpack_url == 'https://api.badgr.io/') {
            $res = mahara_http_request(array(CURLOPT_URL => $backpack_url . "v2/backpack/collections",
                                             CURLOPT_HTTPHEADER => array('Authorization: Token ' . $uid,
                                                                         'accept: application/json')));
        }
        else {
            $res = mahara_http_request(array(CURLOPT_URL => $backpack_url . "displayer/{$uid}/groups.json"));
        }
        $res = json_decode($res->data);

        if (!empty($res->groups)) {
            foreach ($res->groups AS $g) {
                if ($g->name == 'Public badges' && $g->groupId == 0) {
                    $name = get_string('obppublicbadges', 'blocktype.openbadgedisplayer');
                }
                else {
                    $name = hsc($g->name);
                }

                $name .= ' (' . get_string('nbadges', 'blocktype.openbadgedisplayer', $g->badges) . ')';
                $badgegroupnames[$host][$uid][$g->groupId] = $name;

                // Caching badge info into database for better performance
                ensure_record_exists('blocktype_openbadgedisplayer_data',
                    (object) array(
                        'host' => $host,
                        'uid' => $uid,
                        'badgegroupid' => $g->groupId,
                    ),
                    (object) array(
                        'host' => $host,
                        'uid' => $uid,
                        'badgegroupid' => $g->groupId,
                        'name' => $name,
                        'lastupdate' => db_format_timestamp(time())
                    )
                );
            }
        }
        else if (isset($res->status) && isset($res->status->success) && $res->status->success) {
            foreach ($res->result as $g) {
                if (!$g->published) {
                    continue;
                }
                $name = hsc($g->name);

                $name .= ' (' . get_string('nbadges', 'blocktype.openbadgedisplayer', count($g->assertions)) . ')';
                $badgegroupnames[$host][$uid][$g->entityId] = $name;
                // Caching badge info into database for better performance
                ensure_record_exists('blocktype_openbadgedisplayer_data',
                    (object) array(
                        'host' => $host,
                        'uid' => $uid,
                        'badgegroupid' => $g->entityId,
                    ),
                    (object) array(
                        'host' => $host,
                        'uid' => $uid,
                        'badgegroupid' => $g->entityId,
                        'name' => $name,
                        'lastupdate' => db_format_timestamp(time())
                    )
                );
            }
        }
        return $badgegroupnames[$host][$uid];
    }

    private static function get_group_opt($host, $uid) {
        $opt = array();
        $backpack_url = self::get_backpack_url($host);
        $res = mahara_http_request(array(CURLOPT_URL => $backpack_url . "displayer/{$uid}/groups.json"));
        $res = json_decode($res->data);

        if (!empty($res->groups)) {
            foreach ($res->groups AS $g) {
                if ($g->name == 'Public badges' && $g->groupId == 0) {
                    $name = get_string('obppublicbadges', 'blocktype.openbadgedisplayer');
                }
                else {
                    $name = hsc($g->name);
                }

                $name .= ' (' . get_string('nbadges', 'blocktype.openbadgedisplayer', $g->badges) . ')';
                $cb_id = $host . ':' . $uid . ':' . $g->groupId;
                $cb_name = self::_sanitize_name($cb_id);
                $opt[$cb_name] = array(
                    'type' => 'checkbox',
                    'title' => $name,
                    'value' => $cb_id
                );
            }
        }
        return $opt;
    }

    public static function get_backpack_url($host) {
        $sources = self::get_backpack_source();

        return isset($sources[$host]) ? $sources[$host] : false;
    }

    public static function _sanitize_name($name) {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    }

    public static function instance_config_save($values) {
        unset($values['message']);
        // Support old save format.
        $sources = array_keys(self::get_backpack_source());
        $values['badgegroup'] = array();
        $validbackpackids = self::get_user_backpack_ids();

        foreach ($sources as $source) {
            if (isset($values[$source])) {
                $values['badgegroup'] = array_merge($values['badgegroup'], $values[$source]);
                unset($values[$source]);
            }
            else if (param_exists($source)) {
                $values['badgegroup'] = array_merge($values['badgegroup'], param_variable($source));
            }
        }
        // check that what has been entered is allowed
        if (!empty($values['badgegroup'])) {
            foreach ($values['badgegroup'] as $key => $badgegroup) {
                list($host, $uid, $group) = explode(':', $badgegroup);
                if (!isset($uid) || !in_array($uid, array_values($validbackpackids[$host]))) {
                    unset($values['badgegroup'][$key]);
                }
            }
        }
        return $values;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function allowed_in_view(View $view) {
        if ($view->get('type') == 'portfolio') {
            return true;
        }
        return $view->get('owner') != null;
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        return array('js/showdetails.js');
    }

    public static function should_ajaxify() {
        return true;
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        if ($view->get('owner') !== null && !empty($configdata['blocktemplate'])) {
            unset($configdata['blocktemplatehtml']);
            unset($configdata['blocktemplate']);
        }
        return $configdata;
    }
}
