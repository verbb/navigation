import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{B as t,G as n,H as r,L as i,R as a,U as o,V as s,Y as c,Z as l,c as u,i as d,q as f,r as p,t as m,u as h,z as g}from"./Field-C0nNpaS-.js";import{n as _,t as v}from"./pk-form-associated-element-DmZKgNPL-BMF0IqiY.js";import{t as y}from"./required-validator-CEg8dvjS-DznAeO8J.js";import{t as b}from"./mirror-validator-DCjNYrrx-BuTMKpbu.js";import{n as x,t as S}from"./field-labels-CoU9dnNq-qJ92s-2-.js";import{t as C}from"./useEngineField-BN1Z4-dt.js";import{n as w,t as T}from"./live-CihwgG8-.js";var E=l`
    @layer pk-component {
        :host {
            display: block;
            width: 100%;
            font-family: var(--pk-font-family);
            font-size: var(--pk-font-size-base);
            line-height: var(--pk-line-height);
        }

        .textarea {
            display: block;
            width: 100%;
            min-height: 5rem;
            margin: 0;
            padding: 7px 10px;
            border: var(--pk-input-border);
            border-radius: var(--pk-textarea-border-radius, var(--pk-radius-md));
            background: var(--pk-input-bg);
            background-clip: padding-box;
            /* Craft CP body / field value text. */
            color: var(--pk-color-gray-700);
            font: inherit;
            line-height: 1.4;
            resize: vertical;
            appearance: none;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.12s ease, box-shadow 0.12s ease;
        }

        .textarea::placeholder {
            color: var(--pk-input-placeholder-color, var(--pk-color-gray-400));
        }

        /* Craft: focus is box-shadow only — do not also flip border-color (double ring). */
        :host(:not([invalid]):not(:state(user-invalid))) .textarea:focus,
        :host(:not([invalid]):not(:state(user-invalid))) .textarea:focus-visible,
        :host([data-state='focus-visible']:not([invalid]):not(:state(user-invalid))) .textarea {
            box-shadow: var(--pk-input-focus-shadow);
        }

        .textarea:disabled {
            cursor: not-allowed;
            opacity: 0.5;
            background: var(--pk-color-gray-50);
        }

        :host([invalid]) .textarea,
        :host(:state(user-invalid)) .textarea {
            border-color: var(--pk-color-rose-600);
        }

        :host([invalid]) .textarea:focus,
        :host([invalid]) .textarea:focus-visible,
        :host([invalid][data-state='focus-visible']) .textarea,
        :host(:state(user-invalid)) .textarea:focus,
        :host(:state(user-invalid)) .textarea:focus-visible {
            box-shadow: var(--pk-input-invalid-focus-shadow);
        }

        /* Editable-table cells (v1): flush into the row and fill cell height.
         * Chain height through form-control — percentage on .textarea alone
         * doesn't resolve when the wrapper sizes to content (rows / min-height). */
        :host([fit-cell]),
        :host([data-editable-table-input]) {
            display: block;
            height: 100%;
            min-height: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }

        :host([fit-cell]) .form-control,
        :host([data-editable-table-input]) .form-control {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 100%;
        }

        :host([fit-cell]) .textarea,
        :host([data-editable-table-input]) .textarea {
            flex: 1 1 auto;
            border: none;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            height: 100%;
            min-height: 0;
            max-height: 100%;
            /* Match text-cell inset (v1 py-1.5 / px-2). 0.5rem block padding +
             * line-height 1.4 overflows the 34px et cell and shows a scrollbar
             * even for empty / single-line notes. */
            padding: 0.375rem 0.5rem;
            line-height: 1.25;
            overflow-x: hidden;
            overflow-y: auto;
            resize: none;
        }

        :host([fit-cell]:not([invalid]):not(:state(user-invalid))) .textarea:focus,
        :host([fit-cell]:not([invalid]):not(:state(user-invalid))) .textarea:focus-visible,
        :host([fit-cell][data-state='focus-visible']:not([invalid]):not(:state(user-invalid))) .textarea,
        :host([data-editable-table-input]:not([invalid]):not(:state(user-invalid))) .textarea:focus,
        :host([data-editable-table-input]:not([invalid]):not(:state(user-invalid))) .textarea:focus-visible,
        :host([data-editable-table-input][data-state='focus-visible']:not([invalid]):not(:state(user-invalid))) .textarea {
            border: none;
            box-shadow: inset 0 0 0 1px var(--pk-color-gray-200);
        }

        :host([fit-cell][invalid]) .textarea,
        :host([fit-cell]:state(user-invalid)) .textarea,
        :host([fit-cell][invalid]) .textarea:focus,
        :host([fit-cell][invalid]) .textarea:focus-visible,
        :host([fit-cell]:state(user-invalid)) .textarea:focus,
        :host([fit-cell]:state(user-invalid)) .textarea:focus-visible,
        :host([data-editable-table-input][invalid]) .textarea,
        :host([data-editable-table-input]:state(user-invalid)) .textarea,
        :host([data-editable-table-input][invalid]) .textarea:focus,
        :host([data-editable-table-input][invalid]) .textarea:focus-visible,
        :host([data-editable-table-input]:state(user-invalid)) .textarea:focus,
        :host([data-editable-table-input]:state(user-invalid)) .textarea:focus-visible {
            border: none;
            box-shadow: inset 0 0 0 1px var(--pk-color-rose-600);
        }
    }
`,D=class extends v{constructor(...e){super(...e),this.assumeInteractionOn=[`blur`,`input`],this.hasSlotController=new u(this,`instructions`,`hint`,`label`),this.controlId=p(`pk-textarea`),this.placeholder=``,this._value=null,this.defaultValue=null,this.size=`default`,this.label=``,this.instructions=``,this.readonly=!1,this.invalid=!1,this.fitCell=!1,this.withLabel=!1,this.withInstructions=!1}static{this.styles=[d,E]}static get validators(){return[...super.validators,b(),y()]}get value(){return this.valueHasChanged?this._value??``:this._value??this.defaultValue??``}set value(e){let t=e??``;this._value!==t&&(this.valueHasChanged=!0,this._value=t)}connectedCallback(){this.instructions=x(this,this.instructions),this.hasAttribute(`with-hint`)&&(this.withInstructions=!0),super.connectedCallback()}syncFormValue(){this.setValue(this.value||``)}resetToDefaultValue(){this.valueHasChanged=!1,this._value=null}restoreFormState(e){typeof e==`string`&&(this.value=e)}formResetCallback(){this.valueHasChanged=!1,this._value=null,this.input&&(this.input.value=this.defaultValue??``),super.formResetCallback()}updated(e){(e.has(`value`)||e.has(`defaultValue`))&&this.setState(`blank`,!this.value),super.updated(e)}syncStandaloneAria(){if(!this.input)return;let e=this.hasLabelContent(),t=this.hasInstructionsContent();_({control:this.input,labelId:`${this.controlId}-label`,instructionsId:`${this.controlId}-instructions`,hasLabel:e,hasInstructions:t,required:this.required,invalid:this.invalid||!this.internals.validity.valid})}hasLabelContent(){return!!this.label||this.hasSlotController.test(`label`,this.withLabel)}hasInstructionsContent(){return S((e,t)=>this.hasSlotController.test(e,t),this.instructions,this.withInstructions)}focus(e){this.input?.focus(e)}blur(){this.input?.blur()}handleInput(){this.value=this.input.value,this.dispatchEvent(new Event(`input`,{bubbles:!0,composed:!0}))}handleChange(e){this.value=this.input.value,e.stopPropagation(),this.dispatchEvent(new Event(`change`,{bubbles:!0,composed:!0}))}render(){let e=this.hasLabelContent(),t=this.hasInstructionsContent();return c`
            <div part="form-control" class="form-control">
                ${e?c`
                        <label
                            part="label"
                            class="form-control__label"
                            id=${`${this.controlId}-label`}
                            for=${`${this.controlId}-control`}
                        >
                            <slot name="label">${this.label}</slot>
                        </label>
                    `:f}

                ${t?c`
                        <p
                            part="instructions"
                            class="form-control__instructions"
                            id=${`${this.controlId}-instructions`}
                        >
                            <slot name="instructions">${this.instructions}</slot>
                            <slot name="hint"></slot>
                        </p>
                    `:f}

                <textarea
                    part="textarea"
                    class="textarea"
                    id=${e?`${this.controlId}-control`:f}
                    rows=${w(this.fitCell?this.rows??1:this.rows)}
                    .value=${T(this.value)}
                    placeholder=${this.placeholder||f}
                    maxlength=${w(this.maxlength)}
                    ?disabled=${this.disabled}
                    ?readonly=${this.readonly}
                    ?required=${this.required}
                    @input=${this.handleInput}
                    @change=${this.handleChange}
                    @focus=${()=>this.dispatchEvent(new Event(`focus`,{bubbles:!0,composed:!0}))}
                    @blur=${()=>this.dispatchEvent(new Event(`blur`,{bubbles:!0,composed:!0}))}
                ></textarea>
            </div>
        `}};n([t(`textarea`)],D.prototype,`input`,void 0),n([r()],D.prototype,`placeholder`,void 0),n([s()],D.prototype,`value`,null),n([r({attribute:`value`,reflect:!0})],D.prototype,`defaultValue`,void 0),n([r({reflect:!0})],D.prototype,`size`,void 0),n([r()],D.prototype,`label`,void 0),n([r()],D.prototype,`instructions`,void 0),n([r({type:Boolean,reflect:!0})],D.prototype,`readonly`,void 0),n([r({type:Boolean,reflect:!0})],D.prototype,`invalid`,void 0),n([r({type:Boolean,reflect:!0,attribute:`fit-cell`})],D.prototype,`fitCell`,void 0),n([r({type:Number})],D.prototype,`rows`,void 0),n([r({type:Number,attribute:`max-length`})],D.prototype,`maxlength`,void 0),n([r({attribute:`with-label`,type:Boolean})],D.prototype,`withLabel`,void 0),n([r({attribute:`with-instructions`,type:Boolean})],D.prototype,`withInstructions`,void 0),D=n([o(`pk-textarea`)],D);var O=e(a(),1),k=g(),A=i({tagName:`pk-textarea`,elementClass:D,react:O.default,events:{onInput:`input`,onChange:`input`,onFocus:`focus`,onBlur:`blur`}}),j=(0,O.forwardRef)(function(e,t){let{disabled:n,readonly:r,invalid:i,fitCell:a,...o}=e;return(0,k.jsx)(A,{ref:t,...o,...h([`disabled`,`readonly`,`invalid`,`fitCell`],{disabled:n,readonly:r,invalid:i,fitCell:a})})});j.displayName=`Textarea`;var M=({form:e,field:t})=>{let{value:n,setValue:r,setTouched:i,errors:a,isInvalid:o}=C(e,t.name);return(0,k.jsx)(m,{name:t.name,label:t.label,instructions:t.instructions,warning:t.warning,required:t.required,translatable:t.translatable,errors:a,children:(0,k.jsx)(j,{value:String(n??``),onInput:e=>r(e.target.value??``),onBlur:i,placeholder:t.placeholder,disabled:t.disabled,invalid:o})})};export{M as TextareaField};
//# sourceMappingURL=TextareaField-C1Fr1bu6.js.map