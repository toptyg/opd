 var modalFilterArray = {};
        //User to show the filter modal
function showFilter(e, index) {
   $('.modalFilter').hide();
	$(modalFilterArray[index]).css({ left: 0, top: 0 });
	var th = $(e.target).parent();
	var pos = th.offset();
   //console.log(th);
	$(modalFilterArray[index]).width(th.width() * 2.75);
	$(modalFilterArray[index]).css({ 'left': pos.left, 'top': pos.top });
	$(modalFilterArray[index]).show();
	$('#mask').show();
	e.stopPropagation();
	window.onclick = closeFilter;
}

//This function is to use the searchbox to filter the checkbox
function filterValues(node) {
	var searchString = $(node).val().toLowerCase().trim();
	var rootNode = $(node).parent();
	if (searchString == '') {
		rootNode.find('div').show();
	} else {
		rootNode.find("div").hide();
		$.extend($.expr[":"], {
			"containsIN": function(elem, i, match, array) {
				return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
			}
		});
		rootNode.find("div.modal-footer").show();
		rootNode.find('div:containsIN("'+searchString+'")').show();
	}
}
		
//Execute the filter on the table for a given column
function performFilter(node, i, tableId) {
	var rootNode = $(node).parent().parent();
	var searchString = '', counter = 0;

	rootNode.find('input:checkbox').each(function (index, checkbox) {
		if (checkbox.checked) {
			searchString += (counter == 0) ? checkbox.value : '|' + checkbox.value;
			counter++;
		}
	});

	$('#' + tableId).DataTable().column(i).search(
		searchString,
		true, false
	).draw();
	rootNode.hide();
	$('#mask').hide();
}

//Removes the filter from the table for a given column
function closeFilter(e) {
	var elem = e.srcElement;
	while (elem != null) {
		if (elem.className.includes("modalFilter")) {
			return;
		}
		elem = elem.parentElement;
	}
	$('div.modalFilter').hide();
	$('#mask').hide();
}


function resetFilter(node, i, tableId) {
	clearFilter(node, i, tableId);
	var rootNode = $(node).parent().parent();
	rootNode.find('input:checkbox').each(function (index, checkbox) {
		checkbox.checked = false;
		$(checkbox).parent().show();
	});
	$('#' + tableId).DataTable().column(i).search(
		'',
		true, false
	).draw();
}


//Clears search data
function clearFilter(node, i, tableId) {
	var rootNode = $(node).parent().parent();
	rootNode.find(".filterSearchText").val('');
	filterValues(rootNode.find(".filterSearchText"));
}


function extractFromURLEncoded(from, value) {
	var valueStart = from.indexOf('=', from.indexOf(value)) + 1;
	var valueEnd = from.indexOf('&', valueStart);
	valueEnd = (valueEnd === -1 ? from.length : valueEnd);
	return from.substr(valueStart, valueEnd - valueStart);
}


function autocompleteAjaxAlteration(jqXHR, settings) {
	var newData = [{
		index: 0,
		methodname: extractFromURLEncoded(settings.data, 'methodname'),
		args: {
			query: decodeURIComponent(extractFromURLEncoded(settings.data, 'query')).toString().trim()
		}
	}];
	settings.data = JSON.stringify(newData);
}

function transformAjaxResult(response) {
	return response[0].data;
}

function recordCompleteFormatResult(suggestion, currentValue) {
	return '<div class="record-customer-suggestion"><span>' + suggestion.value + "</span><br><code>" + suggestion.desc + "</code></div>";
}

function buildCompleteOnInputListener(jqSelector) {
	return function(e) { if (e.target.value.toString().trim().length === 0) { $(jqSelector).val(0); }};
}

require(["core/config", 'jquery', "/mod/opd/js/datatables.min.js", "/mod/opd/js/jquery.autocomplete.min.js", "/mod/opd/js/chosen.jquery.min.js"], function(cfg, $) {
  
  $(document).ready(function () {

    // View 2. -------------------------------------------------------------------
    $('#dt_view_2').DataTable({
      lengthMenu: [50, 75, 100],
      pagingType: "full_numbers",
      // Translate
      language: {
        "lengthMenu": "Показывать _MENU_ записей на странице",
        "zeroRecords": "К сожалению, ничего найти не удалось",
        "info": "Страница _PAGE_ из _PAGES_",
        "infoEmpty": "Нет совпадений в полях элементов таблицы",
        "search": "Поиск по таблице:",
        "infoFiltered": "(всего _MAX_)",
        "paginate": {
          "first": "<<",
          "last": ">>",
          "next": ">",
          "previous": "<"
        },
      },
      // Columns options
      columns: [
        null,
        null,
        {
          width: '14%'
        },
        null,
        {
          width: '28%'
        },
        {
          orderable: false,
          searchable: false
        }
      ],

                initComplete: function () {
                    configFilter(this, [ 1, 2, 3]);
                }
    });
    //$('.dataTables_length').addClass('bs-select');
          function configFilter($this, colArray) {
            setTimeout(function () {
                var tableName = $this[0].id;
                var columns = $this.api().columns();
                $.each(colArray, function (i, arg) {
                    $('#' + tableName + ' th:eq(' + arg + ')').append('<img src="/mod/opd/pix/38556.png" class="filterIcon" onclick="showFilter(event,\'' + tableName + '_' + arg + '\')" />');
                });

                var template = '<div class="modalFilter new">' +
				'<input style="vertical-align:baseline;" type="text" class="filterSearchText" onkeyup="filterValues(this)" ><img src="/mod/opd/pix/clear.png" style="margin-left:-26px" onclick="clearFilter(this, {1}, \'{2}\');" /></input> <br/>' +
                                 '<div class="modal-content">' +
                                 '{0}</div>' +
                                 '<div class="modal-footer">' +
									 '<a href="#!" onclick="resetFilter(this, {1}, \'{2}\');"  class=" btn left waves-effect waves-light">Сбросить</a>' +
                                     '<a href="#!" onclick="performFilter(this, {1}, \'{2}\');"  class=" btn right waves-effect waves-light">Применить</a>' +
                                 '</div>' +
                             '</div>';
				
                // var uniqueValues = [];				
				// for (var i = 0; i < colArray.length; i++) {
					// var rowArray = colArray[i].split(", ");
					// for (var j = 0; j < rowArray.length; j++) {
						// if (!uniqueValues.includes(rowArray[j])) {
							// uniqueValues.push(rowArray[j]);
						// }
					// }
				// }
				
                $.each(colArray, function (index, value) {
                    columns.every(function (i) {
                        if (value === i) {
                            var column = this, content = '';
                            var columnName = $(this.header()).text().replace(/\s+/g, "_");
                            var distinctArray = [];
							var readyNames = [];
                            column.data().each(function (d, j) {
								
								var rowArray = d.split(", ");
								for (var j = 0; j < rowArray.length; j++) {
									var lowerName = rowArray[j].toLowerCase();
									if (!distinctArray.includes(lowerName)) {
										var capitalRow = rowArray[j].charAt(0).toUpperCase() + rowArray[j].slice(1);
										var id = tableName + "_" + columnName + "_" + j;
										readyNames.push({id: id, name: capitalRow});
										// onchange="formatValues(this,' + value + ');
										
										distinctArray.push(lowerName);
									}
								}
								
                                // if (distinctArray.indexOf(d) == -1) {
                                    // var id = tableName + "_" + columnName + "_" + j; // onchange="formatValues(this,' + value + ');
                                    // content += '<div><input type="checkbox" value="' + d + '"  id="' + id + '"/><label for="' + id + '"> ' + d + '</label></div>';
                                    // distinctArray.push(d);
                                // }
                            });
							
							readyNames = readyNames.sort((a, b) => {
								if (a.name > b.name) {
									return 1;
								}
								if (a.name == b.name) {
									return 0;
								}
								if (a.name < b.name) {
									return -1;
								}
							});
							
							for (var i = 0; i < readyNames.length; i++) {
								content += '<div><input type="checkbox" value="' + readyNames[i].name + '"  id="' + readyNames[i].id + '"/><label for="' + readyNames[i].id + '"> ' + readyNames[i].name + '</label></div>';
							}
							
                            var newTemplate = $(template.replace('{0}', content).replaceAll('{1}', value).replaceAll('{2}', tableName));
                            $('body').append(newTemplate);
							//$($this).append(newTemplate);
                            modalFilterArray[tableName + "_" + value] = newTemplate;
                            content = '';
                        }
                    });
                });
            }, 50);
        }
		
    // View 3. -------------------------------------------------------------------
    $('#dt_view_3').DataTable({
      lengthMenu: [50, 75, 100],
      pagingType: "full_numbers",
      // Translate
      language: {
        "lengthMenu": "Показывать _MENU_ записей на странице",
        "zeroRecords": "К сожалению, ничего найти не удалось",
        "info": "Страница _PAGE_ из _PAGES_",
        "infoEmpty": "Нет совпадений в полях элементов таблицы",
        "search": "Поиск по таблице:",
        "infoFiltered": "(всего _MAX_)",
        "paginate": {
          "first": "<<",
          "last": ">>",
          "next": ">",
          "previous": "<"
        },
        select: {
          rows: {
            _: "Выбрано строк: %d",
            0: "Нажмите на строку для выделения",
            1: "Выбрана 1 строка"
          }
        }
      },
      // Columns options
      columns: [
        null,
        null,
        null,
        null,
        {
          width: '17%'
        },
        {
          width: '8%'
        },
        null,
        {
          orderable: false,
          searchable: false
        }
      ]
    });
  
    // View 3.1 ------------------------------------------------------------------
    $('#dt_view_3_1').DataTable({
      lengthMenu: [50, 75, 100],
      pagingType: "full_numbers",
      // Translate
      language: {
        "lengthMenu": "Показывать _MENU_ записей на странице",
        "zeroRecords": "К сожалению, ничего найти не удалось",
        "info": "Страница _PAGE_ из _PAGES_",
        "infoEmpty": "Нет совпадений в полях элементов таблицы",
        "search": "Поиск по таблице:",
        "infoFiltered": "(всего _MAX_)",
        "paginate": {
          "first": "<<",
          "last": ">>",
          "next": ">",
          "previous": "<"
        },
      },
      // Columns options
      columns: [
        null,
        null,
        null,
        null,
        {
          width: '17%'
        },
        {
          width: '8%'
        },
        null,
        {
          orderable: false,
          searchable: false
        }
      ]
    });
  
    // View 4 --------------------------------------------------------------------
    $('#dt_view_4').DataTable({
      lengthMenu: [50, 75, 100],
      pagingType: "full_numbers",
      // Translate
      language: {
        "lengthMenu": "Показывать _MENU_ записей на странице",
        "zeroRecords": "К сожалению, ничего найти не удалось",
        "info": "Страница _PAGE_ из _PAGES_",
        "infoEmpty": "Нет совпадений в полях элементов таблицы",
        "search": "Поиск по таблице:",
        "infoFiltered": "(всего _MAX_)",
        "paginate": {
          "first": "<<",
          "last": ">>",
          "next": ">",
          "previous": "<"
        },
      },
      // Columns options
      columns: [
        null,
        null,
        null,
        null,
        {
          width: '17%'
        },
        {
          width: '8%'
        },
        null,
        {
          width: '8%'
        },
        {
          // orderable: false,
          searchable: false
        }
      ]
    });

    // This piece of code use this lib: https://github.com/devbridge/jQuery-Autocomplete
    // Autocomplete for "RP" -----------------------------------------------------
    $('#rp-autocomplete-el').autocomplete({
      minChars: 2,
      maxHeight: 200,
      serviceUrl: cfg.wwwroot + "/lib/ajax/service.php?sesskey=" + cfg.sesskey + "&info=mod_opd_get_students_suggestions",
      type: "POST",
      params: {methodname: "mod_opd_get_students_suggestions"},
      ajaxSettings: {
        beforeSend: autocompleteAjaxAlteration,
        contentType: 'application/json'
      },
      transformResult: transformAjaxResult,
      dataType: 'json',
      deferRequestBy: 300, // ms
      showNoSuggestionNotice: true, // Show text if no results
      noSuggestionNotice: 'Пользователь не найден',
      onSelect: function (suggestion) {
        // Set user ID to hidden field
        $('#rp-autocomplete-id').val(suggestion.data)
      },
      formatResult: recordCompleteFormatResult
    }).on('input', buildCompleteOnInputListener("#rp-autocomplete-id"));


    // Autocomplete for "TEACHER" ------------------------------------------------
    $('#teacher-autocomplete-el').autocomplete({
      minChars: 2,
      maxHeight: 200,
      serviceUrl: cfg.wwwroot + "/lib/ajax/service.php?sesskey=" + cfg.sesskey + "&info=mod_opd_get_teachers_suggestions",
      type: "POST",
      params: {methodname: "mod_opd_get_teachers_suggestions"},
      ajaxSettings: {
        beforeSend: autocompleteAjaxAlteration,
        contentType: 'application/json'
      },
      transformResult: transformAjaxResult,
      dataType: 'json',
      deferRequestBy: 300, // ms
      showNoSuggestionNotice: true, // Show text if no results
      noSuggestionNotice: 'Пользователь не найден',
      onSelect: function (suggestion) {
        // Set user ID to hidden field
        $('#teacher-autocomplete-id').val(suggestion.data)
      },
      formatResult: recordCompleteFormatResult
    }).on('input', buildCompleteOnInputListener("#teacher-autocomplete-id"));

    $('#customer-autocomplete-el').autocomplete({
      minChars: 2,
      maxHeight: 200,
      serviceUrl: cfg.wwwroot + "/lib/ajax/service.php?sesskey=" + cfg.sesskey + "&info=mod_opd_get_teachers_suggestions",
      type: "POST",
      params: {methodname: "mod_opd_get_teachers_suggestions"},
      ajaxSettings: {
        beforeSend: autocompleteAjaxAlteration,
        contentType: 'application/json'
      },
      transformResult:transformAjaxResult,
      dataType: 'json',
      deferRequestBy: 300, // ms
      showNoSuggestionNotice: true, // Show text if no results
      noSuggestionNotice: 'Пользователь не найден',
      onSelect: function (suggestion) {
        // Set user ID to hidden field
        $('#customer-autocomplete-id').val(suggestion.data)
      },
      formatResult: recordCompleteFormatResult
    }).on('input', buildCompleteOnInputListener("#customer-autocomplete-id"));

  
    // Multi select for project type ---------------------------------------------
    var projectTypeMSelectEl = $('#project-type-mselect');
    projectTypeMSelectEl.chosen({
      disable_search_threshold: 10,
      width: "100%"
    });
    projectTypeMSelectEl.on('change', function(evt, params) {
      $('#project-type-mselect-hidden').val(projectTypeMSelectEl.val().join(', '))
    });
  });
  
});
