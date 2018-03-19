"use strict";

$(document).ready(function() {

  // function: list all boxes
  var list_boxes = function() {
    $.getJSON('./box_all.php', {_: new Date().getTime()}, function(data) {
      console.log('json: ', data);
      $('#boxTable td').parent().remove();
      $('#boxTable').append("<tbody></tbody>");

      $.each(data, function(i, field) {
        if (typeof field === 'object') {
          var btn_info = '<a title="Show info box" class="button info_btn"';
          btn_info += ' data-name="' + field.name + '" data-url="' + field.json_url + '">';
          btn_info += '<i class="fas fa-info"></i></a>';
          var btn_del = '<a title="Delete this box" class="button del_btn"';
          btn_del += ' data-name="' + field.name + '" data-url="' + field.json_url + '">';
          btn_del += '<i class="fas fa-trash"></i></a>';
          var $row = $('#boxTable > tbody:last-child').append(
            '<tr>',
            $('<td>').text(field.name),
            $('<td>').text(field.description),
            $('<td>').text(field.provider),
            $('<td>').html(btn_info + ' ' + btn_del),
            '</tr>'
          );
        }
      });
    });
  };

  // action: delete specific box
  $('#boxTable').on('click', '.del_btn', function() {
    var box_name = $(this).data("name");
    var box_json = $(this).data("url");
    var result = confirm('You will delete "' + box_name + '"?');

    if (result == true) {
      var url = './box_delete.php?name=' + box_name + '&url=' + box_json;
      $.getJSON(url, function(data) {
        console.log('json: ', data);
        list_boxes();
      });
    }
  });

  // action: show/hide info modal
  $('#boxTable').on('click', '.info_btn', function() {
    var content = '';
    var box_name = $(this).data('name');
    var json_url = $(this).data('url');

    $.getJSON('./box_info.php?url=' + json_url, {_: new Date().getTime()}, function(data) {
      console.log('json: ', data);
      content += '<p>Description: ' + data[0].description + '<br>';
      content += 'Provider: ' + data[0].provider + '<br>';
      content += 'Version: ' + data[0].version + '<br><br>';
      content += 'Checksum Type: ' + data[0].checksum_type + '<br>';
      content += 'Checksum :' + data[0].checksum + '</p>';
      content += '<div class="row">';
      content += '<div class="column column-25">';
      content += '<h3>Files</h3>';
      content += '<a class="button" href="' + json_url + '" target="_blank" title="JSON">';
      content += '<i class="fas fa-file"></i></a> ';
      content += '<a class="button" href="' + data[0].url + '" target="_blank" title="Box">';
      content += '<i class="fas fa-cube"></i></a>';
      content += '</div>';
      content += '<div class="column column-75">';
      content += '<h3>Example</h3>';
      content += '<code>$ vagrant box add ' + box_name + ' ' + json_url + '</code>';
      content += '</div>';

      $('#info_modal h2').text(box_name);
      $('#info_content').html(content);
      $('#info_modal').show();
    });
  });
  $('#close_info').click(function() {
    $('#info_modal').hide();
  });

  // action: show/hide add box modal
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
      url: './box_upload.php',
      processData: false,
      contentType: false,
      cache: false,
      timeout: 600000,
      data: form_data,
      beforeSend: function() {
        $('.button-primary').prop("disabled", true);
      },
      success: function(json) {
        console.log('json: ', json);
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
        list_boxes();
      }
    });
  });

  // action: list_boxes
  list_boxes();
});
