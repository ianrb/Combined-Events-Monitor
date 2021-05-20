// 
// Configuration
// 
var isDebug = hid_isDebug.value,
    ServerAddress = hid_ServerAddress.value,
    RecentEvents = hid_RecentEvents.value,
    AuthRequired = hid_AuthRequired.value,
    AuthUsername, AuthPassword;

if (AuthRequired) {
    AuthUsername = hid_AuthUsername.value;
    AuthPassword = atob(hid_AuthPassword.value);
}

var defaultConfig = {
    linearPlayback: true,
    dynamictime: true,
    dynamictimeduration: 20,
    colors: {
        navbar: "rgba(60,40,100,0.9)",
        primary: "rgba(100,65,160,0.9)",
        secondary: "rgba(60,40,100,0.8)",
        font: "rgba(255,255,255,0.9)",
        icon: "rgba(255,255,255,0.9)",
        mapfont: "rgba(255,255,255,0.9)",
        mapicon: "rgba(255,255,255,0.9)",
        scrollbartrack: "rgba(144,144,144,0.3)",
        scrollbarthumb: "rgba(144,144,144,0.8)",


    },
    // Each event-group (div) can have settings mapped by ID
    eventGroups: {
        // DSD+ 153.920 MHz
        DSDPlus0: {
            mute: false,
            volume: .9,
            rate: 1,
            stereo: 0,
            autoscroll: true,
        },
        //  ADS-B / LRRP
        MapboxMap: {
            mute: true,
            volume: .5,
            rate: 1,
            stereo: 0,
            // autoscroll: true,
        },
        // DSD+ 159.225 MHz
        DSDPlus1: {
            mute: false,
            volume: .9,
            rate: 1,
            stereo: 0,
            autoscroll: true,
        },
        //  CN Rail
        FileEvent0: {
            mute: false,
            volume: .2,
            rate: 1,
            stereo: 0,
            autoscroll: true,
        },
        // rtl_433
        rtl433Event0: {
            mute: true,
            volume: .5,
            rate: 1,
            stereo: 0,
            autoscroll: true,
        },
        // rtl_433
        rtl433Event1: {
            mute: true,
            volume: .5,
            rate: 1,
            stereo: 0,
            autoscroll: true
        },
        // CYET ATZ
        FileEvent1: {
            mute: false,
            volume: 1,
            rate: 1,
            stereo: 0,
            autoscroll: true,
        },
    }
};

var ApiServerAddress = "/api?",
    WebSocketAddress = "wss://josieinthedark.ddns.net:8080/events";
// WebSocketAddress = "wss://" + ServerAddress + ":8080/events";

// Append token,username,password to socket address if authorisation is required
if (AuthRequired) {
    WebSocketAddress = WebSocketAddress + ('?payload=' + encodeURIComponent(btoa('token=AnyStringYouWantPassedToSocket&username=' + AuthUsername + '&password=' + AuthPassword)));
}

// Date Time Formatting for Messages
// var strDateTimeFormat = "MMM Do - HH:mm:ss";
// var strDateTimeFormat = "YYYY-M-D - HH:mm:ss";
var strDateTimeFormat = "HH:mm:ss";

// Mapbox Access Token - Free for small scale projects https://account.mapbox.com/auth/signup/
var mapboxAccessToken = "pk.eyJ1IjoiaWFucmJvd21hbiIsImEiOiJjazdvejVjejQwMThhM2VvM3RibXZiOWl5In0.vv7zkuH0TJw5r_g4YdAL1A";
var mapboxDefaultLocation = [-116.4336, 53.5850],
    mapboxDefaultZoomLevels = [5, 11, 14]
mapboxDefaultZoom = 11;

// Used for comparison to add separator in events

// 
// Audio Player - Upgraded to howlerjs
// 
var audioPlaying = false,
    audioInstance,
    audioPlayer, unplayedEvents = [];

// Mapbox
var map, map3d = false,
    markers = [];

// DSD+
var replacementDSDGroups = [],
    replacementDSDRadios = [];



// 
// Load Configuration
// 
function loadConfig(config) {

    console.log("Loading Colors", config.colors);

    document.documentElement.style.setProperty('--navbar-color', config.colors.navbar);
    document.documentElement.style.setProperty('--primary-color', config.colors.primary);
    document.documentElement.style.setProperty('--secondary-color', config.colors.secondary);

    document.documentElement.style.setProperty('--font-color', config.colors.font);
    document.documentElement.style.setProperty('--icon-color', config.colors.icon);

    document.documentElement.style.setProperty('--map-font-color', config.colors.mapfont);
    document.documentElement.style.setProperty('--map-icon-color', config.colors.mapicon);

    document.documentElement.style.setProperty('--scrollbar-track', config.colors.scrollbartrack);
    document.documentElement.style.setProperty('--scrollbar-thumb', config.colors.scrollbarthumb);


    $('#config-navbar-color').val(config.colors.navbar);
    $('#config-primary-color').val(config.colors.primary);
    $('#config-secondary-color').val(config.colors.secondary);
    $('#config-font-color').val(config.colors.font);
    $('#config-icon-color').val(config.colors.icon);

    $('#config-map-font-color').val(config.colors.mapfont);
    $('#config-map-icon-color').val(config.colors.mapicon);

    $('#config-scrollbartrack-color').val(config.colors.scrollbartrack);
    $('#config-scrollbarthumb-color').val(config.colors.scrollbarthumb);

    if (!config.linearPlayback) {
        $("a[href='#playback-mode']").find('span').text('Concurrent');
        $("a[href='#playback-mode']").find('svg').removeClass('fa-list-ol').addClass('fa-stream');
    }

    $(`[data-action="config-dynamictime"]`).attr('checked', config.dynamictime);

    $(`[data-action="config-dynamictime-duration"]`).val(config.dynamictimeduration);

    for (var i in config.eventGroups) {

        var eg = config.eventGroups[i];

        $(`#${i} [data-action="volume-adjust"]`).val(eg.volume);

        if (!eg.autoscroll) {
            $(`#${i} [data-action="auto-scroll"]`).children('svg').removeClass('fa-comment').addClass('fa-comment-slash');
        }

        if (eg.mute) {
            $(`#${i} [data-action="mute-unmute"]`).children('svg').removeClass('fa-volume-up').addClass('fa-volume-mute');
        }


    }

}

// 
// Get Config
// 
function getConfig() {

    var obj = localStorage.getItem('config');

    // No Config - Load Default
    if (obj == null) {
        console.warn('Configuration not found resetting');
        resetConfig();
        return getConfig();
    }

    var json = JSON.parse(obj);

    // Out of Sync Config - Missing attributes
    if (json == null || json.linearPlayback == null || json.linearPlayback == null | json.eventGroups == null | json.colors == null) {
        resetConfig();
        return getConfig();
    }

    return json;
}

// 
// Rest Config to Default
// 
function resetConfig() {

    console.warn('Resetting Configuration');
    localStorage.setItem('config', JSON.stringify(defaultConfig));
}

function saveConfig() {
    localStorage.setItem('config', JSON.stringify(config));
}

// Themes
function initThemes() {

    $('.color-input').each(function(index) {
        // console.log($(this));
        var myPicker = new JSColor($(this)[0], { format: 'rgba' });
    });
}