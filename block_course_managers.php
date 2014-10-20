<?php
    defined('MOODLE_INTERNAL') || die();

    class block_course_managers extends block_base {

        function init() {
            $this->title   = get_string('coursemanagers', 'block_course_managers');
        }

        function has_config() {
            return true;
        }

        function instance_allow_multiple() {
            return false;
        }

        function applicable_formats() {
            return array('site' => true);
        }

        function specialization() {
            if (isset($this->config->title) && !empty($this->config->title)) {
                $this->title = $this->config->title;
            } else {
                $this->title   = get_string('coursemanagers', 'block_course_managers');
            }
        } 

        function instance_allow_config() {
            return true;
        }

        function get_content() {
            global $CFG, $DB, $OUTPUT;

            if ($this->content !== NULL) {
                return $this->content;
            }
 
            $this->content =  new stdClass;
          
            $sql = "SELECT u.id, u.firstname, u.lastname 
                      FROM {$CFG->prefix}role_assignments ra,
                           {$CFG->prefix}course c,
                           {$CFG->prefix}context ctx,
                           {$CFG->prefix}user u
                     WHERE ra.userid = u.id
                       AND ra.roleid IN ({$CFG->coursecontact})
                       AND ctx.id = ra.contextid
                       AND ctx.contextlevel = '".CONTEXT_COURSE."'
                       AND c.id = ctx.instanceid
                       AND c.visible = 1
                     GROUP BY u.id
                     ORDER BY u.lastname, u.firstname";
            
            if ($managers = $DB->get_records_sql($sql)) {
                $page = 1;
                $this->content->text = '<ul id="block-course_managers-page-'.$page.'" class="block-course_managers-page">';
                $this->content->footer = NULL;
                $i = 0;

                $itemperpage = 10;
                if (isset($this->config->itemperpage) && !empty($this->config->itemperpage)) {
                    $itemperpage = $this->config->itemperpage;
                }
                foreach($managers as $manager) {
                    if (($itemperpage > 0) && ($i > 0) && (($i % $itemperpage) == 0) && ($i < count($managers))) {
                        $this->content->text .= '</ul>';
                        $page++;
                        $this->content->text .= '<ul id="block-course_managers-page-'.$page.'" class="block-course_managers-page">';
                    }
                    $link = new moodle_url($CFG->wwwroot . '/blocks/course_managers/manager.php', array('id' => $manager->id, 'b' => $this->instance->id));
                    $this->content->text .= '<li><div class="link"><a href="'.$link.'">'.$manager->lastname.' '.$manager->firstname.'</a></div></li>';
                    $i++;
                }
                $this->content->text .= '</ul>';
                if ($page > 1) {
                    $this->content->footer = '<div id="block-course_managers-pages" style="text-align:center;"></div>';
                    $this->content->footer .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/course_managers/pages.js"></script>';
                    //$this->content->footer .= '<script type="text/javascript">'."\n".'//<![CDATA['."\n".'showPage(1,'.$page.');'."\n".'//]]>'."\n".'</script>';
                    $this->content->footer .= '<script type="text/javascript">'."\n".'<!--'."\n".'showPage(1,'.$page.');'."\n".'-->'."\n".'</script>';
                }
            }
            return $this->content;
        }

        public function get_aria_role() {
            return 'navigation';
        }

    }
?>
