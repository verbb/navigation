import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{A as t,B as n,G as r,Q as i,R as a,U as o,V as s,X as c,Y as l,a as u,q as d,r as f,s as p,z as m}from"./Field-owt98Kgd.js";import{_ as h,t as g}from"./pk-select-LgrFuFEF-Dx0J6Pwv.js";function _(e,t){let n=String(e??``),r=String(t??``).trim();if(!r)return[{text:n,match:!1}];let i=n.toLowerCase(),a=r.toLowerCase(),o=[],s=0,c=i.indexOf(a);for(;c!==-1;)c>s&&o.push({text:n.slice(s,c),match:!1}),o.push({text:n.slice(c,c+r.length),match:!0}),s=c+r.length,c=i.indexOf(a,s);return s<n.length&&o.push({text:n.slice(s),match:!1}),o.length>0?o:[{text:n,match:!1}]}var v=d`
    @layer pk-component {
        :host {
            display: block;
        }

        .option {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            margin: 0;
            min-height: var(--pk-select-item-min-height, var(--pk-input-height));
            padding-block: var(--pk-select-item-padding-block, 6px);
            padding-inline-start: var(--pk-select-item-padding-inline, 10px);
            padding-inline-end: var(--pk-select-item-padding-inline-end, 2rem);
            border: var(--pk-select-trigger-border-width, 1px) solid transparent;
            background: transparent;
            /* Craft CP body text (~gray-700), not gray-900. */
            color: var(--pk-color-gray-700);
            font: inherit;
            font-family: var(--pk-font-family);
            font-size: var(--pk-select-item-font-size, var(--pk-font-size-base));
            line-height: var(--pk-select-item-line-height, 1.4);
            text-align: left;
            white-space: nowrap;
            cursor: default;
            user-select: none;
            outline: none;
            box-sizing: border-box;
        }

        .start {
            display: none;
            flex: 0 0 auto;
            align-items: center;
        }

        :host([data-has-start]) .start {
            display: inline-flex;
        }

        :host([hidden]) {
            display: none !important;
        }

        .option:focus-visible,
        :host([highlighted]) .option {
            background: var(--pk-color-slate-100);
        }

        :host([disabled]) .option,
        .option[aria-disabled='true'] {
            pointer-events: none;
            opacity: 0.5;
        }

        .check {
            position: absolute;
            inset-inline-end: var(--pk-select-item-indicator-inset, 0.5rem);
            top: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            width: var(--pk-select-item-indicator-size, 0.75rem);
            height: var(--pk-select-item-indicator-size, 0.75rem);
            color: var(--pk-color-gray-700);
            pointer-events: none;
            transform: translateY(-50%);
            line-height: 0;
        }

        :host([selected]) .check {
            display: inline-flex;
        }

        .check svg {
            display: block;
            width: var(--pk-select-item-indicator-size, 0.75rem);
            height: var(--pk-select-item-indicator-size, 0.75rem);
            flex-shrink: 0;
            pointer-events: none;
        }

        .label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            /* Allow custom multi-line option layouts (title + subtitle) to stack. */
            white-space: normal;
        }

        .match {
            padding: 0;
            border-radius: 2px;
            background: var(--pk-color-blue-100);
            color: inherit;
        }
    }
`,y=p(u.check),b=class extends n{constructor(...e){super(...e),this.value=``,this.label=``,this.disabled=!1,this.selected=!1,this.highlighted=!1,this.hidden=!1,this.focusIndex=-1,this.optionId=``,this.matchQuery=``}static{this.styles=v}focusControl(e=!0){this.shadowRoot?.querySelector(`.option`)?.focus({preventScroll:e})}getLabel(){if(this.label.trim())return this.label.trim();let e=this.shadowRoot?.querySelector(`slot:not([name])`);return e?e.assignedNodes().map(e=>(e.textContent??``).trim()).filter(Boolean).join(` `).trim():this.textContent?.trim()??this.value}getSearchText(){let e=this.shadowRoot?.querySelector(`slot:not([name])`);return e&&e.assignedNodes().map(e=>(e.textContent??``).trim()).filter(Boolean).join(` `).trim()||this.getLabel()}hasRichLabelContent(){return[...this.children].some(e=>e instanceof HTMLElement?!e.slot||e.slot===``:!1)}getStartElements(){return[...this.querySelectorAll(`:scope > [slot="start"]`)].filter(e=>e instanceof HTMLElement)}firstUpdated(){(this.shadowRoot?.querySelector(`slot[name="start"]`))?.addEventListener(`slotchange`,()=>this.syncStartDecoration()),this.syncStartDecoration()}syncStartDecoration(){this.toggleAttribute(`data-has-start`,this.getStartElements().length>0)}handleClick(){this.disabled||this.dispatchEvent(new CustomEvent(`pk-option-select`,{detail:{value:this.value},bubbles:!0,composed:!0}))}handleMouseEnter(){this.disabled||this.hidden||this.dispatchEvent(new CustomEvent(`pk-option-highlight`,{detail:{value:this.value},bubbles:!0,composed:!0}))}handleKeyDown(e){if(!new Set([`ArrowDown`,`ArrowUp`,`ArrowLeft`,`ArrowRight`,`Home`,`End`,`Enter`,` `,`Escape`]).has(e.key))return;let t=this.closest(`pk-select, pk-combobox`),n=t?null:this.closest(`[role="listbox"]`);if(!t&&!n)return;e.preventDefault(),e.stopPropagation();let r=new CustomEvent(`pk-listbox-keydown`,{detail:{keyboardEvent:e},bubbles:!0});if(t){t.dispatchEvent(r);return}n.dispatchEvent(r)}renderLabel(){let e=this.matchQuery.trim();return!e||this.hasRichLabelContent()?r`
                <span part="label" class="label">
                    <slot></slot>
                </span>
            `:r`
            <span part="label" class="label">
                ${_(this.getLabel(),e).map(e=>e.match?r`<mark class="match">${e.text}</mark>`:r`<span>${e.text}</span>`)}
            </span>
        `}render(){return r`
            <button
                part="option"
                type="button"
                class="option"
                role="option"
                id=${this.optionId||o}
                ?disabled=${this.disabled}
                aria-disabled=${this.disabled?`true`:o}
                aria-selected=${this.selected?`true`:`false`}
                tabindex=${this.focusIndex}
                @click=${this.handleClick}
                @mouseenter=${this.handleMouseEnter}
                @keydown=${this.handleKeyDown}
            >
                <span part="start" class="start">
                    <slot name="start"></slot>
                </span>
                ${this.renderLabel()}
                <span part="check" class="check" aria-hidden="true">${h(y)}</span>
            </button>
        `}};s([a()],b.prototype,`value`,void 0),s([a()],b.prototype,`label`,void 0),s([a({type:Boolean,reflect:!0})],b.prototype,`disabled`,void 0),s([a({type:Boolean,reflect:!0})],b.prototype,`selected`,void 0),s([a({type:Boolean,reflect:!0})],b.prototype,`highlighted`,void 0),s([a({type:Boolean,reflect:!0})],b.prototype,`hidden`,void 0),s([a({type:Number,attribute:`focus-index`})],b.prototype,`focusIndex`,void 0),s([a()],b.prototype,`optionId`,void 0),s([a({attribute:!1})],b.prototype,`matchQuery`,void 0),b=s([m(`pk-option`)],b);var x=d`
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
`,S=class extends n{constructor(...e){super(...e),this.label=``,this.hidden=!1,this.labelId=f(`pk-option-group-label`)}static{this.styles=x}connectedCallback(){super.connectedCallback(),this.setAttribute(`role`,`group`),this.setAttribute(`aria-labelledby`,this.labelId)}render(){return r`
            <div part="label" class="label" id=${this.labelId}>${this.label}</div>
            <div role="presentation">
                <slot></slot>
            </div>
        `}};s([a({reflect:!0})],S.prototype,`label`,void 0),s([a({type:Boolean,reflect:!0})],S.prototype,`hidden`,void 0),S=s([m(`pk-option-group`)],S);var C=e(i(),1),w=c(),T=l({tagName:`pk-select`,elementClass:g,react:C.default,events:{onPkChange:`pk-change`,onPkClear:`pk-clear`,onInput:`input`,onChange:`change`,onFocusOut:`focusout`,onPkShow:`pk-show`,onPkAfterShow:`pk-after-show`,onPkHide:`pk-hide`,onPkAfterHide:`pk-after-hide`,onPkOpenChange:`pk-open-change`}}),E=l({tagName:`pk-option`,elementClass:b,react:C.default,events:{onPkOptionSelect:`pk-option-select`,onPkOptionHighlight:`pk-option-highlight`}}),D=l({tagName:`pk-option-group`,elementClass:S,react:C.default}),O=(0,C.forwardRef)(function(e,n){let{disabled:r,invalid:i,clearable:a,multiple:o,open:s,...c}=e;return(0,w.jsx)(T,{ref:n,...c,...t([`disabled`,`invalid`,`clearable`,`multiple`,`open`],{disabled:r,invalid:i,clearable:a,multiple:o,open:s})})});O.displayName=`Select`;var k=(0,C.forwardRef)(function(e,n){let{disabled:r,selected:i,highlighted:a,hidden:o,...s}=e;return(0,w.jsx)(E,{ref:n,...s,...t([`disabled`,`selected`,`highlighted`,`hidden`],{disabled:r,selected:i,highlighted:a,hidden:o})})});k.displayName=`Option`;var A=D;export{A as n,O as r,k as t};
//# sourceMappingURL=Select-BBLZtoIA.js.map