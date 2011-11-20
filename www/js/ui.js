var Game = Game || {};

Game.UI = {
	resourceTable: function (price)
	{
		var table = $('<table>');
		var header = $('<tr>');
		var values = $('<tr>');
		$.each(price, function (resource, cost) {
			if (cost > 0) {
				var label = $('<th>');
				label.append($('<img />').attr({src: basePath + '/images/resources/' + resource + '.png'}));
				header.append(label);
				var value = $('<td>').html(cost);
				var pair = {};
				pair[resource] = cost;
				if (!Game.resources.hasSufficientResources(pair)) {
					value.addClass('resourceInsufficient');
				}
				values.append(value);
				Game.descriptions.translate('resource', resource, $(label).children('img'), 'title');
			}
		});
		table.append(header, values);
		return table;
	},
	
	timeTable: function (time)
	{
		var formattedTime = Game.utils.formatTime(time);
		return $('<table>').append($('<tr>').append($('<th>').html('Čas'), $('<td>').html(formattedTime)));
	},
	
	Dialog: Class({
		constructor: function (id)
		{
			this.id = id;
			this.element = $('<div />');
			if (Game.utils.isset(id)) this.element.attr('id', id);
		},
		
		config: {
			width: 400,
			height: 250,
			position: ['center', 'center']
		},
		
		hasButtons: true,
		
		getConfig: function ()
		{
			config = this.config;
			if (Game.utils.isset(this.id) && (data = Game.cookie.get(this.id))) {
				$.each($.parseJSON(data), function (key, value) {
					config[key] = value;
				})
			}
			return config;
		},
		
		setConfig: function (key, value)
		{
			if (Game.utils.isset(this.id)) {
				var config = this.getConfig();
				config[key] = value;
				Game.cookie.set(this.id, JSON.stringify(config), 7);
			}
		},
		
		getBody: function ()
		{
			return Game.utils.isset(this.body) ? this.body : null;
		},
		
		setBody: function (body)
		{
			this.body = body;
			return this;
		},
		
		getTitle: function ()
		{
			return Game.utils.isset(this.title) ? this.title : null;
		},
		
		setTitle: function (title)
		{
			this.title = title;
			return this;
		},
		
		getSubmit: function ()
		{
			return Game.utils.isset(this.submit) ? this.submit : null;
		},
		
		setSubmit: function (submit)
		{
			this.submit = submit;
			return this;
		},
		
		show: function ()
		{
			var element = this.element;
			var config = this.getConfig();
			element.html(this.getBody());
			var buttons = new Array();
			if (submit = this.getSubmit()) {
				var context = this;
				buttons.push({
					text: submit.text,
					click: function (event, ui) {
						submit.click(context);
						$(this).dialog("close");
					}
				});
			}
			buttons.push({
				text: 'Zrušit',
				click: function() {
					$(this).dialog("close");
				}
			});
			$(element).dialog({
				title: this.getTitle(),
				buttons: this.hasButtons ? buttons : [],
				width: config.width,
				height: config.height,
				position: config.position
			});
			$(element).bind('dialogdragstop', {context: this}, function (event, ui) {
				event.data.context.setConfig('position', $(element).dialog("option", "position"));
			});
			$(element).bind('dialogresizestop', {context: this}, function (event, ui) {
				event.data.context.setConfig('width', $(element).dialog("option", "width"));
				event.data.context.setConfig('height', $(element).dialog("option", "height"));
			});
			$(element).bind('dialogclose', {context: this}, function (event, ui) {
				event.data.context.closeHandler(event.data.context);
				$(this).dialog("destroy").remove();
			});
			if (this.hasButtons && submit) {
				$(element).parent().find('button').first().addClass('submitButton');
			}
			return element;
		},
		
		closeHandler: function () {}
	})
}