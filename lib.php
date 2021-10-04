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


defined('MOODLE_INTERNAL') || die();
//require_once(__DIR__.'/classes/event/course_module_viewed.php');
//require_once(__DIR__.'/classes/event/record_created.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once("$CFG->libdir/formslib.php");

require_once($CFG->libdir.'/adminlib.php');

class opdtemplate_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $customdata = $this->_customdata;
        
        $mform = $this->_form;
        $strrequired = get_string("required");

        $mform->addElement('hidden', 'template_id', null);
        $mform->setType('template_id', PARAM_INT);
 
        $mform->addElement('text', 'type', 'Тип проекта', 'size="20"');
        $mform->addRule('type', $strrequired, 'required');
        $mform->setType('type', PARAM_TEXT);

        $mform->addElement('text', 'course_id', 'ID курса', 'size="5"');
        $mform->addRule('course_id', $strrequired, 'required');
        $mform->setType('course_id', PARAM_INT);

	$mform->addElement('text', 'category_id', 'ID кaтегории', 'size="5"');
        $mform->addRule('category_id', $strrequired, 'required');
        $mform->setType('category_id', PARAM_INT);



        $mform->addElement('textarea', 'description', get_string('description'), 'wrap="virtual" rows="5" cols="20"');
        $mform->setType('description', PARAM_TEXT);

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton-template', ($customdata !== null) ? get_string('save') : get_string('add'));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        if ($customdata !== null) {
            $mform->setDefaults($customdata);
        }
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}



function opd_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_opd into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $opd An object from the form.
 * @param mod_opd_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function opd_add_instance($opd, $mform = null) {
    global $DB;

    $opd->timecreated = time();

    $id = $DB->insert_record('opd', $opd);

    return $id;
}


function opd_add_record($opd, $groupid=0, $context, $fields, $datarecord, $processeddata) {
    global $USER, $DB;

    $cm = get_coursemodule_from_instance('opd', $opd->id);

    $record = new stdClass();
    $record->userid = $USER->id;
    $record->opdid = $opd->id;
    $record->groupid = $groupid;
    $record->timecreated = $record->timemodified = time();
    if (has_capability('mod/opd:approve', $context)) {
        $record->approved = 1;
    } else {
        $record->approved = 0;
    }
    foreach ($processeddata->fields as $fieldname => $field) {
        $record->{$field->field->id} = $datarecord->$fieldname;
    }
    $DB->insert_record('opd_records', $record);



    // Trigger an event for creating this record.
    $event = \mod_opd\event\record_created::create(array(
        'objectid' => $record->id,
        'context' => $context,
        'other' => array(
            'opdid' => $opd->id
        )
    ));
    $event->trigger();

    $course = get_course($cm->course);

    return $record->id;
}


function opd_template_view($opd, $course, $cm, $context)
{
    global $CFG,$DB;

$settingsform = new opdtemplate_form('/mod/opd/view.php?pid='.$opd->id."&type=5");


$table = new html_table('uniqueid');
$table->align = array('left','left','left','left','left','left');
$table->head = array('',
                                    //    get_string('number'),
                                        'Тип проекта',
                                        get_string('course'),
				        get_string('category'),
                                        get_string('description'),
                                        'Действия'
                                        );
        $counter=1;
$rows = $DB->get_records_sql('SELECT * FROM {opd_templates}');



foreach ($rows as $row )
{

$c = $DB->get_record('course', array('id' => $row->course_id));
$content = "&nbsp;<a class='btn btn-success btn-sm' href='view.php?pid=$opd->id&type=5&tedit=$row->id' title='Редактировать'><i class='d-none'>1</i><i class='fa fa-pencil fa-sm'></i></a>";
$content .= "&nbsp;<a class='btn btn-danger btn-sm' href='view.php?pid=$opd->id&type=5&tdelete=$row->id' title='Удалить шаблон курса'><i class='d-none'>2</i><i class='fa fa-trash fa-sm'></i></a>";
$content .= "&nbsp;<a class='btn btn-warning btn-sm' href='/course/view.php?id=$c->id' title='Перейти к шаблону курса' target='_blank'><i class='d-none'>2</i><i class='fa fa-arrow-right fa-sm'></i></a>";

$row1 = array(                 $counter,// $row->id,
                                $row->type,
                                $c->fullname,
			        $row->category_id,
                                $row->description,
                            
//                                $row->ico,
                                //<a href="view.php?id='.$row->id.'&del=1">'.get_string('delete').'</a>',
$content
                                );

        $table->data[] = $row1;
$counter++;
 }

echo html_writer::table($table);
echo '<u>Создать новый шаблон:</u>';

$settingsform->display();

}







function opd_template_edit($opd, $course, $cm, $tedit)
{
    global $CFG, $DB;

$c = $DB->get_record('opd_templates', array('id' => $tedit));

echo "Изменение шаблона проекта (ID: " . $tedit . ")";
$form = new opdtemplate_form('/mod/opd/view.php?pid='.$opd->id."&type=5", [
    'template_id' => $c->id,
    'type' => $c->type,
    'course_id' => $c->course_id,
    'category_id' => $c->category_id,
    'description' => $c->description
]);
$form->display();
}

function opd_template_delete ($pid, $tedit)
{
    global $DB;
    $DB->delete_records('opd_templates', array('id' => $tedit));
}


function opd_template_post($opd, $course, $cm, $context)
{ 
    global $CFG,$DB;

    $settingsform = new opdtemplate_form('/mod/opd/view.php?pid='.$opd."&type=5");
    $fromform = $settingsform->get_data();
    $template = false;
    $newTemplate = false;
    $url = new moodle_url('/mod/opd/view.php', array('pid' => $opd,'type'=>5));

    if ($fromform->template_id !== null) {
        $template = $DB->get_record('opd_templates', ['id' => $fromform->template_id], '*');
    } else if ($DB->record_exists('opd_templates', ['opdid' => $opd, 'type' => $fromform->type])) {
        redirect($url, get_string('template_type_already_exists', 'mod_opd'), null, \core\output\notification::NOTIFY_ERROR);
        return;
    }

    if ($template === false) {
        $newTemplate = true;
        $template= new stdClass();
        $template->opdid = $opd;
    }

    $template->type=$fromform->type;
    $template->course_id=$fromform->course_id;
    $template->category_id=$fromform->category_id;
    $template->description=$fromform->description;

    print_r($template);
    print_r($newTemplate);

    if ($newTemplate) {
        $DB->insert_record('opd_templates', $template);
    } else {
        $DB->update_record('opd_templates', $template);
    }


    redirect($url);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $opd   opd object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.3
 */



function opd_view($opd, $course, $cm, $context) {
    global $CFG;
    require_once($CFG->libdir . '/completionlib.php');

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $opd->id
    );

    $event = \mod_opd\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('opd', $opd);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


/**
 * Updates an instance of the opd in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $opd An object from the form in mod_form.php.
 * @param mod_opd_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function opd_update_instance($opd, $mform = null) {
    global $DB;

    $opd->timemodified = time();
    $opd->id = $opd->instance;

    return $DB->update_record('opd', $opd);
}

/**
 * Removes an instance of the opd from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function opd_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('opd', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('opd', array('id' => $id));

    return true;
}




/**
 * Check whether the current user is allowed to manage the given record considering manageentries capability,
 * opd_in_readonly_period() result, ownership (determined by data_isowner()) and manageapproved setting.
 * @param mixed $record record object or id
 * @param object $opd opd object
 * @param object $context context object
 * @return bool returns true if the user is allowd to edit the entry, false otherwise
 */
function opd_user_can_manage_entry($record, $opd, $context) {
    global $DB;

    if (has_capability('mod/opd:manageentries', $context)) {
        return true;
    }

    // Check whether this activity is read-only at present.
    $readonly = opd_in_readonly_period($opd);

    if (!$readonly) {
        // Get record object from db if just id given like in data_isowner.
        // ...done before calling data_isowner() to avoid querying db twice.
        if (!is_object($record)) {
            if (!$record = $DB->get_record('opd_records', array('id' => $record))) {
                return false;
            }
        }
        if (opd_isowner($record)) {
            if ($record->approved || $record->rejected || $record->converted) {
                return false;
            } else {
                return true;
            }
        }
    }

    return false;
}






/**
 * Check whether the specified database activity is currently in a read-only period
 *
 * @param object $opd
 * @return bool returns true if the time fields in $opd indicate a read-only period; false otherwise
 */
function opd_in_readonly_period($opd) {
    $now = time();
    if (!$opd->timeviewfrom && !$opd->timeviewto) {
        return false;
    } else if (($opd->timeviewfrom && $now < $opd->timeviewfrom) || ($opd->timeviewto && $now > $opd->timeviewto)) {
        return false;
    }
    return true;
}


/**
 * Delete a record entry.
 *
 * @param int $recordid The ID for the record to be deleted.
 * @param object $opd The opd object for this activity.
 * @param int $courseid ID for the current course (for logging).
 * @param int $cmid The course module ID.
 * @return bool True if the record deleted, false if not.
 */
function opd_delete_record($recordid, $opd, $courseid, $cmid) {
    global $DB, $CFG;

    if ($deleterecord = $DB->get_record('opd_records', array('id' => $recordid))) {
        if ($deleterecord->opdid == $opd->id) {
            $DB->delete_records('opd_records', array('id'=>$deleterecord->id));
            $DB->delete_records('opd_records_users', array('recordid'=>$deleterecord->id));

            core_tag_tag::remove_all_item_tags('mod_opd', 'opd_records', $recordid);

            // Trigger an event for deleting this record.
            $event = \mod_opd\event\record_deleted::create(array(
                'objectid' => $deleterecord->id,
                'context' => context_module::instance($cmid),
                'courseid' => $courseid,
                'other' => array(
                    'dataid' => $deleterecord->dataid
                )
            ));
            $event->add_record_snapshot('opd_records', $deleterecord);
            $event->trigger();
            $course = get_course($courseid);
            $cm = get_coursemodule_from_instance('opd', $opd->id, 0, false, MUST_EXIST);

            return true;
        }
    }

    return false;
}



// junk functions
/**
 * takes a list of records, the current moduleinstance, a search string,
 * and mode to display prints the translated template
 *
 * @global object
 * @global object
 * @param string $template
 * @param array $records
 * @param object $opd
 * @param string $search
 * @param int $page
 * @param bool $return
 * @param object $jumpurl a moodle_url by which to jump back to the record list (can be null)
 * @return mixed
 */
function opd_print_template($template, $records, $opd, $search='', $page=0, $return=false, moodle_url $jumpurl=null) {
    global $CFG, $DB, $OUTPUT;

    $cm = get_coursemodule_from_instance('opd', $opd->id);
    $context = context_module::instance($cm->id);

    static $fields = array();
    static $opdid = null;

    if (empty($opdid)) {
        $opdid = $opd->id;
    } else if ($opdid != $opd->id) {
        $fields = array();
    }

    if (empty($fields)) {
        $fieldrecords = opd_get_fields();
        foreach ($fieldrecords as $fieldrecord) {
            $fields[]= opd_get_field($fieldrecord, $opd);
        }
    }

    if (empty($records)) {
        return;
    }

    if (!$jumpurl) {
        $jumpurl = new moodle_url('/mod/opd/view.php', array('pid' => $opd->id));
    }
    $jumpurl = new moodle_url($jumpurl, array('page' => $page, 'sesskey' => sesskey()));

    foreach ($records as $record) {   // Might be just one for the single template

    // Replacing tags
        $patterns = array();
        $replacement = array();

    // Then we generate strings to replace for normal tags
        foreach ($fields as $field) {
            $patterns[]='[['.$field->field->name.']]';
            $replacement[] = highlight($search, $field->display_browse_field($record->id, $template));
        }

        $canmanageentries = has_capability('mod/opd:manageentries', $context);

    // Replacing special tags (##Edit##, ##Delete##, ##More##)
        $patterns[]='##edit##';
        $patterns[]='##delete##';
        if (opd_user_can_manage_entry($record, $opd, $context)) {
            $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/opd/edit.php?d='
                             .$opd->id.'&amp;rid='.$record->id.'&amp;sesskey='.sesskey().'">' .
                             $OUTPUT->pix_icon('t/edit', get_string('edit')) . '</a>';
            $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/opd/view.php?d='
                             .$opd->id.'&amp;delete='.$record->id.'&amp;sesskey='.sesskey().'">' .
                             $OUTPUT->pix_icon('t/delete', get_string('delete')) . '</a>';
        } else {
            $replacement[] = '';
            $replacement[] = '';
        }

        $detailurl = $CFG->wwwroot . '/mod/opd/view.php?p=' . $opd->id . '&amp;rid=' . $record->id;
        $patterns[]='##detailurl##';
        $replacement[] = '<a href="'.$detailurl.'">' .
                             $OUTPUT->pix_icon('t/detail', get_string('detail')) . '</a>';

        $patterns[]='##delcheck##';
        if ($canmanageentries) {
            $replacement[] = html_writer::checkbox('delcheck[]', $record->id, false, '', array('class' => 'recordcheckbox'));
        } else {
            $replacement[] = '';
        }

        $patterns[]='##user##';
        $replacement[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$record->userid.
                               '&amp;course='.$opd->course.'">'.fullname($record).'</a>';

        $patterns[] = '##userpicture##';
        $ruser = user_picture::unalias($record, null, 'userid');
        // If the record didn't come with user opd, retrieve the user from database.
        if (!isset($ruser->picture)) {
            $ruser = core_user::get_user($record->userid);
        }
        $replacement[] = $OUTPUT->user_picture($ruser, array('courseid' => $opd->course));


        $patterns[] = '##timeadded##';
        $replacement[] = userdate($record->timecreated);

        $patterns[] = '##timemodified##';
        $replacement [] = userdate($record->timemodified);

        $patterns[]='##approve##';
        if (has_capability('mod/opd:approve', $context) && (!$record->approved)) {
            $approveurl = new moodle_url($jumpurl, array('approve' => $record->id));
            $approveicon = new pix_icon('t/approve', get_string('approve', 'opd'), '', array('class' => 'iconsmall'));
            $replacement[] = html_writer::tag('span', $OUTPUT->action_icon($approveurl, $approveicon),
                    array('class' => 'approve'));
        } else {
            $replacement[] = '';
        }

        $patterns[]='##disapprove##';
        if (has_capability('mod/opd:approve', $context) && ($record->approved)) {
            $disapproveurl = new moodle_url($jumpurl, array('disapprove' => $record->id));
            $disapproveicon = new pix_icon('t/block', get_string('disapprove', 'opd'), '', array('class' => 'iconsmall'));
            $replacement[] = html_writer::tag('span', $OUTPUT->action_icon($disapproveurl, $disapproveicon),
                    array('class' => 'disapprove'));
        } else {
            $replacement[] = '';
        }

        $patterns[] = '##approvalstatus##';
        if ($record->approved) {
            $replacement[] = get_string('approved', 'opd');
        } else {
            $replacement[] = get_string('notapproved', 'opd');
        }

        if (core_tag_tag::is_enabled('mod_opd', 'opd_records')) {
            $patterns[] = "##tags##";
            $replacement[] = $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('mod_opd', 'opd_records', $record->id), '', 'opd-tags');
        }

        // actual replacement of the tags
        $newtext = str_ireplace($patterns, $replacement, $template);

        // no more html formatting and filtering - see MDL-6635
        if ($return) {
            return $newtext;
        } else {
            echo $newtext;

        }
    }
}


/**
 * Check if a database is available for the current user.
 *
 * @param  stdClass  $opd            database record
 * @param  boolean $canmanageentries  optional, if the user can manage entries
 * @param  stdClass  $context         Module context, required if $canmanageentries is not set
 * @return array                      status (available or not and possible warnings)
 * @since  Moodle 3.3
 */
function opd_get_time_availability_status($opd, $canmanageentries = null, $context = null) {
    $open = true;
    $closed = false;
    $warnings = array();

    if ($canmanageentries === null) {
        $canmanageentries = has_capability('mod/opd:manageentries', $context);
    }

    if (!$canmanageentries) {
        $timenow = time();

        if (!empty($opd->timeavailablefrom) and $opd->timeavailablefrom > $timenow) {
            $open = false;
        }
        if (!empty($opd->timeavailableto) and $timenow > $opd->timeavailableto) {
            $closed = true;
        }

        if (!$open or $closed) {
            if (!$open) {
                $warnings['notopenyet'] = userdate($opd->timeavailablefrom);
            }
            if ($closed) {
                $warnings['expired'] = userdate($opd->timeavailableto);
            }
            return array(false, $warnings);
        }
    }

    // Database is available.
    return array(true, $warnings);
}


/**
 * Can user add more entries?
 *
 * @param object $opd
 * @param stdClass $context
 * @return bool
 */

function opd_search_entries($opd, $cm, $context, $type, $record, $theme) {
    global $DB, $USER;

    $sort = 'timecreated';
    $order = 'DESC';

    $approvecap = has_capability('mod/opd:approve', $context);
    $canmanageentries = has_capability('mod/opd:manageentries', $context);

    // Initialise the first group of params for advanced searches.
    $params = array(); // Named params array.

    // Init some variables to be used by advanced search.
    $advsearchselect = '';
    $advwhere        = '';
    // This is used for the initial reduction of advanced search results with required entries.
    $entrysql        = '';
    $namefields = user_picture::fields('u');
    // Remove the id from the string. This already exists in the sql statement.
    $namefields = str_replace('u.id,', '', $namefields);

    $what = ' DISTINCT r.*, ' . $namefields;
    $count = ' COUNT(r.id) ';
    if ($type == 0) {
        return [array(), 0];
    }
    if ($type == 1) {
        $tables = '{opd_themes} r';
    } else if ($type == 2) {
        $tables = '{opd_records} r';
        $advwhere = ' AND r.approved = 0  AND r.rejected = 0';
    } else if ($type == 3) {
        $tables = '{opd_records} r';
        $advwhere = ' AND r.approved = 1  AND r.rejected = 0';
    } else if ($type == 3) {
        $tables = '{opd_records} r';
        $advwhere = ' AND r.approved = 0  AND r.rejected = 1';
    }
    $where = 'WHERE r.opdid = :opdid ';
    
    $params['opdid'] = $opd->id;
    $sortorder = ' ORDER BY r.id ASC ';

    // To actually fetch the records.

    $fromsql    = "FROM $tables $where $advwhere";

    $sqlselect  = "SELECT $what $fromsql $sortorder";
    $sqlcountselect  = "SELECT $count $fromsql";
    $totalcount = $DB->count_records_sql($sqlcountselect, $params);
    $page = 0;

    if ($record) {     // We need to just show one, so where is it in context?
        $nowperpage = 1;
        // TODO MDL-33797 - Reduce this or consider redesigning the paging system.
        if ($allrecordids = $DB->get_fieldset_sql($sqlselect, $params)) {
            $page = (int)array_search($record->id, $allrecordids);
            unset($allrecordids);
        }
    } else {
        $nowperpage = 10000;
    }

    // Get the actual records.
    if (!$records = $DB->get_records_sql($sqlselect, $params, 0, $nowperpage)) {
        // Nothing to show!
        if ($record) {         // Something was requested so try to show that at least (bug 5132)
            $records = array($record->id => $record);
            $totalcount = 1;
        }
    }

    return [$records, $totalcount];
}

/**
 * Check for required fields, and build a list of fields to be updated in a
 * submission.
 *
 * @param $mod stdClass The current recordid - provided as an optimisation.
 * @param $fields array The field data
 * @param $datarecord stdClass The submitted data.
 * @return stdClass containing:
 * * string[] generalnotifications Notifications for the form as a whole.
 * * string[] fieldnotifications Notifications for a specific field.
 * * bool validated Whether the field was validated successfully.
 * * opd_field_base[] fields The field objects to be update.
 */

function opd_get_fields() {
    $fieldsset = [
        [
            "id" => "institute",
            "type" => "menu",
            "name" => "Институт",
            "description" => "",
            "required" => 1,
            "param1" => "ВШТБ, ВШБиПТ, ГИ, ИКНТ"
        ],
        [
            "id" => "themeid",
            "type" => "menu",
            "name" => "Проект",
            "description" => "",
            "required" => 0,
            "param1" => "1, 2, 3, 4"
        ],
        [
            "id" => "theme",
            "type" => "text",
            "name" => "Название проекта",
            "description" => "",
            "required" => 1,
            "param1" => ""
        ],
        [
            "id" => "projectmanageremail",
            "type" => "text",
            "name" => "E-mail руководителя проекта",
            "description" => "",
            "required" => 1,
            "param1" => ""
        ],
        [
            "id" => "projectmanagername",
            "type" => "text",
            "name" => "ФИО руководителя проекта",
            "description" => "",
            "required" => 1,
            "param1" => ""
        ]
    ];
    $result = [];
    foreach ($fieldsset as $field) {
        $object = new stdClass();
        foreach ($field as $key => $value) {
            $object->$key = $value;
        }
        $result[] = $object;
    }
    return $result;
}


function opd_get_singletemplate() {
    $template = <<<EOT
<div class="defaulttemplate">
    <table class="mod-data-default-template ##approvalstatus##">
        <tbody>
            <tr>
                <td class="template-field cell c0"><strong>Институт:</strong></td>
                <td class="template-token cell c1 lastcol"> [[Институт]]</td>
            </tr>
            <tr>
                <td class="template-field cell c0"><strong>Название проекта:</strong></td>
                <td class="template-token cell c1 lastcol"> [[Название проекта]]</td>
            </tr>
            <tr>
                <td class="template-field cell c0"><strong>Источник проекта:  </strong></td>
                <td class="template-token cell c1 lastcol"> [[Источник проекта]]</td>
            </tr>
            <tr>
                <td class="template-field cell c0"><strong>ИД проекта:  </strong></td>
                <td class="template-token cell c1 lastcol"> [[ИД проекта]]</td>
            </tr>
            <tr>
                <td class="template-field cell c0"><strong>ФИО руководителя проекта:  </strong></td>
                <td class="template-token cell c1 lastcol"> [[ФИО руководителя проекта]]</td>
            </tr>
            <tr>
                <td class="template-field cell c0"><strong>E-mail руководителя проекта:  </strong></td>
                <td class="template-token cell c1 lastcol"> [[E-mail руководителя проекта]]</td>
            </tr>
            <tr class="lastrow">
                <td class="controls template-field cell c0 lastcol" colspan="2">
                    <p> Время добавления: ##timeadded##</p>
                    <p>Пользователь: ##user##</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<p> </p>
EOT;
    return $template;
}


function opd_get_listtemplate() {
    $template = <<<EOT
<div class="defaulttemplate">
    ##delcheck## 
    <table class="mod-data-default-template ##approvalstatus##" cellpadding="3">
        <tbody>
            <tr>
                <td class="template-field cell c0" style="text-align: center;"><strong>Институт:</strong></td>
                <td class="template-field cell c0" style="text-align: center;"><strong>Название проекта: </strong></td>
                <td class="template-field cell c0" style="text-align: center;"><strong>ФИО РП:</strong></td>
                <td class="template-field cell c0" style="text-align: center;"><strong>E-mail РП:</strong></td>
                <td class="template-field cell c0" style="text-align: center;"><strong>Кто добавил:  </strong></td>
                <td class="template-field cell c0" style="text-align: center;"><strong>Время добавления: </strong></td>
                <td></td>
            </tr>
            <tr>
                <td class="template-token cell c1 lastcol" style="text-align: center;">[[Институт]]</td>
                <td class="template-token cell c1 lastcol" style="text-align: left;">[[Название проекта]]</td>
                <td class="template-token cell c1 lastcol" style="text-align: left;">[[ФИО руководителя проекта]]</td>
                <td class="template-token cell c1 lastcol" style="text-align: left;">[[E-mail руководителя проекта]]</td>
                <td class="template-token cell c1 lastcol" style="text-align: left;">##user##</td>
                <td class="template-token cell c1 lastcol" style="text-align: left;">##timeadded##</td>
                <td class="template-token cell c1 lastcol" style="text-align: left;">##detailurl##</td>
            </tr>
            <tr class="lastrow">
                <td class="controls template-field cell c0 lastcol" colspan="8"> ##approve##</td>
            </tr>
        </tbody>
    </table>
</div>
<hr />
EOT;
    return $template;
}

function opd_get_addtemplate() {
    $template = <<<EOT
<table style="border-color: #6600cc; border-style: solid; width: 100%; border-width: 0px;" border="0" rules="rows" cellpadding="3">
    <tbody>
        <tr>
            <td style="height: 30px; vertical-align: middle;">
                <p><strong>Институт:</strong></p>
                <p><em>Выберите название института из списка</em></p>
            </td>
            <td style="height: 30px; vertical-align: top;"> [[Институт]]</td>
        </tr>
        <tr>
            <td style="height: 30px; vertical-align: middle;">
                <p><strong>Проекта:</strong></p>
                <p><em>Выбирите один из предлагаемых проектов, либо введите свое название проекта ниже</em></p>
            </td>
            <td style="height: 30px; vertical-align: top;"> [[Проект]]</td>
        </tr>
        <tr>
            <td style="height: 30px; vertical-align: middle;">
                <p><strong>Название проекта:  </strong></p>
                <p><em>Впишите краткое название проекта (до 10 слов)</em></p>
            </td>
            <td style="height: 30px; vertical-align: top;"> [[Название проекта]]</td>
        </tr>
        <tr>
            <td style="height: 30px; vertical-align: middle;">
                <p><strong>ФИО руководителя проекта:  </strong></p>
                <p><em>Впишите ФИО руководителя проекта (например, Яшин Алексей Валерьевич)<strong> </strong></em></p>
            </td>
            <td style="height: 30px; vertical-align: top;"> [[ФИО руководителя проекта]]</td>
        </tr>
        <tr>
            <td style="height: 30px; vertical-align: middle;">
                <p><strong>E-mail руководителя проекта:</strong></p>
                <p><em>Впишите E-mail руководителя проекта (например, yashin@mail.ru)</em></p>
            </td>
            <td style="height: 30px; vertical-align: top;"> [[E-mail руководителя проекта]]</td>
        </tr>
    </tbody>
</table>
<p> </p>
<p> Удостоверьтесь, что запись попала в список проектов (нажмите "Просмотр списка").</p>
EOT;
    return $template;
}

/**
 * returns a subclass field object given a record of the field, used to
 * invoke plugin methods
 * input: $param $field - record from db
 *
 * @global object
 * @param object $field
 * @param object $opd
 * @param object $cm
 * @return object
 */

function opd_get_field($field, $opd, $cm=null) {
    global $CFG;

    if ($field) {
        require_once('field/'.$field->type.'/field.class.php');
        $newfield = 'opd_field_'.$field->type;
        $newfield = new $newfield($field, $opd, $cm);
        return $newfield;
    }
}


/**
 * @package   mod_data
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opd_field_base {     // Base class for Database Field Types (see field/*/field.class.php)

    /** @var string Subclasses must override the type with their name */
    var $type = 'unknown';
    /** @var object The database object that this field belongs to */
    var $opd = NULL;
    /** @var object The field object itself, if we know it */
    var $field = NULL;
    /** @var object The field object itself, if we know it */
    var $record = NULL;
    /** @var int Width of the icon for this fieldtype */
    var $iconwidth = 16;
    /** @var int Width of the icon for this fieldtype */
    var $iconheight = 16;
    /** @var object course module or cmifno */
    var $cm;
    /** @var object activity context */
    var $context;
    
    var $notify_users=array();

    /** @var priority for globalsearch indexing */
    protected static $priority = self::NO_PRIORITY;
    /** priority value for invalid fields regarding indexing */
    const NO_PRIORITY = 0;
    /** priority value for minimum priority */
    const MIN_PRIORITY = 1;
    /** priority value for low priority */
    const LOW_PRIORITY = 2;
    /** priority value for high priority */
    const HIGH_PRIORITY = 3;
    /** priority value for maximum priority */
    const MAX_PRIORITY = 4;

    /**
     * Constructor function
     *
     * @global object
     * @uses CONTEXT_MODULE
     * @param int $field
     * @param int $opd
     * @param int $cm
     */
    function __construct($field=0, $opd=0, $record=0, $cm=0) {   // Field or opd or both, each can be id or object
        global $DB;

        if (empty($field) && empty($opd)) {
            print_error('missingfield', 'opd');
        }

        if (!empty($field)) {
            if (is_object($field)) {
                $this->field = $field;  // Programmer knows what they are doing, we hope
            }
            if (empty($opd)) {
                print_error('invalidid', 'opd');
            }
        }

        if (empty($this->opd)) {         // We need to define this properly
            if (!empty($opd)) {
                if (is_object($opd)) {
                    $this->opd = $opd;  // Programmer knows what they are doing, we hope
                } else if (!$this->opd = $DB->get_record('opd', array('id'=>$opd))) {
                    print_error('invalidid', 'opd');
                }
            } else {                      // No way to define it!
                print_error('missingdata', 'opd');
            }
        }

        if (empty($this->record)) {         // We need to define this properly
            if (!empty($record)) {
                if (is_object($record)) {
                    $this->record = $record;  // Programmer knows what they are doing, we hope
                } else if (!$this->record = $DB->get_record('opd_records', array('id'=>$record))) {
                    print_error('invalidrecordid', 'opd');
                }
            }
        }

        if ($cm) {
            $this->cm = $cm;
        } else {
            $this->cm = get_coursemodule_from_instance('opd', $this->opd->id);
        }

        if (empty($this->field)) {         // We need to define some default values
            $this->define_default_field();
        }

        $this->context = context_module::instance($this->cm->id);
    }


    /**
     * This field just sets up a default field object
     *
     * @return bool
     */
    function define_default_field() {
        global $OUTPUT;
        if (empty($this->opd->id)) {
            echo $OUTPUT->notification('Programmer error: dataid not defined in field class');
        }
        $this->field = new stdClass();
        $this->field->id = 0;
        $this->field->opdid = $this->opd->id;
        $this->field->type   = $this->type;
        $this->field->param1 = '';
        $this->field->param2 = '';
        $this->field->param3 = '';
        $this->field->name = '';
        $this->field->description = '';
        $this->field->required = false;

        return true;
    }

    /**
     * Set up the field object according to data in an object.  Now is the time to clean it!
     *
     * @return bool
     */
    function define_field($opd) {
        $this->field->type        = $this->type;
        $this->field->opdid      = $this->opd->id;

        $this->field->name        = trim($data->name);
        $this->field->description = trim($data->description);
        $this->field->required    = !empty($data->required) ? 1 : 0;

        if (isset($data->param1)) {
            $this->field->param1 = trim($data->param1);
        }
        if (isset($data->param2)) {
            $this->field->param2 = trim($data->param2);
        }
        if (isset($data->param3)) {
            $this->field->param3 = trim($data->param3);
        }
        if (isset($data->param4)) {
            $this->field->param4 = trim($data->param4);
        }
        if (isset($data->param5)) {
            $this->field->param5 = trim($data->param5);
        }

        return true;
    }







    /**
     * Print the relevant form element in the ADD template for this field
     *
     * @global object
     * @param int $recordid
     * @return string
     */
    function display_add_field($formdata=null) {
        global $DB, $OUTPUT;

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id;
            $content = $formdata->$fieldname;
        } else if ($this->record) {
            $content = $this->record->{$this->field->id};
        } else {
            $content = '';
        }

        // beware get_field returns false for new, empty records MDL-18567
        if ($content===false) {
            $content='';
        }

        $str = '<div title="' . s($this->field->description) . '">';
        $str .= '<label for="field_'.$this->field->id.'"><span class="accesshide">'.$this->field->name.'</span>';
        if ($this->field->required) {
            $image = $OUTPUT->pix_icon('req', get_string('requiredelement', 'form'));
            $str .= html_writer::div($image, 'inline-req');
        }
        $str .= '</label><input class="basefieldinput form-control d-inline mod-data-input" ' .
                'type="text" name="field_' . $this->field->id . '" ' .
                'id="field_' . $this->field->id . '" value="' . s($content) . '" />';
        $str .= '</div>';

        return $str;
    }

    
    /**
     * Display the content of the field in browse mode
     *
     * @global object
     * @param int $recordid
     * @param object $template
     * @return bool|string
     */
    function display_browse_field($recordid, $template) {
        global $DB;

        if ($this->record) {
            return $this->record->{$this->field->id};
        } else if ($recordid) {
            $record = $DB->get_record('opd_records', array('id'=>$recordid));
            return $record->{$this->field->id};
        }
        return false;
    }

    /**
     * Check if a field from an add form is empty
     *
     * @param mixed $value
     * @param mixed $name
     * @return bool
     */
     */
    function name() {
        return get_string('fieldtypelabel', "datafield_$this->type");
    }


    /**
     * @param string $relativepath
     * @return bool false
     */
    function file_ok($relativepath) {
        return false;
    }

    /**
     * Returns the priority for being indexed by globalsearch
     *
     * @return int
     */
    public static function get_priority() {
        return static::$priority;
    }

    /**
     * Returns the presentable string value for a field content.
     *
     * The returned string should be plain text.
     *
     * @param stdClass $content
     * @return string
     */
    public static function get_content_value($content) {
        return trim($content->content, "\r\n ");
    }

    /**
     * Return the plugin configs for external functions,
     * in some cases the configs will need formatting or be returned only if the current user has some capabilities enabled.
     *
     * @return array the list of config parameters
     * @since Moodle 3.3
     */
    public function get_config_for_external() {
        // Return all the field configs to null (maybe there is a private key for a service or something similar there).
        $configs = [];
        for ($i = 1; $i <= 10; $i++) {
            $configs["param$i"] = null;
        }
        return $configs;
    }
}


/**
 * Get all of the record ids from a database activity.
 *
 * @param int    $dataid      The dataid of the database module.
 * @param object $selectdata  Contains an additional sql statement for the
 *                            where clause for group and approval fields.
 * @param array  $params      Parameters that coincide with the sql statement.
 * @return array $idarray     An array of record ids
 */
function opd_get_all_recordids($opdid, $selectopd = '', $params = null) {
    global $DB;
    $initsql = 'SELECT r.id
                  FROM {opd_records} r
                 WHERE r.opdid = :opdid';
    if ($selectopd != '') {
        $initsql .= $selectopd;
        $params = array_merge(array('opdid' => $opdid), $params);
    } else {
        $params = array('opdid' => $opdid);
    }
    $initsql .= ' GROUP BY r.id';
    $initrecord = $DB->get_recordset_sql($initsql, $params);
    $idarray = array();
    foreach ($initrecord as $opd) {
        $idarray[] = $opd->id;
    }
    // Close the record set and free up resources.
    $initrecord->close();
    return $idarray;
}




function opd_create_or_update_theme($opdid, $theme) {
    global $DB;
    $exists = $DB->get_record('opd_themes', array('opdid' => $opdid, 'themeid' => $theme[0]));
    if (!$exists) {
        $themerecord = new stdClass();
        $themerecord->opdid = $opdid;
        $themerecord->themeid = $theme[0];
        unset($theme[0]);
        $i = 1;
        foreach ($theme as $fieldname => $fielddata) {
            $themerecord->{'themefield'.$i} = $fielddata;
            $i++;
        }
        $DB->insert_record('opd_themes', $themerecord);
    } else {
        $themerecord = $exists;
        unset($theme[0]);
        $i = 1;
        $imax = 20;
        foreach ($theme as $fieldname => $fielddata) {
            if ($imax < $i) {
                break;
            }
            if ($fielddata != '') {
                $themerecord->{'themefield'.$i} = $fielddata;
            } else {
                $themerecord->{'themefield'.$i} = NULL;
            }
            $i++;
        }
        return $DB->update_record('opd_themes', $themerecord);
    }
}

function opd_show_theme($opdid, $theme, $opd_record_by_user) {
    global $DB;
    $totalcount = $DB->count_records_sql("SELECT COUNT(r.id) FROM {opd_records} r WHERE opdid = :opdid AND themeid= :themeid AND rejected = 0", array('opdid' => $opdid, 'themeid' => $theme->id));
    $baseurl = new moodle_url('/mod/opd/view.php', array('pid' => $opdid, "type"=>"1"));
    $requesturl = new moodle_url('/mod/opd/view.php', array('pid' => $opdid, 'wt' => $theme->id));
    $html = '
    <div class="row">
        <div class="col-6">
            <div class="row">
                <div class="col-12">
                    <table id="dt_view_2_1" class="table table-striped table-sm" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>ID:</th>
                                <td>'.$theme->themeid.'</td>

                                <td>'.$theme->themefield17.'
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-12 mb-4 ">
                    <h4 class="mb-4" >'.$theme->themefield6.'</h4>';
    
    $disabled = '';
    if ($totalcount > 4) {
        $disabled = ' disabled';
        $requesturl = '#';
        $html .= '
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Внимание!</strong> Подать заявку по данному проекту более невозможно.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';    
    }

    if ($opd_record_by_user >= 2) {
        $disabled = ' disabled';
        $html .= '
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Внимание!</strong> Превышен лимит на подачу заявок с вашей учетной записи.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }

    $html .= '
                    <p>
                        Перед тем, как подать заявку на предлагаемый проект, свяжитесь с Ответственным для уточнения деталей.
                    </p>
                    <div class="pt-3 border-bottom"></div>
                </div>
                <div class="col-5 d-flex align-items-center">
                    <p class="font-weight-bold mb-0">Заявок по данной теме: <span class="text-success">'.$totalcount.'</span></p>
                </div>
                <div class="col-7 text-right">
                    <a href="'.$baseurl.'" class="btn btn-outline-secondary">Вернуться</a>
                    <a href="'.$requesturl.'" class="btn btn-success'.$disabled.'">Подать заявку по данной теме</a>
                </div>
            </div>
        </div>
    </div>';
    return $html;
}


function opd_show_records_rejected($opdid, $canmanageentries=false) {
    global $DB;
    $items = $DB->get_recordset_sql("SELECT r.* FROM {opd_records} r WHERE opdid = :opdid AND rejected = 1", array('opdid' => $opdid));
    $content = '';
    foreach ($items as $record) {
        if ($record->fromopdtheme)  {
            $ip = '<span class="badge badge-primary">П</span>';
        } else {
            $ip = '<span class="badge badge-secondary">И</span>';
        }
        $recordurl = new moodle_url('/mod/opd/view.php', array('pid' => $opdid, 'rid' => $record->id));
        $editrecordurl = new moodle_url('/mod/opd/view.php', array('pid' => $opdid, 'rid' => $record->id, 'edit' => 1));
        $content .= "<tr>...................................";
        $content .= "</tr>";
    }
    $items->close();

    $html = '
        <div class="col-12">
            <table id="dt_view_3" class="table table-striped table-sm" cellspacing="0" width="100%">
                <thead>
                <tr>
        ......
        </tr>
                </thead>
                <tbody>'.$content.'</tbody>
            </table>
        </div>';
    return $html;
}

function opd_request_form_template($opdid, $form_data, $context) {
    global $DB, $PAGE;

    $renderer = $PAGE->get_renderer('mod_opd');

    $project_university_options = [
        'СПбПУ',
        'СурГУ',
    ];

    $project_institute_options = [
	'ИБСиБ',
        'ГИ',
        'ИКНТ',
        'ИММИТ',
        'ИПММ',
        'ИПМЭиТ',
        'ИСИ',
        'ИФНИТ',
        'ИЭ',
        'СурГУ',
    ];

    $project_template_options = $DB->get_records('opd_templates', null, 'type');

    $context->university_options = [];
    foreach ($project_university_options as $u) {
        $context->university_options[] = ['university' => $u, 'selected' => isset($form_data->university) && $form_data->university == $u];
    }

    $context->institute_options_options = [];
    foreach ($project_institute_options as $i) {
        $context->institute_options[] = ['institute' => $i, 'selected' => isset($form_data->institute) && $form_data->institute ==  $i];
    }
    
    $context->templates = [];
    foreach ($project_template_options as $i) {
        $context->templates[] = ['type' => $i->type, 'selected' => isset($form_data->projectcoursetemplate) && $form_data->projectcoursetemplate ==  $i->type];
    }

    if (!isset($context->source)) {
        $context->source = $form_data->source;
    }

    if (!isset($context->customer)) {
        $context->customer = $form_data->customer;
    }

    if (!isset($context->responsible)) {
        $context->responsible = $form_data->responsible;
    }

    $context->name = $form_data->projectname;
    $context->manager = $form_data->projectmanagerfio;
    $context->manager_id = $form_data->projectmanagerid;
    $context->manager_email = $form_data->projectmanageremail;
    $context->manager_group = $form_data->projectmanagergroup;
    $context->mentor = $form_data->projectmentorfio;

    $context->customerid = $form_data->customerid;
    $context->customerfio = $form_data->customerfio;
...

    if (isset($form_data->errors['type'])) {
        $context->type_errors = $form_data->errors['type'];
    }

    $template_html = $renderer->render_from_template('mod_opd/request_form', $context);
    return $template_html;
}

function opd_show_form($opdid, $wanted_theme, $form_data, $disable_submit) {
    global $DB;

    $context = new stdClass();
    $context->title = 'Подача заявки';

    if ($wanted_theme) {
        $context->title .= ' по проекту из списка';
        $context->baseurl = new moodle_url('/mod/opd/view.php', array('pid' => $opdid, "wt"=>$wanted_theme->id));
        $context->fromopdtheme = new stdClass();
        $context->fromopdtheme->id = $wanted_theme->themeid;
        $context->fromopdtheme->url = new moodle_url('/mod/opd/view.php', array('pid' => $opdid, "tid"=>$wanted_theme->id));
    } else {
        $context->baseurl = new moodle_url('/mod/opd/view.php', array('pid' => $opdid));
        $context->type_editable = true;
        
	$context->type_templates = array_values($DB->get_records('opd_templates', ['opdid' => $opdid]));
    }

    $context->disable_submit = $disable_submit;

    return opd_request_form_template($opdid, $form_data, $context);
}

function opd_save_request($form_data) {
    global $DB;
    $form_data->timecreated = time();
    unset($form_data->errors);

    $user = $DB->get_record("user", array('id' => $form_data->projectmanagerid));
    $form_data->projectmanagerfio = trim($user->lastname.' '.$user->firstname.' '.$user->middlename);

    $customer = $DB->get_record("user", array('id' => $form_data->customerid));
    $form_data->customerfio = trim($customer->lastname.' '.$customer->firstname.' '.$customer->middlename);


    $id = $DB->insert_record('opd_records', $form_data);
   
   get_noty_users($id,'proposed');
    return $id;
}



function opd_update_group_user($record, $record_user_id, $record_user_action) {
    global $DB;
    $opd_records_user = $DB->get_record(
        'opd_records_users', 
        array('recordid' => $record->id, 'opdid' => $record->opdid, 'userid' => $record_user_id)
    );
    $opd_records_user_other_project = $DB->get_record_sql(
        'SELECT r.*, ru.id as ruid FROM {opd_records} r LEFT JOIN {opd_records_users} ru ON ru.recordid = r.id WHERE r.opdid = :opdid AND ru.userid = :userid AND r.id != :recordid AND r.rejected = 0', 
        array('opdid' => $record->opdid, 'userid' => $record_user_id, 'recordid' => $record->id)
    );
    if ($opd_records_user_other_project && $opd_records_user_other_project->converted) {
        return false;
    }
    if (!$opd_records_user) {
        if ($record_user_action == 'join') {
            if ($opd_records_user_other_project) {
                $record_data = new stdClass();
                $record_data->id = $opd_records_user_other_project->id;
                $record_data->userscount = $DB->count_records_sql(
                    "SELECT COUNT(ru.id) FROM {opd_records_users} ru WHERE recordid = :recordid", 
                    array('recordid' => $opd_records_user_other_project->id)
                );
                $DB->update_record('opd_records', $record_data);
                $DB->delete_records(
                    'opd_records_users', 
                    array('id' => $opd_records_user_other_project->ruid)
                );
            }
            $data = new stdClass();
            $data->userid = $record_user_id;
            $data->recordid = $record->id;
            $data->opdid = $record->opdid;
            $data->timecreated = time();
            $DB->insert_record('opd_records_users', $data);
        }
    } else {
        if ($record_user_action == 'leave') {
            $DB->delete_records(
                'opd_records_users', 
                array('id' => $opd_records_user->id)
            );
        }
    }
    $record_data = new stdClass();
    $record_data->id = $record->id;
    $record_data->userscount = $DB->count_records_sql(
        "SELECT COUNT(ru.id) FROM {opd_records_users} ru WHERE recordid = :recordid", 
        array('recordid' => $record->id)
    );
    $record_data->userscount += 1;
    $DB->update_record('opd_records', $record_data);
}

/**
 * Print tabs on projectp settings page.
 *
 * @param string $selected - current selected tab.
 */

function opd_print_settings_tabs($selected = 'settings') {
    global $CFG;
    // Print tabs for different settings pages.
    $tabs = array();
    $tabs[] = new tabobject('settings', $CFG->wwwroot.'/admin/settings.php?section=modsettingopd',
        'Настройки', 'Настройки', false);

    $tabs[] = new tabobject('stats', $CFG->wwwroot.'/mod/opd/stats.php',
        'Статистика', 'Статистика', false);


    $tabs[] = new tabobject('sync', $CFG->wwwroot.'/mod/opd/sync.php',
        'Синхронизация', 'Синхронизация', false);

//    $ADMIN->add('reports',
//        new admin_externalpage('tooldisabled_user_cleanup', get_string('pluginname', 'tool_disabled_user_cleanup'),
//        "$CFG->wwwroot/$CFG->admin/tool/disabled_user_cleanup/index.php", 'moodle/site:config'));

    ob_start();
    print_tabs(array($tabs), $selected);
    $tabmenu = ob_get_contents();
    ob_end_clean();

    return $tabmenu;
}


function opd_disband($pid, $rid, $reject=false) {
    global $DB;


    $opd = $DB->get_record('opd_records', ['opdid' => $pid, 'id' => $rid]);
    // TODO Notify stakeholders
    // Get all participants before removing (in case we need to notify them)
    $participants = $DB->get_records('opd_records_users', ['opdid' => $pid, 'recordid' => $rid]);
    // Remove all participant records
    $DB->delete_records('opd_records_users', ['opdid' => $pid, 'recordid' => $rid]);
    // Remove course if converted
    if ($opd->converted) {
        delete_course($opd->converted_courseid, false);
    }
    // Cleanup assignment fields
    $opd->projectmanagerid = 0;
    $opd->projectmanagerfio = null;
    $opd->userscount = 1;
    if ($reject) {
        $opd->approved = 0;
        $opd->timeapproved = 0;
        $opd->rejected = 1;
        $opd->timerejected = time();
$opd->projectname= $opd->projectname.'-(расформирован)';

    } else {
        $opd->approved = 0;
        $opd->timeapproved = 0;


    $DB->update_record('opd_records', $opd);

}

function opd_project_status($opd_record) {
    global $DB;
    if (!$opd_record->converted || $opd_record->converted_courseid === null) {
        return 'Курс не создан';
    }

    $checklists = get_coursemodules_in_course('projectchecklist', $opd_record->converted_courseid);

    $resultcl = null;
    foreach ($checklists as $checklist) {
        if (mb_strpos($checklist->name, 'Завершение проекта') !== false) {
            $resultcl = $checklist;
            break;
        }
    }

    return $checked_item->itemname;
}
