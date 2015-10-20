<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <table>
        <?php
        foreach($_SERVER as $key => $val){ ?>
            <tr>
                <td><?= $key ?></td>
                <td><?= $val ?></td>                
            </tr>
        <?php } 
 error_log("Works fine".  date(DATE_RSS));
        ?>
        </table>
    </body>
</html>
