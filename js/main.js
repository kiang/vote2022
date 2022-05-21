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
  var color = '', stroke, radius;
  var p = f.getProperties();
  if(f === currentFeature) {
    color = 'rgba(200,200,0,0.5)';
    stroke = new ol.style.Stroke({
      color: 'rgba(255,0,0,0.5)',
      width: 5
    });
    radius = 25;
  } else {
    var partyCount = 0;
    var areaParty = '';
    for(k in dataPool[p.code][2018].party) {
      if(dataPool[p.code][2018].party[k] > partyCount) {
        partyCount = dataPool[p.code][2018].party[k];
        areaParty = k;
      }
    }
    switch(areaParty) {
      case '中國國民黨':
        color = 'rgba(0,0,200,0.5)';
        break;
      case '民主進步黨':
        color = 'rgba(0,200,0,0.5)';
        break;
      case '無黨團結聯盟':
      case '無黨籍及未經政黨推薦':
        color = 'rgba(200,200,200,0.5)';
        break;
      default:
        console.log(areaParty);
    }
    
    stroke = new ol.style.Stroke({
      color: '#fff',
      width: 1
    });
    radius = 15;
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
var dataPool = {};
$.getJSON('2018_match_2020.json', {}, function(c) {
  dataPool = c;
  vectorAreas.setSource(new ol.source.Vector({
    url: 'areas.json',
    format: new ol.format.TopoJSON()
  }));
  map.on('singleclick', function(evt) {
    pointClicked = false;
    map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
      if(false === pointClicked) {
        currentFeature = feature;
        if(false !== previousFeature) {
          previousFeature.setStyle(areaStyleFunction(previousFeature));
        }
        currentFeature.setStyle(areaStyleFunction(currentFeature));
        previousFeature = currentFeature;
        var p = feature.getProperties();
        var c = '<ul>';
        c += '<li>2018席次: ' + dataPool[p.code].countCand + '</li>';
        c += '<li>2020總票數: ' + dataPool[p.code].total + '</li>';
        c += '<li>門檻: ' + dataPool[p.code].voteBase + '</li>';
        c += '<li>預估政黨保證席次: <ul>';
        for(j in dataPool[p.code].match) {
          c += '<li>' + j + ': ' + dataPool[p.code].match[j] + '</li>';
        }
        c += '</ul></li>';
        c += '</ul><h4>2018當選人</h4><table class="table table-striped">';
        c += '<tr><th>政黨</th><th>姓名</th><th>得票</th></tr>';
        for(j in dataPool[p.code][2018].detail) {
          c += '<tr><td>' + dataPool[p.code][2018].detail[j].party + '</td>';
          c += '<td>' + dataPool[p.code][2018].detail[j].name + '</td>';
          c += '<td>' + dataPool[p.code][2018].detail[j].voteCount + '</td></tr>';
        }
        c += '</table><h4>2020政黨票</h4><table class="table table-striped">';
        c += '<tr><th>政黨</th><th>比例</th><th>得票</th></tr>';
        for(j in dataPool[p.code].votes) {
          c += '<tr><td>' + j + '</td>';
          c += '<td>' + Math.round(dataPool[p.code].votes[j] / dataPool[p.code].total * 10000)/100 + '%</td>';
          c += '<td>' + dataPool[p.code].votes[j] + '</td></tr>';
        }
        c += '</table>';
        $('#sidebarTitle').html(p.election);
        $('#sidebarContent').html(c);
        sidebar.open('home');
        pointClicked = true;
      }
    });
  });
});

var geolocation = new ol.Geolocation({
  projection: appView.getProjection()
});

geolocation.setTracking(true);

geolocation.on('error', function(error) {
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
geolocation.on('change:position', function() {
  var coordinates = geolocation.getPosition();
  positionFeature.setGeometry(coordinates ? new ol.geom.Point(coordinates) : null);
  if(false === firstPosDone) {
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
  if(coordinates) {
    appView.setCenter(coordinates);
  } else {
    alert('目前使用的設備無法提供地理資訊');
  }
  return false;
});