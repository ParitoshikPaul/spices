/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            owlslider : 'js/owl.carousel.min',
            themecore: 'js/themecore',
            themewidgets : 'js/theme-widgets'
        }
    },
    shim: {
        "js/owl.carousel.min": ['jquery']
    },
	deps: [
        'js/themecore'
    ]
};
