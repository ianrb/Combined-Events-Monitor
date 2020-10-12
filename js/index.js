var isDebug = hid_isDebug.value,
    ServerAddress = hid_ServerAddress.value,
    AuthRequired = hid_AuthRequired.value,
    AuthUsername, AuthPassword;

if (AuthRequired) {
    AuthUsername = hid_AuthUsername.value;
    AuthPassword = hid_AuthPassword.value;
}

var ApiServerAddress = "/api.php?",
    WebSocketAddress = "wss://" + ServerAddress + ":8080/events";

// Append token,username,password to socket address if authorisation is required
if (AuthRequired) {
    WebSocketAddress = WebSocketAddress + ('?payload=' + encodeURIComponent(btoa('token=AnyStringYouWantPassedToSocket&username=' + AuthUsername + '&password=' + AuthPassword)));
}

const socket = new WebSocket(WebSocketAddress);


// Date Time Formatting for Messages
var strDateTimeFormat = "MMM Do - HH:mm:ss";

// Mapbox Access Token - Free for small scale projects https://account.mapbox.com/auth/signup/
var mapboxAccessToken = "pk.eyJ1IjoiaWFucmJvd21hbiIsImEiOiJjazdvejVjejQwMThhM2VvM3RibXZiOWl5In0.vv7zkuH0TJw5r_g4YdAL1A";


// 
// Audio Player - Upgraded to howlerjs
// 
var audioPlaying = false,
    audioInstance,
    audioPlayer, muteAll = false;

// Mapbox
var map, map3d = false,
    markers = [];;

// DSD+
var replacementDSDGroups = [],
    replacementDSDRadios = [];


// Load localStorage Config
var config = getConfig();
console.log('config', config);



// 
// jQuery Loaded
// 
$(function() {

    // Load LRRP Map
    loadLRRPMap();

    initThemes();

    initClickEvents();


    // Turn off muted steams from config
    for (var i in config.mute) {
        if (config.mute[i]) {
            $('[data-action="mute-unmute"]').eq(i).removeClass('fa-volume-up').addClass('fa-volume-mute');
        }
    }

    // Turn off muted steams from config
    for (var i in config.autoscroll) {
        if (!config.autoscroll[i]) {
            $('[data-action="auto-scroll"]').eq(i).removeClass('fa-comment').addClass('fa-comment-slash');
        }
    }

    // Set Volume
    for (var i in config.volume) {
        $('.volume-selector').eq(i).val(config.volume[i]);
    }


});





function playSound(file, instance, force = false) {

    if (audioPlaying && !force) { return; }
    if (audioPlaying) {
        audioPlayer.stop();
    }

    var volume = config.volume[instance];
    if (isDebug) {
        console.log('Play Sound', [file, instance, force, volume]);
    }

    audioPlaying = true;
    audioInstance = instance;


    // Highlight Audio Icon
    $('[data-action="mute-unmute"]').css('color', 'unset');
    $('[data-action="mute-unmute"]').eq(instance).css('color', 'red');


    audioPlayer = new Howl({
        src: file,
        format: ['wav'],
        autoplay: true,
        volume: volume,
        onend: function() {
            console.log('Finished!');
            audioPlaying = false;

            // Remove Highlighting after audio plays
            $('[data-action="mute-unmute"]').css('color', 'unset');

        }
    });

    audioPlayer.play();

}


// Connection opened
socket.addEventListener('open', function(event) {

    // socket.send('Hello Server!');
    console.log('server connected');
});

// Listen for messages
socket.addEventListener('message', function(event) {

    event = JSON.parse(event.data);

    var strCssClass = '#DSDPlus' + event.instance + ' .events';

    if (event.cmd == 'DSDConfig') {

        for (var i in event.groups) {
            var group = event.groups;
            if (isDebug) {
                console.log(group);
            }
            replacementDSDGroups.push(group);
        }

        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    if (event.cmd == 'DSDEvents') {
        for (var i in event.events) {
            processDsdEvent(strCssClass, event.instance, event.events[i]);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    if (event.cmd == 'DSDEvent') {
        processDsdEvent(strCssClass, event.instance, event.event, true);
    }


    // 
    // LRRP Events
    // 

    // Recent LRRP Events
    if (event.cmd == 'DSDRecentLRRP') {
        for (var i in event.events) {
            processLRRPEvent(event.events[i]);
        }
    }
    // New LRRP Event
    if (event.cmd == 'DSDLRRP') {
        processLRRPEvent(event.event);
    }


    // 
    // File Events
    // 

    strCssClass = '#FileEvent' + event.instance + ' .events';

    // File Events
    if (event.cmd == 'FileEvents') {
        for (var i in event.events) {
            processFileEvent(strCssClass, event.events[i], event.instance);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    // File Event
    if (event.cmd == 'FileEvent') {
        processFileEvent(strCssClass, event.event, event.instance, true);
    }


    // 
    // rtl_433 Events
    // 
    strCssClass = '#rtl433Event' + event.instance + ' .events';

    //  rtl_433 Events
    if (event.cmd == 'rtl433Events') {
        for (var i in event.events) {
            processRtl433(strCssClass, event.events[i]);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    //  rtl_433 Event
    if (event.cmd == 'rtl433Event') {
        processRtl433(strCssClass, event.event, true);
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }


});

function processDsdEvent(strCssClass, instance, event, newEvent = false) {

    var date = event[0];

    var strDate = moment(date).format(strDateTimeFormat),
        tg = event[1],
        rid = event[2],
        slot = event[3],
        duration = event[4],
        ixDuration = ((duration < 3 ? 3 : duration) * 2);

    var strDateClass = newEvent ? 'badge-warning' : 'badge-info';

    $(`<p class="badge-message" style="padding-top: ${ixDuration + 5}px; padding-bottom: ${ixDuration + 5}px;" data-action="date" value="${date}">
        <span class="badge badge-pill badge-date ${strDateClass}">${strDate}</span>
        <span class="badge badge-pill badge-primary">${tg}</span>
        <span class="badge badge-pill badge-success">${rid}</span>        
        <span class="badge badge-pill badge-duration" style="padding-top: ${ixDuration}px; padding-bottom: ${ixDuration}px; margin-top: -${ixDuration}px;">${duration}s</span>
    </p>`).appendTo(strCssClass).delay(duration * 1000).queue(function() {
        if (newEvent) {
            // Remove oldest message
            $(strCssClass).children('p.badge-message').first().remove();
            // Define 5sec interval to update moment time 'fromNow()' and then after 5 mins convert to standard date time
            $(this).children('.badge-date').toggleClass('badge-warning badge-info');
            var ix = 0;
            var momentInterval = setInterval(() => {
                $(this).children('.badge-date').text(moment(date).fromNow());
                ix++;
                if (ix > 100) {
                    $(this).children('.badge-date').text(moment(date).format(strDateTimeFormat));
                    clearInterval(momentInterval);
                }
            }, 3000);
        }
    });

    // Auto-Scroll
    if (newEvent && config.autoscroll[instance]) {
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    // Auto-Play
    if (newEvent && !config.mute[instance]) {
        playSound(getDSDWaveFile(instance, strDate, strTime), instance);
    }

}

function processLRRPEvent(event) {

    var speed = event[5];
    var heading = event[6];

    var popup = new mapboxgl.Popup({ offset: 25 }).setHTML(
        `<b>${event[2]}</b><br>${event[1]} <br> ${event[3]}, ${event[4]} <br> Speed: ${speed} km/h<br>Heading: ${heading}°`
    );

    if (markers[event[2]]) {
        markers[event[2]].setPopup(popup);
        markers[event[2]].setLngLat([event[4], event[3]]);
    } else {
        markers[event[2]] = new mapboxgl.Marker();
        markers[event[2]].setPopup(popup);
        markers[event[2]].setLngLat([event[4], event[3]]);
        markers[event[2]].addTo(map);
    }
}

function processFileEvent(strCssClass, event, instance, newEvent = false) {


    instance = (instance + 2);
    var file = event[0];
    var date = event[1];
    var strDate = newEvent == true ? (moment.unix(date).fromNow()) : moment.unix(date).format(strDateTimeFormat);
    var strDateClass = newEvent ? 'badge-warning' : 'badge-info';
    var duration = event[2];
    var iDuration = duration;

    if (typeof iDuration !== 'number' || iDuration < 4) {
        iDuration = 3;
    }
    var ixDuration = ((iDuration < 3 ? 3 : iDuration) * 2);

    $(`<p class="badge-message" style="padding-top: ${ixDuration + 5}px; padding-bottom: ${ixDuration + 5}px;" data-action="date" value="${event[0]}">
        <span class="badge badge-pill badge-date ${strDateClass}">${strDate}</span>
        <span class="badge badge-pill badge-duration" style="padding-top: ${ixDuration}px; padding-bottom: ${ixDuration}px; margin-top: -${ixDuration}px;">${duration}s</span>
    </p>`).appendTo(strCssClass, date).delay(duration * 1000).queue(function() {
        if (newEvent) {
            // Remove oldest message
            $(strCssClass).children('p.badge-message').first().remove();
            // Define 5sec interval to update moment time 'fromNow()' and then after 5 mins convert to standard date time
            $(this).children('.badge-date').toggleClass('badge-warning badge-info');
            var ix = 0;
            var momentInterval = setInterval(() => {
                $(this).children('.badge-date').text(moment.unix(date).fromNow());
                ix++;
                if (ix > 100) {
                    $(this).children('.badge-date').text(moment.unix(date).format(strDateTimeFormat));
                    clearInterval(momentInterval);
                }
            }, 3000);
        }
    });

    // Auto-Scroll
    if (newEvent && config.autoscroll[instance]) {
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    // Autoplay
    if (newEvent && !config.mute[instance]) {
        playSound(getWaveFile(event[0]), instance);
    }

}


function processRtl433(strCssClass, event, newEvent = false) {

    var date = event[1];
    var strDate = newEvent == true ? (moment(date).fromNow()) : moment(date).format(strDateTimeFormat);

    var Id = event[0];
    var strName = Id;

    var strEvent = event[5];
    var strEventClass = (event[5] == 'open' ? 'success' : 'warning');
    var strNameClass = "info";

    switch (Id) {
        case 163415:
            strName = "Front Door";
            strNameClass = 'danger';
            break;

        case 209847:
            strName = "Dog Door";
            strNameClass = 'danger';
            break;

    }

    var strDateClass = newEvent ? 'badge-warning' : 'badge-info';

    $(`<p class="badge-message" style="" data-action="date" value="${event[1]}">
        <span class="badge badge-pill badge-date ${strDateClass}">${strDate}</span>
        <span class="badge badge-pill badge-info">${event[2]}</span>
        <span class="badge badge-pill badge-${strNameClass}">${strName}</span>
        <span class="badge badge-pill badge-${strEventClass}">${strEvent}</span>
    </p>`).appendTo(strCssClass, date).delay(5000).queue(function() {
        if (newEvent) {
            // Remove oldest message
            $(strCssClass).children('p.badge-message').first().remove();
            // Define 5sec interval to update moment time 'fromNow()' and then after 5 mins convert to standard date time
            $(this).children('.badge-date').toggleClass('badge-warning badge-info');
            var ix = 0;
            var momentInterval = setInterval(() => {
                $(this).children('.badge-date').text(moment(date).fromNow());
                ix++;
                if (ix > 100) {
                    $(this).children('.badge-date').text(moment(date).format(strDateTimeFormat));
                    clearInterval(momentInterval);
                }
            }, 3000);
        }
    });

}



// 
// Configuration
// 
function getConfig() {
    var obj = localStorage.getItem('config');

    // No Config - Load Default
    if (obj == null) {
        console.warn('Configuration not found resetting');
        resetConfig();
        getConfig();
    }

    var json = JSON.parse(obj);

    // Out of Sync Config - Missing attributes
    if (json.mute == null || json.volume == null) {
        resetConfig();
        getConfig();
    }


    return json;
}

function resetConfig() {

    console.warn('Resetting Configuration');
    localStorage.setItem('config', JSON.stringify({
        mute: [false, false, false, false],
        volume: [.75, .75, .75, .75],
        autoscroll: [true, true, true, true],
    }));
}

function saveConfig() {
    localStorage.setItem('config', JSON.stringify(config));
}

// Themes
function initThemes() {

    // Init 
    var huebPrimaryColor = new Huebee(txtPrimaryColor, {
        // options
    }).on('change', function(color) {
        console.log('change', color);
    });


}

function initClickEvents() {

    // Main Menu
    $(document).on('click', '#main-nav a', function() {

        var val = $(this).attr('href');

        switch (val) {
            case '#mute-all':
                muteAll = !muteAll;

                for (var i in config.mute) {
                    config.mute[i] = muteAll;
                }
                if (muteAll) {
                    $(this).text('Unmute All');
                    $('[data-action="mute-unmute"]').removeClass('fa-volume-up').addClass('fa-volume-mute');
                } else {
                    $(this).text('Mute All');
                    $('[data-action="mute-unmute"]').removeClass('fa-volume-mute').addClass('fa-volume-up');
                }
                break;

            case '#theme':
                $('#modalTheme').modal();

                break;
        }
    });


    // Adjust Stream Volume
    $(document).on('change', '.volume-selector', function() {

        var parent = $(this).closest('[id]').attr('id');
        parentIndex = parseInt(parent.substring(parent.length - 1, parent.length));

        if (parent.indexOf('FileEvent') > -1) {
            parentIndex = (parentIndex + 2);
        }

        var val = $(this).val();

        config.volume[parentIndex] = val;

        console.log('parentIndex', parentIndex);
        console.log('audioInstance', audioInstance);

        if (parentIndex == audioInstance) {
            audioPlayer.volume(val);
        }
        saveConfig();
    });

    // Mute/Un-Mute Stream
    $(document).on('click', '[data-action="mute-unmute"]', function() {
        var parent = $(this).closest('[id]').attr('id');
        parentIndex = parseInt(parent.substring(parent.length - 1, parent.length));

        if (parent.indexOf('FileEvent') > -1) {
            parentIndex = (parentIndex + 2);
        }
        config.mute[parentIndex] = !config.mute[parentIndex];
        saveConfig();

        $(this).toggleClass('fa-volume-mute fa-volume-up');
    });


    // AutoScroll Stream
    $(document).on('click', '[data-action="auto-scroll"]', function() {
        var parent = $(this).closest('[id]').attr('id');
        parentIndex = parseInt(parent.substring(parent.length - 1, parent.length));

        if (parent.indexOf('FileEvent') > -1) {
            parentIndex = (parentIndex + 2);
        }
        config.autoscroll[parentIndex] = !config.autoscroll[parentIndex];
        saveConfig();

        $(this).toggleClass('fa-comment fa-comment-slash');
    });



    // Play Audio Event (DSD+ and File Events)
    $(document).on('click', 'div .events p[data-action="date"]', function() {

        // Take last character from id and use for instance
        var id = $(this).closest('[id]').attr('id');
        var instance = parseInt(id.substring(id.length - 1, id.length));

        // Mark event as read
        var $bd = $(this).children('.badge-date');
        if ($bd.hasClass('badge-warning')) {
            $bd.children('.badge-date').toggleClass('badge-warning badge-info');
        }

        var file;

        // DSD+
        if (id.indexOf('DSDPlus') > -1) {

            // Grab date from element value
            var arDate = $(this).attr('value').replaceAll('/', '').replaceAll(':', '').split(' ');
            console.log('Click DSD Event', [instance, arDate[0], arDate[1]]);
            file = getDSDWaveFile(instance, arDate[0], arDate[1])

        }


        // File Event
        if (id.indexOf('FileEvent') > -1) {
            file = getWaveFile($(this).attr('value'));
            instance = instance + 2;

        }

        if (file) {
            playSound(file, instance, true);
        }
    });

}

function getDSDWaveFile(instance, date, time) {

    var strUrl = ApiServerAddress + $.param({ cmd: 'GetDSDWaveFile', instance: instance, date: date, time: time });
    if (isDebug) {
        console.log('Read DSD Wave File', [instance, date, time]);
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




function loadLRRPMap() {

    function switchLayer(layer) {
        map.setStyle('mapbox://styles/mapbox/' + layer);
    }

    $(document).on('change', 'select.select-theme', function() {

        var val = $(this).find('option:selected').val();
        console.log('val', val);

        switchLayer(val);
    });


    $(document).on('click', '[data-action="toggle-3d"]', function() {

        map3d = !map3d;

        if (map3d) {

            $(this).removeClass('fa-map').addClass('fa-cube');
            var layers = map.getStyle().layers;
            var labelLayerId;
            for (var i = 0; i < layers.length; i++) {
                if (layers[i].type === 'symbol' && layers[i].layout['text-field']) {
                    labelLayerId = layers[i].id;
                    break;
                }
            }

            map.addLayer({
                    'id': '3d-buildings',
                    'source': 'composite',
                    'source-layer': 'building',
                    'filter': ['==', 'extrude', 'true'],
                    'type': 'fill-extrusion',
                    'minzoom': 15,
                    'paint': {
                        'fill-extrusion-color': '#aaa',
                        'fill-extrusion-height': [
                            'interpolate', ['linear'],
                            ['zoom'],
                            15,
                            0,
                            15.05, ['get', 'height']
                        ],
                        'fill-extrusion-base': [
                            'interpolate', ['linear'],
                            ['zoom'],
                            15,
                            0,
                            15.05, ['get', 'min_height']
                        ],
                        'fill-extrusion-opacity': 0.6
                    }
                },
                labelLayerId
            );


        } else {
            $(this).removeClass('fa-cube').addClass('fa-map');
        }
    });


    mapboxgl.accessToken = mapboxAccessToken;
    map = new mapboxgl.Map({
        container: 'map',
        center: [-116.4336, 53.5850],
        zoom: 11,
        style: 'mapbox://styles/mapbox/dark-v10',
        pitch: 45,
        bearing: -17.6,
        container: 'map',
        antialias: true
    });


    map.on('load', function() {

        map.addSource('places', {
            'type': 'geojson',
            'data': {
                'type': 'FeatureCollection',
                'features': [{
                    'type': 'Feature',
                    'properties': {
                        'description': '<strong>BASECAMP</strong><p> Dugs wee</p>',
                        'icon': 'campsite'
                    },
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-116.449768, 53.5866813]
                    }
                }]
            }
        });


        // Add a layer showing the places.
        map.addLayer({
            'id': 'places',
            'type': 'symbol',
            'source': 'places',
            'layout': {
                'icon-image': '{icon}-15',
                'icon-allow-overlap': true
            }
        });

        // When a click event occurs on a feature in the places layer, open a popup at the
        // location of the feature, with description HTML from its properties.
        map.on('click', 'places', function(e) {
            var coordinates = e.features[0].geometry.coordinates.slice();
            var description = e.features[0].properties.description;

            // Ensure that if the map is zoomed out such that multiple
            // copies of the feature are visible, the popup appears
            // over the copy being pointed to.
            while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
            }

            new mapboxgl.Popup()
                .setLngLat(coordinates)
                .setHTML(description)
                .addTo(map);
        });

        // Change the cursor to a pointer when the mouse is over the places layer.
        map.on('mouseenter', 'places', function() {
            map.getCanvas().style.cursor = 'pointer';
        });

        // Change it back to a pointer when it leaves.
        map.on('mouseleave', 'places', function() {
            map.getCanvas().style.cursor = '';
        });



    });
}