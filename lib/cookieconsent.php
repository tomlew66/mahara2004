<?php
/**
 *
 * @package    mahara
 * @subpackge  admin
 * @author     Gregor Anzelj
 * @author     Silktide Ltd.
 * @author URI http://sitebeam.net/cookieconsent/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @license    http://sitebeam.net/cookieconsent/documentation/licence/
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 * @copyright  (C) 2013 Silktide Ltd.
 *
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    License for Cookie Consent plugin
 *    Copyright (C) 2013 Silktide Ltd.
 *
 *    This program is free software: you can redistribute it and/or modify it under the terms
 *    of the GNU General Public License as published by the Free Software Foundation, either
 *    version 3 of the License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU General Public License for more details.
 *
 */

defined('INTERNAL') || die();


function get_cookieconsent_code() {
    global $THEME;

    $stylesheets = '';
    if ($links = $THEME->get_url('style/cookieconsent.css', true)) {
        $links = array_reverse($links);
        foreach ($links as $link) {
            $stylesheets .= '<link rel="stylesheet" type="text/css" href="' . $link . '">' . "\n";
        }
    }
    $values = unserialize(get_config('cookieconsent_settings'));
    // To see full list of options go to https://cookieconsent.insites.com/documentation/javascript-api/
    // * needs the messagelink template to be able to set target=_self
    // the fix for this hasnt been released yet https://github.com/insites/cookieconsent/pull/396
    $initialisation = json_encode(array(
        'theme' => 'classic',
        'content' => array(
          'target' => '_self',
            'message' => get_string('cookieconsentmessage', 'cookieconsent'),
            'href'    => get_config('wwwroot') . 'legal.php',
            'link'    => get_string('cookieconsentlearnmore', 'cookieconsent'),
            'dismiss' => get_string('cookieconsentdismiss', 'cookieconsent'),
        ),
        'palette' => null,
        'elements' => array(
            'messagelink' => '<span id="cookieconsent:desc" class="cc-message">{{message}} <a tabindex="0" class="cc-link" href="{{href}}" target="_self">{{link}}</a></span>',
        ),
    ), JSON_FORCE_OBJECT);
    $wwwroot = get_config('wwwroot');
    return <<<CODE
<!-- Begin Cookie Consent plugin by Silktide - https://cookieconsent.insites.com/ -->
{$stylesheets}
<script>
jQuery(function() {
    window.cookieconsent.initialise(
        {$initialisation}
    );
});
</script>
<script src="{$wwwroot}js/cookieconsent/cookieconsent.min.js"></script>
<!-- End Cookie Consent plugin -->
CODE;
}
