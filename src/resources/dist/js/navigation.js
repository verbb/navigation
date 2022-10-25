/*!
  SerializeJSON jQuery plugin.
  https://github.com/marioizquierdo/jquery.serializeJSON
  version 2.9.0 (Jan, 2018)

  Copyright (c) 2012-2018 Mario Izquierdo
  Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
  and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
*/
!function(e){if("function"==typeof define&&define.amd)define(["jquery"],e);else if("object"==typeof exports){var n=require("jquery");module.exports=e(n)}else e(window.jQuery||window.Zepto||window.$)}(function(e){"use strict";e.fn.serializeJSON=function(n){var r,s,t,i,a,u,l,o,p,c,d,f,y;return r=e.serializeJSON,s=this,t=r.setupOpts(n),i=s.serializeArray(),r.readCheckboxUncheckedValues(i,t,s),a={},e.each(i,function(e,n){u=n.name,l=n.value,p=r.extractTypeAndNameWithNoType(u),c=p.nameWithNoType,(d=p.type)||(d=r.attrFromInputWithName(s,u,"data-value-type")),r.validateType(u,d,t),"skip"!==d&&(f=r.splitInputNameIntoKeysArray(c),o=r.parseValue(l,u,d,t),(y=!o&&r.shouldSkipFalsy(s,u,c,d,t))||r.deepSet(a,f,o,t))}),a},e.serializeJSON={defaultOptions:{checkboxUncheckedValue:void 0,parseNumbers:!1,parseBooleans:!1,parseNulls:!1,parseAll:!1,parseWithFunction:null,skipFalsyValuesForTypes:[],skipFalsyValuesForFields:[],customTypes:{},defaultTypes:{string:function(e){return String(e)},number:function(e){return Number(e)},boolean:function(e){return-1===["false","null","undefined","","0"].indexOf(e)},null:function(e){return-1===["false","null","undefined","","0"].indexOf(e)?e:null},array:function(e){return JSON.parse(e)},object:function(e){return JSON.parse(e)},auto:function(n){return e.serializeJSON.parseValue(n,null,null,{parseNumbers:!0,parseBooleans:!0,parseNulls:!0})},skip:null},useIntKeysAsArrayIndex:!1},setupOpts:function(n){var r,s,t,i,a,u;u=e.serializeJSON,null==n&&(n={}),t=u.defaultOptions||{},s=["checkboxUncheckedValue","parseNumbers","parseBooleans","parseNulls","parseAll","parseWithFunction","skipFalsyValuesForTypes","skipFalsyValuesForFields","customTypes","defaultTypes","useIntKeysAsArrayIndex"];for(r in n)if(-1===s.indexOf(r))throw new Error("serializeJSON ERROR: invalid option '"+r+"'. Please use one of "+s.join(", "));return i=function(e){return!1!==n[e]&&""!==n[e]&&(n[e]||t[e])},a=i("parseAll"),{checkboxUncheckedValue:i("checkboxUncheckedValue"),parseNumbers:a||i("parseNumbers"),parseBooleans:a||i("parseBooleans"),parseNulls:a||i("parseNulls"),parseWithFunction:i("parseWithFunction"),skipFalsyValuesForTypes:i("skipFalsyValuesForTypes"),skipFalsyValuesForFields:i("skipFalsyValuesForFields"),typeFunctions:e.extend({},i("defaultTypes"),i("customTypes")),useIntKeysAsArrayIndex:i("useIntKeysAsArrayIndex")}},parseValue:function(n,r,s,t){var i,a;return i=e.serializeJSON,a=n,t.typeFunctions&&s&&t.typeFunctions[s]?a=t.typeFunctions[s](n):t.parseNumbers&&i.isNumeric(n)?a=Number(n):!t.parseBooleans||"true"!==n&&"false"!==n?t.parseNulls&&"null"==n?a=null:t.typeFunctions&&t.typeFunctions.string&&(a=t.typeFunctions.string(n)):a="true"===n,t.parseWithFunction&&!s&&(a=t.parseWithFunction(a,r)),a},isObject:function(e){return e===Object(e)},isUndefined:function(e){return void 0===e},isValidArrayIndex:function(e){return/^[0-9]+$/.test(String(e))},isNumeric:function(e){return e-parseFloat(e)>=0},optionKeys:function(e){if(Object.keys)return Object.keys(e);var n,r=[];for(n in e)r.push(n);return r},readCheckboxUncheckedValues:function(n,r,s){var t,i,a;null==r&&(r={}),e.serializeJSON,t="input[type=checkbox][name]:not(:checked):not([disabled])",s.find(t).add(s.filter(t)).each(function(s,t){if(i=e(t),null==(a=i.attr("data-unchecked-value"))&&(a=r.checkboxUncheckedValue),null!=a){if(t.name&&-1!==t.name.indexOf("[]["))throw new Error("serializeJSON ERROR: checkbox unchecked values are not supported on nested arrays of objects like '"+t.name+"'. See https://github.com/marioizquierdo/jquery.serializeJSON/issues/67");n.push({name:t.name,value:a})}})},extractTypeAndNameWithNoType:function(e){var n;return(n=e.match(/(.*):([^:]+)$/))?{nameWithNoType:n[1],type:n[2]}:{nameWithNoType:e,type:null}},shouldSkipFalsy:function(n,r,s,t,i){var a=e.serializeJSON.attrFromInputWithName(n,r,"data-skip-falsy");if(null!=a)return"false"!==a;var u=i.skipFalsyValuesForFields;if(u&&(-1!==u.indexOf(s)||-1!==u.indexOf(r)))return!0;var l=i.skipFalsyValuesForTypes;return null==t&&(t="string"),!(!l||-1===l.indexOf(t))},attrFromInputWithName:function(e,n,r){var s,t;return s=n.replace(/(:|\.|\[|\]|\s)/g,"\\$1"),t='[name="'+s+'"]',e.find(t).add(e.filter(t)).attr(r)},validateType:function(n,r,s){var t,i;if(i=e.serializeJSON,t=i.optionKeys(s?s.typeFunctions:i.defaultOptions.defaultTypes),r&&-1===t.indexOf(r))throw new Error("serializeJSON ERROR: Invalid type "+r+" found in input name '"+n+"', please use one of "+t.join(", "));return!0},splitInputNameIntoKeysArray:function(n){var r;return e.serializeJSON,r=n.split("["),""===(r=e.map(r,function(e){return e.replace(/\]/g,"")}))[0]&&r.shift(),r},deepSet:function(n,r,s,t){var i,a,u,l,o,p;if(null==t&&(t={}),(p=e.serializeJSON).isUndefined(n))throw new Error("ArgumentError: param 'o' expected to be an object or array, found undefined");if(!r||0===r.length)throw new Error("ArgumentError: param 'keys' expected to be an array with least one element");i=r[0],1===r.length?""===i?n.push(s):n[i]=s:(a=r[1],""===i&&(o=n[l=n.length-1],i=p.isObject(o)&&(p.isUndefined(o[a])||r.length>2)?l:l+1),""===a?!p.isUndefined(n[i])&&e.isArray(n[i])||(n[i]=[]):t.useIntKeysAsArrayIndex&&p.isValidArrayIndex(a)?!p.isUndefined(n[i])&&e.isArray(n[i])||(n[i]=[]):!p.isUndefined(n[i])&&p.isObject(n[i])||(n[i]={}),u=r.slice(1),p.deepSet(n[i],u,s,t))}}});

// ==========================================================================

// Navigation Plugin for Craft CMS
// Author: Verbb - https://verbb.io/

// ==========================================================================

// @codekit-prepend "_jquery.serializejson.min.js"

// When clicking migrate, change the form submission to our action endpoint
$('.btn-migrate').on('click', function(e) {
    e.preventDefault();

    $('input[name="action"]').val($(this).data('action'));

    $('#main-form').submit();
});


if (typeof Craft.Navigation === typeof undefined) {
    Craft.Navigation = {};
}

(function($) {

// Super important to reset the sort on page load. This is because we have to allow sorting by Structure and ID
// even though we only ever want Structure sorting. But structure data is blown away for deleted nodes, and when
// viewing trashed nodes, we'll get an error when trying to view in structure order. So we need to switch to ID.
// Fortunately `Craft.BaseElementIndex` does this for as when we switch statuses, but not when refreshing the page.
// To replicate, view trashed nodes, then refresh the page - it'll still be sorted by ID. However, manually changing
// to All/Enabled/Disabled status will trigger it back to the default Structure.
Craft.setQueryParam('sort', null);

Craft.Navigation.NodeIndex = Craft.BaseElementIndex.extend({
    elementModals: [],

    init: function(elementType, $container, settings) {
        this.navId = settings.navId;
        this.$navSidebar = $('.navigation-nodes-sidebar');

        this.base(elementType, $container, settings);

        // The `Craft.BaseElementIndex` doesn't actually stop you from switching to a site that's not enabled
        // which is the job for the sidebar source (which we hide) so remove sites from the site menu. 
        if (this.siteMenu) {
            this.siteMenu.$options.each(function(index, item) {
                if (!settings.enabledSiteIds.includes($(item).data('site-id'))) {
                    $(item).remove();
                }
            });
        }

        // Move the instructions to below the nav title, because we can't with Twig along
        var $instructions = $('#js-navigation-nodes-instructions');

        if ($instructions.length) {
            $instructions = $instructions.find('.navigation-nodes-instructions').remove();

            $instructions.insertBefore($('#main-content'));
        }
    },

    afterInit: function() {
        // Ensure the order is always structure
        Object.keys(this.sourceStates).forEach(key => {
            this.sourceStates[key].order = 'structure';
        });
        
        this.base();

        // Add an edit button to each table row
        this.$elements.on('click', 'tbody tr a.node-edit-btn', this.editNode.bind(this));

        // Listen to when submitting a form in the sidebar
        this.$navSidebar.find('form').each($.proxy(function(index, form) {
            const $form = $(form);

            // Handle element forms differently
            if ($form.hasClass('form-type-element')) {
                $form.on('submit', this.showElementModal.bind(this));
            } else {
                $form.on('submit', this.onNodeFormSubmit.bind(this));
            }
        }, this));
    },

    editNode: function(e) {
        e.preventDefault();

        // Open the element slide-out
        var $element = $(e.target).parents('tr').find('.element');

        Craft.createElementEditor($element.data('type'), $element);
    },

    showElementModal: function(e) {
        e.preventDefault();

        this.$form = $(e.target);

        var $saveBtn = this.$form.find('button[type=submit]');
        this.nodeElementType = $saveBtn.data('element-type');
        this.nodeElementSources = $saveBtn.data('sources');

        // Cache element modals, so we don't create new ones each time
        if (!this.elementModals[this.nodeElementType]) {
            this.elementModals[this.nodeElementType] = this.createModal();
        } else {
            this.elementModals[this.nodeElementType].show();

            // De-select any previously selected items
            this.elementModals[this.nodeElementType].elementIndex.view.deselectAllElements();
        }
    },

    createModal: function() {
        return Craft.createElementSelectorModal(this.nodeElementType, {
            defaultSiteId: this.siteId,
            sources: this.nodeElementSources,
            multiSelect: true,
            onSelect: $.proxy(this, 'onElementModalSelect'),
        });
    },

    onElementModalSelect: function(elements) {
        var data = [];

        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];

            // Unselect element in modal
            this.elementModals[this.nodeElementType].$body.find('tr[data-id="' + element.id + '"]').removeClass('sel');

            var nodeData = this.$form.serializeJSON();
            nodeData.navId = this.navId;
            nodeData.siteId = this.siteId;
            nodeData.elementId = element.id;
            nodeData.elementSiteId = element.siteId;
            nodeData.title = element.label;
            nodeData.url = element.url;

            data.push(nodeData);
        }

        this.addNodes({ nodes: data });
    },

    onNodeFormSubmit: function(e) {
        e.preventDefault();

        this.$form = $(e.target);

        var data = this.$form.serializeJSON();
        data.navId = this.navId;
        data.siteId = this.siteId;

        // Always send a collection of nodes to add
        this.addNodes({ nodes: [data] });
    },

    addNodes: function(data) {
        var $spinner = this.$form.find('.spinner');
        var $errorList = this.$form.find('ul.errors');
        var $saveBtn = this.$form.find('button[type=submit]');

        $spinner.removeClass('hidden');

        if ($errorList.length) {
            $errorList.remove();
        }

        Craft.sendActionRequest('POST', 'navigation/nodes/add-nodes', { data })
            .then((response) => {
                Craft.cp.displayNotice(response.data.message);

                var parentId = this.$form.find('[name="parentId"').val();

                this.updateElements();

                // Reset the form, but keep the parent set
                this.$form[0].reset();
                this.$form.find('[name="parentId"').val(parentId);
            })
            .catch((error) => {
                const response = error.response;

                if (response && response.data && response.data.errors) {
                    $errorList = $('<ul class="errors"/>').insertBefore($saveBtn.parent());

                    for (var attribute in response.data.errors) {
                        if (!response.data.errors.hasOwnProperty(attribute)) {
                            continue;
                        }

                        for (var i = 0; i < response.data.errors[attribute].length; i++) {
                            var error = response.data.errors[attribute][i];
                            $('<li>' + error + '</li>').appendTo($errorList);
                        }
                    }
                }

                if (response && response.data && response.data.message) {
                    Craft.cp.displayError(response.data.message);
                } else {
                    console.error(error);

                    Craft.cp.displayError();
                }
            })
            .finally(() => {
                $spinner.addClass('hidden');
            });
    },

    onUpdateElements: function() {
        this.updateParentSelect();
    },

    onSelectSite: function() {
        // When changing the site, the index will update anchor href's, reflecting the
        // current URL with site query params. Which is normally great, but messes with the accordion tabs
        // which only need to contain the hash to toggle tabs. So put them back!
        $('.navigation-nodes-sidebar a.tab').each(function(index, element) {
            var $a = $(element);
            var href = $a.attr('href');
            var parts = href.split('#');

            $a.attr('href', '#' + parts[1]);
        });
    },

    updateParentSelect: function() {
        var data = { navId: this.navId, siteId: this.siteId };

        Craft.sendActionRequest('POST', 'navigation/nodes/get-parent-options', { data })
            .then((response) => {
                var html = '';

                $.each(response.data.options, function(index, value) {
                    var disabled = value.disabled ? 'disabled' : '';
                    html += '<option value="' + value.value + '" ' + disabled + '>' + value.label + '</option>';
                });

                $('.js-parent-node select').each(function(index, element) {
                    var selected = $(element).val();

                    $(element).html(html);
                    $(element).val(selected);
                });
            });
    },
});

// Register it!
Craft.registerElementIndexClass('verbb\\navigation\\elements\\Node', Craft.Navigation.NodeIndex);


})(jQuery);
