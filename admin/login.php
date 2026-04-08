<?php require_once('../config.php') ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
 <?php require_once('inc/header.php') ?>
  <style>
    html, body{
      height:calc(100%) !important;
      width:calc(100%) !important;
      font-family: 'Inter', sans-serif;
    }
    body{
      background-image: url("<?php echo validate_image($_settings->info('cover')) ?>");
      background-size:cover;
      background-repeat:no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }
    .glass-card {
      background: var(--glass-bg);
      backdrop-filter: var(--glass-blur);
      -webkit-backdrop-filter: var(--glass-blur);
      border: 1px solid var(--glass-border);
      border-radius: var(--border-radius-lg);
      box-shadow: var(--card-shadow);
      padding: 2.5rem;
      width: 100%;
      max-width: 450px;
      animation: fadeInLogin 0.8s ease-out;
    }
    .login-title {
      color: white;
      font-weight: 800 !important;
      letter-spacing: -2px !important;
      margin-bottom: 2.5rem;
      font-size: 3.5rem !important;
      text-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
    }
    .form-control {
      background: rgba(255,255,255,0.1) !important;
      border: 1px solid rgba(255,255,255,0.2) !important;
      border-radius: 8px !important;
      color: white !important;
      padding: 12px 15px !important;
    }
    .form-control:focus {
      background: rgba(255,255,255,0.2) !important;
      border-color: rgba(255,255,255,0.5) !important;
      color: white !important;
      box-shadow: none !important;
    }
    .form-control::placeholder {
      color: rgba(255,255,255,0.6) !important;
    }
    .btn-login {
      background: var(--primary-gradient) !important;
      border: none !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      padding: 12px !important;
      transition: all 0.3s !important;
    }
    .btn-login:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 4px 12px rgba(0,123,255,0.4) !important;
    }
    #logo-img{
        height:100px;
        width:100px;
        object-fit:cover;
        border-radius:50%;
        margin-bottom: 1rem;
        border: 3px solid rgba(255,255,255,0.3);
    }
  </style>
  <?php 
  $chk_users = $conn->query("SELECT id FROM users")->num_rows;
  ?>
<body class="hold-transition">
  <script>start_loader()</script>
  
  <div class="glass-card text-center">
    <img src="<?= validate_image($_settings->info('logo')) ?>" alt="" id="logo-img">
    <h1 class="login-title">Administrator</h1>

    <?php if($chk_users > 0): ?>
    <form id="login-frm" action="" method="post">
      <div class="input-group mb-3">
        <input type="text" class="form-control" autofocus name="username" placeholder="Username" required>
      </div>
      <div class="input-group mb-4">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-login text-white py-3">Sign In</button>
      <div class="mt-4">
        <a href="<?= base_url ?>" class="text-white-50 small text-decoration-none">← Go to Website</a>
      </div>
    </form>
    <?php else: ?>
    <form id="setup-frm" action="" method="post">
      <div class="input-group mb-3">
        <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
      </div>
      <div class="input-group mb-3">
        <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
      </div>
      <div class="input-group mb-3">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="input-group mb-4">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-login text-white">Create Administrator</button>
    </form>
    <?php endif; ?>
  </div>



<script>
  $(document).ready(function(){
    end_loader();
    setTimeout(function(){
        end_loader();
    }, 500);
    
    $('#login-frm').submit(function(e) {
        e.preventDefault()
        start_loader()
        if ($('.err_msg').length > 0) $('.err_msg').remove()
        $.ajax({
            url: _base_url_ + 'classes/Login.php?f=login',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            error: err => {
                console.log(err)
                alert_toast("An error occurred", 'danger')
                end_loader()
            },
            success: function(resp) {
                if (resp) {
                    if (resp.status == 'success') {
                        location.replace(_base_url_ + 'admin');
                    } else {
                        var _msg = $("<div class='alert alert-danger text-white err_msg animate__animated animate__shakeX'><i class='fa fa-exclamation-triangle'></i> Invalid credentials</div>")
                        $('#login-frm').prepend(_msg)
                        $('[name="username"]').focus()
                    }
                }
                end_loader()
            }
        })
    })

    $('#setup-frm').submit(function(e){
        e.preventDefault()
        start_loader()
        if($('.err_msg').length > 0) $('.err_msg').remove()
        $.ajax({
            url: _base_url_ + 'classes/Login.php?f=setup_admin',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            error: err => {
                console.log(err)
                alert_toast("An error occurred", 'danger')
                end_loader()
            },
            success: function(resp){
                if(resp){
                    if(resp.status == 'success'){
                        location.reload();
                    }else{
                        var _msg = $("<div class='alert alert-danger text-white err_msg'><i class='fa fa-exclamation-triangle'></i> "+resp.msg+"</div>")
                        $('#setup-frm').prepend(_msg)
                    }
                }
                end_loader()
            }
        })
    })
  })
</script>
</body>
</html>