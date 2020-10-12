<!-- Modals -->

<div class="modal" id="modalTheme" tabindex="-1" role="dialog" aria-labelledby="modalTheme" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title col-12 text-center">ADJUST THEME</h5>
            </div>
            <div class="modal-body">

                <form action="#">

                    <!-- 
                <div class="form-group">
                        <label for="txtSave">Recording Name</label>
                        <input type="text" class="form-control" id="txtSave" name="txtSave" placeholder="Recording Name">
                    </div>
                     -->


                    <div class="form-group">
                        <label for="txtPrimaryColor">Recording Name</label>

                        <input class="form-control color-input" id="txtPrimaryColor" name="txtPrimaryColor" value="#F80" />

                        <!-- <input type="text" class="form-control" id="txtSave" name="txtSave" placeholder="Recording Name"> -->
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-secondary">CANCEL</button>
                <button type="button" id="btnSave" class="btn btn-primary">SAVE</button>
            </div>
        </div>
    </div>
</div>