<?php

require_once(__DIR__.'/../lib.php');
require_once(__DIR__ . "/report_rules.php");

function opd_report_professionagram_graphdata($stream_id, $record_id, $user_id=null) {
    global $DB;

    $opd_record = $DB->get_record('opd_records', ["opdid" => $stream_id, "id" => $record_id], '*', MUST_EXIST);

    $output = [
        "team" => [
            // TODO Team info fields
        ]
    ];
    $output["team"]["practice"] = opd_name_competencies(opd_calculate_practice_rule($opd_record));

    if ($user_id !== null) {
        $user_listed = $DB->record_exists('opd_records_users', [
            "recordid" => $opd_record->id, "opdid" => $opd_record->opdid, "userid" => $user_id
        ]);

        if (!$user_listed) {
            $user_listed = $DB->record_exists_sql('SELECT
                u.* 
            FROM {opd_records} r
            JOIN {context} c ON r.converted = 1 AND r.converted_courseid = c.instanceid AND c.contextlevel = ?
            JOIN {role_assignments} ra ON c.id = ra.contextid
            JOIN {role} rl ON ra.roleid = rl.id
            JOIN {user} u ON (ra.userid IS NOT NULL AND ra.userid = u.id)
            WHERE r.opdid = ? AND r.id = ? AND u.id = ?', [CONTEXT_COURSE, $opd_record->opdid, $opd_record->id, $user_id]);
        }

        if ($user_listed) {
            $output["personal"] = [
                // TODO Person info fields
            ];
            $output["personal"]["theory"] = opd_name_competencies(opd_calculate_theory_rule($opd_record, $user_id));
            $feedback = opd_get_pm_feedback($opd_record, $user_id);
            $output["personal"]["practice"] = opd_name_competencies(opd_calculate_practice_rule($opd_record, $feedback));
            $output["personal"]["pm_feedback"] = $feedback;
            // TODO Minimum
        }
    }

    return $output;
}

const OPD_STUDENT_ROLES = ['student', 'student-project-manager'];

function opd_report_professionagram_data($stream_id) {
    global $DB;

    $opd_records = $DB->get_recordset_sql('SELECT
            r.opdid,
            r.id recordid,
            r.converted converted,
            CONCAT(CONCAT(r.institute, \'-\'), r.id) projectcode,
            r.projectname,
            rl.shortname rolename,
            IFNULL(rau.id, ruu.id) uid,
            IFNULL(rau.firstname, ruu.firstname) firstname,
            IFNULL(rau.lastname, ruu.lastname) lastname
        FROM {opd_records} r
        LEFT JOIN {context} c ON r.converted = 1 AND r.converted_courseid = c.instanceid AND c.contextlevel = ?
        LEFT JOIN {role_assignments} ra ON c.id = ra.contextid
        LEFT JOIN {role} rl ON ra.roleid = rl.id
        LEFT JOIN {opd_records_users} ru ON r.converted = 0 AND r.id = ru.recordid
        LEFT JOIN {user} rau ON rau.id = ra.userid
        LEFT JOIN {user} ruu ON ruu.id = ru.userid
        WHERE r.opdid = ? AND IFNULL(rau.id, ruu.id) IS NOT NULL
        ORDER BY r.id, IFNULL(rau.lastname, ruu.lastname), IFNULL(rau.firstname, ruu.firstname);', [CONTEXT_COURSE, $stream_id]);

    $projects = [];

    foreach ($opd_records as $record) {
        // Skip anyone but students
        if (array_search($record->rolename, OPD_STUDENT_ROLES) === false) {
            continue;
        }
        if (!key_exists($record->recordid, $projects)) {
            $projects[$record->recordid] = new stdClass();
            $projects[$record->recordid]->rid = $record->recordid;
            $projects[$record->recordid]->code = $record->projectcode;
            $projects[$record->recordid]->projectname = $record->projectname;
            $projects[$record->recordid]->students = [];
        }
        $projects[$record->recordid]->students[$record->uid] = new stdClass();
        $projects[$record->recordid]->students[$record->uid]->uid = $record->uid;
        $projects[$record->recordid]->students[$record->uid]->name = $record->lastname . ' ' . $record->firstname;
    }

    foreach ($projects as $p) {
        $p->students = array_values($p->students);
    }

    return array_values($projects);
}