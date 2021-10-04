window.analytics = window.analytics || {};
window.analytics.columns = [{
        title: 'Название проекта',
        field: 'project_name',
        headerFilter: true,
        headerFilterPlaceholder: 'Название проекта...',
        width: 350,
        visible: true
    },
    {
        title: 'Код проекта',
        field: 'project_code',
        headerFilter: true,
        headerFilterPlaceholder: 'Код проекта...',
        width: 150,
        visible: true
    },
    {
        title: 'Тип проекта',
        field: 'project_type',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По типу проекта...',
        width: 180,
        visible: true
    },
    {
        title: 'Инициатор',
        field: 'initiator',
        headerFilter: true,
        headerFilterPlaceholder: 'По инициатору...',
        width: 160,
        visible: true
    },
    {
        title: 'ФИО преподавателя',
        field: 'teacher',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По преподавателю...',
        width: 220,
        visible: true
    },
    {
        title: 'ФИО РП',
        field: 'project_manager',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По руководителю проекта...',
        width: 220,
        visible: true
    },
    {
        title: 'Статус проекта',
        field: 'project_status',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По статусу проекта...',
        width: 120,
        visible: true
    },
    {
        title: 'Кол-во участников',
        field: 'students_count',
        headerFilter: true,
        headerFilterPlaceholder: 'По количеству...',
        width: 80,
        visible: true
    },
    {
        title: 'Тип команды',
        field: 'team_type',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По типу...',
        width: 180,
        visible: false
    },
    {
        title: 'Командная оценка за теорию',
        field: 'command_theory_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 120,
        visible: false
    },
    {
        title: 'Командная оценка за практику',
        field: 'command_practice_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 120,
        visible: false
    },
    {
        title: 'Командная самооценка компетенций до',
        field: 'command_competence_before_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 120,
        visible: false
    },
    {
        title: 'Командная самооценка компетенций после',
        field: 'command_competence_after_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 120,
        visible: false
    },

    //  For Collapsed List
    // {
    //   formatter: "responsiveCollapse",
    //   width: 30,
    //   minWidth: 30,
    //   align: "center",
    //   resizable: false,
    //   headerSort: false
    // },

    // TODO: for students column we may use "Collapsed List" or load all students in project with disabling sorting in this column
    {
        title: 'ФИО студента',
        field: 'student',
        headerFilter: true,
        headerFilterPlaceholder: 'По студентам...',
        responsive: 1,
        width: 220,
        visible: false
    },
    {
        title: 'Институт',
        field: 'institute',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По институту...',
        width: 100,
        visible: false
    },
    {
        title: 'institute студента',
        field: 'student_login',
        headerFilter: true,
        headerFilterPlaceholder: 'По institute...',
        width: 220,
        visible: false
    },
    {
        title: 'Специальность студентов',
        field: 'studentsSpecialty',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По специальности...',
        width: 100,
        visible: false
    },
    {
        title: 'Группа',
        field: 'group',
        headerFilter: true,
        headerFilterPlaceholder: 'По группам...',
        width: 100,
        visible: false
    },
    {
        title: 'Эл. почта',
        field: 'email',
        headerFilter: true,
        headerFilterPlaceholder: 'По почте...',
        width: 100,
        visible: false
    },
    {
        title: 'Телефон',
        field: 'phone',
        headerFilter: true,
        headerFilterPlaceholder: 'По телефону...',
        width: 100,
        visible: false
    },
    {
        title: 'Оценка',
        field: 'poll_rating',
        headerFilter: 'autocomplete',
        headerFilterParams: {
            showListOnEmpty: true,
            allowEmpty: true,
            values: true
        },
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Оценка за теорию',
        field: 'theory_poll_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Командная оценка за практику',
        field: 'commandPractice_poll_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Индивидуальная оценка за практику',
        field: 'individualPractice_poll_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Самооценка студентом компетенций до прохождения курса ОПД',
        field: 'competenciesBefore_selfRating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Самооценка студентом компетенций после прохождения курса ОПД',
        field: 'competenciesAfter_selfRating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Оценка работы преподавателя студентами',
        field: 'teacherWork_studentRating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Оценка работы портала студентами',
        field: 'portalWork_studentRating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    },
    {
        title: 'Оценка общей удовлетворенности курсом студентами',
        field: 'courseSatisfaction_rating',
        headerFilter: true,
        headerFilterPlaceholder: 'По оценке...',
        width: 100,
        visible: false
    }
];