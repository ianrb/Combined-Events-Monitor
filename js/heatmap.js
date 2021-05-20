mapboxgl.accessToken = mapboxAccessToken;

map = new mapboxgl.Map({
    container: 'map',
    center: mapboxDefaultLocation,
    zoom: mapboxDefaultZoom,
    // style: 'mapbox://styles/mapbox/dark-v10',
    // style: 'mapbox://styles/mapbox/satellite-v9',
    style: 'mapbox://styles/mapbox/outdoors-v11',
    // style: 'mapbox://styles/mapbox/streets-v11',
    pitch: 45,
    bearing: -17.6,
    antialias: true
});


map.on('load', function() {



});

map.on('styledata', function() {

    var lrrpsoure = map.getSource('lrrp');

    if (lrrpsoure) {
        return;
    }

    map.addSource('lrrp', {
        'type': 'geojson',
        'data': 'lrrp.geojson'
    });

    map.addLayer({
        'id': 'lrrp-heat',
        'type': 'heatmap',
        'source': 'lrrp',
        'maxzoom': 9,
        'paint': {
            // Increase the heatmap weight based on frequency and property countnitude
            'heatmap-weight': [
                'interpolate', ['linear'],
                ['get', 'count'],
                0,
                0,
                6,
                1
            ],
            // Increase the heatmap color weight weight by zoom level
            // heatmap-intensity is a multiplier on top of heatmap-weight
            'heatmap-intensity': [
                'interpolate', ['linear'],
                ['zoom'],
                0,
                1,
                9,
                3
            ],
            // Color ramp for heatmap.  Domain is 0 (low) to 1 (high).
            // Begin color ramp at 0-stop with a 0-transparancy color
            // to create a blur-like effect.
            'heatmap-color': [
                'interpolate', ['linear'],
                ['heatmap-density'],
                0,
                'rgba(33,102,172,0)',
                0.2,
                'rgb(103,169,207)',
                0.4,
                'rgb(209,229,240)',
                0.6,
                'rgb(253,219,199)',
                0.8,
                'rgb(239,138,98)',
                1,
                'rgb(178,24,43)'
            ],
            // Adjust the heatmap radius by zoom level
            'heatmap-radius': [
                'interpolate', ['linear'],
                ['zoom'],
                0,
                2,
                9,
                20
            ],
            // Transition from heatmap to circle layer by zoom level
            'heatmap-opacity': [
                'interpolate', ['linear'],
                ['zoom'],
                7,
                1,
                9,
                0
            ]
        }
    });

    map.addLayer({
        'id': 'lrrp-point',
        'type': 'circle',
        'source': 'lrrp',
        'minzoom': 7,
        'paint': {
            // Size circle radius by earthquake countnitude and zoom level
            'circle-radius': [
                'interpolate', ['linear'],
                ['zoom'],
                7, ['interpolate', ['linear'],
                    ['get', 'count'], 1, 1, 6, 4
                ],
                16, ['interpolate', ['linear'],
                    ['get', 'count'], 1, 5, 6, 50
                ]
            ],
            // Color circle by earthquake countnitude
            'circle-color': [
                'interpolate', ['linear'],
                ['get', 'count'],
                1,
                'rgba(33,102,172,0)',
                2,
                'rgb(103,169,207)',
                3,
                'rgb(209,229,240)',
                4,
                'rgb(253,219,199)',
                5,
                'rgb(239,138,98)',
                6,
                'rgb(178,24,43)'
            ],
            'circle-stroke-color': 'white',
            'circle-stroke-width': 1,
            // Transition from heatmap to circle layer by zoom level
            'circle-opacity': [
                'interpolate', ['linear'],
                ['zoom'],
                7,
                0,
                8,
                1
            ]
        }
    });



});


// MapBox Switch Layer
$(document).on('change', 'select.select-theme', function() {

    var val = $(this).find('option:selected').val();
    console.log('val', val);

    switchLayer(val);
});

function switchLayer(layer) {

    map.setStyle('mapbox://styles/mapbox/' + layer);

    $(map).trigger('load');

}


// 
// Helpers
// 

// Convert numeric heading to general direction
var arr = ["N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW"];

function headingToDirection(num) {
    var val = Math.floor((num / 22.5) + 0.5);
    return arr[(val % 16)];
}

// 
function parseLRRPClipboard() {

    navigator.clipboard.readText().then(text => {

            console.log("Starting Parse");

            var FeatureCollection = {
                "type": "FeatureCollection",
                "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
                "features": []
            };

            // text = `
            // 2020/10/23 12:32:04       3402  53.58716 -116.40006   3.066 160
            // 2020/10/23 12:33:13       3429  53.58694 -116.39955   0.056 324
            // 2020/10/23 12:32:04       3402  53.18716 -116.20006   3.066 160
            // 2020/10/23 12:34:13       3429  53.58690 -116.39943   0.309 324
            // 2020/10/23 12:32:04       3402  53.58716 -116.40006   3.066 160
            // `;

            var arText = text.split('\n');

            for (var i in arText) {

                var line = arText[i];
                // console.log(i, line)

                var arLine = line.match(/[^ ]+/g);

                if (arLine == null) {
                    console.log("Skipping Null Line", arLine);
                    continue;
                }
                if (arLine.length < 4) {
                    console.log("Skipping Invalid Line", arLine.length);
                    continue;
                }
                // console.log(line);
                // console.log(arLine);

                var strDate = arLine[0],
                    strTime = arLine[1],
                    strId = arLine[2],
                    strLat = arLine[3],
                    strLng = arLine[4],
                    strSpeed = arLine[5],
                    strHeading = arLine[6];

                var $mDate = moment(strDate + " " + strTime, "YYYY/MM/DD HH:mm:ss");
                var unixTime = $mDate.unix();

                var Id = parseInt(strId);

                var feature = FeatureCollection.features.find(el => el.properties.id == Id && el.geometry.coordinates[0] == strLng && el.geometry.coordinates[1] == strLat);

                if (feature == null) {
                    // Add Feature
                    FeatureCollection.features.push({
                        "type": "Feature",
                        "properties": {
                            "id": Id,
                            "count": 1,
                            "time": unixTime
                        },
                        "geometry": {
                            "type": "Point",
                            "coordinates": [strLng, strLat, 0.0]
                        }
                    });

                } else {
                    // Update Feature
                    var Count = feature.properties.count + 1;
                    feature.properties.count = Count;
                }
            }



            console.log('FeatureCollection.features', FeatureCollection);

            navigator.clipboard.writeText(JSON.stringify(FeatureCollection)).then(function() {
                console.log('Async: Copying to clipboard was successful!');
            }, function(err) {
                console.error('Async: Could not copy text: ', err);
            });



        })
        .catch(err => {
            console.error('Failed to read clipboard contents: ', err);
        });


}