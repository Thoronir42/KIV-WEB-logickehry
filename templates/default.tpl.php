<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
        <link href="http://<?= $_SERVER['SERVER_NAME'] ?>/css/default.css" rel="stylesheet">
        
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        
        <title><?= $title ?></title>
    </head>
    <body>
        <div class="row">
          <div id="top_infoBar" class="col-lg-10 col-lg-offset-1">
              <h1>Hello, world!</h1>
          </div>
        </div>
        
        <div class="row">
            <div class="menuBox col-lg-offset-2 col-sm-3 col-md-3 col-lg-3" >
                <ul class="nav nav-pills nav-stacked">
                    <?php foreach($menu as $link) { ?>
                    <li role="presentation" <?= isset($link['active']) ? "class=\"active\"" : "" ?>> <a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        
        
        <?php
        
        ?>
    </body>
</html>
