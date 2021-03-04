<?php
/**
 * @package    mahara
 * @subpackage test/generator
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2012, Petr Skoda {@link http://skodak.org}
 *
 */

require_once(get_config('libroot') . 'institution.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'view.php');

// constants
define("ATTACHMENTS", "attachments");

/**
 * Data generator class for unit tests and other tools like behat that need to create fake test sites.
 *
 */
use Behat\Behat\Exception\UndefinedException as UndefinedException;

class TestingDataGenerator {

    protected $usercounter = 0;
    protected $groupcount = 0;
    protected $institutioncount = 0;
    protected $tagcount = 0;

    /** @var array list to track location to know where to create block on each view */
    public static $viewcolcounts = array();

    /** @var array list of plugin generators */
    protected $generators = array();

    /** @var array lis of common last names */
    public $lastnames = array(
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'García', 'Rodríguez', 'Wilson',
            'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Meyer', 'Weber', 'Schulz', 'Wagner', 'Becker', 'Hoffmann',
            'Novák', 'Svoboda', 'Novotný', 'Dvořák', 'Černý', 'Procházková', 'Kučerová', 'Veselá', 'Horáková', 'Němcová',
            'Смирнов', 'Иванов', 'Кузнецов', 'Соколов', 'Попов', 'Лебедева', 'Козлова', 'Новикова', 'Морозова', 'Петрова',
            '王', '李', '张', '刘', '陈', '楊', '黃', '趙', '吳', '周',
            '佐藤', '鈴木', '高橋', '田中', '渡辺', '伊藤', '山本', '中村', '小林', '斎藤',
    );

    /** @var array lis of common first names */
    public $firstnames = array(
            'Jacob', 'Ethan', 'Michael', 'Jayden', 'William', 'Isabella', 'Sophia', 'Emma', 'Olivia', 'Ava',
            'Lukas', 'Leon', 'Luca', 'Timm', 'Paul', 'Leonie', 'Leah', 'Lena', 'Hanna', 'Laura',
            'Jakub', 'Jan', 'Tomáš', 'Lukáš', 'Matěj', 'Tereza', 'Eliška', 'Anna', 'Adéla', 'Karolína',
            'Даниил', 'Максим', 'Артем', 'Иван', 'Александр', 'София', 'Анастасия', 'Дарья', 'Мария', 'Полина',
            '伟', '伟', '芳', '伟', '秀英', '秀英', '娜', '秀英', '伟', '敏',
            '翔', '大翔', '拓海', '翔太', '颯太', '陽菜', 'さくら', '美咲', '葵', '美羽',
    );

    public $loremipsum = <<<EOD
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nulla non arcu lacinia neque faucibus fringilla. Vivamus porttitor turpis ac leo. Integer in sapien. Nullam eget nisl. Aliquam erat volutpat. Cras elementum. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Integer malesuada. Nullam lectus justo, vulputate eget mollis sed, tempor sed magna. Mauris elementum mauris vitae tortor. Aliquam erat volutpat.
Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Pellentesque ipsum. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Aliquam ante. Proin in tellus sit amet nibh dignissim sagittis. Vivamus porttitor turpis ac leo. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. In sem justo, commodo ut, suscipit at, pharetra vitae, orci. Aliquam erat volutpat. Nulla est.
Vivamus luctus egestas leo. Aenean fermentum risus id tortor. Mauris dictum facilisis augue. Aliquam erat volutpat. Aliquam ornare wisi eu metus. Aliquam id dolor. Duis condimentum augue id magna semper rutrum. Donec iaculis gravida nulla. Pellentesque ipsum. Etiam dictum tincidunt diam. Quisque tincidunt scelerisque libero. Etiam egestas wisi a erat.
Integer lacinia. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Mauris tincidunt sem sed arcu. Nullam feugiat, turpis at pulvinar vulputate, erat libero tristique tellus, nec bibendum odio risus sit amet ante. Aliquam id dolor. Maecenas sollicitudin. Et harum quidem rerum facilis est et expedita distinctio. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Nullam dapibus fermentum ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Pellentesque sapien. Duis risus. Mauris elementum mauris vitae tortor. Suspendisse nisl. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.
In laoreet, magna id viverra tincidunt, sem odio bibendum justo, vel imperdiet sapien wisi sed libero. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Maecenas lorem. Etiam posuere lacus quis dolor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Curabitur ligula sapien, pulvinar a vestibulum quis, facilisis vel sapien. Nam sed tellus id magna elementum tincidunt. Suspendisse nisl. Vivamus luctus egestas leo. Nulla non arcu lacinia neque faucibus fringilla. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Etiam dictum tincidunt diam. Etiam commodo dui eget wisi. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Duis ante orci, molestie vitae vehicula venenatis, tincidunt ac pede. Pellentesque sapien.
EOD;

    /**
     * To be called from data reset code only,
    * do not use in tests.
    * @return void
    */
    public function reset() {
        $this->usercounter = 0;
        $this->$groupcount = 0;
        $this->$institutioncount = 0;
        self::$viewcolcounts = array();

        foreach ($this->generators as $generator) {
            $generator->reset();
        }
    }

    /**
     * Return generator for given plugin.
     * @param string $plugintype the plugin type, e.g. 'artefact' or 'blocktype'.
     * @param string $pluginname the plugin name, e.g. 'blog' or 'file'.
     * @return an instance of a plugin generator extending from CoreGenerator.
     */
    public function get_plugin_generator($plugintype, $pluginname) {
        $pluginfullname = "{$plugintype}.{$pluginname}";
        if (isset($this->generators[$pluginfullname])) {
            return $this->generators[$pluginfullname];
        }
        safe_require($plugintype, $pluginname, 'tests/generator/lib.php');

        $classname =  generate_generator_class_name($plugintype, $pluginname);

        if (!class_exists($classname)) {
            throw new UndefinedException("The plugin $pluginfullname does not support " .
                            "data generators yet. Class {$classname} not found.");
        }

        $this->generators[$pluginfullname] = new $classname($this);
        return $this->generators[$pluginfullname];

    }

    /**
     * Gets the user id from it's username.
     * @param string $username
     * @return int the user id
     *     = false if not exists
     */
    protected function get_user_id($username) {
        if (($res = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($username)))))
            && count($res) === 1) {
            return $res[0]->id;
        }
        return false;
    }

    /**
     * Gets the group id from it's name.
     * @param string $groupname
     * @return int the group id
     *     = false if not exists
     */
    protected function get_group_id($groupname) {
        if (($res = get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower(trim($groupname)))))
            && count($res) === 1) {
            return $res[0]->id;
        }
        return false;
    }

    /**
     * Gets the institution id from it's name.
     * @param string $instname
     * @return int the institution id
     *     = false if not exists
     */
    protected function get_institution_id($instname) {
        if (($res = get_records_sql_array('SELECT id FROM {institution} WHERE name = ?', array($instname)))
            && count($res) === 1) {
            return $res[0]->id;
        }
        return false;
    }

    /**
     * Gets the view id from it's title.
     * @param string $viewtitle
     * @return int the view id
     *     = false if not exists
     */
    protected function get_view_id($viewtitle) {
        if ($res = get_record('view', 'title', $viewtitle)) {
            return $res->id;
        }
        return false;
    }

    /**
     * Gets the view id from it's title and owner.
     * @param string $viewtitle
     * @param int $ownerid
     * @return int the view id
     *     = false if not exists
     */
    protected function get_view_id_by_owner($viewtitle, $ownerid) {
        if ($res = get_record('view', 'title', $viewtitle, 'owner', $ownerid)) {
            return $res->id;
        }
        return false;
    }

    /**
     * Gets the id of one site administrator.
     * @return int the admin id
     *     = false if not exists
     */
    protected function get_first_site_admin_id() {
        if ($admins = get_records_sql_array('
            SELECT u.id
            FROM {usr} u
            WHERE u.admin = 1 AND u.active = 1', array())) {
            return $admins[0]->id;
        }
        return false;
    }

    /**
     * Gets the id of one administrator of the institution given by name.
     * @param string $instname
     * @return int the admin id
     *     = false if not exists
     */
    protected function get_first_institution_admin_id($instname) {
        if ($admins = get_records_sql_array('
            SELECT u.id
            FROM {usr} u
                INNER JOIN {usr_institution} ui ON ui.usr = u.id
            WHERE ui.institution = ?
                AND ui.admin = 1
                AND u.active = 1', array($instname))) {
            return $admins[0]->id;
        }
        return false;
    }

    /**
     * Gets the id of one administrator of the group given by ID.
     * @param int $groupid
     * @return int the group admin id
     *     = false if not exists
     */
    protected function get_first_group_admin_id($groupid) {
        if ($admins = get_records_sql_array('
            SELECT u.id
            FROM {usr} u
                INNER JOIN {group_member} gm ON gm.member = u.id
            WHERE  gm.group = ?
                AND gm.role = ?
                AND u.active = 1', array($groupid, 'admin'))) {
            return $admins[0]->id;
        }
        return false;
    }

    /**
    * Gets the username from given userid
    * @param int $userid
    * @return string $username
    */
    public static function get_user_username($userid) {
      if (!$username = get_field('usr', 'username', 'id', $userid)) {
        throw new SystemException("No such user with id $userid");
      }
      return $username;
    }

    /**
     * Gets the media type of an file
     *
     * @param string $filename
     * @return string media type
     */
    public static function get_mimetype($filename) {
        $path = get_mahararoot_dir() . '/test/behat/upload_files/' . $filename;
        $mimetype = file_mime_type($path);
        list($media, $ext) = explode('/', $mimetype);
        if ($media == 'application') {
            $mediatype = 'archive';
        }
        else if ($media == 'text') {
            $mediatype = 'attachment';
        }
        else {
            $mediatype = $media;
        }

        return $mediatype;
    }

    /**
    * Sort out the list of tags for to be saved for block tags in the config array
    * @param string of tags(s) in a comma separated string
    * @return array $list of tag(s)
    */
    public static function sort_tags($list) {
        $tagsarray = array();
        $tags = explode(',', $list);

        foreach ($tags as $tag) {
           $tag = trim(strtolower($tag));
           $tagsarray[] = $tag;
        }
        return $tagsarray;
    }

    /**
     * Proesses attachment files
     *
     * creates the artefact and links it to the user
     *
     * @param string $filename name of the file attachment
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @param int $parentid of an artefact ... such as when a file is in a folder
     * @return int $artefactid of the newly created artefact
     */
    public static function process_attachment($filename, $ownertype, $ownerid, $parentid=null) {
        $mediatype = self::get_mimetype($filename);
        // we need to find the id of the item we are trying to attach and save it as artefactid
        if (!isset($parentid)) {
            $dbownertype = $ownertype == 'user' ? 'owner' : $ownertype;
            $artefactid = get_field('artefact', 'id', 'title', $filename, $dbownertype, $ownerid);
        }
        else {
            $artefactid = get_field('artefact', 'id', 'title', $filename, 'parent', $parentid);
        }
        if (!$artefactid) {
            $artefactid = self::create_artefact($filename, $ownertype, $ownerid, $mediatype, $parentid);
            self::file_creation($artefactid, $filename, $ownertype, $ownerid);
        }
        return $artefactid;
    }

    /**
     * Create a test user
     * @param array $record
     * @throws SystemException if creating failed
     * @return int new user id
     */
    public function create_user($record) {
        // Data validation
        // Set default auth method for a new user is 'internal' for 'No institution' if not set
        if (empty($record['institution']) || empty($record['authname'])) {
            $record['institution'] = 'mahara';
            $record['authname'] = 'internal';
        }
        if (!$auth = get_record('auth_instance', 'institution', $record['institution'], 'authname', $record['authname'], 'active', 1)) {
            throw new SystemException("The authentication method authname " . $record['authname'] . " for institution '" . $record['institution'] . "' does not exist.");
        }
        $record['authinstance'] = $auth->id;
        // Don't exceed max user accounts for the institution
        $institution = new Institution($record['institution']);
        if ($institution->isFull()) {
            throw new SystemException("Can not add new users to the institution '" . $record['institution'] . "' as it is full.");
        }

        $record['firstname'] = sanitize_firstname($record['firstname']);
        $record['lastname']  = sanitize_lastname($record['lastname']);
        $record['email']     = sanitize_email($record['email']);

        $authobj = AuthFactory::create($auth->id);
        if (method_exists($authobj, 'is_username_valid_admin') && !$authobj->is_username_valid_admin($record['username'])) {
            throw new SystemException("New username'" . $record['username'] . "' is not valid.");
        }
        if (method_exists($authobj, 'is_username_valid') && !$authobj->is_username_valid($record['username'])) {
            throw new SystemException("New username'" . $record['username'] . "' is not valid.");
        }
        if (record_exists_select('usr', 'LOWER(username) = ?', array(strtolower($record['username'])))) {
            throw new ErrorException("The username'" . $record['username'] . "' has been taken.");
        }
        if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($record['password'])) {
            throw new ErrorException("The password'" . $record['password'] . "' is not valid.");
        }
        if (record_exists('usr', 'email', $record['email'])
                        || record_exists('artefact_internal_profile_email', 'email', $record['email'])) {
            throw new ErrorException("The email'" . $record['email'] . "' has been taken.");
        }

        // Create new user
        db_begin();
        raise_time_limit(180);

        $user = (object)array(
                'authinstance'   => $record['authinstance'],
                'username'       => $record['username'],
                'firstname'      => $record['firstname'],
                'lastname'       => $record['lastname'],
                'email'          => $record['email'],
                'password'       => $record['password'],
                'passwordchange' => 0,
        );
        if ($record['institution'] == 'mahara') {
            if ($record['role'] == 'admin') {
                $user->admin = 1;
            }
            else if ($record['role'] == 'staff') {
                $user->staff = 1;
            }
        }

        $remoteauth = $record['authname'] != 'internal';
        if (!isset($record['remoteusername'])) {
            $record['remoteusername'] = null;
        }

        $valid_profile_fields = array('studentid', 'preferredname', 'town', 'country', 'occupation');
        $profiles = array();
        foreach ($valid_profile_fields as $field) {
            if (isset($record[$field]) && !empty($record[$field])) {
                if ($field == 'country') {
                    $countries = getoptions_country();
                    $validcountry = false;
                    if (array_key_exists($record[$field], $countries)) {
                        $validcountry = $record[$field];
                    }
                    else if ($key = array_search($record[$field], $countries)) {
                        $validcountry = $key;
                    }
                    else {
                        throw new SystemException("Invalid profile country name '" . $record['country'] . "'");
                    }
                    $record[$field] = $validcountry;
                }
                $profiles[$field] = $record[$field];
            }
        }
        $user->id = create_user($user, $profiles, $institution, $remoteauth, $record['remoteusername'], $record);

        if (isset($user->admin) && $user->admin) {
            require_once('activity.php');
            activity_add_admin_defaults(array($user->id));
        }
        // Use the institution's privacy option if exists
        $instprivacy = get_field('site_content_version', 'id', 'type', 'privacy', 'institution', $record['institution']);
        $siteprivacy = get_field('site_content_version', 'id', 'type', 'privacy', 'institution', 'mahara');

        // Accept the user privacy agreement
        $sitecontentid = $instprivacy ? $instprivacy : $siteprivacy;
        $agreed = !empty($record['agreement']) ? (bool)$record['agreement'] : 1; // accept by default
        save_user_reply_to_agreement($user->id, $sitecontentid, $agreed);

        if ($record['institution'] != 'mahara') {
            if ($record['role'] == 'admin') {
                set_field('usr_institution', 'admin', 1, 'usr', $user->id, 'institution', $record['institution']);
            }
            else if ($record['role'] == 'staff') {
                set_field('usr_institution', 'staff', 1, 'usr', $user->id, 'institution', $record['institution']);
            }
        }

        db_commit();
        $this->usercounter++;
        return $user->id;
    }

    /**
     * Create a test group
     * @param array $record
     * @throws ErrorException if creating failed
     * @return int new group id
     */
    public function create_group($record) {
        // Data validation
        $record['name'] = trim($record['name']);
        if ($ids = get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower($record['name'])))) {
            if (count($ids) > 1 || $ids[0]->id != $group_data->id) {
                throw new SystemException("Invalid group name '" . $record['name'] . "'. " . get_string('groupalreadyexists', 'group'));
            }
        }
        $record['owner'] = trim($record['owner']);
        $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower($record['owner'])));
        if (!$ids || count($ids) > 1) {
            throw new SystemException("Invalid group owner '" . $record['owner'] . "'. The username does not exist or duplicated");
        }
        $members = array($ids[0]->id => 'admin');
        if (!empty($record['members'])) {
            foreach (explode(',', $record['members']) as $membername) {
                $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($membername))));
                if (!$ids || count($ids) > 1) {
                    throw new SystemException("Invalid group member '" . $membername . "'. The username does not exist or duplicated");
                }
                $members[$ids[0]->id] = 'member';
            }
        }
        if (!empty($record['staff']) && !empty($record['grouptype'])) {
            foreach (explode(',', $record['staff']) as $membername) {
                $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($membername))));
                if (!$ids || count($ids) > 1) {
                    throw new SystemException("Invalid group staff '" . $membername . "'. The username does not exist or duplicated");
                }
                if ($record['grouptype'] == 'course') {
                    $members[$ids[0]->id] = 'tutor';
                }
                else {
                    $members[$ids[0]->id] = 'admin';
                }
            }
        }
        if (!empty($record['admins'])) {
            foreach (explode(',', $record['admins']) as $membername) {
                $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($membername))));
                if (!$ids || count($ids) > 1) {
                    throw new SystemException("Invalid group admin '" . $membername . "'. The username does not exist or duplicated");
                }
                $members[$ids[0]->id] = 'admin';
            }
        }
        $availablegrouptypes = group_get_grouptypes();
        if (!in_array($record['grouptype'], $availablegrouptypes)) {
            throw new SystemException("Invalid grouptype '" . $record['grouptype'] . "'. This grouptype does not exist.\n"
                            . "The available grouptypes are " . join(', ', $availablegrouptypes));
        }
        $availablegroupeditroles = array_keys(group_get_editroles_options());
        if (!in_array($record['editroles'], $availablegroupeditroles)) {
            throw new SystemException("Invalid group editroles '" . $record['editroles'] . "'. This edit role does not exist.\n"
                            . "The available group editroles are " . join(', ', $availablegroupeditroles));
        }
        if (!empty($record['open'])) {
            if (!empty($record['controlled'])) {
                throw new SystemException('Invalid group membership setting. ' . get_string('membershipopencontrolled', 'group'));
            }
            if (!empty($record['request'])) {
                throw new SystemException('Invalid group membership setting. ' . get_string('membershipopenrequest', 'group'));
            }
        }
        if (!empty($record['invitefriends']) && !empty($record['suggestfriends'])) {
            throw new SystemException('Invalid friend invitation setting. ' . get_string('suggestinvitefriends', 'group'));
        }
        if (!empty($record['suggestfriends']) && empty($record['open']) && empty($record['request'])) {
            throw new SystemException('Invalid friend invitation setting. ' . get_string('suggestfriendsrequesterror', 'group'));
        }
        if (!empty($record['editwindowstart']) && !empty($record['editwindowend']) && ($record['editwindowstart'] >= $record['editwindowend'])) {
            throw new SystemException('Invalid group editability setting. ' . get_string('editwindowendbeforestart', 'group'));
        }
        if (!empty($record['institution'])) {
            if (!get_field('institution', 'id', 'name', $record['institution'])) {
                throw new SystemException('Invalid institution for group - Institution with short name "' . $record['institution'] . '" does not exist');
            }
        }
        $group_data = array(
                'id'             => null,
                'name'           => $record['name'],
                'description'    => isset($record['description']) ? $record['description'] : null,
                'grouptype'      => $record['grouptype'],
                'open'           => isset($record['open']) ? $record['open'] : 1,
                'controlled'     => isset($record['controlled']) ? $record['controlled'] : 0,
                'request'        => isset($record['request']) ? $record['request'] : 0,
                'invitefriends'  => isset($record['invitefriends']) ? $record['invitefriends'] : 0,
                'suggestfriends' => isset($record['suggestfriends']) ? $record['suggestfriends'] : 0,
                'category'       => null,
                'public'         => isset($record['public']) ? $record['public'] : 0,
                'usersautoadded' => 0,
                'viewnotify'     => GROUP_ROLES_ALL,
                'submittableto'  => isset($record['submittableto']) ? $record['submittableto'] : 0,
                'allowarchives'  => isset($record['allowarchives']) ? $record['allowarchives'] : 0,
                'editroles'      => isset($record['editroles']) ? $record['editroles'] : 'all',
                'hidden'         => 0,
                'hidemembers'    => 0,
                'hidemembersfrommembers' => 0,
                'groupparticipationreports' => 0,
                'urlid'          => null,
                'editwindowstart' => isset($record['editwindowstart']) ? $record['editwindowstart'] : null,
                'editwindowend'  => isset($record['editwindowend']) ? $record['editwindowend'] : null,
                'sendnow'        => 0,
                'feedbacknotify' => GROUP_ROLES_ALL,
                'members'        => $members,
        );

        // Create a new group
        db_begin();
        $group_data['id'] = group_create($group_data);
        // Because group_create expects user to be logged in to check if they can create a group for a particular institution
        // we will make it for 'mahara' institution and then adjust it here
        if (!empty($record['institution'])) {
            set_field('group', 'institution', $record['institution'], 'id', $group_data['id']);
        }
        db_commit();

        // Attachments
        if (self::check_attachments($record)) {
            self::process_attachments_get_ids($record[ATTACHMENTS], null, $group_data['id']);
        }

        $this->groupcount++;
        return $group_data['id'];
    }

    /**
     * Create a test institution
     * @param array $record
     * @throws ErrorException if creating failed
     * @return int new institution id
     */
    public function create_institution($record) {
        // Data validation
        if (empty($record['name']) || !preg_match('/^[a-zA-Z]{1,255}$/', $record['name'])) {
            throw new SystemException("Invalid institution name '" . $record['name'] .
                         "'. The institution name is entered for system database identification only and must be a single text word without numbers or symbols.");
        }
        if (!empty($record['name']) && record_exists('institution', 'name', $record['name'])) {
            throw new SystemException("Invalid institution name '" . $record['name'] . "'. " . get_string('institutionnamealreadytaken', 'admin'));
        }

        if (get_config('licensemetadata') && !empty($record['licensemandatory']) &&
                        (isset($record['licensedefault']) && $record['licensedefault'] == '')) {
            throw new SystemException("Invalid institution license setting. " . get_string('licensedefaultmandatory', 'admin'));
        }

        if (!empty($record['lang']) && $record['lang'] != 'sitedefault' && !array_key_exists($record['lang'], get_languages())) {
            throw new SystemException("Invalid institution language setting: '" . $record['lang'] . "'. This language is not installed for the site.");
        }
        // Create a new institution
        db_begin();
        // Update the basic institution record...
        $newinstitution = new Institution();
        $newinstitution->initialise($record['name'], $record['displayname']);
        $institution = $newinstitution->name;

        $newinstitution->showonlineusers = !isset($record['showonlineusers']) ? 2 : $record['showonlineusers'];
        if (get_config('usersuniquebyusername')) {
            // Registering absolutely not allowed when this setting is on, it's a
            // security risk. See the documentation for the usersuniquebyusername
            // setting for more information
            $newinstitution->registerallowed = 0;
        }
        else {
            $newinstitution->registerallowed = !empty($record['registerallowed']) ? 1 : 0;
            $newinstitution->registerconfirm  = !empty($record['registerconfirm']) ? 1 : 0;
        }

        if (!empty($record['lang'])) {
            if ($record['lang'] == 'sitedefault') {
                $newinstitution->lang = null;
            }
            else {
                $newinstitution->lang = $record['lang'];
            }
        }

        $newinstitution->theme = (empty($record['theme']) || $record['theme'] == 'sitedefault') ? null : $record['theme'];
        $newinstitution->dropdownmenu = (!empty($record['dropdownmenu'])) ? 1 : 0;
        $newinstitution->skins = (!empty($record['skins'])) ? 1 : 0;
        $newinstitution->tags = (!empty($record['tags'])) ? 1 : 0;
        $newinstitution->style = null;

        if (get_config('licensemetadata')) {
            $newinstitution->licensemandatory = (!empty($record['licensemandatory'])) ? 1 : 0;
            $newinstitution->licensedefault = (isset($record['licensedefault'])) ? $record['licensedefault'] : '';
        }

        if (!empty($record['defaultquota'])) {
            // make sure that it is bytes
            $record['defaultquota'] = get_real_size($record['defaultquota']);
        }
        $newinstitution->defaultquota = empty($record['defaultquota']) ? get_config_plugin('artefact', 'file', 'defaultquota') : $record['defaultquota'];

        $newinstitution->defaultmembershipperiod  = !empty($record['defaultmembershipperiod']) ? intval($record['defaultmembershipperiod']) : null;
        $newinstitution->maxuseraccounts = !empty($record['maxuseraccounts']) ? intval($record['maxuseraccounts']) : null;
        $newinstitution->expiry = !empty($record['expiry']) ? db_format_timestamp($record['expiry']) : null;

        $newinstitution->allowinstitutionpublicviews  = (isset($record['allowinstitutionpublicviews']) && $record['allowinstitutionpublicviews']) ? 1 : 0;

        // Save the changes to the DB
        $newinstitution->commit();

        // Automatically create an internal authentication authinstance
        $authinstance = (object)array(
                'instancename' => 'internal',
                'priority'     => 0,
                'active'       => 1,
                'institution'  => $newinstitution->name,
                'authname'     => 'internal',
        );
        insert_record('auth_instance', $authinstance);
        if (!empty($record['authname'])) {
            $authinstance = (object)array(
                'instancename' => $record['authname'],
                'priority'     => 1,
                'active'       => 1,
                'institution'  => $newinstitution->name,
                'authname'     => $record['authname'],
            );
            insert_record('auth_instance', $authinstance);
        }
        // We need to add the default lines to the site_content table for this institution
        // We also need to set the institution to be using default static pages to begin with
        // so that using custom institution pages is an opt-in situation
        $pages = site_content_pages();
        $now = db_format_timestamp(time());
        foreach ($pages as $name) {
            $page = new stdClass();
            $page->name = $name;
            $page->ctime = $now;
            $page->mtime = $now;
            $page->mauthor = 0;
            $page->content = get_string($page->name . 'defaultcontent', 'install', get_string('staticpageconfiginstitutions', 'install', get_config('wwwroot') . 'admin/users/institutionpages.php'));
            $page->institution = $newinstitution->name;
            insert_record('site_content', $page);

            $institutionconfig = new stdClass();
            $institutionconfig->institution = $newinstitution->name;
            $institutionconfig->field = 'sitepages_' . $name;
            $institutionconfig->value = 'mahara';
            insert_record('institution_config', $institutionconfig);
        }

        if (isset($record['commentthreaded'])) {
            set_config_institution($newinstitution->name, 'commentthreaded', (bool) $record['commentthreaded']);
        }

        db_commit();
    }

    /**
     * Create an empty view
     * @param array $record
     * @throws SystemException if creating failed
     * @return int new view id
     */
    public function create_view($record) {
        switch ($record['ownertype']) {
            case 'institution':
                if (empty($record['ownername'])) {
                    $record['institution'] = 'mahara';
                    break;
                }
                if ($institutionid = $this->get_institution_id($record['ownername'])) {
                    $record['institution'] = $record['ownername'];
                    // Find one of the institution admins
                    if (!$userid = $this->get_first_institution_admin_id($record['ownername'])) {
                        // Find one of site admins
                        $userid = $this->get_first_site_admin_id();
                    }
                }
                else {
                    throw new SystemException("The institution '" . $record['ownername'] . "' does not exist.");
                }
                break;
            case 'group':
                if ($groupid = $this->get_group_id($record['ownername'])) {
                    $record['group'] = $groupid;
                    // Find one of the group admins
                    if (!$userid = $this->get_first_group_admin_id($groupid)) {
                        throw new SystemException("The group '" . $record['ownername'] . "' must have at least one administrator.");
                    }
                }
                else {
                    throw new SystemException("The group '" . $record['ownername'] . "' does not exist.");
                }
                break;
            case 'user':
            default:
                if ($ownerid = get_field('usr', 'id', 'username', $record['ownername'])) {
                    $record['owner'] = $ownerid;
                    // Find one of the site admins
                    $userid = $this->get_first_site_admin_id();
                }
                else {
                    throw new SystemException("The user '" . $record['ownername'] . "' does not exist.");
                }
                break;
        }
        if (empty($userid)) {
            $userid = $this->get_first_site_admin_id();
        }
        if (!empty($record['tags'])) {
            $record['tags'] = array_map('trim', explode(',', $record['tags']));
        }

        require_once('view.php');
        $view = View::create($record, $userid);
    }

    /**
     * Create block content for existing view
     * @param array $record data for each blocktype in each row of the testing table
     * @throws SystemException if creating failed
     * @return int new block id
     */
    public function create_block($record) {
        global $USER;
        $sql = $page = $ownerid = $view = $viewid = null;

        if (preg_match('/^Dashboard page\:/', $record['page'])) {
            list($record['page'], $ownername) = explode(":", $record['page']);
            $ownerid = get_field('usr', 'id', 'username', $ownername);
            $sql = "SELECT id FROM {view} WHERE type = 'dashboard' AND LOWER(TRIM(title)) = ? AND \"owner\" = ?";
            $page = strtolower(trim($record['page']));
            $view = trim($record['page']);
            $viewid = $this->get_view_id_by_owner($view, $ownerid);
        }
        else {
            $sql = "SELECT id FROM {view} WHERE LOWER(TRIM(title)) = ?";
            $page = strtolower(trim($record['page']));
            $view = trim($record['page']);
            $viewid = $this->get_view_id($view);
        }

        if (!isset(self::$viewcolcounts[$viewid])) {
          self::$viewcolcounts[$viewid]['x'] = 0;
          self::$viewcolcounts[$viewid]['y'] = 0;
        }

        if ($ownerid != null) {
            $ids = get_records_sql_array($sql, array($page,$ownerid));
        }
        else {
            $ids = get_records_sql_array($sql, array($page));
        }
        if (!$ids || count($ids) > 1) {
            throw new SystemException("Invalid page name '" . $record['page'] . "'. The page title does not exist, or is duplicated.");
        }
        else {
            require_once('view.php');
            $view = new View($ids[0]->id);
            if (!empty($view->get('institution'))) {
                $ownertype = 'institution';
                $ownerid = $view->get('institution');
            }
            else if (!empty($view->get('group'))) {
                $ownertype = 'group';
                $ownerid = $view->get('group');
            }
            else {
                $ownertype = 'user';
                $ownerid = $view->get('owner');
            }
        }

        $maxcols = 3;

        // We have a valid page so lets see if we can add a block to it
        $blocktype = strtolower(trim($record['type']));
        // Check that the blocktype exists and is active
        if (!get_record('blocktype_installed', 'active', 1, 'name', $blocktype)) {
            throw new SystemException("Invalid block type '" . $record['type'] . "'. The block type is either not installed or not active.");
        }
        $title = trim($record['title']);
        $functionname = 'generate_configdata_' . $record['type'];
        $classname = 'TestingDataGenerator';

        // build configdata
        $configdata = $this->setup_retractable($record['retractable']);
        $data = trim($record['data']);
        $sortedfields = $this->setup_configdata($data);

        if (is_callable($classname . '::' . $functionname)) {
            $result = call_static_method($classname, $functionname, $sortedfields, $ownertype, $ownerid, $title, $view);
            $configdata = array_merge($configdata, (array)$result);

            // make new block
            $blockinstance = self::create_new_block_instance($blocktype, $view, $viewid, $title, self::$viewcolcounts, $configdata, $maxcols);
            //taggedposts blocktype - block instance needs to pre-exist
            if ($functionname == 'generate_configdata_taggedposts') {
                PluginBlocktypeTaggedposts::instance_config_save($configdata, $blockinstance);
            }
            // setting tags to blocks
            self::set_block_tags($view, $blockinstance, $ownertype, $ownerid, $configdata);
        }
        else {
            throw new SystemException("The blocktype {$record['type']} is not supported yet.");
        }
    }

    /**
     * creates a new block instance
     *
     * @param string $blocktype
     * @param View $view object of the current view
     * @param int $viewid of the View
     * @param string $title of the block instance to be created
     * @param int $viewcolcounts the current count of 0,1,2,.. column and 0,1,2,.. row to create next block instance
     * @param array $configdata holding data for new block instance
     * @param int $maxcols ~ 3 (1,2,3,4,6)
     * @param View $otherview for situations such as navigation where a block is related to another view
     */
    public static function create_new_block_instance($blocktype, $view, $viewid, $title, $viewcolcounts, $configdata, $maxcols, $otherview = null) {
        safe_require('blocktype', $blocktype);

        if (!in_array($maxcols, array(1,2,3,4,6))) $maxcols = 3;
        $blockwidth = 12/$maxcols;

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $blocktype,
                'title'      => $title,
                'view'       => $viewid,
            )
        );


        if (!isset(self::$viewcolcounts[$viewid])) {
            self::$viewcolcounts[$viewid]['x'] = 0;
            self::$viewcolcounts[$viewid]['y'] = 0;
        }
        $bi->set('configdata', $configdata);
        $bi->set('positionx', self::$viewcolcounts[$viewid]['x'] * $blockwidth);
        $bi->set('positiony', self::$viewcolcounts[$viewid]['y'] * 3);
        $bi->set('height', 3);
        $bi->set('width', $blockwidth);
        if ($newcol = (self::$viewcolcounts[$viewid]['x']+1) % $maxcols) {
            self::$viewcolcounts[$viewid]['x'] = $newcol;
        }
        else {
            self::$viewcolcounts[$viewid]['x'] = 0;
            self::$viewcolcounts[$viewid]['y']++;
        }

        //in cases such as navigation where we want to add a block to a different view than the current.
        if ($otherview) {
          $otherview->addblockinstance($bi);
        }
        else {
            $bi->commit();
            return $bi;
        }
    }

    /**
     * Set tags for blocks from information in configdata
     *
     * the configdata array is different from normal mahara db so that the tags
     * can be stored somewhere until after the block instance has been created to be added.
     *
     * @param BlockInstance $bi
     * @param string $ownertype
     * @param int $ownerid
     * @param array $configdata
     */
    public static function set_block_tags($view, $bi, $ownertype, $ownerid, $configdata) {
        if (!array_key_exists('tags',$configdata))
            return;
        if ($view->get('group')) {
            $ownertype = 'group';
        }
        else if ($view->get('institution')) {
            $ownertype = 'institution';
        }
        else {
            $ownertype = 'user';
        }

        if ($tags = $configdata['tags']) {
            $tags = check_case_sensitive($tags, 'tag');
            delete_records('tag', 'resourcetype', 'blocktype', 'resourceid', $bi->get('id'));
            foreach ($tags as $tag) {
                // truncate the tag before insert it into the database
                $tag = substr($tag, 0, 128);
                $tag = check_if_institution_tag($tag);
                insert_record('tag',
                    (object)array(
                        'resourcetype' => 'blocktype',
                        'resourceid' => $bi->get('id'),
                        'ownertype' => $ownertype,
                        'ownerid' => $ownerid,
                        'tag' => $tag,
                        'ctime' => db_format_timestamp(time()),
                        'editedby' => $ownerid,
                    )
                );
            }
        }
    }

    /**
     * generate configdata for blocktype: blog aka journal
     * displaying the blogs that were created using the function create_blog
     * given a matching blog title
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_blog($sortedfields) {

        $configdata = array();
        foreach($sortedfields as $key => $value) {
            if ($key == 'journaltitle') {
                if (!$blogid = get_field('artefact', 'id', 'title', $value, 'artefacttype', 'blog')) {
                    throw new SystemException("A blog/journal with the name " . $value . " doesn't exist!");
                }
                $configdata['artefactid'] = $blogid;
            }
            if ($key == 'copytype') {
                $configdata[$key] = $value;
            }
            if ($key == 'count') {
                $configdata[$key ] = $value;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for blocktype: blogpost aka journalentry
     * displaying the blogposts that were created using the function create_blogpost
     * matching a given blog and entry title
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_blogpost($sortedfields) {

        $configdata = array();
        $blogpostid;
        $blogid;

        foreach($sortedfields as $key => $value) {
            if ($key == 'journaltitle') {
                if (!$blogid = get_field('artefact', 'id', 'title', $value, 'artefacttype','blog')) {
                    throw new SystemException("A blog/journal named " . $value . " doesn't exist!");
                }
            }
            if ($key == 'entrytitle') {
                if (!$blogpostid = get_field('artefact','id','title', $value, 'parent', $blogid, 'artefacttype','blogpost')) {
                    throw new SystemException(" There is no such blogpost/journalentry titled " . $value);
                }
                $configdata['artefactid'] = $blogpostid;
            }
            if ($key == 'copytype') {
                $configdata[$key]=$value;
            }
        }
        return $configdata;
    }

    /**
     * generate a comment blocktype.
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array with redundant information as there is no specific artefact connected to it.
     */
    public static function generate_configdata_comment($sortedfields) {
        return array();
    }

    /**
     * generate configdata and instance for blocktype: creativecommons
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_creativecommons($sortedfields) {
        $configdata = array();

        foreach ($sortedfields as $key => $value) {
            switch ($key) {
                case 'commercialuse':
                    //yes=0, no=1
                    $configdata['noncommercial'] = $value == 'yes' ? 0:1;
                    break;
                case 'license':
                    //must be 3.0 or 2.0
                    if ($value == 3.0 || $value == 2.0) {
                        $configdata['version'] = (string)($value*10);
                    }
                    else $configdata['version'] = '30';
                    break;
                case 'allowmods':
                    //yes=0, yes(with mutual sharing)=1, no=2
                    if ($value == 'yes') $configdata['noderivatives'] = '0';
                    if ($value == 'yeswithsharing') $configdata['noderivatives'] = '1';
                    if ($value == 'no') $configdata['noderivatives'] = '2';
                    break;
                default:
                    break;
            }
        }
        $configdata = PluginBlocktypeCreativecommons::instance_config_save($configdata);
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: entireresume
     *
     * doesn't work in group pages
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_entireresume($sortedfields, $ownertype, $ownerid) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'tags') {
                $tags = explode(',', $value);
                foreach ($tags as $tag) {
                   $tag = trim(strtolower($tag));
                   $configdata['tags'][] = $tag;
                }
            }
        }
    }

    /**
     * generate configdata for the blocktype: rss feeds/external feeds
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values for db table
    */
    public static function generate_configdata_externalfeed($sortedfields, $ownertype, $ownerid) {

        $configdata = array();
        $configdata['full'] = 1;
        foreach ($sortedfields as $key => $value) {
            if ($key == 'source') {
                $wheredata = array('url' => $value);
                $feeddata = PluginBlocktypeExternalfeed::parse_feed($value);
                $feeddata->content  = serialize($feeddata->content);
                $feeddata->image    = serialize($feeddata->image);
                $value = ensure_record_exists('blocktype_externalfeed_data', $wheredata, $feeddata, 'id', true);
                $configdata['feedid'] = $value;
            }
            if ($key == 'count') {
                $configdata[$key] = $value;
            }
            if ($key == 'tags') {
                $configdata['tags'] = self::sort_tags($value);

            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: external video
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_externalvideo($sortedfields) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'tags') {
                $configdata['tags'] = self::sort_tags($value);
            }
            if ($key == 'source') {
                $configdata = PluginBlocktypeExternalvideo::process_url($value);
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: filedownload
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_filedownload($sortedfields, $ownertype, $ownerid) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'attachments') {
                $fileattachments = explode(',',$value);
                foreach ($fileattachments as $file) {
                    $configdata['artefactids'][] = self::process_attachment($file, $ownertype, $ownerid);
                }
            }
        }
        return $configdata;
    }
    /**
     * generate configdata for the blocktype folder, which dealts with creating a folder artefact_type
     * as well as file artefacts and connecting them parent ids.
     * @param $data holds the config information from data column
     * @param $ownertype of user
     * @param $owenerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_folder($sortedfields, $ownertype, $ownerid) {
        $folderfiles = array();
        $configdata = array();
        $foldername = -1;

        foreach ($sortedfields as $key => $value) {
            if ($key == 'dirname') {
                $foldername = $value;
            }
            if ($key == 'attachments') {
                $files = explode(',', $value);

                foreach ($files as $file) {
                    $folderfiles[] = $file;
                }
            }
        }

        if ($foldername == -1) {
          throw new SystemException("Cannot save files, there was no foldername given!");
        }

        $folderartefactid = ArtefactTypeFolder::get_folder_id($foldername, $foldername, null, true, $ownerid);
        $configdata['artefactid'] = $folderartefactid;
        // upload each image and put into a folder
        foreach($folderfiles as $file) {
            self::process_attachment($file, $ownertype, $ownerid, $folderartefactid);
        }
        return $configdata;
    }

    /**
     * generate configdata for blocktype: gallery
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_gallery($sortedfields, $ownertype, $ownerid) {
        $configdata = array();

        //separate gallery_images, select, width, style etc.
        foreach ($sortedfields as $key => $value) {
            if ($key == 'attachments') {
                $galleryimagefiles = explode(',', $value);
                $value = array();

                foreach ($galleryimagefiles as $file) {
                    $configdata['artefactids'][] = self::process_attachment($file, $ownertype, $ownerid);
                }
            }
            if ($key == 'imagesel' || $key == 'width' || $key == 'showdesc' || $key == 'imagestyle' || $key == 'photoframe' ) {

                //imageselection options are 0,1,2 in the table
                if ($key == 'imagesel') {
                    $value -= 1;
                    $configdata['select'] = $value;
                }
                else if ($key == 'showdesc') {
                    $value = strtolower($value) == 'yes' ? 1:0;
                    $configdata['showdescription'] = $value;
                }
                else if ($key == 'imagestyle') {
                    $value -= 1;
                    $configdata['style'] = $value;
                }
                else {
                    $configdata[$key] = $value;
                }
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for blocktype: googleapps
     * embedded links include '=' symbol which can get cut up setup_configdata()
     * so I've used == to separate the key and value in the table.
     *
     * @param array $fields holding the 'googleapps' => embeddedsource
     * @return array $configdata of the processed keys and values for the db table
     */
    public static function generate_configdata_googleapps($fields) {
        $configdata = array();
        foreach ($fields as $key => $value) {
            if ($key == 'googleapp') {
              $app = PluginBlocktypeGoogleApps::make_apps_url($value);
              $configdata['appsid'] = $app['url'];
            }
            if ($key == 'height') {
                if ($value > 0) {
                    $configdata['height'] = $value;
                }
            }
            if ($key == 'tags') {
                $tags = explode(',', $value);
                foreach ($tags as $tag) {
                   $tag = trim(strtolower($tag));
                   $configdata['tags'][] = $tag;
                }
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for blocktype: html
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_html($sortedfields, $ownertype, $ownerid) {
        $configdata = array();

        foreach ($sortedfields as $key => $value) {
            if ($key == 'attachment') {
                $configdata['artefactid'] = self::process_attachment($value, $ownertype, $ownerid);
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: image
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_image($sortedfields, $ownertype, $ownerid) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'attachment') {
                $configdata = array('artefactid' => self::process_attachment($value, $ownertype, $ownerid));
            }
            if ($key == 'width' || $key == 'showdescription' || $key == 'style' ) {
                $configdata[$key] = $value;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: internalmedia aka 'embeddedmedia
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_internalmedia($sortedfields, $ownertype, $ownerid) {
        $mediatype;
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'attachment') {
                $filename = $value;
                $filenameparts = explode('.', $filename);
                $ext = end($filenameparts);

                // we need to find the id of the item we are trying to attach and save it as artefactid
                if (!$artefactid = get_field('artefact', 'id', 'title', $filename, 'owner', $ownerid)) {

                    if ($ext == 'wmv' || $ext == 'webm' || $ext == 'mov'|| $ext == 'ogv' || $ext == 'mpeg' || $ext == 'mp4' || $ext == 'flv' || $ext == 'avi' || $ext == '3gp') {
                        $artefactid = self::create_artefact($filename, $ownertype, $ownerid, 'video');
                        self::file_creation($artefactid, $filename, $ownertype, $ownerid);
                    }
                    if ($ext == 'mp3' || $ext == 'oga' || $ext == 'ogg') {
                        $artefactid = self::create_artefact($filename, $ownertype, $ownerid, 'audio');
                        self::file_creation($artefactid, $filename, $ownertype, $ownerid);
                    }
                }
                $configdata['artefactid'] = $artefactid;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: navigation and create navblocks*
     * **when copytoall is true**
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @param string $title of block to be created* (when copytoall is true)
     * @param object the current view to create block on
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_navigation($sortedfields, $ownertype, $ownerid, $title, $view) {
        $configdata = array();
        $copytoall = true;
        $collectionid;

        foreach ($sortedfields as $key => $value) {
            if ($key == 'collection') {
                $configdata[$key] = $collectionid =  get_field('collection', 'id', 'name', $value);
            }
            if ($key == 'copytoall') {
                $copytoall = $value == 'yes'? true : false;
            }
            $collectionobj = new Collection($collectionid);
            // CASE 2: the navigation block being created IS one of the view in the collection
            if ($collectionobj && $copytoall) {
                foreach ($viewids = $collectionobj->get_viewids() as $viewid) {
                    //if vid is not the exactly the same as the og nav block for this collection
                    if ($viewid !== (int)$view->get('id')) {
                        $needsblock = true;

                        //if there exists nav blocks on this view/page
                        if ($navblocks = get_records_sql_array("SELECT id FROM {block_instance} WHERE blocktype = ? AND view = ?", array('navigation', $viewid))) {
                            foreach ($navblocks as $navblock) {
                                $bi = new BlockInstance($navblock->id);
                                $navblockconfigdata = $bi->get('configdata');
                                //if there exists is a nav block on this view that already links to the intended collection
                                if (!empty($navblockconfigdata['collection']) && $navblockconfigdata['collection'] == $configdata['collection']) {
                                    $needsblock = false;
                                }
                            }
                        }
                        if ($needsblock) {
                            //need to add new navigation block
                            $otherview = new View($viewid);
                            // make new block
                            self::create_new_block_instance('navigation', $view, $viewid, $title, self::$viewcolcounts, $configdata, $maxcols = 3, $otherview);
                        }
                    }
                }
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the bloctype: open badges
     * @param array $fields holding each chunk of data between the ; in the behat data column
     * @return array
     */
    public static function generate_configdata_openbadgedisplayer($fields) {
        return array('badgegroup' => array());
    }

    /**
     * generate configdata for the bloctype: peerassessment
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_peerassessment($sortedfields) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'instructions') {
                $configdata[$key] = $value;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: pdf
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_pdf($sortedfields, $ownertype, $ownerid) {
        foreach ($sortedfields as $key => $value) {

            if ($key == 'attachment') {
                $configdata['artefactid'] = self::process_attachment($value, $ownertype, $ownerid);
            }
        }
        return $configdata;
    }

    /**
    * generate configdata for the blocktype: plans
    *
    * @param array $sortedfields holding each chunk of data between the ; in the behat data column
    * @param string $ownertype of user
    * @param string $ownerid of the user
    * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_plans($sortedfields, $ownertype, $ownerid) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'plans') {
                $plans = explode(',',$value);
                foreach ($plans as $plan) {
                    if (!$planid = get_field('artefact', 'id', 'title', $plan, 'artefacttype', 'plan', 'owner', $ownerid)) {
                        throw new SystemException("Invalid Plan '" . $plan . "'");
                    }
                    $configdata['artefactids'][] = $planid;
                }
            }
            if ($key == 'tasksdisplaycount') {
                $configdata['count'] = $value;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: profileinformation
     *
     * As well as going thorugh the general fields in the data column of the table,
     * an ArtefactTypeProfileIcon is created as there are none created in bulk.
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_profileinfo($sortedfields, $ownertype, $ownerid) {
        $configdata = array();

        foreach ($sortedfields as $key => $value) {
            if ($key == 'introtext') {
                require_once('embeddedimage.php');
                $newtext = EmbeddedImage::prepare_embedded_images($value, 'introtext', 0);
                $configdata['introtext'] = $newtext;
            }
            if ($key == 'profileicon') {
                if (!$artefactprofileiconid = get_field('artefact', 'id', 'title', $value, 'owner', $ownerid, 'artefacttype', 'profileicon')) {
                    $folderartefactid = ArtefactTypeFolder::get_folder_id(get_string('imagesdir', 'artefact.file'), get_string('imagesdirdesc', 'artefact.file'), null, true, $ownerid);
                    $artefactprofileiconid = self::create_artefact($value, $ownertype, $ownerid, 'profileicon', $folderartefactid);
                    self::file_creation($artefactprofileiconid, $value, $ownertype, $ownerid, true);

                    execute_sql("UPDATE {usr}
                        SET profileicon = $artefactprofileiconid
                        WHERE id = $ownerid");
                }
                $configdata['profileicon'] = $artefactprofileiconid;
            }
        }
        // gather the user's social profiles data
        safe_require('artefact', 'internal');
        $element_list = ArtefactTypeProfile::get_all_fields();

        foreach ($element_list as $element=>$type) {
            if ($artefactid = get_field('artefact', 'id', 'artefacttype', $element, 'owner', $ownerid)) {
                $configdata['artefactids'][] = $artefactid;
            }
            else if ($element == 'socialprofile') {
                $artefacttypes = ArtefactTypeSocialprofile::$socialnetworks;
                foreach ($artefacttypes as $type) {
                    if ($artefactid = get_field('artefact', 'id', 'artefacttype', 'socialprofile', 'owner', $ownerid, 'note', $type)) {
                        $configdata['artefactids'][] = $artefactid;
                    }
                }
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for blocktype: recentforumposts
     *
     * The recentforumposts blocktype displays forumposts for a given group.
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_recentforumposts($sortedfields, $ownertype, $ownerid) {
        $configdata = array();

        foreach ($sortedfields as $key => $value) {
            if ($key == 'groupname') {
                $groupid;
                //make sure the group exists
                if (!$groupid = get_field('group', 'id', 'name', $value)) {
                    throw new SystemException("Invalid Group '" . $value . "'");
                }
                else {
                    $configdata['groupid'] = $groupid;
                }
            }
            if ($key == 'maxposts') {
                $key = 'limit';
                $configdata[$key] = $value > 0 ? $value : 5;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: recentposts
     *
     * The recentposts blocktypes displays a list of recent posts for the given
     * journal/blog name.
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values of db table
     */
    public static function generate_configdata_recentposts($sortedfields, $ownertype, $ownerid) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'maxposts') {
                $configdata['count'] = $value > 0 ? $value : 10;
            }
            if ($key == 'journaltitle') {
                if (!$blogid = get_field('artefact', 'id', 'title', $value, 'artefacttype','blog')) {
                    throw new SystemException("A blog/journal named " . $value . " doesn't exist!");
                }
                $configdata['artefactids'][] = $blogid;
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: resumefield
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_resumefield($sortedfields, $ownertype, $ownerid) {
        foreach ($sortedfields as $key => $value) {
            if ($key == 'artefacttype') {
                if (!$artefactid = get_field('artefact', 'id', 'owner', $ownerid, 'artefacttype', $value)) {
                    throw new SystemException('The user ' . self::get_user_username($ownerid) . ' does not have a ' . $value);
                }
                else {
                  return array('artefactid' => $artefactid);
                }
            }
        }
    }

    /**
     * generate configdata for the blocktype: social profile
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @param string $ownertype of user
     * @param string $ownerid of the user
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_socialprofile($sortedfields, $ownertype, $ownerid) {
        foreach ($sortedfields as $key => $value) {
            if ($key == 'sns') {
                //split the values for multiple social profile creation
                $medialist = explode(',', $value);
                foreach($medialist as $media) {
                    $newprofile = new ArtefactTypeSocialprofile();
                    $newprofile->set('owner', $ownerid);
                    $newprofile->set('author',$ownerid);
                    $newprofile->set('title', $media);
                    $newprofile->set('description', $media);
                    $newprofile->set('note', $media);
                    $id = $newprofile->commit(); //update the contents of the artefact table only
                    $artefactid[] = $newprofile->get('id');
                }
                return $configdata = array('artefactids' => $artefactid);
            }
        }
    }

    /**
     * generate configdata for the blocktype: taggedposts
     *
     * The blocktype taggedposts displays a list of Journal Entries with the
     * given tag
     *
     * @param array $sortedfields holding each chunk of data between the ; in the behat data column
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_taggedposts($sortedfields) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'maxposts') {
                $configdata['count'] = $value > 0 ? $value : 10;
            }
            if ($key == 'tags') {
                $tags = explode(',',$value);
                $configdata['tagselect'] = $tags;
            }
            if ($key == 'showfullentries') {
                $configdata['full'] = strtolower($value) == 'no' ? 1 : 0;
            }
            if ($key == 'copytype') {
                $configdata[$key] = strtolower($value) == 'nocopy' ? $value : 'tagsonly';
            }
        }
        return $configdata;
    }

    /**
     * generate configdata for the blocktype: text
     * @param string inside data column in behat test
     * @return array $configdata of key and values for db table
     */
    public static function generate_configdata_text($sortedfields) {
        $configdata = array();
        foreach ($sortedfields as $key => $value) {
            if ($key == 'textinput') {
                $configdata['text'] = $value;
            }
            if ($key == 'tags') {
                $configdata['tags'] = self::sort_tags($value);
            }
        }
        return $configdata;
    }

    /**
    * generate configdata for the blocktype: textbox
    *
    * This function will create a textbox blockytpe(appears as note block on front-end)
    * holding an html artefact
    * NOTE:the title of a textbox block is the same as the html artefact the textbox it is associated with; not the title of a block instance
    *
    * @param array $sortedfields holding each chunk of data between the ; in the behat data column
    * @param string $ownertype of user
    * @param string $ownerid of the user
    * @param string $title of block to be created* (when copytoall is true)
    * @param object the current view to create block on
    * @return array $configdata of key and values of db table
    */
    public static function generate_configdata_textbox($sortedfields, $ownertype, $ownerid, $title, $view) {
        $configdata = array();
        $artefactdata = array();
        $bi = null;
        $notetitle = null;
        $copynote = false;
        $existingtextboxfound = false;
        $htmlartefactid = null;

        foreach ($sortedfields as $key => $value) {
            if ($key == 'notetitle') {
                $artefactdata['title'] = trim($value);
            }
            if ($key == 'text') {
                $artefactdata['description'] = trim($value);
            }
            if ($key == 'allowcomments') {
                $artefactdata['allowcomments'] = strtolower($value) == 'yes' ? 1 : 0;
            }
            if ($key == 'tags') {
                // noteblock expects tags in csv form (separated by commas)
                $artefactdata['tags'] = $value;
            }
            if ($key == 'attachments') {
                $attachmentfiles = explode(',', $value);
                foreach( $attachmentfiles as $file) {
                    $configdata['artefactids'][] = self::process_attachment($file, $ownertype, $ownerid);
                }
            }
            if ($key == 'copynote') {
                $copynote = trim($value) == 'true';
            }
            if ($key == 'existingnote') {
                $notetitle = !empty(strtolower($value)) ? $value : null;

                if (empty($notetitle)) {
                    throw new SystemException('Insufficient information. No note title given to use content from another note block');
                }

                $htmlartefactid = get_field('artefact', 'id', 'artefacttype', 'html', 'title', $notetitle);
                $htmlartefact = null;
                if (!empty($htmlartefactid)) {

                    //check artefactid reference in existing textboxes
                    if (!empty($textboxids = get_records_array('block_instance', 'blocktype', 'textbox', null, 'id'))) {
                        foreach ($textboxids as $blockid) {
                            $bi = new BlockInstance($blockid->id);
                            $configdata = $bi->get('configdata');

                            if (empty($configdata['artefactid'])) {
                                //if the textbox doesn't have a reference to an artefactid; an htmlartefact, check next textbox
                                continue;
                            }
                            // if the artefactid reference in textbox configdata matches the id of the html artefact
                            if ($htmlartefactid == $configdata['artefactid']) {
                                $existingtextboxfound = true;
                                break;
                            }
                        }
                    }
                }
                else {
                    // there is not textbox found that references the html or an html artefact with given title
                    throw new SystemException("Could not find a note with the title $notetitle");
                }
            }
        }
        //not null db requirements for artefact checks
        if (!isset($artefactdata['allowcomments'])) {
            $artefactdata['allowcomments'] = 1;
        }
        if ($existingtextboxfound && !$copynote) {
            return $configdata;
        }

        // textbox with a new title but copy data from existing note
        //creation of a new textbox without copying data from another note
        $artefact = new ArtefactTypeHtml(0, null);
        //
        if ($copynote && $existingtextboxfound) {
            $htmlartefact = $bi->get_artefact_instance($htmlartefactid);
            $artefactdata['description'] = $htmlartefact->get('description');
        }
        $artefact->set('title', $artefactdata['title']);
        $artefact->set('description', $artefactdata['description']);
        $artefact->set('allowcomments', $artefactdata['allowcomments']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->set('owner', $ownerid);
        $artefact->commit();

        if (!empty($artefactdata['tags'])) {
            $artefact->set('tags', $artefactdata['tags']);
        }

        //attachments
        $htmlartefactreference = $artefact->get('id');
        $configdata['artefactid'] = $htmlartefactreference;
        foreach($configdata['artefactids'] as $artefactid) {
            $artefact->attach($artefactid);
        }
        $configdata['artefactid'] = $artefact->get('id');
        return $configdata;
    }

    /**
    * Copies file from /test/behat/upload_files folder and places it in the dataroot folder of the site.
    * Then write contents into it given the artefact id
    * @param int $artefactid of the file artefact created in the upload_file function
    * @param string $file attachment for uploading images, pdf, etc.
    * @param int $ownertype of the user
    * @param int $ownerid of the user
    **/
    public static function file_creation($artefactid, $file, $ownertype, $ownerid, $profilepic=false) {
        // get the path of the file artefact from given artefactid
        $filedir = get_config('dataroot') . ArtefactTypeFile::get_file_directory($artefactid);

        if (!check_dir_exists($filedir, true, true)) {
            throw new SystemException("Unable to create folder $filedir");
        }
        else {
            // Write contents to a file...
            $filepath = $filedir . '/' . $artefactid;
            $path = get_mahararoot_dir() . '/test/behat/upload_files/' . $file;
            copy($path, $filepath);
            chmod($filepath, get_config('filepermissions'));

            if ($profilepic) {
              // Move the profile file into the correct place.
              $directory = get_config('dataroot') . 'artefact/file/profileicons/originals/' . ($artefactid % 256) . '/';
              if (!check_dir_exists($directory, true, true)) {
                  throw new SystemException("Unable to create folder $directory");
              }
              copy($path, $directory . $artefactid);
            }
        }
        if (!$artefactid) {
            throw new SystemException("Invalid attachment '" . $file . "'. No attachment by that name owned by " . $ownertype . " with id " . $ownerid);
        }
    }

    /**
     * tidying up and organising the table data into an array
     * @param string $data text from data columm
     * @return array $sortedfields array of $key $value pairs containing fields their entries
     */
    public function setup_configdata($data) {
        $fields = explode(';', $data);
        $sortedfields = array();
        if (!empty($fields)) {
            foreach($fields as $field) {
                if (empty($field)) break;
                list($key, $value) = explode('=', $field, 2);
                if (isset($key) && isset($value)) {
                    $key = trim(strtolower($key));
                    $value = trim($value);
                    $sortedfields[$key]=$value;
                }
                else {
                  throw new SystemException("Empty fields!");
                }
            }
        }
        return $sortedfields;
    }

    /**
     * set up configdata for retractable and retractable on load
     * @param string $setting: auto, yes, no
     * @return array $configdata of key and values for db table
     */
    public function setup_retractable($setting) {
        $configdata = array();
        $configdata['retractable'] = strtolower($setting) =='no' ? 0 : 1;
        $configdata['retractedonload'] = strtolower($setting) =='auto' ? 1 : 0;
        return $configdata;
    }

    /**
    * Create artefacts
    * @param string $file name
    * @param string $ownertype i.e. institution, group, user
    * @param int $ownerid
    * @param string $filetype of the upload file
    * @param string $foldername to upload the file into
    * @return int artefactid
    **/
    public static function create_artefact($file, $ownertype, $ownerid, $filetype, $parentfolderid=null) {
        $artefactid = null;
        $artefact = new stdClass();
        $path = get_mahararoot_dir() . '/test/behat/upload_files/' . $file;

        $ext = explode('.', $file);
        $artefact->oldextension = end($ext);

        $artefact->title = $file;
        switch ($ownertype) {
          case 'user':
              $artefact->owner = $ownerid;
              break;
          case 'institution':
              $artefact->institution = 1;
              break;
          case 'group':
              $artefact->group = 1;
              break;
          default:
              break;
        }
        // $artefact->ownertype = $ownerid;
        $artefact->author = $ownerid;
        // table artefact_file_files needs this information
        $artefact->contenthash = ArtefactTypeFile::generate_content_hash($path);
        $artefact->filetype = file_mime_type($path);

        $now = date("Y-m-d H:i:s");
        $artefact->atime = $artefact->ctime = $artefact->mtime =$now;

        $imagefilesize = filesize($path);
        $filesize = get_real_size($imagefilesize);
        $artefact->size = $filesize;

        // if file belongs inside a folder
        if ($parentfolderid) {
            $artefact->parent = $parentfolderid;
        }

        if ($filetype == 'image') {

            $imageinfo      = getimagesize($path);
            $artefact->width    = $imageinfo[0];
            $artefact->height   = $imageinfo[1];

            $artimg = new ArtefactTypeImage(0, $artefact);
            $artimg->commit();
            $artefactid = $artimg->get('id');
        }

        if ($filetype == 'attachment') {

            $artobj = new ArtefactTypeFile(0, $artefact);
            $artobj->commit();
            $artefactid = $artobj->get('id');
        }

        if ($filetype == 'archive') {
            $artobj = ArtefactTypeFile::new_file($path, $artefact);
            $artobj->commit();
            $artefactid = $artobj->get('id');
        }

        if ($filetype == 'audio') {
            $artobj = ArtefactTypeFile::new_file($path, $artefact);
            $artobj->commit();
            $artefactid = $artobj->get('id');
        }

        if ($filetype == 'profileicon') {
            $imageinfo = getimagesize($path);
            $artefact->width  = $imageinfo[0];
            $artefact->height = $imageinfo[1];

            $artefact->description = get_string('uploadedprofileicon', 'artefact.file');
            $artefact->note = $file;

            // validate the upload as done in Pieform (profileicons.php)
            if (!$imageinfo || !is_image_type($imageinfo[2])) {
                throw new SystemException(get_string('filenotimage'));
            }
            // maximum of five profile pics per user
            if (get_field('artefact', 'COUNT(*)', 'artefacttype', 'profileicon', 'owner', $ownerid) >= 5) {
                throw new SystemException(get_string('onlyfiveprofileicons', 'artefact.file'));
            }

            // by adding new pic, quota isn't exceeded
            $user = new User();
            $user->find_by_id($ownerid);
            if (!$user->quota_allowed($artefact->size)) {
                throw new SystemException(get_string('profileiconuploadexceedsquota', 'artefact.file', get_config('wwwroot')));
            }

            $profileiconartefact = new ArtefactTypeProfileIcon(0, $artefact);
            $profileiconartefact->commit();
            $artefactid = $profileiconartefact->get('id');
        }

        if ($filetype == 'video') {
            $artobj = ArtefactTypeFile::new_file($path, $artefact);
            $artobj->commit();
            $artefactid = $artobj->get('id');
        }
        if (!isset($artefactid)) {
            throw new SystemException('Unable to create artefact for '. $file . ' for filetype ' . $filetype );
        }
        return $artefactid;
    }

    /**
     * A fixture to set up collections of pages in bulk.
     * Currently it only supports adding title / description,
     * | title          | ownertype | ownername | description | pages             |
     * | collection one | user      | UserA     | desc of col |Page One,Page Two  |
     * @param unknown $record
     * @throws SystemException if creating failed
     * @return int new collection id
     */
    public function create_collection($record) {
        // Validation
        $sqljoin = $sqlwhere = null;
        switch ($record['ownertype']) {
            case 'institution':
                if (empty($record['ownername'])) {
                    $record['institution'] = 'mahara';
                    break;
                }
                if ($institutionid = $this->get_institution_id($record['ownername'])) {
                    $record['institution'] = $record['ownername'];
                }
                else {
                    throw new SystemException("The institution '" . $record['ownername'] . "' does not exist.");
                }
                $sqljoin = 'INNER JOIN {institution} i ON i.name = v.institution';
                $sqlwhere = 'AND i.displayname = ?';
                break;
            case 'group':
                if ($groupid = $this->get_group_id($record['ownername'])) {
                    $record['group'] = $groupid;
                }
                else {
                    throw new SystemException("The group '" . $record['ownername'] . "' does not exist.");
                }
                $sqljoin = 'INNER JOIN {group} g ON g.id = v.group';
                $sqlwhere = 'AND g.name = ?';
                break;
            case 'user':
            default:
                if ($ownerid = get_field('usr', 'id', 'username', $record['ownername'])) {
                    $record['owner'] = $ownerid;
                }
                else {
                    throw new SystemException("The user '" . $record['ownername'] . "' does not exist.");
                }
                $sqljoin = 'INNER JOIN {usr} u ON u.id = v.owner';
                $sqlwhere = 'AND u.username = ?';
                break;
        }
        // Check if the given pages exist and belong to the collection's owner
        $addviews = array();
        if (!empty($record['pages'])) {
            $record['pages'] = trim($record['pages']);
            $viewtitles = !empty($record['pages']) ?
                                  explode(',', $record['pages'])
                                : false;
            if (!empty($viewtitles)) {
                foreach ($viewtitles as $viewtitle) {
                    if (!empty($viewtitle) &&
                        ! $view = get_record_sql('SELECT v.id FROM {view} v ' . $sqljoin . ' WHERE v.title = ? ' . $sqlwhere
                            , array(trim($viewtitle), $record['ownername']))
                        ) {
                        throw new SystemException("The page '" . $viewtitle
                            . "' does not exist or not belong to the user '" . $record['ownername'] . "'.");
                    }
                    $addviews['view_' . $view->id] = true;
                }
            }
        }

        // Create a new collection
        require_once('collection.php');
        $data = new stdClass();
        $data->name = $record['title'];
        $data->description = $record['description'];
        if (!empty($record['group'])) {
            $data->group = $record['group'];
        }
        else if (!empty($record['institution'])) {
            $data->institution = $record['institution'];
        }
        else if (!empty($record['owner'])) {
            $data->owner = $record['owner'];
        }
        $data->navigation = 1;
        $data->submittedstatus = 0;
        $data->progresscompletion = 0;
        $collection = new Collection(0, $data);
        $collection->commit();

        // Add views to the collection
        if (!empty($addviews)) {
            $collection->add_views($addviews);
        }
    }


    /**
     * A fixture to set up journals in bulk.
     * Currently it only supports adding title / description / tags for a blog
     *
     * Example:
     * Given the following "journals" exist:
     * | owner   | ownertype | title      | description           | tags      |
     * | userA   | user      | Blog One   | This is my new blog   | cats,dogs |
     * | Group B | group     | Group Blog | This is my group blog |           |
     * @param unknown $record
     * @throws SystemException
     */
    public function create_blog($record) {
        $owner = null;
        $ownertype = null;
        $this->set_owner($record, $owner, $ownertype);

        $record['title'] = trim($record['title']);
        if (!empty($record['title'])) {
            // Check the blog does not already exist with that name
            $blogid = get_field('artefact', 'id', 'artefacttype', 'blog', 'title', $record['title']);
            if ($blogid) {
                throw new SystemException("Invalid journal with '" . $record['title'] . "'. The blog already exists for this " . $record['owner'] . " " . $record['ownertype']);
            }
        }
        else {
            throw new systemException("The " . $record['title'] . " cannot be empty");
        }
        safe_require('artefact', 'blog');
        if (!empty($record['tags'])) {
            $tags = array_map('trim', explode(',', $record['tags']));
        }
        $blogobj = new ArtefactTypeBlog(null, (object) array(
            'title' => trim($record['title']),
            'description' => trim($record['description']),
            'tags' => (!empty($tags) ? $tags : null),
            $ownertype => $owner,
        ));
        $blogobj->commit();
    }

    /**
     * A fixture to set up journal entries in bulk.
     * Currently it only supports adding title / description / tags for a blog entry
     *
     * Example:
     * Given the following "journalposts" exist:
     * | owner   | ownertype | title | entry | blog | tags | draft |
     * | userA   | user | Entry One | This is my entry | Blog 1 | cats,dogs | 0 |
     * | Group B | group | GE 1 | This is my group entry | G Blog 2 | | 0 |
     * | userB   | user | Entry One | This is my entry | | | 1 |  <-- No blog specified should default to default blog
     * @param unknown $record
     * @throws SystemException
     */
    public function create_blogpost($record) {
      $owner = null;
      $ownertype = null;
      $this->set_owner($record, $owner, $ownertype);

      $record['blog'] = trim($record['blog']);
      if (!empty($record['blog'])) {
        // Check the blog exists with that name
        $blogid = get_field('artefact', 'id', 'artefacttype', 'blog', 'title', $record['blog']);
        if (!$blogid) {
          throw new SystemException("Invalid journal '" . $record['blog'] . "'. The " . $record['ownertype'] . " " . $record['owner'] . " does not have a blog called " . $record['blog']);
        }
      }
      else {
        //pick any blog as long as the given user has one
        $blogid = get_field_sql("SELECT id FROM {artefact} WHERE artefacttype = ? AND " . $ownertype . " = ? ORDER BY id LIMIT 1", array('blog', $owner));
        if (!$blogid) {
          throw new SystemException("The " . $record['ownertype'] . " " . $record['owner'] . " does not have a blog to add blog entry to. Please create blog first");
        }
      }
      safe_require('artefact', 'blog');
      $artefact = new ArtefactTypeBlogPost();
      $artefact->set('title', trim($record['title']));
      $artefact->set('description', trim($record['entry']));
      $tags = array_map('trim', explode(',', $record['tags']));
      $artefact->set('tags', (!empty($tags) ? $tags : null));
      $artefact->set('published', !$record['draft']);
      $artefact->set('owner', $owner);
      $artefact->set('parent', $blogid);
      $artefact->commit();
    }

    /**
     * A fixture to set up forums in bulk.
     * Currently it doesn't support indenting and other additional settings
     *
     * And the following "forums" exist:
     *  | group  | title     | description          | creator | config          |
     *  | Group1 | unicorns! | magic mahara unicorns| UserB   | autosubscribe=1 |
     *
     * @param unknown $record
     * @throws SystemException
     */
    public function create_forum($record) {
      $record['title'] = trim($record['title']);
      $record['description'] = trim($record['description']);
      $record['creator'] = trim($record['creator']);
      $record['group'] = trim($record['group']);

      // default forum forum config
      $configdata = array(
        'createtopicusers' => 'members',
        'autosubscribe'    => 1,
        'justcreated'      => 1,
      );

      // check for custom config in table
      if (isset($record['config'])) {
          $config_arr= explode(',', trim($record['config']));

          foreach ($config_arr as $key => $value) {
              $found_equals = strpos($value, '=');
              if ($found_equals === false) {
                  continue;
              }
              list($setting, $value) = explode('=', $value);
              $configdata[$setting] = $value;
          }
      }

      // check that the group exists
      if (!$groupid = get_field('group', 'id', 'name',$record['group'] )) {
        throw new SystemException("Invalid group '" . $record['group'] . "'");
      }

      //check the creator exists as a user
      if (!$creatorid = get_field('usr','id', 'username', $record['creator'])) {
        throw new SystemException("The user " . $record['creator'] . " doesn't exist");
      }

      //check that the creator is an admin of the group (for permission to create forum)
      if (!get_field('group_member', 'member', 'group', $groupid, 'role', "admin", "member", $creatorid)) {
        throw new SystemException("The " . $record['creator'] . " does not have admin rights in group " . $record['group'] . "to create a forum");
      }

      $forum = new InteractionForumInstance(0, (object) array(
        'group'       => $groupid,
        'creator'     => $creatorid,
        'title'       => $record['title'],
        'description' => $record['description']
      ));

      $forum->commit();

      // configure other settings
      PluginInteractionForum::instance_config_save($forum, $configdata);
    }

    /**
     * A fixture to set up forum posts in bulk
     *
     * - if the topic doesn't exist, create a new one
     *   (fyi, a topic is just the first post in a thread ;)
     * - if the forum doesn't exist, post in General Discussion and ignore the title
     * - if no subject, it is the same name as the forum
     *
     * @param unknown $record
     * @throws SystemException
     */
    public function create_forumpost($record) {
        $record['forum'] = trim($record['forum']);
        $record['group'] = trim($record['group']);
        $record['message'] = trim($record['message']);
        $record['topic'] = trim($record['topic']);
        $record['user'] = trim($record['user']);

        $groupid;
        $forumid;
        $topicid;
        $postid;
        $userid;
        $parentpostid = null;
        $newtopic = false;
        $newsubject = false;

        if (!isset($record['topic'])) {
            throw new SystemException("Missing a topic");
        }

        // check that the group exists
        if (!$groupid = get_field('group', 'id', 'name',$record['group'] )) {
            throw new SystemException("Invalid group '" . $record['group'] . "'");
        }

        // check the user exists and is part of the group i.e. can make a post
        if ($userid = get_field('usr','id', 'username', $record['user'])) {
            if (!get_field('group_member', 'member', 'group', $groupid, "member", $userid)) {
                throw new SystemException("The " . $record['user'] . " is not a member in the group " . $record['group']);
            }
        }
        else {
            throw new SystemException("The user " . $record['user'] . " doesn't exist");
        }

        // check the given forum exists else set to default forum General Discussion
        if (!$forumid = get_field('interaction_instance', 'id', 'group', $groupid, 'title', $record['forum'])) {
            // if the forum name doesn't exist, set the forumid to the default General discussion forum
            $forumid = get_field('interaction_instance', 'id', 'group', $groupid, 'title', get_string('defaultforumtitle', 'interaction.forum'));
        }

        // Heads up, it will begin to get confusing here... so here is a brief explanation of my understanding:
            // - the name of a forum is the title it is given when created
            // - the name of a topic is the first subject of a post and is the parent of posts in responses to that parent post.
            //   Only the parent post holds the subject in  the interaction_forum_post
            // - if there is no subject given or the subject given is the same as the topic,
            //   the post responses have no subject, but they hold the postid of the the original parent post,
            // - the name of a subject is either the parent post in a thread or a subparent in a thread with it's own subject
            //   - a subthread post with a new subject holds the parent as well as a subject title in the db

        // check the given topic exists as a topic subject in the forums
        if ($topicid = get_field('interaction_forum_post', 'topic', 'subject', $record['topic'])) {
            $parentpostid = get_field('interaction_forum_post', 'id', 'subject', $record['topic']);
            if (!empty($record['subject'])) {
                // check that the given subject exists
                if (!$subjectpostid = get_field('interaction_forum_post', 'id', 'subject', $record['subject'])) {
                    //new subject
                    $newsubject = true;
                }
                else {
                    //subject exists
                    $parentpostid = $subjectpostid;
                }
            }
        }
        // thread with given topic doesn't exist, so create a new topic with given topic
        else {
            $parentpostid = null;
            $newtopic = true;
            $record['subject'] = $record['topic'];

            //create a new topic
            $topicid = insert_record(
                'interaction_forum_topic',
                (object)array(
                    'forum'  => $forumid,
                    'sticky' =>  0,
                    'closed' =>  0,
                    'sent'   =>  1
                ), 'id', true
            );
        }

        $post = (object)array(
            'topic'   => $topicid,
            'poster'  => $userid,
            'body'    => $record['message'],
            'ctime'   =>  db_format_timestamp(time()),
            'parent'  => $parentpostid,
            'subject' => ($newtopic || $newsubject) ? $record['subject'] : null
        );
        $postid = insert_record('interaction_forum_post', $post, 'id', true);

        $forum = new InteractionForumInstance($forumid);


        // Check for attachments attachments
        if (self::check_attachments($record)) {
            $attachmentids = self::process_attachments_get_ids($record[ATTACHMENTS], $userid);
            foreach($attachmentids as $artefactid) {
                $forum->attach($postid, $artefactid);
            }
        }
    }

    public static function check_attachments($record) {
        return isset($record[ATTACHMENTS]) && !empty($record[ATTACHMENTS]);
    }

    /**
     * Creates artefacts for each attachment and connects them to given user/group
     */
    public static function process_attachments_get_ids($attachments, $userid=null, $groupid=null) {
        $resultartefactids = array();

        if (empty($userid) && empty($groupid)) {
            throw new SystemException('Cannot find a userid or groupid to process attachments');
        }

        if (empty($attachments)) {
            return $resultartefactids;
        }

        $files_arr = explode(',', $attachments);
        if (!empty($files_arr)) {
            foreach ($files_arr as $filename) {
                $file = trim($filename);

                // connect file to user/group
                if (!empty($userid)) {
                    $artefactid = self::process_attachment($file, 'user', $userid);
                }
                else if (!empty($groupid)) {
                    $artefactid = self::process_attachment($file, 'group', $groupid);
                }

                $resultartefactids[] = $artefactid;
            }
        }
        return $resultartefactids;
    }

    /**
     * A fixture to set up messages in bulk.
     * Currently it only supports setting friend request / accept internal notifications
     * @TODO allow for other types of messages
     *
     * Example:
     * Given the following "messages" exist:
     * | emailtype | to | from | subject | messagebody | read | url | urltext |
     * | friendrequest | userA | userB | New friend request | This is a friend request | 1 | user/view.php?id=[from] | Requests |
     * | friendaccept  | userB | userA | Friend request accepted | This is a friend request acceptance | 1 | user/view.php?id=[to] |  |
     * @param unknown $record
     * @throws SystemException
     */
    public function create_message($record) {
      $record['to'] = trim($record['to']);
      $to = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower($record['to'])));
      if (!$to || count($to) > 1) {
        throw new SystemException("Invalid user '" . $record['to'] . "'. The username does not exist or duplicated");
      }
      $to = $to[0]->id;
      $from = null;
      if (strtolower($record['from']) != 'system') {
        $from = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower($record['from'])));
        if (!$from || count($from) > 1) {
          throw new SystemException("Invalid user '" . $record['from'] . "'. The username does not exist or duplicated");
        }
        $from = $from[0]->id;
      }
      $emailtype = strtolower(trim($record['emailtype']));
      if (!in_array($emailtype, array('friendrequest', 'friendaccept'))) {
        throw new SystemException("Invalid emailtype '" . $emailtype . "'. The email type does not exist or is not yet set up");
      }
      $subject = !empty(trim($record['subject'])) ? trim($record['subject']) : 'Message subject';
      $messagebody= !empty(trim($record['messagebody'])) ? trim($record['messagebody']) : 'Message body';
      $read = !empty($record['read']) ? 1 : 0;
      $url = null;
      if (!empty(trim($record['url']))) {
        $url = trim($record['url']);
        // See if the url needs to have a correct id added to it. This works in the following way:
        // the behat writer specifies the url and places the id var in [ ] and indicates where to
        // get the id, eg 'view/user.php?id=[to]' means to fetch the id for the user specified in
        // the 'to' column, which will be set above as variable $to
        if (preg_match_all('/\[(?P<id>\w+)\]/', $url, $matches)) {
          // replace the matched ids with their id number and set up replacement patterns
          foreach ($matches['id'] as $k => $v) {
            if (in_array($v, array('from', 'to'))) {
              $matches['id'][$k] = $$v;
              $matches[1][$k] = '/\[' . $v . '\]/';
            }
          }
          $url = preg_replace($matches[1], $matches['id'], $url);
        }
      }
      $urltext = !empty(trim($record['urltext'])) ? trim($record['urltext']) : null;

      $users = array($to);
      $data = new stdClass();
      $data->url = $url;
      $data->users = $users;
      $data->fromuser = $from;
      $data->strings = (object) array('urltext' => (object) array('key' => $urltext));
      $data->subject = $subject;
      $data->message = $messagebody;

      $activity =  new ActivityTypeMaharamessage($data, false);
      $activity->notify_users();
    }

    /**
     * A fixture to set up page & collection permissions. Currently it only supports setting a blanket permission of
     * "public", "loggedin", "friends", "private", "user + role", and allowcomments & approvecomments
     *
     * Example:
     * Given the following "permissions" exist:
     * | title | accesstype | accessname | allowcomments |
     * | Page 1 | loggedin | loggedin | 1 |
     * | Collection 1 | public | public | 1 |
     * | Page 2 | user | userA | 0 |
     * @param unknown $record
     * @throws SystemException
     */
    public function create_permission($record) {
      $sql = "SELECT id, 'view' AS \"type\" FROM {view} WHERE LOWER(TRIM(title))=?
      UNION
      SELECT id, 'collection' AS \"type\" FROM {collection} WHERE LOWER(TRIM(name))=?";
      $title = strtolower(trim($record['title']));
      $ids = get_records_sql_array($sql, array($title, $title));
      if (!$ids || count($ids) > 1) {
        throw new SystemException("Invalid page/collection name '" . $record['title'] . "'. The page/collection title does not exist, or is duplicated.");
      }
      $id = $ids[0];
      $viewids = array();
      if ($id->type == 'view') {
        $viewids[] = $id->id;
      }
      else {
        $records = get_records_array('collection_view', 'collection', $id->id, 'displayorder', 'view');
        if (!$records) {
          throw new SystemException("Can't set permissions on empty collection named '" . $record['title'] . "'.");
        }
        foreach ($records as $view) {
          $viewids[] = $view->view;
        }
      }

      if ($record['accesstype'] == 'private') {
        $accesslist = array();
      }
      else {
        $role = null;
        switch ($record['accesstype']) {
          case 'user':
          $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($record['accessname']))));
          if (!$ids || count($ids) > 1) {
            throw new SystemException("Invalid access user '" . $record['accessname'] . "'. The username does not exist or duplicated");
          }
          $id = $ids[0]->id;
          $type = 'user';
          if (!empty($record['role']) && $userrole = get_field('usr_access_roles', 'role', 'role', $record['role'])) {
            $role = $userrole;
          }
          break;
          case 'public':
          case 'friends':
          case 'loggedin':
          $type = $id = $record['accesstype'];
          break;
        }
        // TODO: This only supports one access record at a time per page
        $accesslist = array(
        array(
        'startdate' => null,
        'stopdate' => null,
        'type' => $type,
        'role' => $role,
        'id' => $id,
        )
        );
      }
      if (!empty($record['multiplepermissions'])) {
        require_once('view.php');
        $firstview = new View($viewids[0]);
        $currentaccess = $firstview->get_access();
        $accesslist = array_merge($currentaccess, $accesslist);
      }

      $viewconfig = array(
      'startdate'       => null,
      'stopdate'        => null,
      'template'        => 0,
      'retainview'      => (int) (isset($record['retainview']) ? $record['retainview'] : 0),
      'allowcomments'   => (int) (isset($record['allowcomments']) ? $record['allowcomments'] : 1),
      'approvecomments' => (int) (isset($record['approvecomments']) ? $record['approvecomments'] : 0),
      'accesslist'      => $accesslist,
      'lockblocks'      => (int) (isset($record['lockblocks']) ? $record['lockblocks'] : 0),
      );

      require_once('view.php');
      View::update_view_access($viewconfig, $viewids);
    }

    /**
     * A fixture to set up plans in bulk.
     * Currently it only supports adding title / description / tags for a plan
     *
     * Example:
     * Given the following "plans" exist:
     * | owner   | ownertype | title      | description           | tags      |
     * | userA   | user      | Plan One   | This is my new plan   | cats,dogs |
     * | Group B | group     | Group Plan | This is my group plan | unicorn   |
     */
    public function create_plan($record) {
        $owner = null;
        $this->set_owner($record, $owner);

        $artefact = new ArtefactTypePlan();
        $artefact->set('title', $record['title']);
        $artefact->set('description', $record['description']);
        $artefact->set('owner', $owner);

        if (!empty($record['tags'])) {
            $tags = array_map('trim', explode(',', $record['tags']));
            $artefact->set('tags', (!empty($tags) ? $tags : null));
        }
        $artefact->commit();
    }

    /**
     * A fixture to set up  RESUME - BOOKS AND PUBLICATION in bulk.
     *
     * Example:
     * Given the following "books and publications" exist:
     * | user  | date     | title                                     | contribution | description          |
     * | UserA | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author    | seven million copies |
     * | UserB | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author    | seven million copies |
      */
    public function create_resume_book($record) {
        $itemdata = array();
        $userid = $this->get_user_id($record['user']);

        // create artefact
        $artefact = new ArtefactTypeBook();
        $artefact->set('owner', $this->get_user_id($record['user']));
        $artefact->commit();
        $itemdata['artefact'] = $artefact->get('id');

        require_once('embeddedimage.php');
        $description = EmbeddedImage::prepare_embedded_images('<p>'.$record['description'].'</p>', 'book', $userid);
        $record['description'] = $description;

        $formelements = ArtefactTypeBook::get_addform_elements();
        foreach ($formelements as $element => $value) {
            if (isset($record[$element])) {
                 $itemdata[$element] = $record[$element];
            }
        }
        //default to prevent db error
        $itemdata['displayorder'] = 1;
        $table = 'artefact_resume_book';
        $itemid = insert_record($table, (object)$itemdata, 'id', true);

        if (!empty($record['attachment'])) {
            $file = trim($record['attachment']);
            $artefactfileid = self::process_attachment($file, 'user', $userid);
            $artefact->attach($artefactfileid, $itemid);
        }
    }

    /**
     * A fixture to set up  RESUME - CERTIFICATIONS AND ACCREDITATIONS in bulk.
     *
     * Example:
     * Given the following "certifications and accreditations" exist:
     * | user  | date     | title          | description |
     * | UserA | 02/02/80 | example title  | acceditation description |
     * | UserB | 02/02/80 | example title  | certification description |
      */
    public function create_resume_certification($record) {
        $itemdata = array();
        $userid = $this->get_user_id($record['user']);
        $artefact = null;

        // create artefact
        $artefact = new ArtefactTypeCertification();
        $artefact->set('owner', $this->get_user_id($record['user']));
        $artefact->commit();
        $artefactid = $artefact->get('id');
        $itemdata['artefact'] = $artefactid;

        $formelements = ArtefactTypeCertification::get_addform_elements();
        foreach ($formelements as $element => $value) {
          if (isset($record[$element])) {
            $itemdata[$element] = $record[$element];
          }
        }
        //default to prevent db error
        $itemdata['displayorder'] = 1;
        $table = 'artefact_resume_certification';
        $itemid = insert_record($table, (object)$itemdata, 'id', true);

        if (!empty($record['attachment'])) {
            $file = trim($record['attachment']);
            $artefactfileid = self::process_attachment($file, 'user', $userid);
            $artefact->attach($artefactfileid, $itemid);
        }
    }

    /**
     * A fixture to set up  RESUME - CONTACT INFORMATION in bulk.
     * does not work in group pages
     * Example:
     * And the following "contactinformation" exist:
     * | user  | email            | mobilenumber |
     * | UserA | userA@mahara.com | 01234567890  |
     */
    public function create_resume_contactinformation($record) {
        $itemdata = array();
        $userid = $this->get_user_id($record['user']);

        $contactfields = array(
          'email',
          'maildisabled',
          'officialwebsite',
          'personalwebsite',
          'blogaddress',
          'address',
          'town',
          'city',
          'country',
          'homenumber',
          'businessnumber',
          'mobilenumber',
          'faxnumber'
        );

        foreach ($contactfields as $field) {
            if (isset($record[$field])) {
                $itemdata[$field] = $record[$field];
            }
        }

        // the contactinformation artefact is separate from the inner artefacts within
        // such as the officialwebsite, homenumber, email artefacts etc. In the db
        // the description field is left empty, and the $values are in the title field
        // with not embedded text, unlike other artefacts.
        $artefact = ArtefactTypeContactinformation::setup_new($userid);
        foreach ($itemdata as $artefacttype => $title) {
            $classname = generate_artefact_class_name($artefacttype);
            $artefactid = get_field('artefact','id','artefacttype',$artefacttype,'owner',$userid);
            $artefact = new $classname(0, array(
              'owner' => $userid,
              'title' => $title,
            ));
            $artefact->commit();
        }
    }

    /**
     * A fixture to set up  RESUME - COVER LETTER in bulk.
     *
     * Example:
     * And the following "coverletters" exist:
     * | user  | content |
     * | UserA |UserA In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |
     * | UserB |UserB In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |
     */
    public function create_resume_coverletter($record) {
        $userid = $this->get_user_id($record['user']);
        $coverletter = null;

        // if there already exists a coverletter for the same user, throw exception as can only have one
        if (get_field('artefact','id','artefacttype','coverletter','owner',$userid)) {
            throw new SystemException("There already exists a coverletter for" . $record['user']);
        }

        // create artefact
        $classname = generate_artefact_class_name('coverletter');
        require_once('embeddedimage.php');
        $coverletter = EmbeddedImage::prepare_embedded_images('<p>'.$record['content'].'</p>', 'resumecoverletter', $userid );

        $coverletterartefact = new $classname(0, array(
            'owner' => $userid,
            'title' => get_string('coverletter', 'artefact.resume'),
        ));
        $coverletterartefact->set('description', $coverletter);
        $coverletterartefact->commit();
    }

    /**
     * A fixture to set up  RESUME - EDUCATION HISTORY in bulk.
     *
     * Example:
     * And the following "educationhistory" exist:
     * | user  | institution         | startdate | enddate  | qualdescription |
     * | UserA | example institution | 12/12/12  | 12/12/21 | school          |
     * | UserB | example institution | 21/10/21  | 10/12/26 | school          |
     * | UserA | example institution | 12/12/20  | 12/12/21 | school          |
     * | UserB | example institution | 21/10/20  | 10/12/26 | school          |
     */
     public function create_resume_educationhistory($record) {
         $itemdata = array();
         $userid = $this->get_user_id($record['user']);
         $artefact = null;

         if ($artefactid = get_field('artefact', 'id', 'artefacttype', 'educationhistory')) {
             $artefact = new ArtefactTypeEducationhistory($artefactid, null);
             $itemdata['artefact'] =  $artefact->get('id');
         }
         else {
           $artefact = new ArtefactTypeEducationhistory();
           $artefact->set('owner', $this->get_user_id($record['user']));
           $artefact->commit();
           $itemdata['artefact'] = $artefact->get('id');
         }

         $formelements = ArtefactTypeEducationhistory::get_addform_elements();
         foreach ($formelements as $element => $value) {
             if (isset($record[$element])) {
                  $itemdata[$element] = $record[$element];
             }
         }
         //default to prevent db error
         $itemdata['displayorder'] = 1;
         $table = 'artefact_resume_educationhistory';
         $itemid = insert_record($table, (object)$itemdata, 'id', true);
     }


    /**
     * A fixture to set up  RESUME - EMPLOYMENT HISTORY in bulk.
     *
     * Example:
     * And the following "employmenthistory" exist:
     * | user  | employer  | startdate | enddate | jobtitle   | positiondescription    |
     * | UserA | employer1 | 01/02/03  |         | crystal dr | locating magic crystals|
     * | UserB | employer2 | 02/02/00  |         | Cat sitter | pat kittens            |
     */
     public function create_resume_employmenthistory($record) {
         $itemdata = array();
         $userid = $this->get_user_id($record['user']);
         $artefact = null;

         if ($artefactid = get_field('artefact', 'id', 'artefacttype', 'employmenthistory')) {
             $artefact = new ArtefactTypeEmploymenthistory($artefactid, null);
             $itemdata['artefact'] =  $artefact->get('id');
         }
         else {
           $artefact = new ArtefactTypeEmploymenthistory();
           $artefact->set('owner', $this->get_user_id($record['user']));
           $artefact->commit();
           $itemdata['artefact'] = $artefact->get('id');
         }

         $formelements = ArtefactTypeEmploymenthistory::get_addform_elements();
         foreach ($formelements as $element => $value) {
             if (isset($record[$element])) {
                  $itemdata[$element] = $record[$element];
             }
         }
         //default to prevent db error
         $itemdata['displayorder'] = 1;
         $table = 'artefact_resume_employmenthistory';
         $itemid = insert_record($table, (object)$itemdata, 'id', true);

         if (!empty($record['attachment'])) {
             $file = trim($record['attachment']);
             $artefactfileid = self::process_attachment($file, 'user', $userid);
             $artefact->attach($artefactfileid, $itemid);
         }
     }

    /**
     * A fixture to set up  RESUME - GOALS in bulk.
     *
     * Attachments added, will not relate to a specific goal/skill
     * but are connected to the entire set.
     *
     * Example:
     * And the following "goals and skills" exist:
     * | user  | goaltype/skilltype  | title        | description           |
     * | UserA | academicgoal        | fix lateness | pack bag night before |
     * | UserA | careergoal          | meow         | cat a lyst            |
     * | UserA | personalgoal        | gym shark    | do do do              |
     * | UserA | academicskill       | alphabet     | abc                   |
     * | UserA | personalskill       | whistle      | *inset whistle noise  |
     * | UserA | workskill           | team work    | axe throwing?         |
     */
    public function create_resume_goalsandskills($record) {
        $artefact = null;
        $userid = $this->get_user_id($record['user']);

        $goalsandskills = array(
          'personalgoal',
          'academicgoal',
          'careergoal',
          'personalskill',
          'academicskill',
          'workskill',
          'personalgoal',
          'academicgoal',
          'careergoal',
          'personalskill',
          'academicskill',
          'workskill'
        );

        $artefacttype = $record['goaltype/skilltype'];
        if (in_array($artefacttype,  $goalsandskills)) {
            $classname = generate_artefact_class_name($artefacttype);

            // if there exists multiple entires of interest in the table for same user,
            // merge with the pre-existing artefact content
            $artefactid = get_field('artefact','id','artefacttype',$artefacttype,'owner',$userid);
            $goalskill = null;
            if ($artefactid) {
                $goalskill = get_field('artefact','description','id',$artefactid);
                execute_sql("DELETE FROM {artefact} WHERE id=$artefactid AND owner=$userid");
            }
            $artefact = new $classname(0, array(
                'owner' => $userid,
                'title' => get_string("$artefacttype", 'artefact.resume'),
            ));

            require_once('embeddedimage.php');
            $goalskill .= EmbeddedImage::prepare_embedded_images('<p><strong>'.$record['title'].'&nbsp;</strong>'.$record['description'].'</p>', $artefacttype, $userid);
            $artefact->set('description', $goalskill);
            $artefact->commit();

            // Attachments
            if (!empty($record['attachment'])) {
                $file = trim($record['attachment']);
                $artefactid = self::process_attachment($file, 'user', $userid);
                $artefact->attach($artefactid);
            }
        }
    }

    /**
     * A fixture to set up  RESUME - INTERESTS in bulk.
     *
     * Example:
     * And the following "interests" exist:
     * | user  | interest  | description                 |
     * | UserA | FOSS      | exciting open source stuff! |
     * | UserA | Mahara    | awesome e-portfolio system  |
     * | UserA | Coding and Coffee |  |
     */
    public function create_resume_interests($record) {
        $interests = null;
        $userid = $this->get_user_id($record['user']);

        // if there exists multiple entires of interest in the table for same user,
        // merge with the pre-existing interest artefact content
        $interestid = get_field('artefact','id','artefacttype','interest','owner',$userid);
        if ($interestid) {
            $interests = get_field('artefact','description','id',$interestid);
            execute_sql("DELETE FROM {artefact} WHERE id=$interestid AND owner=$userid");
        }

        // create new artefact
        $userid = $this->get_user_id($record['user']);
        $classname = generate_artefact_class_name('interest');
        require_once('embeddedimage.php');
        $interests .= EmbeddedImage::prepare_embedded_images('<p><strong>'.$record['interest'].'&nbsp;</strong>'.$record['description'].'</p>', 'resumeinterest', $userid);

        $artefact = new $classname(0, array(
            'owner' => $userid,
            'title' => get_string('interests', 'artefact.resume'),
        ));
        $artefact->set('description', $interests);
        $artefact->commit();
    }

    /**
     * A fixture to set up  RESUME - PROFESSIONAL MEMBERSHIPS in bulk.
     *
     * Example:
     * And the following "professionalmemberships" exist:
     * | user  | startdate   | title                       | description        |
     * | UserA | 20/02/2008  | cat art company coordinator | catch up with cats |
     * | UserB | 20/02/2008  | cat art company catcher     | catch fish for cats|
     */
    public function create_resume_membership($record) {
        $itemdata = array();
        $userid = $this->get_user_id($record['user']);
        $artefact = null;

        // create artefact
        $artefact = new ArtefactTypeMembership();
        $artefact->set('owner', $this->get_user_id($record['user']));
        $artefact->commit();
        $itemdata['artefact'] = $artefact->get('id');

        require_once('embeddedimage.php');
        $description = EmbeddedImage::prepare_embedded_images('<p>'.$record['description'].'</p>', 'membership', $userid);
        $record['description'] = $description;

        $formelements = ArtefactTypeMembership::get_addform_elements();
        foreach ($formelements as $element => $value) {
            if (isset($record[$element])) {
                 $itemdata[$element] = $record[$element];
            }
        }
        //default to prevent db error
        $itemdata['displayorder'] = 1;
        $table = 'artefact_resume_membership';
        $itemid = insert_record($table, (object)$itemdata, 'id', true);

        if (!empty($record['attachment'])) {
            $file = trim($record['attachment']);
            $artefactfileid = self::process_attachment($file, 'user', $userid);
            $artefact->attach($artefactfileid, $itemid);
        }
    }

    /**
     * A fixture to set up RESUME - personalinformation artefacts in bulk
     *
     * Example:
     * And the following "personalinformation" exist:
     * | user  | dateofbirth | placeofbirth | citizenship | visastatus | gender | maritalstatus |
     * | UserA | 01/01/2000  | Italy        | New Zealand |            |        |               |
     * | UserB | 01/01/2018  | Germany      | New Zealand |            |        |               |
     */
    public function create_resume_personalinformation($record) {
        $artefact = new ArtefactTypePersonalinformation();
        $artefact->set('owner', $this->get_user_id($record['user']));
        $artefact->commit();

        $composites = ArtefactTypePersonalinformation::get_composite_fields();
        foreach ($composites as $composite => $value) {
            if (isset($record[$composite])) {
                $artefact->set_composite($composite, $record[$composite]);
            }
        }
    }

    /**
     * A fixture to set up tasks in bulk
     *
     * Example:
     * And the following "tasks" exist:
     *| owner | ownertype | plan     | title   | description          | completiondate | completed | tags      |
     *| UserA | user      | Plan One | Task One| Task One Description | 12/12/19       | no        | cats,dogs |
     *| UserA | user      | Plan One | Task Two| Task Two Description | 12/01/19       | yes       | cats,dogs |
     *| UserA | user      | Plan Two | Task 2a | Task 2a Description  | 12/10/19       | yes       | cats,dogs |
     *| UserA | user      | Plan Two | Task 2b | Task 2b Description  | 11/05/19       | yes       | cats,dogs |
     *
     * @param array $record row of fields from the behat table for creating tasks in bulk
     */
    public function create_task($record) {
        $owner = null;
        $this->set_owner($record, $owner);

        $record['plan'] = trim($record['plan']);
        if (!empty($record['plan'])) {
            //check that there exists a plan to add a task to
            $planid = get_field('artefact', 'id', 'artefacttype', 'plan', 'title', $record['plan'], 'owner', $owner );
            if (!$planid) {
                throw new SystemException("Invalid Plan '" . $record['plan'] . "'. The " . $record['ownertype'] . " " . $record['owner'] . " does not have a plan called " . $record['plan']);
            }
        }
        else {
            //pick any plan artefact owned by the given user
            $planid = get_field_sql("SELECT id FROM {artefact} WHERE artefacttype = ? AND " . $ownertype . " = ? ORDER BY id LIMIT 1", array('plan', $owner));
            if (!$planid) {
                throw new SystemException("The " . $record['ownertype'] . " " . $record['owner'] . " does not have a plan to add task to. Please create plan first");
            }
        }

        $artefact = new ArtefactTypeTask();
        $artefact->set('title', trim($record['title']));
        $artefact->set('description', trim($record['description']));
        $artefact->set('completed', $record['completed'] ? 1 : 0);
        $artefact->set('owner', $owner);
        $artefact->set('parent', $planid);
        $completiondate = date_create_from_format('d/m/y', $record['completiondate']);
        $artefact->set('completiondate', $completiondate);

        if (!empty($record['tags'])) {
            $tags = array_map('trim', explode(',', $record['tags']));
            $artefact->set('tags', (!empty($tags) ? $tags : null));
        }
        $artefact->commit();
    }

    /**
     * sets up the owner and ownertype when creating bulk artefacts
     * in functions looking like create_[...]
     *
     * $ownertype is currently only used by blog and blogentry
     *
     * @param array $record an array representation of a row of the testing table
     * @param string $owner null variable passed in by reference for owner
     * @param string $owner null variable passed in by reference for ownertype
     * @return return type
     */
    public function set_owner($record, &$owner, &$ownertype = null) {
      $ownertype = null;
      $record['owner'] = trim($record['owner']);
      $record['ownertype'] = trim($record['ownertype']);
      if ($record['ownertype'] == 'group') {
          $owner = get_field('group', 'id', 'name', $record['owner']);
          $ownertype = 'group';
      }
      else if ($record['ownertype'] == 'institution') {
          $owner = get_field('institution', 'name', 'displayname', $record['owner']);
          $ownertype = 'institution';
      }
      else {
          $owner = get_field('usr', 'id', 'username', $record['owner']);
          $ownertype = 'owner';
      }
      if (!$owner) {
          throw new SystemException("Invalid owner. The owner needs to be a username or group/institution display name");
      }
    }
  }
