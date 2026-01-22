<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Home</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
       <li class="nav-item">
         <div class="dropdown show">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              
            <?php
            if(isset($_SESSION['auth']))
            {
             echo $_SESSION['auth_user']['user_name']; 
            }
            else
            {
               echo "Not logged in";
            }
             ?>

            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
              <form action="codeLogic/auth/logout/logout.php" method="POST">
                <button type="submit" name="logout-btn" class="dropdown-item">Logout</button>
              </form>
            </div>
          </div>
      </li>           
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->