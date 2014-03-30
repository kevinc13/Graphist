/*
 | ----------------------------------------------------------------------
 | Application Bootstrap
 | ----------------------------------------------------------------------
 */

require.config({
	baseUrl: "public/js",
	paths: {
		"jquery": "lib/jquery",
		"underscore": "lib/underscore",
		"backbone": "lib/backbone",
		"handlebars": "lib/handlebars"
	},
	shim: {
		"backbone": {
			deps: ["jquery", "underscore"],
			exports: "Backbone"
		},
		"underscore": {
			exports: "_"
		}
	}	
});

/*
 | ----------------------------------------------------------------------
 | Define main module
 | ----------------------------------------------------------------------
 */
 
define(["app"], function(app) {
	return app;
});