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
 * @since 2.0
 * @package block_course_managers
 * @copyright 2013 Roberto Pinna {roberto.pinna@uniupo.it}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_course_managers_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2020072300) {
        if ($timetableconfig = $DB->get_record('config', array('name' => 'block_course_managers_timetableurl'))) {
            $DB->delete_records('config', array('id' => $timetableconfig->id));
        }
        upgrade_block_savepoint(true, 2020072300, 'course_managers', false);
    }

    return true;
}
