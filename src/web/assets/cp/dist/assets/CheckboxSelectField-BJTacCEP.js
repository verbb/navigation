import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{G as t,H as n,L as r,R as i,U as a,V as o,W as s,Y as c,Z as l,l as u,t as d,u as f,z as p}from"./Field-DTPx4asS.js";import"./pk-checkbox-Bavq3-9C-BWy5YEeJ.js";import{t as m}from"./useEngineField-zgqC2O22.js";var h=p(),g=e(i(),1),_=l`
    @layer pk-component {
        :host {
            display: block;
            font-family: var(--pk-font-family);
            font-size: var(--pk-font-size-sm);
            line-height: var(--pk-line-height);
        }

        :host([disabled]) {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: var(--pk-checkbox-select-gap, 0);
        }

        .options--horizontal {
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--pk-checkbox-select-gap, 0);
        }

        ::slotted(pk-checkbox),
        pk-checkbox {
            display: block;
        }

        .options--horizontal pk-checkbox.all-option {
            width: 100%;
        }
    }
`,v={fromAttribute(e){if(!e)return[];try{let t=JSON.parse(e);return Array.isArray(t)?t.filter(e=>!!(e&&typeof e==`object`&&`value`in e)).map(e=>({label:String(e.label??e.value),value:String(e.value)})):[]}catch{return[]}},toAttribute(e){return JSON.stringify(e??[])}},y={fromAttribute(e){if(e==null||e===``)return[];if(e===`*`)return`*`;try{let t=JSON.parse(e);return t===`*`?`*`:Array.isArray(t)?t.map(String):[]}catch{return[]}},toAttribute(e){return e===`*`?`*`:JSON.stringify(e??[])}},b=class extends s{constructor(...e){super(...e),this.options=[],this.value=[],this.showAllOption=!1,this.allLabel=`All`,this.disabled=!1,this.orientation=`vertical`,this.ariaLabel=null,this.optionElements=[],this.allOptionElement=null,this.handleAllChange=e=>{e.stopPropagation(),this.value=e.detail.checked?`*`:[],this.dispatchValueChange()},this.handleItemChange=(e,t)=>{if(t.stopPropagation(),this.isAllSelected)return;let n=t.detail.checked,r=this.selectedValues;this.value=n?[...r,e]:r.filter(t=>t!==e),this.dispatchValueChange()}}static{this.styles=_}connectedCallback(){this.hasAttribute(`role`)||this.setAttribute(`role`,`group`),super.connectedCallback()}updated(e){if(e.has(`options`)||e.has(`showAllOption`)){this.rebuildOptionElements();return}(e.has(`value`)||e.has(`disabled`))&&this.updateOptionStates()}firstUpdated(){this.rebuildOptionElements()}focus(e){this.optionElements.find(e=>!e.disabled)?.focus(e)}get isAllSelected(){return this.value===`*`}get selectedValues(){return this.isAllSelected?this.options.map(e=>e.value):Array.isArray(this.value)?this.value:[]}dispatchValueChange(){let e=this.isAllSelected?`*`:[...this.selectedValues];this.dispatchEvent(new CustomEvent(`pk-change`,{detail:{value:e},bubbles:!0,composed:!0})),this.dispatchEvent(new Event(`change`,{bubbles:!0,composed:!0}))}rebuildOptionElements(){let e=this.shadowRoot?.querySelector(`.options`);if(e){for(let e of this.optionElements)e.remove();if(this.optionElements=[],this.allOptionElement=null,this.showAllOption){let t=document.createElement(`pk-checkbox`);t.classList.add(`all-option`),t.append(this.allLabel),t.addEventListener(`pk-change`,this.handleAllChange),e.append(t),this.allOptionElement=t,this.optionElements.push(t)}for(let t of this.options){let n=document.createElement(`pk-checkbox`);n.checkboxValue=t.value,n.append(t.label),n.addEventListener(`pk-change`,e=>{this.handleItemChange(t.value,e)}),e.append(n),this.optionElements.push(n)}this.updateOptionStates()}}updateOptionStates(){this.allOptionElement&&(this.allOptionElement.checked=this.isAllSelected,this.allOptionElement.disabled=this.disabled);for(let e of this.options){let t=this.optionElements.find(t=>t!==this.allOptionElement&&t.checkboxValue===e.value);t&&(t.checked=this.isAllSelected||this.selectedValues.includes(e.value),t.disabled=this.disabled||this.isAllSelected)}}render(){return c`
            <div
                part="base"
                class=${u({options:!0,"options--horizontal":this.orientation===`horizontal`})}
            ></div>
        `}};t([n({attribute:`options`,converter:v})],b.prototype,`options`,void 0),t([n({attribute:`value`,converter:y})],b.prototype,`value`,void 0),t([n({type:Boolean,attribute:`show-all-option`})],b.prototype,`showAllOption`,void 0),t([n({attribute:`all-label`})],b.prototype,`allLabel`,void 0),t([n({type:Boolean,reflect:!0})],b.prototype,`disabled`,void 0),t([n({reflect:!0})],b.prototype,`orientation`,void 0),t([n({attribute:`aria-label`})],b.prototype,`ariaLabel`,void 0),t([o()],b.prototype,`optionElements`,void 0),b=t([a(`pk-checkbox-select`)],b);var x=r({tagName:`pk-checkbox-select`,elementClass:b,react:g.default,events:{onPkChange:`pk-change`,onNativeChange:`change`}}),S=(0,g.forwardRef)(function({disabled:e,onChange:t,onPkChange:n,...r},i){let a=(0,g.useCallback)(e=>{if(n?.(e),!t)return;let r=e.detail;r&&`value`in r&&t(r.value)},[t,n]);return(0,h.jsx)(x,{ref:i,...r,...f([`disabled`],{disabled:e}),...t||n?{onPkChange:a}:{}})});S.displayName=`CheckboxSelect`;var C=({form:e,field:t})=>{let{value:n,setValue:r,errors:i}=m(e,t.name);return(0,h.jsx)(d,{name:t.name,label:t.label,instructions:t.instructions,warning:t.warning,required:t.required,errors:i,children:(0,h.jsx)(S,{options:t.options||[],value:n??[],onChange:r,showAllOption:t.showAllOption??!1,allLabel:t.allLabel,disabled:t.disabled})})};export{C as CheckboxSelectField};
//# sourceMappingURL=CheckboxSelectField-BJTacCEP.js.map