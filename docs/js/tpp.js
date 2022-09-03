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
  if (f === currentFeature) {
    return new ol.style.Style();
  } else {
    if (tpp[p.id]) {
      color = 'rgba(29,168,165,0.7)';
    } else {
      color = 'rgba(255,255,255,0.3)';
    }

    stroke = new ol.style.Stroke({
      color: '#000',
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

function cunliStyleFunction(f) {
  var p = f.getProperties();
  let cunliStyle = new ol.style.Style({
    fill: new ol.style.Fill({
      color: 'rgba(255,255,255,0.3)'
    }),
    stroke: new ol.style.Stroke({
      color: '#000',
      width: 1
    }),
    text: new ol.style.Text({
      font: '14px "Open Sans", "Arial Unicode MS", "sans-serif"',
      fill: new ol.style.Fill({
        color: 'rgba(0,0,255,0.7)'
      })
    })
  });
  cunliStyle.getText().setText(p.name.toString() + "\n" + p.rate.toString() + '% | ' + p.votes.toString());
  return cunliStyle;
}

var appView = new ol.View({
  center: ol.proj.fromLonLat([120.341986, 23.176082]),
  zoom: 8
});

var vectorAreas = new ol.layer.Vector({
  style: areaStyleFunction
});

var vectorCunli = new ol.layer.Vector({
  style: cunliStyleFunction
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
  layers: [baseLayer, vectorAreas, vectorCunli],
  target: 'map',
  view: appView
});

map.addControl(sidebar);
var pointClicked = false;
var previousFeature = false;
var currentFeature = false;
var tpp = {};
var details = {};
$.getJSON('tpp_zone/details.json', {}, function (c) {
  details = c;
})
$.getJSON('tpp/list.json', {}, function (c) {
  tpp = c;
  vectorAreas.setSource(new ol.source.Vector({
    url: '2022.json',
    format: new ol.format.GeoJSON()
  }));
  map.on('singleclick', function (evt) {
    pointClicked = false;
    map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
      if (false === pointClicked) {
        var p = feature.getProperties();
        if (p.areas) {
          currentFeature = feature;
          if (false !== previousFeature) {
            previousFeature.setStyle(areaStyleFunction(previousFeature));
          }
          currentFeature.setStyle(areaStyleFunction(currentFeature));
          previousFeature = currentFeature;
          vectorCunli.setSource(new ol.source.Vector({
            url: 'tpp_zone/' + p.id + '.json',
            format: new ol.format.GeoJSON()
          }));

          if (tpp[p.id]) {
            if (tpp[p.id].name) {
              var c = '<img src="tpp/' + p.id + '.jpg" style="width: 100%;" />';
              c += '<table class="table table-striped">';
              c += '<tr><th>姓名</th><td>' + tpp[p.id].name + '</td></tr>';
              c += '<tr><th>選區</th><td>' + p.name + '</td></tr>';
              c += '<tr><th>行政區</th><td>' + p.areas + '</td></tr>';
              c += '<tr><th>介紹</th><td>' + tpp[p.id].info.replace("\n", '<br />') + '</td></tr>';
              c += '</table>';
              if (tpp[p.id].fb !== '') {
                c += '<div class="fb-page" data-href="' + tpp[p.id].fb + '" data-tabs="timeline" data-width="380" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="' + tpp[p.id].fb + '" class="fb-xfbml-parse-ignore"><a href="' + tpp[p.id].fb + '">' + tpp[p.id].name + '</a></blockquote></div>';
              }
              $('#sidebarTitle').html(tpp[p.id].name);
              $('#sidebarContent').html(c);
            } else {
              var c = '', sidebarTitle = '';
              for (k in tpp[p.id]) {
                c += '<img src="tpp/' + p.id + '-' + tpp[p.id][k].sort + '.jpg" style="width: 100%;" />';
              }
              for (k in tpp[p.id]) {
                sidebarTitle += tpp[p.id][k].name + ' ';
                c += '<table class="table table-striped">';
                c += '<tr><th>姓名</th><td>' + tpp[p.id][k].name + '</td></tr>';
                c += '<tr><th>區域</th><td>' + p.areas + '</td></tr>';
                c += '<tr><th>介紹</th><td>' + tpp[p.id][k].info.replace("\n", '<br />') + '</td></tr>';
                c += '</table>';
                if (tpp[p.id][k].fb !== '') {
                  c += '<div class="fb-page" data-href="' + tpp[p.id][k].fb + '" data-tabs="timeline" data-width="380" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="' + tpp[p.id][k].fb + '" class="fb-xfbml-parse-ignore"><a href="' + tpp[p.id][k].fb + '">' + tpp[p.id][k].name + '</a></blockquote></div>';
                }
              }
              $('#sidebarTitle').html(sidebarTitle);
              $('#sidebarContent').html(c);
            }

          } else {
            var c = '';
            c += '<table class="table table-striped">';
            c += '<tr><th>姓名</th><td>&nbsp;</td></tr>';
            c += '<tr><th>選區</th><td>' + p.name + '</td></tr>';
            c += '<tr><th>行政區</th><td>' + p.areas + '</td></tr>';
            c += '<tr><th>介紹</th><td>&nbsp;</td></tr>';
            c += '</table>';
            $('#sidebarTitle').html('');
            $('#sidebarContent').html(c);
          }
        } else {
          $('#sidebarTitle').html(p.name);
          var c = '', voteSum = 0;
          for (k in details[p.id]) {
            voteSum += details[p.id][k];
          }
          c += '<table class="table table-striped">';
          c += '<tr><th>政黨</th><th>得票</th><th>比例</th></tr>';
          for (k in details[p.id]) {
            c += '<tr><th>' + k + '</th><td>' + details[p.id][k] + '</td><td>' + Math.round(details[p.id][k] / voteSum * 10000) / 100 + '%</td></tr>';
          }
          c += '</table>';
          $('#sidebarContent').html(c);
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