require(
    [
        "jquery",
        "xlsx",
        "/mod/opd/js/analytics/tabulator.min.js",
        "/mod/opd/js/chosen.jquery.min.js"
    ],
    function ($, XLSX, Tabulator, Chosen) {
        function analyticLoaderVisibility(visibility) {
            var loaderEl = $('#analytic-tc-loader');
            if (visibility === 'show') {
                $(loaderEl).fadeIn();
            }
            if (visibility === 'hide') {
                $(loaderEl).fadeOut();
            }
        }

        $(document).ready(function () {

            // Table column configurator
            var columnSelector = $('#analytic-columns-select');

            var _CONFIG = {
                height: 566,
                data: [],
                layout: 'fitColumns',
                ajaxURL: 'ajax_stat.php',
                ajaxLoaderLoading: 'Загрузка...',
                ajaxParams: {
                    table_type: window.analytics.table_type || "ratings",
                    stream_id: $(this).val()
                },
                columns: window.analytics.columns || [],
                pagination: 'local',
                paginationSize: 25,
                paginationSizeSelector: [25, 50, 75, 100],
                locale: 'ru-ru',
                langs: {
                    'ru-ru': {
                        'pagination': {
                            'first': 'В начало',
                            'first_title': 'В начало таблицы',
                            'last': 'В конец',
                            'last_title': 'В конец таблицы',
                            'prev': 'Назад',
                            'prev_title': 'Шаг назад',
                            'next': 'Вперед',
                            'next_title': 'Шаг вперед',
                            'page_size': 'Записей на странице'
                        }
                    }
                },
                dataLoaded: function(data) {
                    analyticLoaderVisibility('hide');
                },
                tableBuilt: function() {
                    $.each(this.getColumnDefinitions(), function (key, value) {
                        columnSelector.append($('<option></option>').attr({
                            value: value.field,
                            selected: value.visible
                        }).text(value.title));
                    });
                    // re-init chosen
                    columnSelector.trigger('chosen:updated');
                }
            };

            /*
            ,
             */

            var tabulator = new Tabulator('#analytic-table', _CONFIG);

            // On change event
            columnSelector.on('change', function (evt, params) {
                $.each(tabulator.getColumnDefinitions(), function (key, value) {
                    if (columnSelector.val().indexOf(value.field) !== -1) {
                        tabulator.showColumn(value.field);
                    } else {
                        tabulator.hideColumn(value.field);
                    }
                });
            });

            columnSelector.chosen({
                width: '100%',
                no_results_text: 'Ничего не нашлось по запросу: ',
                // max_selected_options: 6
            });


            // 1.1. Export to cvs logic
            $('#analytic-action-csv').on('click', function (event) {
                event.preventDefault();
                tabulator.download('csv', 'streams.csv', {
                    delimiter: ',',
                    bom: true
                });
            });

            // 1.2. Export to xlsx logic
            $('#analytic-action-xlsx').on('click', function (event) {
                event.preventDefault();
                tabulator.download('xlsx', 'streams.xlsx', {
                    sheetName: 'Streams'
                });
            });

            // 1.3. Export to JSON logic
            $('#analytic-action-json').on('click', function (event) {
                event.preventDefault();
                tabulator.download('json', 'streams.json');
            });

            // Init stream chosen single select, docs: https://harvesthq.github.io/chosen/options.html
            var streamSelect = $('#analytic-stream-select');
            // On first init event
            streamSelect.on('chosen:ready', function (evt, params) {
                tabulator.setData('ajax_stat.php', {
                    table_type: window.analytics.table_type || "ratings",
                    stream_id: $(this).val()
                });
            });
            // On change event
            streamSelect.on('change', function (evt, params) {
                tabulator.setData('ajax_stat.php', {
                    table_type: window.analytics.table_type || "ratings",
                    stream_id: params.selected
                });
            });
            // Init
            streamSelect.chosen({
                width: '100%',
                no_results_text: 'Ничего не нашлось по запросу: '
            });
            /* ---------------------------------------------------------------------------
             *
             * ---------------------------------------------------------------------------
             */
            return;
        });
    });