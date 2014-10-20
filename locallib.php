<?php
    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }

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
                ORDER BY categories.sortorder ASC, course.fullname ASC";

       return $DB->get_records_sql($sql);
   }

   function course_managers_get_timetable($user) {
       global $CFG;

       $timetable = '';

       $request = curl_init(); 
       $baseurl = $CFG->block_course_managers_timetableurl;
       curl_setopt($request, CURLOPT_URL, $baseurl); 
       curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); 
   
       $html = curl_exec($request); 
       if (!curl_errno($request)){ 
           if (($end = strpos($html, $user->lastname.' '.$user->firstname)) !== false) {
               $end = $end-2;
               $starttag = 'href="';
               if (($start = strrpos(substr($html, 0, $end), $starttag)) !== false) {
                   $start = $start+strlen($starttag);
                   $baseurl = dirname($baseurl).'/'.substr($html, $start, $end-$start);
                   curl_setopt($request, CURLOPT_URL, $baseurl); 
   
                   $html = curl_exec($request); 
                   if (!curl_errno($request)){ 
                       if (($start = strpos($html, '<table class="orario_timetable">')) !== false) {
                           $endtag = '</table>';
                           if (($end = strpos($html, $endtag, $start)) !== false) {
                               $end = $end+strlen($endtag);
                               $baseurl = dirname($baseurl);
                               $timetable =  str_replace('href="', 'href="'.$baseurl.'/', substr($html,$start,$end-$start));
                           }
                       }
                   }
               }
           }
       } 
       curl_close($request); 

       return $timetable;
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

?>
