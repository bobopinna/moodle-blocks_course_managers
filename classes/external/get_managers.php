<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_course_managers\external;

use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Provides the block_course_managers_get_managers external function.
 *
 * @package     block_course_managers
 * @category    external
 * @copyright   2023 Roberto Pinna
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_managers extends external_api {

    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'blockid' => new external_value(PARAM_INT, 'The block instance id', VALUE_REQUIRED),
            'orderby' => new external_value(PARAM_ALPHA, 'Course managers list order', VALUE_REQUIRED),
        ]);
    }

    /**
     * Finds users with the identity matching the given query.
     *
     * @param int $blockid The block instance id.
     * @return array
     */
    public static function execute(int $blockid, string $orderby): array {
        global $DB, $CFG;

        $params = external_api::validate_parameters(self::execute_parameters(), [
            'blockid' => $blockid,
            'orderby' => $orderby,
        ]);

        $managers = array();

        if (isset($CFG->coursecontact) && !empty($CFG->coursecontact)) {

            $order = 'u.lastname ASC, u.firstname ASC';
            if (!empty($orderby) && ($orderby == 'accesstime')) {
                $order = 'u.lastaccess DESC, '. $order;
            }
            $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                    FROM {$CFG->prefix}role_assignments ra,
                         {$CFG->prefix}course c,
                         {$CFG->prefix}context ctx,
                         {$CFG->prefix}user u
                    WHERE ra.userid = u.id
                      AND ra.roleid IN ({$CFG->coursecontact})
                      AND ctx.id = ra.contextid
                      AND ctx.contextlevel = '" . CONTEXT_COURSE . "'
                      AND c.id = ctx.instanceid
                      AND c.visible = 1
                    GROUP BY u.id
                    ORDER BY $order";

            if ($users = $DB->get_records_sql($sql)) {
                foreach ($users as $user) {
                    $manager = new \stdClass();
                    $manager->fullname = fullname($user);
                    $query = array('id' => $user->id, 'b' => $blockid);
                    $manager->url = (string) new \moodle_url($CFG->wwwroot . '/blocks/course_managers/manager.php', $query);

                    $managers[] = $manager;
                }
            }
        }

        return $managers;
    }

    /**
     * Describes the external function result value.
     *
     * @return external_description
     */
    public static function execute_returns() : external_description {
        return new external_multiple_structure(
            new external_single_structure([
                // The output of the {@see fullname()} can contain formatting HTML such as <ruby> tags.
                // So we need PARAM_RAW here and the caller is supposed to render it appropriately.
                'fullname' => new external_value(PARAM_RAW, 'The fullname of the user'),
                'url' => new external_value(PARAM_URL, 'Manager page url.'),
            ])
        );
    }
}
