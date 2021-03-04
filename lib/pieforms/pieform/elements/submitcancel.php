<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package    pieform
 * @subpackage element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Renders a submit and cancel button
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 *                          The element can have 'class' option where the classes are added to both the submit and cancel options
 *                          and have 'subclass' array where we can add different classes to each.
 * @return string           The HTML for the element
 */
function pieform_element_submitcancel(Pieform $form, $element) {/*{{{*/
    if (!isset($element['value']) || !is_array($element['value']) || count($element['value']) != 2) {
        throw new PieformException('The submitcancel element "' . $element['name']
            . '" must have a two element array for its value');
    }
    $form->include_plugin('element', 'button');
    $form->include_plugin('element', 'cancel');

    // first try for string indices
    $plugins = array('button', 'cancel');
    $elems = '';

    foreach ($element['value'] as $key => $value) {
        if (!is_numeric($key) && in_array($key, $plugins)) {
            $function = 'pieform_element_' . $key;
            if (function_exists($function)) {
                $item = $element;
                $item['class'] = isset($element['class']) ? $element['class'] . ' ' . $key : $key;
                if (isset($element['subclass']) && is_array($element['subclass']) && !empty($element['subclass'][$key])) {
                    $item['class'] .= ' ' . Pieform::hsc($element['subclass'][$key]);
                }
                $item['usebuttontag'] = ($key == 'button') ? true : false;
                $item['value'] = $element['value'][$key];
                if (isset($element['confirm']) && isset($element['confirm'][$key])) {
                    $item['confirm'] = $element['confirm'][$key];
                }
                else {
                    unset($item['confirm']);
                }
                $elems .= $function($form, $item);
                $elems .= ' ';
            }
        }
    }

    if (!empty($elems)) {
        return $elems;
    }
    else if (isset($element['value'][0]) && isset($element['value'][1])) { // ensure default numeric indices exist
        $submitelement = $element;
        $submitelement['class'] = (isset($submitelement['class'])) ? $submitelement['class'] . ' submit' : 'submit';
        if (isset($element['subclass']) && is_array($element['subclass']) && !empty($element['subclass'][0])) {
            $submitelement['class'] .= ' ' . Pieform::hsc($element['subclass'][0]);
        }
        $submitelement['value'] = $element['value'][0];
        $submitelement['usebuttontag'] = true;
        $cancelelement = $element;
        $cancelelement['class'] = (isset($cancelelement['class'])) ? $cancelelement['class'] . ' cancel' : 'cancel';
        if (isset($element['subclass']) && is_array($element['subclass']) && !empty($element['subclass'][1])) {
            $cancelelement['class'] .= ' ' . Pieform::hsc($element['subclass'][1]);
        }
        $cancelelement['value'] = $element['value'][1];
        if (isset($element['confirm']) && isset($element['confirm'][0])) {
            $submitelement['confirm'] = $element['confirm'][0];
        }
        else {
            unset($submitelement['confirm']);
        }
        if (isset($element['confirm']) && isset($element['confirm'][1])) {
            $cancelelement['confirm'] = $element['confirm'][1];
        }
        else {
            unset($cancelelement['confirm']);
        }
        return pieform_element_button($form, $submitelement) . ' ' . pieform_element_cancel($form, $cancelelement);
    }
}/*}}}*/

function pieform_element_submitcancel_set_attributes($element) {/*{{{*/
    $element['submitelement'] = true;
    return $element;
}/*}}}*/

function pieform_element_submitcancel_get_value(Pieform $form, $element) {/*{{{*/
    if (is_array($element['value'])) {
        if (isset($element['value']['button'])) {
            return $element['value']['button'];
        }
        else {
            return $element['value'][0];
        }
    }
    else {
        return $element['value'];
    }
}/*}}}*/
