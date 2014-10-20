<?php
 
    require_once('../../config.php');
    require_once($CFG->dirroot . '/course/lib.php');
    require_once($CFG->dirroot . '/tag/lib.php');
    require_once($CFG->dirroot . '/user/profile/lib.php');
    require_once($CFG->libdir . '/coursecatlib.php');
    require_once('locallib.php');

    $userid = required_param('id', PARAM_INT);
    $blockid = optional_param('b', 0, PARAM_INT);

    if (! $user = $DB->get_record('user', array('id' => $userid)) ) {
        error("No such user in this course");
    }

    if (! $course = $DB->get_record('course', array('id' => SITEID)) ) {
        error("No such course id");
    }

    if ($managercourses = course_managers_get_courses($user->id)) {
        $displaylist = coursecat::make_categories_list();

        $strcategory = new lang_string("category");
        $strpersonalprofile = get_string('personalprofile');
        $struser = get_string('user');

        $pageurl = new moodle_url('/blocks/course_managers/manager.php', array('id'=>$userid, 'b'=>$blockid));
        $PAGE->set_url($pageurl);

        $systemcontext = context_system::instance();

        $PAGE->set_context($systemcontext);

        $fullname = fullname($user, has_capability('moodle/site:viewfullnames', $systemcontext));
        $pagetitle = strip_tags($strpersonalprofile.': '. $fullname);

        $PAGE->set_title($pagetitle);
        $PAGE->set_heading($strpersonalprofile.': '.$fullname);

        /// Retrieve block instance title
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

        /// Get the hidden field list
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

        // Print the standard content of this page, the basic profile info

        echo $OUTPUT->heading(fullname($user));

        echo '<div class="userprofilebox clearfix">';

        echo '<div class="profilepicture">';
        echo $OUTPUT->user_picture($user, array('size'=>100));
        echo '</div>';

        echo '<div class="descriptionbox"><div class="description">';
        // Print the description

        if ($user->description && !isset($hiddenfields['description'])) {
            if (!empty($CFG->profilesforenrolledusersonly) && !$currentuser && !$DB->record_exists('role_assignments', array('userid'=>$user->id))) {
                echo get_string('profilenotshown', 'moodle');
            } else {
                $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php', $usercontext->id, 'user', 'profile', null);
                $options = array('overflowdiv'=>true);
                echo format_text($user->description, $user->descriptionformat, $options);
            }
        }
        echo '</div>';


        // Print all the little details in a list

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

        if (isset($identityfields['email']) and ($currentuser
          or $user->maildisplay == 1
          or has_capability('moodle/course:useremail', $context)
          or ($user->maildisplay == 2 and enrol_sharing_course($user, $USER)))) {
            print_row(get_string("email").":", obfuscate_mailto($user->email, ''));
        }

        if ($user->url && !isset($hiddenfields['webpage'])) {
            $url = $user->url;
            if (strpos($user->url, '://') === false) {
                $url = 'http://'. $url;
            }
            print_row(get_string("webpage") .":", '<a href="'.s($url).'">'.s($user->url).'</a>');
        }

        if ($user->icq && !isset($hiddenfields['icqnumber'])) {
            print_row(get_string('icqnumber').':',"<a href=\"http://web.icq.com/wwp?uin=".urlencode($user->icq)."\">".s($user->icq)." <img src=\"http://web.icq.com/whitepages/online?icq=".urlencode($user->icq)."&amp;img=5\" alt=\"\" /></a>");
        }

        if ($user->skype && !isset($hiddenfields['skypeid'])) {
            if (strpos($CFG->httpswwwroot, 'https:') === 0) {
                // Bad luck, skype devs are lazy to set up SSL on their servers - see MDL-37233.
                $statusicon = '';
            } else {
                $statusicon = ' '.html_writer::empty_tag('img', array('src'=>'http://mystatus.skype.com/smallicon/'.urlencode($user->skype), 'alt'=>get_string('status')));
            }
            print_row(get_string('skypeid').':','<a href="skype:'.urlencode($user->skype).'?call">'.s($user->skype).$statusicon.'</a>');
        }
        if ($user->yahoo && !isset($hiddenfields['yahooid'])) {
            print_row(get_string('yahooid').':', '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.urlencode($user->yahoo).'&amp;.src=pg">'.s($user->yahoo)." <img src=\"http://opi.yahoo.com/online?u=".urlencode($user->yahoo)."&m=g&t=0\" alt=\"\"></a>");
        }
        if ($user->aim && !isset($hiddenfields['aimid'])) {
            print_row(get_string('aimid').':', '<a href="aim:goim?screenname='.urlencode($user->aim).'">'.s($user->aim).'</a>');
        }
        if ($user->msn && !isset($hiddenfields['msnid'])) {
            print_row(get_string('msnid').':', s($user->msn));
        }

        /// Print the Custom User Fields
        print_profile_custom_fields($user->id);

        /// Printing tagged interests
        if (!empty($CFG->usetags)) {
            if ($interests = tag_get_tags_csv('user', $user->id) ) {
                 print_row(get_string('interests') .": ", $interests);
            }
        }

        echo '</table>'; // Field table
       
        echo '</div>'; // Description Box
        echo '</div>'; // Description Box
        echo '</div>'; // Description Box

        if (isset($CFG->block_course_managers_timetableurl) && !empty($CFG->block_course_managers_timetableurl)) {
            // Print Timetable
            $timetable = course_managers_get_timetable($user);
            if (!empty($timetable)) {
                echo $OUTPUT->box_start('timetable');
                echo $OUTPUT->heading(get_string('timetable', 'block_course_managers'));
                echo $timetable;  
                echo $OUTPUT->box_end();
            }
        }

        // Print Reservations 
        $reservations = course_managers_get_reservations($user->id);

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
            $tablecolumns = array ('name', 'startdate', 'location', 'intro','timeopen', 'timeclose');

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
            foreach($reservations as $reservation) {
                $mod = get_coursemodule_from_instance('reservation', $reservation->id, $reservation->course);
                if (empty($mod)) {
                    continue;
                }
                $dimmed = '';
                if (!$mod->visible) {
                    $dimmed = 'dimmed';
                }

                $place = $reservation->location!='0'?format_string(trim($reservation->location)):'';
                $description = format_string(preg_replace('/\n|\r|\r\n/',' ',strip_tags(trim($reservation->intro))));
                $eventdate = userdate($reservation->timestart, get_string('strftimedatetime'));
                $timeopen = '';
                if (!empty($reservation->timeopen)) {
                    $timeopen = userdate($reservation->timeopen, get_string('strftimedatetime'));
                }
                if (!empty($reservation->timeopen) && ($reservation->timeopen > $now)) {
                    $timeopen = html_writer::tag('span', $timeopen, array('class' => 'notopened', 'title' => get_string('notopened', 'reservation')));
                }
                $timeclose = userdate($reservation->timeclose, get_string('strftimedatetime'));
                if ($reservation->timeclose < $now) {
                    $timeclose = html_writer::tag('span', $timeclose, array('class' => 'notopened', 'title' => get_string('closed', 'reservation')));
                }
                $reservationurl =  new moodle_url($CFG->wwwroot.'/mod/reservation/view.php', array('r' => $reservation->id));
                $link = html_writer::tag('a', $reservation->name, array('href' => $reservationurl, 'class' => $dimmed));

                $row = array();
                $context = get_context_instance(CONTEXT_MODULE, $mod->id);
                if ((has_capability('mod/reservation:viewrequest',$context)) || (empty($dimmed))) {
                    $row = array ($link, $eventdate, $place, $description, $timeopen, $timeclose);
                    if (has_capability('mod/reservation:viewrequest',$context)) {
                        $row[] = $DB->count_records('reservation_request', array('reservation' => $reservation->id,'timecancelled' => 0)) .' '. get_string('students');
                    } else if (has_capability('mod/reservation:reserve',$context)) {
                        if ($DB->get_record('reservation_request', array('reservation' => $reservation->id,'userid' => $USER->id,'timecancelled' => 0))) {
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

        // Print Courses 
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
                          $managercourse->summary .= "<br /><p class=\"category\">";
                          $managercourse->summary .= "$strcategory: <a href=\"$CFG->wwwroot/course/category.php?id=$managercourse->category\">";
                          $managercourse->summary .= $displaylist[$managercourse->category];
                          $managercourse->summary .= "</a></p>";
                      }
                      echo $renderer->course_info_box($managercourse);
                }
                echo $OUTPUT->box_end();
            } else {
                echo $OUTPUT->heading(get_string('nocourses'));
            }
        } else {
            echo $OUTPUT->heading(get_string('youcantsee','block_course_managers'));
        }

        echo $OUTPUT->footer();
    } else {
        error('Bad script call');
    }

    function print_row($left, $right) {
        echo "\n<tr><th class=\"label c0\">$left</th><td class=\"info c1\">$right</td></tr>\n";
    }

    function print_profile_custom_fields($userid) {
    global $CFG, $USER, $DB;

    if ($categories = $DB->get_records('user_info_category', null, 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = $DB->get_records('user_info_field', array('categoryid'=>$category->id), 'sortorder ASC')) {
                foreach ($fields as $field) {
                    require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
                    $newfield = 'profile_field_'.$field->datatype;
                    $formfield = new $newfield($field->id, $userid);
                    if ($formfield->is_visible() and !$formfield->is_empty()) {
                        print_row(format_string($formfield->field->name).':', $formfield->display_data());
                        //echo html_writer::tag('dt', format_string($formfield->field->name));
                        //echo html_writer::tag('dd', $formfield->display_data());
                    }
                }
            }
        }
    }
}
?>
