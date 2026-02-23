<?php
ob_start();
session_start();
include('include/header.php');
include('codeLogic/auth/login/login-logic.php');

if (isset($_SESSION['auth'])) {
    $_SESSION['status'] = "You are already logged !";
    header('Location: index.php');
    exit(0);
}
?>

<div class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 my-5">
                <?php
                if (isset($_SESSION['auth_status'])) {
                ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Hey!</strong> <?php echo $_SESSION['auth_status']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                <?php
                    unset($_SESSION['auth_status']);
                }
                ?>
                <?php
                include('./message/message.php');
                ?>
                <div class="card my-5">
                    <div class="card-header bg-light text-center">
                        <h5>Login To Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="">Email Id</label>
                                <input type="text" name="email" placeholder="Email Id" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="">Password</label>
                                <input type="password" name="password" placeholder="Password" class="form-control">
                            </div>
                            <div class="input-group mb-3">
                                <div class="row">
                                    <div class="form-group col-6">
                                        <input type="text" class="form-control" name="captcha" id="captcha" placeholder="Enter captcha.." required>
                                    </div>
                                    <style>
                                        .pxdoubt {
                                            background-color: transparent;
                                            background-image: url('./captcha/bg6.png');
                                            letter-spacing: 2px;
                                            color: black;
                                            font-size: 26px;
                                            font-weight: bold;
                                            text-align: center;
                                        }

                                        @media (max-width: 400px) {
                                            .pxdoubt {
                                                letter-spacing: 0;
                                                text-align: left !important;
                                            }
                                        }
                                    </style>
                                    <div class="d-flex justify-content-end form-group col-6">
                                        <div class="container d-flex justify-content-end row">
                                            <div class="col-md-12">
                                                <input onCopy="return false" onCut="return false" onDrag="return false" type="text" class="form-control pxdoubt" name="code" value="<?php $ra_num = md5(random_bytes(64));
                                                                                                                                                                                    echo $ca_code = substr($ra_num, 0, 6); ?>" readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <button type="submit" name="login_btn" class="btn btn-primary btn-block">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
include('include/script.php');
?>


</div>
</body>

</html>

<?php

ob_end_flush();

?>