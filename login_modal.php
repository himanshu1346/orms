<style>
    #uni_modal .modal-footer{
        display:none;
    }
</style>
<div class="container-fluid">
    <form action="" id="clogin-form">
        <div class="form-group">
            <label for="email" class="control-label">Email</label>
            <input type="email" name="email" id="email" class="form-control form-control-sm form-control-border" required>
        </div>
        <div class="form-group">
            <label for="password" class="control-label">Password</label>
            <input type="password" name="password" id="password" class="form-control form-control-sm form-control-border" required>
        </div>
        <div class="form-group d-flex justify-content-between align-items-center">
            <a href="javascript:void(0)" id="create_account">Create Account</a>
            <button class="btn btn-primary btn-flat btn-sm">Login</button>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('#create_account').click(function(){
            uni_modal("Create Account","register.php","mid-large")
        })
        $('#clogin-form').submit(function(e){
            e.preventDefault();
            var _this = $(this)
            $('.pop-msg').remove()
            var el = $('<div>')
                el.addClass("pop-msg alert alert-danger")
                el.hide()
            start_loader();
            $.ajax({
                url:_base_url_+"classes/Login.php?f=client_login",
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
