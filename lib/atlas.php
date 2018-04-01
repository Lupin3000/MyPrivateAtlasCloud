<?php
include './session.php';

function return_logout()
{
  $ini_array = parse_ini_file('./config/application.ini', true);
  $security = $ini_array['login']['security'];

  if ($security == "on")
  {
    echo '<li class="navigation-item">';
    echo '<a class="navigation-link" id="logout_btn" href="./logout.php" title="Logout from application">Logout</a>';
    echo '</li>';
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="description" content="Simple Vagrant box cloud">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>MyPrivate Vagrant Cloud</title>
  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
  <link rel="stylesheet" href="//cdn.rawgit.com/necolas/normalize.css/master/normalize.css">
  <link rel="stylesheet" href="//cdn.rawgit.com/milligram/milligram/master/dist/milligram.min.css">
  <script language="JavaScript" type="text/javascript" src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
  <script language="JavaScript" type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <link rel="stylesheet" href="./css/main.css">
  <!--[if IE]><link rel="shortcut icon" href="./img/favicon.ico"><![endif]-->
  <link rel="apple-touch-icon-precomposed" href="./img/cloud.png">
  <link rel="icon" href="./img/favicon.ico">
</head>
<body>
  <div class="wrapper">
    <nav class="navigation">
      <section class="container">
        <a class="navigation-title" href="." title="MyPrivate Vagrant Cloud">
          <h1 class="title">
            <i class="fas fa-cloud"></i> MyPrivate Vagrant Cloud
          </h1>
        </a>
        <ul class="navigation-list float-right">
          <li class="navigation-item">
            <a class="navigation-link" id="add_btn" href="#" title="Add new Vagrant box">Add Box</a>
          </li>
          <li class="navigation-item">
            <a class="navigation-link" id="help_btn" href="#" title="Show help page">Help</a>
          </li>
          <?php return_logout(); ?>
        </ul>
      </section>
    </nav>
    <div class="container">
      <h1>Discover the world of Vagrant boxes</h1>
      <table id="boxTable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Provider</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- placeholder -->
        </tbody>
      </table>
    </div>
  </div>
  <!-- add_modal -->
  <div id="add_modal" class="modal">
    <div class="container modal-content">
      <span class="close float-right" id="close_add">&times;</span>
      <h2>Add new Vagrant box</h2>
      <form id="boxUploadForm">
        <fieldset>
          <label for="boxname">Box Name</label>
          <input type="text" placeholder="box/name" name="boxname" id="boxname" maxlength="50" required>
          <label for="boxprovider">Provider</label>
          <input type="text" placeholder="virtualbox" name="boxprovider" id="boxprovider" maxlength="25" required>
          <label for="boxdescription">Description</label>
          <input type="text" placeholder="lorem ipsum dolor" name="boxdescription" id="boxdescription" maxlength="80" required>
          <label for="boxfile">Box</label>
          <input type="file" name="boxfile" id="boxfile" required>
          <br>
          <input class="button-primary" type="submit" value="Send">
        </fieldset>
      </form>
    </div>
  </div>
  <!-- info_modal -->
  <div id="info_modal" class="info">
    <div class="container modal-content">
      <span class="close float-right" id="close_info">&times;</span>
      <h2><!-- placeholder --></h2>
      <div id="info_content">
        <!-- placeholder -->
      </div>
      <div id="flip">
        <h3>Update Box</h3>
      </div>
      <div id="update_box">
        <form id="boxUpdateForm">
          <input type="hidden" name="boxname" id="boxname_add" value="">
          <label for="boxfile">Box</label>
          <input type="file" name="boxfile" id="boxfile_add" required>
          <fieldset>
            <br>
            <input class="button-primary" type="submit" value="Send">
          </fieldset>
        </form>
      </div>
    </div>
  </div>
  <!-- help_modal -->
  <div id="help_modal" class="info">
    <div class="container modal-content">
      <span class="close float-right" id="close_help">&times;</span>
      <h2>Help</h2>
      <p>How can I use the Vagrant box locally? The answers you will now get from an example.</p>
      <img src="./img/help.jpg" alt="help image with example box">
      <h4>Use cloud repository</h4>
      <p>
        <code>
          # add cloud box to repository<br>
          $ vagrant box add demo/centos7 http://example.com/boxes/meta/centos7.json<br><br>
          # list all boxes (optional)<br>
          $ vagrant box list<br><br>
          # create Vagrant project<br>
          $ vagrant init demo/centos7
        </code>
      </p>
      <h4>Use without cloud repository</h4>
      <p>Attention: The versioning for boxes is not automaticaly given!</p>
      <p>
        <code>
          # download box<br>
          $ curl -C - -k -o ~/Downloads/centos.box http://example.com/boxes/bin/centos7.box<br><br>
          # add local base box to repository<br>
          $ vagrant box add --provider PROVIDER --box-version VERSION demo/centos7 ~/Downloads/centos7.box<br><br>
          # list all boxes (optional)<br>
          $ vagrant box list<br><br>
          # create Vagrant project<br>
          $ vagrant init demo/centos7
        </code>
      </p>
      <h4>Local Vagrant boxes</h4>
      <p>
        <code>
          # show local boxes<br>
          $ vagrant box list<br>
          demo/centos7 (virtualbox, 1521038207)<br><br>
          # use box for project<br>
          $ vagrant init demo/centos7<br><br>
          # delete box<br>
          $ vagrant box remove demo/centos7
        </code>
      </p>
    </div>
  </div>
  <script language="JavaScript" type="text/javascript" src="./js/box.js"></script>
</body>
</html>
