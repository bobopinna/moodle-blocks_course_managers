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

        $choices = array();
        $choices['accesstime'] = get_string('byaccesstime', 'block_course_managers');
        $choices['alphabetically'] = get_string('alphabetically', 'block_course_managers');
        $mform->addElement('select', 'config_orderby', get_string('configorderby', 'block_course_managers'), $choices);
        if (isset($this->block->config->orderby)) {
            $mform->setDefault('config_orderby', $this->block->config->orderby);
        } else {
            $mform->setDefault('config_orderby', 'alphabetically');
            if (isset($this->block->config->orderaccess) && !empty($this->block->config->orderaccess)) {
                $mform->setDefault('config_orderby', 'accesstime');
            }
        }
    }

    public function set_data($defaults) {

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
