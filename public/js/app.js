/*
 | ----------------------------------------------------------------------
 | Define App Module
 | ----------------------------------------------------------------------
 */

define(["jquery", "underscore", "backbone", "handlebars"], function() {

	/*
	 | ----------------------------------------------------------------------
	 | Define Graphist Object
	 | ----------------------------------------------------------------------
	 */
	 
	function Graphist() {};
	
	Graphist.prototype.globals = {};
	Graphist.prototype.globals.cache = {};
	
	/*
	 | ----------------------------------------------------------------------
	 | Handlebars Helper Functions
	 | ----------------------------------------------------------------------
	 */
	
	Handlebars.registerHelper('condEqual', function (v1, v2, options) {
		if (v1 == v2)
		{
			return options.fn(this);
		}
	
		return options.inverse(this);
	});
	
	Handlebars.registerHelper('condNotEqual', function (v1, v2, options) {
		if (v1 != v2)
		{
			return options.fn(this);
		}
	
		return options.inverse(this);
	});
	
	Handlebars.registerHelper('greaterThan', function(v1, v2, options) {
		if (v1 > v2)
		{
			return options.fn(this);
		}
	
		return options.inverse(this);
	});
	
	/*
	 | ----------------------------------------------------------------------
	 | Register namespace
	 | ----------------------------------------------------------------------
	 */
	var ns = Graphist.prototype;
	
	/*
	 | ----------------------------------------------------------------------
	 | Base 64 Encoding
	 | ----------------------------------------------------------------------
	 */
	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	
	var encodeBase64 = function(input) {
        input = escape(input);
        var output = "";
        var chr1, chr2, chr3 = "";
        var enc1, enc2, enc3, enc4 = "";
        var i = 0;
        
        do {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
        
            if (isNaN(chr2)) {
               enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
               enc4 = 64;
            }
            
            output = output +
               keyStr.charAt(enc1) +
               keyStr.charAt(enc2) +
               keyStr.charAt(enc3) +
               keyStr.charAt(enc4);
            chr1 = chr2 = chr3 = "";
            enc1 = enc2 = enc3 = enc4 = "";
        } while (i < input.length);
        
        return output;
    }

    /*
	 | ----------------------------------------------------------------------
	 | Cookies
	 | ----------------------------------------------------------------------
	 */
	ns.getCookie = function(name)
	{
		var i,x,y,cookies;
		cookies = document.cookie.split(";");
		
		for (i = 0; i < cookies.length; i++)
		{
			x = cookies[i].substr(0, cookies[i].indexOf("="));
			y = cookies[i].substr(cookies[i].indexOf("=")+1);
			x = x.replace(/^\s+|\s+$/g,"");
			if (x == name)
			{
				return unescape(y);
			}
		}
	};

	ns.createCookie = function(name, value, expires)
	{
		var cookie = name + "=" + escape(value) + ";";
		 
		if (expires) {
			// If it's a date
			if (expires instanceof Date) {
			  	// If it isn't a valid date
			  	if (isNaN(expires.getTime())) {
			  		expires = new Date();
			  	}
			} else {
				expires = new Date(new Date().getTime() + parseInt(expires) * 1000 * 60 * 60 * 24);
			}

			cookie += "expires=" + expires.toGMTString() + ";";
		}
	 
	  	document.cookie = cookie;
	}

	ns.deleteCookie = function(name)
	{
    	ns.createCookie(name, "", -1);
	}

	/*
	 | ----------------------------------------------------------------------
	 | Helpers
	 | ----------------------------------------------------------------------
	 */
	
	$.fn.serializeObject = function()
	{
	   var o = {};
	   var a = this.serializeArray();
	   $.each(a, function() {
	       if (o[this.name]) {
	           if (!o[this.name].push) {
	               o[this.name] = [o[this.name]];
	           }
	           o[this.name].push(this.value || '');
	       } else {
	           o[this.name] = this.value || '';
	       }
	   });
	   return o;
	};
	
	String.prototype.startsWith = function (str) {
	    return this.slice(0, str.length) == str;
	};
	
	Array.prototype.contains = function ( needle ) {
		for (i in this) {
	    	if (this[i] === needle) return true;
	    }
	    return false;
	}
	
	Array.min = function( array ){
	    return Math.min.apply( Math, array );
	};
	
	ns.notify = function(type, message, duration) 
    {
        $('<div class="notification '+type+'">'+message+'</div>').hide()
			.prependTo("body").fadeIn().delay(duration).fadeOut(400, function() { $(this).remove(); });
    };

	/*
	 | ----------------------------------------------------------------------
	 | Backbone Models
	 | ----------------------------------------------------------------------
	 */

	var Migration = Backbone.Model.extend({ });

	var Table = Backbone.Model.extend({ });

	/*
	 | ----------------------------------------------------------------------
	 | Backbone Collections
	 | ----------------------------------------------------------------------
	 */

	var Migrations = Backbone.Collection.extend({
		model: Migration
	});

	var Tables = Backbone.Collection.extend({
		model: Table
	});

	/*
	 | ----------------------------------------------------------------------
	 | Backbone Views
	 | ----------------------------------------------------------------------
	 */
	
	var MigrationView = Backbone.View.extend({
		
		template: Handlebars.compile($("#migration_template").html()),
		
		className: "migration",
		
		tagName: "section",
		
		initialize: function()
		{
			this.render();
		},
		
		render: function()
		{
			var data = this.model.toJSON();
			
			this.$el.html( this.template(data) );
			this.$el.attr("data-migration-id", data.migration_id);

			if (data.hasOwnProperty("data")) {
				this.$el.attr("data-migration-data", JSON.stringify(data.data));
			}
			
			return this;
		}
		
	});
	
	var migrationsHTML = "";
	
	var MigrationsView = Backbone.View.extend({
		
		initialize: function()
		{
			this.collection = new Migrations(this.options.data);
			this.render();
		},
		
		render: function()
		{
			var that = this;
			
			_.each(this.collection.models, function(item)
			{
				that.renderMigration(item);
			}, this);
			
			this.$el.html(migrationsHTML);
			
			migrationsHTML = "";
		},

		renderMigration: function(item)
		{	
			item.set("user_id", ns.globals.user.user_id);

			var view = new MigrationView({ model: item });

	        migrationsHTML += view.el.outerHTML;
		}
		
	});

	var TableView = Backbone.View.extend({
		
		template: Handlebars.compile($("#table_template").html()),
		
		className: "table",
		
		tagName: "section",
		
		initialize: function()
		{
			this.render();
		},
		
		render: function()
		{
			var data = this.model.toJSON();
			
			this.$el.html( this.template(data) );
			this.$el.attr("data-migration-id", data.migration_id);
			this.$el.attr("data-foreign-keys", JSON.stringify(data.foreign_keys));

			if (data.hasOwnProperty("data")) {
				this.$el.attr("data-migration-data", JSON.stringify(data.data));
			}
			
			return this;
		}
		
	});
	
	var tablesHTML = "";
	
	var TablesView = Backbone.View.extend({
		
		initialize: function()
		{
			this.collection = new Tables(this.options.data);
			this.render();
		},
		
		render: function()
		{
			var that = this;
			
			_.each(this.collection.models, function(item)
			{
				that.renderTable(item);
			}, this);
			
			this.$el.html(tablesHTML);
			
			tablesHTML = "";
		},

		renderTable: function(item)
		{	
			item.set("user_id", ns.globals.user.user_id);

			var view = new TableView({ model: item });

	        tablesHTML += view.el.outerHTML;
		}
		
	}); 

	/*
	 | ----------------------------------------------------------------------
	 | Define API
	 | ----------------------------------------------------------------------
	 */

	ns.API = {};

	/*
	 | ----------------------------------------------------------------------
	 | API resources
	 | ----------------------------------------------------------------------
	 */
	
	ns.API.resources = {
		"migrations": {
			"get": "api/migrations/get"
		},

		"migration": {
			"create": "api/migration/create",
			"servers": "api/migration/servers",
			"tables": "api/migration/tables",
			"entities": "api/migration/entities",
			"relationships": "api/migration/relationships",
			"execute": "api/migration/execute",
			"destroy": "api/migration/destroy"
		}
	};

	/*
	 | ----------------------------------------------------------------------
	 | API functions
	 | ----------------------------------------------------------------------
	 */
	
	ns.API.functions = {};

		/*
		 | ----------------------------------------------------------------------
		 | Migrations
		 | ----------------------------------------------------------------------
		 */
		
		ns.API.functions.migrations = {};

		ns.API.functions.migrations.get = function() {
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migrations.get + "?user_id=" + ns.globals.user.user_id,
				type: "GET",
				dataType: "json",
				success: function(response) {
					if (response.success === "yes") {
						if (response.migrations.length > 0) {
							var migrationsView = new MigrationsView({data: response.migrations, el: $("#start .migrations")});
						}
						d.resolve();
					} else {
						d.fail();
					}
				}
			});

			return d.promise();
		}

		/*
		 | ----------------------------------------------------------------------
		 | Migration
		 | ----------------------------------------------------------------------
		 */
		
		ns.API.functions.migration = {};

		ns.API.functions.migration.create = function() {
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migration.create,
				type: "POST",
				data: {user_id: ns.globals.user.user_id},
				dataType: "json",
				success: function(response) {
					if (response.success === "yes") {
						ns.globals.migration = {
							migration_id: response.migration_id
						};
						d.resolve();
					} else {
						d.fail();
					}
				}
			});

			return d.promise();
		}

		ns.API.functions.migration.addServer = function(data) {
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migration.servers,
				type: "POST",
				data: data,
				dataType: "json",
				success: function(response) {
					if (response.success === "yes")  {
						ns.globals.migration.data = response.data;
						ns.deleteCookie("migration");
						document.cookie = "migration=" + JSON.stringify(ns.globals.migration) + ";";
						d.resolve();
					} else {
						d.fail();
					}
				}
			});

			return d.promise();
		};

		ns.API.functions.migration.getTables = function(container, returnData) {
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migration.tables + "?migration_id=" + ns.globals.migration.migration_id,
				type: "GET",
				dataType: "json",
				success: function(response) {
					if (response.success === "yes") {
						if (response.tables.length > 0) {
							if (!returnData) {
								var tablesView = new TablesView({data: response.tables, el: container});
								d.resolve();
							} else {
								d.resolve(response.tables);
							}
						}
					} else {
						d.fail();
					}
				}
			});

			return d.promise();
		};

		ns.API.functions.migration.saveEntities = function(data)
		{
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migration.entities,
				type: "POST",
				data: data,
				dataType: "json",
				success: function(response) {
					if (response.success === "yes") {
						ns.globals.migration.data = response.data;
						ns.deleteCookie("migration");
						document.cookie = "migration=" + JSON.stringify(ns.globals.migration) + ";";
						d.resolve();
					} else {
						d.fail();
					}
				}
			});

			return d.promise();
		}

		ns.API.functions.migration.saveRelationships = function(data)
		{
			var d = new $.Deferred();

			var requestDataObj = {};
			requestDataObj.migration_id = ns.globals.migration.migration_id;
			requestDataObj.user_id = ns.globals.user.user_id;
			requestDataObj.relationships = JSON.stringify(data);

			$.ajax({
				url: ns.API.resources.migration.relationships,
				type: "POST",
				data: requestDataObj,
				dataType: "json",
				success: function(response)
				{
					if (response.success === "yes") {
						ns.globals.migration.data = response.data;
						ns.deleteCookie("migration");
						document.cookie = "migration=" + JSON.stringify(ns.globals.migration) + ";";
						d.resolve();
					} else {
						d.fail();
					}
				}
			});  

			return d.promise();   
		}

		ns.API.functions.migration.destroy = function(migrationId)
		{
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migration.destroy,
				type: "POST",
				data: {migration_id: migrationId, user_id: ns.globals.user.user_id},
				dataType: "json",
				success: function(response)
				{
					if (response.success === "yes") {
						d.resolve();
					} else {	
						d.fail();
					}
				}
			});

			return d.promise();
		}

		ns.API.functions.migration.execute = function(migrationId)
		{
			var d = new $.Deferred();

			$.ajax({
				url: ns.API.resources.migration.execute,
				type: "POST",
				data: {
					migration_id: migrationId,
					user_id: ns.globals.user.user_id,
					produce_tsv: false
				},
				dataType: "json",
				success: function(response)
				{
					if (response.success === "yes")
					{
						d.resolve();
					}
					else
					{
						d.fail();
					}
				}
			});

			return d.promise();
		}

	/*
	 | ----------------------------------------------------------------------
	 | Document Stuff
	 | ----------------------------------------------------------------------
	 */
	
	ns.document = {};

	ns.document.showAlert = function(type, message)
	{
		showAlert(type, message);
	};

	ns.document.Modal = function(modal, overlay) {
		this.modal = modal;
		this.overlay = overlay;
	}

	ns.document.Modal.prototype.show = function() {
		this.modal.css({
			top:'50%',
			left:'50%',
			margin:'-'+($(this.modal).height() / 2)+'px 0 0 -'+($(this.modal).width() / 2)+'px'
		});
		this.modal.fadeIn(200);
		this.overlay.fadeIn(200);

		this.modal.attr('data-modal-toggled', 'true');
	};

	ns.document.Modal.prototype.hide = function() {
		this.modal.fadeOut(200);
		this.overlay.fadeOut(200);
		this.modal.attr('data-modal-toggled', 'false');
	};

	ns.document.Sidebar = function(sidebar, trigger, speed) {
		this.sidebar      = sidebar;
		this.sidebarWidth = this.sidebar.width();

		this.trigger      = trigger;
	 
		this.speed        = speed;
	}

	ns.document.Sidebar.prototype.show = function() {
		this.sidebar.animate({"right": "0"}, this.speed);

		$('[data-sidebar-opened="true"]').click();

		this.trigger.attr('data-sidebar-opened', 'true');
	}

	ns.document.Sidebar.prototype.hide = function() {
		this.sidebar.animate({"right": "-" + (this.sidebarWidth + 1)}, this.speed);

		this.trigger.attr("data-sidebar-opened", "false");
	}

	ns.document.closeModal = function(modal)
	{
		var modalController = new ns.document.Modal(modal, $('#overlay'));
		modalController.hide();
	};
	
	return new Graphist;
});