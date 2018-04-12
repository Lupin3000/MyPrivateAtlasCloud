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
          for (var i = 0; i < json['boxes'].length; i++) {
            if (json['boxes'][i].version == json.latestversion) {
              var content = 'Provider: ' + json['boxes'][i].provider + '<br>';
              content += 'Checksum Type: ' + json['boxes'][i].checksum_type + '<br>';
              content += 'Checksum: ' + json['boxes'][i].checksum
            }
          }
          var files = '<a class="button info_btn" href="' + json.json_url + '" target="_blank" title="open JSON file">';
          files += '<i class="fas fa-file"></i></a> ';
          for (var i = 0; i < json['boxes'].length; i++) {
            files += '<a class="button info_btn" href="' + json['boxes'][i].url + '" target="_blank" title="open box version: ' + json['boxes'][i].version + '">';
            files += '<i class="fas fa-cube"></i></a>';
          }
          var history = '<p>';
          for (var i = 0; i < json['boxes'].length; i++) {
            history += 'v.' + json['boxes'][i].version + '<br>';
          }
          history += '</p>';
          var example = '$ vagrant box add ' + box_name + ' ' + json.json_url;

          $('#info_modal h2').text(json.name);
          $('#info_modal p').html('Latest version: v.' + json.latestversion + '<br>' + json.description);
          $('#tab1').html(content);
          $('#tab3 p').html(files);
          $('#tab4').html(history);
          $('#tab5 code').text(example);
          $('#boxname_put').val(box_name);
          $('#boxdescription_put').val(json.description);
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

  // action: show/hide help modal
  $('#help_btn').click(function() {
    $('#help_modal').show();
  });
  $('#close_help').click(function() {
    $('#help_modal').hide();
  });

  // action: show/hide add modal for create box
  $('#add_btn').click(function() {
    $('#boxUploadForm').trigger('reset');
    $('#add_modal').show();
  });
  $('#close_add').click(function() {
    $('#add_modal').hide();
  });

  // action: show/hide info modal for read specific box
  $('#boxTable').on('click', '.info_btn', function() {
    var box_name = $(this).data('name');
    read_box(box_name);
  });
  $('#close_info').click(function() {
    $('#info_modal').hide();
  });

  // action: toggle tabs of info modal
  $('.tabs .tab-links a').on('click', function(event) {
    event.preventDefault();
    var currentAttrValue = $(this).attr('href');
    $('.tabs ' + currentAttrValue).show().siblings().hide();
    $(this).parent('li').addClass('active').siblings().removeClass('active');
  });

  // action: delete specific box
  $('#boxTable').on('click', '.del_btn', function() {
    var box_name = $(this).data("name");
    var box_file = $(this).data("box");
    var result = confirm('You will delete "' + box_name + '"?');
    if (result == true) {
      delete_box(box_file);
    }
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

  // action: list_boxes
  read_all_boxes();
});
