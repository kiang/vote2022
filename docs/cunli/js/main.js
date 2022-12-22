var sidebar = new ol.control.Sidebar({ element: 'sidebar', position: 'right' });
var jsonFiles, filesLength, fileKey = 0;

var projection = ol.proj.get('EPSG:3857');
var projectionExtent = projection.getExtent();
var size = ol.extent.getWidth(projectionExtent) / 256;
var resolutions = new Array(20);
var matrixIds = new Array(20);
for (var z = 0; z < 20; ++z) {
    // generate resolutions and matrixIds arrays for this WMTS
    resolutions[z] = size / Math.pow(2, z);
    matrixIds[z] = z;
}

function areaStyleFunction(f) {
    var color = '', stroke;
    var p = f.getProperties();
    if (!details[p.VILLCODE]) {
        return;
    }

    if (f === currentFeature) {
        color = 'rgba(255,255,0,0.5)';
        stroke = new ol.style.Stroke({
            color: '#f00',
            width: 5
        });
    } else {
        stroke = new ol.style.Stroke({
            color: '#000',
            width: 1
        });
        switch (details[p.VILLCODE]['major']['party']) {
            case '中國國民黨':
                color = 'rgba(0,0,255,0.5)';
                break;
            case '民主進步黨':
                color = 'rgba(27,148,49,0.5)';
                break;
            case '台灣民眾黨':
                color = 'rgba(29,168,165,0.5)';
                break;
            default:
            case '無黨籍及未經政黨推薦':
                color = 'rgba(200,200,200,0.5)';
                if (details[p.VILLCODE]['major']['is_current'] === 'N') {
                    stroke = new ol.style.Stroke({
                        color: '#f0f',
                        width: 3
                    });
                    color = 'rgba(255,0,255,0.5)';
                } else {
                    color = 'rgba(200,200,200,0.5)';
                }
                break;
        }
    }
    return new ol.style.Style({
        fill: new ol.style.Fill({
            color: color
        }),
        stroke: stroke
    })
}

var appView = new ol.View({
    center: ol.proj.fromLonLat([120.341986, 23.176082]),
    zoom: 8
});

var vectorAreas = new ol.layer.Vector({
    source: new ol.source.Vector({
        url: 'https://kiang.github.io/taiwan_basecode/cunli/topo/20221118.json',
        //url: 'http://localhost/~kiang/taiwan_basecode/cunli/topo/20221118.json',
        format: new ol.format.TopoJSON()
    }),
    style: areaStyleFunction
});

var baseLayer = new ol.layer.Tile({
    source: new ol.source.WMTS({
        matrixSet: 'EPSG:3857',
        format: 'image/png',
        url: 'https://wmts.nlsc.gov.tw/wmts',
        layer: 'EMAP',
        tileGrid: new ol.tilegrid.WMTS({
            origin: ol.extent.getTopLeft(projectionExtent),
            resolutions: resolutions,
            matrixIds: matrixIds
        }),
        style: 'default',
        wrapX: true,
        attributions: '<a href="http://maps.nlsc.gov.tw/" target="_blank">國土測繪圖資服務雲</a>'
    }),
    opacity: 0.8
});

var map = new ol.Map({
    layers: [baseLayer, vectorAreas],
    target: 'map',
    view: appView
});

map.addControl(sidebar);
var pointClicked = false;
var previousFeature = false;
var currentFeature = false;
var tpp = {};
var details = {};
$.getJSON('cunli/candidates.json', {}, function (c) {
    details = c;

    map.on('singleclick', function (evt) {
        pointClicked = false;
        map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
            if (false === pointClicked) {
                var p = feature.getProperties();
                if (p.VILLCODE) {
                    currentFeature = feature;
                    if (false !== previousFeature) {
                        previousFeature.setStyle(areaStyleFunction(previousFeature));
                    }
                    currentFeature.setStyle(areaStyleFunction(currentFeature));
                    previousFeature = currentFeature;

                    if (details[p.VILLCODE]) {
                        sidebarTitle = details[p.VILLCODE].major.cand_name;
                        c = '<table class="table table-striped">';
                        c += '<tr><th>村里</th><td>' + details[p.VILLCODE].major.area + '</td></tr>';
                        c += '<tr><th>姓名</th><td><strong>' + details[p.VILLCODE].major.cand_name + '</strong></td></tr>';
                        c += '<tr><th>政黨</th><td>' + details[p.VILLCODE].major.party + '</td></tr>';
                        c += '<tr><th>性別</th><td>' + ((details[p.VILLCODE].major.cand_sex === 'm') ? '男' : '女') + '</td></tr>';
                        c += '<tr><th>生日</th><td>' + details[p.VILLCODE].major.cand_birthday + '</td></tr>';
                        c += '<tr><th>出生地</th><td>' + details[p.VILLCODE].major.cand_bornplace + '</td></tr>';
                        c += '<tr><th>學歷</th><td>' + details[p.VILLCODE].major.cand_edu + '</td></tr>';
                        c += '<tr><th>是否現任</th><td>' + ((details[p.VILLCODE].major.is_current === 'Y') ? '是' : '否') + '</td></tr>';
                        c += '<tr><th>得票</th><td>' + details[p.VILLCODE].major.ticket_num + '</td></tr>';
                        c += '</table> <hr />';
                        if (details[p.VILLCODE].candidates) {
                            for (k in details[p.VILLCODE].candidates) {
                                c += '<table class="table table-striped">';
                                c += '<tr><th>姓名</th><td><strong>' + details[p.VILLCODE].candidates[k].cand_name + '</strong></td></tr>';
                                c += '<tr><th>政黨</th><td>' + details[p.VILLCODE].candidates[k].party + '</td></tr>';
                                c += '<tr><th>性別</th><td>' + ((details[p.VILLCODE].candidates[k].cand_sex === 'm') ? '男' : '女') + '</td></tr>';
                                c += '<tr><th>生日</th><td>' + details[p.VILLCODE].candidates[k].cand_birthday + '</td></tr>';
                                c += '<tr><th>出生地</th><td>' + details[p.VILLCODE].candidates[k].cand_bornplace + '</td></tr>';
                                c += '<tr><th>學歷</th><td>' + details[p.VILLCODE].candidates[k].cand_edu + '</td></tr>';
                                c += '<tr><th>是否現任</th><td>' + ((details[p.VILLCODE].candidates[k].is_current === 'Y') ? '是' : '否') + '</td></tr>';
                                c += '<tr><th>得票</th><td>' + details[p.VILLCODE].candidates[k].ticket_num + '</td></tr>';
                                c += '</table>';
                            }
                        }
                        $('#sidebarTitle').html(details[p.VILLCODE].major.area + details[p.VILLCODE].major.cand_name);
                        $('#sidebarContent').html(c);
                    } else {
                        $('#sidebarTitle').html('');
                        $('#sidebarContent').html('');
                    }
                }

                sidebar.open('home');
                pointClicked = true;
                if (FB) {
                    FB.XFBML.parse();
                }
            }
        });
    });
});

var geolocation = new ol.Geolocation({
    projection: appView.getProjection()
});

geolocation.setTracking(true);

geolocation.on('error', function (error) {
    console.log(error.message);
});

var positionFeature = new ol.Feature();

positionFeature.setStyle(new ol.style.Style({
    image: new ol.style.Circle({
        radius: 6,
        fill: new ol.style.Fill({
            color: '#3399CC'
        }),
        stroke: new ol.style.Stroke({
            color: '#fff',
            width: 2
        })
    })
}));

var firstPosDone = false;
geolocation.on('change:position', function () {
    var coordinates = geolocation.getPosition();
    positionFeature.setGeometry(coordinates ? new ol.geom.Point(coordinates) : null);
    if (false === firstPosDone) {
        appView.setCenter(coordinates);
        firstPosDone = true;
    }
});

new ol.layer.Vector({
    map: map,
    source: new ol.source.Vector({
        features: [positionFeature]
    })
});

$('#btn-geolocation').click(function () {
    var coordinates = geolocation.getPosition();
    if (coordinates) {
        appView.setCenter(coordinates);
    } else {
        alert('目前使用的設備無法提供地理資訊');
    }
    return false;
});