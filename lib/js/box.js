"use strict";

$(document).ready(function() {
  // function: info_link
  var info_link = function(field) {
    var file = field.json_url;
    var name = field.name;
    var description = field.description_l;
    var provider = field.provider;
    var url = field.url;
    var version = field.version;
    var checksum = field.checksum;
    var checksum_type = field.checksum_type;
    var html_link = '<a title="Show info box" class="button info_btn" data-box=\'';
    html_link += '["' + file + '", "' + name + '", "' + description + '", "' + provider + '", "' + url + '", "' + version + '", "' + checksum + '", "' + checksum_type + '"]';
    html_link += '\'><i class="fas fa-info"></i></a>';
    return html_link
  }
  // function: list_boxes
  var list_boxes = function() {
    $.getJSON('list_all_boxes.php', {_: new Date().getTime()}, function(data) {
      console.log("JSON Data: ", data);
      $.each(data, function(i, field) {
        if (typeof field === 'object') {
          var info = info_link(field);
          var del = '<a title="Delete this box" class="button del_btn" data-name="' + field.name + '" data-url="' + field.json_url + '"><i class="fas fa-trash"></i></a>';
          var $row = $('#boxTable > tbody:last-child').append(
            '<tr>',
            $('<td>').text(field.name),
            $('<td>').text(field.description_s),
            $('<td>').text(field.provider),
            $('<td>').html(info + ' ' + del),
            '</tr>'
          );
        }
      });
    });
  };
  // function: reload_tbody
  var reload_tbody = function() {
    $("#boxTable td").parent().remove();
    $('#boxTable').append("<tbody></tbody>");
    list_boxes();
  }
  // action: delete box
  $('#boxTable').on('click', '.del_btn', function() {
    var box_name = $(this).data("name");
    var box_json = $(this).data("url");
    var result = confirm('You will delete "' + box_name + '"?');
    if (result == true) {
      var url = 'delete.php?name=' + box_name + '&url=' + box_json;
      $.get(url, function(data) {
        console.log('JSON Data', data);
        reload_tbody();
      });
    }
  });
  // action: show/hide info modal
  $('#boxTable').on('click', '.info_btn', function() {
    var box_data = $(this).data("box");
    var box_arr = box_data.toString().split(",");
    var box_desc = '<p>Description: ' + box_arr[2] + "<br>";
    box_desc += 'Provider: ' + box_arr[3] + "<br>";
    box_desc += 'Version: ' + box_arr[5] + "<br><br>";
    box_desc += 'Checksum: ' + box_arr[6] + "<br>";
    box_desc += 'Checksum Type: ' + box_arr[7] + "</p>";
    box_desc += '<div>';
    box_desc += '<h3>Direct Download</h3>';
    box_desc += '<a class="button" href="' + box_arr[0] + '" target="_blank" title="JSON">';
    box_desc += '<i class="fas fa-file"></i></a> ';
    box_desc += '<a class="button" href="' + box_arr[4] + '" target="_blank" title="Box">';
    box_desc += '<i class="fas fa-cube"></i></a>';
    box_desc += '</div>';
    $('#info_modal h2').text(box_arr[1]);
    $('#info_modal p').html(box_desc);
    $('#info_modal').show();
  });
  $('#close_info').click(function() {
    $('#info_modal').hide();
  });
  // action: show/hide add modal
  $('#add_btn').click(function() {
    $('#boxUploadForm').trigger('reset');
    $('#add_modal').show();
  });
  $('#close_add').click(function() {
    $('#add_modal').hide();
  });
  // action: show/hide help modal
  $('#help_btn').click(function() {
    $('#help_modal').show();
  });
  $('#close_help').click(function() {
    $('#help_modal').hide();
  });
  // action: submit formular
  $('form').submit(function(event) {
    event.preventDefault();
    var form = $('#boxUploadForm')[0];
    var form_data = new FormData(form);
    $.ajax({
      type: 'POST',
      enctype: 'multipart/form-data',
      url: 'upload.php',
      processData: false,
      contentType: false,
      cache: false,
      timeout: 600000,
      data: form_data,
      beforeSend: function() {
        $('.button-primary').prop("disabled", true);
      },
      success: function(json) {
        console.log("SUCCESS : ", json);
        if(json.status == true) {
          alert(json.message);
        } else {
          alert('interal error');
        }
      },
      error: function(e) {
        console.log("ERROR : ", e);
      },
      complete: function() {
        $('.button-primary').prop("disabled", false);
        $('#add_modal').hide();
        reload_tbody();
      }
    });
  });
  // action: list_boxes
  list_boxes();
});
