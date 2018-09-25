<?php

require "config/db_connect.php";

// Получение списка элементов
function getElements($db) {
  $query = "
      SELECT
         id,
         title as `text`,
         category_id
      FROM
         elements
      ORDER BY
         `category_id`
  ";

  $data = $db->query($query);
  return $data;
}

// Получение списка категорий
function getCategories($db){
  $query = "
      SELECT
         id AS `id`,
         IF (parent_id = 0, '#', parent_id) AS `parent`,
         title as `text`
      FROM
         categories
      ORDER BY
         `parent`
  ";

  $data = $db->query($query);
  return $data;
}

// Формирование дерева
function getTree($db) {
    $data_categories = getCategories($db);
    $data_elements = getElements($db);
    $tree = array();
    while ($row = $data_categories->fetch_assoc()) {
        if ($row['parent'] == '#'){
          array_push($tree, array(
              'id' => 'cat-' . $row['id'],
              'parent' => $row['parent'],
              'text' => $row['text'],
              'type' => 'category'
          ));
        }
        else {
          array_push($tree, array(
              'id' => 'cat-' . $row['id'],
              'parent' => 'cat-' . $row['parent'],
              'text' => $row['text'],
              'type' => 'category'
          ));
        }
    }
    while ($row = $data_elements->fetch_assoc()) {
        array_push($tree, array(
            'id' => 'el-' . ((string)$row['id']),
            'parent' => 'cat-' .  $row['category_id'],
            'text' => $row['text'],
            'type' => 'element'
        ));
    }

    return $tree;
}

// Добавление новой категории
function addCategory($db, $params) {
  $title = $params['title'];
  $parentId = (int)$params['parent'];
  $query = "INSERT INTO categories(title, parent_id)
            VALUES ('$title', '$parentId')";
  $db->query($query);

  $result = array(
    'action' => 'add_category',
    'message' => 'Новая категория добавлена',
    'new_id' =>  $db->insert_id
  );

  return $result;
}

// Добавление нового элемента
function addElement($db, $params) {
  $title = $params['title'];
  $categoryId = (int)$params['parent'];
  $query = "INSERT INTO elements(title, category_id)
            VALUES ('$title', '$categoryId')";
  $db->query($query);

  $result = array(
    'action' => 'add_element',
    'message' => 'Новый элемент добавлен',
    'new_id' =>  $db->insert_id
  );

  return $result;
}

// Удаление элемента
function removeElement($db, $params) {
  $id = (int)$params['id'];
  $query = "DELETE FROM elements
            WHERE id = $id";
  $db->query($query);

  $result = array(
    'action' => 'remove_element',
    'message' => 'Выбранный элемент удален',
  );

  return $result;
}

// Удаление категории
function removeCategory($db, $arr, $id_category) {
  foreach ($arr as $key => $value) {
    $parent_id = substr($value['parent'], 4);
    if ($parent_id == $id_category) {
      if(substr($value['id'], 0, 2) == 'el') {
        $params = array(
          "id" => substr($value['id'], 3)
        );
        removeElement($db, $params);
      }

      if(substr($value['id'], 0, 3) == 'cat') {
        removeCategory($db, $arr, substr($value['id'], 4));
      }
    }
  }

  $id = (int)$id_category;
  $query = "DELETE FROM categories
            WHERE id = $id";
  $db->query($query);

  $result = array(
    'action' => 'remove_category',
    'message' => 'Выбранная категория удаленa',
  );

  return $result;
}

// Изменение названия
function editTitle ($db, $params) {
  $title = $params['title'];
  if (substr($params['id'], 0, 3) == 'cat') {
    $categoryID = (int)(substr($params['id'], 4));
    $query = "UPDATE categories
              SET title = '$title'
              WHERE id = $categoryID";
    $db->query($query);
  } else if(substr($params['id'], 0, 2) == 'el') {
    $elementId = (int)(substr($params['id'], 3));
    $query = "UPDATE elements
              SET title = '$title'
              WHERE id = $elementId";
    $db->query($query);
  }

  $result = array(
    'action' => 'edit_title',
    'message' => 'Название изменено',
  );

  return $result;
}

// Перенос
function move($db, $params) {
    $newParentId = (int)(substr($params['new_parent'], 4));

    if (substr($params['id'], 0, 3) == 'cat') {
      $categoryId = (int)(substr($params['id'], 4));
      $query = "UPDATE categories
                SET parent_id = '$newParentId'
                WHERE id = $categoryId";
      $db->query($query);
    } else if(substr($params['id'], 0, 2) == 'el') {
      $elementId = (int)(substr($params['id'], 3));
      $query = "UPDATE elements
                SET category_id = '$newParentId'
                WHERE id = $elementId";
      $db->query($query);
    }

    $result = array(
      'action' => 'move',
      'message' => 'Перенос прошел успешно'
    );
    return $result;
}

try {
  // Подключаемся к базе данных
  $conn = connectDB();

  // Получаем данные из массива GET
  $action = $_GET['action'];
  switch ($action) {
    // Получение дерева
    case 'get_tree':
      $result = getTree($conn);
      break;
    // Добавление новой категории
    case 'add_category':
      $result = addCategory($conn, $_GET);
      break;
    // Добавление нового элемента
    case 'add_element':
      $result = addElement($conn, $_GET);
      break;
    // Удаление элемента
    case 'remove_element':
      $result = removeElement($conn, $_GET);
      break;
    // Удаление категории
    case 'remove_category':
      $arr = getTree($conn);
      $id_category = $_GET['id'];
      $result = removeCategory($conn, $arr, $id_category);
      break;
    case 'edit_title':
      $result = editTitle($conn, $_GET);
      break;
    // Перемещение
    case 'move':
      $result = move($conn, $_GET);
      break;
    // Действие по умолчанию, ничего не делает
    default:
      $result = 'unknown action';
      break;
  }

  echo json_encode(array(
      'code' => 'success',
      'result' => $result
  ));
}
catch (Exception $e) {
    echo json_encode(array(
        'code' => 'error',
        'message' => $e->getMessage()
    ));
}
