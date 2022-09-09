/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery', 'jquery-ui-modules/widget'], function($) {
	$.widget('codazon.googlemappro', {
		options: {
			mapLat: 45.6107667,
			mapLong: -73.6108024,
			mapZoom: 10,
			mapAddress: '',
			markerTitle: '',
			jsSource: '//maps.googleapis.com/maps/api/js?v=3.31&key=AIzaSyByF5Th99QzkJtIhod9awRaDK2CGSNB43o',
		},
		_create: function(){
			var self  = this, config = this.options;
			require([config.jsSource], function(){
				var myLatlng = new google.maps.LatLng(config.mapLat, config.mapLong);
				var mapOptions = {
					zoom: config.mapZoom,
					center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				var map = null;
				function createMap(){
					var map = new google.maps.Map(self.element.get(0), mapOptions);
					var markers = [];
                    markers.push({
                        title: config.markerTitle,
                        address: config.mapAddress,
                        latitude: config.mapLat,
                        longitude: config.mapLong
                    });

					
					google.maps.event.addListenerOnce(map, 'idle', function(){});
                    
                    
                    if (typeof config.additionalMarkers == 'object') {
                        if (config.additionalMarkers.length) {
                            $.each(config.additionalMarkers, function(i, location) {
                                markers.push(location);
                            });
                        }
                    }
                    
                    
                    var infowindow = new google.maps.InfoWindow();
                    $.each(markers, function(i, location) {
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(parseFloat(location.latitude), parseFloat(location.longitude)),
                            map: map,
                            title: location.title
                        });
                        google.maps.event.addListener(marker, 'click', function() {
                            infowindow.setContent(location.address);
                            infowindow.open(map, marker);
                        });
                    });
                    
					return map;
				}
				
				if(self.element.parents('.cdz-menu').length > 0){
					var $menu = self.element.parents('.cdz-menu').first(),
					$li = self.element.parents('li.level0').first(),
					$ul = $li.find('> .groupmenu-drop');
					if(self.element.parents('.cdz-slide').length || self.element.parents('.cdz-fade').length || self.element.parents('.cdz-normal').length){
						$ul.on('animated',function(){
							if(map === null){
								map = createMap();
							}else{
								google.maps.event.trigger(map, 'resize');
							}
						});
						$li.hover(function(){
							setTimeout(function(){
								if(map === null){
									map = createMap();
								}else{
									google.maps.event.trigger(map, 'resize');
								}
							},450);
						},function(){
							
						});
					}else{
						$li.hover(function(){
							setTimeout(function(){
								if(map === null){
									map = createMap();
								}else{
									google.maps.event.trigger(map, 'resize');
								}
							},450);
						},function(){
							
						});
					}
				}else{
					map = createMap();
				}
			});
		}
	});
	return $.codazon.googlemappro;
});