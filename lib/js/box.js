"use strict";

$(document).ready(function() {
  // function: create new box
  var create_box = function(form_data) {
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
        console.log('POST /api/add: ', json);
        alert(json.message);
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
      },
      complete: function() {
        $('.button-primary').prop("disabled", false);
        $('#add_modal').hide();
        read_all_boxes();
      }
    });
  };

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
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        var $row = $('#boxTable > tbody:last-child').append([
          $('<tr>').append([
            $('<td colspan="4">').text('Internal error: ' + exception)
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
      beforeSend: function() {
        $('#tab1 > ul > li').remove();
        $('#tab4 > ul > li').remove();
      },
      success: function(json) {
        console.log('GET /api/info?name=' + box_name + ': ', json);
        if (json.status) {
          $('#info_modal h2').text(json.name);
          $('#info_modal p').text(json.description);
          // tab 1
          for (var i = 0; i < json['boxes'].length; i++) {
            if (json['boxes'][i].version == json.latestversion) {
              $('#tab1 > strong').text('v.' + json['boxes'][i].version);
              var $content = $('#tab1 > ul').append([
                $('<li>').text(json['boxes'][i].provider),
                $('<li>').text(json['boxes'][i].size),
                $('<li>').text(json['boxes'][i].created),
                $('<li>').text(json['boxes'][i].checksum_type),
                $('<li>').text(json['boxes'][i].checksum)
              ]);
            }
          }
          // tab2
          $('#boxname_put').val(box_name);
          $('#boxprovider_put').val('');
          $('#boxdescription_put').val(json.description);
          // tab3
          var $files = $('#tab3 > p').empty().append([
            $('<a>').attr({
              "class": "button info_btn",
              "title": "open JSON file",
              "href": json.json_url ,
              "target": "_blank"
            }).append([
              $('<i>').attr('class', 'fas fa-file')
            ])
          ]);
          for (var i = 0; i < json['boxes'].length; i++) {
            $files += $('#tab3 > p').append([
              $('<a>').attr({
                "class": "button info_btn",
                "title": "open box version: " + json['boxes'][i].version,
                "href": json['boxes'][i].url,
                "target": "_blank"
              }).append([
                $('<i>').attr('class', 'fas fa-cube')
              ])
            ]);
          }
          // tab 4
          for (var i = 0; i < json['boxes'].length; i++) {
            var $history = $('#tab4 > ul').append([
              $('<li>').text('v.' + json['boxes'][i].version + ' - ' + json['boxes'][i].created)
            ]);
          }
          // tab 5
          $('#tab5 code').text('$ vagrant box add ' + box_name + ' ' + json.json_url);
        } else {
          $('#info_modal h2').text(box_name);
          $('#info_modal p').remove();
          $('.tabs').empty().text('Error: ' + json.message);
        }
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        $('#info_modal h2').text(box_name);
        $('#info_modal p').remove();
        $('.tabs').empty().text('Error: something went wrong!');
      },
      complete: function() {
        $('#info_modal').show();
      }
    });
  };

  // function: update specific box
  var update_box = function(form_data) {
    $.ajax({
      type: 'POST',
      enctype: 'multipart/form-data',
      url: './api/update',
      processData: false,
      contentType: false,
      cache: false,
      timeout: 600000,
      data: form_data,
      beforeSend: function() {
        $('.button-primary').prop("disabled", true);
      },
      success: function(json) {
        console.log('PUT /api/update', json);
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
      },
      complete: function() {
        $('.button-primary').prop("disabled", false);
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
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
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

  // action: submit formular for create box
  $('#boxUploadForm').submit(function(event) {
    event.preventDefault();
    var form = $('#boxUploadForm')[0];
    var form_data = new FormData(form);
    create_box(form_data);
  });

  // action: submit formular for update box
  $('#boxUpdateForm').submit(function(event) {
    event.preventDefault();
    var form = $('#boxUpdateForm')[0];
    var form_data = new FormData(form);
    update_box(form_data);
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

  // action: list_boxes
  read_all_boxes();
});
