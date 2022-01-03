// @ts-nocheck
function loadGrid(options) {
  var table = $(options.el).DataTable(options);

  $(options.el + " thead tr:eq(1) th").each(function(i) {
    if (
      options.columns[i].filter === true ||
      typeof options.columns[i].filter === "undefined"
    ) {
      $(this).html('<input type="text" style="width: 100%;" />');
    } else {
      $(this).html(
        '<input type="text" readonly="true" style="width: 100%; background: #eeeeee; border: none;" />'
      );
    }

    $("input", this).on("keyup change", function(e) {
      if (table.column(i).search() !== this.value) {
        if (e.which === 13 || this.value === "") {
          table
            .column(i)
            .search(this.value)
            .draw();
        }
      }
    });
  });

  if (typeof options.modeSelect !== "undefined") {
    if (options.modeSelect === "single") {
      singleSelect(options.el);
    } else if (options.modeSelect === "multiple") {
      multipleSelect(options.el);
    }
  }
}

function singleSelect(selector) {
  $(selector + " tbody").on("click", "tr", function() {
    if (!$(this).hasClass("tb-selected")) {
      $(selector)
        .DataTable()
        .$("tr.tb-selected")
        .removeClass("tb-selected");
      $(this).addClass("tb-selected");
    }
  });
}

function multipleSelect(selector) {
  $(selector + " tbody").on("click", "tr", function() {
    $(this).toggleClass("tb-selected");
  });
}

function clearSelected(selector) {
  $(selector + " tbody tr").removeClass("tb-selected");
}

function rowSelected(selector) {
  var data = [];

  var row_selected = $(selector)
    .DataTable()
    .rows(".tb-selected")
    .data();

  if (typeof row_selected !== "undefined" && row_selected.length > 0) {
    $.each(row_selected, function(i, v) {
      data.push(v);
    });

    return data;
  } else {
    return [];
  }
}

function reloadGrid(selector, resetPage) {
  if (resetPage === true) {
    $(selector)
      .DataTable()
      .ajax.reload("null", true);
  } else {
    $(selector)
      .DataTable()
      .ajax.reload();
  }
}

function rowDoubleClick(selector, el) {
  return $(selector)
    .DataTable()
    .rows(el)
    .data()[0];
}

function isSelectRow(rowdata) {
  if (typeof rowdata === "undefined" || rowdata.length === 0) {
    return false;
  }
  return true;
}

function isNull(data) {
  if (data === null || data === "NULL") {
    return "";
  }
  return data;
}
