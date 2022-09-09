/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            contentBuilder: 'Codazon_ThemeLayoutPro/js/content-builder',
			jqueryTmpl: 'Codazon_ThemeLayoutPro/js/jquery.tmpl.min',
			contentBuilderJqueryUi: 'Codazon_ThemeLayoutPro/js/jquery-ui.min'
        }
    },
	shim:{
		'Codazon_ThemeLayoutPro/js/content-builder': ['jqueryTmpl', 'contentBuilderJqueryUi'],
		'Codazon_ThemeLayoutPro/js/jquery.tmpl.min': ['jquery'],
		'jquery/jquery-uiCodazon_ThemeLayoutPro/js/jquery-ui.min': ['jquery/jquery-ui']
	}
};
