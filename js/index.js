// Load localStorage Config
var config = getConfig();
console.log('getConfig', config);
var $settingspopovers = [],
    $filterpopovers = [];

// 
// Main Initialiser
// 
$(function() {

    $('body').show();

    loadConfig(config);

    initClickEvents();

    initThemes();

    initMapBox();

    updateDynamicTime();

    keepAlive();


    context.init({ preventDoubleContext: false });

    // DSD+ 
    context.attach("[id^='DSDPlus'] .badge-message", [{
            text: 'Download',
            action: function(e, parentId, el) {

                // Take last character from id and use for instance
                var instance = parseInt(parentId.substring(parentId.length - 1, parentId.length));

                var file;

                // DSD+
                if (parentId.indexOf('DSDPlus') > -1) {

                    // Grab date from element value
                    var arDate = $(el).attr('value').split('|');
                    console.log('Click DSD Event', [parentId, arDate[0], arDate[1]]);
                    file = getDSDWaveFile(instance, arDate[0], arDate[1]);

                }


                if (file) {
                    downloadFile(file, parentId);
                }

            }
        },
        {
            text: 'Filter Map',
            action: function(e, parentId, el) {

                var rid = $(el).find('[data-action="rid"]').text();

                var marker = markers[rid];

                // NOT COMPLETE - 
                console.log('marker', marker);
                $(`#MapboxMap [data-toggle="filter-events"]`).click();

                $(`#filter_controls_MapboxMap input[data-action="filter-log"]`).val(rid).trigger('keyup');

            }
        },
        {
            text: 'Filter Events',
            action: function(e, parentId, el) {

                var rid = $(el).find('[data-action="rid"]').text();

                $(`#${parentId} [data-toggle="filter-events"]`).click();
                $(`#filter_controls_${parentId} input[data-action="filter-log"]`).val(rid).trigger('keyup');
            }
        }
    ]);


    // File Event 
    context.attach("[id^='FileEvent'] .badge-message", [{
        text: 'Download',
        action: function(e, parentId, el) {

            // e.preventDefault();
            // Take last character from id and use for instance
            var instance = parseInt(parentId.substring(parentId.length - 1, parentId.length));

            var file;

            // File Event
            if (parentId.indexOf('FileEvent') > -1) {
                file = getWaveFile($(el).attr('value'));
            }

            if (file) {
                downloadFile(file, parentId);
            }

        }
    }]);


});


function updateDynamicTime() {

    setInterval(function() {

        $('[data-action="dynamic-time"]').each(function() {

            var $mmt = moment.unix($(this).attr('value'));
            var diff = moment(new Date()).diff($mmt) / 1000 / 60;

            // console.log('diff', diff);
            // console.log('diff', config.dynamictimeduration);

            if (!config.dynamictime | (config.dynamictime && diff > config.dynamictimeduration)) {

                // $(this).removeAttr('data-action');
                $(this).text($mmt.format(strDateTimeFormat));

            } else {
                $(this).text($mmt.fromNow());

            }


        });

    }, 2500);

}
// Background AJAX request every 15 mins to keep session alive
function keepAlive() {
    setInterval(function() {
        $.get('/api?keepalive', function() {
            if (isDebug) {
                console.log('Background AJAX for keepalive');
            }
        })
    }, 900000);
}


function initClickEvents() {

    // Main Menu
    // $(document).on('click', '#main-nav a', function() {
    $(document).on('click', "[href^='#']", function() {

        var val = $(this).attr('href');
        // alert(val);

        switch (val) {
            case '#mute-all':

                config.muteAll = !config.muteAll;

                for (var i in config.eventGroups) {
                    config.eventGroups[i].mute = config.muteAll;
                }

                if (config.muteAll) {
                    $(this).find('span').text('Unmute All');
                    $(this).find('svg').removeClass('fa-volume-up').addClass('fa-volume-mute');
                    $('[data-action="mute-unmute"] svg').removeClass('fa-volume-up').addClass('fa-volume-mute');
                } else {
                    $(this).find('span').text('Mute All');
                    $(this).find('svg').removeClass('fa-volume-mute').addClass('fa-volume-up');
                    $('[data-action="mute-unmute"] svg').removeClass('fa-volume-mute').addClass('fa-volume-up');
                }

                saveConfig();

                break;

            case '#playback-mode':

                config.linearPlayback = !config.linearPlayback;

                if (config.linearPlayback) {
                    $(this).find('span').text('Concurrent');
                    $(this).find('svg').removeClass('fa-list-ol').addClass('fa-stream');
                } else {
                    $(this).find('span').text('Linear');
                    $(this).find('svg').removeClass('fa-stream').addClass('fa-list-ol');
                }

                saveConfig();

                break;





            case '#config':
                $('#modalConfig').modal();
                break;

            case '#reset-config':
                resetConfig();
                location.reload();
                break;

            case '#ssl-bypass':
                $('#modalSelfSignedBypass').modal();
                break;

            case '#open-ssl-bypass':
                window.open(`https://${ServerAddress}:8080/`, "_blank");
                break;

            case '#refresh-page':
                location.reload();
                break;


            case '#logout':
                window.location = '/logout';
                break;


        }
    });

    // Data-Action Click
    $(document).on('click', '[data-action]', function() {

        var dataAction = $(this).attr('data-action');
        var el = $(this);

        switch (dataAction) {

            // MapBox
            case "toggle-3d":
                toggle3D(el);
                break;

            case "map-zoom-in":

                var currentZoom = parseInt(map.getZoom());
                var targetZoom = currentZoom + 2;

                // var closest = mapboxDefaultZoomLevels.reduce(function(prev, curr) {
                //     return (Math.abs(curr - currentZoom) < Math.abs(prev - currentZoom) ? curr : prev);
                // });
                // console.log('closest', closest);

                map.setCenter(mapboxDefaultLocation);
                map.zoomTo(targetZoom, { duration: 900 });
                break;

            case "map-zoom-out":

                var currentZoom = parseInt(map.getZoom());
                var targetZoom = currentZoom - 2;


                map.setCenter(mapboxDefaultLocation);
                map.zoomTo(targetZoom, { duration: 900 });
                break;

            case "map-zoom-to":

                var val = $(this).attr('value');
                var arlatlng = val.split(',');

                var targetZoom = 16,
                    lat = arlatlng[0],
                    lng = arlatlng[1];

                console.log('map-zoom-to', [val, lat, lng, targetZoom]);

                map.setCenter([lat, lng]);
                map.zoomTo(targetZoom, { duration: 900 });
                break;


            case "toggle-plane":
                map.setCenter(mapboxDefaultLocation);
                map.zoomTo(5, { duration: 900 });
                break;

            case "toggle-town":
                map.setCenter(mapboxDefaultLocation);
                map.zoomTo(mapboxDefaultZoom, { duration: 900 });
                break;

            case "toggle-trees":
                map.setCenter(mapboxDefaultLocation);
                map.zoomTo(14, { duration: 900 });
                break;








                // Autoscroll
            case "auto-scroll":
                var id = $(this).closest('[id]').attr('id');
                config.eventGroups[id].autoscroll = !config.eventGroups[id].autoscroll;
                saveConfig();
                $(this).children('svg').toggleClass('fa-comment fa-comment-slash');
                break;

                // Play / Download
            case "play-download":
                var id = $(this).closest('[id]').attr('id');
                config.eventGroups[id].playdownload = !config.eventGroups[id].playdownload;
                saveConfig();
                $(this).children('svg').toggleClass('fa-play fa-download');
                break;

                // Mute / Unmute
            case "mute-unmute":
                var id = $(this).closest('[id]').attr('id');
                config.eventGroups[id].mute = !config.eventGroups[id].mute;
                saveConfig();
                $(this).children('svg').toggleClass('fa-volume-mute fa-volume-up');
                break;

                // Play Audio Event (DSD+ and File Events)
            case "date":

                // var $elm = $(this).find('.progress-bar');
                var $elm = $(this);
                // Take last character from id and use for instance
                var id = $(this).closest('[id]').attr('id');
                var instance = parseInt(id.substring(id.length - 1, id.length));
                console.log('id', id);

                var file;

                // DSD+
                if (id.indexOf('DSDPlus') > -1) {

                    // Grab date from element value
                    var arDate = $(this).attr('value').split('|');
                    console.log('Click DSD Event', [id, arDate[0], arDate[1]]);
                    file = getDSDWaveFile(instance, arDate[0], arDate[1])

                }

                // File Event
                if (id.indexOf('FileEvent') > -1) {
                    file = getWaveFile($(this).attr('value'));
                }

                if (file) {
                    playSound(file, id, $elm, true);
                }

                break;




            case "open-external":

                var val = $(this).attr('value');
                window.open(val, "_blank");
                break;


        }


        // Data-Action Click
        $(document).on('input', '[data-action]', function() {

            var dataAction = $(this).attr('data-action');
            var el = $(this);

            switch (dataAction) {

                case "config-dynamictime":
                    var val = $('#config-dynamictime').is(':checked');
                    console.log('val', val);
                    config.dynamictime = val;
                    break;

                case "config-dynamictime-duration":
                    var val = $('#config-dynamictime-duration').val();
                    config.dynamictimeduration = val;
                    break;

                case "config-navbar-color":
                    var val = $('#config-navbar-color').val();
                    document.documentElement.style.setProperty('--navbar-color', val);
                    break;


                case "config-primary-color":
                    var val = $('#config-primary-color').val();
                    document.documentElement.style.setProperty('--primary-color', val);
                    break;

                case "config-secondary-color":
                    var val = $('#config-secondary-color').val();
                    document.documentElement.style.setProperty('--secondary-color', val);
                    break;

                case "config-font-color":
                    var val = $('#config-font-color').val();
                    document.documentElement.style.setProperty('--font-color', val);
                    break;

                case "config-icon-color":
                    var val = $('#config-icon-color').val();
                    document.documentElement.style.setProperty('--icon-color', val);
                    break;


                case "config-map-font-color":
                    var val = $('#config-map-font-color').val();
                    document.documentElement.style.setProperty('--map-font-color', val);
                    break;

                case "config-map-icon-color":
                    var val = $('#config-map-icon-color').val();
                    document.documentElement.style.setProperty('--map-icon-color', val);
                    break;

                case "config-scrollbartrack-color":
                    var val = $('#config-scrollbartrack-color').val();
                    document.documentElement.style.setProperty('--scrollbar-track', val);
                    break;

                case "config-scrollbarthumb-color":
                    var val = $('#config-scrollbarthumb-color').val();
                    document.documentElement.style.setProperty('--scrollbar-thumb', val);
                    break;
            }
        });

    });


    // Config Modal
    $('#btnSaveConfig').on('click', function() {

        var dynamictime = $('#config-dynamictime').is(':checked'),
            dynamictimeduration = $('#config-dynamictime-duration').val(),
            // 
            navbar = $('#config-navbar-color').val(),
            primary = $('#config-primary-color').val(),
            secondary = $('#config-secondary-color').val(),
            //  
            font = $('#config-font-color').val(),
            icon = $('#config-icon-color').val(),
            //    
            mapfont = $('#config-map-font-color').val(),
            mapicon = $('#config-map-icon-color').val(),
            scrollbartrack = $('#config-scrollbartrack-color').val(),
            scrollbarthumb = $('#config-scrollbarthumb-color').val();

        // console.log('val', [primary, secondary, navbar, scrollbartrack, scrollbarthumb]);

        config.dynamictime = dynamictime;
        config.dynamictimeduration = dynamictimeduration;

        config.colors.navbar = navbar;
        config.colors.primary = primary;
        config.colors.secondary = secondary;

        config.colors.font = font;
        config.colors.icon = icon;

        config.colors.mapfont = mapfont;
        config.colors.mapicon = mapicon;

        config.colors.scrollbartrack = scrollbartrack;
        config.colors.scrollbarthumb = scrollbarthumb;

        document.documentElement.style.setProperty('--navbar-color', navbar);
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);

        document.documentElement.style.setProperty('--font-color', font);
        document.documentElement.style.setProperty('--icon-color', icon);

        document.documentElement.style.setProperty('--scrollbar-track', scrollbartrack);
        document.documentElement.style.setProperty('--scrollbar-thumb', scrollbarthumb);

        saveConfig();

        $('#modalConfig').modal('hide');


    });

}

function getDSDWaveFile(instance, date, file) {

    var strUrl = ApiServerAddress + $.param({ cmd: 'GetDSDWaveFile', instance: instance, date: date, file: file });
    if (isDebug) {
        console.log('Read DSD Wave File', [instance, date, file]);
    }
    return strUrl;
}

function getWaveFile(file, download = false) {

    var strUrl = ApiServerAddress + $.param({ cmd: 'GetWaveFile', file: file });
    if (isDebug) {
        console.log('Read Wave File', [file, download, strUrl]);
    }

    return strUrl;
}



// unused not done
// filter event-groups by ID -and expand to full

function filterEventGroups(searchId = "") {

    var $allListElements = $(`div.event-group`);

    var $matchingListElements = $allListElements.filter(function(i, li) {
        var listItemText = $(li).attr('id');
        return ~listItemText.indexOf(searchId);
    });

    $allListElements.hide();
    $matchingListElements.show();

    $matchingListElements.each(function(i) {

        $(this).removeClass('col-md-4').removeClass('col-lg-6').addClass('col-lg-12');
        $(this).find('.card-body').height('70vh');
    });

}



// 
// Popovers
// 
$(function() {

    // Settings
    $('[data-toggle="settings-popover"]').on('click', function(e) {

        var id = $(this).closest('[id]').attr('id');

        if ($settingspopovers[id]) {
            $settingspopovers[id].popover('toggle');
        } else {
            initSettingsPopover(id);
        }

    });


    // Filter
    $('[data-toggle="filter-events"]').on('click', function(e) {

        var id = $(this).closest('[id]').attr('id');

        if ($filterpopovers[id]) {
            $filterpopovers[id].popover('toggle');

            var strCssClass = `#${id} .events`;
            $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));

        } else {
            initFilterPopover(id);
        }

    });

});


function initSettingsPopover(id) {

    var volume = config.eventGroups[id].volume;
    var rate = config.eventGroups[id].rate;
    var stereo = config.eventGroups[id].stereo;

    $settingspopovers[id] = $(`#${id} [data-toggle="settings-popover"]`).popover({
        animation: false,
        html: true,
        container: 'body',
        trigger: 'manual',
        title: `SETTINGS`,
        content: `
<div id="controls_${id}">
<button data-action="reset-volume" type="button" class="btn btn-sm ml-2 mr-2"><i class="fas fa-volume-up"></i></button>
<input class="mr-2" type="range" data-action="volume-adjust" min="0" max="1" step="0.01" value="${volume}">VOLUME
<b data-action="current-volume">${Math.round(volume * 100)}%</b>
<br>
<button data-action="reset-rate" type="button" class="btn btn-sm ml-2 mr-2"><i class="fas fa-fast-forward"></i></button>
<input class="mr-2" type="range" data-action="rate-adjust" min="0.5" max="1.5" step="0.01" value="${rate}">RATE
<b data-action="current-rate">${Math.round(rate * 100)}%</b>
<br>
<button data-action="reset-stereo" type="button" class="btn btn-sm ml-2 mr-2"><i class="fas fa-headphones"></i></button>
<input class="mr-2" type="range" data-action="stereo-adjust" min="-1" max="1" step="0.01" value="${stereo}">STEREO
<b data-action="current-stereo">${Math.round(stereo * 100)}%</b>
</div>


`,
    }).on('shown.bs.popover', function(x) {

        console.log('shown.bs.popover');

        var volume = config.eventGroups[id].volume;
        var rate = config.eventGroups[id].rate;
        var stereo = config.eventGroups[id].stereo;

        console.log('shown.bs.popover', [id, x, volume, rate, stereo]);

        $(`#controls_${id} > [data-action="volume-adjust"]`).val(volume).trigger('change');
        // $(`#controls_${id} > [data-action="current-volume"]`).text(Math.round(volume * 100) + '%');

        $(`#controls_${id} > [data-action="rate-adjust"]`).val(rate).trigger('change');
        // $(`#controls_${id} > [data-action=current-rate]`).text(Math.round(rate * 100) + '%');


        $(`#controls_${id} > [data-action="stereo-adjust"]`).val(stereo).trigger('change');
        // $(`#controls_${id} > [data-action="current-stereo"]`).text(Math.round(stereo * 100) + '%');

    });

    // Init Click Events (per control)
    $(document).on('click', `#controls_${id} [data-action]`, function() {

        var dataAction = $(this).attr('data-action');
        var val = $(this).val();

        switch (dataAction) {

            case "reset-volume":
                $(`#controls_${id} [data-action=volume-adjust]`).val(defaultConfig.eventGroups[id].volume).trigger('change');
                break;
            case "reset-rate":
                $(`#controls_${id} [data-action=rate-adjust]`).val(defaultConfig.eventGroups[id].rate).trigger('change');
                break;
            case "reset-stereo":
                $(`#controls_${id} [data-action=stereo-adjust]`).val(defaultConfig.eventGroups[id].stereo).trigger('change');
                break;
        }

    });

    // Init change Events (per control)
    $(document).on('change', `#controls_${id} [data-action]`, function() {

        var dataAction = $(this).attr('data-action');
        var val = $(this).val();

        // var id2 = $(`[aria-describedby="${id}"]`).closest('[id]').attr('id');
        // console.log('on [data-action] change', [id, id2, val, audioInstance, dataAction]);
        // console.log('tt', $(`#controls_${id}`));
        console.log(id, val);

        switch (dataAction) {

            case "volume-adjust":

                config.eventGroups[id].volume = val;
                $(`#controls_${id} [data-action=current-volume]`).text(Math.round(val * 100) + '%');

                if (id == audioInstance) {
                    audioPlayer.volume(val);
                }

                break;

            case "rate-adjust":

                config.eventGroups[id].rate = val;
                $(`#controls_${id} [data-action=current-rate]`).text(Math.round(val * 100) + '%');

                if (id == audioInstance) {
                    audioPlayer.rate(val);
                }
                break;

            case "stereo-adjust":

                var val2 = parseFloat(parseFloat(val).toFixed(1))

                config.eventGroups[id].stereo = val2;
                $(`#controls_${id} [data-action=current-stereo]`).text(Math.round(val2 * 100) + '%');

                if (id == audioInstance) {
                    audioPlayer.stereo(val2);
                }
                break

        }

        saveConfig();
    });


    $settingspopovers[id].popover('show');

}

function initFilterPopover(id) {

    $filterpopovers[id] = $(`#${id} [data-toggle="filter-events"]`).popover({
        animation: false,
        html: true,
        container: 'body',
        trigger: 'manual',
        title: "FILTER",
        content: `
<div id="filter_controls_${id}">
<input data-action="filter-log" type="form-control" class="filter-log" placeholder="Type to Filter" aria-label="Type to Filter">
</div>


`,
    }).on('shown.bs.popover', function(x) {

        console.log('shown.bs.popover');

        $(`#filter_controls_${id} input[data-action="filter-log"]`).focus();

    }).on('hide.bs.popover', function(x) {

        console.log('shown.bs.popover');

        $(`#filter_controls_${id} input[data-action="filter-log"]`).val('').keyup();

    });


    // Filter Log
    $(document).on('keyup', `#filter_controls_${id} input[data-action="filter-log"]`, function() {

        // console.log('filter_controls_', id);
        var that = this;

        if (id == 'MapboxMap') {

            var $allListElements = $(`#map .marker`);

            var $matchingListElements = $allListElements.filter(function(i, li) {
                var listItemText = $(li).html().toUpperCase(),
                    searchText = that.value.toUpperCase();
                return ~listItemText.indexOf(searchText);
            });

            $allListElements.hide();
            $matchingListElements.show();

        } else {

            var $allListElements = $(`#${id} .events > div[class*=badge-message`);

            var $matchingListElements = $allListElements.filter(function(i, li) {
                var listItemText = $(li).html().toUpperCase(),
                    searchText = that.value.toUpperCase();
                return ~listItemText.indexOf(searchText);
            });

            $allListElements.hide();
            $matchingListElements.show();
        }



    });


    $filterpopovers[id].popover('show');

}