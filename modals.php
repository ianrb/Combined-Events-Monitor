<!-- Modals -->

<div class="modal" id="modalProcessing" tabindex="-1" role="dialog" aria-labelledby="modalProcessing" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title col-12 text-center">PROCESSING</h5>
            </div>
            <div class="modal-body">
                <p>Processing - Press STOP to cancel</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnStop" class="btn btn-primary">STOP</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modalSave" tabindex="-1" role="dialog" aria-labelledby="modalSave" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title col-12 text-center">SAVE RECORDING</h5>
            </div>
            <div class="modal-body">

                <form action="#">
                    <div class="form-group">
                        <label for="txtSave">Recording Name</label>
                        <input type="text" class="form-control" id="txtSave" name="txtSave" placeholder="Recording Name">
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

