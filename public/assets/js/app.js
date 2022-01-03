// @ts-nocheck
function call_ajax(type, url, data) {
  if (typeof data === "undefined") {
    data = {};
  }

  return $.ajax({
    type: type,
    url: url,
    data: data,
    dataType: "json",
    cache: false,
  });
}

function pack_dd(data, id, value, defaultSelect) {
  var result = [];

  if (defaultSelect === true) {
    result.push({ value: "", text: "" });
  }

  if (data.length > 0) {
    $.each(data, function(i, v) {
      result.push({ value: v[id], text: v[value] });
    });
  }

  return result;
}

function isChecked(c) {
  if (c === 1) {
    return "checked";
  } else {
    return "";
  }
}
