<?php
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$pagination_js = '';
/**
 * Provides a mechanism for choosing one or more artefacts from a list of them.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_artefactchooser(Pieform $form, $element) {
    global $USER, $pagination_js;

    $value = $form->get_value($element);
    $element['offset'] = param_integer('offset', 0);
    list($html, $pagination, $count, $offset, $artefactdata) = View::build_artefactchooser_data($element, $form->get_property('viewgroup'), $form->get_property('viewinstitution'));

    $smarty = smarty_core();
    $smarty->assign('datatable', $element['name'] . '_data');
    $smarty->assign('artefacts', (empty($html) ? false : $html));
    $smarty->assign('pagination', $pagination['html']);

    $formname = $form->get_name();
    $smarty->assign('blockinstance', substr($formname, strpos($formname, '_') + 1));

    // Save the pagination javascript for later, when it is asked for. This is
    // messy, but can't be helped until Pieforms goes to a more OO way of
    // managing stuff.
    $pagination_js = $pagination['javascript'];
    $pagination_js .= "\nvar acSelectArtefacts = " . json_encode($artefactdata) . "\n";

    $baseurl = View::make_base_url();
    $smarty->assign('browseurl', $baseurl);
    $smarty->assign('searchurl', $baseurl . '&s=1');
    $smarty->assign('searchable', !empty($element['search']));
    $smarty->assign('lazyload', !empty($element['lazyload']));

    return $smarty->fetch('form/artefactchooser.tpl');
}

function pieform_element_artefactchooser_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($global[$name]) || isset($global["{$name}_onpage"])) {
        $value  = (isset($global[$name])) ? $global[$name] : array();

        if ($element['selectone']) {
            if (!$value) {
                return null;
            }

            if (preg_match('/^\d+$/', $value)) {
                return intval($value);
            }
        }
        else {
            $onpage = (isset($global["{$name}_onpage"])) ? $global["{$name}_onpage"] : array();
            $selected = (is_array($value)) ? array_map('intval', array_keys($value)) : array();
            $default  = (is_array($element['defaultvalue'])) ? $element['defaultvalue'] : array();

            // 1) Start with what's currently available
            // 2) Remove everything on the page that was active when submitted
            // 3) Add in everything that was selected
            $value = array_merge(array_diff($default, $onpage), $selected);
            return array_map('intval', $value);
        }

        throw new PieformException("Invalid value for artefactchooser form element '$name' = '$value'");
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return null;
}

function pieform_element_artefactchooser_set_attributes($element) {
    if (!isset($element['selectone'])) {
        $element['selectone'] = true;
    }
    if (!isset($element['limit'])) {
        $element['limit'] = 10;
    }
    if (!isset($element['template'])) {
        $element['template'] = 'form/artefactchooser-element.tpl';
    }
    if (!isset($element['search'])) {
        $element['search'] = true;
    }

    return $element;
}

/**
 * Extension by Mahara. This api function returns the javascript required to
 * set up the element, assuming the element has been placed in the page using
 * javascript. This feature is used in the views interface.
 *
 * In theory, this could go upstream to pieforms itself
 *
 * @param Pieform $form     The form
 * @param array   $element  The element
 */
function pieform_element_artefactchooser_views_js(Pieform $form, $element) {
    global $pagination_js;

    // NOTE: $element['name'] is not set properly at this point
    $element = pieform_element_artefactchooser_set_attributes($element);
    $element['name'] = (!empty($element['selectone'])) ? 'artefactid' : 'artefactids';

    $pagination_js = 'var p = ' . $pagination_js;

    // TODO: This is quite a lot of javascript to be sending inline, especially the ArtefactChooserData
    // class.

    if (!empty($element['selectone'])) {
        $artefactchooserdata = '';
        $artefactchooserselect = empty($element['selectjscallback']) ? '' : 'new ArtefactChooserSelect(data.data.artefactdata);';
    }
    else {
        $artefactchooserdata = 'new ArtefactChooserData();';
        $artefactchooserselect = '';
    }
    $pagination_js .= <<<EOF
jQuery(function($) {
  var ul = $('#{$form->get_name()}_{$element['name']}_container ul.artefactchooser-tabs').first();
  var doneBrowse = false;
  var browseA = null;
  var searchA = null;
  var browseTabCurrent = true;
  if (ul.length) {
      ul.find('a').each(function(i, a) {
          p.rewritePaginatorLink(a);
          // Need to make sure the accessible hidden <span> is present
          // If loaded via ajax it may not be present
          if (a.childNodes.length < 2) {
              $(a).append('<span class="sr-only">(' + get_string_ajax('tab', 'mahara') + ')</span>');
          }

          if (!doneBrowse) {
              browseA = a;
              $(browseA).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ' ' + get_string_ajax('selected', 'mahara') + ')');
              doneBrowse = true;

              // Hide the search form
              $(a).on('click', function(e) {
                  $('#artefactchooser-searchform').addClass('d-none');
                  $(searchA).removeClass('active');
                  $(browseA).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ' ' + get_string_ajax('selected', 'mahara') + ')');
                  $(searchA).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ')');
                  $(browseA).addClass('active');
                  $(browseA).trigger("blur");
                  $('#artefactchooser-searchfield').val(''); // forget the search for now, easier than making the tabs remember it
                  if (!browseTabCurrent) {
                      {$artefactchooserdata}
                      browseTabCurrent = true;
                  }
                  e.preventDefault();
              });
          }
          else {
              searchA = a;

              // Display the search form
              $(a).on('click', function(e) {
                  $('#artefactchooser-searchform').show();
                  $('#artefactchooser-searchform').removeClass('d-none');
                  $(browseA).removeClass('active');
                  $(searchA).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ' ' + get_string_ajax('selected', 'mahara') + ')');
                  $(browseA).find('.sr-only').html('(' + get_string_ajax('tab', 'mahara') + ')');
                  $(searchA).addClass('active');

                  $('#artefactchooser-searchfield').on('keypress', function(e) {
                      if (e.keycode == 13) { // enter pressed - submitting form
                          e.preventDefault();
                          $('#artefactchooser-searchsubmit').triggerHandler('click', true);
                      }
                  });

                  // Wire up the search button
                  $('#artefactchooser-searchsubmit').on('click', function(e) {
                      if (e._event != true) {
                          e.preventDefault();
                      }

                      var loc = searchA.href.indexOf('?');
                      var queryData = [];
                      if (loc != -1) {
                          queryData = {
                            extradata: JSON.stringify(p.extraData),
                            search: $('#artefactchooser-searchfield').val()
                          }
                      }

                      sendjsonrequest(p.jsonScript, queryData, 'GET', function(data) {
                          // Use pagination.js to update search results
                          p.updateResults(data);
                          {$artefactchooserdata}
                          {$artefactchooserselect}
                      });
                  });
                  $('#artefactchooser-searchfield').trigger("focus");
                  if (browseTabCurrent) {
                      {$artefactchooserdata}
                      browseTabCurrent = false;
                  }
                  e.preventDefault();
              });
          }
      });
  }
});
EOF;
    if (!empty($element['selectone']) && !empty($element['selectjscallback'])) {
        $datatable = $element['name'] . '_data';
        $pagination_js .=<<<EOF
/**
 * Call the selectjscallback function whenever a radio button is clicked
 */
var ArtefactChooserSelect = (function($) {
  return function (artefacts) {
      var self = this;

      this.artefacts = artefacts;

      this.init = function() {
          self.connectPagination();
          self.connectRadios();
      }

      /**
       * Connects pagination so that when a page is changed, we are told about it
       */
      this.connectPagination = function() {
          paginatorProxy.addObserver(self);
          $(self).on('pagechanged', self.pageChanged);
      }

      /**
       * Update artefact data & connect radios to the selectjscallback
       */
      this.pageChanged = function(ev, data) {
          self.artefacts = data.artefactdata;
          self.connectRadios(data);
      }

      this.connectRadios = function(data) {
          $('#{$datatable} input').on('click', function(data) {
              if (self.artefacts[data.target.value]) {
                  {$element['selectjscallback']}(self.artefacts[data.target.value]);
              }
          });
      }

      self.init();
  }
}(jQuery));

// reattach listeners when page has finished updating
jQuery(window).on('pageupdated', {}, function(e, data) {
    new ArtefactChooserSelect(data.data.artefactdata);
});

new ArtefactChooserSelect(acSelectArtefacts);

EOF;
    }
    if (empty($element['selectone'])) {
        $pagination_js .=<<<EOF
/**
 * Manages the problem of changing pages in the artefact chooser losing what
 * things were selected/not selected
 */
var ArtefactChooserData = (function($) {
  return function() {
      var self = this;

      this.init = function() {
          self.insertElementContainers();
          self.connectPagination();
          self.connectCheckboxes();
          self.scrapeForOnpage();
          self.scrapeForSelected();
      }

      /**
       * Puts two containers into the DOM, that will each contain hidden form elements
       * - one for all the elements on the current page of results, and one for
       * the currently selected options.
       *
       * Clears out existing containers instead of making new ones, if containers
       * already exist. This happens when changing tabs on the artefact chooser
       */
      this.insertElementContainers = function() {
          self.seenElementsContainer     = $('#seen-elements-container');
          self.selectedElementsContainer = $('#selected-elements-container');

          if (self.seenElementsContainer.length) {
              // Clear out the list of seen elements
              self.seenElementsContainer.empty();
          }
          else {
              self.seenElementsContainer = $('<div>', {'id': 'seen-elements-container', 'style': 'display: none;'});
              self.seenElementsContainer.insertAfter('#artefactchooser-body');
          }

          if (self.selectedElementsContainer.length) {
              // Clear out the list of selected elements
              self.selectedElementsContainer.empty();
          }
          else {
              self.selectedElementsContainer = $('<div>', {'id': 'selected-elements-container', 'style': 'display: none;'});
              self.selectedElementsContainer.insertAfter('#artefactchooser-body');
          }
      }

      /**
       * Connects pagination so that when a page is changed, we are told about it
       */
      this.connectPagination = function() {
          paginatorProxy.addObserver(self);
          $(self).on('pagechanged', self.pageChanged);
      }

      /**
       * Connects checkboxes so when they're clicked we can deal with it
       */
      this.connectCheckboxes = function() {
          $('#artefactchooser-body input.artefactid-checkbox').each(function(id, checkBox) {
              $(this).on('click', function() {
                  self.checkboxClicked(checkBox);
              });
          });
      }

      /**
       * Find all hidden onpage inputs, and move them to the container, otherwise
       * destroy them if they're already in there (which happens if we go to a
       * page we've already seen)
       */
      this.scrapeForOnpage = function() {
          $('#artefactchooser-body input.artefactid-onpage').each(function(index, input) {
              var append = true;
              self.seenElementsContainer.children().each(function(id, seen) {
                  if (seen.value == input.value) {
                      return append = false;
                  }
              });
              if (append) {
                  self.seenElementsContainer.append(input);
              }
              else {
                  // Element is surplus to requirements
                  $(input).remove();
              }
          });
      }

      /**
       * Find all hidden currently selected inputs, and move them to the selected container
       */
      this.scrapeForSelected = function() {
          $('#artefactchooser-body input.artefactid-checkbox').each(function(id, checkBox) {
              if ($(checkBox).prop('checked')) {
                  self.ensureSelectedElement(checkBox);
              }
          });
      }

      /**
       * When a checkbox is clicked, update the list of selected inputs
       */
      this.checkboxClicked = function(checkbox) {
          if (checkbox.checked) {
              // Add to the list if it's not there
              self.ensureSelectedElement(checkbox);
          }
          else {
              // Remove from the list if it's there
              self.removeSelectedElement(checkbox);
          }
      }

      /**
       * When a pagination link is clicked, update the list of seen inputs
       */
      this.pageChanged = function(ev, data) {
          self.scrapeForOnpage();
          if ($.inArray(data.offset, self.seenOffsets) == -1) {
              self.scrapeForSelected();
              self.seenOffsets.push(data.offset);
          }
          else {
              self.syncroniseCheckboxStateFromContainer();
          }
          self.connectCheckboxes();
      }

      /**
       * Ensures that the element we have been given is in the list of selected
       * elements
       */
      this.ensureSelectedElement = function(element) {
          var append = true;
          $(self.selectedElementsContainer.children()).each(function(index, selected) {
              if (selected.name == element.name) {
                  return append = false;
              }
          });

          if (append) {
              self.selectedElementsContainer.append($('<input>', {'type': 'hidden', 'name': element.name, 'value': 1})
              );
          }
      }

      /**
       * Ensures that the element we have been given is NOT in the list of
       * selected elements
       */
      this.removeSelectedElement = function(element) {
          self.selectedElementsContainer.children().each(function(index, selected) {
              if (selected.name == element.name) {
                  $(selected).remove();
                  return false;
              }
          });
      }

      /**
       * Called when the user browses back to a page they have already seen. They
       * may have added/removed what they have checked on that page, so we need
       * to syncronise the display with their choices
       */
      this.syncroniseCheckboxStateFromContainer = function() {
          $('#artefactchooser-body input.artefactid-checkbox').each(function(i, checkbox) {
              $(checkbox).prop('checked', false);
              self.selectedElementsContainer.children().each(function(index, selected) {
                  if (selected.name == checkbox.name) {
                      // Checkbox should be checked
                      $(checkbox).prop('checked', true);
                      return false;
                  }
              });
          });
      }

      // Contains hidden elements representing every artefact we have seen,
      // regardless of whether it has been selected
      this.seenElementsContainer = null;

      // Contains hidden elements representing every artefact that has been
      // selected on any page we have seen
      this.selectedElementsContainer = null;

      // Pagination offsets we have already seen. We have always seen offset 0
      // when we begin.
      this.seenOffsets = [0];

      self.init();
  }
}(jQuery));

new ArtefactChooserData();

EOF;
    }
    return $pagination_js;
}
