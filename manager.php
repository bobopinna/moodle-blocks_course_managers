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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/tag/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once(__DIR__ . '/locallib.php');

$userid = required_param('id', PARAM_INT);
$blockid = optional_param('b', 0, PARAM_INT);

if (! $user = $DB->get_record('user', array('id' => $userid)) ) {
    throw new moodle_exception('invaliduserid', 'error');
}

if (! $course = $DB->get_record('course', array('id' => SITEID)) ) {
    throw new moodle_exception('invalidcourseid', 'error');
}

if ($managercourses = course_managers_get_courses($user->id)) {
    $displaylist = null;
    if (class_exists('core_course_category')) {
        $displaylist = core_course_category::make_categories_list();
    } else {
        require_once($CFG->libdir. '/coursecatlib.php');
        $displaylist = coursecat::make_categories_list();
    }

    $strcategory = new lang_string("category");
    $strpersonalprofile = get_string('personalprofile');
    $struser = get_string('user');

    $pageurl = new moodle_url('/blocks/course_managers/manager.php', array('id' => $userid, 'b' => $blockid));
    $PAGE->set_url($pageurl);

    $systemcontext = context_system::instance();

    $PAGE->set_context($systemcontext);

    $fullname = fullname($user, has_capability('moodle/site:viewfullnames', $systemcontext));
    $pagetitle = strip_tags($strpersonalprofile.': '. $fullname);

    $PAGE->set_title($pagetitle);
    $PAGE->set_heading($strpersonalprofile.': '.$fullname);

    // Retrieve block instance title.
    $strblocktitle = get_string('coursemanagers', 'block_course_managers');
    if ($configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid))) {
        $config = unserialize(base64_decode($configdata));
        if (isset($config->title) && !empty($config->title)) {
            $strblocktitle = $config->title;
        }
    }

    $PAGE->navbar->add($strblocktitle);
    $PAGE->navbar->add($fullname);

    echo $OUTPUT->header();

    // Get the hidden field list.
    if (has_capability('moodle/user:viewhiddendetails', $systemcontext)) {
        $hiddenfields = array();
    } else {
        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
    }

    if (has_capability('moodle/site:viewuseridentity', $systemcontext)) {
        $identityfields = array_flip(explode(',', $CFG->showuseridentity));
    } else {
        $identityfields = array();
    }

    $currentuser = ($user->id == $USER->id);
    $context = $usercontext = context_user::instance($user->id, MUST_EXIST);

    echo '<div class="userprofile">';

    // Print the standard content of this page, the basic profile info.

    echo $OUTPUT->heading(fullname($user));

    echo '<div class="userprofilebox clearfix">';

    echo '<div class="profilepicture">';
    echo $OUTPUT->user_picture($user, array('size' => 100));
    echo '</div>';

    echo '<div class="descriptionbox"><div class="description">';
    // Print the description.

    if ($user->description && !isset($hiddenfields['description'])) {
        $querysql = array('userid' => $user->id);
        if (!empty($CFG->profilesforenrolledusersonly) && !$currentuser && !$DB->record_exists('role_assignments', $querysql)) {
            echo get_string('profilenotshown', 'moodle');
        } else {
            $user->description = file_rewrite_pluginfile_urls($user->description,
                    'pluginfile.php', $usercontext->id, 'user', 'profile', null);
            $options = array('overflowdiv' => true);
            echo format_text($user->description, $user->descriptionformat, $options);
        }
    }
    echo '</div>';


    // Print all the little details in a list.

    echo '<table class="list">';

    if (! isset($hiddenfields['country']) && $user->country) {
        print_row(get_string('country') . ':', get_string($user->country, 'countries'));
    }

    if (! isset($hiddenfields['city']) && $user->city) {
        print_row(get_string('city') . ':', $user->city);
    }

    if (isset($identityfields['address']) && $user->address) {
        print_row(get_string("address").":", "$user->address");
    }

    if (isset($identityfields['phone1']) && $user->phone1) {
        print_row(get_string("phone").":", "$user->phone1");
    }

    if (isset($identityfields['phone2']) && $user->phone2) {
        print_row(get_string("phone2").":", "$user->phone2");
    }

    if (isset($identityfields['institution']) && $user->institution) {
        print_row(get_string("institution").":", "$user->institution");
    }

    if (isset($identityfields['department']) && $user->department) {
        print_row(get_string("department").":", "$user->department");
    }

    if (isset($identityfields['idnumber']) && $user->idnumber) {
        print_row(get_string("idnumber").":", "$user->idnumber");
    }

    if (isset($identityfields['email']) && ($currentuser || $user->maildisplay == 1
      || has_capability('moodle/course:useremail', $context)
      || ($user->maildisplay == 2 && enrol_sharing_course($user, $USER)))) {
        print_row(get_string("email").":", obfuscate_mailto($user->email, ''));
    }

    if (isset($user->url) && !empty($user->url) && !isset($hiddenfields['webpage'])) {
        $url = $user->url;
        if (strpos($user->url, '://') === false) {
            $url = 'http://'. $url;
        }
        print_row(get_string("webpage") .":", '<a href="'.s($url).'">'.s($user->url).'</a>');
    }

    if (isset($user->icq) && !empty($user->icq) && !isset($hiddenfields['icqnumber'])) {
        print_row(get_string('icqnumber').':', "<a href=\"http://web.icq.com/wwp?uin=".urlencode($user->icq)."\">".
                s($user->icq)." <img src=\"http://web.icq.com/whitepages/online?icq=".urlencode($user->icq).
                "&amp;img=5\" alt=\"\" /></a>");
    }

    if (isset($user->skype) && !empty($user->skype) && !isset($hiddenfields['skypeid'])) {
        if (strpos($CFG->httpswwwroot, 'https:') === 0) {
            // Bad luck, skype devs are lazy to set up SSL on their servers - see MDL-37233.
            $statusicon = '';
        } else {
            $urlsrc = 'http://mystatus.skype.com/smallicon/'.urlencode($user->skype);
            $statusicon = ' '.html_writer::empty_tag('img', array('src' => $urlsrc, 'alt' => get_string('status')));
        }
        print_row(get_string('skypeid').':', '<a href="skype:'.urlencode($user->skype).'?call">'.
                s($user->skype).$statusicon.'</a>');
    }
    if (isset($user->yahoo) && !empty($user->yahoo) && !isset($hiddenfields['yahooid'])) {
        print_row(get_string('yahooid').':',
                '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.urlencode($user->yahoo).'&amp;.src=pg">'.
                s($user->yahoo).' <img src="http://opi.yahoo.com/online?u='.urlencode($user->yahoo).'&m=g&t=0" alt=""></a>');
    }
    if (isset($user->aim) && !empty($user->aim) && !isset($hiddenfields['aimid'])) {
        print_row(get_string('aimid').':', '<a href="aim:goim?screenname='.urlencode($user->aim).'">'.s($user->aim).'</a>');
    }
    if (isset($user->msn) && !empty($user->msn) && !isset($hiddenfields['msnid'])) {
        print_row(get_string('msnid').':', s($user->msn));
    }

    // Print the Custom User Fields.
    print_profile_custom_fields($user->id);

    // Printing tagged interests.
    if (!empty($CFG->usetags)) {
        if ($interests = core_tag_tag::get_item_tags('core', 'user', $user->id)) {
             print_row(get_string('interests') .": ", $interests);
        }
    }

    echo '</table>'; // Field table.

    echo '</div>'; // Description Box.
    echo '</div>'; // Description Box.
    echo '</div>'; // Description Box.

    course_managers_print_reservations($user->id, 86400);

    // Print Courses.
    if (!isset($hiddenfields['mycourses'])) {
        if ($managercourses) {
            $renderer = $PAGE->get_renderer('core', 'course');

            echo $OUTPUT->box_start('courseboxes');
            echo $OUTPUT->heading(get_string('courses'));
            foreach ($managercourses as $managercourse) {
                if (!isset($managercourse->summary)) {
                    $managercourse->summary = '';
                }
                if ($managercourse->category > 0) {
                    $managercourse->summary .= '<br /><p class="category">';
                    $url = new moodle_url('/course/index.php', array ('categoryid' => $managercourse->category));
                    $managercourse->summary .= $strcategory.' <a href="'.$url.'">';
                    $managercourse->summary .= $displaylist[$managercourse->category];
                    $managercourse->summary .= '</a></p>';
                }
                echo $renderer->course_info_box($managercourse);
            }
            echo $OUTPUT->box_end();
        } else {
            echo $OUTPUT->heading(get_string('nocourses'));
        }
    } else {
        echo $OUTPUT->heading(get_string('youcantsee', 'block_course_managers'));
    }

    echo $OUTPUT->footer();
} else {
    throw new moodle_exception('invalidmanagerid', 'block_course_managers');
}
