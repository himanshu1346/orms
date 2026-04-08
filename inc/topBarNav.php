<style>
  .user-img{
        position: absolute;
        height: 27px;
        width: 27px;
        object-fit: cover;
        left: -7%;
        top: -12%;
  }
  .btn-rounded{
        border-radius: 50px;
  }
</style>
<!-- Navbar -->
      <style>
        #login-nav {
          position: fixed !important;
          top: 0 !important;
          z-index: 1037;
          padding: 0.3em 2.5em !important;
        }
        #top-Nav{
          top: 2.3em;
        }
        .text-sm .layout-navbar-fixed .wrapper .main-header ~ .content-wrapper, .layout-navbar-fixed .wrapper .main-header.text-sm ~ .content-wrapper {
          margin-top: calc(3.6) !important;
          padding-top: calc(3.2em) !important
        }
        @media (max-width: 768px) {
          #login-nav {
            padding: 0.3em 0.5em !important;
          }
          #login-nav .d-flex {
            flex-direction: column;
            align-items: center;
          }
          #top-Nav {
            top: 3.8em;
          }
          .text-sm .layout-navbar-fixed .wrapper .main-header ~ .content-wrapper, .layout-navbar-fixed .wrapper .main-header.text-sm ~ .content-wrapper {
            margin-top: calc(5.1) !important;
            padding-top: calc(4.7em) !important
          }
        }
      </style>
      <nav class="w-100 px-2 py-1 position-fixed top-0 bg-dark text-light" id="login-nav">
        <div class="d-flex justify-content-between w-100">
          <div>
            <span class="mr-2"><i class="fa fa-phone mr-1"></i> <?= htmlspecialchars($_settings->info('contact')) ?></span>
          </div>
          <div>
            <?php if($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 2): ?>
              <span class="mx-2"><img src="<?= validate_image($_settings->userdata('avatar')) ?>" alt="User Avatar" id="student-img-avatar" class="user-img"></span>
              <span class="mx-2 text-light">Howdy, <?= htmlspecialchars($_settings->userdata('firstname')) ?></span>
              <span class="mx-1"><a href="<?= base_url.'classes/Login.php?f=client_logout' ?>" class="text-light"><i class="fa fa-power-off"></i></a></span>
            <?php elseif($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 1): ?>
              <span class="mx-2 text-light">Welcome, <?= htmlspecialchars($_settings->userdata('username')) ?> (Admin)</span>
              <span class="mx-1"><a href="<?= base_url.'classes/Login.php?f=logout' ?>" class="text-light"><i class="fa fa-power-off"></i></a></span>
            <?php else: ?>
              <a href="javascript:void(0)" id="login-btn" class="mx-2 text-light">Login</a>
              <a href="javascript:void(0)" id="register-btn" class="mx-2 text-light">Register</a>
              <a href="./admin" class="mx-2 text-light">Admin Login</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
      <nav class="main-header navbar navbar-expand-lg navbar-light border-0 text-sm bg-gradient-light" id='top-Nav'>
        
        <div class="container">
          <a href="./" class="navbar-brand">
            <img src="<?php echo validate_image($_settings->info('logo'))?>" alt="Site Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span><?= htmlspecialchars($_settings->info('short_name')) ?></span>
          </a>

          <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
              <li class="nav-item">
                <a href="./" class="nav-link <?= isset($page) && $page =='home' ? "active" : "" ?>">Home</a>
              </li>
              <li class="nav-item">
                <a href="./?page=rooms" class="nav-link <?= isset($page) && $page =='rooms' ? "active" : "" ?>">Rooms</a>
              </li>
              <li class="nav-item">
                <a href="./?page=activities" class="nav-link <?= isset($page) && $page =='activities' ? "active" : "" ?>">Activities</a>
              </li>
              <li class="nav-item">
                <a href="./?page=about" class="nav-link <?= isset($page) && $page =='about' ? "active" : "" ?>">About Us</a>
              </li>
              <li class="nav-item">
                <a href="./?page=contact_us" class="nav-link <?= isset($page) && $page =='contact_us' ? "active" : "" ?>">Contact Us</a>
              </li>
              <?php if($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 2): ?>
              <li class="nav-item">
                <a href="./?page=profile" class="nav-link <?= isset($page) && $page =='profile' ? "active" : "" ?>">Profile</a>
              </li>
              <li class="nav-item">
                <a href="./?page=my_reservations" class="nav-link <?= isset($page) && $page =='my_reservations' ? "active" : "" ?>">My Reservations</a>
              </li>
              <?php endif; ?>
              <!-- <li class="nav-item">
                <a href="#" class="nav-link">Contact</a>
              </li> -->
            </ul>

            
          </div>
          <!-- Right navbar links -->
          <div class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
          </div>
        </div>
      </nav>
      <!-- /.navbar -->
      <script>
        $(function(){
          $('#login-btn').click(function(){
            uni_modal("Login","login_modal.php")
          })
          $('#register-btn').click(function(){
            uni_modal("Create Account","register.php","mid-large")
          })
        })
      </script>