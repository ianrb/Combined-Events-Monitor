function initMapBox() {

    mapboxgl.accessToken = mapboxAccessToken;

    map = new mapboxgl.Map({
        container: 'map',
        center: mapboxDefaultLocation,
        zoom: mapboxDefaultZoom,
        style: 'mapbox://styles/mapbox/dark-v10',
        pitch: 45,
        bearing: -17.6,
        antialias: true
    });


    map.on('load', function() {

        map.addSource('places', {
            'type': 'geojson',
            'data': {
                'type': 'FeatureCollection',
                'features': [{
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': mapboxDefaultLocation
                    },
                    'properties': {
                        'description': '<strong>Combined Events Monitor</strong><p>General Location of where you\'re monitoring</p>',
                        'icon': 'campsite'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-117.871686, 53.308492]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 1</strong><p>5009 Township Rd 502, Brule, AB T7V<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-117.573812, 53.398694]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 2</strong><p>184 Eaton Rd, Hinton, AB T7V 1Y5<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-117.3254105, 53.0308694]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 3</strong><p>5215 49th St, Cadomin, AB T7V 1Y5<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-116.9790717, 53.2327227]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 4</strong><p>4902 Center Avenue, Robb, AB<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-116.79527019, 53.5539631]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 5</strong><p>2002 10th Street, Marlboro, AB<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-116.4301887332971, 53.585824018052975]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 6</strong><p>4835 6 Ave, Edson, AB T7E 1E1<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-115.993542, 53.666631]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 7</strong><p>51 Street, Peers<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-115.770495, 53.616945]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 8</strong><p>5412 50 Street, Niton Junction<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-115.23485115267736, 53.609761358064574]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 9</strong><p>4919 52nd Avenue, Wildwood<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-115.01876041412599, 53.601078951073085]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 10</strong><p>4907 50 Street, Evansburg<br></p>',
                        'icon': 'fire-station'
                    }
                }, {
                    'type': 'Feature',
                    'geometry': {
                        'type': 'Point',
                        'coordinates': [-116.39142348512654, 53.587845746243055]
                    },
                    'properties': {
                        'description': '<strong>Fire Station 12</strong><p>2716 1st Avenue, Edson<br></p>',
                        'icon': 'fire-station'
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

    // MapBox Switch Layer
    $(document).on('change', 'select.select-theme', function() {

        var val = $(this).find('option:selected').val();
        console.log('val', val);

        switchLayer(val);
    });


}


function switchLayer(layer) {
    map.setStyle('mapbox://styles/mapbox/' + layer);
}

function toggle3D(el) {

    map3d = !map3d;

    if (map3d) {

        $(el).children('svg').removeClass('fa-map').addClass('fa-cube');
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
                'minzoom': 5,
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
        $(el).children('svg').removeClass('fa-cube').addClass('fa-map');
    }

}

// 
// Helpers
// 

// Convert numeric heading to general direction
function headingToDirection(num) {
    var val = Math.floor((num / 22.5) + 0.5);
    var arr = ["N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW"];
    return arr[(val % 16)];
}

function getColorFromAltitude(alt) {

    var altcolor = 'blue';

    if (alt < 20000) {
        altcolor = 'green';
    }
    if (alt < 10000) {
        altcolor = 'yellow';
    }
    if (alt < 5000) {
        altcolor = 'brown';
    }

    return altcolor;

}