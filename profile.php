<?php 
if(!($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 2)){
    redirect('./');
}
$qry = $conn->query("SELECT * FROM `client_list` where id = '{$_settings->userdata('id')}'");
if($qry->num_rows > 0){
    $res = $qry->fetch_array();
    foreach($res as $k => $v){
        if(!is_numeric($k))
        $$k = $v;
    }
}
?>
<div class="content py-5">
    <div class="card card-outline card-primary rounded-0 shadow">
        <div class="card-header">
            <h4 class="card-title"><b>My Profile</b></h4>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <form action="" id="update-profile">
                    <input type="hidden" name="id" value="<?= $_settings->userdata('id') ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstname" class="control-label">First Name</label>
                                <input type="text" name="firstname" id="firstname" class="form-control form-control-sm form-control-border" value="<?= isset($firstname) ? $firstname : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname" class="control-label">Last Name</label>
                                <input type="text" name="lastname" id="lastname" class="form-control form-control-sm form-control-border" value="<?= isset($lastname) ? $lastname : '' ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender" class="control-label">Gender</label>
                                <select name="gender" id="gender" class="form-control form-control-sm form-control-border" required>
                                    <option <?= isset($gender) && $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option <?= isset($gender) && $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact" class="control-label">Contact #</label>
                                <input type="text" name="contact" id="contact" class="form-control form-control-sm form-control-border" value="<?= isset($contact) ? $contact : '' ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address" class="control-label">Address</label>
                        <textarea rows="3" name="address" id="address" class="form-control form-control-sm rounded-0" required><?= isset($address) ? $address : '' ?></textarea>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="email" class="control-label">Email (Username)</label>
                        <input type="email" name="email" id="email" class="form-control form-control-sm form-control-border" value="<?= isset($email) ? $email : '' ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="oldpassword" class="control-label">Current Password</label>
                                <input type="password" name="oldpassword" id="oldpassword" class="form-control form-control-sm form-control-border" placeholder="Required to update profile">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="control-label">New Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-sm form-control-border" placeholder="Leave blank if no change">
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right mt-3">
                        <button class="btn btn-primary btn-flat btn-sm">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('#update-profile').submit(function(e){
            e.preventDefault();
            var _this = $(this)
            $('.pop-msg').remove()
            var el = $('<div>')
                el.addClass("pop-msg alert alert-danger")
                el.hide()
            start_loader();
            $.ajax({
                url:_base_url_+"classes/Users.php?f=save_client",
                method:'POST',
                data:$(this).serialize(),
                dataType:'json',
                error:err=>{
                    console.log(err)
                    alert_toast("An error occured",'error');
                    end_loader();
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        location.reload();
                    }else if(!!resp.msg){
                        el.text(resp.msg)
                        _this.prepend(el)
                        el.show('slow')
                    }else{
                        el.text("An error occurred due to unknown reason.")
                        _this.prepend(el)
                        el.show('slow')
                    }
                    end_loader();
                }
            })
        })
    })
</script>
