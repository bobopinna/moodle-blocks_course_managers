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
 * Display useful information about courses teacher
 *
 * @package block_course_managers
 * @copyright Roberto Pinna (roberto.pinna@uniupo.it)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Extend base block to provide the list of courses teachers
 *
 * @package block_course_managers
 * @copyright Roberto Pinna (roberto.pinna@uniupo.it)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_managers extends block_base {

    public function init() {
        $this->title = get_string('coursemanagers', 'block_course_managers');
    }

    public function has_config() {
        return false;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function applicable_formats() {
        return array('site' => true);
    }

    public function specialization() {
        if (isset($this->config->title) && !empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = get_string('coursemanagers', 'block_course_managers');
        }
    }

    public function instance_allow_config() {
        return true;
    }

    public function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        if (isset($CFG->coursecontact) && !empty($CFG->coursecontact)) {

            $attributes = array();
            $attributes['id'] = 'block-course_managers-search-filter';
            $attributes['type'] = 'text';
            $attributes['placeholder'] = get_string('typetofilter', 'block_course_managers');
            $filterfield = html_writer::empty_tag('input', $attributes);

            $attributes = array();
            $attributes['id'] = 'block-course_managers-search';
            $this->content->text = html_writer::tag('div', $filterfield, $attributes);

            $attributes = array();
            $attributes['id'] = 'block-course_managers-list';
            $this->content->text .= html_writer::tag('div', '', $attributes);
        } else {
            $this->content->text = get_string('nocoursecontact', 'block_course_managers');
        }

        $arguments = array('blockid' => (int) $this->instance->id);
        $this->page->requires->js_call_amd('block_course_managers/get_managers', 'init', $arguments);

        return $this->content;
    }

    public function get_aria_role() {
        return 'navigation';
    }
}
