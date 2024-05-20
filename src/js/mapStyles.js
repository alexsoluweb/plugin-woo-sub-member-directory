/**
 * Default Google Map Style
 * @returns {Array} - Map Style
 */
export default [
    {
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#212121"
            }
        ]
    },
    {
        "elementType": "labels.icon",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#757575"
            }
        ]
    },
    {
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "color": "#212121"
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#757575"
            }
        ]
    },
    {
        "featureType": "administrative.country",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#9e9e9e"
            }
        ]
    },
    {
        "featureType": "administrative.land_parcel",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "administrative.locality",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#bdbdbd"
            }
        ]
    },
    {
        "featureType": "poi",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#757575"
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#181818"
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#616161"
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "color": "#1b1b1b"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#2c2c2c"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#8a8a8a"
            }
        ]
    },
    {
        "featureType": "road.arterial",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#373737"
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#3c3c3c"
            }
        ]
    },
    {
        "featureType": "road.highway.controlled_access",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#4e4e4e"
            }
        ]
    },
    {
        "featureType": "road.local",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#616161"
            }
        ]
    },
    {
        "featureType": "transit",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#757575"
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#000000"
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#3d3d3d"
            }
        ]
    }
];

/**
 * Custom Marker Icon
 * @returns {google.maps.Symbol} - Marker Icon
 */
export function svgMarker()
{
    return {
        path: "m6.9844 0c-1.9373 2.9606e-16 -3.586 0.67976-4.9453 2.0391-1.3593 1.3593-2.0391 3.008-2.0391 4.9453 0 0.9693 0.25 2.0943 0.75 3.375s1.0548 2.4293 1.6641 3.4453c0.6093 1.016 1.3201 2.0626 2.1328 3.1406 0.81267 1.078 1.3748 1.7969 1.6875 2.1562 0.31267 0.35933 0.56267 0.64852 0.75 0.86719l0.73242-0.85547c0.4686-0.5627 1.0585-1.2621 1.7305-2.2148 0.672-0.9527 1.3498-1.9763 2.0371-3.0703s1.2751-2.2656 1.7598-3.5156 0.72656-2.3595 0.72656-3.3281c0-1.9373-0.67976-3.586-2.0391-4.9453-1.3593-1.3593-3.008-2.0391-4.9453-2.0391h-0.0019531zm0 2.5898a3.4725 3.4725 0 0 1 3.4727 3.4727 3.4725 3.4725 0 0 1-3.4727 3.4727 3.4725 3.4725 0 0 1-3.4707-3.4727 3.4725 3.4725 0 0 1 3.4707-3.4727z",
        fillColor: "#fff",
        fillOpacity: 1,
        strokeWeight: 0,
        scale: 1.5,
        anchor: new google.maps.Point(7, 20)
    };
}