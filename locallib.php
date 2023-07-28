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

function course_managers_get_courses($userid) {
    global $CFG, $DB;

    $sql = "SELECT course.*, categories.sortorder
              FROM {$CFG->prefix}course course,
                   {$CFG->prefix}course_categories categories,
                   {$CFG->prefix}context context,
                   {$CFG->prefix}role_assignments ra
             WHERE ra.roleid IN ({$CFG->coursecontact})
               AND context.contextlevel = '".CONTEXT_COURSE."'
               AND context.id = ra.contextid
               AND context.instanceid = course.id
               AND course.visible = 1
               AND categories.id = course.category
               AND ra.userid = {$userid}
             GROUP BY course.id
             ORDER BY categories.sortorder ASC, course.fullname ASC";

    return $DB->get_records_sql($sql);
}

function print_row($left, $right) {
    echo "\n<tr><th class=\"label c0\">$left</th><td class=\"info c1\">$right</td></tr>\n";
}

function print_profile_custom_fields($userid) {
    global $CFG, $USER, $DB;

    if ($categories = $DB->get_records('user_info_category', null, 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = $DB->get_records('user_info_field', array('categoryid' => $category->id), 'sortorder ASC')) {
                foreach ($fields as $field) {
                    require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
                    $newfield = 'profile_field_'.$field->datatype;
                    $formfield = new $newfield($field->id, $userid);
                    if ($formfield->is_visible() && !$formfield->is_empty()) {
                        print_row(format_string($formfield->field->name).':', $formfield->display_data());
                    }
                }
            }
        }
    }
}
