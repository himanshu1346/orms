<style>
    #uni_modal .modal-footer{
        display:none;
    }
</style>
<div class="container-fluid">
    <form action="" id="registration-form">
        <input type="hidden" name="id">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="firstname" class="control-label">First Name</label>
                    <input type="text" name="firstname" id="firstname" class="form-control form-control-sm form-control-border" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="lastname" class="control-label">Last Name</label>
                    <input type="text" name="lastname" id="lastname" class="form-control form-control-sm form-control-border" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="gender" class="control-label">Gender</label>
                    <select name="gender" id="gender" class="form-control form-control-sm form-control-border" required>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="contact" class="control-label">Contact #</label>
                    <input type="text" name="contact" id="contact" class="form-control form-control-sm form-control-border" required>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="address" class="control-label">Address</label>
            <textarea rows="3" name="address" id="address" class="form-control form-control-sm rounded-0" required></textarea>
        </div>
        <hr>
        <div class="form-group">
            <label for="email" class="control-label">Email (Username)</label>
            <input type="email" name="email" id="email" class="form-control form-control-sm form-control-border" required>
        </div>
        <div class="form-group">
            <label for="password" class="control-label">Password</label>
            <input type="password" name="password" id="password" class="form-control form-control-sm form-control-border" required>
        </div>
        <div class="form-group d-flex justify-content-between align-items-center">
            <a href="javascript:void(0)" id="login_link">Already have an account?</a>
            <button class="btn btn-primary btn-flat btn-sm">Register</button>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('#login_link').click(function(){
            uni_modal("Login","login_modal.php")
        })
        $('#registration-form').submit(function(e){
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
