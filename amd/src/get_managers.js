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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provides the list of courses managers.
 *
 * @module      block_course_managers/get_managers
 * @copyright   2023 Roberto Pinna
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {render as renderTemplate} from 'core/templates';

const Selectors = {
    filter: "block-course_managers-search-filter",
    list: "block-course_managers-list",
};

export const init = (blockId) => {
    document.addEventListener('DOMContentLoaded', () => {
        const filter = document.querySelector(Selectors.filter);

        filter.addEventListener("keyup", () => {
            getManagers(Selectors.list, filter.value, blockId);
        });

        getManagers(Selectors.list, null, blockId);
    });
};

/**
 * Load the list of managers matching the query and render the list for them.
 *
 * @param {String} selector The list selector.
 * @param {String} query The query string.
 * @param {Integer} blockId Current block insance id.
 */
export async function getManagers(selector, query, blockId) {

    const request = {
        methodname: 'block_course_managers_get_managers',
        args: {
            query: query,
            blockid: blockId,
        }
    };

    try {
        const response = await Ajax.call([request])[0];

        let managers = [];
        response.list.forEach(manager => {
            managers.push(renderTemplate('block_course_managers/manager_element', manager));
        });
        managers = await Promise.all(managers);

        document.querySelector(selector).innerHTML = managers.join("\n");

    } catch (e) {
        window.console.log('Communication error retrieving courses managers');
    }
}
