<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tree</title>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.5/themes/default/style.min.css"/>
    <link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
  <div class="container">
      <h1>Дерево</h1>
      <div class="buttons">
        <button id="btnCreateCategory" class="bt-green">Create category</button>
        <button id="btnCreateElement" class="bt-green">Create element</button>
        <button id="btnRemove" class="bt-red">Remove</button>
        <button id="btnRename" class="bt-yellow">Rename</button>
      </div>
      <div id="tree" class="column tree"></div>
  </div>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.5/jstree.min.js"></script>
  <script src="js/scripts.js" type="text/javascript"></script>
</body>
</html>
