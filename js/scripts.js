// Модуль приложения
var app = (function($) {
  var ajaxUrl = '/php/scripts.php';

  // Инициализация дерева с помощью jstree
  function initTree(data) {
    $('#tree').jstree({
        core: {
            check_callback: true,
            data: data,
            themes : { "stripes" : true }
        },

        types: {
          category : {
            icon : "glyphicon glyphicon-folder-open"
          },
          element : {
            icon : "glyphicon glyphicon-file",
          }
        },
        plugins : [
          "themes", "json_data", "ui", "crrm", "dnd", "types", "wholerow"
        ]
    }).bind('move_node.jstree', function(e, data) {
      var params = {
          id: data.node.id,
          new_parent: data.parent,
          action: 'move'
      };
      ajaxRequest(params);
    });
  }

  $('#btnCreateCategory').click(function() {
    var ref = $('#tree').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }

    sel = sel[0];

    if (sel.slice(0, 2) != 'el') {
      var parent_id = sel.slice(4, sel.lenght);
      var params = {
        title: 'new_category',
        parent: parent_id,
        action: 'add_category'
      };

      ajaxRequest(params);
    } else {
      alert('Невозможно добавить раздел в элемент!');
    }
  });

  $('#btnCreateElement').click(function() {
    var ref = $('#tree').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }

    sel = sel[0];

    if (sel.slice(0, 2) != 'el') {
      var parent_id = sel.slice(4, sel.lenght);
      var params = {
        title: 'new_element',
        parent: parent_id,
        action: 'add_element'
      };

      ajaxRequest(params);
    } else {
      alert('Выберите, пожалуйста, раздел.');
    }
  });

  $('#btnRename').click(function() {
    var ref = $('#tree').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }

    sel = sel[0];
    ref.edit(sel, null, function (node, status) {
      var params = {
        id: sel,
        title: node.text,
        action: 'edit_title'
      };
      ajaxRequest(params);
    });
  });

  $('#btnRemove').click(function() {
    var ref = $('#tree').jstree(true),
  		  sel = ref.get_selected();
  	if(!sel.length) { return false; }

    if(sel[0].slice(0, 2) == 'el') {
      var id_element = sel[0].slice(3, sel.lenght);
      var params = {
        id: id_element,
        action: 'remove_element'
      };
      ajaxRequest(params);

      ref.delete_node(sel);
    } else if(sel[0].slice(0, 3) == 'cat') {
      var id_category = sel[0].slice(4, sel.lenght);
      var params = {
        id: id_category,
        action: 'remove_category'
      };
      ajaxRequest(params);

      ref.delete_node(sel);
    } else {
      alert('Пожалуйста, выберите нужную категорию.');
    }
  });

  function ajaxRequest(params) {
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function(resp) {
            if (resp.code === 'success') {
              if (resp.result.action == 'add_category' || resp.result.action =='add_element') {
                var ref = $('#tree').jstree(true),
                    sel = ref.get_selected();

                if (resp.result.action == 'add_category'){
                  var new_section = {id: 'cat-' + resp.result.new_id, text: 'new_category', type: 'category'};
                }
                else {
                  var new_section = {id: 'el-' + resp.result.new_id, text: 'new_element', type: 'element'};
                }

                ref.create_node(sel, new_section);

                ref.edit(new_section.id, null, function (node, status) {
                  var params = {
                    id: new_section.id,
                    title: node.text,
                    action: 'edit_title'
                  };
                  ajaxRequest(params);
                });
              }

              console.log(resp.result.message);
            } else {
              console.error('Ошибка получения данных с сервера: ', resp.message);
            }
        },
        error: function(error) {
            console.error('Ошибка: ', error);
        }
    });
  }

  // Загрузка дерева с сервера
  function loadData() {
      var params = {
          action: 'get_tree'
      };

      $.ajax({
          url: ajaxUrl,
          method: 'GET',
          data: params,
          dataType: 'json',
          success: function(resp) {
              // Инициализируем дерево категорий
              if (resp.code === 'success') {
                console.log(resp.result);
                initTree(resp.result);
              } else {
                console.error('Ошибка получения данных с сервера: ', resp.message);
              }
          },
          error: function(error) {
              console.error('Ошибка: ', error);
          }
      });
  }

  function init() {
      loadData();
  }

  return {
      init: init
  }
})(jQuery);

jQuery(document).ready(app.init);
