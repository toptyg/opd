<?php

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/../../lib/externallib.php');


class mod_opd_ajax extends external_api {
    public static function get_teachers_suggestions($query) {
        global $DB;

        $items = $DB->get_recordset_sql(
            "SELECT DISTINCT u.id,  u.username ,u.lastname, u.firstname, u.middlename FROM {user} u LEFT JOIN {role_assignments} asg ON asg.userid = u.id WHERE asg.roleid IN (3,4) AND ".$DB->sql_like('u.lastname', ':query', false),
//	"SELECT DISTINCT u.id, u.username, u.lastname, u.firstname, u.middlename FROM {user} as u  WHERE ".$DB->sql_like('u.lastname', ':query', false),
            array('query' => '%'.$query.'%'),
            0,
            20
        );

        $suggestions = [];
        foreach ($items as $item) {
            $suggestions[] = [
                "value" => trim("{$item->lastname} {$item->firstname} {$item->middlename}"),
		"desc"=>  trim("{$item->username}"),
                "data" => $item->id
            ];
        }

        return ['suggestions' => $suggestions];
    }




    public static function get_teachers_suggestions_parameters() {
        return new external_function_parameters(
            ['query' => new external_value(PARAM_TEXT, 'Search query')]
        );
    }

    public static function get_teachers_suggestions_returns() {
        return new external_single_structure(['suggestions' => new external_multiple_structure(
            new external_single_structure(
                [
                    'value' => new external_value(PARAM_TEXT, 'Name of teacher'),
		    'desc' => new external_value(PARAM_TEXT, 'desc of teacher'),
                    'data' => new external_value(PARAM_INT, 'ID of teacher')
                ]
            )
        )]);
    }


    public static function get_students_suggestions_parameters() {
        return new external_function_parameters(
            ['query' => new external_value(PARAM_TEXT, 'Search query')]
        );
    }

    public static function get_students_suggestions_returns() {
        return new external_single_structure(['suggestions' => new external_multiple_structure(
            new external_single_structure(
                [
                    'value' => new external_value(PARAM_TEXT, 'Name of student'),
		    'desc' => new external_value(PARAM_TEXT, 'desc of student'),
                    'data' => new external_value(PARAM_INT, 'ID of student')
                ]
            )
        )]);
    }
}