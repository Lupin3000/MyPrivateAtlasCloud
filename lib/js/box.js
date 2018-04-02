"use strict";

$(document).ready(function() {

  // function: list all vagrant boxes
  var read_all_boxes = function() {
    $.ajax({
      type: 'GET',
      url: './api/list',
      cache: false,
      dataType: 'json',
      timeout: 10000,
      beforeSend: function() {
        $('#boxTable > tbody').remove();
        $('#boxTable').append("<tbody></tbody>");
      },
      success: function(json) {
        console.log('GET /api/list: ', json);
        if (json.status) {
          $.each(json, function(i, field) {
            if (typeof field === 'object') {
              var $row = $('#boxTable > tbody:last-child').append([
                $('<tr>').append([
                  $('<td>').text(field.name),
                  $('<td>').text(field.description),
                  $('<td>').text(field.provider),
                  $('<td>').append([
                    $('<a>').attr({
                      "title": "Show info about box",
                      "class": "button info_btn",
                      "data-name": field.name.replace('/', '_')
                    }).append([
                      $('<i>').attr('class', 'fas fa-info')
                    ]),
                    $('<a>').attr({
                      "title": "Delete this box now",
                      "class": "button del_btn",
                      "data-name": field.name,
                      "data-box": field.box
                    }).append([
                      $('<i>').attr('class', 'fas fa-trash')
                    ])
                  ])
                ]),
              ]);
            }
          });
        } else {
          var $row = $('#boxTable > tbody:last-child').append([
            $('<tr>').append([
              $('<td colspan="4">').text('Request error: ' + json.message)
            ]),
          ]);
        }
      },
      error: function(e) {
        console.log("ERROR : ", e.status + ' ' + e.statusText);
        var $row = $('#boxTable > tbody:last-child').append([
          $('<tr>').append([
            $('<td colspan="4">').text('Internal error: ' + e.status + ' ' + e.statusText)
          ]),
        ]);
      }
    });
  };

  // function: list specifc box info
  var read_box = function(box_name) {
    $.ajax({
      type: 'GET',
      url: './api/info',
      cache: false,
      dataType: 'json',
      timeout: 10000,
      data: {'name': box_name},
      success: function(json) {
        console.log('GET /api/info?name=' + box_name + ': ', json);
        if (json.status) {
          var content = 'Description: ' + json[0].description + '<br>';
          content += 'Provider: ' + json[0].provider + '<br>';
          content += 'Version: v.' + json[0].version + '<br><br>';
          content += 'Checksum Type: ' + json[0].checksum_type + '<br>';
          content += 'Checksum: ' + json[0].checksum;
          var files = '<a class="button" href="' + json[0].json_url + '" target="_blank" title="JSON url">';
          files += '<i class="fas fa-file"></i></a> ';
          files += '<a class="button" href="' + json[0].box_url + '" target="_blank" title="Box url">';
          files += '<i class="fas fa-cube"></i></a>';
          var example = '$ vagrant box add ' + box_name + ' ' + json[0].json_url;
          $('#info_modal h2').text(json[0].name);
          $('#info_modal #tab1').html(content);
          $('#info_modal p').html(files);
          $('#info_modal code').text(example);
          $('#boxname_put').val(box_name);
          $('#boxdescription_put').val(json[0].description);
          $('#info_modal').show();
        } else {
          alert('Request error: ' + json.message);
        }
      },
      error: function(e) {
        console.log("ERROR : ", e.status + ' ' + e.statusText);
      }
    });
  };

  // function: delete specifc box
  var delete_box = function(box_name) {
    $.ajax({
      type: 'GET',
      url: './api/delete',
      cache: false,
      dataType: 'json',
      timeout: 10000,
      data: {'name': box_name},
      success: function(json) {
        console.log('DELETE /api/delete?name=' + box_name + ': ', json);
      },
      error: function(e) {
        console.log("ERROR : ", e.status + ' ' + e.statusText);
      },
      complete: function() {
        read_all_boxes();
      }
    });
  };

  // action: delete specific box
  $('#boxTable').on('click', '.del_btn', function() {
    var box_name = $(this).data("name");
    var box_file = $(this).data("box");
    var result = confirm('You will delete "' + box_name + '"?');
    if (result == true) {
      delete_box(box_file);
    }
  });

  // action: show/hide info modal
  $('#boxTable').on('click', '.info_btn', function() {
    var box_name = $(this).data('name');
    read_box(box_name);
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
      url: './api/add',
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
        alert(json.message);
      },
      error: function(e) {
        console.log("ERROR : ", e);
      },
      complete: function() {
        $('.button-primary').prop("disabled", false);
        $('#add_modal').hide();
        read_all_boxes();
      }
    });
  });

  // action: tabs
  $('.tabs .tab-links a').on('click', function(e) {
    var currentAttrValue = $(this).attr('href');
    $('.tabs ' + currentAttrValue).show().siblings().hide();
    $(this).parent('li').addClass('active').siblings().removeClass('active');
    e.preventDefault();
  });

  // action: list_boxes
  read_all_boxes();
});
