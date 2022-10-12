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
