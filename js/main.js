/*

*
		<コ:彡
*
		HalcyNamer
*

*		nabil kashyap (www.nabilk.com)
		MIT license
*		crowdsourced metadata tagging for
		Swarthmore Colleges local contentDM instance
*

*/

function test(arg){
	string = arg;
	console.log(string);
}

Backbone.emulateHTTP = true;
// setup app namespace
var HN = {
	registry: []
};

// initial years index data
HN.yearsList = [ 1964, 1969, 1974, 1979, 1984, 1989, 1994, 1999, 2004, 2009 ];

// set up routes
HN.routes = Backbone.Router.extend({

	routes: {
		'' : 'index',
		'class/:year' : 'getclass',
		'pop' : 'pop'
	},

	// initialize the indexview
	initialize: function(){
		test('init');
		this.indexview = new HN.indexView({ model: new HN.years(HN.yearsList) });
	},
	// render the index
	index: function(){
		test('index');
		this.indexview.render();
	},

	// because page data is not changing and otherwise too many zombie listeners were created: a fetch success method
	getclass: function(year){
		this.volumeview = new HN.volumeView({ collection: new HN.volume() });
		var volumeview = this.volumeview;
		this.volumeview.collection.fetch({
			data: { year: year },
			processData: true,
			success: function() { volumeview.render();},
			error: function(a,b,c) { test(b); }
		});
	}
});

HN.years = Backbone.Model.extend({});

HN.page = Backbone.Model.extend({});

// for saving the model, if I ever get to that!
HN.form = Backbone.Model.extend({

	url: 'php/halcyon.php?save=1',
	defaults: { tag: '' }
});

// this fetch url won't work without data: { year: '' }
HN.volume = Backbone.Collection.extend({

	model: HN.page,
	url: './php/halcyon.php',
});

HN.indexView = Backbone.View.extend({
	
	el: '#main',
	template: _.template($('#index-template').html()),
	initialize: function(){
	},

	render: function(){
		test('render index');
		var el = this.$el;
		var template = this.template;
		el.empty();
		_.each( this.model.attributes, function(d){
			el.append(template({'year': d }));
		});
		return this;
	}
});

HN.pageView = Backbone.View.extend({

	className: 'page',
	template: _.template($('#page-template').html()),
	events: {

	},
	initialize: function(){

		this.listenTo(this.model, 'change', this.render);
	},
	render: function(){

		this.$el.append( this.template( this.model.toJSON() ));
		this.parentView.$el.append(this.$el);
		return this;
	}
});

HN.formView = Backbone.View.extend({

	tagName: 'form',
	className: 'tags',
	template: _.template($('#form-template').html()),
	events: {
		'click input:submit': 'saveform',
		'change #tags-list': 'input'
	},
	initialize: function(){
	},
	render: function(){
		var that = this;
		this.$el.append(this.template(this.model.toJSON()));
		this.parentView.$el.append(this.$el);
		$('#tags-' + this.model.attributes.id).tagit({
			allowSpaces: true,
			singleField: true,
			placeholderText: 'enter a name',
			afterTagAdded: function(d){ that.input(d) }
			});
		$('#btn-tag-' + this.model.attributes.id).tooltip({
			animation: true,
			html: true,
			title: "<h4>click to add tags</h4>"
		});
		this.$el.hide();
		return this;
	},
	saveform: function(e){
		e.preventDefault();
		test('submit');
		this.model.save();
		this.parentView.tagToggle();
	},
	input: function(e){

		test('input change');
		var $input = $(e.target);
		this.model.set($input.attr('name'), $input.val());
	},
	tagInput: function(d){
		test(d);
	}
});

HN.volumeView = Backbone.View.extend({
	el: '#main',
	counter: 0,
	isShowing: false,
	template: _.template($('#page-template').html()),
	events: {
		'click button.left' : 'count',
		'click button.right' : 'count',
		'click button.tag' : 'tagToggle'
	},
	count: function(e){
		e.preventDefault();
		var dir = $(e.target).attr('class');
		var max = $('.page ul').length - 1;
		// counter logic
		if (dir.indexOf("right") > 0) { this.counter = (this.counter == max) ? 0 : this.counter + 1; } 
		else if (dir.indexOf("left") > 0) {  this.counter = (this.counter  === 0) ? max : this.counter - 1; }
		if(this.isShowing === true) { this.tagToggle() };
		// test(this.counter);
		this.show();
	},
	// quick function for showing and hiding the right page
	show: function(){
		test(this.counter);
		$('div.page').hide();
		$('div.page').removeClass('showing');
		$($('div.page')[this.counter]).addClass('showing');
		$($('div.page')[this.counter]).fadeIn().show();
		$('form').fadeOut().hide();
	},
	tagToggle: function(){
		this.isShowing = !this.isShowing;
		var currentform = $($('form')[this.counter]);
		test('toggle');
		(this.isShowing === true) ? currentform.fadeIn().show() : currentform.fadeOut().hide();
		(this.isShowing === true) ? currentform.addClass('showing') : currentform.removeClass('showing');
		(this.isShowing === true) ? $($('.page img')[this.counter]).addClass('with-form') : $($('.page img')[this.counter]).removeClass('with-form');
		
	},	
	// doesn't really do anything -- I like the idea of binding to reset, like on fetch, but doesn't seem to happen
	initialize: function(){
		this.listenTo(this.collection, 'reset', this.render);

	},
	render: function(){
		test('render volume');
		this.$el.empty();
		var thisView = this;
		this.$el.append($('#button-left-template').html());

		this.collection.each(function(d){
			var pageview = new HN.pageView({ model: d });
			pageview.parentView = thisView;
			pageview.render();

			var form = new HN.form(d.attributes);
			var formview = new HN.formView({ model: form });
			formview.parentView = thisView;
			formview.render();
		});
		this.show();
		this.$el.append($('#button-right-template').html());
		return this;
	}
});

var readysetgo = new HN.routes();

$(function() { 
	$('.navbar-header span#about').hover(function(){
		$('#info').toggle('slow', 'linear');
	});
	Backbone.history.start(); 
});