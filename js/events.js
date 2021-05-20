var progressInterval;
var lastDSDEventDate = [],
    lastFileEventDate = [];
var audioPlayer;
var rtl433LastEvent = {};


const socket = new WebSocket(WebSocketAddress);

socket.onerror = function(event) {

    $('#modalSelfSignedBypass').modal();
};

function checkUnplayedEvents() {

    if (unplayedEvents.length > 0) {

        var event = unplayedEvents[0];
        console.log('unplayedEvents', event);

        playSound(event[0], event[1]);
        unplayedEvents.shift();
    }
}

function downloadFile(file, id) {

    window.open(file, "_blank");

}

function playSound(file, id, $elem, force = false) {

    console.warn('Play Sound - Element', $elem);


    if (!config.linearPlayback && audioPlaying) {

        if (force) {
            // console.log('Stop Playback (force)');
            audioPlayer.stop();
        } else {
            console.log('Event Added to Unplayed Events', [file, id]);
            unplayedEvents.push([file, id]);
            return;
        }
    }

    // If Instance Playing - Stop Audio
    if (audioPlaying && typeof instance !== 'undefined' && id == instance) {
        console.error("Already playing setting false return");
        audioPlaying = false;
        return;
    }


    var ec = config.eventGroups[id];

    if (isDebug) {
        console.log('Play Sound', [file, id, force, ec]);
    }
    console.log('Play Sound', [file, id, force, ec]);

    audioInstance = id;

    // if (audioPlayer) {
    //     audioPlayer.stop();
    // }

    console.log('ec stereo', ec.stereo);

    // var audioPlayer = new Howl({
    audioPlayer = new Howl({
        src: file,
        format: ['wav'],
        autoplay: true,
        volume: ec.volume,
        rate: ec.rate,
        stereo: ec.stereo,
        onload: function() {

            if ($elem) {

                console.log('Remove Highlighting1', $elem);

                $elem.find('.progress').removeClass('badge-warning').addClass('badge-info');

                // alert('wow');

                // console.log('Remove Highlighting2', $elem.closest('.progress'));
                // $elem.closest('.progress').removeClass('badge-warning').addClass('badge-info');



            }

            // Progress Animation
            // add 1sec to call to prevent stop
            var duration = parseInt(audioPlayer._duration) * 1000 + 1000;
            // console.log("LOADED", duration); 
            $('#' + id + ' [data-action="mute-unmute"] svg').css('color', 'var(--font-color)');

            if ($elem) {
                $elem2 = $elem.find('.progress-bar');

                if ($elem2) {


                    var ix = 0;
                    var momentInterval = setInterval(() => {

                        var diff = parseInt(((ix / duration) * 100));
                        $elem2.css('width', diff + '%');

                        console.log("audioPlaying", audioPlaying);


                        if (!audioPlaying | (ix >= duration)) {
                            console.warn("audioPlaying skipping", audioPlaying);
                            $elem2.css('width', '100%');

                            // audioPlayer.stop();
                            clearInterval(momentInterval);
                        }

                        ix = (ix + 100);
                    }, 100);
                }
            }
        },
        onplay: function() {

            audioPlaying = true;

        },
        onpause: function() {
            console.log("onpause");
            // audioPlaying = false;
            $('#' + id + ' [data-action="mute-unmute"] svg').css('color', 'var(--icon-color)');
            // $('#' + id + ' [data-action="mute-unmute"] svg').css('color', 'unset');

        },
        onstop: function() {
            console.log("ONSTOP");
            audioPlaying = false;
            $('#' + id + ' [data-action="mute-unmute"] svg').css('color', 'var(--icon-color)');
            // $('#' + id + ' [data-action="mute-unmute"] svg').css('color', 'unset');

            checkUnplayedEvents();

        },
        // Check Unplayed Events (events that were added to array because force flag was not true)
        onend: function() {
            console.log("ONEND");
            audioPlaying = false;

            $('#' + id + ' [data-action="mute-unmute"] svg').css('color', 'var(--icon-color)');
            checkUnplayedEvents();
        }
    });

    // console.log('play audio at rate', audioPlayer.stereo);

    // audioPlayer.play();

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

        console.log('DSDConfig', event);

        for (var i in event.event.groups) {
            var group = event.event.groups[i];
            // if (isDebug) {
            // console.log(group);
            // }
            replacementDSDGroups.push(group);
        }

        for (var i in event.event.radios) {
            var radio = event.event.radios[i];
            // if (isDebug) {
            // console.log(radio);
            // }
            replacementDSDRadios.push(radio);
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
    if (event.cmd == 'DSDLRRPs') {
        for (var i in event.events) {
            processLRRPEvent(event.events[i]);
        }
    }
    // New LRRP Event
    if (event.cmd == 'DSDLRRP') {
        processLRRPEvent(event.event);
    }


    // 
    // Aircraft Events
    // 

    // Recent Aircraft Events
    if (event.cmd == 'AircraftEvents') {
        for (var i in event.events) {
            processAircraftEvent(event.events[i]);
        }
    }
    // New Aircraft Event
    if (event.cmd == 'AircraftEvent') {
        processAircraftEvent(event.event);
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
            processRtl433(strCssClass, event.instance, event.events[i]);
        }
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    //  rtl_433 Event
    if (event.cmd == 'rtl433Event') {
        processRtl433(strCssClass, event.instance, event.event, true);
    }


});

function processDsdEvent(strCssClass, instance, event, newEvent = false) {


    if (!event || !event[0]) {
        return;
    }
    // console.log('event', event);


    var date = event[0],
        time = event[1],
        file = event[2],
        slot = event[3],
        tg = event[4],
        rid = event[5],
        duration = parseInt(event[6]);

    var $mDate = moment.unix(time);
    var strDateTime = (newEvent == true ? $mDate.fromNow() : $mDate.format(strDateTimeFormat));

    if ($mDate && !$mDate.isValid()) {
        console.warn('Skipping Invalid Date', file);
        return;
    }

    // var durr = moment(new Date()).diff($mDate) / 1000 / 60;
    var replacementRadio = replacementDSDRadios.filter(word => word[0] == rid)[0];
    if (replacementRadio) {
        rid = replacementRadio[1];
    }

    var replacementGroup = replacementDSDGroups.filter(word => word[0] == tg)[0];
    if (replacementGroup) {
        tg = replacementGroup[1];
    }
    // 
    var ixDuration = ((duration > 20 ? 20 : duration < 3 ? 3 : duration) * 12);

    var strDateClass = newEvent ? 'badge-warning' : 'badge-info';

    // If New Date add separator
    var strDate = $mDate.format('MMM Do');
    if (lastDSDEventDate[instance] != strDate) {
        $(`<h4 style="text-align:center; padding-top:5rem; padding-bottom: 2rem;"> ${strDate} </h4>`).appendTo(strCssClass);
    }
    lastDSDEventDate[instance] = strDate;

    // Remove oldest message if more than defined max recent events
    var eventscount = $(strCssClass).children('.badge-message').length;
    if (eventscount >= hid_RecentEvents) {
        $(strCssClass).children('.badge-message').first().remove();
    }


    // var arDateTime = date.split(' ');
    // var arDate = file.replaceAll('/', '').replaceAll(':', '').split(' ');
    // var arDate = file.replaceAll('/', '').replaceAll(':', '').split(' ');

    var $elem = $(`

    <div class="badge-message" data-action="date" duration="${duration}" value="${date}|${file}">    

        <div class="progress ${strDateClass}" style="height: ${ixDuration}px;">
            <div style="width:100%;">
                <div class="message-container">
                    <span class="badge-top-left" data-action="dynamic-time" value="${time}">${strDateTime}</span>
                    <span class="badge-bottom-left">${duration} second${duration > 1 ? 's':''}</span>
                    <span class="badge-top-right" data-action="rid">${rid}</span>
                    <span class="badge-bottom-right">${tg}</span>
                </div>
                <div class="progress-bar" style="width:100%; height:100%">  
                </div>
            </div>
        </div>
    </div>

        `).appendTo(strCssClass);

    // Auto-Scroll
    var eg = `DSDPlus${instance}`;
    if (newEvent && config.eventGroups[eg].autoscroll) {
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    // Auto-Play
    if (newEvent && !config.eventGroups[eg].mute) {
        playSound(getDSDWaveFile(instance, date, file), eg, $elem);
        // playSound(getDSDWaveFile(instance, strDateTime, date), eg, $elem);
    }

}

function processLRRPEvent(event) {

    var date = event[0],
        time = event[1],
        rid = event[2],
        latitude = event[3],
        longitude = event[4],
        speed = event[5],
        heading = event[6];

    var $mDate = moment(date + time, "YYYY/MM/DDhh:mm:ss");

    var strdate = $mDate.fromNow();
    var strunix = $mDate.unix();


    rid = parseInt(rid);
    heading = parseInt(heading);
    var navheading = heading;
    var icon = "fa-bus";

    // replace rid with name if in lookup
    var name = rid;

    var replacementRadio = replacementDSDRadios.filter(word => word[0] == rid)[0];
    // console.log('tt', replacementRadio);

    if (replacementRadio) {
        name = replacementRadio[1];
    }


    var popup = new mapboxgl.Popup({ offset: 5 }).setHTML(
        `
        <b>${name}</b>
        <br>RID: ${rid}
        <br>Speed: ${speed} km/h
        <br>Heading: ${heading}°
        <br>Direction: ${headingToDirection(heading)}
        <br>${latitude},${longitude}
        <br>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-sm btn-secondary" data-action="map-zoom-to" value="${longitude},${latitude}">Zoom To</button>
            <div class="btn-group btn-group-sm" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Open With
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                  <a class="dropdown-item" data-action="open-external" value="https://www.google.com/maps/place/${latitude},${longitude}">Google Maps</a>
                  <a class="dropdown-item" data-action="open-external" value="https://earth.google.com/web/@${latitude},${longitude},942.11263543a,149.39453107d,35y,359.99999914h,0t,0r">Google Earth</a>
                  <a class="dropdown-item" data-action="open-external" value="https://www.google.com/search?q=${latitude},${longitude}">Google Search</a>
                </div>
            </div>
        </div>

        `
    );


    // console.log('heading', [rid, heading]);

    switch (rid) {
        case 3413:
            icon = "fa-truck-pickup";
            heading = (heading - 90);
            break;

        case 3418:
            icon = "fa-faucet";
            heading = (heading - 90);
            break;

            // Fire
        case 3101:
        case 3102:
        case 3103:
        case 3104:
        case 3105:
        case 3219:
        case 3214:
        case 3002:
        case 4106:
            icon = "fa-fire";
            break;
        case 3208:
            icon = "fa-fire";
            break;


        default:
            icon = "fa-bus";
            heading = (heading - 180);
            break;
    }

    var strHtml = `
    <h6>${name}</h6> 
    <p> <span data-action="dynamic-time" value="${strunix}">updating</span> </p>   
    `;

    if (speed > 0) {
        strHtml += `<p>${Math.round(speed)} km/h</p>`;
        strHtml += `<i class="fas fa-location-arrow" style="width: 1rem; height:1rem; color:red; transform: rotate(${(navheading - 45)}deg);"></i>`;
    }

    strHtml += `<i class="fas ${icon}" style="height:1rem; width:1rem; transform: rotate(${heading}deg);"></i>`;

    // Update existing marker
    if (markers[rid]) {
        markers[rid].setPopup(popup);
        markers[rid].setLngLat([longitude, latitude]);
        markers[rid]._element.innerHTML = strHtml;

        // console.log('updting lrrp', [rid, strHtml]);

    } else {
        // Add New Marker
        var el = document.createElement('div');
        el.className = 'marker';
        el.style.opacity = .8;
        el.innerHTML = strHtml;

        markers[rid] = new mapboxgl.Marker(el);
        markers[rid].setPopup(popup);
        markers[rid].setLngLat([longitude, latitude]);
        markers[rid].addTo(map);
    }




    // Play Sound
    var ec = config.eventGroups['MapboxMap'];

    if (!ec.mute) {
        var rtlHowl = new Howl({
            src: '/sounds/sharp.mp3',
            format: ['mp3'],
            autoplay: true,
            volume: ec.volume,
            rate: ec.rate,
            stereo: ec.stereo,
        });
    }

}

function processAircraftEvent(event) {

    var clock = event[0],
        hexid = event[1],
        ident = event[2],
        squawk = event[3],
        alt = event[4],
        vrate = event[5],
        latitude = event[6],
        longitude = event[7],
        speed = event[8],
        track = event[9],
        navheading = event[9],
        icon = "fa-plane";


    if (!latitude | !longitude) {
        return;
    }

    var date = moment.unix(clock).fromNow();
    var intlalt = Intl.NumberFormat().format(alt);
    var altcolor = getColorFromAltitude(alt);
    var inavh = 270 + parseInt(navheading);


    var popup = new mapboxgl.Popup({ offset: 35 }).setHTML(
        `
        <a target="_blank" href="https://www.google.com/search?q=flight+${hexid}">${hexid}</a> / <a target="_blank" href="https://flightaware.com/live/flight/${ident}">${ident}</a>
        <br>Squawk: ${squawk}
        <br>Altitude: ${intlalt} ft
        <br> Speed: ${speed} kts
        <br>Track: ${track}° / ${headingToDirection(track)}
        <br>Heading: ${navheading}° / ${headingToDirection(navheading)}
        <br>${latitude},${longitude}
        <br>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-sm btn-secondary" data-action="map-zoom-to" value="${longitude},${latitude}">Zoom To</button>
            <div class="btn-group btn-group-sm" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Open With
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                  <a class="dropdown-item" data-action="open-external" value="https://www.google.com/maps/place/${latitude},${longitude}">Google Maps</a>
                  <a class="dropdown-item" data-action="open-external" value="https://www.google.com/search?q=flight+${hexid}">Google Search</a>
                </div>
            </div>
        </div>

        
        `);

    var strHtml = `
    <h6>${ident}</h6>
    <b data-action="dynamic-time" value="${clock}">${date}</b>

    
    
    
    ` +
        (speed > 0 ? `<p style="margin:0;">${(Math.round(speed * 1.85))} km/h / ${speed} kts / ${intlalt} ft<p>` : '') +
        `<i class="fas ${icon}" style="color: ${altcolor}; height:2rem; width:2rem; transform: rotate(${inavh}deg);"></i>
    `;


    // Update existing marker
    if (markers[hexid]) {
        markers[hexid].setPopup(popup);
        markers[hexid].setLngLat([longitude, latitude]);
        markers[hexid]._element.innerHTML = strHtml;

    } else {
        // Add New Marker
        var el = document.createElement('div');
        el.className = 'marker';
        el.style.opacity = .8;
        el.innerHTML = strHtml;

        markers[hexid] = new mapboxgl.Marker(el);
        markers[hexid].setPopup(popup);
        markers[hexid].setLngLat([longitude, latitude]);
        markers[hexid].addTo(map);
    }




    // Play Sound
    var ec = config.eventGroups['MapboxMap'];

    if (!ec.mute) {
        var rtlHowl = new Howl({
            src: '/sounds/sharp.mp3',
            format: ['mp3'],
            autoplay: true,
            volume: ec.volume,
            rate: ec.rate,
            stereo: ec.stereo,
        });
    }


}


// 
// Process File Events
// 

function processFileEvent(strCssClass, event, instance, newEvent = false) {

    var file = event[0];
    var date = event[1];
    var strDateTime = newEvent == true ? (moment.unix(date).fromNow()) : moment.unix(date).format(strDateTimeFormat);
    var strDate = moment.unix(date).format('MMM Do');
    var strDateClass = newEvent ? 'badge-warning' : 'badge-info';
    var duration = event[2];
    var iDuration = duration;

    if (typeof iDuration !== 'number' || iDuration < 4) {
        iDuration = 3;
    }
    var ixDuration = ((duration > 20 ? 20 : duration & duration < 3 ? 3 : duration) * 20);

    // Remove oldest message if more than defined max recent events
    var eventscount = $(strCssClass).children('.badge-message').length;
    if (eventscount >= hid_RecentEvents) {
        $(strCssClass).children('.badge-message').first().remove();
    }


    // If New Date add separator
    if (lastFileEventDate[instance] == null || lastFileEventDate[instance] != strDate) {
        $(`<h4 style="text-align:center; padding-top:5rem; padding-bottom: 2rem;">${strDate}</h4>`).appendTo(strCssClass);
    }
    lastFileEventDate[instance] = strDate;


    $elem = $(`

    <div class="badge-message" data-action="date" duration="${duration}" value="${file}">    
    
    <div class="progress ${strDateClass}" style="height: ${ixDuration}px">
        <div style="width:100%;">
            <div class="message-container">
                <span class="badge-float-left">${duration} ${duration > 1 ? 'seconds' : 'second'}</span>
                <span class="badge-float-right" data-action="dynamic-time" value="${date}">${strDateTime}</span>
            </div>
        <div class="progress-bar" style="width:100%; height:100%">  
        </div>
        </div>
        </div></div>
        
        `);

    $elem.appendTo(strCssClass).delay(duration * 1000).queue(function() {

    });

    // Auto-Scroll
    var eg = `FileEvent${instance}`;

    if (newEvent && config.eventGroups[eg].autoscroll) {
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }

    // Autoplay
    if (newEvent && !config.eventGroups[eg].mute) {
        console.log('Auto Play', eg);

        // TODO - Animate Play and mark as 'read'
        playSound(getWaveFile(file), eg);
    }

}


function processRtl433(strCssClass, instance, event, newEvent = false) {

    var $mDate = moment(event.time, "YYYY-MM-DD hh:mm:ss");

    var strDateTime = newEvent == true ? ($mDate.fromNow()) : $mDate.format(strDateTimeFormat);
    var strunix = $mDate.unix();

    var Id = event.id;
    var strName = Id;

    var strEvents = "";

    if (event.state) {
        strEvents += `,${event.state}`;
    }

    if (event.pressure_kPa) {
        strEvents += `,${event.pressure_kPa} kPa`;
    }
    if (event.temperature_C) {
        strEvents += `,${event.temperature_C} °C`;
    }
    if (event.temperature_F) {
        strEvents += `,${event.temperature_F} °F`;
    }
    if (event.wind_dir_deg) {
        strEvents += `,${event.wind_dir_deg} °`;
    }
    if (event.rain_mm) {
        strEvents += `,${event.rain_mm} " mm`;
    }
    if (event.rain_inch) {
        strEvents += `,${event.rain_inch}`;
    }
    if (event.code) {
        strEvents += `,${event.code}`;
    }
    if (event.event) {
        strEvents += `,${event.event}`;
    }
    // if (event.freq) {
    //     strEvents += `,${event.freq} MHz`;
    // }




    // If New Date add separator
    var strDate = $mDate.format('MMM Do');
    if (lastDSDEventDate[instance] != strDate) {
        $(`<h4 style="text-align:center; padding-top:5rem; padding-bottom: 2rem;"> ${strDate} </h4>`).appendTo(strCssClass);
    }
    lastDSDEventDate[instance] = strDate;




    // var test = [event.state, event.pressure_kPa, event.temperature_C, event.temperature_F, event.wind_dir_deg, event.rain_mm, event.rain_inch, event.code];
    // var strEvents = test.filter(item => item).join(', ');
    // // console.log('ttt', test2);


    switch (Id) {
        case 163415:
            strName = "Front Door";
            break;

        case 645163:
            strName = "Back Door";
            break;

        case 209847:
        case 622010:
            strName = "Dog Door";
            break;

    }

    // Skip Repeated Events
    if (rtl433LastEvent.id == strName && rtl433LastEvent.events == strEvents) {
        // console.log('Skipping Duplicate Event');
        return;
    }
    rtl433LastEvent.id = strName;
    rtl433LastEvent.events = strEvents;


    // Remove oldest message if more than defined max recent events
    var eventscount = $(strCssClass).children('.badge-message-alt').length;
    if (eventscount >= hid_RecentEvents) {
        $(strCssClass).children('.badge-message-alt').first().remove();
    }

    var arEvents = strEvents.substring(1).split(',');

    $(`
    
    <div data-action="date" class="row badge-message-alt">
        <div class="col-6" data-action="dynamic-time" value="${strunix}">${strDateTime}</div>
        <div class="col-6">${strName}</div>
        <div class="col-6">${event.model}</div>
        <div class="col-6">${arEvents.join('<br>')}</div>
    </div>


    `).appendTo(strCssClass, event.time).delay(5000).queue(function() {

    });


    // Auto-Scroll
    var eg = `rtl433Event${instance}`;

    if (newEvent && config.eventGroups[eg].autoscroll) {
        $(strCssClass).scrollTop($(strCssClass).prop('scrollHeight'));
    }


    if (newEvent) {
        // Play Sound
        var id = `rtl433Event${instance}`;
        var ec = config.eventGroups[id];

        if (!ec.mute) {
            var rtlHowl = new Howl({
                src: '/sounds/sharp.mp3',
                format: ['mp3'],
                autoplay: true,
                volume: ec.volume,
                rate: ec.rate,
                stereo: ec.stereo,
            });
        }
    }


}