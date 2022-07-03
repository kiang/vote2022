var areas = {}, cityPool = {}, tpp = {};
$.getJSON('2022.json', {}, function (c) {

    for (k in c.features) {
        c.features[k].properties.city = c.features[k].properties.name.substring(0, 3);
        if (!cityPool[c.features[k].properties.city]) {
            cityPool[c.features[k].properties.city] = [];
        }
        cityPool[c.features[k].properties.city].push(c.features[k].properties.id);
        areas[c.features[k].properties.id] = c.features[k].properties;
    }

    $.getJSON('tpp/list.json', {}, function (c) {
        tpp = c;
        $('#menu').change(function () {
            var selected = $(this).val();
            if (selected !== '') {
                var content = '';
                for (k in cityPool[selected]) {
                    var areaKey = cityPool[selected][k];
                    if (tpp[areaKey]) {
                        if (tpp[areaKey].fb) {
                            content += '<div class="fb-page" data-href="' + tpp[areaKey].fb + '" data-tabs="timeline" data-width="380" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="' + tpp[areaKey].fb + '" class="fb-xfbml-parse-ignore"><a href="' + tpp[areaKey].fb + '">' + tpp[areaKey].name + '</a></blockquote></div>';
                        } else {
                            for (j in tpp[areaKey]) {
                                content += '<div class="fb-page" data-href="' + tpp[areaKey][j].fb + '" data-tabs="timeline" data-width="380" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="' + tpp[areaKey][j].fb + '" class="fb-xfbml-parse-ignore"><a href="' + tpp[areaKey][j].fb + '">' + tpp[areaKey][j].name + '</a></blockquote></div>';
                            }
                        }
                    }
                }
                $('#content').html(content);
                if (FB) {
                    FB.XFBML.parse();
                }
            } else {
                $('#content').html('<div style="font-size: 200px;"><i class="fa-solid fa-arrow-up-long"></i>請選擇縣市</div>');
            }
        });
    });
})
