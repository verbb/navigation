import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{B as t,G as n,K as r,Q as i,R as a,U as o,W as s,X as c,r as l,u,z as d}from"./Field-CeHB_khG.js";import{l as f,t as p}from"./pk-select-BPnO9qgj-DON8GqWV.js";var m=t(),h=e(d(),1),g=i`
    @layer pk-component {
        :host {
            display: block;
        }

        /* Hard hex fallbacks: when groups portal outside pk-select, size-token
         * vars / theme tokens may be absent and invalid color inherits option black.
         * Weight/color match v1 ComboboxLabel / SelectLabel (text-slate-700, no font-medium). */
        .label {
            padding-block-start: var(--pk-select-group-label-padding-block-start, 8px);
            padding-block-end: var(--pk-select-group-label-padding-block-end, 2px);
            padding-inline: var(--pk-select-item-padding-inline, 10px);
            color: var(--pk-select-group-label-color, var(--pk-color-slate-700, rgba(96, 125, 159, 0.7)));
            font-family: var(--pk-font-family);
            /* Default matches v1 ComboboxLabel/SelectLabel text-xs (12px). */
            font-size: var(--pk-select-group-label-font-size, 12px);
            font-weight: 400;
            line-height: 1.3;
            /* Light-DOM ancestors (e.g. Formie empty dropzone text-center) must not center labels. */
            text-align: left;
            user-select: none;
            pointer-events: none;
        }

        :host([hidden]),
        :host([data-pk-filter-empty]) {
            display: none !important;
        }
    }
`,_=class extends n{constructor(...e){super(...e),this.label=``,this.hidden=!1,this.labelId=l(`pk-option-group-label`)}static{this.styles=g}connectedCallback(){super.connectedCallback(),this.setAttribute(`role`,`group`),this.setAttribute(`aria-labelledby`,this.labelId)}render(){return c`
            <div part="label" class="label" id=${this.labelId}>${this.label}</div>
            <div role="presentation">
                <slot></slot>
            </div>
        `}};r([o({reflect:!0})],_.prototype,`label`,void 0),r([o({type:Boolean,reflect:!0})],_.prototype,`hidden`,void 0),_=r([s(`pk-option-group`)],_);var v=a({tagName:`pk-select`,elementClass:p,react:h.default,events:{onPkChange:`pk-change`,onPkClear:`pk-clear`,onInput:`input`,onChange:`change`,onFocusOut:`focusout`,onPkShow:`pk-show`,onPkAfterShow:`pk-after-show`,onPkHide:`pk-hide`,onPkAfterHide:`pk-after-hide`,onPkOpenChange:`pk-open-change`}}),y=a({tagName:`pk-option`,elementClass:f,react:h.default,events:{onPkOptionSelect:`pk-option-select`,onPkOptionHighlight:`pk-option-highlight`}}),b=a({tagName:`pk-option-group`,elementClass:_,react:h.default}),x=(0,h.forwardRef)(function(e,t){let{disabled:n,invalid:r,clearable:i,multiple:a,open:o,...s}=e;return(0,m.jsx)(v,{ref:t,...s,...u([`disabled`,`invalid`,`clearable`,`multiple`,`open`],{disabled:n,invalid:r,clearable:i,multiple:a,open:o})})});x.displayName=`Select`;var S=(0,h.forwardRef)(function(e,t){let{disabled:n,selected:r,highlighted:i,hidden:a,...o}=e;return(0,m.jsx)(y,{ref:t,...o,...u([`disabled`,`selected`,`highlighted`,`hidden`],{disabled:n,selected:r,highlighted:i,hidden:a})})});S.displayName=`Option`;var C=b;export{C as n,x as r,S as t};
//# sourceMappingURL=Select-CLcn697S.js.map