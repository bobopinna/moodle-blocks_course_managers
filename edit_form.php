<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the course managers block
 *
 * @package block_course_managers
 * @copyright 2010 Roberto Pinna {roberto.pinna@uniupo.it}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing Course Managers block instances.
 *
 * @package   block_course_managers
 * @copyright 2010 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_course_managers_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Fields for editing Course Managers block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_course_managers'));
        $mform->setType('config_title', PARAM_MULTILANG);

        $choices = array(0 => get_string('all'));
        $choices[1] = '1';
        $choices[2] = '2';
        $choices[3] = '3';
        $choices[4] = '4';
        $choices[5] = '5';
        $choices[6] = '6';
        $choices[7] = '7';
        $choices[8] = '8';
        $choices[9] = '9';
        $choices[10] = '10';
        $choices[15] = '15';
        $choices[20] = '20';
        $choices[30] = '30';
        $choices[50] = '50';
        $mform->addElement('select', 'config_itemperpage', get_string('configitemperpage', 'block_course_managers'), $choices);
        if (isset($this->block->config->itemperpage)) {
            $mform->setDefault('config_itemperpage', $this->block->config->itemperpage);
        } else {
            $mform->setDefault('config_itemperpage', '10');
        }

        $choices = array();
        $choices['search'] = get_string('searchbox', 'block_course_managers');
        $choices['pages'] = get_string('pages', 'block_course_managers');
        $choices['letters'] = get_string('letters', 'block_course_managers');
        $mform->addElement('select', 'config_multipage', get_string('configmultipage', 'block_course_managers'), $choices);
        if (isset($this->block->config->multipage)) {
            $mform->setDefault('config_multipage', $this->block->config->multipage);
        } else {
            $mform->setDefault('config_multipage', 'search');
        }

        $mform->addElement('checkbox', 'config_orderbyaccess', get_string('configorderbyaccess', 'block_course_managers'));
        if (isset($this->block->config->orderbyaccess)) {
            $mform->setDefault('config_orderbyaccess', $this->block->config->orderbyaccess);
        } else {
            $mform->setDefault('config_orderbyaccess', false);
        }
        $mform->disabledIf('config_orderbyaccess', 'config_multipage', 'eq', 'letters');
    }

    protected function set_data($defaults) {

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        parent::set_data($defaults);

        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}
