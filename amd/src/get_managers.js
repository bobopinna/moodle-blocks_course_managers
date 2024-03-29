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

import {call as fetchMany} from 'core/ajax';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

const Selectors = {
    filter: "#block-course_managers-search-filter",
    list: "#block-course_managers-list",
};

var nothingString = null;

export const init = async(blockId, orderBy) => {
    Templates.appendNodeContents(Selectors.list, '<i class="fa-solid fa-circle-notch fa-spin fa-xl"></i>', '');

    const managers = await getManagers(blockId, orderBy);
    nothingString = await getString('nothingtodisplay', 'block_course_managers');

    if (managers.length > 0) {
        displayManagers(managers, Selectors.list, null);

        document.addEventListener("keyup", e => {
            if (e.target.closest(Selectors.filter)) {
                displayManagers(managers, Selectors.list, Selectors.filter);
            }
        });
    } else {
        document.querySelectorAll(Selectors.list + " *").forEach(n => n.remove());
        Templates.appendNodeContents(Selectors.list, '<i>' + nothingString + '</i>', '');
    }
};

/**
 * Load the list of managers.
 *
 * @param {Number} blockId Current block instance id.
 * @param {String} orderBy Course managers list order.
 */
export const getManagers = (blockId, orderBy) => {
    const managers = fetchMany([{
        methodname: 'block_course_managers_get_managers',
        args: {blockid: blockId, orderby: orderBy},
    }], true, false);
    return managers[0];
};

/**
 * Display the list of filtered managers.
 *
 * @param {Array} managers The list of site managers.
 * @param {String} listSelector The HTML element selector for output the list.
 * @param {String} filterSelector The HTML element selector to get filter value.
 **/
const displayManagers = (managers, listSelector, filterSelector) => {
    let query = null;
    if (filterSelector !== null) {
        query = document.querySelector(filterSelector).value;
    }

    if (managers.length > 0) {
        let counter = 0;
        managers.forEach(manager => {
            if (counter == 0) {
                document.querySelectorAll(listSelector + " *").forEach(n => n.remove());
                Templates.appendNodeContents(listSelector, '<ul></ul>', '');
            }
            if ((query === null) || manager.fullname.toLowerCase().includes(query.toLowerCase())) {
                counter++;
                Templates.renderForPromise('block_course_managers/manager_element', manager)
                .then(({html, js}) => {
                    Templates.appendNodeContents(listSelector + ' ul', html, js);
                })
                .catch((error) => displayException(error));
            }
        });
        if (counter == 0) {
            Templates.appendNodeContents(listSelector, '<i>' + nothingString + '</i>', '');
        }
    }
};
