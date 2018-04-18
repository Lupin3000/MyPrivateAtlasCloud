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
        $('.load').show();
      },
      success: function(json) {
        console.log('POST /api/add: ', json);
        alert(json.message);
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        alert('Oops, something went wrong! Please look at the logs.');
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
        $('.load').show();
      },
      success: function(json) {
        console.log('GET /api/list: ', json);
        if (json.status) {
          $('#boxTable > tbody').remove();
          $('#boxTable').append("<tbody></tbody>");
          if (json['boxes'].length == 0) {
            var $row = $('#boxTable > tbody:last-child').append([
              $('<tr>').append([
                $('<td>').text('Now it\'s time for your first box!').attr({
                  "colspan": 4
                })
              ])
            ]);
          }
          $.each(json['boxes'], function(i, field) {
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
                      "data-box": field.name.replace('/', '_')
                    }).append([
                      $('<i>').attr('class', 'fas fa-trash')
                    ])
                  ])
                ]),
              ]);
            }
          });
        } else {
          alert(json.message);
        }
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        alert('Oops, something went wrong! Please look at the logs.');
      },
      complete: function() {
        $('.load').hide();
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
        $('.load').show();
      },
      success: function(json) {
        console.log('GET /api/info?name=' + box_name + ': ', json);
        if (json.status) {
          $('#info_modal h2').text(json.name);
          $('#info_modal p').text(json.description);
          // tab 1
          $('#tab1 > ul > li').remove();
          for (var i = 0; i < json['versions'].length; i++) {
            if (json['versions'][i].version == json.latestversion) {
              $('#tab1 > strong').text('v.' + json['versions'][i].version);
              var $content = $('#tab1 > ul').append([
                $('<li>').text(json['versions'][i].provider),
                $('<li>').text(json['versions'][i].size),
                $('<li>').text(json['versions'][i].created),
                $('<li>').text(json['versions'][i].checksum_type),
                $('<li>').text(json['versions'][i].checksum)
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
              "href": json.json ,
              "target": "_blank"
            }).append([
              $('<i>').attr('class', 'fas fa-file')
            ])
          ]);
          for (var i = 0; i < json['versions'].length; i++) {
            $files += $('#tab3 > p').append([
              $('<a>').attr({
                "class": "button info_btn",
                "title": "open box version: " + json['versions'][i].version,
                "href": json['versions'][i].box,
                "target": "_blank"
              }).append([
                $('<i>').attr('class', 'fas fa-cube')
              ])
            ]);
          }
          // tab 4
          $('#tab4 > ul > li').remove();
          for (var i = 0; i < json['versions'].length; i++) {
            var $history = $('#tab4 > ul').append([
              $('<li>').text('v.' + json['versions'][i].version + ' - Released: ' + json['versions'][i].created)
            ]);
          }
          // tab 5
          $('#tab5 code').text('$ vagrant box add ' + box_name + ' ' + json.json);
          // action
          $('#info_modal').show();
        } else {
          alert(json.message);
        }
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        alert('Oops, something went wrong! Please look at the logs.');
      },
      complete: function() {
        $('.load').hide();
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
        $('.load').show();
      },
      success: function(json) {
        console.log('POST /api/update', json);
        alert(json.message);
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        alert('Oops, something went wrong! Please look at the logs.');
      },
      complete: function() {
        $('.button-primary').prop("disabled", false);
        read_all_boxes();
        $('#info_modal').hide();
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
      beforeSend: function() {
        $('.load').show();
      },
      success: function(json) {
        console.log('GET /api/delete?name=' + box_name + ': ', json);
      },
      error: function(jqxhr, status, exception) {
        console.log('ERROR:', exception);
        alert('Oops, something went wrong! Please look at the logs.');
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

  // action: search'n'filter table
  $('.searchBoxTable').on("keyup", function() {
    var value = $(this).val();
    $('table tr').each(function(index) {
      if (index != 0) {
        var $row = $(this);
        var col_a = $row.find('td:first').text();
        var col_b = $row.find('td:nth-child(3)').text();
        if (col_a.indexOf(value) != 0 && col_b.indexOf(value) != 0) {
          $(this).hide();
        } else {
          $(this).show();
        }
      }
    });
  });

  // action: list_boxes
  read_all_boxes();
});
