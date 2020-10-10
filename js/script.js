var isDebug = hid_isDebug.value,
    AuthRequired = hid_AuthRequired.value,
    AuthUsername, AuthPassword;

if (AuthRequired) {
    var AuthUsername = hid_AuthUsername.value,
        AuthPassword = hid_AuthPassword.value;
}


var WebSocketAdress = "wss://josieinthedark.ddns.net:8080/events";
if (isDebug) {
    // WebSocketAdress = "wss://192.168.0.150:8080/events";
}

// Append token,username,password to socket address if authorisation is required
if (AuthRequired) {
    WebSocketAdress = WebSocketAdress + ('?payload=' + encodeURIComponent(btoa('token=AnyStringYouWantPasswedToSocket&username=' + AuthUsername + '&password=' + AuthPassword)));
}

const socket = new WebSocket(WebSocketAdress);


// Date Time Formatting for Messages
var strDateTimeFormat = "MMM Do - HH:mm:ss";

// Mapbox Access Token - Free for small scale projects https://account.mapbox.com/auth/signup/
var mapboxAcessToken = "pk.eyJ1IjoiaWFucmJvd21hbiIsImEiOiJjazdvejVjejQwMThhM2VvM3RibXZiOWl5In0.vv7zkuH0TJw5r_g4YdAL1A";

// var strDateTimeFormat = "ddd, MMM Do - HH:mm:ss";

// Audio Player - also instances use one player
var audioPlayer = new Audio();
audioPlayer.pause();
var autoplayStreams = [true, true, true, true],
    muteAll = false;

// Map
var map, map3d = false,
    markers = [];;


// DSD+
var replacementDSDGroups = [],
    replacementDSDRadios = [];

// Connection opened
socket.addEventListener('open', function(event) {

    // socket.send('Hello Server!');
    console.log('server connected');
});

// Listen for messages
socket.addEventListener('message', function(event) {

    event = JSON.parse(event.data);

    // console.log('S', event);

    var strCssClass = '#DSDPlus' + event.instance + ' .events';


    if (event.cmd == 'DSDConfig') {

        for (var i in event.groups) {


            var group = event.groups;
            console.log(group);
        }

        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    if (event.cmd == 'DSDEvents') {
        for (var i in event.events) {
            processDsdEvent(strCssClass, event.events[i]);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    if (event.cmd == 'DSDEvent') {

        var strDate = event.event[0];
        var arDate = strDate.split(' ');
        var strDate = arDate[0].replaceAll("/", "")
        var strTime = arDate[arDate.length - 1].replaceAll(':', '');

        processDsdEvent(strCssClass, event.event, true);


        // Autoscroll
        if (autoplayStreams[event.instance]) {
            $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
        }
        // Autoplay
        if (audioPlayer.ended && autoplayStreams[event.instance]) {
            readDSDWaveFile((event.instance + 1), strDate, strTime);
        }

    }

    if (event.cmd == 'DSDLRRP') {

        var speed = event.event[5];
        var heading = event.event[6];

        var popup = new mapboxgl.Popup({ offset: 25 }).setHTML(
            `<b>${event.event[2]}</b><br>${event.event[1]} <br> ${event.event[3]}, ${event.event[4]} <br> Speed: ${speed} km/h<br>Heading: ${heading}°`
        );

        if (markers[event.event[2]]) {
            // markers[event.event[2]].setIcon('popup');
            markers[event.event[2]].setPopup(popup);
            markers[event.event[2]].setLngLat([event.event[4], event.event[3]]);
        } else {
            markers[event.event[2]] = new mapboxgl.Marker();
            markers[event.event[2]].setPopup(popup);
            markers[event.event[2]].setLngLat([event.event[4], event.event[3]]);
            markers[event.event[2]].addTo(map);
        }

    }


    strCssClass = '#FileEvent' + event.instance + ' .events';

    if (event.cmd == 'FileEvents') {
        for (var i in event.events) {
            processFileEvent(strCssClass, event.events[i], event.instance);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }
    if (event.cmd == 'FileEvent') {
        processFileEvent(strCssClass, event.event, event.instance, true);
    }


    strCssClass = '#rtl433Event' + event.instance + ' .events';

    if (event.cmd == 'rtl433Events') {
        for (var i in event.events) {
            processRtl433(strCssClass, event.events[i]);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    if (event.cmd == 'rtl433Event') {
        processRtl433(strCssClass, event.event, true);
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }


});

function processDsdEvent(strCssClass, event, newEvent = false) {

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
            $(this).children('.badge-date').text(moment(date).format(strDateTimeFormat)).removeClass('badge-warning').addClass('badge-info');
        }
    });

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
            $(this).children('.badge-date').text(moment.unix(date).format(strDateTimeFormat)).removeClass('badge-warning').addClass('badge-info');
        }
    });

    // Autoplay
    if (newEvent && audioPlayer.paused && autoplayStreams[instance]) {
        var strFile = event[0];
        readWaveFile(strFile);
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

}


function processRtl433(strCssClass, event, newEvent = false) {

    var date = event[1];
    var strdate = newEvent == true ? (moment(date).fromNow()) : moment(date).format(strDateTimeFormat);

    var Id = event[0];
    var strName = Id;

    var strEvent = event[5];
    var strEventClasss = (event[5] == 'open' ? 'success' : 'warning');
    var strNameClass = "info";

    if (Id == 163415) {
        strName = "Front Door";
        strNameClass = 'danger';
    }

    if (Id == 209847) {
        strName = "Dog Door?";
        strNameClass = 'warning';
    }

    var strDateClass = newEvent ? 'badge-warning' : 'badge-info';

    $(`<p class="badge-message" style="" data-action="date" value="${event[1]}">
        <span class="badge badge-pill badge-date ${strDateClass}">${strdate}</span>
        <span class="badge badge-pill badge-info">${event[2]}</span>
        <span class="badge badge-pill badge-${strNameClass}">${strName}</span>
        <span class="badge badge-pill badge-${strEventClasss}">${strEvent}</span>
    </p>`).appendTo(strCssClass, date).delay(5000).queue(function() {
        if (newEvent) {
            $(this).children('.badge-date').toggleClass('badge-warning badge-info').text(moment(date).format(strDateTimeFormat));
        }
    });

}

function switchLayer(layer) {
    map.setStyle('mapbox://styles/mapbox/' + layer);
}


$(function() {


    loadLRRPMap();


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

    // Main Menu
    $(document).on('click', '#main-nav a', function() {

        var val = $(this).attr('href');
        console.log('val', val);


        switch (val) {
            case '#mute-all':
                muteAll = !muteAll;

                for (var i in autoplayStreams) {
                    autoplayStreams[i] = muteAll;
                }
                if (muteAll) {
                    $(this).text('Unmute All');
                    $('[data-action="play-pause"]').removeClass('fa-volume-up').addClass('fa-volume-mute');
                } else {
                    $(this).text('Mute All');
                    $('[data-action="play-pause"]').removeClass('fa-volume-mute').addClass('fa-volume-up');
                }
                break;
        }
    });


    // Mute Stream
    $(document).on('click', '[data-action="play-pause"]', function() {
        var parent = $(this).closest('[id]').attr('id');
        parentIndex = parseInt(parent.substring(parent.length - 1, parent.length));

        if (parent.indexOf('FileEvent') > -1) {
            parentIndex = (parentIndex + 2);
        }
        // console.log('autoplayStreams', [parentIndex, autoplayStreams]);
        autoplayStreams[parentIndex] = !autoplayStreams[parentIndex];
        $(this).toggleClass('fa-volume-mute fa-volume-up');
    });


    // Play DSD
    $(document).on('click', 'div[id^="DSDPlus"] .events p[data-action="date"]', function() {

        var parentIndex = $(this).closest('[id]').attr('id');
        parentIndex = parentIndex.substring(parentIndex.length - 1, parentIndex.length);
        parentIndex++;

        var strDate = $(this).attr('value').replaceAll('/', '').replaceAll(':', '');

        var bd = $(this).children('.badge-date');
        if ($(bd).hasClass('badge-warning')) {
            $(this).children('.badge-date').toggleClass('badge-warning badge-info');
        }



        $(this).children('.badge-date').animate({ "left": "+=50px" }, "slow");

        var arDate = strDate.split(' ');
        var strDate = arDate[0];
        var strTime = arDate[1];
        console.log('arDate', arDate);

        readDSDWaveFile(parentIndex, strDate, strTime);

    });
    //  Download DSD
    // var touchCounterDSD;
    // $(document).on('touchstart', 'div[id^="DSDPlus"] .events p[data-action="date"]', function() {
    //     var count = 0;
    //     var val = $(this);
    //     touchCounterDSD = setInterval(function(val) {
    //         count++;
    //         if (count >= 3) {
    //             // alert('touch lasted 3 seconds');
    //             var parentIndex = $(val).closest('[id]').attr('id');
    //             parentIndex = parentIndex.substring(parentIndex.length - 1, parentIndex.length);
    //             parentIndex++;

    //             var strDate = $(val).attr('value').replaceAll('/', '').replaceAll(':', '');

    //             var bd = $(val).children('.badge-date');
    //             if ($(bd).hasClass('badge-warning')) {
    //                 $(val).children('.badge-date').toggleClass('badge-warning badge-info');
    //             }

    //             var arDate = strDate.split(' ');
    //             var strDate = arDate[0];
    //             var strTime = arDate[1];
    //             console.log('arDate', arDate);

    //             downloadDSDWaveFile(parentIndex, strDate, strTime);

    //         }
    //     }, 1000, val);

    //     var val = $(this);
    //     console.log('touchstart', val);

    // });

    // $(document).on('touchend', 'div[id^="DSDPlus"] .events p[data-action="date"]', function() {
    //     var val = $(this);
    //     console.log('touchend', val);

    // });



    // 
    // Play File Event
    $(document).on('click', 'div[id^="FileEvent"] .events p[data-action="date"]', function() {
        var strFile = $(this).attr('value');
        var bd = $(this).children('.badge-date');
        if ($(bd).hasClass('badge-warning')) {
            $(this).children('.badge-date').toggleClass('badge-warning badge-info');
        }

        readWaveFile(strFile);
    });


});






function readDSDWaveFile(instance, date, time) {

    var strUrl = 'https://josieinthedark.ddns.net/api.php?' + $.param({ cmd: 'ReadDSDWaveFile', instance: instance, date: date, time: time });
    // console.log('Read DSD Wave File', [instance, date, time]);
    audioPlayer.src = strUrl;
    audioPlayer.play();
}

function downloadDSDWaveFile(instance, date, time) {

    var strUrl = 'https://josieinthedark.ddns.net/api.php?' + $.param({ cmd: 'ReadDSDWaveFile', instance: instance, date: date, time: time });
    window.open(strUrl, '_blank');
}



function readWaveFile(file) {

    var strUrl = 'https://josieinthedark.ddns.net/api.php?' + $.param({ cmd: 'ReadWaveFile', file: file });
    console.log('Read Wave File', [file]);
    audioPlayer.src = strUrl;
    audioPlayer.play();
}

function loadLRRPMap() {

    mapboxgl.accessToken = mapboxAcessToken;
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
                        'description': '<strong>STRONGHOLD BASE</strong><p> Dugs wee</p>',
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