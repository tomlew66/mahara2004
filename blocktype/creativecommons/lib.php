<?php
/**
 * Creative Commons License Block type for Mahara
 *
 * @package    mahara
 * @subpackage blocktype-creativecommons
 * @author     Francois Marier <francois@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeCreativecommons extends MaharaCoreBlocktype {

    // Standard Creative Commons naming scheme
    const noncommercial = 'nc';
    const noderivatives = 'nd';
    const sharealike    = 'sa';

    public static function single_only() {
        return true;
    }

    public static function single_artefact_per_block() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.creativecommons');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.creativecommons');
    }

    public static function get_categories() {
        return array('general' => 15000);
    }

    public static function get_css_icon_type($blockname) {
        return 'icon-brand';
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $THEME;
        $configdata = $instance->get('configdata');
        if (!isset($configdata['license'])) {
            return '';
        }

        $greparray = preg_grep('/^([a-z\-]+)$/', array($configdata['license']));
        $licensetype = reset($greparray);
        if (isset($configdata['version'])) {
            $licenseversion = get_string('version' . $configdata['version'], 'blocktype.creativecommons');
        }
        else {
            $licenseversion = get_string('version30', 'blocktype.creativecommons');
        }
        $licenseurl = "http://creativecommons.org/licenses/$licensetype/$licenseversion/";

        $view = $instance->get_view();
        $workname = '<span rel="dc:type" href="http://purl.org/dc/dcmitype/Text" property="dc:title">'
            . $view->display_title(true, false, false) . '</span>';
        $authorurl = $view->owner_link();
        $authorname = hsc($view->formatted_owner());

        $licensename = get_string('cclicensename', 'blocktype.creativecommons', get_string($licensetype, 'blocktype.creativecommons'), $licenseversion);
        $licenselink = '<a rel="license" href="' . $licenseurl . '">' . $licensename . '</a>';
        $attributionlink = '<a rel="cc:attributionURL" property="cc:attributionName" href="' . $authorurl . '">' . $authorname . '</a>';
        $licensestatement = get_string('cclicensestatement', 'blocktype.creativecommons', $workname, $attributionlink, $licenselink);

        $permissionlink = '<a rel="cc:morePermissions" href="'. $authorurl .'">' . $authorname . '</a>';
        $otherpermissions = get_string('otherpermissions', 'blocktype.creativecommons', $permissionlink);

        $smarty = smarty_core();
        $smarty->assign('licenseurl', $licenseurl);
        $smarty->assign('licenselogo', $THEME->get_image_url($licensetype . '-3_0', 'blocktype/creativecommons'));
        $smarty->assign('licensestatement', $licensestatement);
        $smarty->assign('otherpermissions', $otherpermissions);
        return $smarty->fetch('blocktype:creativecommons:statement.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array('js/creativecommons.js');
    }
    //sets wording for block
    public static function instance_config_save($values) {
        $license = 'by';
        $values['noncommercial'] = (isset($values['noncommercial']) && !empty($values['noncommercial'])) ? '0' : '1';

        if (1 == $values['noncommercial']) {
            $license .= '-' . self::noncommercial;
        }

        if (1 == $values['noderivatives']) {
            $license .= '-' . self::sharealike;
        }
        else if (2 == $values['noderivatives']) {
            $license .= '-' . self::noderivatives;
        }
        $values['license'] = $license;
        return $values;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $THEME;
        $configdata = $instance->get('configdata');
        $noderivatives = 1;
        $noncommercial = 0;

        if (isset($configdata['license'])) {
            $noncommercial = !(strpos($configdata['license'], self::noncommercial) === false);

            $noderivatives = 0;
            if (strpos($configdata['license'], self::noderivatives) !== false) {
                $noderivatives = 2;
            }
            else if (strpos($configdata['license'], self::sharealike) !== false) {
                $noderivatives = 1;
            }
        }

        $displayseal = ' hidden';
        if (0 == $noncommercial and $noderivatives < 2) {
            $displayseal = '';
        }

        $version = isset($configdata['version']) ? $configdata['version'] : 30;

        // Dirty hack to display the seal just before the table
        // TODO: add a way to append stuff to the header in the maharatable pieform renderer
        $sealpositionhack = "jQuery('#freecultureseal').insertBefore('#insconf'); jQuery('#instconf').addClass('fl');";

        return array(
            'seal' => array(
                'type' => 'html',
                'value' => '<div id="freecultureseal" class="fr' .$displayseal.'"><a href="http://freedomdefined.org/">'.
                           '<img "alt="'.hsc(get_string('sealalttext', 'blocktype.creativecommons')).'" '.
                           'onload="'.hsc($sealpositionhack).'" '.
                           'style="border-width:0;" src="'.
                           $THEME->get_image_url('seal', 'blocktype/creativecommons') . '" /></a></div>',
            ),

            'noncommercial' => array(
                'type'  => 'switchbox',
                'title' => get_string('config:noncommercial', 'blocktype.creativecommons'),
                'onclick' => 'toggle_seal();',
                'defaultvalue' => !$noncommercial,
                'help' => true,
              ),

            'noderivatives' => array(
                'type' => 'select',
                'title' => get_string('config:noderivatives', 'blocktype.creativecommons'),
                'defaultvalue' => $noderivatives,
                'collapseifoneoption' => true,
                'options' => array(
                                   0 => get_string('yes'),
                                   1 => get_string('config:sharealike', 'blocktype.creativecommons'),
                                   2 => get_string('no'),
                                   ),
                'onclick' => 'toggle_seal();',
                'help' => true,
            ),

            'version' => array(
                'type' => 'select',
                'title' => get_string('config:version', 'blocktype.creativecommons'),
                'defaultvalue' => $version,
                'collapseifoneoption' => true,
                'options' => array(
                    30 => get_string('version30', 'blocktype.creativecommons'),
                    40 => get_string('version40', 'blocktype.creativecommons'),
                    ),
                'help' => true,
            ),
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return multitype:
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }
}
