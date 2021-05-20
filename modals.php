<!-- Modals -->

<div class="modal" id="modalConfig" tabindex="-1" role="dialog" aria-labelledby="modalConfig" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title col-12 text-center">CONFIGURATION</h5>
            </div>
            <div class="modal-body">

                <form action="#" id="config-form">

                    <div class="form-group row">
                        <label for="config-dynamictime" class="col-6 col-form-label">Dynamic Time <small id="emailHelp" class="form-text">Display relative time until user defined minutes at which point use absolute time.</small></label>
                        <div class="col-3">
                            <input class="form-control" type="checkbox" data-action="config-dynamictime" id="config-dynamictime" value="1" checked>
                        </div>
                        <div class="col-3">
                            <input class="form-control" type="number" data-action="config-dynamictime-duration" id="config-dynamictime-duration" min="0" max="2000">
                        </div>
                    </div>

                    <!-- 
                    <div class="form-group row">
                        <label for="config-dynamictime-duration" class="col-6 col-form-label">Use Standard After</label>
                        <div class="col-6">
                            <input class="form-control" type="time" data-action="config-dynamictime-duration" id="config-dynamictime-duration">
                        </div>
                    </div>
 -->

                    <h5 class="text-center">Theme</h5>

                    <div class="form-group row">
                        <label for="config-navbar-color" class="col-6 col-form-label">Navbar Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-navbar-color" id="config-navbar-color">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="config-primary-color" class="col-6 col-form-label">Primary Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-primary-color" id="config-primary-color">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="config-secondary-color" class="col-6 col-form-label">Secondary Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-secondary-color" id="config-secondary-color">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="config-font-color" class="col-6 col-form-label">Font Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-font-color" id="config-font-color">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="config-icon-color" class="col-6 col-form-label">Icon Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-icon-color" id="config-icon-color">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="config-scrollbartrack-color" class="col-6 col-form-label"> Track Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-scrollbartrack-color" id="config-scrollbartrack-color">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="config-scrollbarthumb-color" class="col-6 col-form-label"> Thumb Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-scrollbarthumb-color" id="config-scrollbarthumb-color">
                        </div>
                    </div>



                    <h5 class="text-center">MapBox</h5>

                    <div class="form-group row">
                        <label for="config-map-icon-color" class="col-6 col-form-label">Icon Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-map-icon-color" id="config-map-icon-color">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="config-map-font-color" class="col-6 col-form-label">Font Color</label>
                        <div class="col-6">
                            <input class="form-control color-input" data-action="config-map-font-color" id="config-map-font-color">
                        </div>
                    </div>




                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" href="#reset-config">RESET CONFIGURATION</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-primary" id="btnSaveConfig">SAVE</button>
            </div>
        </div>
    </div>
</div>






<div class="modal" id="modalSelfSignedBypass" tabindex="-1" role="dialog" aria-labelledby="modalSelfSignedBypass" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title col-12 text-center">SELF-SIGNED CERTIFICATE BYPASS</h5>
            </div>
            <div class="modal-body">

                <form action="#">

                    <h2></h2>

                    <p>
                        Some Web Browsers block self-signed certificates including the web socket on port 8080 and to ensure that SSL is not blocked press "OPEN NEW TAB" and allow this self-signed certificate then REFRESH the page
                    </p>

                    <p>This message can also appear if the socket is not running or fails to connect for any other unspecified reason</p>

                </form>

            </div>
            <div class="modal-footer">
                <a type="button" href="#refresh-page" class="btn btn-secondary">REFRESH</a>
                <a type="button" href="#open-ssl-bypass" target="_blank" class="btn btn-primary">OPEN NEW TAB</a>
            </div>
        </div>
    </div>
</div>