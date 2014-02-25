<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>PHP Paginator</title>
  <link rel="stylesheet" href="paginator.css" />
</head>

<body>
  <?php

    require_once('Paginator.php');

    $paginator = new Paginator();
    $paginator->initialize(500);
    echo $paginator->paginate();

  ?>
</body>

</html>