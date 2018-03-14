<?php
session_start();

$ini_array = parse_ini_file('./config/config.ini', true);
$security = $ini_array['login']['security'];
$user = $ini_array['login']['user'];
$password = $ini_array['login']['password'];

if (isset($_POST['login'])) {
  if (empty($_POST['user']) || empty($_POST['passwd'])) {
    $error = 'Invalid credentials';
  } else {
    if ($_POST['user'] == $user && $_POST['passwd'] == $password) {
      $_SESSION['valid'] = true;
      $_SESSION['timeout'] = time();
      $_SESSION['user'] = $_POST['user'];
      header('Location: ./atlas.php');
    } else {
      $error = 'Wrong credentials';
    }
  }
}

if ($security == "off"){
  $_SESSION['valid'] = true;
  $_SESSION['timeout'] = time();
  $_SESSION['user'] = 'anonymous';
  header('Location: ./atlas.php');
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>MyPrivate Vagrant Cloud</title>
  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
  <link rel="stylesheet" href="//cdn.rawgit.com/necolas/normalize.css/master/normalize.css">
  <link rel="stylesheet" href="//cdn.rawgit.com/milligram/milligram/master/dist/milligram.min.css">
  <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
  <link rel="stylesheet" href="./css/main.css">
</head>
<body>
  <div class="wrapper">
    <nav class="navigation">
      <section class="container">
        <a class="navigation-title" href=".">
          <h1 class="title">
            <i class="fas fa-cloud"></i> MyPrivate Vagrant Cloud
          </h1>
        </a>
      </section>
    </nav>
    <div class="container">
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <fieldset>
          <label for="user">Name</label>
          <input type="text" placeholder="Username" name="user" id="user">
          <label for="passwd">Name</label>
          <input type="password" placeholder="*****" name="passwd" id="passwd">
          <input class="button-primary" type="submit" name="login" value="Login">
        </fieldset>
      </form>
      <?php echo $error; ?>
    </div>
  </div>
</body>
</html>
