void 0===Craft.Navigation&&(Craft.Navigation={}),function(r){function s(e){var i="";r.each(e,function(e,t){var n=t.disabled?"disabled":"";i+='<option value="'+t.value+'" '+n+">"+t.label+"</option>"}),r('select[name="parent"]').each(function(e,t){var n=r(t).val();r(t).html(i),r(t).val(n)})}Craft.Navigation=Garnish.Base.extend({nav:null,siteId:null,structure:null,structureElements:{},elementType:null,elementModals:[],$builderContainer:r(".js-nav-builder"),$structureContainer:r(".js-nav-builder .structure"),$emptyContainer:r(".js-navigation-empty"),$addElementButton:r(".js-btn-element-add"),$addElementLoader:r(".nav-content-pane .buttons .spinner"),$manualForm:r("#manual-form"),$manualLoader:r("#manual-form .spinner"),$template:r("#js-node-template").html(),init:function(e,t){this.nav=e,this.siteId=t.siteId,this.structure=this.$structureContainer.data("structure");for(var n=this.$structureContainer.find("li"),i=0;i<n.length;i++){var s=r(n[i]),a=s.find(".element").data("id");this.structureElements[a]=new Craft.Navigation.StructureElement(this,s)}this.addListener(this.$addElementButton,"activate","showModal"),this.addListener(this.$manualForm,"submit","onManualSubmit")},showModal:function(e){this.elementType=r(e.currentTarget).data("element-type"),this.elementModals[this.elementType]?this.elementModals[this.elementType].show():this.elementModals[this.elementType]=this.createModal(this.elementType)},createModal:function(e){return Craft.createElementSelectorModal(e,{criteria:{enabledForSite:null},sources:"*",multiSelect:!0,onSelect:r.proxy(this,"onModalSelect")})},onModalSelect:function(e){for(var t=r('.tab-list-item[data-element-type="'+this.elementType.replace(/\\/gi,"\\\\")+'"]'),n=t.find(".js-parent-node select").val(),i=t.find("#newWindow-field input").val(),s=0;s<e.length;s++){var a=e[s];this.elementModals[this.elementType].$body.find('tr[data-id="'+a.id+'"]').removeClass("sel");var d={navId:this.nav.id,siteId:this.siteId,elementId:a.id,elementSiteId:a.siteId,title:a.label,url:a.url,type:this.elementType,newWindow:i,parentId:n};this.saveNode(d)}},onManualSubmit:function(e){e.preventDefault();var t=this.$manualForm.find(".js-parent-node select").val(),n=this.$manualForm.find("#newWindow-field input").val(),i={navId:this.nav.id,siteId:this.siteId,title:this.$manualForm.find("#title").val(),url:this.$manualForm.find("#url").val(),newWindow:n,parentId:t};this.saveNode(i)},addNode:function(e){var t="manual",n;e.type&&(t=e.type.split("\\").pop());var i=this.$template.replace(/__siteId__/gi,e.siteId?e.siteId:"").replace(/__status__/gi,e.enabled?"enabled":"disabled").replace(/__title__/gi,e.title).replace(/__id__/gi,e.id).replace(/__url__/gi,e.url).replace(/__type__/gi,t),s=r(i),a=this.structure.$container;if(0<e.newParentId){var d=this.structure.$container.find('.element[data-id="'+e.newParentId+'"]').closest("li"),l=d.find("> ul"),o=d.data("level");l.length||(l=r("<ul/>")).appendTo(d),a=l}return s.appendTo(a),this.structure.structureDrag.addItems(s),s.css("margin-bottom",-30),s.velocity({"margin-bottom":0},"fast"),s},saveNode:function(e){this.$manualLoader.removeClass("hidden"),this.$addElementLoader.removeClass("hidden"),Craft.postActionRequest("navigation/nodes/save-node",e,r.proxy(function(e,t){if(this.$manualLoader.addClass("hidden"),this.$addElementLoader.addClass("hidden"),e.success){this.$manualForm.find("#title").val(""),this.$manualForm.find("#url").val("");var n=e.node.id,i=this.addNode(e.node);this.structureElements[n]=new Craft.Navigation.StructureElement(this,i),this.$emptyContainer.addClass("hidden"),s(e.parentOptions),Craft.cp.displayNotice(Craft.t("navigation","Node added."))}else Craft.cp.displayError(e.message)},this))}}),Craft.Navigation.StructureElement=Garnish.Base.extend({container:null,structure:null,$node:null,$elements:null,$element:null,$settingsBtn:null,$deleteBtn:null,init:function(e,t){this.container=e,this.structure=e.structure,this.$node=t,this.$element=t.find(".element:first"),this.$settingsBtn=this.$node.find(".settings:first"),this.$deleteBtn=this.$node.find(".delete:first"),this.structure.structureDrag.settings.onDragStop=r.proxy(this,"onDragStop"),this.addListener(this.$settingsBtn,"click","showSettings"),this.addListener(this.$element,"dblclick","showSettings"),this.addListener(this.$deleteBtn,"click","removeNode")},onDragStop:function(){var e,t,n,i={nodeId:this.$element.data("id"),siteId:this.$element.data("site-id"),navId:this.container.nav.id};setTimeout(function(){Craft.postActionRequest("navigation/nodes/move",i,r.proxy(function(e,t){e.success&&s(e.parentOptions)},this))},500)},showSettings:function(){new Craft.Navigation.Editor(this.$element)},removeNode:function(){for(var e=[],n=this.$node.find(".element"),t=0;t<n.length;t++)e[t]=r(n[t]).data("id");var i;confirm(Craft.t("navigation","Are you sure you want to delete “{title}” and its descendants?",{title:this.$element.data("label")}))&&Craft.postActionRequest("navigation/nodes/delete",{nodeIds:e},r.proxy(function(e,t){e.success?(Craft.cp.displayNotice(Craft.t("navigation","Node deleted.")),s(e.parentOptions),n.each(r.proxy(function(e,t){this.structure.removeElement(r(t)),delete this.container.structureElements[r(t).data("id")]},this)),0==Object.keys(this.container.structureElements).length&&this.container.$emptyContainer.removeClass("hidden")):Craft.cp.displayError(e.errors)},this))}}),Craft.Navigation.Editor=Garnish.Base.extend({$node:null,nodeId:null,siteId:null,$form:null,$fieldsContainer:null,$cancelBtn:null,$saveBtn:null,$spinner:null,hud:null,init:function(e){this.$node=e,this.nodeId=e.data("id"),this.siteId=e.data("site-id"),this.$node.addClass("loading");var t={nodeId:this.nodeId,siteId:this.siteId};Craft.postActionRequest("navigation/nodes/editor",t,r.proxy(this,"showEditor"))},showEditor:function(e,t){if(e.success){this.$node.removeClass("loading");var n=r();this.$form=r("<form/>"),r('<input type="hidden" name="nodeId" value="'+this.nodeId+'">').appendTo(this.$form),r('<input type="hidden" name="siteId" value="'+this.siteId+'">').appendTo(this.$form),this.$fieldsContainer=r('<div class="fields"/>').appendTo(this.$form),this.$fieldsContainer.html(e.html),Craft.initUiElements(this.$fieldsContainer);var i=r('<div class="hud-footer"/>').appendTo(this.$form),s=r('<div class="buttons right"/>').appendTo(i);this.$cancelBtn=r('<div class="btn">'+Craft.t("app","Cancel")+"</div>").appendTo(s),this.$saveBtn=r('<input class="btn submit" type="submit" value="'+Craft.t("app","Save")+'"/>').appendTo(s),this.$spinner=r('<div class="spinner left hidden"/>').appendTo(s),n=n.add(this.$form),this.hud=new Garnish.HUD(this.$node,n,{bodyClass:"body nav-editor-hud",closeOtherHUDs:!1}),this.hud.on("hide",r.proxy(function(){delete this.hud},this)),this.addListener(this.$saveBtn,"click","saveNode"),this.addListener(this.$cancelBtn,"click","closeHud")}},saveNode:function(e){e.preventDefault(),this.$spinner.removeClass("hidden");var t=this.$form.serialize(),n=this.$node.parent().find(".status"),i=this.$node.find(".target");Craft.postActionRequest("navigation/nodes/save-node",t,r.proxy(function(e,t){this.$spinner.addClass("hidden"),e.success?(Craft.cp.displayNotice(Craft.t("navigation","Node updated.")),s(e.parentOptions),this.$node.parent().data("label",e.node.title),this.$node.parent().find(".title").text(e.node.title),e.node.enabled&&e.node.enabledForSite?(n.addClass("enabled"),n.removeClass("disabled")):(n.addClass("disabled"),n.removeClass("enabled")),this.closeHud()):(Garnish.shake(this.hud.$hud),Craft.cp.displayError(e.errors))},this))},closeHud:function(){this.hud.hide(),delete this.hud}})}(jQuery);
//# sourceMappingURL=navigation.js.map