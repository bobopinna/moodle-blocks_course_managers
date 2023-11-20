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

function course_managers_get_reservations($userid, $deltatime=-1) {
    global $DB;

    if ($deltatime < 0) {
        $configdeltatime = get_config('reservation', 'deltatime');
        if ($configdeltatime >= 0) {
            $deltatime = $configdeltatime;
        }
    }

    if ($DB->get_record('modules', array('name' => 'reservation', 'visible' => 1))) {
        $query = '(teachers = :id1 OR teachers like :id2 OR teachers like :id3 OR teachers like :id4)';
        $values = array('id1' => $userid, 'id2' => $userid.',%', 'id3' => '%,'.$userid, 'id4' => '%,'.$userid.',%');

        if ($deltatime >= 0) {
            $query .= 'AND timestart > :now';
            $values['now'] = time() - $deltatime;
        }

        return $DB->get_records_select('reservation', $query, $values, 'timestart');
    } else {
        return null;
    }
}


// Print Reservations.
function course_managers_print_reservations($userid, $deltatime) {
    global $CFG, $DB, $OUTPUT;

    $reservations = course_managers_get_reservations($userid, $deltatime);

    if ($reservations) {
        require_once($CFG->libdir.'/tablelib.php');
        require_once($CFG->dirroot.'/mod/reservation/locallib.php');

        $strreservations = get_string('modulenameplural', 'reservation');
        $strname  = get_string('name');
        $streventdate  = get_string('date');
        $strlocation  = get_string('location', 'reservation');
        $strintro  = get_string('moduleintro');
        $stropen  = get_string('timeopen', 'reservation');
        $strclose  = get_string('timeclose', 'reservation');

        $tableheaders  = array ($strname, $streventdate, $strlocation, $strintro, $stropen, $strclose);
        $tablecolumns = array ('name', 'startdate', 'location', 'intro', 'timeopen', 'timeclose');

        if (isloggedin() && !isguestuser()) {
            $tableheaders[] = get_string('reserved', 'reservation');
            $tablecolumns[] = 'reserved';
        }

        $table = new flexible_table('reservations');
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->sortable(false);
        $table->collapsible(true);
        foreach ($tablecolumns as $column) {
            $table->column_class($column, $column);
        }
        $table->set_attribute('id', 'reservations');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->define_baseurl($pageurl);
        $table->setup();

        echo $OUTPUT->box_start('reservations');
        echo $OUTPUT->heading($strreservations);

        $table->start_output();

        $now = time();
        foreach ($reservations as $reservation) {
            $mod = get_coursemodule_from_instance('reservation', $reservation->id, $reservation->course);
            if (empty($mod)) {
                continue;
            }
            $dimmed = '';
            if (!$mod->visible) {
                $dimmed = 'dimmed';
            }

            $place = $reservation->location != '0' ? format_string(trim($reservation->location)) : '';
            $description = format_string(preg_replace('/\n|\r|\r\n/', ' ', strip_tags(trim($reservation->intro))));
            $eventdate = userdate($reservation->timestart, get_string('strftimedatetime'));
            $timeopen = '';
            if (!empty($reservation->timeopen)) {
                $timeopen = userdate($reservation->timeopen, get_string('strftimedatetime'));
            }
            if (!empty($reservation->timeopen) && ($reservation->timeopen > $now)) {
                $timeopen = html_writer::tag('span', $timeopen,
                         array('class' => 'notopened', 'title' => get_string('notopened', 'reservation')));
            }
            $timeclose = userdate($reservation->timeclose, get_string('strftimedatetime'));
            if ($reservation->timeclose < $now) {
                $timeclose = html_writer::tag('span', $timeclose,
                        array('class' => 'notopened', 'title' => get_string('closed', 'reservation')));
            }
            $reservationurl = new moodle_url($CFG->wwwroot.'/mod/reservation/view.php', array('r' => $reservation->id));
            $link = html_writer::tag('a', $reservation->name, array('href' => $reservationurl, 'class' => $dimmed));

            $row = array();
            $context = context_module::instance($mod->id);
            if ((has_capability('mod/reservation:viewrequest', $context)) || (empty($dimmed))) {
                $row = array ($link, $eventdate, $place, $description, $timeopen, $timeclose);
                if (has_capability('mod/reservation:viewrequest', $context)) {
                    $row[] = $DB->count_records('reservation_request',
                            array('reservation' => $reservation->id, 'timecancelled' => 0)) .' '. get_string('students');
                } else if (has_capability('mod/reservation:reserve', $context)) {
                    $querysql = array('reservation' => $reservation->id, 'userid' => $USER->id, 'timecancelled' => 0);
                    if ($DB->get_record('reservation_request', $querysql)) {
                        $row[] = get_string('yes');
                    } else {
                        $row[] = get_string('no');
                    }
                }
            }
            if (!empty($row)) {
                    $table->add_data($row);
            }
        }
        $table->finish_output();

        echo $OUTPUT->box_end();
    }
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
