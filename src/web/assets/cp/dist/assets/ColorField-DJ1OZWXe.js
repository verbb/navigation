import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{B as t,G as n,H as r,L as i,R as a,U as o,V as s,Y as c,Z as l,l as u,q as d,t as f,u as p,z as m}from"./Field-C0nNpaS-.js";import{t as h}from"./pk-form-associated-element-DmZKgNPL-BMF0IqiY.js";import{t as g}from"./required-validator-CEg8dvjS-DznAeO8J.js";import{t as _}from"./mirror-validator-DCjNYrrx-BuTMKpbu.js";import{t as v}from"./useEngineField-BN1Z4-dt.js";var y=l`
    @layer pk-component {
        :host {
            display: inline-block;
            position: relative;
            /* Former lg min-width — default now matches input default chrome. */
            min-width: 6.75rem;
            font-family: var(--pk-font-family);
            vertical-align: middle;
        }

        :host([size='xs']) {
            min-width: 5.5rem;
        }

        :host([size='sm']) {
            min-width: 6.125rem;
        }

        :host([size='lg']),
        :host([size='xl']) {
            min-width: 7.375rem;
        }

        :host([fit-cell]) {
            display: block;
            width: 100%;
            min-width: 0;
            max-width: 100%;
            height: 100%;
        }

        :host([fit-cell]) .root {
            display: block;
            width: 100%;
            height: 100%;
        }

        .root {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .swatch {
            position: absolute;
            top: 50%;
            left: 0.5rem;
            z-index: 2;
            width: 1.25rem;
            height: 1.25rem;
            transform: translateY(-50%);
            border-radius: var(--pk-radius-sm);
        }

        :host([size='xs']) .swatch {
            left: 0.375rem;
            width: 1rem;
            height: 1rem;
        }

        :host([size='sm']) .swatch {
            left: 0.375rem;
            width: 1.25rem;
            height: 1.25rem;
        }

        :host([size='lg']) .swatch,
        :host([size='xl']) .swatch {
            left: 0.5rem;
            width: 1.5rem;
            height: 1.5rem;
        }

        :host([fit-cell]) .swatch {
            left: 0.5rem;
            width: 1rem;
            height: 1rem;
        }

        .swatch-preview {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            box-shadow: inset 0 0 0 1px rgb(0 0 0 / 0.15);
        }

        .swatch-preview.is-transparent {
            background-color: #fff;
            background-image:
                linear-gradient(45deg, #d1d5db 25%, transparent 25%),
                linear-gradient(-45deg, #d1d5db 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #d1d5db 75%),
                linear-gradient(-45deg, transparent 75%, #d1d5db 75%);
            background-size: 8px 8px;
            background-position: 0 0, 0 4px, 4px -4px, -4px 0;
        }

        .swatch-picker {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            border: 0;
            opacity: 0;
            cursor: pointer;
            appearance: none;
        }

        .swatch-picker:disabled {
            cursor: not-allowed;
        }

        .hash {
            position: absolute;
            top: 50%;
            left: 2.125rem;
            z-index: 1;
            transform: translateY(-50%);
            color: var(--pk-color-gray-300);
            font-family: var(--pk-font-family-mono, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace);
            font-size: var(--pk-font-size-mono, 0.9em);
            line-height: var(--pk-line-height-mono, 1.5);
            pointer-events: none;
            user-select: none;
        }

        :host([size='xs']) .hash {
            left: 1.625rem;
        }

        :host([size='sm']) .hash {
            left: 2rem;
        }

        :host([size='lg']) .hash,
        :host([size='xl']) .hash {
            left: 2.5rem;
        }

        :host([fit-cell]) .hash {
            left: 1.75rem;
        }

        .hex-input {
            display: block;
            width: 100%;
            /* Former lg — matches pk-input default chrome (~34px). */
            height: 2.125rem;
            margin: 0;
            padding-inline: 3rem 0.75rem;
            border: var(--pk-input-border);
            border-radius: var(--pk-input-border-radius);
            background: var(--pk-input-bg);
            color: var(--pk-color-gray-700);
            font-family: var(--pk-font-family-mono, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace);
            font-size: var(--pk-font-size-mono, 0.9em);
            line-height: var(--pk-line-height-mono, 1.5);
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.12s ease, box-shadow 0.12s ease;
        }

        :host([size='xs']) .hex-input {
            height: 1.625rem;
            padding-inline: 2.25rem 0.625rem;
        }

        :host([size='sm']) .hex-input {
            height: 1.875rem;
            padding-inline: 2.75rem 0.75rem;
        }

        :host([size='lg']) .hex-input,
        :host([size='xl']) .hex-input {
            height: 2.375rem;
            padding-inline: 3.25rem 0.875rem;
        }

        :host([fit-cell]) .hex-input {
            width: 100%;
            max-width: 100%;
            height: 100%;
            padding-inline: 2.25rem 0.5rem;
            border: 0;
            border-radius: 0;
            background: transparent;
        }

        .hex-input:focus,
        .hex-input:focus-visible {
            border-color: var(--pk-color-sky-600);
            box-shadow: var(--pk-input-focus-shadow);
        }

        :host([invalid]) .hex-input:focus,
        :host([invalid]) .hex-input:focus-visible {
            border-color: var(--pk-color-rose-600);
            box-shadow: var(--pk-input-invalid-focus-shadow);
        }

        :host([fit-cell]:not([invalid])) .hex-input:focus,
        :host([fit-cell]:not([invalid])) .hex-input:focus-visible {
            box-shadow: inset 0 0 0 1px var(--pk-color-gray-200);
        }

        :host([fit-cell][invalid]) .hex-input,
        :host([fit-cell][invalid]) .hex-input:focus,
        :host([fit-cell][invalid]) .hex-input:focus-visible {
            box-shadow: inset 0 0 0 1px var(--pk-color-rose-600);
        }

        .hex-input:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        :host([invalid]) .hex-input {
            border-color: var(--pk-color-rose-600);
        }

        :host([disabled]) .swatch {
            opacity: 0.5;
        }
    }
`,b=`#000000`;function x(e){return String(e||``).replace(/^#/,``).replace(/[^0-9a-fA-F]/g,``).slice(0,6).toLowerCase()}function S(e){return e.length===3||e.length===6}function C(e){return e.length===3?e.split(``).map(e=>`${e}${e}`).join(``):e}function w(e){return e.length===6?`#${e}`:e.length===3?`#${C(e)}`:b}var T=class extends h{constructor(...e){super(...e),this.assumeInteractionOn=[`blur`,`input`],this.size=`default`,this.fitCell=!1,this.readonly=!1,this.invalid=!1,this.value=``,this.defaultValue=``,this.ariaLabel=null,this.hexValue=``}static{this.styles=y}static get validators(){return[...super.validators,_(),g()]}connectedCallback(){super.connectedCallback(),this.syncHexFromValue()}willUpdate(e){e.has(`value`)&&this.syncHexFromValue(),super.willUpdate(e)}syncHexFromValue(){this.hexValue=x(this.value)}get validationTarget(){return this.input}syncFormValue(){let e=this.hexValue?`#${this.hexValue}`:``;this.setFormValue(e,e)}resetToDefaultValue(){this.value=this.defaultValue,this.hexValue=x(this.defaultValue)}restoreFormState(e){typeof e==`string`&&(this.value=e,this.hexValue=x(e))}emitChange(){let e=this.hexValue?`#${this.hexValue}`:``;this.value=e,this.dispatchEvent(new CustomEvent(`pk-change`,{detail:{value:e},bubbles:!0,composed:!0})),this.dispatchEvent(new Event(`input`,{bubbles:!0,composed:!0})),this.dispatchEvent(new Event(`change`,{bubbles:!0,composed:!0}))}handleHexInput(e){if(this.disabled||this.readonly)return;let t=x(e.target.value);this.hexValue=t,this.emitChange()}handlePickerChange(e){if(this.disabled||this.readonly)return;let t=x(e.target.value);this.hexValue=t,this.emitChange()}render(){let e=w(this.hexValue),t=!S(this.hexValue);return c`
            <div class="root">
                <div part="swatch" class="swatch">
                    <div
                        class=${u({"swatch-preview":!0,"is-transparent":t})}
                        style=${t?d:`background-color: ${e}`}
                    ></div>
                    <input
                        part="picker"
                        class="swatch-picker"
                        type="color"
                        .value=${e}
                        ?disabled=${this.disabled||this.readonly}
                        aria-label="Color picker"
                        @input=${this.handlePickerChange}
                    />
                </div>
                <span class="hash" aria-hidden="true">#</span>
                <input
                    part="input"
                    class="hex-input"
                    type="text"
                    inputmode="text"
                    autocomplete="off"
                    maxlength="6"
                    .value=${this.hexValue}
                    ?disabled=${this.disabled}
                    ?readonly=${this.readonly}
                    ?required=${this.required}
                    aria-label=${this.ariaLabel??d}
                    aria-invalid=${this.invalid?`true`:d}
                    @input=${this.handleHexInput}
                />
            </div>
        `}};n([r({reflect:!0})],T.prototype,`size`,void 0),n([r({type:Boolean,reflect:!0,attribute:`fit-cell`})],T.prototype,`fitCell`,void 0),n([r({type:Boolean,reflect:!0})],T.prototype,`readonly`,void 0),n([r({type:Boolean,reflect:!0})],T.prototype,`invalid`,void 0),n([r()],T.prototype,`value`,void 0),n([r({attribute:`default-value`})],T.prototype,`defaultValue`,void 0),n([r({attribute:`aria-label`})],T.prototype,`ariaLabel`,void 0),n([t(`.hex-input`)],T.prototype,`input`,void 0),n([s()],T.prototype,`hexValue`,void 0),T=n([o(`pk-color-input`)],T);var E=e(a(),1),D=m(),O=i({tagName:`pk-color-input`,elementClass:T,react:E.default,events:{onPkChange:`pk-change`,onInput:`input`,onNativeChange:`change`,onBlur:`blur`}}),k=(0,E.forwardRef)(function({disabled:e,readonly:t,readOnly:n,invalid:r,isInvalid:i,onChange:a,onValueChange:o,onPkChange:s,...c},l){let u=!!(r??i),d=!!(t??n),f=a??o,m=(0,E.useCallback)(e=>{if(s?.(e),!f)return;let t=e.detail;t&&`value`in t&&f(t.value??``)},[s,f]);return(0,D.jsx)(O,{ref:l,...c,...p([`disabled`,`readonly`,`invalid`],{disabled:e,readonly:d,invalid:u}),...f||s?{onPkChange:m}:{}})});k.displayName=`ColorInput`;var A=({form:e,field:t})=>{let{value:n,setValue:r,setTouched:i,errors:a,isInvalid:o}=v(e,t.name);return(0,D.jsx)(f,{name:t.name,label:t.label,instructions:t.instructions,warning:t.warning,required:t.required,errors:a,children:(0,D.jsx)(k,{value:String(n||``),onPkChange:e=>{r(e.detail?.value??``),i()},disabled:t.disabled,invalid:o})})};export{A as ColorField};
//# sourceMappingURL=ColorField-DJ1OZWXe.js.map