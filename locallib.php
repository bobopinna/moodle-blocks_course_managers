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

defined('MOODLE_INTERNAL') || die();

/**
 * This file keeps track of upgrades to the course managers block
 *
 * @package block_course_managers
 * @copyright 2010 Roberto Pinna {roberto.pinna@uniupo.it}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function course_managers_get_courses($userid) {
    global $CFG, $DB;

    $sql = "SELECT course.*
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

function course_managers_get_reservations($userid) {
    global $DB, $CFG;

    if ($DB->get_record('modules', array('name' => 'reservation', 'visible' => 1))) {
        $query = '(teachers = :id1 OR teachers like :id2 OR teachers like :id3 OR teachers like :id4)';
        $values = array('id1' => $userid, 'id2' => $userid.',%', 'id3' => '%,'.$userid, 'id4' => '%,'.$userid.',%');

        if (isset($CFG->reservation_deltatime) && ($CFG->reservation_deltatime >= 0)) {
            $query .= 'AND timestart > :now';
            $values['now'] = time() - $CFG->reservation_deltatime;
        }

        return $DB->get_records_select('reservation', $query, $values, 'timestart');
    } else {
        return null;
    }
}
