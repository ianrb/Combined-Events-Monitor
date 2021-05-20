<div class="py-5 bg-container">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card card-list mb-12 box-shadow">
                    <div class="card-body mt-3">
                        <?php
                        if (isset($_GET['error'])) {
                            $error = $config->get_sanatized_varible('error');
                        ?>
                            <h2 class="text-center">An Error has Occurred</h2>
                            <h4 class="text-center">Error <?php echo $error; ?></h4>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>