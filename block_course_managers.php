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

            if (isset($CFG->coursecontact) && !empty($CFG->coursecontact)) {          
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
                    $itemperpage = 10;
                    if (isset($this->config->itemperpage) && !empty($this->config->itemperpage)) {
                        $itemperpage = $this->config->itemperpage;
                    }
    
                    $managersdata = '  var managers = Array();'."\n".
                                    '  managers[\'itemperpage\'] = '.$itemperpage.";\n".
                                    '  managers[\'elements\'] = '.count($managers).";\n".
                                    '  managers[\'users\'] = Array()'.";\n"; 

                    $managerslist = html_writer::start_tag('ul', array('class' => 'block-course_managers-page'));
                    $i = 1;
                    foreach($managers as $manager) {
                        $linkurl = new moodle_url($CFG->wwwroot . '/blocks/course_managers/manager.php', array('id' => $manager->id, 'b' => $this->instance->id));
                        
                        $managersdata .= '    managers[\'users\']['.$i.'] = Array();'."\n";
                        $managersdata .= '    managers[\'users\']['.$i.'][\'fullname\'] = \''.htmlentities($manager->lastname.' '.$manager->firstname, ENT_QUOTES).'\';'."\n";
                        $managersdata .= '    managers[\'users\']['.$i.'][\'link\'] = \''.$linkurl.'\';'."\n";
                        $link = html_writer::tag('a', $manager->lastname.' '.$manager->firstname, array('href' => $linkurl));
                        $linkdiv = html_writer::tag('div', $link, array('class' => 'link'));
                        $managerslist .= html_writer::tag('li', $linkdiv, array())."\n";
                        $i++;
                    }
                    $managerslist .= html_writer::end_tag('ul')."\n";
                    $maxheight = $itemperpage * 1.85;
                    $attributes = array();
                    $attributes['id'] = 'block-course_managers-list';
                    $attributes['style'] = 'overflow-y: scroll; max-height: '.$maxheight.'em;';
                    $this->content->text = html_writer::tag('div', $managerslist, $attributes)."\n";
                    if (count($managers)/$itemperpage > 1) {
                        $multipage = '';
                        if (isset($this->config->multipage) && !empty($this->config->multipage)) {
                            $multipage = $this->config->multipage;
                        }

                        $this->content->text .= '<script type="text/javascript">'."\n".'<!--'."\n".$managersdata."\n".'-->'."\n".'</script>';
                        $this->content->text .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/course_managers/pages.js"></script>';
                        switch ($multipage) {
                            case 'letters': 
                               $attributes = array();
                               $attributes['id'] = 'block-course_managers-letters';
                               $this->content->text  = html_writer::tag('div', '', $attributes)."\n".$this->content->text;
                               $this->content->text .= '<script type="text/javascript">'."\n".'<!--'."\n".'generateLetters("", managers);'."\n".'-->'."\n".'</script>';
                            break;
                            case 'pages': 
                               $attributes = array();
                               $attributes['id'] = 'block-course_managers-pages';
                               $this->content->text  = html_writer::tag('div', '', $attributes)."\n".$this->content->text;
                               $this->content->text .= '<script type="text/javascript">'."\n".'<!--'."\n".'showPage(1, managers);'."\n".'-->'."\n".'</script>';
                            break;
                            default:
                               $attributes = array();
                               $attributes['id'] = 'block-course_managers-search-filter';
                               $attributes['type'] = 'text';
                               $attributes['onKeyUp'] = 'showResults(this.value, managers);';
                               $attributes['placeholder'] = get_string('typetofilter', 'block_course_managers');
                               $filterfield = html_writer::empty_tag('input', $attributes);
                               $attributes = array();
                               $attributes['id'] = 'block-course_managers-search';
                               $this->content->text  = html_writer::tag('div', $filterfield, $attributes)."\n".$this->content->text;
                               $this->content->text .= '<script type="text/javascript">'."\n".'<!--'."\n".'document.getElementById("block-course_managers-search").style.display="block";'."\n".'-->'."\n".'</script>';
                            break;
                        }
                    }
                }
            } else {
                $this->content->text = get_string('nocoursecontact', 'block_course_managers');
            }

            return $this->content;
        }

        public function get_aria_role() {
            return 'navigation';
        }

    }
?>
