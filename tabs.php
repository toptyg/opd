<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2005 Martin Dougiamas  http://dougiamas.com             //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

// This file to be included so we can assume config.php has already been included.
// We also assume that $user, $course, $currenttab have been set
global $DB;

    if (empty($currenttab) or empty($opd) or empty($course)) {
        print_error('cannotcallscript');
    }

    $context = context_module::instance($cm->id);

    $row = array();

    if (isloggedin()) { // just a perf shortcut
        if (opd_user_can_add_entry($opd, $context)) { // took out participation list here!
            $row[] = new tabobject('add', new moodle_url('/mod/opd/view.php', array('pid' => $opd->id)), get_string('add','opd'));
        }
    }
    $themescount = $DB->count_records_sql("SELECT COUNT(t.id) FROM {opd_themes} t WHERE opdid = :opdid", array('opdid' => $opd->id));
    $newcount = $DB->count_records_sql("SELECT COUNT(r.id) FROM {opd_records} r WHERE opdid = :opdid AND approved = 0 AND rejected = 0", array('opdid' => $opd->id));
    $approvedcount = $DB->count_records_sql("SELECT COUNT(r.id) FROM {opd_records} r WHERE opdid = :opdid AND approved = 1 AND rejected = 0", array('opdid' => $opd->id));
    $rejectedcount = $DB->count_records_sql("SELECT COUNT(r.id) FROM {opd_records} r WHERE opdid = :opdid AND approved = 0 AND rejected = 1", array('opdid' => $opd->id));
    $templatescount = $DB->count_records_sql("SELECT COUNT(r.id) FROM {opd_templates} r WHERE opdid = :opdid", array('opdid' => $opd->id));
    
    $row[] = new tabobject('list_themes', new moodle_url('/mod/opd/view.php', array('pid' => $opd->id, 'type' => 1)), 'Предлагаемые темы проектов&nbsp;<span class="badge badge-pill badge-info">'.$themescount.'</span>', 'Предлагаемые темы проектов');
    $row[] = new tabobject('list_new', new moodle_url('/mod/opd/view.php', array('pid' => $opd->id, 'type' => 2)), 'Ждут утверждения&nbsp;<span class="badge badge-pill badge-warning">'.$newcount.'</span>', 'Ждут утверждения');
    $row[] = new tabobject('list_approved', new moodle_url('/mod/opd/view.php', array('pid' => $opd->id, 'type' => 3)), 'Утвержденные заявки&nbsp;<span class="badge badge-pill badge-success">'.$approvedcount.'</span>', 'Утвержденные заявки');
    if ($canmanageentries) {
    $row[] = new tabobject('list_rejected',new moodle_url('/mod/opd/view.php', array('pid' => $opd->id, 'type' => 4)), 'Отклоненные заявки&nbsp;<span class="badge badge-pill badge-danger">'.$rejectedcount.'</span>','Отклоненные заявки');
    $row[] = new tabobject('list_templates', new moodle_url('/mod/opd/view.php', array('pid' => $opd->id, 'type' => 5)), 'Шаблоны проектов&nbsp;<span class="badge badge-pill badge-danger">'. $templatescount.'</span>','Шаблоны проектов');
    }


// Print out the tabs and continue!
    echo $OUTPUT->tabtree($row, $currenttab);


