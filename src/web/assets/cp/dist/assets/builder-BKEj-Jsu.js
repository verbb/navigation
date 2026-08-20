const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./TextField-D1bYIEeD.js","./Field-C0nNpaS-.js","./rolldown-runtime-QTnfLwEv.js","./useEngineField-BN1Z4-dt.js","./Input-CtEeGlxx.js","./button-group-item.styles-DYmBR28a-BiVND-sp.js","./pk-form-associated-element-DmZKgNPL-BMF0IqiY.js","./required-validator-CEg8dvjS-DznAeO8J.js","./mirror-validator-DCjNYrrx-BuTMKpbu.js","./field-labels-CoU9dnNq-qJ92s-2-.js","./pk-clear--mPWZP7H-BHgvcQ98.js","./live-CihwgG8-.js","./TextareaField-C1Fr1bu6.js","./NumberField-DHA092Fi.js","./SelectField-j6mLG-tP.js","./dist-BpymPXnn.js","./SelectInput-UZuLegmy.js","./Select-BT3fQ6WL.js","./pk-select-odSNU1tN-BFCya_2X.js","./LightswitchField-B0zNVH7z.js","./Lightswitch-BbFeYjvT.js","./pk-change-BMLA71i0.js","./ColorField-DJ1OZWXe.js","./RadioGroupField-Ip5mYdA8.js","./CheckboxSelectField-B7fYX5Ye.js","./ComboboxField-DmgdN12v.js","./GroupField-DI5yNLXf.js","./DateTimeField-DgUP9GrO.js","./CodeEditorField-CNF-AR16.js"])))=>i.map(i=>d[i]);
import{r as e,t}from"./rolldown-runtime-QTnfLwEv.js";import{A as n,B as r,C as i,D as a,E as o,G as s,H as c,K as l,L as u,O as d,R as f,S as p,T as m,U as h,V as g,W as _,Y as v,Z as y,_ as b,a as x,b as ee,c as S,d as C,f as w,g as te,h as ne,j as re,k as ie,l as ae,m as oe,p as se,q as T,r as E,s as D,t as O,u as ce,v as le,w as ue,x as de,y as k,z as fe}from"./Field-C0nNpaS-.js";import{C as pe,S as me,_ as he,d as ge,f as _e,g as ve,h as ye,i as be,l as xe,m as Se,p as Ce,u as we,v as Te,w as Ee,x as De,y as Oe}from"./pk-select-odSNU1tN-BFCya_2X.js";import{a as ke,i as Ae,n as je,o as Me,r as Ne,s as Pe,t as Fe}from"./dist-BpymPXnn.js";import{i as Ie,n as Le,r as Re,t as ze}from"./button-group-item.styles-DYmBR28a-BiVND-sp.js";import{t as Be}from"./pk-change-BMLA71i0.js";import{t as Ve}from"./pk-form-associated-element-DmZKgNPL-BMF0IqiY.js";import{t as He}from"./required-validator-CEg8dvjS-DznAeO8J.js";import{n as Ue,t as We}from"./SelectInput-UZuLegmy.js";import{t as Ge}from"./Lightswitch-BbFeYjvT.js";var Ke=[l,y`
        @layer pk-component {
            :host {
                display: block;
                box-sizing: border-box;
            }

            :host([centered]) {
                position: absolute;
                top: 50%;
                left: 50%;
                display: block;
                width: fit-content;
                height: fit-content;
                margin: 0;
                transform: translate(-50%, -50%);
            }

            .spinner {
                display: block;
                box-sizing: border-box;
                margin-inline: auto;
                border-style: solid;
                border-bottom-color: transparent;
                border-left-color: transparent;
                border-radius: 50%;
                animation: pk-spinner-spin 0.5s linear infinite;
            }

            /* Sizes */
            :host([size='xxs']) .spinner {
                width: 0.75rem;
                height: 0.75rem;
                border-width: 1px;
            }

            :host([size='xs']) .spinner {
                width: 1rem;
                height: 1rem;
                border-width: 2px;
            }

            :host([size='sm']) .spinner,
            :host(:not([size])) .spinner {
                width: 1.5rem;
                height: 1.5rem;
                border-width: 2px;
            }

            :host([size='md']) .spinner {
                width: 2rem;
                height: 2rem;
                border-width: 2px;
            }

            :host([size='lg']) .spinner {
                width: 3rem;
                height: 3rem;
                border-width: 2px;
            }

            :host([size='xl']) .spinner {
                width: 4rem;
                height: 4rem;
                border-width: 2px;
            }

            /* Variants — matched to button loading contrast */
            :host([variant='default']:not([tone])) .spinner {
                border-top-color: var(--pk-color-red-500);
                border-right-color: var(--pk-color-red-500);
            }

            :host([variant='primary']:not([tone])) .spinner,
            :host([variant='secondary']:not([tone])) .spinner {
                border-top-color: var(--pk-color-white);
                border-right-color: var(--pk-color-white);
            }

            :host([variant='dashed']:not([tone])) .spinner,
            :host([variant='outline']:not([tone])) .spinner,
            :host([variant='transparent']:not([tone])) .spinner {
                border-top-color: var(--pk-color-gray-700);
                border-right-color: var(--pk-color-gray-700);
            }

            /* Standalone tone overrides */
            :host([tone='sky']) .spinner {
                border-top-color: var(--pk-color-sky-600);
                border-right-color: var(--pk-color-sky-600);
            }

            :host([tone='emerald']) .spinner {
                border-top-color: var(--pk-color-emerald-600);
                border-right-color: var(--pk-color-emerald-600);
            }

            :host([tone='violet']) .spinner {
                border-top-color: var(--pk-color-violet-600);
                border-right-color: var(--pk-color-violet-600);
            }

            :host([tone='amber']) .spinner {
                border-top-color: var(--pk-color-amber-500);
                border-right-color: var(--pk-color-amber-500);
            }

            @keyframes pk-spinner-spin {
                to {
                    transform: rotate(360deg);
                }
            }
        }
    `],qe=class extends _{constructor(...e){super(...e),this.variant=`default`,this.size=`sm`,this.centered=!1}static{this.styles=Ke}render(){return v`
            <div part="base" class="spinner" aria-hidden="true"></div>
        `}};s([c({reflect:!0})],qe.prototype,`variant`,void 0),s([c({reflect:!0})],qe.prototype,`size`,void 0),s([c({reflect:!0})],qe.prototype,`tone`,void 0),s([c({type:Boolean,reflect:!0})],qe.prototype,`centered`,void 0),qe=s([h(`pk-spinner`)],qe);var Je=`/*! tailwindcss v4.3.3 | MIT License | https://tailwindcss.com */
@layer properties{*,:before,:after{--tw-translate-x:0;--tw-translate-y:0;--tw-translate-z:0;--tw-scale-x:1;--tw-scale-y:1;--tw-scale-z:1;--tw-rotate-x:initial;--tw-rotate-y:initial;--tw-rotate-z:initial;--tw-skew-x:initial;--tw-skew-y:initial;--tw-pan-x:initial;--tw-pan-y:initial;--tw-pinch-zoom:initial;--tw-space-y-reverse:0;--tw-space-x-reverse:0;--tw-divide-x-reverse:0;--tw-border-style:solid;--tw-divide-y-reverse:0;--tw-leading:initial;--tw-font-weight:initial;--tw-tracking:initial;--tw-ordinal:initial;--tw-slashed-zero:initial;--tw-numeric-figure:initial;--tw-numeric-spacing:initial;--tw-numeric-fraction:initial;--tw-shadow:0 0 #0000;--tw-shadow-color:initial;--tw-shadow-alpha:100%;--tw-inset-shadow:0 0 #0000;--tw-inset-shadow-color:initial;--tw-inset-shadow-alpha:100%;--tw-ring-color:initial;--tw-ring-shadow:0 0 #0000;--tw-inset-ring-color:initial;--tw-inset-ring-shadow:0 0 #0000;--tw-ring-inset:initial;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-offset-shadow:0 0 #0000;--tw-outline-style:solid;--tw-blur:initial;--tw-brightness:initial;--tw-contrast:initial;--tw-grayscale:initial;--tw-hue-rotate:initial;--tw-invert:initial;--tw-opacity:initial;--tw-saturate:initial;--tw-sepia:initial;--tw-drop-shadow:initial;--tw-drop-shadow-color:initial;--tw-drop-shadow-alpha:100%;--tw-drop-shadow-size:initial;--tw-backdrop-blur:initial;--tw-backdrop-brightness:initial;--tw-backdrop-contrast:initial;--tw-backdrop-grayscale:initial;--tw-backdrop-hue-rotate:initial;--tw-backdrop-invert:initial;--tw-backdrop-opacity:initial;--tw-backdrop-saturate:initial;--tw-backdrop-sepia:initial;--tw-duration:initial;--tw-ease:initial}::backdrop{--tw-translate-x:0;--tw-translate-y:0;--tw-translate-z:0;--tw-scale-x:1;--tw-scale-y:1;--tw-scale-z:1;--tw-rotate-x:initial;--tw-rotate-y:initial;--tw-rotate-z:initial;--tw-skew-x:initial;--tw-skew-y:initial;--tw-pan-x:initial;--tw-pan-y:initial;--tw-pinch-zoom:initial;--tw-space-y-reverse:0;--tw-space-x-reverse:0;--tw-divide-x-reverse:0;--tw-border-style:solid;--tw-divide-y-reverse:0;--tw-leading:initial;--tw-font-weight:initial;--tw-tracking:initial;--tw-ordinal:initial;--tw-slashed-zero:initial;--tw-numeric-figure:initial;--tw-numeric-spacing:initial;--tw-numeric-fraction:initial;--tw-shadow:0 0 #0000;--tw-shadow-color:initial;--tw-shadow-alpha:100%;--tw-inset-shadow:0 0 #0000;--tw-inset-shadow-color:initial;--tw-inset-shadow-alpha:100%;--tw-ring-color:initial;--tw-ring-shadow:0 0 #0000;--tw-inset-ring-color:initial;--tw-inset-ring-shadow:0 0 #0000;--tw-ring-inset:initial;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-offset-shadow:0 0 #0000;--tw-outline-style:solid;--tw-blur:initial;--tw-brightness:initial;--tw-contrast:initial;--tw-grayscale:initial;--tw-hue-rotate:initial;--tw-invert:initial;--tw-opacity:initial;--tw-saturate:initial;--tw-sepia:initial;--tw-drop-shadow:initial;--tw-drop-shadow-color:initial;--tw-drop-shadow-alpha:100%;--tw-drop-shadow-size:initial;--tw-backdrop-blur:initial;--tw-backdrop-brightness:initial;--tw-backdrop-contrast:initial;--tw-backdrop-grayscale:initial;--tw-backdrop-hue-rotate:initial;--tw-backdrop-invert:initial;--tw-backdrop-opacity:initial;--tw-backdrop-saturate:initial;--tw-backdrop-sepia:initial;--tw-duration:initial;--tw-ease:initial}}@layer dnd-kit;@layer base{*,:after,:before{box-sizing:border-box;border:0 solid;margin:0;padding:0}::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}::file-selector-button{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;tab-size:4;line-height:1.5;font-family:var(--default-font-family,-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");font-feature-settings:var(--default-font-feature-settings,normal);font-variation-settings:var(--default-font-variation-settings,normal);-webkit-tap-highlight-color:transparent}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;-webkit-text-decoration:inherit;-webkit-text-decoration:inherit;-webkit-text-decoration:inherit;-webkit-text-decoration:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:var(--default-mono-font-family,ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace);font-feature-settings:var(--default-mono-font-feature-settings,normal);font-variation-settings:var(--default-mono-font-variation-settings,normal);font-size:1em}small{font-size:80%}sub,sup{vertical-align:baseline;font-size:75%;line-height:0;position:relative}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}:-moz-focusring:where(:not(iframe)){outline:auto}progress{vertical-align:baseline}summary{display:list-item}ol,ul,menu{list-style:none}img,svg,video,canvas,audio,iframe,embed,object{vertical-align:middle;display:block}img,video{max-width:100%;height:auto}button,input,select,optgroup,textarea{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::file-selector-button{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}:where(select:is([multiple],[size])) optgroup{font-weight:bolder}:where(select:is([multiple],[size])) optgroup option{padding-inline-start:20px}::file-selector-button{margin-inline-end:4px}::placeholder{opacity:1}@supports (not ((-webkit-appearance:-apple-pay-button))) or (contain-intrinsic-size:1px){::placeholder{color:currentColor}@supports (color:color-mix(in lab, red, red)){::placeholder{color:color-mix(in oklab, currentcolor 50%, transparent)}}}textarea{resize:vertical}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-date-and-time-value{min-height:1lh;text-align:inherit}::-webkit-datetime-edit{display:inline-flex}::-webkit-datetime-edit-fields-wrapper{padding:0}::-webkit-datetime-edit{padding-block:0}::-webkit-datetime-edit-year-field{padding-block:0}::-webkit-datetime-edit-month-field{padding-block:0}::-webkit-datetime-edit-day-field{padding-block:0}::-webkit-datetime-edit-hour-field{padding-block:0}::-webkit-datetime-edit-minute-field{padding-block:0}::-webkit-datetime-edit-second-field{padding-block:0}::-webkit-datetime-edit-millisecond-field{padding-block:0}::-webkit-datetime-edit-meridiem-field{padding-block:0}::-webkit-calendar-picker-indicator{line-height:1}:-moz-ui-invalid{box-shadow:none}button,input:where([type=button],[type=reset],[type=submit]){appearance:button}::file-selector-button{appearance:button}::-webkit-inner-spin-button{height:auto}::-webkit-outer-spin-button{height:auto}[hidden]:where(:not([hidden=until-found])){display:none!important}}@layer components;@layer utilities{.\\@container{container-type:inline-size}.pointer-events-none{pointer-events:none}.collapse{visibility:collapse}.invisible{visibility:hidden}.visible{visibility:visible}.sr-only{clip-path:inset(50%);white-space:nowrap;border-width:0;width:1px;height:1px;margin:-1px;padding:0;position:absolute;overflow:hidden}.not-sr-only{clip-path:none;white-space:normal;width:auto;height:auto;margin:0;padding:0;position:static;overflow:visible}.absolute{position:absolute}.fixed{position:fixed}.relative{position:relative}.static{position:static}.sticky{position:sticky}.-inset-1{inset:calc(var(--spacing) * -1)}.inset-0{inset:0}.top-1{top:var(--spacing)}.top-1\\/2{top:50%}.right-0{right:0}.right-1{right:var(--spacing)}.left-1{left:var(--spacing)}.left-1\\/2{left:50%}.isolate{isolation:isolate}.isolation-auto{isolation:auto}.z-10{z-index:10}.z-11{z-index:11}.z-20{z-index:20}.z-\\[1\\]{z-index:1}.\\!container{width:100%!important}@media (min-width:40rem){.\\!container{max-width:40rem!important}}@media (min-width:48rem){.\\!container{max-width:48rem!important}}@media (min-width:64rem){.\\!container{max-width:64rem!important}}@media (min-width:80rem){.\\!container{max-width:80rem!important}}@media (min-width:96rem){.\\!container{max-width:96rem!important}}.container{width:100%}@media (min-width:40rem){.container{max-width:40rem}}@media (min-width:48rem){.container{max-width:48rem}}@media (min-width:64rem){.container{max-width:64rem}}@media (min-width:80rem){.container{max-width:80rem}}@media (min-width:96rem){.container{max-width:96rem}}.m-0{margin:0}.-mx-6{margin-inline:calc(var(--spacing) * -6)}.mt-1{margin-top:var(--spacing)}.mt-2{margin-top:calc(var(--spacing) * 2)}.mt-4{margin-top:calc(var(--spacing) * 4)}.-mr-1{margin-right:calc(var(--spacing) * -1)}.-mb-2{margin-bottom:calc(var(--spacing) * -2)}.-mb-2\\.5{margin-bottom:calc(var(--spacing) * -2.5)}.mb-2{margin-bottom:calc(var(--spacing) * 2)}.mb-3{margin-bottom:calc(var(--spacing) * 3)}.mb-4{margin-bottom:calc(var(--spacing) * 4)}.ml-1{margin-left:var(--spacing)}.ml-2{margin-left:calc(var(--spacing) * 2)}.ml-\\[7px\\]{margin-left:7px}.\\!block{display:block!important}.block{display:block}.contents{display:contents}.flex{display:flex}.flow-root{display:flow-root}.grid{display:grid}.hidden{display:none}.hidden\\!{display:none!important}.inline{display:inline}.inline-block{display:inline-block}.inline-flex{display:inline-flex}.inline-grid{display:inline-grid}.inline-table{display:inline-table}.list-item{display:list-item}.table{display:table}.table-caption{display:table-caption}.table-cell{display:table-cell}.table-column{display:table-column}.table-column-group{display:table-column-group}.table-footer-group{display:table-footer-group}.table-header-group{display:table-header-group}.table-row{display:table-row}.table-row-group{display:table-row-group}.size-2{width:calc(var(--spacing) * 2);height:calc(var(--spacing) * 2)}.size-2\\.5{width:calc(var(--spacing) * 2.5);height:calc(var(--spacing) * 2.5)}.size-3{width:calc(var(--spacing) * 3);height:calc(var(--spacing) * 3)}.size-3\\.5{width:calc(var(--spacing) * 3.5);height:calc(var(--spacing) * 3.5)}.size-4{width:calc(var(--spacing) * 4);height:calc(var(--spacing) * 4)}.size-5{width:calc(var(--spacing) * 5);height:calc(var(--spacing) * 5)}.size-6{width:calc(var(--spacing) * 6);height:calc(var(--spacing) * 6)}.size-10{width:calc(var(--spacing) * 10);height:calc(var(--spacing) * 10)}.size-full{width:100%;height:100%}.h-0{height:0}.h-0\\.5{height:calc(var(--spacing) * .5)}.h-7{height:calc(var(--spacing) * 7)}.h-9{height:calc(var(--spacing) * 9)}.h-\\[36px\\]{height:36px}.h-full{height:100%}.max-h-40{max-height:calc(var(--spacing) * 40)}.max-h-\\[36px\\]{max-height:36px}.min-h-0{min-height:0}.min-h-8{min-height:calc(var(--spacing) * 8)}.min-h-9{min-height:calc(var(--spacing) * 9)}.min-h-10{min-height:calc(var(--spacing) * 10)}.min-h-\\[36px\\]{min-height:36px}.min-h-\\[320px\\]{min-height:320px}.min-h-auto{min-height:auto}.w-0{width:0}.w-3{width:calc(var(--spacing) * 3)}.w-7{width:calc(var(--spacing) * 7)}.w-\\[90\\%\\]{width:90%}.w-full{width:100%}.max-w-\\[560px\\]{max-width:560px}.max-w-full{max-width:100%}.max-w-md{max-width:var(--container-md)}.min-w-0{min-width:0}.min-w-\\[640px\\]{min-width:640px}.flex-1{flex:1}.flex-shrink,.shrink{flex-shrink:1}.shrink-0{flex-shrink:0}.grow{flex-grow:1}.border-collapse{border-collapse:collapse}.-translate-x-1{--tw-translate-x:calc(var(--spacing) * -1);translate:var(--tw-translate-x) var(--tw-translate-y)}.-translate-x-1\\/2{--tw-translate-x:calc(calc(1 / 2 * 100%) * -1);translate:var(--tw-translate-x) var(--tw-translate-y)}.-translate-y-1{--tw-translate-y:calc(var(--spacing) * -1);translate:var(--tw-translate-x) var(--tw-translate-y)}.-translate-y-1\\/2{--tw-translate-y:calc(calc(1 / 2 * 100%) * -1);translate:var(--tw-translate-x) var(--tw-translate-y)}.translate-none{translate:none}.scale-3d{scale:var(--tw-scale-x) var(--tw-scale-y) var(--tw-scale-z)}.rotate-90{rotate:90deg}.rotate-180{rotate:180deg}.rotate-270{rotate:270deg}.transform{transform:var(--tw-rotate-x,) var(--tw-rotate-y,) var(--tw-rotate-z,) var(--tw-skew-x,) var(--tw-skew-y,)}.cursor-default{cursor:default}.cursor-grabbing{cursor:grabbing}.cursor-move{cursor:move}.cursor-not-allowed{cursor:not-allowed}.cursor-pointer{cursor:pointer}.touch-pinch-zoom{--tw-pinch-zoom:pinch-zoom;touch-action:var(--tw-pan-x,) var(--tw-pan-y,) var(--tw-pinch-zoom,)}.resize{resize:both}.list-disc{list-style-type:disc}.list-none{list-style-type:none}.columns-3{columns:3}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}.grid-cols-\\[2\\.5rem_\\.\\.\\.\\]{grid-template-columns:2.5rem ...}.grid-cols-\\[2\\.5rem_minmax\\(0\\,1fr\\)_2\\.25rem\\]{grid-template-columns:2.5rem minmax(0,1fr) 2.25rem}.grid-cols-\\[2\\.5rem_minmax\\(0\\,1fr\\)_8rem_2\\.25rem\\]{grid-template-columns:2.5rem minmax(0,1fr) 8rem 2.25rem}.grid-cols-\\[120px_minmax\\(0\\,1fr\\)\\]{grid-template-columns:120px minmax(0,1fr)}.flex-col{flex-direction:column}.flex-wrap{flex-wrap:wrap}.items-center{align-items:center}.items-start{align-items:flex-start}.justify-between{justify-content:space-between}.justify-center{justify-content:center}.justify-end{justify-content:flex-end}.gap-1{gap:var(--spacing)}.gap-1\\.5{gap:calc(var(--spacing) * 1.5)}.gap-2{gap:calc(var(--spacing) * 2)}.gap-4{gap:calc(var(--spacing) * 4)}:where(.space-y-1>:not(:last-child)){--tw-space-y-reverse:0;margin-block-start:calc(var(--spacing) * var(--tw-space-y-reverse));margin-block-end:calc(var(--spacing) * calc(1 - var(--tw-space-y-reverse)))}:where(.space-y-4>:not(:last-child)){--tw-space-y-reverse:0;margin-block-start:calc(calc(var(--spacing) * 4) * var(--tw-space-y-reverse));margin-block-end:calc(calc(var(--spacing) * 4) * calc(1 - var(--tw-space-y-reverse)))}:where(.space-y-reverse>:not(:last-child)){--tw-space-y-reverse:1}:where(.space-x-reverse>:not(:last-child)){--tw-space-x-reverse:1}:where(.divide-x>:not(:last-child)){--tw-divide-x-reverse:0;border-inline-style:var(--tw-border-style);border-inline-start-width:calc(1px * var(--tw-divide-x-reverse));border-inline-end-width:calc(1px * calc(1 - var(--tw-divide-x-reverse)))}:where(.divide-y>:not(:last-child)){--tw-divide-y-reverse:0;border-bottom-style:var(--tw-border-style);border-top-style:var(--tw-border-style);border-top-width:calc(1px * var(--tw-divide-y-reverse));border-bottom-width:calc(1px * calc(1 - var(--tw-divide-y-reverse)))}:where(.divide-y-reverse>:not(:last-child)){--tw-divide-y-reverse:1}.self-end{align-self:flex-end}.self-start{align-self:flex-start}.self-stretch{align-self:stretch}.truncate{text-overflow:ellipsis;white-space:nowrap;overflow:hidden}.overflow-auto{overflow:auto}.overflow-hidden{overflow:hidden}.overflow-x-auto{overflow-x:auto}.rounded{border-radius:.25rem}.rounded-\\[5px\\]{border-radius:5px}.rounded-\\[10px\\]{border-radius:10px}.rounded-full{border-radius:3.40282e38px}.rounded-lg{border-radius:var(--radius-lg)}.rounded-md{border-radius:var(--radius-md)}.rounded-none{border-radius:0}.rounded-s{border-start-start-radius:.25rem;border-end-start-radius:.25rem}.rounded-ss{border-start-start-radius:.25rem}.rounded-e{border-start-end-radius:.25rem;border-end-end-radius:.25rem}.rounded-se{border-start-end-radius:.25rem}.rounded-ee{border-end-end-radius:.25rem}.rounded-es{border-end-start-radius:.25rem}.rounded-t{border-top-left-radius:.25rem;border-top-right-radius:.25rem}.rounded-t-none{border-top-left-radius:0;border-top-right-radius:0}.rounded-l{border-top-left-radius:.25rem;border-bottom-left-radius:.25rem}.rounded-tl{border-top-left-radius:.25rem}.rounded-r{border-top-right-radius:.25rem;border-bottom-right-radius:.25rem}.rounded-tr{border-top-right-radius:.25rem}.rounded-b{border-bottom-right-radius:.25rem;border-bottom-left-radius:.25rem}.rounded-b-none{border-bottom-right-radius:0;border-bottom-left-radius:0}.rounded-br{border-bottom-right-radius:.25rem}.rounded-bl{border-bottom-left-radius:.25rem}.border{border-style:var(--tw-border-style);border-width:1px}.border-2{border-style:var(--tw-border-style);border-width:2px}.border-x{border-inline-style:var(--tw-border-style);border-inline-width:1px}.border-y{border-block-style:var(--tw-border-style);border-block-width:1px}.border-s{border-inline-start-style:var(--tw-border-style);border-inline-start-width:1px}.border-e{border-inline-end-style:var(--tw-border-style);border-inline-end-width:1px}.border-bs{border-block-start-style:var(--tw-border-style);border-block-start-width:1px}.border-be{border-block-end-style:var(--tw-border-style);border-block-end-width:1px}.border-t{border-top-style:var(--tw-border-style);border-top-width:1px}.border-r{border-right-style:var(--tw-border-style);border-right-width:1px}.border-b{border-bottom-style:var(--tw-border-style);border-bottom-width:1px}.border-l{border-left-style:var(--tw-border-style);border-left-width:1px}.border-dashed{--tw-border-style:dashed;border-style:dashed}.border-none{--tw-border-style:none;border-style:none}.border-\\[rgba\\(96\\,125\\,159\\,0\\.25\\)\\]{border-color:#607d9f40}.border-amber-300{border-color:var(--color-amber-300)}.border-black{border-color:var(--color-black)}.border-black\\/10{border-color:#0000001a}@supports (color:color-mix(in lab, red, red)){.border-black\\/10{border-color:color-mix(in oklab, var(--color-black) 10%, transparent)}}.border-error{border-color:var(--color-error)}.border-gray-100{border-color:var(--color-gray-100)}.border-gray-200{border-color:var(--color-gray-200)}.border-red-200{border-color:var(--color-red-200)}.border-sky-500{border-color:var(--color-sky-500)}.border-transparent{border-color:#0000}.border-l-gray-100{border-left-color:var(--color-gray-100)}.bg-\\[\\#f3f7fc\\]{background-color:#f3f7fc}.bg-amber-50{background-color:var(--color-amber-50)}.bg-black{background-color:var(--color-black)}.bg-gray-50{background-color:var(--color-gray-50)}.bg-gray-100{background-color:var(--color-gray-100)}.bg-orange-50{background-color:var(--color-orange-50)}.bg-orange-50\\/40{background-color:#fff7ed66}@supports (color:color-mix(in lab, red, red)){.bg-orange-50\\/40{background-color:color-mix(in oklab, var(--color-orange-50) 40%, transparent)}}.bg-red-50{background-color:var(--color-red-50)}.bg-rose-500{background-color:var(--color-rose-500)}.bg-rose-500\\/12{background-color:#f43f5e1f}@supports (color:color-mix(in lab, red, red)){.bg-rose-500\\/12{background-color:color-mix(in oklab, var(--color-rose-500) 12%, transparent)}}.bg-sky-50{background-color:var(--color-sky-50)}.bg-sky-500{background-color:var(--color-sky-500)}.bg-slate-100{background-color:var(--color-slate-100)}.bg-slate-200{background-color:var(--color-slate-200)}.bg-slate-200\\/55{background-color:#607d9f1c}@supports (color:color-mix(in lab, red, red)){.bg-slate-200\\/55{background-color:color-mix(in oklab, var(--color-slate-200) 55%, transparent)}}.bg-transparent{background-color:#0000}.bg-white{background-color:var(--color-white)}.bg-repeat{background-repeat:repeat}.mask-no-clip{-webkit-mask-clip:no-clip;mask-clip:no-clip}.mask-repeat{-webkit-mask-repeat:repeat;mask-repeat:repeat}.p-0{padding:0}.p-3{padding:calc(var(--spacing) * 3)}.p-4{padding:calc(var(--spacing) * 4)}.p-5{padding:calc(var(--spacing) * 5)}.p-6{padding:calc(var(--spacing) * 6)}.p-12{padding:calc(var(--spacing) * 12)}.px-1{padding-inline:var(--spacing)}.px-2{padding-inline:calc(var(--spacing) * 2)}.px-3{padding-inline:calc(var(--spacing) * 3)}.px-4{padding-inline:calc(var(--spacing) * 4)}.px-5{padding-inline:calc(var(--spacing) * 5)}.py-0{padding-block:0}.py-0\\.5{padding-block:calc(var(--spacing) * .5)}.py-1{padding-block:var(--spacing)}.py-1\\.5{padding-block:calc(var(--spacing) * 1.5)}.py-2{padding-block:calc(var(--spacing) * 2)}.py-2\\.5{padding-block:calc(var(--spacing) * 2.5)}.py-3{padding-block:calc(var(--spacing) * 3)}.py-4{padding-block:calc(var(--spacing) * 4)}.py-12{padding-block:calc(var(--spacing) * 12)}.py-\\[2px\\]{padding-block:2px}.pr-1{padding-right:var(--spacing)}.pr-3{padding-right:calc(var(--spacing) * 3)}.pb-2{padding-bottom:calc(var(--spacing) * 2)}.pb-2\\.5{padding-bottom:calc(var(--spacing) * 2.5)}.pb-8{padding-bottom:calc(var(--spacing) * 8)}.pl-0{padding-left:0}.pl-0\\.5{padding-left:calc(var(--spacing) * .5)}.pl-5{padding-left:calc(var(--spacing) * 5)}.text-center{text-align:center}.text-left{text-align:left}.text-right{text-align:right}.font-mono{font-family:var(--font-mono)}.text-base{font-size:var(--text-base);line-height:var(--tw-leading,var(--text-base--line-height))}.text-sm{font-size:var(--text-sm);line-height:var(--tw-leading,var(--text-sm--line-height))}.text-xs{font-size:var(--text-xs);line-height:var(--tw-leading,var(--text-xs--line-height))}.text-\\[10px\\]{font-size:10px}.text-\\[11px\\]{font-size:11px}.text-\\[12px\\]{font-size:12px}.text-\\[13px\\]{font-size:13px}.leading-none{--tw-leading:1;line-height:1}.leading-relaxed{--tw-leading:var(--leading-relaxed);line-height:var(--leading-relaxed)}.leading-snug{--tw-leading:var(--leading-snug);line-height:var(--leading-snug)}.leading-tight{--tw-leading:var(--leading-tight);line-height:var(--leading-tight)}.font-medium{--tw-font-weight:var(--font-weight-medium);font-weight:var(--font-weight-medium)}.font-normal{--tw-font-weight:var(--font-weight-normal);font-weight:var(--font-weight-normal)}.font-semibold{--tw-font-weight:var(--font-weight-semibold);font-weight:var(--font-weight-semibold)}.tracking-wide{--tw-tracking:var(--tracking-wide);letter-spacing:var(--tracking-wide)}.text-wrap{text-wrap:wrap}.break-all{word-break:break-all}.text-clip{text-overflow:clip}.text-ellipsis{text-overflow:ellipsis}.whitespace-nowrap{white-space:nowrap}.whitespace-pre-wrap{white-space:pre-wrap}.text-\\[\\#8f98a3\\]{color:#8f98a3}.text-\\[\\#29323d\\]{color:#29323d}.text-\\[\\#576575\\]{color:#576575}.text-amber-950{color:var(--color-amber-950)}.text-emerald-600{color:var(--color-emerald-600)}.text-error{color:var(--color-error)}.text-gray-400{color:var(--color-gray-400)}.text-gray-400\\/80{color:#7b8793cc}@supports (color:color-mix(in lab, red, red)){.text-gray-400\\/80{color:color-mix(in oklab, var(--color-gray-400) 80%, transparent)}}.text-gray-500{color:var(--color-gray-500)}.text-gray-600{color:var(--color-gray-600)}.text-gray-700{color:var(--color-gray-700)}.text-gray-800{color:var(--color-gray-800)}.text-gray-900{color:var(--color-gray-900)}.text-red-600{color:var(--color-red-600)}.text-red-700{color:var(--color-red-700)}.text-red-800{color:var(--color-red-800)}.text-red-900{color:var(--color-red-900)}.text-rose-600{color:var(--color-rose-600)}.text-sky-600{color:var(--color-sky-600)}.text-slate-500{color:var(--color-slate-500)}.text-transparent{color:#0000}.text-white{color:var(--color-white)}.capitalize{text-transform:capitalize}.lowercase{text-transform:lowercase}.normal-case{text-transform:none}.uppercase{text-transform:uppercase}.italic{font-style:italic}.not-italic{font-style:normal}.diagonal-fractions{--tw-numeric-fraction:diagonal-fractions;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.lining-nums{--tw-numeric-figure:lining-nums;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.oldstyle-nums{--tw-numeric-figure:oldstyle-nums;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.ordinal{--tw-ordinal:ordinal;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.proportional-nums{--tw-numeric-spacing:proportional-nums;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.slashed-zero{--tw-slashed-zero:slashed-zero;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.stacked-fractions{--tw-numeric-fraction:stacked-fractions;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.tabular-nums{--tw-numeric-spacing:tabular-nums;font-variant-numeric:var(--tw-ordinal,) var(--tw-slashed-zero,) var(--tw-numeric-figure,) var(--tw-numeric-spacing,) var(--tw-numeric-fraction,)}.normal-nums{font-variant-numeric:normal}.line-through{text-decoration-line:line-through}.no-underline{text-decoration-line:none}.overline{text-decoration-line:overline}.underline{text-decoration-line:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.subpixel-antialiased{-webkit-font-smoothing:auto;-moz-osx-font-smoothing:auto}.opacity-0{opacity:0}.opacity-40{opacity:.4}.opacity-70{opacity:.7}.opacity-90{opacity:.9}.opacity-100{opacity:1}.shadow{--tw-shadow:0 1px 3px 0 var(--tw-shadow-color,#0000001a), 0 1px 2px -1px var(--tw-shadow-color,#0000001a);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.shadow-\\[0_0_0_1px_rgba\\(205\\,216\\,228\\,0\\.25\\)\\,0_2px_12px_rgba\\(205\\,216\\,228\\,0\\.5\\)\\]{--tw-shadow:0 0 0 1px var(--tw-shadow-color,#cdd8e440), 0 2px 12px var(--tw-shadow-color,#cdd8e480);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.shadow-\\[inset_0_-1px_0_\\#e3e5e8\\,0_0_0_1px_rgba\\(205\\,216\\,228\\,0\\.25\\)\\,0_2px_12px_rgba\\(205\\,216\\,228\\,0\\.5\\)\\]{--tw-shadow:inset 0 -1px 0 var(--tw-shadow-color,#e3e5e8), 0 0 0 1px var(--tw-shadow-color,#cdd8e440), 0 2px 12px var(--tw-shadow-color,#cdd8e480);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.shadow-lg{--tw-shadow:0 10px 15px -3px var(--tw-shadow-color,#0000001a), 0 4px 6px -4px var(--tw-shadow-color,#0000001a);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.shadow-sm{--tw-shadow:0 1px 3px 0 var(--tw-shadow-color,#0000001a), 0 1px 2px -1px var(--tw-shadow-color,#0000001a);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.shadow-xl{--tw-shadow:0 20px 25px -5px var(--tw-shadow-color,#0000001a), 0 8px 10px -6px var(--tw-shadow-color,#0000001a);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.ring{--tw-ring-shadow:var(--tw-ring-inset,) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color,currentcolor);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.inset-ring,.inset-ring-1{--tw-inset-ring-shadow:inset 0 0 0 1px var(--tw-inset-ring-color,currentcolor);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.inset-ring-rose-600{--tw-inset-ring-color:var(--color-rose-600)}.outline{outline-style:var(--tw-outline-style);outline-width:1px}.blur{--tw-blur:blur(8px);filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.drop-shadow{--tw-drop-shadow-size:drop-shadow(0 1px 2px var(--tw-drop-shadow-color,#0000001a)) drop-shadow(0 1px 1px var(--tw-drop-shadow-color,#0000000f));--tw-drop-shadow:drop-shadow(0 1px 2px #0000001a) drop-shadow(0 1px 1px #0000000f);filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.grayscale{--tw-grayscale:grayscale(100%);filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.invert{--tw-invert:invert(100%);filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.sepia{--tw-sepia:sepia(100%);filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.filter{filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.backdrop-blur{--tw-backdrop-blur:blur(8px);-webkit-backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,);backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,)}.backdrop-grayscale{--tw-backdrop-grayscale:grayscale(100%);-webkit-backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,);backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,)}.backdrop-invert{--tw-backdrop-invert:invert(100%);-webkit-backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,);backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,)}.backdrop-sepia{--tw-backdrop-sepia:sepia(100%);-webkit-backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,);backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,)}.backdrop-filter{-webkit-backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,);backdrop-filter:var(--tw-backdrop-blur,) var(--tw-backdrop-brightness,) var(--tw-backdrop-contrast,) var(--tw-backdrop-grayscale,) var(--tw-backdrop-hue-rotate,) var(--tw-backdrop-invert,) var(--tw-backdrop-opacity,) var(--tw-backdrop-saturate,) var(--tw-backdrop-sepia,)}.transition{transition-property:color,background-color,border-color,outline-color,text-decoration-color,fill,stroke,--tw-gradient-from,--tw-gradient-via,--tw-gradient-to,opacity,box-shadow,transform,translate,scale,rotate,filter,-webkit-backdrop-filter,backdrop-filter,display,content-visibility,overlay,pointer-events;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-opacity{transition-property:opacity;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-transform{transition-property:transform,translate,scale,rotate;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.duration-150{--tw-duration:.15s;transition-duration:.15s}.duration-300{--tw-duration:.3s;transition-duration:.3s}.ease-in{--tw-ease:var(--ease-in);transition-timing-function:var(--ease-in)}.ease-in-out{--tw-ease:var(--ease-in-out);transition-timing-function:var(--ease-in-out)}.ease-out{--tw-ease:var(--ease-out);transition-timing-function:var(--ease-out)}.outline-none{--tw-outline-style:none;outline-style:none}.select-none{-webkit-user-select:none;user-select:none}:where(.divide-x-reverse>:not(:last-child)){--tw-divide-x-reverse:1}.ring-inset{--tw-ring-inset:inset}@media (hover:hover){.group-hover\\:bg-gray-50:is(:where(.group):hover *){background-color:var(--color-gray-50)}.group-hover\\:opacity-100:is(:where(.group):hover *){opacity:1}.hover\\:bg-\\[\\#edf2f8\\]:hover{background-color:#edf2f8}.hover\\:bg-gray-50:hover{background-color:var(--color-gray-50)}.hover\\:bg-gray-100:hover{background-color:var(--color-gray-100)}.hover\\:bg-transparent:hover{background-color:#0000}.hover\\:text-gray-600:hover{color:var(--color-gray-600)}.hover\\:text-gray-700:hover{color:var(--color-gray-700)}.hover\\:no-underline:hover{text-decoration-line:none}}.focus\\:outline-none:focus{--tw-outline-style:none;outline-style:none}.focus-visible\\:opacity-100:focus-visible{opacity:1}.focus-visible\\:ring-0:focus-visible{--tw-ring-shadow:var(--tw-ring-inset,) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color,currentcolor);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.focus-visible\\:ring-2:focus-visible{--tw-ring-shadow:var(--tw-ring-inset,) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color,currentcolor);box-shadow:var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)}.focus-visible\\:ring-sky-600\\/40:focus-visible{--tw-ring-color:#0284c766}@supports (color:color-mix(in lab, red, red)){.focus-visible\\:ring-sky-600\\/40:focus-visible{--tw-ring-color:color-mix(in oklab, var(--color-sky-600) 40%, transparent)}}.focus-visible\\:outline-none:focus-visible{--tw-outline-style:none;outline-style:none}.\\[\\&_\\:is\\(p\\,ul\\,ol\\)\\:last-child\\]\\:mb-0 :is(p,ul,ol):last-child{margin-bottom:0}.\\[\\&_code\\]\\:text-\\[0\\.85em\\] code{font-size:.85em}.\\[\\&\\:\\:part\\(base\\)\\]\\:cursor-move::part(base){cursor:move}}@layer theme{:host,:host{--font-sans:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";--font-mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;--spacing:.25rem;--container-md:28rem;--text-xs:.75rem;--text-xs--line-height:calc(1 / .75);--text-sm:.875rem;--text-sm--line-height:calc(1.25 / .875);--text-base:1rem;--text-base--line-height:calc(1.5 / 1);--font-weight-normal:400;--font-weight-medium:500;--font-weight-semibold:600;--tracking-wide:.025em;--leading-tight:1.25;--leading-snug:1.375;--leading-relaxed:1.625;--radius-md:4px;--radius-lg:5px;--ease-in:cubic-bezier(.4, 0, 1, 1);--ease-out:cubic-bezier(0, 0, .2, 1);--ease-in-out:cubic-bezier(.4, 0, .2, 1);--default-transition-duration:.15s;--default-transition-timing-function:cubic-bezier(.4, 0, .2, 1);--default-font-family:var(--font-sans);--default-mono-font-family:var(--font-mono);--color-white:#fff;--color-black:#000;--color-error:#d81f23;--color-gray-50:#f3f7fc;--color-gray-100:#e4edf6;--color-gray-200:#cdd8e4;--color-gray-400:#7b8793;--color-gray-500:#606d7b;--color-gray-600:#515f6c;--color-gray-700:#3f4d5a;--color-gray-800:#33404d;--color-gray-900:#1f2933;--color-slate-100:#607d9f1a;--color-slate-200:#607d9f33;--color-slate-500:#607d9f80;--color-red-50:#fef2f2;--color-red-200:#fecaca;--color-red-500:#ef4444;--color-red-600:#dc2626;--color-red-700:#b91c1c;--color-red-800:#991b1b;--color-red-900:#7f1d1d;--color-orange-50:#fff7ed;--color-amber-50:#fffbeb;--color-amber-300:#fcd34d;--color-amber-700:#b45309;--color-amber-950:#451a03;--color-emerald-600:#059669;--color-teal-700:#0f766e;--color-sky-50:#f0f9ff;--color-sky-500:#0ea5e9;--color-sky-600:#0284c7;--color-blue-500:#3b82f6;--color-rose-500:#f43f5e;--color-rose-600:#e11d48}}hr{margin-block:var(--spacing);border-color:var(--color-gray-100)}.navigation-node-tree-view-popover::part(panel){width:18rem;padding:0;overflow:hidden}.rdp-month_grid:not(.data) td:not(:last-child),.rdp-month_grid:not(.data) th:not(:last-child){padding-inline-end:0!important}.rdp-month_grid:not(.data) td:not(:first-child),.rdp-month_grid:not(.data) th:not(:first-child){padding-inline-start:0!important}.rdp-month_grid:not(.data) td,.rdp-month_grid:not(.data) th{padding-block:0!important}@property --tw-translate-x{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-y{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-z{syntax:"*";inherits:false;initial-value:0}@property --tw-scale-x{syntax:"*";inherits:false;initial-value:1}@property --tw-scale-y{syntax:"*";inherits:false;initial-value:1}@property --tw-scale-z{syntax:"*";inherits:false;initial-value:1}@property --tw-rotate-x{syntax:"*";inherits:false}@property --tw-rotate-y{syntax:"*";inherits:false}@property --tw-rotate-z{syntax:"*";inherits:false}@property --tw-skew-x{syntax:"*";inherits:false}@property --tw-skew-y{syntax:"*";inherits:false}@property --tw-pan-x{syntax:"*";inherits:false}@property --tw-pan-y{syntax:"*";inherits:false}@property --tw-pinch-zoom{syntax:"*";inherits:false}@property --tw-space-y-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-space-x-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-divide-x-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-border-style{syntax:"*";inherits:false;initial-value:solid}@property --tw-divide-y-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-leading{syntax:"*";inherits:false}@property --tw-font-weight{syntax:"*";inherits:false}@property --tw-tracking{syntax:"*";inherits:false}@property --tw-ordinal{syntax:"*";inherits:false}@property --tw-slashed-zero{syntax:"*";inherits:false}@property --tw-numeric-figure{syntax:"*";inherits:false}@property --tw-numeric-spacing{syntax:"*";inherits:false}@property --tw-numeric-fraction{syntax:"*";inherits:false}@property --tw-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-shadow-color{syntax:"*";inherits:false}@property --tw-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-inset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-shadow-color{syntax:"*";inherits:false}@property --tw-inset-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-ring-color{syntax:"*";inherits:false}@property --tw-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-ring-color{syntax:"*";inherits:false}@property --tw-inset-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-ring-inset{syntax:"*";inherits:false}@property --tw-ring-offset-width{syntax:"<length>";inherits:false;initial-value:0}@property --tw-ring-offset-color{syntax:"*";inherits:false;initial-value:#fff}@property --tw-ring-offset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-outline-style{syntax:"*";inherits:false;initial-value:solid}@property --tw-blur{syntax:"*";inherits:false}@property --tw-brightness{syntax:"*";inherits:false}@property --tw-contrast{syntax:"*";inherits:false}@property --tw-grayscale{syntax:"*";inherits:false}@property --tw-hue-rotate{syntax:"*";inherits:false}@property --tw-invert{syntax:"*";inherits:false}@property --tw-opacity{syntax:"*";inherits:false}@property --tw-saturate{syntax:"*";inherits:false}@property --tw-sepia{syntax:"*";inherits:false}@property --tw-drop-shadow{syntax:"*";inherits:false}@property --tw-drop-shadow-color{syntax:"*";inherits:false}@property --tw-drop-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-drop-shadow-size{syntax:"*";inherits:false}@property --tw-backdrop-blur{syntax:"*";inherits:false}@property --tw-backdrop-brightness{syntax:"*";inherits:false}@property --tw-backdrop-contrast{syntax:"*";inherits:false}@property --tw-backdrop-grayscale{syntax:"*";inherits:false}@property --tw-backdrop-hue-rotate{syntax:"*";inherits:false}@property --tw-backdrop-invert{syntax:"*";inherits:false}@property --tw-backdrop-opacity{syntax:"*";inherits:false}@property --tw-backdrop-saturate{syntax:"*";inherits:false}@property --tw-backdrop-sepia{syntax:"*";inherits:false}@property --tw-duration{syntax:"*";inherits:false}@property --tw-ease{syntax:"*";inherits:false}`,Ye=`:host{--pk-color-white:#fff;--pk-color-black:#000;--pk-color-link:#2563eb;--pk-color-error:#d81f23;--pk-color-success:var(--pk-color-teal-700);--pk-color-warning:var(--pk-color-amber-700);--pk-color-gray-50:#f3f7fc;--pk-color-gray-100:#e4edf6;--pk-color-gray-150:#d8e2ee;--pk-color-gray-200:#cdd8e4;--pk-color-gray-250:#b3bfcc;--pk-color-gray-300:#9aa5b1;--pk-color-gray-350:#8b96a2;--pk-color-gray-400:#7b8793;--pk-color-gray-500:#606d7b;--pk-color-gray-550:#596673;--pk-color-gray-600:#515f6c;--pk-color-gray-700:#3f4d5a;--pk-color-gray-800:#33404d;--pk-color-gray-900:#1f2933;--pk-color-gray-1000:#131a20;--pk-color-slate-50:#607d9f0d;--pk-color-slate-100:#607d9f1a;--pk-color-slate-150:#607d9f26;--pk-color-slate-200:#607d9f33;--pk-color-slate-250:#607d9f40;--pk-color-slate-300:#607d9f4d;--pk-color-slate-350:#607d9f59;--pk-color-slate-400:#607d9f66;--pk-color-slate-500:#607d9f80;--pk-color-slate-550:#607d9f8c;--pk-color-slate-600:#607d9f99;--pk-color-slate-700:#607d9fb3;--pk-color-slate-800:#607d9fcc;--pk-color-slate-900:#607d9fe6;--pk-color-slate-1000:#607d9f;--pk-color-red-50:#fef2f2;--pk-color-red-100:#fee2e2;--pk-color-red-200:#fecaca;--pk-color-red-300:#fca5a5;--pk-color-red-400:#f87171;--pk-color-red-500:#ef4444;--pk-color-red-600:#dc2626;--pk-color-red-700:#b91c1c;--pk-color-red-800:#991b1b;--pk-color-red-900:#7f1d1d;--pk-color-red-950:#450a0a;--pk-color-orange-50:#fff7ed;--pk-color-orange-100:#ffedd5;--pk-color-orange-200:#fed7aa;--pk-color-orange-300:#fdba74;--pk-color-orange-400:#fb923c;--pk-color-orange-500:#f97316;--pk-color-orange-600:#ea580c;--pk-color-orange-700:#c2410c;--pk-color-orange-800:#9a3412;--pk-color-orange-900:#7c2d12;--pk-color-orange-950:#431407;--pk-color-amber-50:#fffbeb;--pk-color-amber-100:#fef3c7;--pk-color-amber-200:#fde68a;--pk-color-amber-300:#fcd34d;--pk-color-amber-400:#fbbf24;--pk-color-amber-500:#f59e0b;--pk-color-amber-600:#d97706;--pk-color-amber-700:#b45309;--pk-color-amber-800:#92400e;--pk-color-amber-900:#78350f;--pk-color-amber-950:#451a03;--pk-color-yellow-50:#fefce8;--pk-color-yellow-100:#fef9c3;--pk-color-yellow-200:#fef08a;--pk-color-yellow-300:#fde047;--pk-color-yellow-400:#facc15;--pk-color-yellow-500:#eab308;--pk-color-yellow-600:#ca8a04;--pk-color-yellow-700:#a16207;--pk-color-yellow-750:#93580b;--pk-color-yellow-800:#854d0e;--pk-color-yellow-900:#713f12;--pk-color-yellow-950:#422006;--pk-color-lime-50:#f7fee7;--pk-color-lime-100:#ecfccb;--pk-color-lime-200:#d9f99d;--pk-color-lime-300:#bef264;--pk-color-lime-400:#a3e635;--pk-color-lime-500:#84cc16;--pk-color-lime-600:#65a30d;--pk-color-lime-700:#4d7c0f;--pk-color-lime-800:#3f6212;--pk-color-lime-900:#365314;--pk-color-lime-950:#1a2e05;--pk-color-green-050:#f0fdf4;--pk-color-green-100:#dcfce7;--pk-color-green-200:#bbf7d0;--pk-color-green-300:#86efac;--pk-color-green-400:#4ade80;--pk-color-green-500:#22c55e;--pk-color-green-600:#16a34a;--pk-color-green-700:#15803d;--pk-color-green-800:#166534;--pk-color-green-900:#14532d;--pk-color-green-950:#052e16;--pk-color-emerald-50:#ecfdf5;--pk-color-emerald-100:#d1fae5;--pk-color-emerald-200:#a7f3d0;--pk-color-emerald-300:#6ee7b7;--pk-color-emerald-400:#34d399;--pk-color-emerald-500:#10b981;--pk-color-emerald-600:#059669;--pk-color-emerald-700:#047857;--pk-color-emerald-800:#065f46;--pk-color-emerald-900:#064e3b;--pk-color-emerald-950:#022c22;--pk-color-teal-50:#f0fdfa;--pk-color-teal-100:#ccfbf1;--pk-color-teal-200:#99f6e4;--pk-color-teal-300:#5eead4;--pk-color-teal-400:#2dd4bf;--pk-color-teal-500:#14b8a6;--pk-color-teal-550:#11a697;--pk-color-teal-600:#0d9488;--pk-color-teal-700:#0f766e;--pk-color-teal-800:#115e59;--pk-color-teal-900:#134e4a;--pk-color-teal-950:#042f2e;--pk-color-teal-550-border:#008779;--pk-color-cyan-50:#ecfeff;--pk-color-cyan-100:#cffafe;--pk-color-cyan-200:#a5f3fc;--pk-color-cyan-300:#67e8f9;--pk-color-cyan-400:#22d3ee;--pk-color-cyan-500:#06b6d4;--pk-color-cyan-600:#0891b2;--pk-color-cyan-700:#0e7490;--pk-color-cyan-800:#155e75;--pk-color-cyan-900:#164e63;--pk-color-cyan-950:#083344;--pk-color-sky-50:#f0f9ff;--pk-color-sky-100:#e0f2fe;--pk-color-sky-200:#bae6fd;--pk-color-sky-300:#7dd3fc;--pk-color-sky-400:#38bdf8;--pk-color-sky-500:#0ea5e9;--pk-color-sky-600:#0284c7;--pk-color-sky-700:#0369a1;--pk-color-sky-800:#075985;--pk-color-sky-900:#0c4a6e;--pk-color-sky-950:#082f49;--pk-color-blue-50:#eff6ff;--pk-color-blue-100:#dbeafe;--pk-color-blue-200:#bfdbfe;--pk-color-blue-300:#93c5fd;--pk-color-blue-400:#60a5fa;--pk-color-blue-500:#3b82f6;--pk-color-blue-600:#2563eb;--pk-color-blue-700:#1d4ed8;--pk-color-blue-800:#1e40af;--pk-color-blue-900:#1e3a8a;--pk-color-blue-950:#172554;--pk-color-indigo-50:#eef2ff;--pk-color-indigo-100:#e0e7ff;--pk-color-indigo-200:#c7d2fe;--pk-color-indigo-300:#a5b4fc;--pk-color-indigo-400:#818cf8;--pk-color-indigo-500:#6366f1;--pk-color-indigo-600:#4f46e5;--pk-color-indigo-700:#4338ca;--pk-color-indigo-800:#3730a3;--pk-color-indigo-900:#312e81;--pk-color-indigo-950:#1e1b4b;--pk-color-violet-50:#f5f3ff;--pk-color-violet-100:#ede9fe;--pk-color-violet-200:#ddd6fe;--pk-color-violet-300:#c4b5fd;--pk-color-violet-400:#a78bfa;--pk-color-violet-500:#8b5cf6;--pk-color-violet-600:#7c3aed;--pk-color-violet-700:#6d28d9;--pk-color-violet-800:#5b21b6;--pk-color-violet-900:#4c1d95;--pk-color-violet-950:#2e1065;--pk-color-purple-50:#faf5ff;--pk-color-purple-100:#f3e8ff;--pk-color-purple-200:#e9d5ff;--pk-color-purple-300:#d8b4fe;--pk-color-purple-400:#c084fc;--pk-color-purple-500:#a855f7;--pk-color-purple-600:#9333ea;--pk-color-purple-700:#7e22ce;--pk-color-purple-800:#6b21a8;--pk-color-purple-900:#581c87;--pk-color-purple-950:#3b0764;--pk-color-fuchsia-50:#fdf4ff;--pk-color-fuchsia-100:#fae8ff;--pk-color-fuchsia-200:#f5d0fe;--pk-color-fuchsia-300:#f0abfc;--pk-color-fuchsia-400:#e879f9;--pk-color-fuchsia-500:#d946ef;--pk-color-fuchsia-600:#c026d3;--pk-color-fuchsia-700:#a21caf;--pk-color-fuchsia-800:#86198f;--pk-color-fuchsia-900:#701a75;--pk-color-fuchsia-950:#4a044e;--pk-color-pink-50:#fdf2f8;--pk-color-pink-100:#fce7f3;--pk-color-pink-200:#fbcfe8;--pk-color-pink-300:#f9a8d4;--pk-color-pink-400:#f472b6;--pk-color-pink-500:#ec4899;--pk-color-pink-600:#db2777;--pk-color-pink-700:#be185d;--pk-color-pink-800:#9d174d;--pk-color-pink-900:#831843;--pk-color-pink-950:#500724;--pk-color-rose-50:#fff1f2;--pk-color-rose-100:#ffe4e6;--pk-color-rose-200:#fecdd3;--pk-color-rose-300:#fda4af;--pk-color-rose-400:#fb7185;--pk-color-rose-500:#f43f5e;--pk-color-rose-600:#e11d48;--pk-color-rose-700:#be123c;--pk-color-rose-800:#9f1239;--pk-color-rose-900:#881337;--pk-color-rose-950:#4c0519;--pk-radius-xs:2px;--pk-radius-sm:3px;--pk-radius-md:4px;--pk-radius-lg:5px;--pk-shadow-focus:0 0 0 2px var(--pk-color-sky-600), 0 0 5px 1px hsl(from var(--pk-color-sky-600) h s l / .7);--pk-shadow-focus-inset:0 0 0 1px #fff, 0 0 0 3px var(--pk-color-sky-600), 0 0 5px 1px hsl(from var(--pk-color-sky-600) h s l / .7);--pk-shadow-popup:0 0 0 1px #1f29331a, 0 5px 20px #1f293340;--pk-shadow-popover:0 0 0 1px #7b879340, 0 25px 100px #1f293380;--pk-shadow-modal:0 0 0 1px color-mix(in oklab, var(--pk-color-gray-400) 25%, transparent), 0 25px 100px color-mix(in oklab, var(--pk-color-gray-900) 50%, transparent);--pk-font-family:system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;--pk-font-family-mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;--pk-font-size-mono:.9em;--pk-line-height-mono:1.5;--pk-font-size-sm:13px;--pk-font-size-base:14px;--pk-line-height:1.4;--pk-checkbox-size:1rem;--pk-radio-size:1rem;--pk-input-height:var(--pk-btn-height-default);--pk-input-control-line-height:1.25rem;--pk-input-border-color:var(--pk-color-slate-400);--pk-input-border:1px solid var(--pk-input-border-color);--pk-input-border-radius:var(--pk-radius-sm);--pk-input-bg:#fbfcfe;--pk-input-placeholder-color:var(--pk-color-gray-400);--pk-input-focus-shadow:0 0 0 1px var(--pk-color-sky-600), 0 0 4px 0 hsl(from var(--pk-color-sky-600) h s l / .7);--pk-input-invalid-focus-shadow:0 0 0 1px var(--pk-color-rose-600), 0 0 4px 0 hsl(from var(--pk-color-rose-600) h s l / .7);--pk-input-mono-font-family:var(--pk-font-family-mono);--pk-input-mono-font-size:var(--pk-font-size-mono);--pk-input-mono-line-height:var(--pk-line-height-mono);--pk-textarea-border-radius:var(--pk-radius-md);--pk-checkbox-border-color:#c0cbd9;--pk-control-label-gap:.35rem;--pk-btn-icon-gap-xxs:4px;--pk-btn-icon-gap-xs:4px;--pk-btn-icon-gap-sm:5px;--pk-btn-icon-gap-default:6px;--pk-btn-icon-gap-lg:7px;--pk-btn-icon-gap-xl:8px;--pk-btn-icon-gap:var(--pk-btn-icon-gap-default);--pk-btn-caret-size-xxs:10px;--pk-btn-caret-size-xs:11px;--pk-btn-caret-size-sm:11px;--pk-btn-caret-size-default:12px;--pk-btn-caret-size-lg:13px;--pk-btn-caret-size-xl:14px;--pk-btn-height-xxs:1.375rem;--pk-btn-height-xs:1.625rem;--pk-btn-height-sm:1.875rem;--pk-btn-height-default:2.125rem;--pk-btn-height-lg:2.375rem;--pk-btn-height-xl:2.625rem;--pk-btn-font-xxs:11px;--pk-btn-font-xs:12px;--pk-btn-font-sm:13px;--pk-btn-font-default:var(--pk-font-size-base);--pk-btn-font-lg:var(--pk-font-size-base);--pk-btn-font-xl:1rem;--pk-btn-padding-inline-xxs:5px;--pk-btn-padding-inline-xs:7px;--pk-btn-padding-inline-sm:8px;--pk-btn-padding-inline-default:9px;--pk-btn-padding-inline-lg:12px;--pk-btn-padding-inline-xl:15px;--pk-btn-icon-size-xxs:10px;--pk-btn-icon-size-xs:11px;--pk-btn-icon-size-sm:13px;--pk-btn-icon-size-default:14px;--pk-btn-icon-size-lg:14px;--pk-btn-icon-size-xl:16px;--pk-btn-radius-xxs:var(--pk-radius-sm);--pk-btn-radius-xs:var(--pk-radius-sm);--pk-btn-radius-sm:var(--pk-radius-md);--pk-btn-radius-default:var(--pk-radius-lg);--pk-btn-radius-lg:var(--pk-radius-lg);--pk-btn-radius-xl:var(--pk-radius-lg);--pk-action-fill:var(--pk-color-slate-250);--pk-action-fill-hover:var(--pk-color-slate-300);--pk-action-fill-active:var(--pk-color-slate-400);--pk-action-on:var(--pk-color-gray-700);--pk-action-primary-fill:var(--pk-color-red-600);--pk-action-primary-fill-hover:var(--pk-color-red-700);--pk-action-primary-fill-active:var(--pk-color-red-800);--pk-action-primary-on:var(--pk-color-white)}html{scrollbar-gutter:stable}html.pk-scroll-lock{--pk-scroll-lock:1}@layer pk-utilities{:is(pk-spinner,pk-icon,pk-button,pk-checkbox,pk-checkbox-select,pk-color-input,pk-code-editor,pk-input,pk-input-group,pk-input-group-addon,pk-input-group-button,pk-input-group-input,pk-input-group-text,pk-input-group-textarea,pk-textarea,pk-tiptap-editor,pk-tiptap-input,pk-tiptap-content,pk-field,pk-separator,pk-status,pk-toggle,pk-toggle-group,pk-lightswitch,pk-button-group,pk-button-group-separator,pk-button-group-text,pk-dropdown-label,pk-dropdown-item,pk-dropdown-separator,pk-radio,pk-radio-group,pk-tab,pk-tab-heading,pk-tab-panel,pk-tabs,pk-scroll-area,pk-option,pk-option-group,pk-select,pk-calendar,pk-date-picker,pk-combobox,pk-time-picker,pk-copy-button,pk-editable-table):not(:defined){animation:2s step-end pk-fouce}:is(pk-dialog,pk-popup):not(:defined){display:none}:is(pk-dropdown-menu,pk-popover,pk-tooltip):not(:defined)>:not([slot=trigger]){display:none}:is(pk-select,pk-combobox,pk-time-picker,pk-checkbox-select):not(:defined)>:not([slot]){display:none}.pk-cloak:has(:not(:defined)){animation:2s step-end pk-fouce}@keyframes pk-fouce{0%{visibility:hidden}}}.pk-dialog__header{border-bottom:1px solid var(--pk-color-gray-150);border-radius:var(--pk-radius-lg) var(--pk-radius-lg) 0 0;text-align:left;background:#f3f7fb;flex-direction:column;flex-shrink:0;gap:.25rem;padding:1rem;display:flex;position:relative}.pk-dialog__title{color:var(--pk-color-gray-700);margin:0;font-size:.9375rem;font-weight:600;line-height:1.2}.pk-dialog__body{font-size:var(--pk-font-size-sm);color:var(--pk-color-gray-600);padding:1rem;line-height:1.5}.pk-dialog__footer{border-top:1px solid var(--pk-color-gray-150);border-radius:0 0 var(--pk-radius-lg) var(--pk-radius-lg);background:#e4edf6;flex-direction:row;justify-content:flex-end;gap:.5rem;padding:.625rem 1rem;display:flex}.pk-dialog__close{--pk-dialog-close-focus-padding:.25rem;top:calc(1rem - var(--pk-dialog-close-focus-padding));right:calc(1rem - var(--pk-dialog-close-focus-padding));opacity:.7;--pk-btn-padding-inline:var(--pk-dialog-close-focus-padding);--pk-btn-height:calc(1.125rem + 2 * var(--pk-dialog-close-focus-padding));--pk-btn-font:1.125rem;--pk-btn-radius:var(--pk-radius-sm);line-height:1;position:absolute}.pk-dialog__close:hover,.pk-dialog__close:focus-visible{opacity:1}[data-pk-field-wrap-controls] pk-field{vertical-align:middle;width:auto;max-width:100%;display:inline-block}`,Xe=t((e=>{function t(e,t){var n=e.length;e.push(t);a:for(;0<n;){var r=n-1>>>1,a=e[r];if(0<i(a,t))e[r]=t,e[n]=a,n=r;else break a}}function n(e){return e.length===0?null:e[0]}function r(e){if(e.length===0)return null;var t=e[0],n=e.pop();if(n!==t){e[0]=n;a:for(var r=0,a=e.length,o=a>>>1;r<o;){var s=2*(r+1)-1,c=e[s],l=s+1,u=e[l];if(0>i(c,n))l<a&&0>i(u,c)?(e[r]=u,e[l]=n,r=l):(e[r]=c,e[s]=n,r=s);else if(l<a&&0>i(u,n))e[r]=u,e[l]=n,r=l;else break a}}return t}function i(e,t){var n=e.sortIndex-t.sortIndex;return n===0?e.id-t.id:n}if(e.unstable_now=void 0,typeof performance==`object`&&typeof performance.now==`function`){var a=performance;e.unstable_now=function(){return a.now()}}else{var o=Date,s=o.now();e.unstable_now=function(){return o.now()-s}}var c=[],l=[],u=1,d=null,f=3,p=!1,m=!1,h=!1,g=!1,_=typeof setTimeout==`function`?setTimeout:null,v=typeof clearTimeout==`function`?clearTimeout:null,y=typeof setImmediate<`u`?setImmediate:null;function b(e){for(var i=n(l);i!==null;){if(i.callback===null)r(l);else if(i.startTime<=e)r(l),i.sortIndex=i.expirationTime,t(c,i);else break;i=n(l)}}function x(e){if(h=!1,b(e),!m)if(n(c)!==null)m=!0,ee||(ee=!0,re());else{var t=n(l);t!==null&&oe(x,t.startTime-e)}}var ee=!1,S=-1,C=5,w=-1;function te(){return g?!0:!(e.unstable_now()-w<C)}function ne(){if(g=!1,ee){var t=e.unstable_now();w=t;var i=!0;try{a:{m=!1,h&&(h=!1,v(S),S=-1),p=!0;var a=f;try{b:{for(b(t),d=n(c);d!==null&&!(d.expirationTime>t&&te());){var o=d.callback;if(typeof o==`function`){d.callback=null,f=d.priorityLevel;var s=o(d.expirationTime<=t);if(t=e.unstable_now(),typeof s==`function`){d.callback=s,b(t),i=!0;break b}d===n(c)&&r(c),b(t)}else r(c);d=n(c)}if(d!==null)i=!0;else{var u=n(l);u!==null&&oe(x,u.startTime-t),i=!1}}break a}finally{d=null,f=a,p=!1}i=void 0}}finally{i?re():ee=!1}}}var re;if(typeof y==`function`)re=function(){y(ne)};else if(typeof MessageChannel<`u`){var ie=new MessageChannel,ae=ie.port2;ie.port1.onmessage=ne,re=function(){ae.postMessage(null)}}else re=function(){_(ne,0)};function oe(t,n){S=_(function(){t(e.unstable_now())},n)}e.unstable_IdlePriority=5,e.unstable_ImmediatePriority=1,e.unstable_LowPriority=4,e.unstable_NormalPriority=3,e.unstable_Profiling=null,e.unstable_UserBlockingPriority=2,e.unstable_cancelCallback=function(e){e.callback=null},e.unstable_forceFrameRate=function(e){0>e||125<e?console.error(`forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported`):C=0<e?Math.floor(1e3/e):5},e.unstable_getCurrentPriorityLevel=function(){return f},e.unstable_next=function(e){switch(f){case 1:case 2:case 3:var t=3;break;default:t=f}var n=f;f=t;try{return e()}finally{f=n}},e.unstable_requestPaint=function(){g=!0},e.unstable_runWithPriority=function(e,t){switch(e){case 1:case 2:case 3:case 4:case 5:break;default:e=3}var n=f;f=e;try{return t()}finally{f=n}},e.unstable_scheduleCallback=function(r,i,a){var o=e.unstable_now();switch(typeof a==`object`&&a?(a=a.delay,a=typeof a==`number`&&0<a?o+a:o):a=o,r){case 1:var s=-1;break;case 2:s=250;break;case 5:s=1073741823;break;case 4:s=1e4;break;default:s=5e3}return s=a+s,r={id:u++,callback:i,priorityLevel:r,startTime:a,expirationTime:s,sortIndex:-1},a>o?(r.sortIndex=a,t(l,r),n(c)===null&&r===n(l)&&(h?(v(S),S=-1):h=!0,oe(x,a-o))):(r.sortIndex=s,t(c,r),m||p||(m=!0,ee||(ee=!0,re()))),r},e.unstable_shouldYield=te,e.unstable_wrapCallback=function(e){var t=f;return function(){var n=f;f=t;try{return e.apply(this,arguments)}finally{f=n}}}})),Ze=t(((e,t)=>{t.exports=Xe()})),Qe=t((e=>{var t=f();function n(e){var t=`https://react.dev/errors/`+e;if(1<arguments.length){t+=`?args[]=`+encodeURIComponent(arguments[1]);for(var n=2;n<arguments.length;n++)t+=`&args[]=`+encodeURIComponent(arguments[n])}return`Minified React error #`+e+`; visit `+t+` for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`}function r(){}var i={d:{f:r,r:function(){throw Error(n(522))},D:r,C:r,L:r,m:r,X:r,S:r,M:r},p:0,findDOMNode:null},a=Symbol.for(`react.portal`);function o(e,t,n){var r=3<arguments.length&&arguments[3]!==void 0?arguments[3]:null;return{$$typeof:a,key:r==null?null:``+r,children:e,containerInfo:t,implementation:n}}var s=t.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE;function c(e,t){if(e===`font`)return``;if(typeof t==`string`)return t===`use-credentials`?t:``}e.__DOM_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE=i,e.createPortal=function(e,t){var r=2<arguments.length&&arguments[2]!==void 0?arguments[2]:null;if(!t||t.nodeType!==1&&t.nodeType!==9&&t.nodeType!==11)throw Error(n(299));return o(e,t,null,r)},e.flushSync=function(e){var t=s.T,n=i.p;try{if(s.T=null,i.p=2,e)return e()}finally{s.T=t,i.p=n,i.d.f()}},e.preconnect=function(e,t){typeof e==`string`&&(t?(t=t.crossOrigin,t=typeof t==`string`?t===`use-credentials`?t:``:void 0):t=null,i.d.C(e,t))},e.prefetchDNS=function(e){typeof e==`string`&&i.d.D(e)},e.preinit=function(e,t){if(typeof e==`string`&&t&&typeof t.as==`string`){var n=t.as,r=c(n,t.crossOrigin),a=typeof t.integrity==`string`?t.integrity:void 0,o=typeof t.fetchPriority==`string`?t.fetchPriority:void 0;n===`style`?i.d.S(e,typeof t.precedence==`string`?t.precedence:void 0,{crossOrigin:r,integrity:a,fetchPriority:o}):n===`script`&&i.d.X(e,{crossOrigin:r,integrity:a,fetchPriority:o,nonce:typeof t.nonce==`string`?t.nonce:void 0})}},e.preinitModule=function(e,t){if(typeof e==`string`)if(typeof t==`object`&&t){if(t.as==null||t.as===`script`){var n=c(t.as,t.crossOrigin);i.d.M(e,{crossOrigin:n,integrity:typeof t.integrity==`string`?t.integrity:void 0,nonce:typeof t.nonce==`string`?t.nonce:void 0})}}else t??i.d.M(e)},e.preload=function(e,t){if(typeof e==`string`&&typeof t==`object`&&t&&typeof t.as==`string`){var n=t.as,r=c(n,t.crossOrigin);i.d.L(e,n,{crossOrigin:r,integrity:typeof t.integrity==`string`?t.integrity:void 0,nonce:typeof t.nonce==`string`?t.nonce:void 0,type:typeof t.type==`string`?t.type:void 0,fetchPriority:typeof t.fetchPriority==`string`?t.fetchPriority:void 0,referrerPolicy:typeof t.referrerPolicy==`string`?t.referrerPolicy:void 0,imageSrcSet:typeof t.imageSrcSet==`string`?t.imageSrcSet:void 0,imageSizes:typeof t.imageSizes==`string`?t.imageSizes:void 0,media:typeof t.media==`string`?t.media:void 0})}},e.preloadModule=function(e,t){if(typeof e==`string`)if(t){var n=c(t.as,t.crossOrigin);i.d.m(e,{as:typeof t.as==`string`&&t.as!==`script`?t.as:void 0,crossOrigin:n,integrity:typeof t.integrity==`string`?t.integrity:void 0})}else i.d.m(e)},e.requestFormReset=function(e){i.d.r(e)},e.unstable_batchedUpdates=function(e,t){return e(t)},e.useFormState=function(e,t,n){return s.H.useFormState(e,t,n)},e.useFormStatus=function(){return s.H.useHostTransitionStatus()},e.version=`19.2.7`})),$e=t(((e,t)=>{function n(){if(!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__>`u`||typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE!=`function`))try{__REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(n)}catch(e){console.error(e)}}n(),t.exports=Qe()})),et=t((e=>{var t=Ze(),n=f(),r=$e();function i(e){var t=`https://react.dev/errors/`+e;if(1<arguments.length){t+=`?args[]=`+encodeURIComponent(arguments[1]);for(var n=2;n<arguments.length;n++)t+=`&args[]=`+encodeURIComponent(arguments[n])}return`Minified React error #`+e+`; visit `+t+` for the full message or use the non-minified dev environment for full errors and additional helpful warnings.`}function a(e){return!(!e||e.nodeType!==1&&e.nodeType!==9&&e.nodeType!==11)}function o(e){var t=e,n=e;if(e.alternate)for(;t.return;)t=t.return;else{e=t;do t=e,t.flags&4098&&(n=t.return),e=t.return;while(e)}return t.tag===3?n:null}function s(e){if(e.tag===13){var t=e.memoizedState;if(t===null&&(e=e.alternate,e!==null&&(t=e.memoizedState)),t!==null)return t.dehydrated}return null}function c(e){if(e.tag===31){var t=e.memoizedState;if(t===null&&(e=e.alternate,e!==null&&(t=e.memoizedState)),t!==null)return t.dehydrated}return null}function l(e){if(o(e)!==e)throw Error(i(188))}function u(e){var t=e.alternate;if(!t){if(t=o(e),t===null)throw Error(i(188));return t===e?e:null}for(var n=e,r=t;;){var a=n.return;if(a===null)break;var s=a.alternate;if(s===null){if(r=a.return,r!==null){n=r;continue}break}if(a.child===s.child){for(s=a.child;s;){if(s===n)return l(a),e;if(s===r)return l(a),t;s=s.sibling}throw Error(i(188))}if(n.return!==r.return)n=a,r=s;else{for(var c=!1,u=a.child;u;){if(u===n){c=!0,n=a,r=s;break}if(u===r){c=!0,r=a,n=s;break}u=u.sibling}if(!c){for(u=s.child;u;){if(u===n){c=!0,n=s,r=a;break}if(u===r){c=!0,r=s,n=a;break}u=u.sibling}if(!c)throw Error(i(189))}}if(n.alternate!==r)throw Error(i(190))}if(n.tag!==3)throw Error(i(188));return n.stateNode.current===n?e:t}function d(e){var t=e.tag;if(t===5||t===26||t===27||t===6)return e;for(e=e.child;e!==null;){if(t=d(e),t!==null)return t;e=e.sibling}return null}var p=Object.assign,m=Symbol.for(`react.element`),h=Symbol.for(`react.transitional.element`),g=Symbol.for(`react.portal`),_=Symbol.for(`react.fragment`),v=Symbol.for(`react.strict_mode`),y=Symbol.for(`react.profiler`),b=Symbol.for(`react.consumer`),x=Symbol.for(`react.context`),ee=Symbol.for(`react.forward_ref`),S=Symbol.for(`react.suspense`),C=Symbol.for(`react.suspense_list`),w=Symbol.for(`react.memo`),te=Symbol.for(`react.lazy`),ne=Symbol.for(`react.activity`),re=Symbol.for(`react.memo_cache_sentinel`),ie=Symbol.iterator;function ae(e){return typeof e!=`object`||!e?null:(e=ie&&e[ie]||e[`@@iterator`],typeof e==`function`?e:null)}var oe=Symbol.for(`react.client.reference`);function se(e){if(e==null)return null;if(typeof e==`function`)return e.$$typeof===oe?null:e.displayName||e.name||null;if(typeof e==`string`)return e;switch(e){case _:return`Fragment`;case y:return`Profiler`;case v:return`StrictMode`;case S:return`Suspense`;case C:return`SuspenseList`;case ne:return`Activity`}if(typeof e==`object`)switch(e.$$typeof){case g:return`Portal`;case x:return e.displayName||`Context`;case b:return(e._context.displayName||`Context`)+`.Consumer`;case ee:var t=e.render;return e=e.displayName,e||=(e=t.displayName||t.name||``,e===``?`ForwardRef`:`ForwardRef(`+e+`)`),e;case w:return t=e.displayName||null,t===null?se(e.type)||`Memo`:t;case te:t=e._payload,e=e._init;try{return se(e(t))}catch{}}return null}var T=Array.isArray,E=n.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE,D=r.__DOM_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE,O={pending:!1,data:null,method:null,action:null},ce=[],le=-1;function ue(e){return{current:e}}function de(e){0>le||(e.current=ce[le],ce[le]=null,le--)}function k(e,t){le++,ce[le]=e.current,e.current=t}var fe=ue(null),pe=ue(null),me=ue(null),he=ue(null);function ge(e,t){switch(k(me,t),k(pe,e),k(fe,null),t.nodeType){case 9:case 11:e=(e=t.documentElement)&&(e=e.namespaceURI)?Vd(e):0;break;default:if(e=t.tagName,t=t.namespaceURI)t=Vd(t),e=Hd(t,e);else switch(e){case`svg`:e=1;break;case`math`:e=2;break;default:e=0}}de(fe),k(fe,e)}function _e(){de(fe),de(pe),de(me)}function ve(e){e.memoizedState!==null&&k(he,e);var t=fe.current,n=Hd(t,e.type);t!==n&&(k(pe,e),k(fe,n))}function ye(e){pe.current===e&&(de(fe),de(pe)),he.current===e&&(de(he),Qf._currentValue=O)}var be,xe;function Se(e){if(be===void 0)try{throw Error()}catch(e){var t=e.stack.trim().match(/\n( *(at )?)/);be=t&&t[1]||``,xe=-1<e.stack.indexOf(`
    at`)?` (<anonymous>)`:-1<e.stack.indexOf(`@`)?`@unknown:0:0`:``}return`
`+be+e+xe}var Ce=!1;function we(e,t){if(!e||Ce)return``;Ce=!0;var n=Error.prepareStackTrace;Error.prepareStackTrace=void 0;try{var r={DetermineComponentFrameRoot:function(){try{if(t){var n=function(){throw Error()};if(Object.defineProperty(n.prototype,"props",{set:function(){throw Error()}}),typeof Reflect==`object`&&Reflect.construct){try{Reflect.construct(n,[])}catch(e){var r=e}Reflect.construct(e,[],n)}else{try{n.call()}catch(e){r=e}e.call(n.prototype)}}else{try{throw Error()}catch(e){r=e}(n=e())&&typeof n.catch==`function`&&n.catch(function(){})}}catch(e){if(e&&r&&typeof e.stack==`string`)return[e.stack,r.stack]}return[null,null]}};r.DetermineComponentFrameRoot.displayName=`DetermineComponentFrameRoot`;var i=Object.getOwnPropertyDescriptor(r.DetermineComponentFrameRoot,`name`);i&&i.configurable&&Object.defineProperty(r.DetermineComponentFrameRoot,"name",{value:`DetermineComponentFrameRoot`});var a=r.DetermineComponentFrameRoot(),o=a[0],s=a[1];if(o&&s){var c=o.split(`
`),l=s.split(`
`);for(i=r=0;r<c.length&&!c[r].includes(`DetermineComponentFrameRoot`);)r++;for(;i<l.length&&!l[i].includes(`DetermineComponentFrameRoot`);)i++;if(r===c.length||i===l.length)for(r=c.length-1,i=l.length-1;1<=r&&0<=i&&c[r]!==l[i];)i--;for(;1<=r&&0<=i;r--,i--)if(c[r]!==l[i]){if(r!==1||i!==1)do if(r--,i--,0>i||c[r]!==l[i]){var u=`
`+c[r].replace(` at new `,` at `);return e.displayName&&u.includes(`<anonymous>`)&&(u=u.replace(`<anonymous>`,e.displayName)),u}while(1<=r&&0<=i);break}}}finally{Ce=!1,Error.prepareStackTrace=n}return(n=e?e.displayName||e.name:``)?Se(n):``}function Te(e,t){switch(e.tag){case 26:case 27:case 5:return Se(e.type);case 16:return Se(`Lazy`);case 13:return e.child!==t&&t!==null?Se(`Suspense Fallback`):Se(`Suspense`);case 19:return Se(`SuspenseList`);case 0:case 15:return we(e.type,!1);case 11:return we(e.type.render,!1);case 1:return we(e.type,!0);case 31:return Se(`Activity`);default:return``}}function Ee(e){try{var t=``,n=null;do t+=Te(e,n),n=e,e=e.return;while(e);return t}catch(e){return`
Error generating stack: `+e.message+`
`+e.stack}}var De=Object.prototype.hasOwnProperty,Oe=t.unstable_scheduleCallback,ke=t.unstable_cancelCallback,Ae=t.unstable_shouldYield,je=t.unstable_requestPaint,Me=t.unstable_now,Ne=t.unstable_getCurrentPriorityLevel,Pe=t.unstable_ImmediatePriority,Fe=t.unstable_UserBlockingPriority,Ie=t.unstable_NormalPriority,Le=t.unstable_LowPriority,Re=t.unstable_IdlePriority,ze=t.log,Be=t.unstable_setDisableYieldValue,Ve=null,He=null;function Ue(e){if(typeof ze==`function`&&Be(e),He&&typeof He.setStrictMode==`function`)try{He.setStrictMode(Ve,e)}catch{}}var We=Math.clz32?Math.clz32:qe,Ge=Math.log,Ke=Math.LN2;function qe(e){return e>>>=0,e===0?32:31-(Ge(e)/Ke|0)|0}var Je=256,Ye=262144,Xe=4194304;function Qe(e){var t=e&42;if(t!==0)return t;switch(e&-e){case 1:return 1;case 2:return 2;case 4:return 4;case 8:return 8;case 16:return 16;case 32:return 32;case 64:return 64;case 128:return 128;case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:return e&261888;case 262144:case 524288:case 1048576:case 2097152:return e&3932160;case 4194304:case 8388608:case 16777216:case 33554432:return e&62914560;case 67108864:return 67108864;case 134217728:return 134217728;case 268435456:return 268435456;case 536870912:return 536870912;case 1073741824:return 0;default:return e}}function et(e,t,n){var r=e.pendingLanes;if(r===0)return 0;var i=0,a=e.suspendedLanes,o=e.pingedLanes;e=e.warmLanes;var s=r&134217727;return s===0?(s=r&~a,s===0?o===0?n||(n=r&~e,n!==0&&(i=Qe(n))):i=Qe(o):i=Qe(s)):(r=s&~a,r===0?(o&=s,o===0?n||(n=s&~e,n!==0&&(i=Qe(n))):i=Qe(o)):i=Qe(r)),i===0?0:t!==0&&t!==i&&(t&a)===0&&(a=i&-i,n=t&-t,a>=n||a===32&&n&4194048)?t:i}function tt(e,t){return(e.pendingLanes&~(e.suspendedLanes&~e.pingedLanes)&t)===0}function nt(e,t){switch(e){case 1:case 2:case 4:case 8:case 64:return t+250;case 16:case 32:case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:return t+5e3;case 4194304:case 8388608:case 16777216:case 33554432:return-1;case 67108864:case 134217728:case 268435456:case 536870912:case 1073741824:return-1;default:return-1}}function rt(){var e=Xe;return Xe<<=1,!(Xe&62914560)&&(Xe=4194304),e}function it(e){for(var t=[],n=0;31>n;n++)t.push(e);return t}function at(e,t){e.pendingLanes|=t,t!==268435456&&(e.suspendedLanes=0,e.pingedLanes=0,e.warmLanes=0)}function ot(e,t,n,r,i,a){var o=e.pendingLanes;e.pendingLanes=n,e.suspendedLanes=0,e.pingedLanes=0,e.warmLanes=0,e.expiredLanes&=n,e.entangledLanes&=n,e.errorRecoveryDisabledLanes&=n,e.shellSuspendCounter=0;var s=e.entanglements,c=e.expirationTimes,l=e.hiddenUpdates;for(n=o&~n;0<n;){var u=31-We(n),d=1<<u;s[u]=0,c[u]=-1;var f=l[u];if(f!==null)for(l[u]=null,u=0;u<f.length;u++){var p=f[u];p!==null&&(p.lane&=-536870913)}n&=~d}r!==0&&st(e,r,0),a!==0&&i===0&&e.tag!==0&&(e.suspendedLanes|=a&~(o&~t))}function st(e,t,n){e.pendingLanes|=t,e.suspendedLanes&=~t;var r=31-We(t);e.entangledLanes|=t,e.entanglements[r]=e.entanglements[r]|1073741824|n&261930}function ct(e,t){var n=e.entangledLanes|=t;for(e=e.entanglements;n;){var r=31-We(n),i=1<<r;i&t|e[r]&t&&(e[r]|=t),n&=~i}}function lt(e,t){var n=t&-t;return n=n&42?1:ut(n),(n&(e.suspendedLanes|t))===0?n:0}function ut(e){switch(e){case 2:e=1;break;case 8:e=4;break;case 32:e=16;break;case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:case 4194304:case 8388608:case 16777216:case 33554432:e=128;break;case 268435456:e=134217728;break;default:e=0}return e}function dt(e){return e&=-e,2<e?8<e?e&134217727?32:268435456:8:2}function ft(){var e=D.p;return e===0?(e=window.event,e===void 0?32:mp(e.type)):e}function pt(e,t){var n=D.p;try{return D.p=e,t()}finally{D.p=n}}var mt=Math.random().toString(36).slice(2),ht=`__reactFiber$`+mt,gt=`__reactProps$`+mt,_t=`__reactContainer$`+mt,vt=`__reactEvents$`+mt,yt=`__reactListeners$`+mt,bt=`__reactHandles$`+mt,xt=`__reactResources$`+mt,St=`__reactMarker$`+mt;function Ct(e){delete e[ht],delete e[gt],delete e[vt],delete e[yt],delete e[bt]}function wt(e){var t=e[ht];if(t)return t;for(var n=e.parentNode;n;){if(t=n[_t]||n[ht]){if(n=t.alternate,t.child!==null||n!==null&&n.child!==null)for(e=df(e);e!==null;){if(n=e[ht])return n;e=df(e)}return t}e=n,n=e.parentNode}return null}function Tt(e){if(e=e[ht]||e[_t]){var t=e.tag;if(t===5||t===6||t===13||t===31||t===26||t===27||t===3)return e}return null}function Et(e){var t=e.tag;if(t===5||t===26||t===27||t===6)return e.stateNode;throw Error(i(33))}function Dt(e){var t=e[xt];return t||=e[xt]={hoistableStyles:new Map,hoistableScripts:new Map},t}function Ot(e){e[St]=!0}var kt=new Set,At={};function jt(e,t){Mt(e,t),Mt(e+`Capture`,t)}function Mt(e,t){for(At[e]=t,e=0;e<t.length;e++)kt.add(t[e])}var Nt=RegExp(`^[:A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD][:A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD\\-.0-9\\u00B7\\u0300-\\u036F\\u203F-\\u2040]*$`),Pt={},Ft={};function A(e){return De.call(Ft,e)?!0:De.call(Pt,e)?!1:Nt.test(e)?Ft[e]=!0:(Pt[e]=!0,!1)}function j(e,t,n){if(A(t))if(n===null)e.removeAttribute(t);else{switch(typeof n){case`undefined`:case`function`:case`symbol`:e.removeAttribute(t);return;case`boolean`:var r=t.toLowerCase().slice(0,5);if(r!==`data-`&&r!==`aria-`){e.removeAttribute(t);return}}e.setAttribute(t,``+n)}}function It(e,t,n){if(n===null)e.removeAttribute(t);else{switch(typeof n){case`undefined`:case`function`:case`symbol`:case`boolean`:e.removeAttribute(t);return}e.setAttribute(t,``+n)}}function Lt(e,t,n,r){if(r===null)e.removeAttribute(n);else{switch(typeof r){case`undefined`:case`function`:case`symbol`:case`boolean`:e.removeAttribute(n);return}e.setAttributeNS(t,n,``+r)}}function Rt(e){switch(typeof e){case`bigint`:case`boolean`:case`number`:case`string`:case`undefined`:return e;case`object`:return e;default:return``}}function zt(e){var t=e.type;return(e=e.nodeName)&&e.toLowerCase()===`input`&&(t===`checkbox`||t===`radio`)}function Bt(e,t,n){var r=Object.getOwnPropertyDescriptor(e.constructor.prototype,t);if(!e.hasOwnProperty(t)&&r!==void 0&&typeof r.get==`function`&&typeof r.set==`function`){var i=r.get,a=r.set;return Object.defineProperty(e,t,{configurable:!0,get:function(){return i.call(this)},set:function(e){n=``+e,a.call(this,e)}}),Object.defineProperty(e,t,{enumerable:r.enumerable}),{getValue:function(){return n},setValue:function(e){n=``+e},stopTracking:function(){e._valueTracker=null,delete e[t]}}}}function Vt(e){if(!e._valueTracker){var t=zt(e)?`checked`:`value`;e._valueTracker=Bt(e,t,``+e[t])}}function Ht(e){if(!e)return!1;var t=e._valueTracker;if(!t)return!0;var n=t.getValue(),r=``;return e&&(r=zt(e)?e.checked?`true`:`false`:e.value),e=r,e===n?!1:(t.setValue(e),!0)}function Ut(e){if(e||=typeof document<`u`?document:void 0,e===void 0)return null;try{return e.activeElement||e.body}catch{return e.body}}var Wt=/[\n"\\]/g;function Gt(e){return e.replace(Wt,function(e){return`\\`+e.charCodeAt(0).toString(16)+` `})}function Kt(e,t,n,r,i,a,o,s){e.name=``,o!=null&&typeof o!=`function`&&typeof o!=`symbol`&&typeof o!=`boolean`?e.type=o:e.removeAttribute(`type`),t==null?o!==`submit`&&o!==`reset`||e.removeAttribute(`value`):o===`number`?(t===0&&e.value===``||e.value!=t)&&(e.value=``+Rt(t)):e.value!==``+Rt(t)&&(e.value=``+Rt(t)),t==null?n==null?r!=null&&e.removeAttribute(`value`):Jt(e,o,Rt(n)):Jt(e,o,Rt(t)),i==null&&a!=null&&(e.defaultChecked=!!a),i!=null&&(e.checked=i&&typeof i!=`function`&&typeof i!=`symbol`),s!=null&&typeof s!=`function`&&typeof s!=`symbol`&&typeof s!=`boolean`?e.name=``+Rt(s):e.removeAttribute(`name`)}function qt(e,t,n,r,i,a,o,s){if(a!=null&&typeof a!=`function`&&typeof a!=`symbol`&&typeof a!=`boolean`&&(e.type=a),t!=null||n!=null){if(!(a!==`submit`&&a!==`reset`||t!=null)){Vt(e);return}n=n==null?``:``+Rt(n),t=t==null?n:``+Rt(t),s||t===e.value||(e.value=t),e.defaultValue=t}r??=i,r=typeof r!=`function`&&typeof r!=`symbol`&&!!r,e.checked=s?e.checked:!!r,e.defaultChecked=!!r,o!=null&&typeof o!=`function`&&typeof o!=`symbol`&&typeof o!=`boolean`&&(e.name=o),Vt(e)}function Jt(e,t,n){t===`number`&&Ut(e.ownerDocument)===e||e.defaultValue===``+n||(e.defaultValue=``+n)}function Yt(e,t,n,r){if(e=e.options,t){t={};for(var i=0;i<n.length;i++)t[`$`+n[i]]=!0;for(n=0;n<e.length;n++)i=t.hasOwnProperty(`$`+e[n].value),e[n].selected!==i&&(e[n].selected=i),i&&r&&(e[n].defaultSelected=!0)}else{for(n=``+Rt(n),t=null,i=0;i<e.length;i++){if(e[i].value===n){e[i].selected=!0,r&&(e[i].defaultSelected=!0);return}t!==null||e[i].disabled||(t=e[i])}t!==null&&(t.selected=!0)}}function Xt(e,t,n){if(t!=null&&(t=``+Rt(t),t!==e.value&&(e.value=t),n==null)){e.defaultValue!==t&&(e.defaultValue=t);return}e.defaultValue=n==null?``:``+Rt(n)}function Zt(e,t,n,r){if(t==null){if(r!=null){if(n!=null)throw Error(i(92));if(T(r)){if(1<r.length)throw Error(i(93));r=r[0]}n=r}n??=``,t=n}n=Rt(t),e.defaultValue=n,r=e.textContent,r===n&&r!==``&&r!==null&&(e.value=r),Vt(e)}function Qt(e,t){if(t){var n=e.firstChild;if(n&&n===e.lastChild&&n.nodeType===3){n.nodeValue=t;return}}e.textContent=t}var $t=new Set(`animationIterationCount aspectRatio borderImageOutset borderImageSlice borderImageWidth boxFlex boxFlexGroup boxOrdinalGroup columnCount columns flex flexGrow flexPositive flexShrink flexNegative flexOrder gridArea gridRow gridRowEnd gridRowSpan gridRowStart gridColumn gridColumnEnd gridColumnSpan gridColumnStart fontWeight lineClamp lineHeight opacity order orphans scale tabSize widows zIndex zoom fillOpacity floodOpacity stopOpacity strokeDasharray strokeDashoffset strokeMiterlimit strokeOpacity strokeWidth MozAnimationIterationCount MozBoxFlex MozBoxFlexGroup MozLineClamp msAnimationIterationCount msFlex msZoom msFlexGrow msFlexNegative msFlexOrder msFlexPositive msFlexShrink msGridColumn msGridColumnSpan msGridRow msGridRowSpan WebkitAnimationIterationCount WebkitBoxFlex WebKitBoxFlexGroup WebkitBoxOrdinalGroup WebkitColumnCount WebkitColumns WebkitFlex WebkitFlexGrow WebkitFlexPositive WebkitFlexShrink WebkitLineClamp`.split(` `));function en(e,t,n){var r=t.indexOf(`--`)===0;n==null||typeof n==`boolean`||n===``?r?e.setProperty(t,``):t===`float`?e.cssFloat=``:e[t]=``:r?e.setProperty(t,n):typeof n!=`number`||n===0||$t.has(t)?t===`float`?e.cssFloat=n:e[t]=(``+n).trim():e[t]=n+`px`}function tn(e,t,n){if(t!=null&&typeof t!=`object`)throw Error(i(62));if(e=e.style,n!=null){for(var r in n)!n.hasOwnProperty(r)||t!=null&&t.hasOwnProperty(r)||(r.indexOf(`--`)===0?e.setProperty(r,``):r===`float`?e.cssFloat=``:e[r]=``);for(var a in t)r=t[a],t.hasOwnProperty(a)&&n[a]!==r&&en(e,a,r)}else for(var o in t)t.hasOwnProperty(o)&&en(e,o,t[o])}function nn(e){if(e.indexOf(`-`)===-1)return!1;switch(e){case`annotation-xml`:case`color-profile`:case`font-face`:case`font-face-src`:case`font-face-uri`:case`font-face-format`:case`font-face-name`:case`missing-glyph`:return!1;default:return!0}}var rn=new Map([[`acceptCharset`,`accept-charset`],[`htmlFor`,`for`],[`httpEquiv`,`http-equiv`],[`crossOrigin`,`crossorigin`],[`accentHeight`,`accent-height`],[`alignmentBaseline`,`alignment-baseline`],[`arabicForm`,`arabic-form`],[`baselineShift`,`baseline-shift`],[`capHeight`,`cap-height`],[`clipPath`,`clip-path`],[`clipRule`,`clip-rule`],[`colorInterpolation`,`color-interpolation`],[`colorInterpolationFilters`,`color-interpolation-filters`],[`colorProfile`,`color-profile`],[`colorRendering`,`color-rendering`],[`dominantBaseline`,`dominant-baseline`],[`enableBackground`,`enable-background`],[`fillOpacity`,`fill-opacity`],[`fillRule`,`fill-rule`],[`floodColor`,`flood-color`],[`floodOpacity`,`flood-opacity`],[`fontFamily`,`font-family`],[`fontSize`,`font-size`],[`fontSizeAdjust`,`font-size-adjust`],[`fontStretch`,`font-stretch`],[`fontStyle`,`font-style`],[`fontVariant`,`font-variant`],[`fontWeight`,`font-weight`],[`glyphName`,`glyph-name`],[`glyphOrientationHorizontal`,`glyph-orientation-horizontal`],[`glyphOrientationVertical`,`glyph-orientation-vertical`],[`horizAdvX`,`horiz-adv-x`],[`horizOriginX`,`horiz-origin-x`],[`imageRendering`,`image-rendering`],[`letterSpacing`,`letter-spacing`],[`lightingColor`,`lighting-color`],[`markerEnd`,`marker-end`],[`markerMid`,`marker-mid`],[`markerStart`,`marker-start`],[`overlinePosition`,`overline-position`],[`overlineThickness`,`overline-thickness`],[`paintOrder`,`paint-order`],[`panose-1`,`panose-1`],[`pointerEvents`,`pointer-events`],[`renderingIntent`,`rendering-intent`],[`shapeRendering`,`shape-rendering`],[`stopColor`,`stop-color`],[`stopOpacity`,`stop-opacity`],[`strikethroughPosition`,`strikethrough-position`],[`strikethroughThickness`,`strikethrough-thickness`],[`strokeDasharray`,`stroke-dasharray`],[`strokeDashoffset`,`stroke-dashoffset`],[`strokeLinecap`,`stroke-linecap`],[`strokeLinejoin`,`stroke-linejoin`],[`strokeMiterlimit`,`stroke-miterlimit`],[`strokeOpacity`,`stroke-opacity`],[`strokeWidth`,`stroke-width`],[`textAnchor`,`text-anchor`],[`textDecoration`,`text-decoration`],[`textRendering`,`text-rendering`],[`transformOrigin`,`transform-origin`],[`underlinePosition`,`underline-position`],[`underlineThickness`,`underline-thickness`],[`unicodeBidi`,`unicode-bidi`],[`unicodeRange`,`unicode-range`],[`unitsPerEm`,`units-per-em`],[`vAlphabetic`,`v-alphabetic`],[`vHanging`,`v-hanging`],[`vIdeographic`,`v-ideographic`],[`vMathematical`,`v-mathematical`],[`vectorEffect`,`vector-effect`],[`vertAdvY`,`vert-adv-y`],[`vertOriginX`,`vert-origin-x`],[`vertOriginY`,`vert-origin-y`],[`wordSpacing`,`word-spacing`],[`writingMode`,`writing-mode`],[`xmlnsXlink`,`xmlns:xlink`],[`xHeight`,`x-height`]]),an=/^[\u0000-\u001F ]*j[\r\n\t]*a[\r\n\t]*v[\r\n\t]*a[\r\n\t]*s[\r\n\t]*c[\r\n\t]*r[\r\n\t]*i[\r\n\t]*p[\r\n\t]*t[\r\n\t]*:/i;function on(e){return an.test(``+e)?`javascript:throw new Error('React has blocked a javascript: URL as a security precaution.')`:e}function sn(){}var cn=null;function ln(e){return e=e.target||e.srcElement||window,e.correspondingUseElement&&(e=e.correspondingUseElement),e.nodeType===3?e.parentNode:e}var un=null,dn=null;function fn(e){var t=Tt(e);if(t&&(e=t.stateNode)){var n=e[gt]||null;a:switch(e=t.stateNode,t.type){case`input`:if(Kt(e,n.value,n.defaultValue,n.defaultValue,n.checked,n.defaultChecked,n.type,n.name),t=n.name,n.type===`radio`&&t!=null){for(n=e;n.parentNode;)n=n.parentNode;for(n=n.querySelectorAll(`input[name="`+Gt(``+t)+`"][type="radio"]`),t=0;t<n.length;t++){var r=n[t];if(r!==e&&r.form===e.form){var a=r[gt]||null;if(!a)throw Error(i(90));Kt(r,a.value,a.defaultValue,a.defaultValue,a.checked,a.defaultChecked,a.type,a.name)}}for(t=0;t<n.length;t++)r=n[t],r.form===e.form&&Ht(r)}break a;case`textarea`:Xt(e,n.value,n.defaultValue);break a;case`select`:t=n.value,t!=null&&Yt(e,!!n.multiple,t,!1)}}}var pn=!1;function mn(e,t,n){if(pn)return e(t,n);pn=!0;try{return e(t)}finally{if(pn=!1,(un!==null||dn!==null)&&(bu(),un&&(t=un,e=dn,dn=un=null,fn(t),e)))for(t=0;t<e.length;t++)fn(e[t])}}function hn(e,t){var n=e.stateNode;if(n===null)return null;var r=n[gt]||null;if(r===null)return null;n=r[t];a:switch(t){case`onClick`:case`onClickCapture`:case`onDoubleClick`:case`onDoubleClickCapture`:case`onMouseDown`:case`onMouseDownCapture`:case`onMouseMove`:case`onMouseMoveCapture`:case`onMouseUp`:case`onMouseUpCapture`:case`onMouseEnter`:(r=!r.disabled)||(e=e.type,r=!(e===`button`||e===`input`||e===`select`||e===`textarea`)),e=!r;break a;default:e=!1}if(e)return null;if(n&&typeof n!=`function`)throw Error(i(231,t,typeof n));return n}var gn=!(typeof window>`u`||window.document===void 0||window.document.createElement===void 0),_n=!1;if(gn)try{var vn={};Object.defineProperty(vn,"passive",{get:function(){_n=!0}}),window.addEventListener(`test`,vn,vn),window.removeEventListener(`test`,vn,vn)}catch{_n=!1}var yn=null,bn=null,xn=null;function Sn(){if(xn)return xn;var e,t=bn,n=t.length,r,i=`value`in yn?yn.value:yn.textContent,a=i.length;for(e=0;e<n&&t[e]===i[e];e++);var o=n-e;for(r=1;r<=o&&t[n-r]===i[a-r];r++);return xn=i.slice(e,1<r?1-r:void 0)}function Cn(e){var t=e.keyCode;return`charCode`in e?(e=e.charCode,e===0&&t===13&&(e=13)):e=t,e===10&&(e=13),32<=e||e===13?e:0}function wn(){return!0}function Tn(){return!1}function En(e){function t(t,n,r,i,a){for(var o in this._reactName=t,this._targetInst=r,this.type=n,this.nativeEvent=i,this.target=a,this.currentTarget=null,e)e.hasOwnProperty(o)&&(t=e[o],this[o]=t?t(i):i[o]);return this.isDefaultPrevented=(i.defaultPrevented==null?!1===i.returnValue:i.defaultPrevented)?wn:Tn,this.isPropagationStopped=Tn,this}return p(t.prototype,{preventDefault:function(){this.defaultPrevented=!0;var e=this.nativeEvent;e&&(e.preventDefault?e.preventDefault():typeof e.returnValue!=`unknown`&&(e.returnValue=!1),this.isDefaultPrevented=wn)},stopPropagation:function(){var e=this.nativeEvent;e&&(e.stopPropagation?e.stopPropagation():typeof e.cancelBubble!=`unknown`&&(e.cancelBubble=!0),this.isPropagationStopped=wn)},persist:function(){},isPersistent:wn}),t}var Dn={eventPhase:0,bubbles:0,cancelable:0,timeStamp:function(e){return e.timeStamp||Date.now()},defaultPrevented:0,isTrusted:0},On=En(Dn),kn=p({},Dn,{view:0,detail:0}),An=En(kn),jn,Mn,Nn,M=p({},kn,{screenX:0,screenY:0,clientX:0,clientY:0,pageX:0,pageY:0,ctrlKey:0,shiftKey:0,altKey:0,metaKey:0,getModifierState:Wn,button:0,buttons:0,relatedTarget:function(e){return e.relatedTarget===void 0?e.fromElement===e.srcElement?e.toElement:e.fromElement:e.relatedTarget},movementX:function(e){return`movementX`in e?e.movementX:(e!==Nn&&(Nn&&e.type===`mousemove`?(jn=e.screenX-Nn.screenX,Mn=e.screenY-Nn.screenY):Mn=jn=0,Nn=e),jn)},movementY:function(e){return`movementY`in e?e.movementY:Mn}}),Pn=En(M),Fn=En(p({},M,{dataTransfer:0})),In=En(p({},kn,{relatedTarget:0})),Ln=En(p({},Dn,{animationName:0,elapsedTime:0,pseudoElement:0})),Rn=En(p({},Dn,{clipboardData:function(e){return`clipboardData`in e?e.clipboardData:window.clipboardData}})),zn=En(p({},Dn,{data:0})),Bn={Esc:`Escape`,Spacebar:` `,Left:`ArrowLeft`,Up:`ArrowUp`,Right:`ArrowRight`,Down:`ArrowDown`,Del:`Delete`,Win:`OS`,Menu:`ContextMenu`,Apps:`ContextMenu`,Scroll:`ScrollLock`,MozPrintableKey:`Unidentified`},Vn={8:`Backspace`,9:`Tab`,12:`Clear`,13:`Enter`,16:`Shift`,17:`Control`,18:`Alt`,19:`Pause`,20:`CapsLock`,27:`Escape`,32:` `,33:`PageUp`,34:`PageDown`,35:`End`,36:`Home`,37:`ArrowLeft`,38:`ArrowUp`,39:`ArrowRight`,40:`ArrowDown`,45:`Insert`,46:`Delete`,112:`F1`,113:`F2`,114:`F3`,115:`F4`,116:`F5`,117:`F6`,118:`F7`,119:`F8`,120:`F9`,121:`F10`,122:`F11`,123:`F12`,144:`NumLock`,145:`ScrollLock`,224:`Meta`},Hn={Alt:`altKey`,Control:`ctrlKey`,Meta:`metaKey`,Shift:`shiftKey`};function Un(e){var t=this.nativeEvent;return t.getModifierState?t.getModifierState(e):(e=Hn[e])?!!t[e]:!1}function Wn(){return Un}var N=En(p({},kn,{key:function(e){if(e.key){var t=Bn[e.key]||e.key;if(t!==`Unidentified`)return t}return e.type===`keypress`?(e=Cn(e),e===13?`Enter`:String.fromCharCode(e)):e.type===`keydown`||e.type===`keyup`?Vn[e.keyCode]||`Unidentified`:``},code:0,location:0,ctrlKey:0,shiftKey:0,altKey:0,metaKey:0,repeat:0,locale:0,getModifierState:Wn,charCode:function(e){return e.type===`keypress`?Cn(e):0},keyCode:function(e){return e.type===`keydown`||e.type===`keyup`?e.keyCode:0},which:function(e){return e.type===`keypress`?Cn(e):e.type===`keydown`||e.type===`keyup`?e.keyCode:0}})),Gn=En(p({},M,{pointerId:0,width:0,height:0,pressure:0,tangentialPressure:0,tiltX:0,tiltY:0,twist:0,pointerType:0,isPrimary:0})),Kn=En(p({},kn,{touches:0,targetTouches:0,changedTouches:0,altKey:0,metaKey:0,ctrlKey:0,shiftKey:0,getModifierState:Wn})),qn=En(p({},Dn,{propertyName:0,elapsedTime:0,pseudoElement:0})),Jn=En(p({},M,{deltaX:function(e){return`deltaX`in e?e.deltaX:`wheelDeltaX`in e?-e.wheelDeltaX:0},deltaY:function(e){return`deltaY`in e?e.deltaY:`wheelDeltaY`in e?-e.wheelDeltaY:`wheelDelta`in e?-e.wheelDelta:0},deltaZ:0,deltaMode:0})),Yn=En(p({},Dn,{newState:0,oldState:0})),Xn=[9,13,27,32],Zn=gn&&`CompositionEvent`in window,P=null;gn&&`documentMode`in document&&(P=document.documentMode);var Qn=gn&&`TextEvent`in window&&!P,$n=gn&&(!Zn||P&&8<P&&11>=P),er=` `,tr=!1;function nr(e,t){switch(e){case`keyup`:return Xn.indexOf(t.keyCode)!==-1;case`keydown`:return t.keyCode!==229;case`keypress`:case`mousedown`:case`focusout`:return!0;default:return!1}}function rr(e){return e=e.detail,typeof e==`object`&&`data`in e?e.data:null}var ir=!1;function ar(e,t){switch(e){case`compositionend`:return rr(t);case`keypress`:return t.which===32?(tr=!0,er):null;case`textInput`:return e=t.data,e===er&&tr?null:e;default:return null}}function or(e,t){if(ir)return e===`compositionend`||!Zn&&nr(e,t)?(e=Sn(),xn=bn=yn=null,ir=!1,e):null;switch(e){case`paste`:return null;case`keypress`:if(!(t.ctrlKey||t.altKey||t.metaKey)||t.ctrlKey&&t.altKey){if(t.char&&1<t.char.length)return t.char;if(t.which)return String.fromCharCode(t.which)}return null;case`compositionend`:return $n&&t.locale!==`ko`?null:t.data;default:return null}}var sr={color:!0,date:!0,datetime:!0,"datetime-local":!0,email:!0,month:!0,number:!0,password:!0,range:!0,search:!0,tel:!0,text:!0,time:!0,url:!0,week:!0};function cr(e){var t=e&&e.nodeName&&e.nodeName.toLowerCase();return t===`input`?!!sr[e.type]:t===`textarea`}function lr(e,t,n,r){un?dn?dn.push(r):dn=[r]:un=r,t=Ed(t,`onChange`),0<t.length&&(n=new On(`onChange`,`change`,null,n,r),e.push({event:n,listeners:t}))}var ur=null,dr=null;function fr(e){yd(e,0)}function pr(e){if(Ht(Et(e)))return e}function mr(e,t){if(e===`change`)return t}var hr=!1;if(gn){var gr;if(gn){var _r=`oninput`in document;if(!_r){var vr=document.createElement(`div`);vr.setAttribute(`oninput`,`return;`),_r=typeof vr.oninput==`function`}gr=_r}else gr=!1;hr=gr&&(!document.documentMode||9<document.documentMode)}function yr(){ur&&(ur.detachEvent(`onpropertychange`,br),dr=ur=null)}function br(e){if(e.propertyName===`value`&&pr(dr)){var t=[];lr(t,dr,e,ln(e)),mn(fr,t)}}function xr(e,t,n){e===`focusin`?(yr(),ur=t,dr=n,ur.attachEvent(`onpropertychange`,br)):e===`focusout`&&yr()}function Sr(e){if(e===`selectionchange`||e===`keyup`||e===`keydown`)return pr(dr)}function Cr(e,t){if(e===`click`)return pr(t)}function wr(e,t){if(e===`input`||e===`change`)return pr(t)}function Tr(e,t){return e===t&&(e!==0||1/e==1/t)||e!==e&&t!==t}var Er=typeof Object.is==`function`?Object.is:Tr;function Dr(e,t){if(Er(e,t))return!0;if(typeof e!=`object`||!e||typeof t!=`object`||!t)return!1;var n=Object.keys(e),r=Object.keys(t);if(n.length!==r.length)return!1;for(r=0;r<n.length;r++){var i=n[r];if(!De.call(t,i)||!Er(e[i],t[i]))return!1}return!0}function Or(e){for(;e&&e.firstChild;)e=e.firstChild;return e}function kr(e,t){var n=Or(e);e=0;for(var r;n;){if(n.nodeType===3){if(r=e+n.textContent.length,e<=t&&r>=t)return{node:n,offset:t-e};e=r}a:{for(;n;){if(n.nextSibling){n=n.nextSibling;break a}n=n.parentNode}n=void 0}n=Or(n)}}function Ar(e,t){return e&&t?e===t?!0:e&&e.nodeType===3?!1:t&&t.nodeType===3?Ar(e,t.parentNode):`contains`in e?e.contains(t):e.compareDocumentPosition?!!(e.compareDocumentPosition(t)&16):!1:!1}function jr(e){e=e!=null&&e.ownerDocument!=null&&e.ownerDocument.defaultView!=null?e.ownerDocument.defaultView:window;for(var t=Ut(e.document);t instanceof e.HTMLIFrameElement;){try{var n=typeof t.contentWindow.location.href==`string`}catch{n=!1}if(n)e=t.contentWindow;else break;t=Ut(e.document)}return t}function Mr(e){var t=e&&e.nodeName&&e.nodeName.toLowerCase();return t&&(t===`input`&&(e.type===`text`||e.type===`search`||e.type===`tel`||e.type===`url`||e.type===`password`)||t===`textarea`||e.contentEditable===`true`)}var Nr=gn&&`documentMode`in document&&11>=document.documentMode,Pr=null,Fr=null,Ir=null,Lr=!1;function Rr(e,t,n){var r=n.window===n?n.document:n.nodeType===9?n:n.ownerDocument;Lr||Pr==null||Pr!==Ut(r)||(r=Pr,`selectionStart`in r&&Mr(r)?r={start:r.selectionStart,end:r.selectionEnd}:(r=(r.ownerDocument&&r.ownerDocument.defaultView||window).getSelection(),r={anchorNode:r.anchorNode,anchorOffset:r.anchorOffset,focusNode:r.focusNode,focusOffset:r.focusOffset}),Ir&&Dr(Ir,r)||(Ir=r,r=Ed(Fr,`onSelect`),0<r.length&&(t=new On(`onSelect`,`select`,null,t,n),e.push({event:t,listeners:r}),t.target=Pr)))}function zr(e,t){var n={};return n[e.toLowerCase()]=t.toLowerCase(),n[`Webkit`+e]=`webkit`+t,n[`Moz`+e]=`moz`+t,n}var Br={animationend:zr(`Animation`,`AnimationEnd`),animationiteration:zr(`Animation`,`AnimationIteration`),animationstart:zr(`Animation`,`AnimationStart`),transitionrun:zr(`Transition`,`TransitionRun`),transitionstart:zr(`Transition`,`TransitionStart`),transitioncancel:zr(`Transition`,`TransitionCancel`),transitionend:zr(`Transition`,`TransitionEnd`)},Vr={},Hr={};gn&&(Hr=document.createElement(`div`).style,`AnimationEvent`in window||(delete Br.animationend.animation,delete Br.animationiteration.animation,delete Br.animationstart.animation),`TransitionEvent`in window||delete Br.transitionend.transition);function Ur(e){if(Vr[e])return Vr[e];if(!Br[e])return e;var t=Br[e],n;for(n in t)if(t.hasOwnProperty(n)&&n in Hr)return Vr[e]=t[n];return e}var Wr=Ur(`animationend`),Gr=Ur(`animationiteration`),Kr=Ur(`animationstart`),qr=Ur(`transitionrun`),Jr=Ur(`transitionstart`),Yr=Ur(`transitioncancel`),F=Ur(`transitionend`),Xr=new Map,Zr=`abort auxClick beforeToggle cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel`.split(` `);Zr.push(`scrollEnd`);function Qr(e,t){Xr.set(e,t),jt(t,[e])}var $r=typeof reportError==`function`?reportError:function(e){if(typeof window==`object`&&typeof window.ErrorEvent==`function`){var t=new window.ErrorEvent(`error`,{bubbles:!0,cancelable:!0,message:typeof e==`object`&&e&&typeof e.message==`string`?String(e.message):String(e),error:e});if(!window.dispatchEvent(t))return}else if(typeof process==`object`&&typeof process.emit==`function`){process.emit(`uncaughtException`,e);return}console.error(e)},ei=[],ti=0,ni=0;function ri(){for(var e=ti,t=ni=ti=0;t<e;){var n=ei[t];ei[t++]=null;var r=ei[t];ei[t++]=null;var i=ei[t];ei[t++]=null;var a=ei[t];if(ei[t++]=null,r!==null&&i!==null){var o=r.pending;o===null?i.next=i:(i.next=o.next,o.next=i),r.pending=i}a!==0&&si(n,i,a)}}function ii(e,t,n,r){ei[ti++]=e,ei[ti++]=t,ei[ti++]=n,ei[ti++]=r,ni|=r,e.lanes|=r,e=e.alternate,e!==null&&(e.lanes|=r)}function ai(e,t,n,r){return ii(e,t,n,r),ci(e)}function oi(e,t){return ii(e,null,null,t),ci(e)}function si(e,t,n){e.lanes|=n;var r=e.alternate;r!==null&&(r.lanes|=n);for(var i=!1,a=e.return;a!==null;)a.childLanes|=n,r=a.alternate,r!==null&&(r.childLanes|=n),a.tag===22&&(e=a.stateNode,e===null||e._visibility&1||(i=!0)),e=a,a=a.return;return e.tag===3?(a=e.stateNode,i&&t!==null&&(i=31-We(n),e=a.hiddenUpdates,r=e[i],r===null?e[i]=[t]:r.push(t),t.lane=n|536870912),a):null}function ci(e){if(50<du)throw du=0,fu=null,Error(i(185));for(var t=e.return;t!==null;)e=t,t=e.return;return e.tag===3?e.stateNode:null}var li={};function ui(e,t,n,r){this.tag=e,this.key=n,this.sibling=this.child=this.return=this.stateNode=this.type=this.elementType=null,this.index=0,this.refCleanup=this.ref=null,this.pendingProps=t,this.dependencies=this.memoizedState=this.updateQueue=this.memoizedProps=null,this.mode=r,this.subtreeFlags=this.flags=0,this.deletions=null,this.childLanes=this.lanes=0,this.alternate=null}function di(e,t,n,r){return new ui(e,t,n,r)}function fi(e){return e=e.prototype,!(!e||!e.isReactComponent)}function pi(e,t){var n=e.alternate;return n===null?(n=di(e.tag,t,e.key,e.mode),n.elementType=e.elementType,n.type=e.type,n.stateNode=e.stateNode,n.alternate=e,e.alternate=n):(n.pendingProps=t,n.type=e.type,n.flags=0,n.subtreeFlags=0,n.deletions=null),n.flags=e.flags&65011712,n.childLanes=e.childLanes,n.lanes=e.lanes,n.child=e.child,n.memoizedProps=e.memoizedProps,n.memoizedState=e.memoizedState,n.updateQueue=e.updateQueue,t=e.dependencies,n.dependencies=t===null?null:{lanes:t.lanes,firstContext:t.firstContext},n.sibling=e.sibling,n.index=e.index,n.ref=e.ref,n.refCleanup=e.refCleanup,n}function mi(e,t){e.flags&=65011714;var n=e.alternate;return n===null?(e.childLanes=0,e.lanes=t,e.child=null,e.subtreeFlags=0,e.memoizedProps=null,e.memoizedState=null,e.updateQueue=null,e.dependencies=null,e.stateNode=null):(e.childLanes=n.childLanes,e.lanes=n.lanes,e.child=n.child,e.subtreeFlags=0,e.deletions=null,e.memoizedProps=n.memoizedProps,e.memoizedState=n.memoizedState,e.updateQueue=n.updateQueue,e.type=n.type,t=n.dependencies,e.dependencies=t===null?null:{lanes:t.lanes,firstContext:t.firstContext}),e}function hi(e,t,n,r,a,o){var s=0;if(r=e,typeof e==`function`)fi(e)&&(s=1);else if(typeof e==`string`)s=Uf(e,n,fe.current)?26:e===`html`||e===`head`||e===`body`?27:5;else a:switch(e){case ne:return e=di(31,n,t,a),e.elementType=ne,e.lanes=o,e;case _:return gi(n.children,a,o,t);case v:s=8,a|=24;break;case y:return e=di(12,n,t,a|2),e.elementType=y,e.lanes=o,e;case S:return e=di(13,n,t,a),e.elementType=S,e.lanes=o,e;case C:return e=di(19,n,t,a),e.elementType=C,e.lanes=o,e;default:if(typeof e==`object`&&e)switch(e.$$typeof){case x:s=10;break a;case b:s=9;break a;case ee:s=11;break a;case w:s=14;break a;case te:s=16,r=null;break a}s=29,n=Error(i(130,e===null?`null`:typeof e,``)),r=null}return t=di(s,n,t,a),t.elementType=e,t.type=r,t.lanes=o,t}function gi(e,t,n,r){return e=di(7,e,r,t),e.lanes=n,e}function _i(e,t,n){return e=di(6,e,null,t),e.lanes=n,e}function vi(e){var t=di(18,null,null,0);return t.stateNode=e,t}function yi(e,t,n){return t=di(4,e.children===null?[]:e.children,e.key,t),t.lanes=n,t.stateNode={containerInfo:e.containerInfo,pendingChildren:null,implementation:e.implementation},t}var bi=new WeakMap;function xi(e,t){if(typeof e==`object`&&e){var n=bi.get(e);return n===void 0?(t={value:e,source:t,stack:Ee(t)},bi.set(e,t),t):n}return{value:e,source:t,stack:Ee(t)}}var Si=[],Ci=0,wi=null,Ti=0,Ei=[],Di=0,Oi=null,ki=1,Ai=``;function ji(e,t){Si[Ci++]=Ti,Si[Ci++]=wi,wi=e,Ti=t}function Mi(e,t,n){Ei[Di++]=ki,Ei[Di++]=Ai,Ei[Di++]=Oi,Oi=e;var r=ki;e=Ai;var i=32-We(r)-1;r&=~(1<<i),n+=1;var a=32-We(t)+i;if(30<a){var o=i-i%5;a=(r&(1<<o)-1).toString(32),r>>=o,i-=o,ki=1<<32-We(t)+i|n<<i|r,Ai=a+e}else ki=1<<a|n<<i|r,Ai=e}function Ni(e){e.return!==null&&(ji(e,1),Mi(e,1,0))}function Pi(e){for(;e===wi;)wi=Si[--Ci],Si[Ci]=null,Ti=Si[--Ci],Si[Ci]=null;for(;e===Oi;)Oi=Ei[--Di],Ei[Di]=null,Ai=Ei[--Di],Ei[Di]=null,ki=Ei[--Di],Ei[Di]=null}function Fi(e,t){Ei[Di++]=ki,Ei[Di++]=Ai,Ei[Di++]=Oi,ki=t.id,Ai=t.overflow,Oi=e}var Ii=null,I=null,L=!1,Li=null,Ri=!1,zi=Error(i(519));function Bi(e){throw Ki(xi(Error(i(418,1<arguments.length&&arguments[1]!==void 0&&arguments[1]?`text`:`HTML`,``)),e)),zi}function Vi(e){var t=e.stateNode,n=e.type,r=e.memoizedProps;switch(t[ht]=e,t[gt]=r,n){case`dialog`:Q(`cancel`,t),Q(`close`,t);break;case`iframe`:case`object`:case`embed`:Q(`load`,t);break;case`video`:case`audio`:for(n=0;n<_d.length;n++)Q(_d[n],t);break;case`source`:Q(`error`,t);break;case`img`:case`image`:case`link`:Q(`error`,t),Q(`load`,t);break;case`details`:Q(`toggle`,t);break;case`input`:Q(`invalid`,t),qt(t,r.value,r.defaultValue,r.checked,r.defaultChecked,r.type,r.name,!0);break;case`select`:Q(`invalid`,t);break;case`textarea`:Q(`invalid`,t),Zt(t,r.value,r.defaultValue,r.children)}n=r.children,typeof n!=`string`&&typeof n!=`number`&&typeof n!=`bigint`||t.textContent===``+n||!0===r.suppressHydrationWarning||Md(t.textContent,n)?(r.popover!=null&&(Q(`beforetoggle`,t),Q(`toggle`,t)),r.onScroll!=null&&Q(`scroll`,t),r.onScrollEnd!=null&&Q(`scrollend`,t),r.onClick!=null&&(t.onclick=sn),t=!0):t=!1,t||Bi(e,!0)}function Hi(e){for(Ii=e.return;Ii;)switch(Ii.tag){case 5:case 31:case 13:Ri=!1;return;case 27:case 3:Ri=!0;return;default:Ii=Ii.return}}function Ui(e){if(e!==Ii)return!1;if(!L)return Hi(e),L=!0,!1;var t=e.tag,n;if((n=t!==3&&t!==27)&&((n=t===5)&&(n=e.type,n=!(n!==`form`&&n!==`button`)||Ud(e.type,e.memoizedProps)),n=!n),n&&I&&Bi(e),Hi(e),t===13){if(e=e.memoizedState,e=e===null?null:e.dehydrated,!e)throw Error(i(317));I=uf(e)}else if(t===31){if(e=e.memoizedState,e=e===null?null:e.dehydrated,!e)throw Error(i(317));I=uf(e)}else t===27?(t=I,Zd(e.type)?(e=lf,lf=null,I=e):I=t):I=Ii?cf(e.stateNode.nextSibling):null;return!0}function Wi(){I=Ii=null,L=!1}function Gi(){var e=Li;return e!==null&&(Zl===null?Zl=e:Zl.push.apply(Zl,e),Li=null),e}function Ki(e){Li===null?Li=[e]:Li.push(e)}var qi=ue(null),Ji=null,R=null;function Yi(e,t,n){k(qi,t._currentValue),t._currentValue=n}function Xi(e){e._currentValue=qi.current,de(qi)}function Zi(e,t,n){for(;e!==null;){var r=e.alternate;if((e.childLanes&t)===t?r!==null&&(r.childLanes&t)!==t&&(r.childLanes|=t):(e.childLanes|=t,r!==null&&(r.childLanes|=t)),e===n)break;e=e.return}}function Qi(e,t,n,r){var a=e.child;for(a!==null&&(a.return=e);a!==null;){var o=a.dependencies;if(o!==null){var s=a.child;o=o.firstContext;a:for(;o!==null;){var c=o;o=a;for(var l=0;l<t.length;l++)if(c.context===t[l]){o.lanes|=n,c=o.alternate,c!==null&&(c.lanes|=n),Zi(o.return,n,e),r||(s=null);break a}o=c.next}}else if(a.tag===18){if(s=a.return,s===null)throw Error(i(341));s.lanes|=n,o=s.alternate,o!==null&&(o.lanes|=n),Zi(s,n,e),s=null}else s=a.child;if(s!==null)s.return=a;else for(s=a;s!==null;){if(s===e){s=null;break}if(a=s.sibling,a!==null){a.return=s.return,s=a;break}s=s.return}a=s}}function $i(e,t,n,r){e=null;for(var a=t,o=!1;a!==null;){if(!o){if(a.flags&524288)o=!0;else if(a.flags&262144)break}if(a.tag===10){var s=a.alternate;if(s===null)throw Error(i(387));if(s=s.memoizedProps,s!==null){var c=a.type;Er(a.pendingProps.value,s.value)||(e===null?e=[c]:e.push(c))}}else if(a===he.current){if(s=a.alternate,s===null)throw Error(i(387));s.memoizedState.memoizedState!==a.memoizedState.memoizedState&&(e===null?e=[Qf]:e.push(Qf))}a=a.return}e!==null&&Qi(t,e,n,r),t.flags|=262144}function ea(e){for(e=e.firstContext;e!==null;){if(!Er(e.context._currentValue,e.memoizedValue))return!0;e=e.next}return!1}function ta(e){Ji=e,R=null,e=e.dependencies,e!==null&&(e.firstContext=null)}function na(e){return ia(Ji,e)}function ra(e,t){return Ji===null&&ta(e),ia(e,t)}function ia(e,t){var n=t._currentValue;if(t={context:t,memoizedValue:n,next:null},R===null){if(e===null)throw Error(i(308));R=t,e.dependencies={lanes:0,firstContext:t},e.flags|=524288}else R=R.next=t;return n}var aa=typeof AbortController<`u`?AbortController:function(){var e=[],t=this.signal={aborted:!1,addEventListener:function(t,n){e.push(n)}};this.abort=function(){t.aborted=!0,e.forEach(function(e){return e()})}},oa=t.unstable_scheduleCallback,sa=t.unstable_NormalPriority,ca={$$typeof:x,Consumer:null,Provider:null,_currentValue:null,_currentValue2:null,_threadCount:0};function la(){return{controller:new aa,data:new Map,refCount:0}}function ua(e){e.refCount--,e.refCount===0&&oa(sa,function(){e.controller.abort()})}var da=null,fa=0,pa=0,ma=null;function ha(e,t){if(da===null){var n=da=[];fa=0,pa=dd(),ma={status:`pending`,value:void 0,then:function(e){n.push(e)}}}return fa++,t.then(ga,ga),t}function ga(){if(--fa===0&&da!==null){ma!==null&&(ma.status=`fulfilled`);var e=da;da=null,pa=0,ma=null;for(var t=0;t<e.length;t++)(0,e[t])()}}function _a(e,t){var n=[],r={status:`pending`,value:null,reason:null,then:function(e){n.push(e)}};return e.then(function(){r.status=`fulfilled`,r.value=t;for(var e=0;e<n.length;e++)(0,n[e])(t)},function(e){for(r.status=`rejected`,r.reason=e,e=0;e<n.length;e++)(0,n[e])(void 0)}),r}var va=E.S;E.S=function(e,t){eu=Me(),typeof t==`object`&&t&&typeof t.then==`function`&&ha(e,t),va!==null&&va(e,t)};var ya=ue(null);function ba(){var e=ya.current;return e===null?q.pooledCache:e}function xa(e,t){t===null?k(ya,ya.current):k(ya,t.pool)}function Sa(){var e=ba();return e===null?null:{parent:ca._currentValue,pool:e}}var Ca=Error(i(460)),wa=Error(i(474)),Ta=Error(i(542)),Ea={then:function(){}};function Da(e){return e=e.status,e===`fulfilled`||e===`rejected`}function Oa(e,t,n){switch(n=e[n],n===void 0?e.push(t):n!==t&&(t.then(sn,sn),t=n),t.status){case`fulfilled`:return t.value;case`rejected`:throw e=t.reason,Ma(e),e;default:if(typeof t.status==`string`)t.then(sn,sn);else{if(e=q,e!==null&&100<e.shellSuspendCounter)throw Error(i(482));e=t,e.status=`pending`,e.then(function(e){if(t.status===`pending`){var n=t;n.status=`fulfilled`,n.value=e}},function(e){if(t.status===`pending`){var n=t;n.status=`rejected`,n.reason=e}})}switch(t.status){case`fulfilled`:return t.value;case`rejected`:throw e=t.reason,Ma(e),e}throw Aa=t,Ca}}function ka(e){try{var t=e._init;return t(e._payload)}catch(e){throw typeof e==`object`&&e&&typeof e.then==`function`?(Aa=e,Ca):e}}var Aa=null;function ja(){if(Aa===null)throw Error(i(459));var e=Aa;return Aa=null,e}function Ma(e){if(e===Ca||e===Ta)throw Error(i(483))}var Na=null,Pa=0;function Fa(e){var t=Pa;return Pa+=1,Na===null&&(Na=[]),Oa(Na,e,t)}function Ia(e,t){t=t.props.ref,e.ref=t===void 0?null:t}function La(e,t){throw t.$$typeof===m?Error(i(525)):(e=Object.prototype.toString.call(t),Error(i(31,e===`[object Object]`?`object with keys {`+Object.keys(t).join(`, `)+`}`:e)))}function Ra(e){function t(t,n){if(e){var r=t.deletions;r===null?(t.deletions=[n],t.flags|=16):r.push(n)}}function n(n,r){if(!e)return null;for(;r!==null;)t(n,r),r=r.sibling;return null}function r(e){for(var t=new Map;e!==null;)e.key===null?t.set(e.index,e):t.set(e.key,e),e=e.sibling;return t}function a(e,t){return e=pi(e,t),e.index=0,e.sibling=null,e}function o(t,n,r){return t.index=r,e?(r=t.alternate,r===null?(t.flags|=67108866,n):(r=r.index,r<n?(t.flags|=67108866,n):r)):(t.flags|=1048576,n)}function s(t){return e&&t.alternate===null&&(t.flags|=67108866),t}function c(e,t,n,r){return t===null||t.tag!==6?(t=_i(n,e.mode,r),t.return=e,t):(t=a(t,n),t.return=e,t)}function l(e,t,n,r){var i=n.type;return i===_?d(e,t,n.props.children,r,n.key):t!==null&&(t.elementType===i||typeof i==`object`&&i&&i.$$typeof===te&&ka(i)===t.type)?(t=a(t,n.props),Ia(t,n),t.return=e,t):(t=hi(n.type,n.key,n.props,null,e.mode,r),Ia(t,n),t.return=e,t)}function u(e,t,n,r){return t===null||t.tag!==4||t.stateNode.containerInfo!==n.containerInfo||t.stateNode.implementation!==n.implementation?(t=yi(n,e.mode,r),t.return=e,t):(t=a(t,n.children||[]),t.return=e,t)}function d(e,t,n,r,i){return t===null||t.tag!==7?(t=gi(n,e.mode,r,i),t.return=e,t):(t=a(t,n),t.return=e,t)}function f(e,t,n){if(typeof t==`string`&&t!==``||typeof t==`number`||typeof t==`bigint`)return t=_i(``+t,e.mode,n),t.return=e,t;if(typeof t==`object`&&t){switch(t.$$typeof){case h:return n=hi(t.type,t.key,t.props,null,e.mode,n),Ia(n,t),n.return=e,n;case g:return t=yi(t,e.mode,n),t.return=e,t;case te:return t=ka(t),f(e,t,n)}if(T(t)||ae(t))return t=gi(t,e.mode,n,null),t.return=e,t;if(typeof t.then==`function`)return f(e,Fa(t),n);if(t.$$typeof===x)return f(e,ra(e,t),n);La(e,t)}return null}function p(e,t,n,r){var i=t===null?null:t.key;if(typeof n==`string`&&n!==``||typeof n==`number`||typeof n==`bigint`)return i===null?c(e,t,``+n,r):null;if(typeof n==`object`&&n){switch(n.$$typeof){case h:return n.key===i?l(e,t,n,r):null;case g:return n.key===i?u(e,t,n,r):null;case te:return n=ka(n),p(e,t,n,r)}if(T(n)||ae(n))return i===null?d(e,t,n,r,null):null;if(typeof n.then==`function`)return p(e,t,Fa(n),r);if(n.$$typeof===x)return p(e,t,ra(e,n),r);La(e,n)}return null}function m(e,t,n,r,i){if(typeof r==`string`&&r!==``||typeof r==`number`||typeof r==`bigint`)return e=e.get(n)||null,c(t,e,``+r,i);if(typeof r==`object`&&r){switch(r.$$typeof){case h:return e=e.get(r.key===null?n:r.key)||null,l(t,e,r,i);case g:return e=e.get(r.key===null?n:r.key)||null,u(t,e,r,i);case te:return r=ka(r),m(e,t,n,r,i)}if(T(r)||ae(r))return e=e.get(n)||null,d(t,e,r,i,null);if(typeof r.then==`function`)return m(e,t,n,Fa(r),i);if(r.$$typeof===x)return m(e,t,n,ra(t,r),i);La(t,r)}return null}function v(i,a,s,c){for(var l=null,u=null,d=a,h=a=0,g=null;d!==null&&h<s.length;h++){d.index>h?(g=d,d=null):g=d.sibling;var _=p(i,d,s[h],c);if(_===null){d===null&&(d=g);break}e&&d&&_.alternate===null&&t(i,d),a=o(_,a,h),u===null?l=_:u.sibling=_,u=_,d=g}if(h===s.length)return n(i,d),L&&ji(i,h),l;if(d===null){for(;h<s.length;h++)d=f(i,s[h],c),d!==null&&(a=o(d,a,h),u===null?l=d:u.sibling=d,u=d);return L&&ji(i,h),l}for(d=r(d);h<s.length;h++)g=m(d,i,h,s[h],c),g!==null&&(e&&g.alternate!==null&&d.delete(g.key===null?h:g.key),a=o(g,a,h),u===null?l=g:u.sibling=g,u=g);return e&&d.forEach(function(e){return t(i,e)}),L&&ji(i,h),l}function y(a,s,c,l){if(c==null)throw Error(i(151));for(var u=null,d=null,h=s,g=s=0,_=null,v=c.next();h!==null&&!v.done;g++,v=c.next()){h.index>g?(_=h,h=null):_=h.sibling;var y=p(a,h,v.value,l);if(y===null){h===null&&(h=_);break}e&&h&&y.alternate===null&&t(a,h),s=o(y,s,g),d===null?u=y:d.sibling=y,d=y,h=_}if(v.done)return n(a,h),L&&ji(a,g),u;if(h===null){for(;!v.done;g++,v=c.next())v=f(a,v.value,l),v!==null&&(s=o(v,s,g),d===null?u=v:d.sibling=v,d=v);return L&&ji(a,g),u}for(h=r(h);!v.done;g++,v=c.next())v=m(h,a,g,v.value,l),v!==null&&(e&&v.alternate!==null&&h.delete(v.key===null?g:v.key),s=o(v,s,g),d===null?u=v:d.sibling=v,d=v);return e&&h.forEach(function(e){return t(a,e)}),L&&ji(a,g),u}function b(e,r,o,c){if(typeof o==`object`&&o&&o.type===_&&o.key===null&&(o=o.props.children),typeof o==`object`&&o){switch(o.$$typeof){case h:a:{for(var l=o.key;r!==null;){if(r.key===l){if(l=o.type,l===_){if(r.tag===7){n(e,r.sibling),c=a(r,o.props.children),c.return=e,e=c;break a}}else if(r.elementType===l||typeof l==`object`&&l&&l.$$typeof===te&&ka(l)===r.type){n(e,r.sibling),c=a(r,o.props),Ia(c,o),c.return=e,e=c;break a}n(e,r);break}else t(e,r);r=r.sibling}o.type===_?(c=gi(o.props.children,e.mode,c,o.key),c.return=e,e=c):(c=hi(o.type,o.key,o.props,null,e.mode,c),Ia(c,o),c.return=e,e=c)}return s(e);case g:a:{for(l=o.key;r!==null;){if(r.key===l)if(r.tag===4&&r.stateNode.containerInfo===o.containerInfo&&r.stateNode.implementation===o.implementation){n(e,r.sibling),c=a(r,o.children||[]),c.return=e,e=c;break a}else{n(e,r);break}else t(e,r);r=r.sibling}c=yi(o,e.mode,c),c.return=e,e=c}return s(e);case te:return o=ka(o),b(e,r,o,c)}if(T(o))return v(e,r,o,c);if(ae(o)){if(l=ae(o),typeof l!=`function`)throw Error(i(150));return o=l.call(o),y(e,r,o,c)}if(typeof o.then==`function`)return b(e,r,Fa(o),c);if(o.$$typeof===x)return b(e,r,ra(e,o),c);La(e,o)}return typeof o==`string`&&o!==``||typeof o==`number`||typeof o==`bigint`?(o=``+o,r!==null&&r.tag===6?(n(e,r.sibling),c=a(r,o),c.return=e,e=c):(n(e,r),c=_i(o,e.mode,c),c.return=e,e=c),s(e)):n(e,r)}return function(e,t,n,r){try{Pa=0;var i=b(e,t,n,r);return Na=null,i}catch(t){if(t===Ca||t===Ta)throw t;var a=di(29,t,null,e.mode);return a.lanes=r,a.return=e,a}}}var za=Ra(!0),Ba=Ra(!1),Va=!1;function Ha(e){e.updateQueue={baseState:e.memoizedState,firstBaseUpdate:null,lastBaseUpdate:null,shared:{pending:null,lanes:0,hiddenCallbacks:null},callbacks:null}}function Ua(e,t){e=e.updateQueue,t.updateQueue===e&&(t.updateQueue={baseState:e.baseState,firstBaseUpdate:e.firstBaseUpdate,lastBaseUpdate:e.lastBaseUpdate,shared:e.shared,callbacks:null})}function Wa(e){return{lane:e,tag:0,payload:null,callback:null,next:null}}function Ga(e,t,n){var r=e.updateQueue;if(r===null)return null;if(r=r.shared,K&2){var i=r.pending;return i===null?t.next=t:(t.next=i.next,i.next=t),r.pending=t,t=ci(e),si(e,null,n),t}return ii(e,r,t,n),ci(e)}function Ka(e,t,n){if(t=t.updateQueue,t!==null&&(t=t.shared,n&4194048)){var r=t.lanes;r&=e.pendingLanes,n|=r,t.lanes=n,ct(e,n)}}function qa(e,t){var n=e.updateQueue,r=e.alternate;if(r!==null&&(r=r.updateQueue,n===r)){var i=null,a=null;if(n=n.firstBaseUpdate,n!==null){do{var o={lane:n.lane,tag:n.tag,payload:n.payload,callback:null,next:null};a===null?i=a=o:a=a.next=o,n=n.next}while(n!==null);a===null?i=a=t:a=a.next=t}else i=a=t;n={baseState:r.baseState,firstBaseUpdate:i,lastBaseUpdate:a,shared:r.shared,callbacks:r.callbacks},e.updateQueue=n;return}e=n.lastBaseUpdate,e===null?n.firstBaseUpdate=t:e.next=t,n.lastBaseUpdate=t}var Ja=!1;function Ya(){if(Ja){var e=ma;if(e!==null)throw e}}function Xa(e,t,n,r){Ja=!1;var i=e.updateQueue;Va=!1;var a=i.firstBaseUpdate,o=i.lastBaseUpdate,s=i.shared.pending;if(s!==null){i.shared.pending=null;var c=s,l=c.next;c.next=null,o===null?a=l:o.next=l,o=c;var u=e.alternate;u!==null&&(u=u.updateQueue,s=u.lastBaseUpdate,s!==o&&(s===null?u.firstBaseUpdate=l:s.next=l,u.lastBaseUpdate=c))}if(a!==null){var d=i.baseState;o=0,u=l=c=null,s=a;do{var f=s.lane&-536870913,m=f!==s.lane;if(m?(Y&f)===f:(r&f)===f){f!==0&&f===pa&&(Ja=!0),u!==null&&(u=u.next={lane:0,tag:s.tag,payload:s.payload,callback:null,next:null});a:{var h=e,g=s;f=t;var _=n;switch(g.tag){case 1:if(h=g.payload,typeof h==`function`){d=h.call(_,d,f);break a}d=h;break a;case 3:h.flags=h.flags&-65537|128;case 0:if(h=g.payload,f=typeof h==`function`?h.call(_,d,f):h,f==null)break a;d=p({},d,f);break a;case 2:Va=!0}}f=s.callback,f!==null&&(e.flags|=64,m&&(e.flags|=8192),m=i.callbacks,m===null?i.callbacks=[f]:m.push(f))}else m={lane:f,tag:s.tag,payload:s.payload,callback:s.callback,next:null},u===null?(l=u=m,c=d):u=u.next=m,o|=f;if(s=s.next,s===null){if(s=i.shared.pending,s===null)break;m=s,s=m.next,m.next=null,i.lastBaseUpdate=m,i.shared.pending=null}}while(1);u===null&&(c=d),i.baseState=c,i.firstBaseUpdate=l,i.lastBaseUpdate=u,a===null&&(i.shared.lanes=0),Gl|=o,e.lanes=o,e.memoizedState=d}}function Za(e,t){if(typeof e!=`function`)throw Error(i(191,e));e.call(t)}function Qa(e,t){var n=e.callbacks;if(n!==null)for(e.callbacks=null,e=0;e<n.length;e++)Za(n[e],t)}var $a=ue(null),eo=ue(0);function to(e,t){e=Ul,k(eo,e),k($a,t),Ul=e|t.baseLanes}function no(){k(eo,Ul),k($a,$a.current)}function ro(){Ul=eo.current,de($a),de(eo)}var io=ue(null),ao=null;function oo(e){var t=e.alternate;k(z,z.current&1),k(io,e),ao===null&&(t===null||$a.current!==null||t.memoizedState!==null)&&(ao=e)}function so(e){k(z,z.current),k(io,e),ao===null&&(ao=e)}function co(e){e.tag===22?(k(z,z.current),k(io,e),ao===null&&(ao=e)):lo(e)}function lo(){k(z,z.current),k(io,io.current)}function uo(e){de(io),ao===e&&(ao=null),de(z)}var z=ue(0);function fo(e){for(var t=e;t!==null;){if(t.tag===13){var n=t.memoizedState;if(n!==null&&(n=n.dehydrated,n===null||af(n)||of(n)))return t}else if(t.tag===19&&(t.memoizedProps.revealOrder===`forwards`||t.memoizedProps.revealOrder===`backwards`||t.memoizedProps.revealOrder===`unstable_legacy-backwards`||t.memoizedProps.revealOrder===`together`)){if(t.flags&128)return t}else if(t.child!==null){t.child.return=t,t=t.child;continue}if(t===e)break;for(;t.sibling===null;){if(t.return===null||t.return===e)return null;t=t.return}t.sibling.return=t.return,t=t.sibling}return null}var po=0,B=null,V=null,H=null,mo=!1,ho=!1,go=!1,_o=0,vo=0,yo=null,bo=0;function U(){throw Error(i(321))}function xo(e,t){if(t===null)return!1;for(var n=0;n<t.length&&n<e.length;n++)if(!Er(e[n],t[n]))return!1;return!0}function So(e,t,n,r,i,a){return po=a,B=t,t.memoizedState=null,t.updateQueue=null,t.lanes=0,E.H=e===null||e.memoizedState===null?zs:Bs,go=!1,a=n(r,i),go=!1,ho&&(a=wo(t,n,r,i)),Co(e),a}function Co(e){E.H=Rs;var t=V!==null&&V.next!==null;if(po=0,H=V=B=null,mo=!1,vo=0,yo=null,t)throw Error(i(300));e===null||rc||(e=e.dependencies,e!==null&&ea(e)&&(rc=!0))}function wo(e,t,n,r){B=e;var a=0;do{if(ho&&(yo=null),vo=0,ho=!1,25<=a)throw Error(i(301));if(a+=1,H=V=null,e.updateQueue!=null){var o=e.updateQueue;o.lastEffect=null,o.events=null,o.stores=null,o.memoCache!=null&&(o.memoCache.index=0)}E.H=Vs,o=t(n,r)}while(ho);return o}function To(){var e=E.H,t=e.useState()[0];return t=typeof t.then==`function`?Mo(t):t,e=e.useState()[0],(V===null?null:V.memoizedState)!==e&&(B.flags|=1024),t}function Eo(){var e=_o!==0;return _o=0,e}function Do(e,t,n){t.updateQueue=e.updateQueue,t.flags&=-2053,e.lanes&=~n}function Oo(e){if(mo){for(e=e.memoizedState;e!==null;){var t=e.queue;t!==null&&(t.pending=null),e=e.next}mo=!1}po=0,H=V=B=null,ho=!1,vo=_o=0,yo=null}function ko(){var e={memoizedState:null,baseState:null,baseQueue:null,queue:null,next:null};return H===null?B.memoizedState=H=e:H=H.next=e,H}function Ao(){if(V===null){var e=B.alternate;e=e===null?null:e.memoizedState}else e=V.next;var t=H===null?B.memoizedState:H.next;if(t!==null)H=t,V=e;else{if(e===null)throw B.alternate===null?Error(i(467)):Error(i(310));V=e,e={memoizedState:V.memoizedState,baseState:V.baseState,baseQueue:V.baseQueue,queue:V.queue,next:null},H===null?B.memoizedState=H=e:H=H.next=e}return H}function jo(){return{lastEffect:null,events:null,stores:null,memoCache:null}}function Mo(e){var t=vo;return vo+=1,yo===null&&(yo=[]),e=Oa(yo,e,t),t=B,(H===null?t.memoizedState:H.next)===null&&(t=t.alternate,E.H=t===null||t.memoizedState===null?zs:Bs),e}function No(e){if(typeof e==`object`&&e){if(typeof e.then==`function`)return Mo(e);if(e.$$typeof===x)return na(e)}throw Error(i(438,String(e)))}function Po(e){var t=null,n=B.updateQueue;if(n!==null&&(t=n.memoCache),t==null){var r=B.alternate;r!==null&&(r=r.updateQueue,r!==null&&(r=r.memoCache,r!=null&&(t={data:r.data.map(function(e){return e.slice()}),index:0})))}if(t??={data:[],index:0},n===null&&(n=jo(),B.updateQueue=n),n.memoCache=t,n=t.data[t.index],n===void 0)for(n=t.data[t.index]=Array(e),r=0;r<e;r++)n[r]=re;return t.index++,n}function Fo(e,t){return typeof t==`function`?t(e):t}function Io(e){return Lo(Ao(),V,e)}function Lo(e,t,n){var r=e.queue;if(r===null)throw Error(i(311));r.lastRenderedReducer=n;var a=e.baseQueue,o=r.pending;if(o!==null){if(a!==null){var s=a.next;a.next=o.next,o.next=s}t.baseQueue=a=o,r.pending=null}if(o=e.baseState,a===null)e.memoizedState=o;else{t=a.next;var c=s=null,l=null,u=t,d=!1;do{var f=u.lane&-536870913;if(f===u.lane?(po&f)===f:(Y&f)===f){var p=u.revertLane;if(p===0)l!==null&&(l=l.next={lane:0,revertLane:0,gesture:null,action:u.action,hasEagerState:u.hasEagerState,eagerState:u.eagerState,next:null}),f===pa&&(d=!0);else if((po&p)===p){u=u.next,p===pa&&(d=!0);continue}else f={lane:0,revertLane:u.revertLane,gesture:null,action:u.action,hasEagerState:u.hasEagerState,eagerState:u.eagerState,next:null},l===null?(c=l=f,s=o):l=l.next=f,B.lanes|=p,Gl|=p;f=u.action,go&&n(o,f),o=u.hasEagerState?u.eagerState:n(o,f)}else p={lane:f,revertLane:u.revertLane,gesture:u.gesture,action:u.action,hasEagerState:u.hasEagerState,eagerState:u.eagerState,next:null},l===null?(c=l=p,s=o):l=l.next=p,B.lanes|=f,Gl|=f;u=u.next}while(u!==null&&u!==t);if(l===null?s=o:l.next=c,!Er(o,e.memoizedState)&&(rc=!0,d&&(n=ma,n!==null)))throw n;e.memoizedState=o,e.baseState=s,e.baseQueue=l,r.lastRenderedState=o}return a===null&&(r.lanes=0),[e.memoizedState,r.dispatch]}function Ro(e){var t=Ao(),n=t.queue;if(n===null)throw Error(i(311));n.lastRenderedReducer=e;var r=n.dispatch,a=n.pending,o=t.memoizedState;if(a!==null){n.pending=null;var s=a=a.next;do o=e(o,s.action),s=s.next;while(s!==a);Er(o,t.memoizedState)||(rc=!0),t.memoizedState=o,t.baseQueue===null&&(t.baseState=o),n.lastRenderedState=o}return[o,r]}function zo(e,t,n){var r=B,a=Ao(),o=L;if(o){if(n===void 0)throw Error(i(407));n=n()}else n=t();var s=!Er((V||a).memoizedState,n);if(s&&(a.memoizedState=n,rc=!0),a=a.queue,us(Ho.bind(null,r,a,e),[e]),a.getSnapshot!==t||s||H!==null&&H.memoizedState.tag&1){if(r.flags|=2048,as(9,{destroy:void 0},Vo.bind(null,r,a,n,t),null),q===null)throw Error(i(349));o||po&127||Bo(r,t,n)}return n}function Bo(e,t,n){e.flags|=16384,e={getSnapshot:t,value:n},t=B.updateQueue,t===null?(t=jo(),B.updateQueue=t,t.stores=[e]):(n=t.stores,n===null?t.stores=[e]:n.push(e))}function Vo(e,t,n,r){t.value=n,t.getSnapshot=r,Uo(t)&&Wo(e)}function Ho(e,t,n){return n(function(){Uo(t)&&Wo(e)})}function Uo(e){var t=e.getSnapshot;e=e.value;try{var n=t();return!Er(e,n)}catch{return!0}}function Wo(e){var t=oi(e,2);t!==null&&hu(t,e,2)}function Go(e){var t=ko();if(typeof e==`function`){var n=e;if(e=n(),go){Ue(!0);try{n()}finally{Ue(!1)}}}return t.memoizedState=t.baseState=e,t.queue={pending:null,lanes:0,dispatch:null,lastRenderedReducer:Fo,lastRenderedState:e},t}function Ko(e,t,n,r){return e.baseState=n,Lo(e,V,typeof r==`function`?r:Fo)}function qo(e,t,n,r,a){if(Fs(e))throw Error(i(485));if(e=t.action,e!==null){var o={payload:a,action:e,next:null,isTransition:!0,status:`pending`,value:null,reason:null,listeners:[],then:function(e){o.listeners.push(e)}};E.T===null?o.isTransition=!1:n(!0),r(o),n=t.pending,n===null?(o.next=t.pending=o,Jo(t,o)):(o.next=n.next,t.pending=n.next=o)}}function Jo(e,t){var n=t.action,r=t.payload,i=e.state;if(t.isTransition){var a=E.T,o={};E.T=o;try{var s=n(i,r),c=E.S;c!==null&&c(o,s),Yo(e,t,s)}catch(n){Zo(e,t,n)}finally{a!==null&&o.types!==null&&(a.types=o.types),E.T=a}}else try{a=n(i,r),Yo(e,t,a)}catch(n){Zo(e,t,n)}}function Yo(e,t,n){typeof n==`object`&&n&&typeof n.then==`function`?n.then(function(n){Xo(e,t,n)},function(n){return Zo(e,t,n)}):Xo(e,t,n)}function Xo(e,t,n){t.status=`fulfilled`,t.value=n,Qo(t),e.state=n,t=e.pending,t!==null&&(n=t.next,n===t?e.pending=null:(n=n.next,t.next=n,Jo(e,n)))}function Zo(e,t,n){var r=e.pending;if(e.pending=null,r!==null){r=r.next;do t.status=`rejected`,t.reason=n,Qo(t),t=t.next;while(t!==r)}e.action=null}function Qo(e){e=e.listeners;for(var t=0;t<e.length;t++)(0,e[t])()}function $o(e,t){return t}function es(e,t){if(L){var n=q.formState;if(n!==null){a:{var r=B;if(L){if(I){b:{for(var i=I,a=Ri;i.nodeType!==8;){if(!a){i=null;break b}if(i=cf(i.nextSibling),i===null){i=null;break b}}a=i.data,i=a===`F!`||a===`F`?i:null}if(i){I=cf(i.nextSibling),r=i.data===`F!`;break a}}Bi(r)}r=!1}r&&(t=n[0])}}return n=ko(),n.memoizedState=n.baseState=t,r={pending:null,lanes:0,dispatch:null,lastRenderedReducer:$o,lastRenderedState:t},n.queue=r,n=Ms.bind(null,B,r),r.dispatch=n,r=Go(!1),a=Ps.bind(null,B,!1,r.queue),r=ko(),i={state:t,dispatch:null,action:e,pending:null},r.queue=i,n=qo.bind(null,B,i,a,n),i.dispatch=n,r.memoizedState=e,[t,n,!1]}function ts(e){return ns(Ao(),V,e)}function ns(e,t,n){if(t=Lo(e,t,$o)[0],e=Io(Fo)[0],typeof t==`object`&&t&&typeof t.then==`function`)try{var r=Mo(t)}catch(e){throw e===Ca?Ta:e}else r=t;t=Ao();var i=t.queue,a=i.dispatch;return n!==t.memoizedState&&(B.flags|=2048,as(9,{destroy:void 0},rs.bind(null,i,n),null)),[r,a,e]}function rs(e,t){e.action=t}function is(e){var t=Ao(),n=V;if(n!==null)return ns(t,n,e);Ao(),t=t.memoizedState,n=Ao();var r=n.queue.dispatch;return n.memoizedState=e,[t,r,!1]}function as(e,t,n,r){return e={tag:e,create:n,deps:r,inst:t,next:null},t=B.updateQueue,t===null&&(t=jo(),B.updateQueue=t),n=t.lastEffect,n===null?t.lastEffect=e.next=e:(r=n.next,n.next=e,e.next=r,t.lastEffect=e),e}function os(){return Ao().memoizedState}function ss(e,t,n,r){var i=ko();B.flags|=e,i.memoizedState=as(1|t,{destroy:void 0},n,r===void 0?null:r)}function cs(e,t,n,r){var i=Ao();r=r===void 0?null:r;var a=i.memoizedState.inst;V!==null&&r!==null&&xo(r,V.memoizedState.deps)?i.memoizedState=as(t,a,n,r):(B.flags|=e,i.memoizedState=as(1|t,a,n,r))}function ls(e,t){ss(8390656,8,e,t)}function us(e,t){cs(2048,8,e,t)}function ds(e){B.flags|=4;var t=B.updateQueue;if(t===null)t=jo(),B.updateQueue=t,t.events=[e];else{var n=t.events;n===null?t.events=[e]:n.push(e)}}function fs(e){var t=Ao().memoizedState;return ds({ref:t,nextImpl:e}),function(){if(K&2)throw Error(i(440));return t.impl.apply(void 0,arguments)}}function ps(e,t){return cs(4,2,e,t)}function ms(e,t){return cs(4,4,e,t)}function hs(e,t){if(typeof t==`function`){e=e();var n=t(e);return function(){typeof n==`function`?n():t(null)}}if(t!=null)return e=e(),t.current=e,function(){t.current=null}}function gs(e,t,n){n=n==null?null:n.concat([e]),cs(4,4,hs.bind(null,t,e),n)}function _s(){}function vs(e,t){var n=Ao();t=t===void 0?null:t;var r=n.memoizedState;return t!==null&&xo(t,r[1])?r[0]:(n.memoizedState=[e,t],e)}function ys(e,t){var n=Ao();t=t===void 0?null:t;var r=n.memoizedState;if(t!==null&&xo(t,r[1]))return r[0];if(r=e(),go){Ue(!0);try{e()}finally{Ue(!1)}}return n.memoizedState=[r,t],r}function bs(e,t,n){return n===void 0||po&1073741824&&!(Y&261930)?e.memoizedState=t:(e.memoizedState=n,e=mu(),B.lanes|=e,Gl|=e,n)}function xs(e,t,n,r){return Er(n,t)?n:$a.current===null?!(po&42)||po&1073741824&&!(Y&261930)?(rc=!0,e.memoizedState=n):(e=mu(),B.lanes|=e,Gl|=e,t):(e=bs(e,n,r),Er(e,t)||(rc=!0),e)}function Ss(e,t,n,r,i){var a=D.p;D.p=a!==0&&8>a?a:8;var o=E.T,s={};E.T=s,Ps(e,!1,t,n);try{var c=i(),l=E.S;l!==null&&l(s,c),typeof c==`object`&&c&&typeof c.then==`function`?Ns(e,t,_a(c,r),pu(e)):Ns(e,t,r,pu(e))}catch(n){Ns(e,t,{then:function(){},status:`rejected`,reason:n},pu())}finally{D.p=a,o!==null&&s.types!==null&&(o.types=s.types),E.T=o}}function Cs(){}function ws(e,t,n,r){if(e.tag!==5)throw Error(i(476));var a=Ts(e).queue;Ss(e,a,t,O,n===null?Cs:function(){return Es(e),n(r)})}function Ts(e){var t=e.memoizedState;if(t!==null)return t;t={memoizedState:O,baseState:O,baseQueue:null,queue:{pending:null,lanes:0,dispatch:null,lastRenderedReducer:Fo,lastRenderedState:O},next:null};var n={};return t.next={memoizedState:n,baseState:n,baseQueue:null,queue:{pending:null,lanes:0,dispatch:null,lastRenderedReducer:Fo,lastRenderedState:n},next:null},e.memoizedState=t,e=e.alternate,e!==null&&(e.memoizedState=t),t}function Es(e){var t=Ts(e);t.next===null&&(t=e.alternate.memoizedState),Ns(e,t.next.queue,{},pu())}function Ds(){return na(Qf)}function Os(){return Ao().memoizedState}function ks(){return Ao().memoizedState}function As(e){for(var t=e.return;t!==null;){switch(t.tag){case 24:case 3:var n=pu();e=Wa(n);var r=Ga(t,e,n);r!==null&&(hu(r,t,n),Ka(r,t,n)),t={cache:la()},e.payload=t;return}t=t.return}}function js(e,t,n){var r=pu();n={lane:r,revertLane:0,gesture:null,action:n,hasEagerState:!1,eagerState:null,next:null},Fs(e)?Is(t,n):(n=ai(e,t,n,r),n!==null&&(hu(n,e,r),Ls(n,t,r)))}function Ms(e,t,n){Ns(e,t,n,pu())}function Ns(e,t,n,r){var i={lane:r,revertLane:0,gesture:null,action:n,hasEagerState:!1,eagerState:null,next:null};if(Fs(e))Is(t,i);else{var a=e.alternate;if(e.lanes===0&&(a===null||a.lanes===0)&&(a=t.lastRenderedReducer,a!==null))try{var o=t.lastRenderedState,s=a(o,n);if(i.hasEagerState=!0,i.eagerState=s,Er(s,o))return ii(e,t,i,0),q===null&&ri(),!1}catch{}if(n=ai(e,t,i,r),n!==null)return hu(n,e,r),Ls(n,t,r),!0}return!1}function Ps(e,t,n,r){if(r={lane:2,revertLane:dd(),gesture:null,action:r,hasEagerState:!1,eagerState:null,next:null},Fs(e)){if(t)throw Error(i(479))}else t=ai(e,n,r,2),t!==null&&hu(t,e,2)}function Fs(e){var t=e.alternate;return e===B||t!==null&&t===B}function Is(e,t){ho=mo=!0;var n=e.pending;n===null?t.next=t:(t.next=n.next,n.next=t),e.pending=t}function Ls(e,t,n){if(n&4194048){var r=t.lanes;r&=e.pendingLanes,n|=r,t.lanes=n,ct(e,n)}}var Rs={readContext:na,use:No,useCallback:U,useContext:U,useEffect:U,useImperativeHandle:U,useLayoutEffect:U,useInsertionEffect:U,useMemo:U,useReducer:U,useRef:U,useState:U,useDebugValue:U,useDeferredValue:U,useTransition:U,useSyncExternalStore:U,useId:U,useHostTransitionStatus:U,useFormState:U,useActionState:U,useOptimistic:U,useMemoCache:U,useCacheRefresh:U};Rs.useEffectEvent=U;var zs={readContext:na,use:No,useCallback:function(e,t){return ko().memoizedState=[e,t===void 0?null:t],e},useContext:na,useEffect:ls,useImperativeHandle:function(e,t,n){n=n==null?null:n.concat([e]),ss(4194308,4,hs.bind(null,t,e),n)},useLayoutEffect:function(e,t){return ss(4194308,4,e,t)},useInsertionEffect:function(e,t){ss(4,2,e,t)},useMemo:function(e,t){var n=ko();t=t===void 0?null:t;var r=e();if(go){Ue(!0);try{e()}finally{Ue(!1)}}return n.memoizedState=[r,t],r},useReducer:function(e,t,n){var r=ko();if(n!==void 0){var i=n(t);if(go){Ue(!0);try{n(t)}finally{Ue(!1)}}}else i=t;return r.memoizedState=r.baseState=i,e={pending:null,lanes:0,dispatch:null,lastRenderedReducer:e,lastRenderedState:i},r.queue=e,e=e.dispatch=js.bind(null,B,e),[r.memoizedState,e]},useRef:function(e){var t=ko();return e={current:e},t.memoizedState=e},useState:function(e){e=Go(e);var t=e.queue,n=Ms.bind(null,B,t);return t.dispatch=n,[e.memoizedState,n]},useDebugValue:_s,useDeferredValue:function(e,t){return bs(ko(),e,t)},useTransition:function(){var e=Go(!1);return e=Ss.bind(null,B,e.queue,!0,!1),ko().memoizedState=e,[!1,e]},useSyncExternalStore:function(e,t,n){var r=B,a=ko();if(L){if(n===void 0)throw Error(i(407));n=n()}else{if(n=t(),q===null)throw Error(i(349));Y&127||Bo(r,t,n)}a.memoizedState=n;var o={value:n,getSnapshot:t};return a.queue=o,ls(Ho.bind(null,r,o,e),[e]),r.flags|=2048,as(9,{destroy:void 0},Vo.bind(null,r,o,n,t),null),n},useId:function(){var e=ko(),t=q.identifierPrefix;if(L){var n=Ai,r=ki;n=(r&~(1<<32-We(r)-1)).toString(32)+n,t=`_`+t+`R_`+n,n=_o++,0<n&&(t+=`H`+n.toString(32)),t+=`_`}else n=bo++,t=`_`+t+`r_`+n.toString(32)+`_`;return e.memoizedState=t},useHostTransitionStatus:Ds,useFormState:es,useActionState:es,useOptimistic:function(e){var t=ko();t.memoizedState=t.baseState=e;var n={pending:null,lanes:0,dispatch:null,lastRenderedReducer:null,lastRenderedState:null};return t.queue=n,t=Ps.bind(null,B,!0,n),n.dispatch=t,[e,t]},useMemoCache:Po,useCacheRefresh:function(){return ko().memoizedState=As.bind(null,B)},useEffectEvent:function(e){var t=ko(),n={impl:e};return t.memoizedState=n,function(){if(K&2)throw Error(i(440));return n.impl.apply(void 0,arguments)}}},Bs={readContext:na,use:No,useCallback:vs,useContext:na,useEffect:us,useImperativeHandle:gs,useInsertionEffect:ps,useLayoutEffect:ms,useMemo:ys,useReducer:Io,useRef:os,useState:function(){return Io(Fo)},useDebugValue:_s,useDeferredValue:function(e,t){return xs(Ao(),V.memoizedState,e,t)},useTransition:function(){var e=Io(Fo)[0],t=Ao().memoizedState;return[typeof e==`boolean`?e:Mo(e),t]},useSyncExternalStore:zo,useId:Os,useHostTransitionStatus:Ds,useFormState:ts,useActionState:ts,useOptimistic:function(e,t){return Ko(Ao(),V,e,t)},useMemoCache:Po,useCacheRefresh:ks};Bs.useEffectEvent=fs;var Vs={readContext:na,use:No,useCallback:vs,useContext:na,useEffect:us,useImperativeHandle:gs,useInsertionEffect:ps,useLayoutEffect:ms,useMemo:ys,useReducer:Ro,useRef:os,useState:function(){return Ro(Fo)},useDebugValue:_s,useDeferredValue:function(e,t){var n=Ao();return V===null?bs(n,e,t):xs(n,V.memoizedState,e,t)},useTransition:function(){var e=Ro(Fo)[0],t=Ao().memoizedState;return[typeof e==`boolean`?e:Mo(e),t]},useSyncExternalStore:zo,useId:Os,useHostTransitionStatus:Ds,useFormState:is,useActionState:is,useOptimistic:function(e,t){var n=Ao();return V===null?(n.baseState=e,[e,n.queue.dispatch]):Ko(n,V,e,t)},useMemoCache:Po,useCacheRefresh:ks};Vs.useEffectEvent=fs;function Hs(e,t,n,r){t=e.memoizedState,n=n(r,t),n=n==null?t:p({},t,n),e.memoizedState=n,e.lanes===0&&(e.updateQueue.baseState=n)}var Us={enqueueSetState:function(e,t,n){e=e._reactInternals;var r=pu(),i=Wa(r);i.payload=t,n!=null&&(i.callback=n),t=Ga(e,i,r),t!==null&&(hu(t,e,r),Ka(t,e,r))},enqueueReplaceState:function(e,t,n){e=e._reactInternals;var r=pu(),i=Wa(r);i.tag=1,i.payload=t,n!=null&&(i.callback=n),t=Ga(e,i,r),t!==null&&(hu(t,e,r),Ka(t,e,r))},enqueueForceUpdate:function(e,t){e=e._reactInternals;var n=pu(),r=Wa(n);r.tag=2,t!=null&&(r.callback=t),t=Ga(e,r,n),t!==null&&(hu(t,e,n),Ka(t,e,n))}};function Ws(e,t,n,r,i,a,o){return e=e.stateNode,typeof e.shouldComponentUpdate==`function`?e.shouldComponentUpdate(r,a,o):t.prototype&&t.prototype.isPureReactComponent?!Dr(n,r)||!Dr(i,a):!0}function Gs(e,t,n,r){e=t.state,typeof t.componentWillReceiveProps==`function`&&t.componentWillReceiveProps(n,r),typeof t.UNSAFE_componentWillReceiveProps==`function`&&t.UNSAFE_componentWillReceiveProps(n,r),t.state!==e&&Us.enqueueReplaceState(t,t.state,null)}function Ks(e,t){var n=t;if(`ref`in t)for(var r in n={},t)r!==`ref`&&(n[r]=t[r]);if(e=e.defaultProps)for(var i in n===t&&(n=p({},n)),e)n[i]===void 0&&(n[i]=e[i]);return n}function qs(e){$r(e)}function Js(e){console.error(e)}function Ys(e){$r(e)}function Xs(e,t){try{var n=e.onUncaughtError;n(t.value,{componentStack:t.stack})}catch(e){setTimeout(function(){throw e})}}function Zs(e,t,n){try{var r=e.onCaughtError;r(n.value,{componentStack:n.stack,errorBoundary:t.tag===1?t.stateNode:null})}catch(e){setTimeout(function(){throw e})}}function Qs(e,t,n){return n=Wa(n),n.tag=3,n.payload={element:null},n.callback=function(){Xs(e,t)},n}function $s(e){return e=Wa(e),e.tag=3,e}function ec(e,t,n,r){var i=n.type.getDerivedStateFromError;if(typeof i==`function`){var a=r.value;e.payload=function(){return i(a)},e.callback=function(){Zs(t,n,r)}}var o=n.stateNode;o!==null&&typeof o.componentDidCatch==`function`&&(e.callback=function(){Zs(t,n,r),typeof i!=`function`&&(ru===null?ru=new Set([this]):ru.add(this));var e=r.stack;this.componentDidCatch(r.value,{componentStack:e===null?``:e})})}function tc(e,t,n,r,a){if(n.flags|=32768,typeof r==`object`&&r&&typeof r.then==`function`){if(t=n.alternate,t!==null&&$i(t,n,a,!0),n=io.current,n!==null){switch(n.tag){case 31:case 13:return ao===null?Du():n.alternate===null&&Wl===0&&(Wl=3),n.flags&=-257,n.flags|=65536,n.lanes=a,r===Ea?n.flags|=16384:(t=n.updateQueue,t===null?n.updateQueue=new Set([r]):t.add(r),Gu(e,r,a)),!1;case 22:return n.flags|=65536,r===Ea?n.flags|=16384:(t=n.updateQueue,t===null?(t={transitions:null,markerInstances:null,retryQueue:new Set([r])},n.updateQueue=t):(n=t.retryQueue,n===null?t.retryQueue=new Set([r]):n.add(r)),Gu(e,r,a)),!1}throw Error(i(435,n.tag))}return Gu(e,r,a),Du(),!1}if(L)return t=io.current,t===null?(r!==zi&&(t=Error(i(423),{cause:r}),Ki(xi(t,n))),e=e.current.alternate,e.flags|=65536,a&=-a,e.lanes|=a,r=xi(r,n),a=Qs(e.stateNode,r,a),qa(e,a),Wl!==4&&(Wl=2)):(!(t.flags&65536)&&(t.flags|=256),t.flags|=65536,t.lanes=a,r!==zi&&(e=Error(i(422),{cause:r}),Ki(xi(e,n)))),!1;var o=Error(i(520),{cause:r});if(o=xi(o,n),Xl===null?Xl=[o]:Xl.push(o),Wl!==4&&(Wl=2),t===null)return!0;r=xi(r,n),n=t;do{switch(n.tag){case 3:return n.flags|=65536,e=a&-a,n.lanes|=e,e=Qs(n.stateNode,r,e),qa(n,e),!1;case 1:if(t=n.type,o=n.stateNode,!(n.flags&128)&&(typeof t.getDerivedStateFromError==`function`||o!==null&&typeof o.componentDidCatch==`function`&&(ru===null||!ru.has(o))))return n.flags|=65536,a&=-a,n.lanes|=a,a=$s(a),ec(a,e,n,r),qa(n,a),!1}n=n.return}while(n!==null);return!1}var nc=Error(i(461)),rc=!1;function ic(e,t,n,r){t.child=e===null?Ba(t,null,n,r):za(t,e.child,n,r)}function ac(e,t,n,r,i){n=n.render;var a=t.ref;if(`ref`in r){var o={};for(var s in r)s!==`ref`&&(o[s]=r[s])}else o=r;return ta(t),r=So(e,t,n,o,a,i),s=Eo(),e!==null&&!rc?(Do(e,t,i),Oc(e,t,i)):(L&&s&&Ni(t),t.flags|=1,ic(e,t,r,i),t.child)}function oc(e,t,n,r,i){if(e===null){var a=n.type;return typeof a==`function`&&!fi(a)&&a.defaultProps===void 0&&n.compare===null?(t.tag=15,t.type=a,sc(e,t,a,r,i)):(e=hi(n.type,null,r,t,t.mode,i),e.ref=t.ref,e.return=t,t.child=e)}if(a=e.child,!kc(e,i)){var o=a.memoizedProps;if(n=n.compare,n=n===null?Dr:n,n(o,r)&&e.ref===t.ref)return Oc(e,t,i)}return t.flags|=1,e=pi(a,r),e.ref=t.ref,e.return=t,t.child=e}function sc(e,t,n,r,i){if(e!==null){var a=e.memoizedProps;if(Dr(a,r)&&e.ref===t.ref)if(rc=!1,t.pendingProps=r=a,kc(e,i))e.flags&131072&&(rc=!0);else return t.lanes=e.lanes,Oc(e,t,i)}return mc(e,t,n,r,i)}function cc(e,t,n,r){var i=r.children,a=e===null?null:e.memoizedState;if(e===null&&t.stateNode===null&&(t.stateNode={_visibility:1,_pendingMarkers:null,_retryCache:null,_transitions:null}),r.mode===`hidden`){if(t.flags&128){if(a=a===null?n:a.baseLanes|n,e!==null){for(r=t.child=e.child,i=0;r!==null;)i=i|r.lanes|r.childLanes,r=r.sibling;r=i&~a}else r=0,t.child=null;return uc(e,t,a,n,r)}if(n&536870912)t.memoizedState={baseLanes:0,cachePool:null},e!==null&&xa(t,a===null?null:a.cachePool),a===null?no():to(t,a),co(t);else return r=t.lanes=536870912,uc(e,t,a===null?n:a.baseLanes|n,n,r)}else a===null?(e!==null&&xa(t,null),no(),lo(t)):(xa(t,a.cachePool),to(t,a),lo(t),t.memoizedState=null);return ic(e,t,i,n),t.child}function lc(e,t){return e!==null&&e.tag===22||t.stateNode!==null||(t.stateNode={_visibility:1,_pendingMarkers:null,_retryCache:null,_transitions:null}),t.sibling}function uc(e,t,n,r,i){var a=ba();return a=a===null?null:{parent:ca._currentValue,pool:a},t.memoizedState={baseLanes:n,cachePool:a},e!==null&&xa(t,null),no(),co(t),e!==null&&$i(e,t,r,!0),t.childLanes=i,null}function W(e,t){return t=Cc({mode:t.mode,children:t.children},e.mode),t.ref=e.ref,e.child=t,t.return=e,t}function dc(e,t,n){return za(t,e.child,null,n),e=W(t,t.pendingProps),e.flags|=2,uo(t),t.memoizedState=null,e}function fc(e,t,n){var r=t.pendingProps,a=(t.flags&128)!=0;if(t.flags&=-129,e===null){if(L){if(r.mode===`hidden`)return e=W(t,r),t.lanes=536870912,lc(null,e);if(so(t),(e=I)?(e=rf(e,Ri),e=e!==null&&e.data===`&`?e:null,e!==null&&(t.memoizedState={dehydrated:e,treeContext:Oi===null?null:{id:ki,overflow:Ai},retryLane:536870912,hydrationErrors:null},n=vi(e),n.return=t,t.child=n,Ii=t,I=null)):e=null,e===null)throw Bi(t);return t.lanes=536870912,null}return W(t,r)}var o=e.memoizedState;if(o!==null){var s=o.dehydrated;if(so(t),a)if(t.flags&256)t.flags&=-257,t=dc(e,t,n);else if(t.memoizedState!==null)t.child=e.child,t.flags|=128,t=null;else throw Error(i(558));else if(rc||$i(e,t,n,!1),a=(n&e.childLanes)!==0,rc||a){if(r=q,r!==null&&(s=lt(r,n),s!==0&&s!==o.retryLane))throw o.retryLane=s,oi(e,s),hu(r,e,s),nc;Du(),t=dc(e,t,n)}else e=o.treeContext,I=cf(s.nextSibling),Ii=t,L=!0,Li=null,Ri=!1,e!==null&&Fi(t,e),t=W(t,r),t.flags|=4096;return t}return e=pi(e.child,{mode:r.mode,children:r.children}),e.ref=t.ref,t.child=e,e.return=t,e}function pc(e,t){var n=t.ref;if(n===null)e!==null&&e.ref!==null&&(t.flags|=4194816);else{if(typeof n!=`function`&&typeof n!=`object`)throw Error(i(284));(e===null||e.ref!==n)&&(t.flags|=4194816)}}function mc(e,t,n,r,i){return ta(t),n=So(e,t,n,r,void 0,i),r=Eo(),e!==null&&!rc?(Do(e,t,i),Oc(e,t,i)):(L&&r&&Ni(t),t.flags|=1,ic(e,t,n,i),t.child)}function hc(e,t,n,r,i,a){return ta(t),t.updateQueue=null,n=wo(t,r,n,i),Co(e),r=Eo(),e!==null&&!rc?(Do(e,t,a),Oc(e,t,a)):(L&&r&&Ni(t),t.flags|=1,ic(e,t,n,a),t.child)}function gc(e,t,n,r,i){if(ta(t),t.stateNode===null){var a=li,o=n.contextType;typeof o==`object`&&o&&(a=na(o)),a=new n(r,a),t.memoizedState=a.state!==null&&a.state!==void 0?a.state:null,a.updater=Us,t.stateNode=a,a._reactInternals=t,a=t.stateNode,a.props=r,a.state=t.memoizedState,a.refs={},Ha(t),o=n.contextType,a.context=typeof o==`object`&&o?na(o):li,a.state=t.memoizedState,o=n.getDerivedStateFromProps,typeof o==`function`&&(Hs(t,n,o,r),a.state=t.memoizedState),typeof n.getDerivedStateFromProps==`function`||typeof a.getSnapshotBeforeUpdate==`function`||typeof a.UNSAFE_componentWillMount!=`function`&&typeof a.componentWillMount!=`function`||(o=a.state,typeof a.componentWillMount==`function`&&a.componentWillMount(),typeof a.UNSAFE_componentWillMount==`function`&&a.UNSAFE_componentWillMount(),o!==a.state&&Us.enqueueReplaceState(a,a.state,null),Xa(t,r,a,i),Ya(),a.state=t.memoizedState),typeof a.componentDidMount==`function`&&(t.flags|=4194308),r=!0}else if(e===null){a=t.stateNode;var s=t.memoizedProps,c=Ks(n,s);a.props=c;var l=a.context,u=n.contextType;o=li,typeof u==`object`&&u&&(o=na(u));var d=n.getDerivedStateFromProps;u=typeof d==`function`||typeof a.getSnapshotBeforeUpdate==`function`,s=t.pendingProps!==s,u||typeof a.UNSAFE_componentWillReceiveProps!=`function`&&typeof a.componentWillReceiveProps!=`function`||(s||l!==o)&&Gs(t,a,r,o),Va=!1;var f=t.memoizedState;a.state=f,Xa(t,r,a,i),Ya(),l=t.memoizedState,s||f!==l||Va?(typeof d==`function`&&(Hs(t,n,d,r),l=t.memoizedState),(c=Va||Ws(t,n,c,r,f,l,o))?(u||typeof a.UNSAFE_componentWillMount!=`function`&&typeof a.componentWillMount!=`function`||(typeof a.componentWillMount==`function`&&a.componentWillMount(),typeof a.UNSAFE_componentWillMount==`function`&&a.UNSAFE_componentWillMount()),typeof a.componentDidMount==`function`&&(t.flags|=4194308)):(typeof a.componentDidMount==`function`&&(t.flags|=4194308),t.memoizedProps=r,t.memoizedState=l),a.props=r,a.state=l,a.context=o,r=c):(typeof a.componentDidMount==`function`&&(t.flags|=4194308),r=!1)}else{a=t.stateNode,Ua(e,t),o=t.memoizedProps,u=Ks(n,o),a.props=u,d=t.pendingProps,f=a.context,l=n.contextType,c=li,typeof l==`object`&&l&&(c=na(l)),s=n.getDerivedStateFromProps,(l=typeof s==`function`||typeof a.getSnapshotBeforeUpdate==`function`)||typeof a.UNSAFE_componentWillReceiveProps!=`function`&&typeof a.componentWillReceiveProps!=`function`||(o!==d||f!==c)&&Gs(t,a,r,c),Va=!1,f=t.memoizedState,a.state=f,Xa(t,r,a,i),Ya();var p=t.memoizedState;o!==d||f!==p||Va||e!==null&&e.dependencies!==null&&ea(e.dependencies)?(typeof s==`function`&&(Hs(t,n,s,r),p=t.memoizedState),(u=Va||Ws(t,n,u,r,f,p,c)||e!==null&&e.dependencies!==null&&ea(e.dependencies))?(l||typeof a.UNSAFE_componentWillUpdate!=`function`&&typeof a.componentWillUpdate!=`function`||(typeof a.componentWillUpdate==`function`&&a.componentWillUpdate(r,p,c),typeof a.UNSAFE_componentWillUpdate==`function`&&a.UNSAFE_componentWillUpdate(r,p,c)),typeof a.componentDidUpdate==`function`&&(t.flags|=4),typeof a.getSnapshotBeforeUpdate==`function`&&(t.flags|=1024)):(typeof a.componentDidUpdate!=`function`||o===e.memoizedProps&&f===e.memoizedState||(t.flags|=4),typeof a.getSnapshotBeforeUpdate!=`function`||o===e.memoizedProps&&f===e.memoizedState||(t.flags|=1024),t.memoizedProps=r,t.memoizedState=p),a.props=r,a.state=p,a.context=c,r=u):(typeof a.componentDidUpdate!=`function`||o===e.memoizedProps&&f===e.memoizedState||(t.flags|=4),typeof a.getSnapshotBeforeUpdate!=`function`||o===e.memoizedProps&&f===e.memoizedState||(t.flags|=1024),r=!1)}return a=r,pc(e,t),r=(t.flags&128)!=0,a||r?(a=t.stateNode,n=r&&typeof n.getDerivedStateFromError!=`function`?null:a.render(),t.flags|=1,e!==null&&r?(t.child=za(t,e.child,null,i),t.child=za(t,null,n,i)):ic(e,t,n,i),t.memoizedState=a.state,e=t.child):e=Oc(e,t,i),e}function _c(e,t,n,r){return Wi(),t.flags|=256,ic(e,t,n,r),t.child}var vc={dehydrated:null,treeContext:null,retryLane:0,hydrationErrors:null};function yc(e){return{baseLanes:e,cachePool:Sa()}}function bc(e,t,n){return e=e===null?0:e.childLanes&~n,t&&(e|=Jl),e}function xc(e,t,n){var r=t.pendingProps,a=!1,o=(t.flags&128)!=0,s;if((s=o)||(s=e!==null&&e.memoizedState===null?!1:(z.current&2)!=0),s&&(a=!0,t.flags&=-129),s=(t.flags&32)!=0,t.flags&=-33,e===null){if(L){if(a?oo(t):lo(t),(e=I)?(e=rf(e,Ri),e=e!==null&&e.data!==`&`?e:null,e!==null&&(t.memoizedState={dehydrated:e,treeContext:Oi===null?null:{id:ki,overflow:Ai},retryLane:536870912,hydrationErrors:null},n=vi(e),n.return=t,t.child=n,Ii=t,I=null)):e=null,e===null)throw Bi(t);return of(e)?t.lanes=32:t.lanes=536870912,null}var c=r.children;return r=r.fallback,a?(lo(t),a=t.mode,c=Cc({mode:`hidden`,children:c},a),r=gi(r,a,n,null),c.return=t,r.return=t,c.sibling=r,t.child=c,r=t.child,r.memoizedState=yc(n),r.childLanes=bc(e,s,n),t.memoizedState=vc,lc(null,r)):(oo(t),Sc(t,c))}var l=e.memoizedState;if(l!==null&&(c=l.dehydrated,c!==null)){if(o)t.flags&256?(oo(t),t.flags&=-257,t=wc(e,t,n)):t.memoizedState===null?(lo(t),c=r.fallback,a=t.mode,r=Cc({mode:`visible`,children:r.children},a),c=gi(c,a,n,null),c.flags|=2,r.return=t,c.return=t,r.sibling=c,t.child=r,za(t,e.child,null,n),r=t.child,r.memoizedState=yc(n),r.childLanes=bc(e,s,n),t.memoizedState=vc,t=lc(null,r)):(lo(t),t.child=e.child,t.flags|=128,t=null);else if(oo(t),of(c)){if(s=c.nextSibling&&c.nextSibling.dataset,s)var u=s.dgst;s=u,r=Error(i(419)),r.stack=``,r.digest=s,Ki({value:r,source:null,stack:null}),t=wc(e,t,n)}else if(rc||$i(e,t,n,!1),s=(n&e.childLanes)!==0,rc||s){if(s=q,s!==null&&(r=lt(s,n),r!==0&&r!==l.retryLane))throw l.retryLane=r,oi(e,r),hu(s,e,r),nc;af(c)||Du(),t=wc(e,t,n)}else af(c)?(t.flags|=192,t.child=e.child,t=null):(e=l.treeContext,I=cf(c.nextSibling),Ii=t,L=!0,Li=null,Ri=!1,e!==null&&Fi(t,e),t=Sc(t,r.children),t.flags|=4096);return t}return a?(lo(t),c=r.fallback,a=t.mode,l=e.child,u=l.sibling,r=pi(l,{mode:`hidden`,children:r.children}),r.subtreeFlags=l.subtreeFlags&65011712,u===null?(c=gi(c,a,n,null),c.flags|=2):c=pi(u,c),c.return=t,r.return=t,r.sibling=c,t.child=r,lc(null,r),r=t.child,c=e.child.memoizedState,c===null?c=yc(n):(a=c.cachePool,a===null?a=Sa():(l=ca._currentValue,a=a.parent===l?a:{parent:l,pool:l}),c={baseLanes:c.baseLanes|n,cachePool:a}),r.memoizedState=c,r.childLanes=bc(e,s,n),t.memoizedState=vc,lc(e.child,r)):(oo(t),n=e.child,e=n.sibling,n=pi(n,{mode:`visible`,children:r.children}),n.return=t,n.sibling=null,e!==null&&(s=t.deletions,s===null?(t.deletions=[e],t.flags|=16):s.push(e)),t.child=n,t.memoizedState=null,n)}function Sc(e,t){return t=Cc({mode:`visible`,children:t},e.mode),t.return=e,e.child=t}function Cc(e,t){return e=di(22,e,null,t),e.lanes=0,e}function wc(e,t,n){return za(t,e.child,null,n),e=Sc(t,t.pendingProps.children),e.flags|=2,t.memoizedState=null,e}function Tc(e,t,n){e.lanes|=t;var r=e.alternate;r!==null&&(r.lanes|=t),Zi(e.return,t,n)}function Ec(e,t,n,r,i,a){var o=e.memoizedState;o===null?e.memoizedState={isBackwards:t,rendering:null,renderingStartTime:0,last:r,tail:n,tailMode:i,treeForkCount:a}:(o.isBackwards=t,o.rendering=null,o.renderingStartTime=0,o.last=r,o.tail=n,o.tailMode=i,o.treeForkCount=a)}function Dc(e,t,n){var r=t.pendingProps,i=r.revealOrder,a=r.tail;r=r.children;var o=z.current,s=(o&2)!=0;if(s?(o=o&1|2,t.flags|=128):o&=1,k(z,o),ic(e,t,r,n),r=L?Ti:0,!s&&e!==null&&e.flags&128)a:for(e=t.child;e!==null;){if(e.tag===13)e.memoizedState!==null&&Tc(e,n,t);else if(e.tag===19)Tc(e,n,t);else if(e.child!==null){e.child.return=e,e=e.child;continue}if(e===t)break a;for(;e.sibling===null;){if(e.return===null||e.return===t)break a;e=e.return}e.sibling.return=e.return,e=e.sibling}switch(i){case`forwards`:for(n=t.child,i=null;n!==null;)e=n.alternate,e!==null&&fo(e)===null&&(i=n),n=n.sibling;n=i,n===null?(i=t.child,t.child=null):(i=n.sibling,n.sibling=null),Ec(t,!1,i,n,a,r);break;case`backwards`:case`unstable_legacy-backwards`:for(n=null,i=t.child,t.child=null;i!==null;){if(e=i.alternate,e!==null&&fo(e)===null){t.child=i;break}e=i.sibling,i.sibling=n,n=i,i=e}Ec(t,!0,n,null,a,r);break;case`together`:Ec(t,!1,null,null,void 0,r);break;default:t.memoizedState=null}return t.child}function Oc(e,t,n){if(e!==null&&(t.dependencies=e.dependencies),Gl|=t.lanes,(n&t.childLanes)===0)if(e!==null){if($i(e,t,n,!1),(n&t.childLanes)===0)return null}else return null;if(e!==null&&t.child!==e.child)throw Error(i(153));if(t.child!==null){for(e=t.child,n=pi(e,e.pendingProps),t.child=n,n.return=t;e.sibling!==null;)e=e.sibling,n=n.sibling=pi(e,e.pendingProps),n.return=t;n.sibling=null}return t.child}function kc(e,t){return(e.lanes&t)===0?(e=e.dependencies,!!(e!==null&&ea(e))):!0}function Ac(e,t,n){switch(t.tag){case 3:ge(t,t.stateNode.containerInfo),Yi(t,ca,e.memoizedState.cache),Wi();break;case 27:case 5:ve(t);break;case 4:ge(t,t.stateNode.containerInfo);break;case 10:Yi(t,t.type,t.memoizedProps.value);break;case 31:if(t.memoizedState!==null)return t.flags|=128,so(t),null;break;case 13:var r=t.memoizedState;if(r!==null)return r.dehydrated===null?(n&t.child.childLanes)===0?(oo(t),e=Oc(e,t,n),e===null?null:e.sibling):xc(e,t,n):(oo(t),t.flags|=128,null);oo(t);break;case 19:var i=(e.flags&128)!=0;if(r=(n&t.childLanes)!==0,r||=($i(e,t,n,!1),(n&t.childLanes)!==0),i){if(r)return Dc(e,t,n);t.flags|=128}if(i=t.memoizedState,i!==null&&(i.rendering=null,i.tail=null,i.lastEffect=null),k(z,z.current),r)break;return null;case 22:return t.lanes=0,cc(e,t,n,t.pendingProps);case 24:Yi(t,ca,e.memoizedState.cache)}return Oc(e,t,n)}function jc(e,t,n){if(e!==null)if(e.memoizedProps!==t.pendingProps)rc=!0;else{if(!kc(e,n)&&!(t.flags&128))return rc=!1,Ac(e,t,n);rc=!!(e.flags&131072)}else rc=!1,L&&t.flags&1048576&&Mi(t,Ti,t.index);switch(t.lanes=0,t.tag){case 16:a:{var r=t.pendingProps;if(e=ka(t.elementType),t.type=e,typeof e==`function`)fi(e)?(r=Ks(e,r),t.tag=1,t=gc(null,t,e,r,n)):(t.tag=0,t=mc(null,t,e,r,n));else{if(e!=null){var a=e.$$typeof;if(a===ee){t.tag=11,t=ac(null,t,e,r,n);break a}else if(a===w){t.tag=14,t=oc(null,t,e,r,n);break a}}throw t=se(e)||e,Error(i(306,t,``))}}return t;case 0:return mc(e,t,t.type,t.pendingProps,n);case 1:return r=t.type,a=Ks(r,t.pendingProps),gc(e,t,r,a,n);case 3:a:{if(ge(t,t.stateNode.containerInfo),e===null)throw Error(i(387));r=t.pendingProps;var o=t.memoizedState;a=o.element,Ua(e,t),Xa(t,r,null,n);var s=t.memoizedState;if(r=s.cache,Yi(t,ca,r),r!==o.cache&&Qi(t,[ca],n,!0),Ya(),r=s.element,o.isDehydrated)if(o={element:r,isDehydrated:!1,cache:s.cache},t.updateQueue.baseState=o,t.memoizedState=o,t.flags&256){t=_c(e,t,r,n);break a}else if(r!==a){a=xi(Error(i(424)),t),Ki(a),t=_c(e,t,r,n);break a}else{switch(e=t.stateNode.containerInfo,e.nodeType){case 9:e=e.body;break;default:e=e.nodeName===`HTML`?e.ownerDocument.body:e}for(I=cf(e.firstChild),Ii=t,L=!0,Li=null,Ri=!0,n=Ba(t,null,r,n),t.child=n;n;)n.flags=n.flags&-3|4096,n=n.sibling}else{if(Wi(),r===a){t=Oc(e,t,n);break a}ic(e,t,r,n)}t=t.child}return t;case 26:return pc(e,t),e===null?(n=kf(t.type,null,t.pendingProps,null))?t.memoizedState=n:L||(n=t.type,e=t.pendingProps,r=Bd(me.current).createElement(n),r[ht]=t,r[gt]=e,Pd(r,n,e),Ot(r),t.stateNode=r):t.memoizedState=kf(t.type,e.memoizedProps,t.pendingProps,e.memoizedState),null;case 27:return ve(t),e===null&&L&&(r=t.stateNode=ff(t.type,t.pendingProps,me.current),Ii=t,Ri=!0,a=I,Zd(t.type)?(lf=a,I=cf(r.firstChild)):I=a),ic(e,t,t.pendingProps.children,n),pc(e,t),e===null&&(t.flags|=4194304),t.child;case 5:return e===null&&L&&((a=r=I)&&(r=tf(r,t.type,t.pendingProps,Ri),r===null?a=!1:(t.stateNode=r,Ii=t,I=cf(r.firstChild),Ri=!1,a=!0)),a||Bi(t)),ve(t),a=t.type,o=t.pendingProps,s=e===null?null:e.memoizedProps,r=o.children,Ud(a,o)?r=null:s!==null&&Ud(a,s)&&(t.flags|=32),t.memoizedState!==null&&(a=So(e,t,To,null,null,n),Qf._currentValue=a),pc(e,t),ic(e,t,r,n),t.child;case 6:return e===null&&L&&((e=n=I)&&(n=nf(n,t.pendingProps,Ri),n===null?e=!1:(t.stateNode=n,Ii=t,I=null,e=!0)),e||Bi(t)),null;case 13:return xc(e,t,n);case 4:return ge(t,t.stateNode.containerInfo),r=t.pendingProps,e===null?t.child=za(t,null,r,n):ic(e,t,r,n),t.child;case 11:return ac(e,t,t.type,t.pendingProps,n);case 7:return ic(e,t,t.pendingProps,n),t.child;case 8:return ic(e,t,t.pendingProps.children,n),t.child;case 12:return ic(e,t,t.pendingProps.children,n),t.child;case 10:return r=t.pendingProps,Yi(t,t.type,r.value),ic(e,t,r.children,n),t.child;case 9:return a=t.type._context,r=t.pendingProps.children,ta(t),a=na(a),r=r(a),t.flags|=1,ic(e,t,r,n),t.child;case 14:return oc(e,t,t.type,t.pendingProps,n);case 15:return sc(e,t,t.type,t.pendingProps,n);case 19:return Dc(e,t,n);case 31:return fc(e,t,n);case 22:return cc(e,t,n,t.pendingProps);case 24:return ta(t),r=na(ca),e===null?(a=ba(),a===null&&(a=q,o=la(),a.pooledCache=o,o.refCount++,o!==null&&(a.pooledCacheLanes|=n),a=o),t.memoizedState={parent:r,cache:a},Ha(t),Yi(t,ca,a)):((e.lanes&n)!==0&&(Ua(e,t),Xa(t,null,null,n),Ya()),a=e.memoizedState,o=t.memoizedState,a.parent===r?(r=o.cache,Yi(t,ca,r),r!==a.cache&&Qi(t,[ca],n,!0)):(a={parent:r,cache:r},t.memoizedState=a,t.lanes===0&&(t.memoizedState=t.updateQueue.baseState=a),Yi(t,ca,r))),ic(e,t,t.pendingProps.children,n),t.child;case 29:throw t.pendingProps}throw Error(i(156,t.tag))}function Mc(e){e.flags|=4}function Nc(e,t,n,r,i){if((t=(e.mode&32)!=0)&&(t=!1),t){if(e.flags|=16777216,(i&335544128)===i)if(e.stateNode.complete)e.flags|=8192;else if(wu())e.flags|=8192;else throw Aa=Ea,wa}else e.flags&=-16777217}function Pc(e,t){if(t.type!==`stylesheet`||t.state.loading&4)e.flags&=-16777217;else if(e.flags|=16777216,!Wf(t))if(wu())e.flags|=8192;else throw Aa=Ea,wa}function Fc(e,t){t!==null&&(e.flags|=4),e.flags&16384&&(t=e.tag===22?536870912:rt(),e.lanes|=t,Yl|=t)}function Ic(e,t){if(!L)switch(e.tailMode){case`hidden`:t=e.tail;for(var n=null;t!==null;)t.alternate!==null&&(n=t),t=t.sibling;n===null?e.tail=null:n.sibling=null;break;case`collapsed`:n=e.tail;for(var r=null;n!==null;)n.alternate!==null&&(r=n),n=n.sibling;r===null?t||e.tail===null?e.tail=null:e.tail.sibling=null:r.sibling=null}}function G(e){var t=e.alternate!==null&&e.alternate.child===e.child,n=0,r=0;if(t)for(var i=e.child;i!==null;)n|=i.lanes|i.childLanes,r|=i.subtreeFlags&65011712,r|=i.flags&65011712,i.return=e,i=i.sibling;else for(i=e.child;i!==null;)n|=i.lanes|i.childLanes,r|=i.subtreeFlags,r|=i.flags,i.return=e,i=i.sibling;return e.subtreeFlags|=r,e.childLanes=n,t}function Lc(e,t,n){var r=t.pendingProps;switch(Pi(t),t.tag){case 16:case 15:case 0:case 11:case 7:case 8:case 12:case 9:case 14:return G(t),null;case 1:return G(t),null;case 3:return n=t.stateNode,r=null,e!==null&&(r=e.memoizedState.cache),t.memoizedState.cache!==r&&(t.flags|=2048),Xi(ca),_e(),n.pendingContext&&(n.context=n.pendingContext,n.pendingContext=null),(e===null||e.child===null)&&(Ui(t)?Mc(t):e===null||e.memoizedState.isDehydrated&&!(t.flags&256)||(t.flags|=1024,Gi())),G(t),null;case 26:var a=t.type,o=t.memoizedState;return e===null?(Mc(t),o===null?(G(t),Nc(t,a,null,r,n)):(G(t),Pc(t,o))):o?o===e.memoizedState?(G(t),t.flags&=-16777217):(Mc(t),G(t),Pc(t,o)):(e=e.memoizedProps,e!==r&&Mc(t),G(t),Nc(t,a,e,r,n)),null;case 27:if(ye(t),n=me.current,a=t.type,e!==null&&t.stateNode!=null)e.memoizedProps!==r&&Mc(t);else{if(!r){if(t.stateNode===null)throw Error(i(166));return G(t),null}e=fe.current,Ui(t)?Vi(t,e):(e=ff(a,r,n),t.stateNode=e,Mc(t))}return G(t),null;case 5:if(ye(t),a=t.type,e!==null&&t.stateNode!=null)e.memoizedProps!==r&&Mc(t);else{if(!r){if(t.stateNode===null)throw Error(i(166));return G(t),null}if(o=fe.current,Ui(t))Vi(t,o);else{var s=Bd(me.current);switch(o){case 1:o=s.createElementNS(`http://www.w3.org/2000/svg`,a);break;case 2:o=s.createElementNS(`http://www.w3.org/1998/Math/MathML`,a);break;default:switch(a){case`svg`:o=s.createElementNS(`http://www.w3.org/2000/svg`,a);break;case`math`:o=s.createElementNS(`http://www.w3.org/1998/Math/MathML`,a);break;case`script`:o=s.createElement(`div`),o.innerHTML=`<script><\/script>`,o=o.removeChild(o.firstChild);break;case`select`:o=typeof r.is==`string`?s.createElement(`select`,{is:r.is}):s.createElement(`select`),r.multiple?o.multiple=!0:r.size&&(o.size=r.size);break;default:o=typeof r.is==`string`?s.createElement(a,{is:r.is}):s.createElement(a)}}o[ht]=t,o[gt]=r;a:for(s=t.child;s!==null;){if(s.tag===5||s.tag===6)o.appendChild(s.stateNode);else if(s.tag!==4&&s.tag!==27&&s.child!==null){s.child.return=s,s=s.child;continue}if(s===t)break a;for(;s.sibling===null;){if(s.return===null||s.return===t)break a;s=s.return}s.sibling.return=s.return,s=s.sibling}t.stateNode=o;a:switch(Pd(o,a,r),a){case`button`:case`input`:case`select`:case`textarea`:r=!!r.autoFocus;break a;case`img`:r=!0;break a;default:r=!1}r&&Mc(t)}}return G(t),Nc(t,t.type,e===null?null:e.memoizedProps,t.pendingProps,n),null;case 6:if(e&&t.stateNode!=null)e.memoizedProps!==r&&Mc(t);else{if(typeof r!=`string`&&t.stateNode===null)throw Error(i(166));if(e=me.current,Ui(t)){if(e=t.stateNode,n=t.memoizedProps,r=null,a=Ii,a!==null)switch(a.tag){case 27:case 5:r=a.memoizedProps}e[ht]=t,e=!!(e.nodeValue===n||r!==null&&!0===r.suppressHydrationWarning||Md(e.nodeValue,n)),e||Bi(t,!0)}else e=Bd(e).createTextNode(r),e[ht]=t,t.stateNode=e}return G(t),null;case 31:if(n=t.memoizedState,e===null||e.memoizedState!==null){if(r=Ui(t),n!==null){if(e===null){if(!r)throw Error(i(318));if(e=t.memoizedState,e=e===null?null:e.dehydrated,!e)throw Error(i(557));e[ht]=t}else Wi(),!(t.flags&128)&&(t.memoizedState=null),t.flags|=4;G(t),e=!1}else n=Gi(),e!==null&&e.memoizedState!==null&&(e.memoizedState.hydrationErrors=n),e=!0;if(!e)return t.flags&256?(uo(t),t):(uo(t),null);if(t.flags&128)throw Error(i(558))}return G(t),null;case 13:if(r=t.memoizedState,e===null||e.memoizedState!==null&&e.memoizedState.dehydrated!==null){if(a=Ui(t),r!==null&&r.dehydrated!==null){if(e===null){if(!a)throw Error(i(318));if(a=t.memoizedState,a=a===null?null:a.dehydrated,!a)throw Error(i(317));a[ht]=t}else Wi(),!(t.flags&128)&&(t.memoizedState=null),t.flags|=4;G(t),a=!1}else a=Gi(),e!==null&&e.memoizedState!==null&&(e.memoizedState.hydrationErrors=a),a=!0;if(!a)return t.flags&256?(uo(t),t):(uo(t),null)}return uo(t),t.flags&128?(t.lanes=n,t):(n=r!==null,e=e!==null&&e.memoizedState!==null,n&&(r=t.child,a=null,r.alternate!==null&&r.alternate.memoizedState!==null&&r.alternate.memoizedState.cachePool!==null&&(a=r.alternate.memoizedState.cachePool.pool),o=null,r.memoizedState!==null&&r.memoizedState.cachePool!==null&&(o=r.memoizedState.cachePool.pool),o!==a&&(r.flags|=2048)),n!==e&&n&&(t.child.flags|=8192),Fc(t,t.updateQueue),G(t),null);case 4:return _e(),e===null&&Sd(t.stateNode.containerInfo),G(t),null;case 10:return Xi(t.type),G(t),null;case 19:if(de(z),r=t.memoizedState,r===null)return G(t),null;if(a=(t.flags&128)!=0,o=r.rendering,o===null)if(a)Ic(r,!1);else{if(Wl!==0||e!==null&&e.flags&128)for(e=t.child;e!==null;){if(o=fo(e),o!==null){for(t.flags|=128,Ic(r,!1),e=o.updateQueue,t.updateQueue=e,Fc(t,e),t.subtreeFlags=0,e=n,n=t.child;n!==null;)mi(n,e),n=n.sibling;return k(z,z.current&1|2),L&&ji(t,r.treeForkCount),t.child}e=e.sibling}r.tail!==null&&Me()>tu&&(t.flags|=128,a=!0,Ic(r,!1),t.lanes=4194304)}else{if(!a)if(e=fo(o),e!==null){if(t.flags|=128,a=!0,e=e.updateQueue,t.updateQueue=e,Fc(t,e),Ic(r,!0),r.tail===null&&r.tailMode===`hidden`&&!o.alternate&&!L)return G(t),null}else 2*Me()-r.renderingStartTime>tu&&n!==536870912&&(t.flags|=128,a=!0,Ic(r,!1),t.lanes=4194304);r.isBackwards?(o.sibling=t.child,t.child=o):(e=r.last,e===null?t.child=o:e.sibling=o,r.last=o)}return r.tail===null?(G(t),null):(e=r.tail,r.rendering=e,r.tail=e.sibling,r.renderingStartTime=Me(),e.sibling=null,n=z.current,k(z,a?n&1|2:n&1),L&&ji(t,r.treeForkCount),e);case 22:case 23:return uo(t),ro(),r=t.memoizedState!==null,e===null?r&&(t.flags|=8192):e.memoizedState!==null!==r&&(t.flags|=8192),r?n&536870912&&!(t.flags&128)&&(G(t),t.subtreeFlags&6&&(t.flags|=8192)):G(t),n=t.updateQueue,n!==null&&Fc(t,n.retryQueue),n=null,e!==null&&e.memoizedState!==null&&e.memoizedState.cachePool!==null&&(n=e.memoizedState.cachePool.pool),r=null,t.memoizedState!==null&&t.memoizedState.cachePool!==null&&(r=t.memoizedState.cachePool.pool),r!==n&&(t.flags|=2048),e!==null&&de(ya),null;case 24:return n=null,e!==null&&(n=e.memoizedState.cache),t.memoizedState.cache!==n&&(t.flags|=2048),Xi(ca),G(t),null;case 25:return null;case 30:return null}throw Error(i(156,t.tag))}function Rc(e,t){switch(Pi(t),t.tag){case 1:return e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 3:return Xi(ca),_e(),e=t.flags,e&65536&&!(e&128)?(t.flags=e&-65537|128,t):null;case 26:case 27:case 5:return ye(t),null;case 31:if(t.memoizedState!==null){if(uo(t),t.alternate===null)throw Error(i(340));Wi()}return e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 13:if(uo(t),e=t.memoizedState,e!==null&&e.dehydrated!==null){if(t.alternate===null)throw Error(i(340));Wi()}return e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 19:return de(z),null;case 4:return _e(),null;case 10:return Xi(t.type),null;case 22:case 23:return uo(t),ro(),e!==null&&de(ya),e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 24:return Xi(ca),null;case 25:return null;default:return null}}function zc(e,t){switch(Pi(t),t.tag){case 3:Xi(ca),_e();break;case 26:case 27:case 5:ye(t);break;case 4:_e();break;case 31:t.memoizedState!==null&&uo(t);break;case 13:uo(t);break;case 19:de(z);break;case 10:Xi(t.type);break;case 22:case 23:uo(t),ro(),e!==null&&de(ya);break;case 24:Xi(ca)}}function Bc(e,t){try{var n=t.updateQueue,r=n===null?null:n.lastEffect;if(r!==null){var i=r.next;n=i;do{if((n.tag&e)===e){r=void 0;var a=n.create,o=n.inst;r=a(),o.destroy=r}n=n.next}while(n!==i)}}catch(e){Z(t,t.return,e)}}function Vc(e,t,n){try{var r=t.updateQueue,i=r===null?null:r.lastEffect;if(i!==null){var a=i.next;r=a;do{if((r.tag&e)===e){var o=r.inst,s=o.destroy;if(s!==void 0){o.destroy=void 0,i=t;var c=n,l=s;try{l()}catch(e){Z(i,c,e)}}}r=r.next}while(r!==a)}}catch(e){Z(t,t.return,e)}}function Hc(e){var t=e.updateQueue;if(t!==null){var n=e.stateNode;try{Qa(t,n)}catch(t){Z(e,e.return,t)}}}function Uc(e,t,n){n.props=Ks(e.type,e.memoizedProps),n.state=e.memoizedState;try{n.componentWillUnmount()}catch(n){Z(e,t,n)}}function Wc(e,t){try{var n=e.ref;if(n!==null){switch(e.tag){case 26:case 27:case 5:var r=e.stateNode;break;case 30:r=e.stateNode;break;default:r=e.stateNode}typeof n==`function`?e.refCleanup=n(r):n.current=r}}catch(n){Z(e,t,n)}}function Gc(e,t){var n=e.ref,r=e.refCleanup;if(n!==null)if(typeof r==`function`)try{r()}catch(n){Z(e,t,n)}finally{e.refCleanup=null,e=e.alternate,e!=null&&(e.refCleanup=null)}else if(typeof n==`function`)try{n(null)}catch(n){Z(e,t,n)}else n.current=null}function Kc(e){var t=e.type,n=e.memoizedProps,r=e.stateNode;try{a:switch(t){case`button`:case`input`:case`select`:case`textarea`:n.autoFocus&&r.focus();break a;case`img`:n.src?r.src=n.src:n.srcSet&&(r.srcset=n.srcSet)}}catch(t){Z(e,e.return,t)}}function qc(e,t,n){try{var r=e.stateNode;Fd(r,e.type,n,t),r[gt]=t}catch(t){Z(e,e.return,t)}}function Jc(e){return e.tag===5||e.tag===3||e.tag===26||e.tag===27&&Zd(e.type)||e.tag===4}function Yc(e){a:for(;;){for(;e.sibling===null;){if(e.return===null||Jc(e.return))return null;e=e.return}for(e.sibling.return=e.return,e=e.sibling;e.tag!==5&&e.tag!==6&&e.tag!==18;){if(e.tag===27&&Zd(e.type)||e.flags&2||e.child===null||e.tag===4)continue a;e.child.return=e,e=e.child}if(!(e.flags&2))return e.stateNode}}function Xc(e,t,n){var r=e.tag;if(r===5||r===6)e=e.stateNode,t?(n.nodeType===9?n.body:n.nodeName===`HTML`?n.ownerDocument.body:n).insertBefore(e,t):(t=n.nodeType===9?n.body:n.nodeName===`HTML`?n.ownerDocument.body:n,t.appendChild(e),n=n._reactRootContainer,n!=null||t.onclick!==null||(t.onclick=sn));else if(r!==4&&(r===27&&Zd(e.type)&&(n=e.stateNode,t=null),e=e.child,e!==null))for(Xc(e,t,n),e=e.sibling;e!==null;)Xc(e,t,n),e=e.sibling}function Zc(e,t,n){var r=e.tag;if(r===5||r===6)e=e.stateNode,t?n.insertBefore(e,t):n.appendChild(e);else if(r!==4&&(r===27&&Zd(e.type)&&(n=e.stateNode),e=e.child,e!==null))for(Zc(e,t,n),e=e.sibling;e!==null;)Zc(e,t,n),e=e.sibling}function Qc(e){var t=e.stateNode,n=e.memoizedProps;try{for(var r=e.type,i=t.attributes;i.length;)t.removeAttributeNode(i[0]);Pd(t,r,n),t[ht]=e,t[gt]=n}catch(t){Z(e,e.return,t)}}var $c=!1,el=!1,tl=!1,nl=typeof WeakSet==`function`?WeakSet:Set,rl=null;function il(e,t){if(e=e.containerInfo,Rd=sp,e=jr(e),Mr(e)){if(`selectionStart`in e)var n={start:e.selectionStart,end:e.selectionEnd};else a:{n=(n=e.ownerDocument)&&n.defaultView||window;var r=n.getSelection&&n.getSelection();if(r&&r.rangeCount!==0){n=r.anchorNode;var a=r.anchorOffset,o=r.focusNode;r=r.focusOffset;try{n.nodeType,o.nodeType}catch{n=null;break a}var s=0,c=-1,l=-1,u=0,d=0,f=e,p=null;b:for(;;){for(var m;f!==n||a!==0&&f.nodeType!==3||(c=s+a),f!==o||r!==0&&f.nodeType!==3||(l=s+r),f.nodeType===3&&(s+=f.nodeValue.length),(m=f.firstChild)!==null;)p=f,f=m;for(;;){if(f===e)break b;if(p===n&&++u===a&&(c=s),p===o&&++d===r&&(l=s),(m=f.nextSibling)!==null)break;f=p,p=f.parentNode}f=m}n=c===-1||l===-1?null:{start:c,end:l}}else n=null}n||={start:0,end:0}}else n=null;for(zd={focusedElem:e,selectionRange:n},sp=!1,rl=t;rl!==null;)if(t=rl,e=t.child,t.subtreeFlags&1028&&e!==null)e.return=t,rl=e;else for(;rl!==null;){switch(t=rl,o=t.alternate,e=t.flags,t.tag){case 0:if(e&4&&(e=t.updateQueue,e=e===null?null:e.events,e!==null))for(n=0;n<e.length;n++)a=e[n],a.ref.impl=a.nextImpl;break;case 11:case 15:break;case 1:if(e&1024&&o!==null){e=void 0,n=t,a=o.memoizedProps,o=o.memoizedState,r=n.stateNode;try{var h=Ks(n.type,a);e=r.getSnapshotBeforeUpdate(h,o),r.__reactInternalSnapshotBeforeUpdate=e}catch(e){Z(n,n.return,e)}}break;case 3:if(e&1024){if(e=t.stateNode.containerInfo,n=e.nodeType,n===9)ef(e);else if(n===1)switch(e.nodeName){case`HEAD`:case`HTML`:case`BODY`:ef(e);break;default:e.textContent=``}}break;case 5:case 26:case 27:case 6:case 4:case 17:break;default:if(e&1024)throw Error(i(163))}if(e=t.sibling,e!==null){e.return=t.return,rl=e;break}rl=t.return}}function al(e,t,n){var r=n.flags;switch(n.tag){case 0:case 11:case 15:bl(e,n),r&4&&Bc(5,n);break;case 1:if(bl(e,n),r&4)if(e=n.stateNode,t===null)try{e.componentDidMount()}catch(e){Z(n,n.return,e)}else{var i=Ks(n.type,t.memoizedProps);t=t.memoizedState;try{e.componentDidUpdate(i,t,e.__reactInternalSnapshotBeforeUpdate)}catch(e){Z(n,n.return,e)}}r&64&&Hc(n),r&512&&Wc(n,n.return);break;case 3:if(bl(e,n),r&64&&(e=n.updateQueue,e!==null)){if(t=null,n.child!==null)switch(n.child.tag){case 27:case 5:t=n.child.stateNode;break;case 1:t=n.child.stateNode}try{Qa(e,t)}catch(e){Z(n,n.return,e)}}break;case 27:t===null&&r&4&&Qc(n);case 26:case 5:bl(e,n),t===null&&r&4&&Kc(n),r&512&&Wc(n,n.return);break;case 12:bl(e,n);break;case 31:bl(e,n),r&4&&dl(e,n);break;case 13:bl(e,n),r&4&&fl(e,n),r&64&&(e=n.memoizedState,e!==null&&(e=e.dehydrated,e!==null&&(n=Ju.bind(null,n),sf(e,n))));break;case 22:if(r=n.memoizedState!==null||$c,!r){t=t!==null&&t.memoizedState!==null||el,i=$c;var a=el;$c=r,(el=t)&&!a?Sl(e,n,(n.subtreeFlags&8772)!=0):bl(e,n),$c=i,el=a}break;case 30:break;default:bl(e,n)}}function ol(e){var t=e.alternate;t!==null&&(e.alternate=null,ol(t)),e.child=null,e.deletions=null,e.sibling=null,e.tag===5&&(t=e.stateNode,t!==null&&Ct(t)),e.stateNode=null,e.return=null,e.dependencies=null,e.memoizedProps=null,e.memoizedState=null,e.pendingProps=null,e.stateNode=null,e.updateQueue=null}var sl=null,cl=!1;function ll(e,t,n){for(n=n.child;n!==null;)ul(e,t,n),n=n.sibling}function ul(e,t,n){if(He&&typeof He.onCommitFiberUnmount==`function`)try{He.onCommitFiberUnmount(Ve,n)}catch{}switch(n.tag){case 26:el||Gc(n,t),ll(e,t,n),n.memoizedState?n.memoizedState.count--:n.stateNode&&(n=n.stateNode,n.parentNode.removeChild(n));break;case 27:el||Gc(n,t);var r=sl,i=cl;Zd(n.type)&&(sl=n.stateNode,cl=!1),ll(e,t,n),pf(n.stateNode),sl=r,cl=i;break;case 5:el||Gc(n,t);case 6:if(r=sl,i=cl,sl=null,ll(e,t,n),sl=r,cl=i,sl!==null)if(cl)try{(sl.nodeType===9?sl.body:sl.nodeName===`HTML`?sl.ownerDocument.body:sl).removeChild(n.stateNode)}catch(e){Z(n,t,e)}else try{sl.removeChild(n.stateNode)}catch(e){Z(n,t,e)}break;case 18:sl!==null&&(cl?(e=sl,Qd(e.nodeType===9?e.body:e.nodeName===`HTML`?e.ownerDocument.body:e,n.stateNode),Np(e)):Qd(sl,n.stateNode));break;case 4:r=sl,i=cl,sl=n.stateNode.containerInfo,cl=!0,ll(e,t,n),sl=r,cl=i;break;case 0:case 11:case 14:case 15:Vc(2,n,t),el||Vc(4,n,t),ll(e,t,n);break;case 1:el||(Gc(n,t),r=n.stateNode,typeof r.componentWillUnmount==`function`&&Uc(n,t,r)),ll(e,t,n);break;case 21:ll(e,t,n);break;case 22:el=(r=el)||n.memoizedState!==null,ll(e,t,n),el=r;break;default:ll(e,t,n)}}function dl(e,t){if(t.memoizedState===null&&(e=t.alternate,e!==null&&(e=e.memoizedState,e!==null))){e=e.dehydrated;try{Np(e)}catch(e){Z(t,t.return,e)}}}function fl(e,t){if(t.memoizedState===null&&(e=t.alternate,e!==null&&(e=e.memoizedState,e!==null&&(e=e.dehydrated,e!==null))))try{Np(e)}catch(e){Z(t,t.return,e)}}function pl(e){switch(e.tag){case 31:case 13:case 19:var t=e.stateNode;return t===null&&(t=e.stateNode=new nl),t;case 22:return e=e.stateNode,t=e._retryCache,t===null&&(t=e._retryCache=new nl),t;default:throw Error(i(435,e.tag))}}function ml(e,t){var n=pl(e);t.forEach(function(t){if(!n.has(t)){n.add(t);var r=Yu.bind(null,e,t);t.then(r,r)}})}function hl(e,t){var n=t.deletions;if(n!==null)for(var r=0;r<n.length;r++){var a=n[r],o=e,s=t,c=s;a:for(;c!==null;){switch(c.tag){case 27:if(Zd(c.type)){sl=c.stateNode,cl=!1;break a}break;case 5:sl=c.stateNode,cl=!1;break a;case 3:case 4:sl=c.stateNode.containerInfo,cl=!0;break a}c=c.return}if(sl===null)throw Error(i(160));ul(o,s,a),sl=null,cl=!1,o=a.alternate,o!==null&&(o.return=null),a.return=null}if(t.subtreeFlags&13886)for(t=t.child;t!==null;)_l(t,e),t=t.sibling}var gl=null;function _l(e,t){var n=e.alternate,r=e.flags;switch(e.tag){case 0:case 11:case 14:case 15:hl(t,e),vl(e),r&4&&(Vc(3,e,e.return),Bc(3,e),Vc(5,e,e.return));break;case 1:hl(t,e),vl(e),r&512&&(el||n===null||Gc(n,n.return)),r&64&&$c&&(e=e.updateQueue,e!==null&&(r=e.callbacks,r!==null&&(n=e.shared.hiddenCallbacks,e.shared.hiddenCallbacks=n===null?r:n.concat(r))));break;case 26:var a=gl;if(hl(t,e),vl(e),r&512&&(el||n===null||Gc(n,n.return)),r&4){var o=n===null?null:n.memoizedState;if(r=e.memoizedState,n===null)if(r===null)if(e.stateNode===null){a:{r=e.type,n=e.memoizedProps,a=a.ownerDocument||a;b:switch(r){case`title`:o=a.getElementsByTagName(`title`)[0],(!o||o[St]||o[ht]||o.namespaceURI===`http://www.w3.org/2000/svg`||o.hasAttribute(`itemprop`))&&(o=a.createElement(r),a.head.insertBefore(o,a.querySelector(`head > title`))),Pd(o,r,n),o[ht]=e,Ot(o),r=o;break a;case`link`:var s=Vf(`link`,`href`,a).get(r+(n.href||``));if(s){for(var c=0;c<s.length;c++)if(o=s[c],o.getAttribute(`href`)===(n.href==null||n.href===``?null:n.href)&&o.getAttribute(`rel`)===(n.rel==null?null:n.rel)&&o.getAttribute(`title`)===(n.title==null?null:n.title)&&o.getAttribute(`crossorigin`)===(n.crossOrigin==null?null:n.crossOrigin)){s.splice(c,1);break b}}o=a.createElement(r),Pd(o,r,n),a.head.appendChild(o);break;case`meta`:if(s=Vf(`meta`,`content`,a).get(r+(n.content||``))){for(c=0;c<s.length;c++)if(o=s[c],o.getAttribute(`content`)===(n.content==null?null:``+n.content)&&o.getAttribute(`name`)===(n.name==null?null:n.name)&&o.getAttribute(`property`)===(n.property==null?null:n.property)&&o.getAttribute(`http-equiv`)===(n.httpEquiv==null?null:n.httpEquiv)&&o.getAttribute(`charset`)===(n.charSet==null?null:n.charSet)){s.splice(c,1);break b}}o=a.createElement(r),Pd(o,r,n),a.head.appendChild(o);break;default:throw Error(i(468,r))}o[ht]=e,Ot(o),r=o}e.stateNode=r}else Hf(a,e.type,e.stateNode);else e.stateNode=If(a,r,e.memoizedProps);else o===r?r===null&&e.stateNode!==null&&qc(e,e.memoizedProps,n.memoizedProps):(o===null?n.stateNode!==null&&(n=n.stateNode,n.parentNode.removeChild(n)):o.count--,r===null?Hf(a,e.type,e.stateNode):If(a,r,e.memoizedProps))}break;case 27:hl(t,e),vl(e),r&512&&(el||n===null||Gc(n,n.return)),n!==null&&r&4&&qc(e,e.memoizedProps,n.memoizedProps);break;case 5:if(hl(t,e),vl(e),r&512&&(el||n===null||Gc(n,n.return)),e.flags&32){a=e.stateNode;try{Qt(a,``)}catch(t){Z(e,e.return,t)}}r&4&&e.stateNode!=null&&(a=e.memoizedProps,qc(e,a,n===null?a:n.memoizedProps)),r&1024&&(tl=!0);break;case 6:if(hl(t,e),vl(e),r&4){if(e.stateNode===null)throw Error(i(162));r=e.memoizedProps,n=e.stateNode;try{n.nodeValue=r}catch(t){Z(e,e.return,t)}}break;case 3:if(Bf=null,a=gl,gl=gf(t.containerInfo),hl(t,e),gl=a,vl(e),r&4&&n!==null&&n.memoizedState.isDehydrated)try{Np(t.containerInfo)}catch(t){Z(e,e.return,t)}tl&&(tl=!1,yl(e));break;case 4:r=gl,gl=gf(e.stateNode.containerInfo),hl(t,e),vl(e),gl=r;break;case 12:hl(t,e),vl(e);break;case 31:hl(t,e),vl(e),r&4&&(r=e.updateQueue,r!==null&&(e.updateQueue=null,ml(e,r)));break;case 13:hl(t,e),vl(e),e.child.flags&8192&&e.memoizedState!==null!=(n!==null&&n.memoizedState!==null)&&($l=Me()),r&4&&(r=e.updateQueue,r!==null&&(e.updateQueue=null,ml(e,r)));break;case 22:a=e.memoizedState!==null;var l=n!==null&&n.memoizedState!==null,u=$c,d=el;if($c=u||a,el=d||l,hl(t,e),el=d,$c=u,vl(e),r&8192)a:for(t=e.stateNode,t._visibility=a?t._visibility&-2:t._visibility|1,a&&(n===null||l||$c||el||xl(e)),n=null,t=e;;){if(t.tag===5||t.tag===26){if(n===null){l=n=t;try{if(o=l.stateNode,a)s=o.style,typeof s.setProperty==`function`?s.setProperty(`display`,`none`,`important`):s.display=`none`;else{c=l.stateNode;var f=l.memoizedProps.style,p=f!=null&&f.hasOwnProperty(`display`)?f.display:null;c.style.display=p==null||typeof p==`boolean`?``:(``+p).trim()}}catch(e){Z(l,l.return,e)}}}else if(t.tag===6){if(n===null){l=t;try{l.stateNode.nodeValue=a?``:l.memoizedProps}catch(e){Z(l,l.return,e)}}}else if(t.tag===18){if(n===null){l=t;try{var m=l.stateNode;a?$d(m,!0):$d(l.stateNode,!1)}catch(e){Z(l,l.return,e)}}}else if((t.tag!==22&&t.tag!==23||t.memoizedState===null||t===e)&&t.child!==null){t.child.return=t,t=t.child;continue}if(t===e)break a;for(;t.sibling===null;){if(t.return===null||t.return===e)break a;n===t&&(n=null),t=t.return}n===t&&(n=null),t.sibling.return=t.return,t=t.sibling}r&4&&(r=e.updateQueue,r!==null&&(n=r.retryQueue,n!==null&&(r.retryQueue=null,ml(e,n))));break;case 19:hl(t,e),vl(e),r&4&&(r=e.updateQueue,r!==null&&(e.updateQueue=null,ml(e,r)));break;case 30:break;case 21:break;default:hl(t,e),vl(e)}}function vl(e){var t=e.flags;if(t&2){try{for(var n,r=e.return;r!==null;){if(Jc(r)){n=r;break}r=r.return}if(n==null)throw Error(i(160));switch(n.tag){case 27:var a=n.stateNode;Zc(e,Yc(e),a);break;case 5:var o=n.stateNode;n.flags&32&&(Qt(o,``),n.flags&=-33),Zc(e,Yc(e),o);break;case 3:case 4:var s=n.stateNode.containerInfo;Xc(e,Yc(e),s);break;default:throw Error(i(161))}}catch(t){Z(e,e.return,t)}e.flags&=-3}t&4096&&(e.flags&=-4097)}function yl(e){if(e.subtreeFlags&1024)for(e=e.child;e!==null;){var t=e;yl(t),t.tag===5&&t.flags&1024&&t.stateNode.reset(),e=e.sibling}}function bl(e,t){if(t.subtreeFlags&8772)for(t=t.child;t!==null;)al(e,t.alternate,t),t=t.sibling}function xl(e){for(e=e.child;e!==null;){var t=e;switch(t.tag){case 0:case 11:case 14:case 15:Vc(4,t,t.return),xl(t);break;case 1:Gc(t,t.return);var n=t.stateNode;typeof n.componentWillUnmount==`function`&&Uc(t,t.return,n),xl(t);break;case 27:pf(t.stateNode);case 26:case 5:Gc(t,t.return),xl(t);break;case 22:t.memoizedState===null&&xl(t);break;case 30:xl(t);break;default:xl(t)}e=e.sibling}}function Sl(e,t,n){for(n&&=(t.subtreeFlags&8772)!=0,t=t.child;t!==null;){var r=t.alternate,i=e,a=t,o=a.flags;switch(a.tag){case 0:case 11:case 15:Sl(i,a,n),Bc(4,a);break;case 1:if(Sl(i,a,n),r=a,i=r.stateNode,typeof i.componentDidMount==`function`)try{i.componentDidMount()}catch(e){Z(r,r.return,e)}if(r=a,i=r.updateQueue,i!==null){var s=r.stateNode;try{var c=i.shared.hiddenCallbacks;if(c!==null)for(i.shared.hiddenCallbacks=null,i=0;i<c.length;i++)Za(c[i],s)}catch(e){Z(r,r.return,e)}}n&&o&64&&Hc(a),Wc(a,a.return);break;case 27:Qc(a);case 26:case 5:Sl(i,a,n),n&&r===null&&o&4&&Kc(a),Wc(a,a.return);break;case 12:Sl(i,a,n);break;case 31:Sl(i,a,n),n&&o&4&&dl(i,a);break;case 13:Sl(i,a,n),n&&o&4&&fl(i,a);break;case 22:a.memoizedState===null&&Sl(i,a,n),Wc(a,a.return);break;case 30:break;default:Sl(i,a,n)}t=t.sibling}}function Cl(e,t){var n=null;e!==null&&e.memoizedState!==null&&e.memoizedState.cachePool!==null&&(n=e.memoizedState.cachePool.pool),e=null,t.memoizedState!==null&&t.memoizedState.cachePool!==null&&(e=t.memoizedState.cachePool.pool),e!==n&&(e!=null&&e.refCount++,n!=null&&ua(n))}function wl(e,t){e=null,t.alternate!==null&&(e=t.alternate.memoizedState.cache),t=t.memoizedState.cache,t!==e&&(t.refCount++,e!=null&&ua(e))}function Tl(e,t,n,r){if(t.subtreeFlags&10256)for(t=t.child;t!==null;)El(e,t,n,r),t=t.sibling}function El(e,t,n,r){var i=t.flags;switch(t.tag){case 0:case 11:case 15:Tl(e,t,n,r),i&2048&&Bc(9,t);break;case 1:Tl(e,t,n,r);break;case 3:Tl(e,t,n,r),i&2048&&(e=null,t.alternate!==null&&(e=t.alternate.memoizedState.cache),t=t.memoizedState.cache,t!==e&&(t.refCount++,e!=null&&ua(e)));break;case 12:if(i&2048){Tl(e,t,n,r),e=t.stateNode;try{var a=t.memoizedProps,o=a.id,s=a.onPostCommit;typeof s==`function`&&s(o,t.alternate===null?`mount`:`update`,e.passiveEffectDuration,-0)}catch(e){Z(t,t.return,e)}}else Tl(e,t,n,r);break;case 31:Tl(e,t,n,r);break;case 13:Tl(e,t,n,r);break;case 23:break;case 22:a=t.stateNode,o=t.alternate,t.memoizedState===null?a._visibility&2?Tl(e,t,n,r):(a._visibility|=2,Dl(e,t,n,r,(t.subtreeFlags&10256)!=0||!1)):a._visibility&2?Tl(e,t,n,r):Ol(e,t),i&2048&&Cl(o,t);break;case 24:Tl(e,t,n,r),i&2048&&wl(t.alternate,t);break;default:Tl(e,t,n,r)}}function Dl(e,t,n,r,i){for(i&&=(t.subtreeFlags&10256)!=0||!1,t=t.child;t!==null;){var a=e,o=t,s=n,c=r,l=o.flags;switch(o.tag){case 0:case 11:case 15:Dl(a,o,s,c,i),Bc(8,o);break;case 23:break;case 22:var u=o.stateNode;o.memoizedState===null?(u._visibility|=2,Dl(a,o,s,c,i)):u._visibility&2?Dl(a,o,s,c,i):Ol(a,o),i&&l&2048&&Cl(o.alternate,o);break;case 24:Dl(a,o,s,c,i),i&&l&2048&&wl(o.alternate,o);break;default:Dl(a,o,s,c,i)}t=t.sibling}}function Ol(e,t){if(t.subtreeFlags&10256)for(t=t.child;t!==null;){var n=e,r=t,i=r.flags;switch(r.tag){case 22:Ol(n,r),i&2048&&Cl(r.alternate,r);break;case 24:Ol(n,r),i&2048&&wl(r.alternate,r);break;default:Ol(n,r)}t=t.sibling}}var kl=8192;function Al(e,t,n){if(e.subtreeFlags&kl)for(e=e.child;e!==null;)jl(e,t,n),e=e.sibling}function jl(e,t,n){switch(e.tag){case 26:Al(e,t,n),e.flags&kl&&e.memoizedState!==null&&Gf(n,gl,e.memoizedState,e.memoizedProps);break;case 5:Al(e,t,n);break;case 3:case 4:var r=gl;gl=gf(e.stateNode.containerInfo),Al(e,t,n),gl=r;break;case 22:e.memoizedState===null&&(r=e.alternate,r!==null&&r.memoizedState!==null?(r=kl,kl=16777216,Al(e,t,n),kl=r):Al(e,t,n));break;default:Al(e,t,n)}}function Ml(e){var t=e.alternate;if(t!==null&&(e=t.child,e!==null)){t.child=null;do t=e.sibling,e.sibling=null,e=t;while(e!==null)}}function Nl(e){var t=e.deletions;if(e.flags&16){if(t!==null)for(var n=0;n<t.length;n++){var r=t[n];rl=r,Il(r,e)}Ml(e)}if(e.subtreeFlags&10256)for(e=e.child;e!==null;)Pl(e),e=e.sibling}function Pl(e){switch(e.tag){case 0:case 11:case 15:Nl(e),e.flags&2048&&Vc(9,e,e.return);break;case 3:Nl(e);break;case 12:Nl(e);break;case 22:var t=e.stateNode;e.memoizedState!==null&&t._visibility&2&&(e.return===null||e.return.tag!==13)?(t._visibility&=-3,Fl(e)):Nl(e);break;default:Nl(e)}}function Fl(e){var t=e.deletions;if(e.flags&16){if(t!==null)for(var n=0;n<t.length;n++){var r=t[n];rl=r,Il(r,e)}Ml(e)}for(e=e.child;e!==null;){switch(t=e,t.tag){case 0:case 11:case 15:Vc(8,t,t.return),Fl(t);break;case 22:n=t.stateNode,n._visibility&2&&(n._visibility&=-3,Fl(t));break;default:Fl(t)}e=e.sibling}}function Il(e,t){for(;rl!==null;){var n=rl;switch(n.tag){case 0:case 11:case 15:Vc(8,n,t);break;case 23:case 22:if(n.memoizedState!==null&&n.memoizedState.cachePool!==null){var r=n.memoizedState.cachePool.pool;r!=null&&r.refCount++}break;case 24:ua(n.memoizedState.cache)}if(r=n.child,r!==null)r.return=n,rl=r;else a:for(n=e;rl!==null;){r=rl;var i=r.sibling,a=r.return;if(ol(r),r===n){rl=null;break a}if(i!==null){i.return=a,rl=i;break a}rl=a}}}var Ll={getCacheForType:function(e){var t=na(ca),n=t.data.get(e);return n===void 0&&(n=e(),t.data.set(e,n)),n},cacheSignal:function(){return na(ca).controller.signal}},Rl=typeof WeakMap==`function`?WeakMap:Map,K=0,q=null,J=null,Y=0,X=0,zl=null,Bl=!1,Vl=!1,Hl=!1,Ul=0,Wl=0,Gl=0,Kl=0,ql=0,Jl=0,Yl=0,Xl=null,Zl=null,Ql=!1,$l=0,eu=0,tu=1/0,nu=null,ru=null,iu=0,au=null,ou=null,su=0,cu=0,lu=null,uu=null,du=0,fu=null;function pu(){return K&2&&Y!==0?Y&-Y:E.T===null?ft():dd()}function mu(){if(Jl===0)if(!(Y&536870912)||L){var e=Ye;Ye<<=1,!(Ye&3932160)&&(Ye=262144),Jl=e}else Jl=536870912;return e=io.current,e!==null&&(e.flags|=32),Jl}function hu(e,t,n){(e===q&&(X===2||X===9)||e.cancelPendingCommit!==null)&&(Su(e,0),yu(e,Y,Jl,!1)),at(e,n),(!(K&2)||e!==q)&&(e===q&&(!(K&2)&&(Kl|=n),Wl===4&&yu(e,Y,Jl,!1)),rd(e))}function gu(e,t,n){if(K&6)throw Error(i(327));var r=!n&&(t&127)==0&&(t&e.expiredLanes)===0||tt(e,t),a=r?Au(e,t):Ou(e,t,!0),o=r;do{if(a===0){Vl&&!r&&yu(e,t,0,!1);break}else{if(n=e.current.alternate,o&&!vu(n)){a=Ou(e,t,!1),o=!1;continue}if(a===2){if(o=t,e.errorRecoveryDisabledLanes&o)var s=0;else s=e.pendingLanes&-536870913,s=s===0?s&536870912?536870912:0:s;if(s!==0){t=s;a:{var c=e;a=Xl;var l=c.current.memoizedState.isDehydrated;if(l&&(Su(c,s).flags|=256),s=Ou(c,s,!1),s!==2){if(Hl&&!l){c.errorRecoveryDisabledLanes|=o,Kl|=o,a=4;break a}o=Zl,Zl=a,o!==null&&(Zl===null?Zl=o:Zl.push.apply(Zl,o))}a=s}if(o=!1,a!==2)continue}}if(a===1){Su(e,0),yu(e,t,0,!0);break}a:{switch(r=e,o=a,o){case 0:case 1:throw Error(i(345));case 4:if((t&4194048)!==t)break;case 6:yu(r,t,Jl,!Bl);break a;case 2:Zl=null;break;case 3:case 5:break;default:throw Error(i(329))}if((t&62914560)===t&&(a=$l+300-Me(),10<a)){if(yu(r,t,Jl,!Bl),et(r,0,!0)!==0)break a;su=t,r.timeoutHandle=Kd(_u.bind(null,r,n,Zl,nu,Ql,t,Jl,Kl,Yl,Bl,o,`Throttled`,-0,0),a);break a}_u(r,n,Zl,nu,Ql,t,Jl,Kl,Yl,Bl,o,null,-0,0)}}break}while(1);rd(e)}function _u(e,t,n,r,i,a,o,s,c,l,u,d,f,p){if(e.timeoutHandle=-1,d=t.subtreeFlags,d&8192||(d&16785408)==16785408){d={stylesheets:null,count:0,imgCount:0,imgBytes:0,suspenseyImages:[],waitingForImages:!0,waitingForViewTransition:!1,unsuspend:sn},jl(t,a,d);var m=(a&62914560)===a?$l-Me():(a&4194048)===a?eu-Me():0;if(m=qf(d,m),m!==null){su=a,e.cancelPendingCommit=m(Lu.bind(null,e,t,a,n,r,i,o,s,c,u,d,null,f,p)),yu(e,a,o,!l);return}}Lu(e,t,a,n,r,i,o,s,c)}function vu(e){for(var t=e;;){var n=t.tag;if((n===0||n===11||n===15)&&t.flags&16384&&(n=t.updateQueue,n!==null&&(n=n.stores,n!==null)))for(var r=0;r<n.length;r++){var i=n[r],a=i.getSnapshot;i=i.value;try{if(!Er(a(),i))return!1}catch{return!1}}if(n=t.child,t.subtreeFlags&16384&&n!==null)n.return=t,t=n;else{if(t===e)break;for(;t.sibling===null;){if(t.return===null||t.return===e)return!0;t=t.return}t.sibling.return=t.return,t=t.sibling}}return!0}function yu(e,t,n,r){t&=~ql,t&=~Kl,e.suspendedLanes|=t,e.pingedLanes&=~t,r&&(e.warmLanes|=t),r=e.expirationTimes;for(var i=t;0<i;){var a=31-We(i),o=1<<a;r[a]=-1,i&=~o}n!==0&&st(e,n,t)}function bu(){return K&6?!0:(id(0,!1),!1)}function xu(){if(J!==null){if(X===0)var e=J.return;else e=J,R=Ji=null,Oo(e),Na=null,Pa=0,e=J;for(;e!==null;)zc(e.alternate,e),e=e.return;J=null}}function Su(e,t){var n=e.timeoutHandle;n!==-1&&(e.timeoutHandle=-1,qd(n)),n=e.cancelPendingCommit,n!==null&&(e.cancelPendingCommit=null,n()),su=0,xu(),q=e,J=n=pi(e.current,null),Y=t,X=0,zl=null,Bl=!1,Vl=tt(e,t),Hl=!1,Yl=Jl=ql=Kl=Gl=Wl=0,Zl=Xl=null,Ql=!1,t&8&&(t|=t&32);var r=e.entangledLanes;if(r!==0)for(e=e.entanglements,r&=t;0<r;){var i=31-We(r),a=1<<i;t|=e[i],r&=~a}return Ul=t,ri(),n}function Cu(e,t){B=null,E.H=Rs,t===Ca||t===Ta?(t=ja(),X=3):t===wa?(t=ja(),X=4):X=t===nc?8:typeof t==`object`&&t&&typeof t.then==`function`?6:1,zl=t,J===null&&(Wl=1,Xs(e,xi(t,e.current)))}function wu(){var e=io.current;return e===null?!0:(Y&4194048)===Y?ao===null:(Y&62914560)===Y||Y&536870912?e===ao:!1}function Tu(){var e=E.H;return E.H=Rs,e===null?Rs:e}function Eu(){var e=E.A;return E.A=Ll,e}function Du(){Wl=4,Bl||(Y&4194048)!==Y&&io.current!==null||(Vl=!0),!(Gl&134217727)&&!(Kl&134217727)||q===null||yu(q,Y,Jl,!1)}function Ou(e,t,n){var r=K;K|=2;var i=Tu(),a=Eu();(q!==e||Y!==t)&&(nu=null,Su(e,t)),t=!1;var o=Wl;a:do try{if(X!==0&&J!==null){var s=J,c=zl;switch(X){case 8:xu(),o=6;break a;case 3:case 2:case 9:case 6:io.current===null&&(t=!0);var l=X;if(X=0,zl=null,Pu(e,s,c,l),n&&Vl){o=0;break a}break;default:l=X,X=0,zl=null,Pu(e,s,c,l)}}ku(),o=Wl;break}catch(t){Cu(e,t)}while(1);return t&&e.shellSuspendCounter++,R=Ji=null,K=r,E.H=i,E.A=a,J===null&&(q=null,Y=0,ri()),o}function ku(){for(;J!==null;)Mu(J)}function Au(e,t){var n=K;K|=2;var r=Tu(),a=Eu();q!==e||Y!==t?(nu=null,tu=Me()+500,Su(e,t)):Vl=tt(e,t);a:do try{if(X!==0&&J!==null){t=J;var o=zl;b:switch(X){case 1:X=0,zl=null,Pu(e,t,o,1);break;case 2:case 9:if(Da(o)){X=0,zl=null,Nu(t);break}t=function(){X!==2&&X!==9||q!==e||(X=7),rd(e)},o.then(t,t);break a;case 3:X=7;break a;case 4:X=5;break a;case 7:Da(o)?(X=0,zl=null,Nu(t)):(X=0,zl=null,Pu(e,t,o,7));break;case 5:var s=null;switch(J.tag){case 26:s=J.memoizedState;case 5:case 27:var c=J;if(s?Wf(s):c.stateNode.complete){X=0,zl=null;var l=c.sibling;if(l!==null)J=l;else{var u=c.return;u===null?J=null:(J=u,Fu(u))}break b}}X=0,zl=null,Pu(e,t,o,5);break;case 6:X=0,zl=null,Pu(e,t,o,6);break;case 8:xu(),Wl=6;break a;default:throw Error(i(462))}}ju();break}catch(t){Cu(e,t)}while(1);return R=Ji=null,E.H=r,E.A=a,K=n,J===null?(q=null,Y=0,ri(),Wl):0}function ju(){for(;J!==null&&!Ae();)Mu(J)}function Mu(e){var t=jc(e.alternate,e,Ul);e.memoizedProps=e.pendingProps,t===null?Fu(e):J=t}function Nu(e){var t=e,n=t.alternate;switch(t.tag){case 15:case 0:t=hc(n,t,t.pendingProps,t.type,void 0,Y);break;case 11:t=hc(n,t,t.pendingProps,t.type.render,t.ref,Y);break;case 5:Oo(t);default:zc(n,t),t=J=mi(t,Ul),t=jc(n,t,Ul)}e.memoizedProps=e.pendingProps,t===null?Fu(e):J=t}function Pu(e,t,n,r){R=Ji=null,Oo(t),Na=null,Pa=0;var i=t.return;try{if(tc(e,i,t,n,Y)){Wl=1,Xs(e,xi(n,e.current)),J=null;return}}catch(t){if(i!==null)throw J=i,t;Wl=1,Xs(e,xi(n,e.current)),J=null;return}t.flags&32768?(L||r===1?e=!0:Vl||Y&536870912?e=!1:(Bl=e=!0,(r===2||r===9||r===3||r===6)&&(r=io.current,r!==null&&r.tag===13&&(r.flags|=16384))),Iu(t,e)):Fu(t)}function Fu(e){var t=e;do{if(t.flags&32768){Iu(t,Bl);return}e=t.return;var n=Lc(t.alternate,t,Ul);if(n!==null){J=n;return}if(t=t.sibling,t!==null){J=t;return}J=t=e}while(t!==null);Wl===0&&(Wl=5)}function Iu(e,t){do{var n=Rc(e.alternate,e);if(n!==null){n.flags&=32767,J=n;return}if(n=e.return,n!==null&&(n.flags|=32768,n.subtreeFlags=0,n.deletions=null),!t&&(e=e.sibling,e!==null)){J=e;return}J=e=n}while(e!==null);Wl=6,J=null}function Lu(e,t,n,r,a,o,s,c,l){e.cancelPendingCommit=null;do Hu();while(iu!==0);if(K&6)throw Error(i(327));if(t!==null){if(t===e.current)throw Error(i(177));if(o=t.lanes|t.childLanes,o|=ni,ot(e,n,o,s,c,l),e===q&&(J=q=null,Y=0),ou=t,au=e,su=n,cu=o,lu=a,uu=r,t.subtreeFlags&10256||t.flags&10256?(e.callbackNode=null,e.callbackPriority=0,Xu(Ie,function(){return Uu(),null})):(e.callbackNode=null,e.callbackPriority=0),r=(t.flags&13878)!=0,t.subtreeFlags&13878||r){r=E.T,E.T=null,a=D.p,D.p=2,s=K,K|=4;try{il(e,t,n)}finally{K=s,D.p=a,E.T=r}}iu=1,Ru(),zu(),Bu()}}function Ru(){if(iu===1){iu=0;var e=au,t=ou,n=(t.flags&13878)!=0;if(t.subtreeFlags&13878||n){n=E.T,E.T=null;var r=D.p;D.p=2;var i=K;K|=4;try{_l(t,e);var a=zd,o=jr(e.containerInfo),s=a.focusedElem,c=a.selectionRange;if(o!==s&&s&&s.ownerDocument&&Ar(s.ownerDocument.documentElement,s)){if(c!==null&&Mr(s)){var l=c.start,u=c.end;if(u===void 0&&(u=l),`selectionStart`in s)s.selectionStart=l,s.selectionEnd=Math.min(u,s.value.length);else{var d=s.ownerDocument||document,f=d&&d.defaultView||window;if(f.getSelection){var p=f.getSelection(),m=s.textContent.length,h=Math.min(c.start,m),g=c.end===void 0?h:Math.min(c.end,m);!p.extend&&h>g&&(o=g,g=h,h=o);var _=kr(s,h),v=kr(s,g);if(_&&v&&(p.rangeCount!==1||p.anchorNode!==_.node||p.anchorOffset!==_.offset||p.focusNode!==v.node||p.focusOffset!==v.offset)){var y=d.createRange();y.setStart(_.node,_.offset),p.removeAllRanges(),h>g?(p.addRange(y),p.extend(v.node,v.offset)):(y.setEnd(v.node,v.offset),p.addRange(y))}}}}for(d=[],p=s;p=p.parentNode;)p.nodeType===1&&d.push({element:p,left:p.scrollLeft,top:p.scrollTop});for(typeof s.focus==`function`&&s.focus(),s=0;s<d.length;s++){var b=d[s];b.element.scrollLeft=b.left,b.element.scrollTop=b.top}}sp=!!Rd,zd=Rd=null}finally{K=i,D.p=r,E.T=n}}e.current=t,iu=2}}function zu(){if(iu===2){iu=0;var e=au,t=ou,n=(t.flags&8772)!=0;if(t.subtreeFlags&8772||n){n=E.T,E.T=null;var r=D.p;D.p=2;var i=K;K|=4;try{al(e,t.alternate,t)}finally{K=i,D.p=r,E.T=n}}iu=3}}function Bu(){if(iu===4||iu===3){iu=0,je();var e=au,t=ou,n=su,r=uu;t.subtreeFlags&10256||t.flags&10256?iu=5:(iu=0,ou=au=null,Vu(e,e.pendingLanes));var i=e.pendingLanes;if(i===0&&(ru=null),dt(n),t=t.stateNode,He&&typeof He.onCommitFiberRoot==`function`)try{He.onCommitFiberRoot(Ve,t,void 0,(t.current.flags&128)==128)}catch{}if(r!==null){t=E.T,i=D.p,D.p=2,E.T=null;try{for(var a=e.onRecoverableError,o=0;o<r.length;o++){var s=r[o];a(s.value,{componentStack:s.stack})}}finally{E.T=t,D.p=i}}su&3&&Hu(),rd(e),i=e.pendingLanes,n&261930&&i&42?e===fu?du++:(du=0,fu=e):du=0,id(0,!1)}}function Vu(e,t){(e.pooledCacheLanes&=t)===0&&(t=e.pooledCache,t!=null&&(e.pooledCache=null,ua(t)))}function Hu(){return Ru(),zu(),Bu(),Uu()}function Uu(){if(iu!==5)return!1;var e=au,t=cu;cu=0;var n=dt(su),r=E.T,a=D.p;try{D.p=32>n?32:n,E.T=null,n=lu,lu=null;var o=au,s=su;if(iu=0,ou=au=null,su=0,K&6)throw Error(i(331));var c=K;if(K|=4,Pl(o.current),El(o,o.current,s,n),K=c,id(0,!1),He&&typeof He.onPostCommitFiberRoot==`function`)try{He.onPostCommitFiberRoot(Ve,o)}catch{}return!0}finally{D.p=a,E.T=r,Vu(e,t)}}function Wu(e,t,n){t=xi(n,t),t=Qs(e.stateNode,t,2),e=Ga(e,t,2),e!==null&&(at(e,2),rd(e))}function Z(e,t,n){if(e.tag===3)Wu(e,e,n);else for(;t!==null;){if(t.tag===3){Wu(t,e,n);break}else if(t.tag===1){var r=t.stateNode;if(typeof t.type.getDerivedStateFromError==`function`||typeof r.componentDidCatch==`function`&&(ru===null||!ru.has(r))){e=xi(n,e),n=$s(2),r=Ga(t,n,2),r!==null&&(ec(n,r,t,e),at(r,2),rd(r));break}}t=t.return}}function Gu(e,t,n){var r=e.pingCache;if(r===null){r=e.pingCache=new Rl;var i=new Set;r.set(t,i)}else i=r.get(t),i===void 0&&(i=new Set,r.set(t,i));i.has(n)||(Hl=!0,i.add(n),e=Ku.bind(null,e,t,n),t.then(e,e))}function Ku(e,t,n){var r=e.pingCache;r!==null&&r.delete(t),e.pingedLanes|=e.suspendedLanes&n,e.warmLanes&=~n,q===e&&(Y&n)===n&&(Wl===4||Wl===3&&(Y&62914560)===Y&&300>Me()-$l?!(K&2)&&Su(e,0):ql|=n,Yl===Y&&(Yl=0)),rd(e)}function qu(e,t){t===0&&(t=rt()),e=oi(e,t),e!==null&&(at(e,t),rd(e))}function Ju(e){var t=e.memoizedState,n=0;t!==null&&(n=t.retryLane),qu(e,n)}function Yu(e,t){var n=0;switch(e.tag){case 31:case 13:var r=e.stateNode,a=e.memoizedState;a!==null&&(n=a.retryLane);break;case 19:r=e.stateNode;break;case 22:r=e.stateNode._retryCache;break;default:throw Error(i(314))}r!==null&&r.delete(t),qu(e,n)}function Xu(e,t){return Oe(e,t)}var Zu=null,Qu=null,$u=!1,ed=!1,td=!1,nd=0;function rd(e){e!==Qu&&e.next===null&&(Qu===null?Zu=Qu=e:Qu=Qu.next=e),ed=!0,$u||($u=!0,ud())}function id(e,t){if(!td&&ed){td=!0;do for(var n=!1,r=Zu;r!==null;){if(!t)if(e!==0){var i=r.pendingLanes;if(i===0)var a=0;else{var o=r.suspendedLanes,s=r.pingedLanes;a=(1<<31-We(42|e)+1)-1,a&=i&~(o&~s),a=a&201326741?a&201326741|1:a?a|2:0}a!==0&&(n=!0,ld(r,a))}else a=Y,a=et(r,r===q?a:0,r.cancelPendingCommit!==null||r.timeoutHandle!==-1),!(a&3)||tt(r,a)||(n=!0,ld(r,a));r=r.next}while(n);td=!1}}function ad(){od()}function od(){ed=$u=!1;var e=0;nd!==0&&Gd()&&(e=nd);for(var t=Me(),n=null,r=Zu;r!==null;){var i=r.next,a=sd(r,t);a===0?(r.next=null,n===null?Zu=i:n.next=i,i===null&&(Qu=n)):(n=r,(e!==0||a&3)&&(ed=!0)),r=i}iu!==0&&iu!==5||id(e,!1),nd!==0&&(nd=0)}function sd(e,t){for(var n=e.suspendedLanes,r=e.pingedLanes,i=e.expirationTimes,a=e.pendingLanes&-62914561;0<a;){var o=31-We(a),s=1<<o,c=i[o];c===-1?((s&n)===0||(s&r)!==0)&&(i[o]=nt(s,t)):c<=t&&(e.expiredLanes|=s),a&=~s}if(t=q,n=Y,n=et(e,e===t?n:0,e.cancelPendingCommit!==null||e.timeoutHandle!==-1),r=e.callbackNode,n===0||e===t&&(X===2||X===9)||e.cancelPendingCommit!==null)return r!==null&&r!==null&&ke(r),e.callbackNode=null,e.callbackPriority=0;if(!(n&3)||tt(e,n)){if(t=n&-n,t===e.callbackPriority)return t;switch(r!==null&&ke(r),dt(n)){case 2:case 8:n=Fe;break;case 32:n=Ie;break;case 268435456:n=Re;break;default:n=Ie}return r=cd.bind(null,e),n=Oe(n,r),e.callbackPriority=t,e.callbackNode=n,t}return r!==null&&r!==null&&ke(r),e.callbackPriority=2,e.callbackNode=null,2}function cd(e,t){if(iu!==0&&iu!==5)return e.callbackNode=null,e.callbackPriority=0,null;var n=e.callbackNode;if(Hu()&&e.callbackNode!==n)return null;var r=Y;return r=et(e,e===q?r:0,e.cancelPendingCommit!==null||e.timeoutHandle!==-1),r===0?null:(gu(e,r,t),sd(e,Me()),e.callbackNode!=null&&e.callbackNode===n?cd.bind(null,e):null)}function ld(e,t){if(Hu())return null;gu(e,t,!0)}function ud(){Yd(function(){K&6?Oe(Pe,ad):od()})}function dd(){if(nd===0){var e=pa;e===0&&(e=Je,Je<<=1,!(Je&261888)&&(Je=256)),nd=e}return nd}function fd(e){return e==null||typeof e==`symbol`||typeof e==`boolean`?null:typeof e==`function`?e:on(``+e)}function pd(e,t){var n=t.ownerDocument.createElement(`input`);return n.name=t.name,n.value=t.value,e.id&&n.setAttribute(`form`,e.id),t.parentNode.insertBefore(n,t),e=new FormData(e),n.parentNode.removeChild(n),e}function md(e,t,n,r,i){if(t===`submit`&&n&&n.stateNode===i){var a=fd((i[gt]||null).action),o=r.submitter;o&&(t=(t=o[gt]||null)?fd(t.formAction):o.getAttribute(`formAction`),t!==null&&(a=t,o=null));var s=new On(`action`,`action`,null,r,i);e.push({event:s,listeners:[{instance:null,listener:function(){if(r.defaultPrevented){if(nd!==0){var e=o?pd(i,o):new FormData(i);ws(n,{pending:!0,data:e,method:i.method,action:a},null,e)}}else typeof a==`function`&&(s.preventDefault(),e=o?pd(i,o):new FormData(i),ws(n,{pending:!0,data:e,method:i.method,action:a},a,e))},currentTarget:i}]})}}for(var hd=0;hd<Zr.length;hd++){var gd=Zr[hd];Qr(gd.toLowerCase(),`on`+(gd[0].toUpperCase()+gd.slice(1)))}Qr(Wr,`onAnimationEnd`),Qr(Gr,`onAnimationIteration`),Qr(Kr,`onAnimationStart`),Qr(`dblclick`,`onDoubleClick`),Qr(`focusin`,`onFocus`),Qr(`focusout`,`onBlur`),Qr(qr,`onTransitionRun`),Qr(Jr,`onTransitionStart`),Qr(Yr,`onTransitionCancel`),Qr(F,`onTransitionEnd`),Mt(`onMouseEnter`,[`mouseout`,`mouseover`]),Mt(`onMouseLeave`,[`mouseout`,`mouseover`]),Mt(`onPointerEnter`,[`pointerout`,`pointerover`]),Mt(`onPointerLeave`,[`pointerout`,`pointerover`]),jt(`onChange`,`change click focusin focusout input keydown keyup selectionchange`.split(` `)),jt(`onSelect`,`focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange`.split(` `)),jt(`onBeforeInput`,[`compositionend`,`keypress`,`textInput`,`paste`]),jt(`onCompositionEnd`,`compositionend focusout keydown keypress keyup mousedown`.split(` `)),jt(`onCompositionStart`,`compositionstart focusout keydown keypress keyup mousedown`.split(` `)),jt(`onCompositionUpdate`,`compositionupdate focusout keydown keypress keyup mousedown`.split(` `));var _d=`abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting`.split(` `),vd=new Set(`beforetoggle cancel close invalid load scroll scrollend toggle`.split(` `).concat(_d));function yd(e,t){t=(t&4)!=0;for(var n=0;n<e.length;n++){var r=e[n],i=r.event;r=r.listeners;a:{var a=void 0;if(t)for(var o=r.length-1;0<=o;o--){var s=r[o],c=s.instance,l=s.currentTarget;if(s=s.listener,c!==a&&i.isPropagationStopped())break a;a=s,i.currentTarget=l;try{a(i)}catch(e){$r(e)}i.currentTarget=null,a=c}else for(o=0;o<r.length;o++){if(s=r[o],c=s.instance,l=s.currentTarget,s=s.listener,c!==a&&i.isPropagationStopped())break a;a=s,i.currentTarget=l;try{a(i)}catch(e){$r(e)}i.currentTarget=null,a=c}}}}function Q(e,t){var n=t[vt];n===void 0&&(n=t[vt]=new Set);var r=e+`__bubble`;n.has(r)||(Cd(t,e,2,!1),n.add(r))}function bd(e,t,n){var r=0;t&&(r|=4),Cd(n,e,r,t)}var xd=`_reactListening`+Math.random().toString(36).slice(2);function Sd(e){if(!e[xd]){e[xd]=!0,kt.forEach(function(t){t!==`selectionchange`&&(vd.has(t)||bd(t,!1,e),bd(t,!0,e))});var t=e.nodeType===9?e:e.ownerDocument;t===null||t[xd]||(t[xd]=!0,bd(`selectionchange`,!1,t))}}function Cd(e,t,n,r){switch(mp(t)){case 2:var i=cp;break;case 8:i=lp;break;default:i=up}n=i.bind(null,t,n,e),i=void 0,!_n||t!==`touchstart`&&t!==`touchmove`&&t!==`wheel`||(i=!0),r?i===void 0?e.addEventListener(t,n,!0):e.addEventListener(t,n,{capture:!0,passive:i}):i===void 0?e.addEventListener(t,n,!1):e.addEventListener(t,n,{passive:i})}function wd(e,t,n,r,i){var a=r;if(!(t&1)&&!(t&2)&&r!==null)a:for(;;){if(r===null)return;var s=r.tag;if(s===3||s===4){var c=r.stateNode.containerInfo;if(c===i)break;if(s===4)for(s=r.return;s!==null;){var l=s.tag;if((l===3||l===4)&&s.stateNode.containerInfo===i)return;s=s.return}for(;c!==null;){if(s=wt(c),s===null)return;if(l=s.tag,l===5||l===6||l===26||l===27){r=a=s;continue a}c=c.parentNode}}r=r.return}mn(function(){var r=a,i=ln(n),s=[];a:{var c=Xr.get(e);if(c!==void 0){var l=On,u=e;switch(e){case`keypress`:if(Cn(n)===0)break a;case`keydown`:case`keyup`:l=N;break;case`focusin`:u=`focus`,l=In;break;case`focusout`:u=`blur`,l=In;break;case`beforeblur`:case`afterblur`:l=In;break;case`click`:if(n.button===2)break a;case`auxclick`:case`dblclick`:case`mousedown`:case`mousemove`:case`mouseup`:case`mouseout`:case`mouseover`:case`contextmenu`:l=Pn;break;case`drag`:case`dragend`:case`dragenter`:case`dragexit`:case`dragleave`:case`dragover`:case`dragstart`:case`drop`:l=Fn;break;case`touchcancel`:case`touchend`:case`touchmove`:case`touchstart`:l=Kn;break;case Wr:case Gr:case Kr:l=Ln;break;case F:l=qn;break;case`scroll`:case`scrollend`:l=An;break;case`wheel`:l=Jn;break;case`copy`:case`cut`:case`paste`:l=Rn;break;case`gotpointercapture`:case`lostpointercapture`:case`pointercancel`:case`pointerdown`:case`pointermove`:case`pointerout`:case`pointerover`:case`pointerup`:l=Gn;break;case`toggle`:case`beforetoggle`:l=Yn}var d=(t&4)!=0,f=!d&&(e===`scroll`||e===`scrollend`),p=d?c===null?null:c+`Capture`:c;d=[];for(var m=r,h;m!==null;){var g=m;if(h=g.stateNode,g=g.tag,g!==5&&g!==26&&g!==27||h===null||p===null||(g=hn(m,p),g!=null&&d.push(Td(m,g,h))),f)break;m=m.return}0<d.length&&(c=new l(c,u,null,n,i),s.push({event:c,listeners:d}))}}if(!(t&7)){a:{if(c=e===`mouseover`||e===`pointerover`,l=e===`mouseout`||e===`pointerout`,c&&n!==cn&&(u=n.relatedTarget||n.fromElement)&&(wt(u)||u[_t]))break a;if((l||c)&&(c=i.window===i?i:(c=i.ownerDocument)?c.defaultView||c.parentWindow:window,l?(u=n.relatedTarget||n.toElement,l=r,u=u?wt(u):null,u!==null&&(f=o(u),d=u.tag,u!==f||d!==5&&d!==27&&d!==6)&&(u=null)):(l=null,u=r),l!==u)){if(d=Pn,g=`onMouseLeave`,p=`onMouseEnter`,m=`mouse`,(e===`pointerout`||e===`pointerover`)&&(d=Gn,g=`onPointerLeave`,p=`onPointerEnter`,m=`pointer`),f=l==null?c:Et(l),h=u==null?c:Et(u),c=new d(g,m+`leave`,l,n,i),c.target=f,c.relatedTarget=h,g=null,wt(i)===r&&(d=new d(p,m+`enter`,u,n,i),d.target=h,d.relatedTarget=f,g=d),f=g,l&&u)b:{for(d=Dd,p=l,m=u,h=0,g=p;g;g=d(g))h++;g=0;for(var _=m;_;_=d(_))g++;for(;0<h-g;)p=d(p),h--;for(;0<g-h;)m=d(m),g--;for(;h--;){if(p===m||m!==null&&p===m.alternate){d=p;break b}p=d(p),m=d(m)}d=null}else d=null;l!==null&&Od(s,c,l,d,!1),u!==null&&f!==null&&Od(s,f,u,d,!0)}}a:{if(c=r?Et(r):window,l=c.nodeName&&c.nodeName.toLowerCase(),l===`select`||l===`input`&&c.type===`file`)var v=mr;else if(cr(c))if(hr)v=wr;else{v=Sr;var y=xr}else l=c.nodeName,!l||l.toLowerCase()!==`input`||c.type!==`checkbox`&&c.type!==`radio`?r&&nn(r.elementType)&&(v=mr):v=Cr;if(v&&=v(e,r)){lr(s,v,n,i);break a}y&&y(e,c,r),e===`focusout`&&r&&c.type===`number`&&r.memoizedProps.value!=null&&Jt(c,`number`,c.value)}switch(y=r?Et(r):window,e){case`focusin`:(cr(y)||y.contentEditable===`true`)&&(Pr=y,Fr=r,Ir=null);break;case`focusout`:Ir=Fr=Pr=null;break;case`mousedown`:Lr=!0;break;case`contextmenu`:case`mouseup`:case`dragend`:Lr=!1,Rr(s,n,i);break;case`selectionchange`:if(Nr)break;case`keydown`:case`keyup`:Rr(s,n,i)}var b;if(Zn)b:{switch(e){case`compositionstart`:var x=`onCompositionStart`;break b;case`compositionend`:x=`onCompositionEnd`;break b;case`compositionupdate`:x=`onCompositionUpdate`;break b}x=void 0}else ir?nr(e,n)&&(x=`onCompositionEnd`):e===`keydown`&&n.keyCode===229&&(x=`onCompositionStart`);x&&($n&&n.locale!==`ko`&&(ir||x!==`onCompositionStart`?x===`onCompositionEnd`&&ir&&(b=Sn()):(yn=i,bn=`value`in yn?yn.value:yn.textContent,ir=!0)),y=Ed(r,x),0<y.length&&(x=new zn(x,e,null,n,i),s.push({event:x,listeners:y}),b?x.data=b:(b=rr(n),b!==null&&(x.data=b)))),(b=Qn?ar(e,n):or(e,n))&&(x=Ed(r,`onBeforeInput`),0<x.length&&(y=new zn(`onBeforeInput`,`beforeinput`,null,n,i),s.push({event:y,listeners:x}),y.data=b)),md(s,e,r,n,i)}yd(s,t)})}function Td(e,t,n){return{instance:e,listener:t,currentTarget:n}}function Ed(e,t){for(var n=t+`Capture`,r=[];e!==null;){var i=e,a=i.stateNode;if(i=i.tag,i!==5&&i!==26&&i!==27||a===null||(i=hn(e,n),i!=null&&r.unshift(Td(e,i,a)),i=hn(e,t),i!=null&&r.push(Td(e,i,a))),e.tag===3)return r;e=e.return}return[]}function Dd(e){if(e===null)return null;do e=e.return;while(e&&e.tag!==5&&e.tag!==27);return e||null}function Od(e,t,n,r,i){for(var a=t._reactName,o=[];n!==null&&n!==r;){var s=n,c=s.alternate,l=s.stateNode;if(s=s.tag,c!==null&&c===r)break;s!==5&&s!==26&&s!==27||l===null||(c=l,i?(l=hn(n,a),l!=null&&o.unshift(Td(n,l,c))):i||(l=hn(n,a),l!=null&&o.push(Td(n,l,c)))),n=n.return}o.length!==0&&e.push({event:t,listeners:o})}var kd=/\r\n?/g,Ad=/\u0000|\uFFFD/g;function jd(e){return(typeof e==`string`?e:``+e).replace(kd,`
`).replace(Ad,``)}function Md(e,t){return t=jd(t),jd(e)===t}function $(e,t,n,r,a,o){switch(n){case`children`:typeof r==`string`?t===`body`||t===`textarea`&&r===``||Qt(e,r):(typeof r==`number`||typeof r==`bigint`)&&t!==`body`&&Qt(e,``+r);break;case`className`:It(e,`class`,r);break;case`tabIndex`:It(e,`tabindex`,r);break;case`dir`:case`role`:case`viewBox`:case`width`:case`height`:It(e,n,r);break;case`style`:tn(e,r,o);break;case`data`:if(t!==`object`){It(e,`data`,r);break}case`src`:case`href`:if(r===``&&(t!==`a`||n!==`href`)){e.removeAttribute(n);break}if(r==null||typeof r==`function`||typeof r==`symbol`||typeof r==`boolean`){e.removeAttribute(n);break}r=on(``+r),e.setAttribute(n,r);break;case`action`:case`formAction`:if(typeof r==`function`){e.setAttribute(n,`javascript:throw new Error('A React form was unexpectedly submitted. If you called form.submit() manually, consider using form.requestSubmit() instead. If you\\'re trying to use event.stopPropagation() in a submit event handler, consider also calling event.preventDefault().')`);break}else typeof o==`function`&&(n===`formAction`?(t!==`input`&&$(e,t,`name`,a.name,a,null),$(e,t,`formEncType`,a.formEncType,a,null),$(e,t,`formMethod`,a.formMethod,a,null),$(e,t,`formTarget`,a.formTarget,a,null)):($(e,t,`encType`,a.encType,a,null),$(e,t,`method`,a.method,a,null),$(e,t,`target`,a.target,a,null)));if(r==null||typeof r==`symbol`||typeof r==`boolean`){e.removeAttribute(n);break}r=on(``+r),e.setAttribute(n,r);break;case`onClick`:r!=null&&(e.onclick=sn);break;case`onScroll`:r!=null&&Q(`scroll`,e);break;case`onScrollEnd`:r!=null&&Q(`scrollend`,e);break;case`dangerouslySetInnerHTML`:if(r!=null){if(typeof r!=`object`||!(`__html`in r))throw Error(i(61));if(n=r.__html,n!=null){if(a.children!=null)throw Error(i(60));e.innerHTML=n}}break;case`multiple`:e.multiple=r&&typeof r!=`function`&&typeof r!=`symbol`;break;case`muted`:e.muted=r&&typeof r!=`function`&&typeof r!=`symbol`;break;case`suppressContentEditableWarning`:case`suppressHydrationWarning`:case`defaultValue`:case`defaultChecked`:case`innerHTML`:case`ref`:break;case`autoFocus`:break;case`xlinkHref`:if(r==null||typeof r==`function`||typeof r==`boolean`||typeof r==`symbol`){e.removeAttribute(`xlink:href`);break}n=on(``+r),e.setAttributeNS(`http://www.w3.org/1999/xlink`,`xlink:href`,n);break;case`contentEditable`:case`spellCheck`:case`draggable`:case`value`:case`autoReverse`:case`externalResourcesRequired`:case`focusable`:case`preserveAlpha`:r!=null&&typeof r!=`function`&&typeof r!=`symbol`?e.setAttribute(n,``+r):e.removeAttribute(n);break;case`inert`:case`allowFullScreen`:case`async`:case`autoPlay`:case`controls`:case`default`:case`defer`:case`disabled`:case`disablePictureInPicture`:case`disableRemotePlayback`:case`formNoValidate`:case`hidden`:case`loop`:case`noModule`:case`noValidate`:case`open`:case`playsInline`:case`readOnly`:case`required`:case`reversed`:case`scoped`:case`seamless`:case`itemScope`:r&&typeof r!=`function`&&typeof r!=`symbol`?e.setAttribute(n,``):e.removeAttribute(n);break;case`capture`:case`download`:!0===r?e.setAttribute(n,``):!1!==r&&r!=null&&typeof r!=`function`&&typeof r!=`symbol`?e.setAttribute(n,r):e.removeAttribute(n);break;case`cols`:case`rows`:case`size`:case`span`:r!=null&&typeof r!=`function`&&typeof r!=`symbol`&&!isNaN(r)&&1<=r?e.setAttribute(n,r):e.removeAttribute(n);break;case`rowSpan`:case`start`:r==null||typeof r==`function`||typeof r==`symbol`||isNaN(r)?e.removeAttribute(n):e.setAttribute(n,r);break;case`popover`:Q(`beforetoggle`,e),Q(`toggle`,e),j(e,`popover`,r);break;case`xlinkActuate`:Lt(e,`http://www.w3.org/1999/xlink`,`xlink:actuate`,r);break;case`xlinkArcrole`:Lt(e,`http://www.w3.org/1999/xlink`,`xlink:arcrole`,r);break;case`xlinkRole`:Lt(e,`http://www.w3.org/1999/xlink`,`xlink:role`,r);break;case`xlinkShow`:Lt(e,`http://www.w3.org/1999/xlink`,`xlink:show`,r);break;case`xlinkTitle`:Lt(e,`http://www.w3.org/1999/xlink`,`xlink:title`,r);break;case`xlinkType`:Lt(e,`http://www.w3.org/1999/xlink`,`xlink:type`,r);break;case`xmlBase`:Lt(e,`http://www.w3.org/XML/1998/namespace`,`xml:base`,r);break;case`xmlLang`:Lt(e,`http://www.w3.org/XML/1998/namespace`,`xml:lang`,r);break;case`xmlSpace`:Lt(e,`http://www.w3.org/XML/1998/namespace`,`xml:space`,r);break;case`is`:j(e,`is`,r);break;case`innerText`:case`textContent`:break;default:(!(2<n.length)||n[0]!==`o`&&n[0]!==`O`||n[1]!==`n`&&n[1]!==`N`)&&(n=rn.get(n)||n,j(e,n,r))}}function Nd(e,t,n,r,a,o){switch(n){case`style`:tn(e,r,o);break;case`dangerouslySetInnerHTML`:if(r!=null){if(typeof r!=`object`||!(`__html`in r))throw Error(i(61));if(n=r.__html,n!=null){if(a.children!=null)throw Error(i(60));e.innerHTML=n}}break;case`children`:typeof r==`string`?Qt(e,r):(typeof r==`number`||typeof r==`bigint`)&&Qt(e,``+r);break;case`onScroll`:r!=null&&Q(`scroll`,e);break;case`onScrollEnd`:r!=null&&Q(`scrollend`,e);break;case`onClick`:r!=null&&(e.onclick=sn);break;case`suppressContentEditableWarning`:case`suppressHydrationWarning`:case`innerHTML`:case`ref`:break;case`innerText`:case`textContent`:break;default:if(!At.hasOwnProperty(n))a:{if(n[0]===`o`&&n[1]===`n`&&(a=n.endsWith(`Capture`),t=n.slice(2,a?n.length-7:void 0),o=e[gt]||null,o=o==null?null:o[n],typeof o==`function`&&e.removeEventListener(t,o,a),typeof r==`function`)){typeof o!=`function`&&o!==null&&(n in e?e[n]=null:e.hasAttribute(n)&&e.removeAttribute(n)),e.addEventListener(t,r,a);break a}n in e?e[n]=r:!0===r?e.setAttribute(n,``):j(e,n,r)}}}function Pd(e,t,n){switch(t){case`div`:case`span`:case`svg`:case`path`:case`a`:case`g`:case`p`:case`li`:break;case`img`:Q(`error`,e),Q(`load`,e);var r=!1,a=!1,o;for(o in n)if(n.hasOwnProperty(o)){var s=n[o];if(s!=null)switch(o){case`src`:r=!0;break;case`srcSet`:a=!0;break;case`children`:case`dangerouslySetInnerHTML`:throw Error(i(137,t));default:$(e,t,o,s,n,null)}}a&&$(e,t,`srcSet`,n.srcSet,n,null),r&&$(e,t,`src`,n.src,n,null);return;case`input`:Q(`invalid`,e);var c=o=s=a=null,l=null,u=null;for(r in n)if(n.hasOwnProperty(r)){var d=n[r];if(d!=null)switch(r){case`name`:a=d;break;case`type`:s=d;break;case`checked`:l=d;break;case`defaultChecked`:u=d;break;case`value`:o=d;break;case`defaultValue`:c=d;break;case`children`:case`dangerouslySetInnerHTML`:if(d!=null)throw Error(i(137,t));break;default:$(e,t,r,d,n,null)}}qt(e,o,c,l,u,s,a,!1);return;case`select`:for(a in Q(`invalid`,e),r=s=o=null,n)if(n.hasOwnProperty(a)&&(c=n[a],c!=null))switch(a){case`value`:o=c;break;case`defaultValue`:s=c;break;case`multiple`:r=c;default:$(e,t,a,c,n,null)}t=o,n=s,e.multiple=!!r,t==null?n!=null&&Yt(e,!!r,n,!0):Yt(e,!!r,t,!1);return;case`textarea`:for(s in Q(`invalid`,e),o=a=r=null,n)if(n.hasOwnProperty(s)&&(c=n[s],c!=null))switch(s){case`value`:r=c;break;case`defaultValue`:a=c;break;case`children`:o=c;break;case`dangerouslySetInnerHTML`:if(c!=null)throw Error(i(91));break;default:$(e,t,s,c,n,null)}Zt(e,r,a,o);return;case`option`:for(l in n)if(n.hasOwnProperty(l)&&(r=n[l],r!=null))switch(l){case`selected`:e.selected=r&&typeof r!=`function`&&typeof r!=`symbol`;break;default:$(e,t,l,r,n,null)}return;case`dialog`:Q(`beforetoggle`,e),Q(`toggle`,e),Q(`cancel`,e),Q(`close`,e);break;case`iframe`:case`object`:Q(`load`,e);break;case`video`:case`audio`:for(r=0;r<_d.length;r++)Q(_d[r],e);break;case`image`:Q(`error`,e),Q(`load`,e);break;case`details`:Q(`toggle`,e);break;case`embed`:case`source`:case`link`:Q(`error`,e),Q(`load`,e);case`area`:case`base`:case`br`:case`col`:case`hr`:case`keygen`:case`meta`:case`param`:case`track`:case`wbr`:case`menuitem`:for(u in n)if(n.hasOwnProperty(u)&&(r=n[u],r!=null))switch(u){case`children`:case`dangerouslySetInnerHTML`:throw Error(i(137,t));default:$(e,t,u,r,n,null)}return;default:if(nn(t)){for(d in n)n.hasOwnProperty(d)&&(r=n[d],r!==void 0&&Nd(e,t,d,r,n,void 0));return}}for(c in n)n.hasOwnProperty(c)&&(r=n[c],r!=null&&$(e,t,c,r,n,null))}function Fd(e,t,n,r){switch(t){case`div`:case`span`:case`svg`:case`path`:case`a`:case`g`:case`p`:case`li`:break;case`input`:var a=null,o=null,s=null,c=null,l=null,u=null,d=null;for(m in n){var f=n[m];if(n.hasOwnProperty(m)&&f!=null)switch(m){case`checked`:break;case`value`:break;case`defaultValue`:l=f;default:r.hasOwnProperty(m)||$(e,t,m,null,r,f)}}for(var p in r){var m=r[p];if(f=n[p],r.hasOwnProperty(p)&&(m!=null||f!=null))switch(p){case`type`:o=m;break;case`name`:a=m;break;case`checked`:u=m;break;case`defaultChecked`:d=m;break;case`value`:s=m;break;case`defaultValue`:c=m;break;case`children`:case`dangerouslySetInnerHTML`:if(m!=null)throw Error(i(137,t));break;default:m!==f&&$(e,t,p,m,r,f)}}Kt(e,s,c,l,u,d,o,a);return;case`select`:for(o in m=s=c=p=null,n)if(l=n[o],n.hasOwnProperty(o)&&l!=null)switch(o){case`value`:break;case`multiple`:m=l;default:r.hasOwnProperty(o)||$(e,t,o,null,r,l)}for(a in r)if(o=r[a],l=n[a],r.hasOwnProperty(a)&&(o!=null||l!=null))switch(a){case`value`:p=o;break;case`defaultValue`:c=o;break;case`multiple`:s=o;default:o!==l&&$(e,t,a,o,r,l)}t=c,n=s,r=m,p==null?!!r!=!!n&&(t==null?Yt(e,!!n,n?[]:``,!1):Yt(e,!!n,t,!0)):Yt(e,!!n,p,!1);return;case`textarea`:for(c in m=p=null,n)if(a=n[c],n.hasOwnProperty(c)&&a!=null&&!r.hasOwnProperty(c))switch(c){case`value`:break;case`children`:break;default:$(e,t,c,null,r,a)}for(s in r)if(a=r[s],o=n[s],r.hasOwnProperty(s)&&(a!=null||o!=null))switch(s){case`value`:p=a;break;case`defaultValue`:m=a;break;case`children`:break;case`dangerouslySetInnerHTML`:if(a!=null)throw Error(i(91));break;default:a!==o&&$(e,t,s,a,r,o)}Xt(e,p,m);return;case`option`:for(var h in n)if(p=n[h],n.hasOwnProperty(h)&&p!=null&&!r.hasOwnProperty(h))switch(h){case`selected`:e.selected=!1;break;default:$(e,t,h,null,r,p)}for(l in r)if(p=r[l],m=n[l],r.hasOwnProperty(l)&&p!==m&&(p!=null||m!=null))switch(l){case`selected`:e.selected=p&&typeof p!=`function`&&typeof p!=`symbol`;break;default:$(e,t,l,p,r,m)}return;case`img`:case`link`:case`area`:case`base`:case`br`:case`col`:case`embed`:case`hr`:case`keygen`:case`meta`:case`param`:case`source`:case`track`:case`wbr`:case`menuitem`:for(var g in n)p=n[g],n.hasOwnProperty(g)&&p!=null&&!r.hasOwnProperty(g)&&$(e,t,g,null,r,p);for(u in r)if(p=r[u],m=n[u],r.hasOwnProperty(u)&&p!==m&&(p!=null||m!=null))switch(u){case`children`:case`dangerouslySetInnerHTML`:if(p!=null)throw Error(i(137,t));break;default:$(e,t,u,p,r,m)}return;default:if(nn(t)){for(var _ in n)p=n[_],n.hasOwnProperty(_)&&p!==void 0&&!r.hasOwnProperty(_)&&Nd(e,t,_,void 0,r,p);for(d in r)p=r[d],m=n[d],!r.hasOwnProperty(d)||p===m||p===void 0&&m===void 0||Nd(e,t,d,p,r,m);return}}for(var v in n)p=n[v],n.hasOwnProperty(v)&&p!=null&&!r.hasOwnProperty(v)&&$(e,t,v,null,r,p);for(f in r)p=r[f],m=n[f],!r.hasOwnProperty(f)||p===m||p==null&&m==null||$(e,t,f,p,r,m)}function Id(e){switch(e){case`css`:case`script`:case`font`:case`img`:case`image`:case`input`:case`link`:return!0;default:return!1}}function Ld(){if(typeof performance.getEntriesByType==`function`){for(var e=0,t=0,n=performance.getEntriesByType(`resource`),r=0;r<n.length;r++){var i=n[r],a=i.transferSize,o=i.initiatorType,s=i.duration;if(a&&s&&Id(o)){for(o=0,s=i.responseEnd,r+=1;r<n.length;r++){var c=n[r],l=c.startTime;if(l>s)break;var u=c.transferSize,d=c.initiatorType;u&&Id(d)&&(c=c.responseEnd,o+=u*(c<s?1:(s-l)/(c-l)))}if(--r,t+=8*(a+o)/(i.duration/1e3),e++,10<e)break}}if(0<e)return t/e/1e6}return navigator.connection&&(e=navigator.connection.downlink,typeof e==`number`)?e:5}var Rd=null,zd=null;function Bd(e){return e.nodeType===9?e:e.ownerDocument}function Vd(e){switch(e){case`http://www.w3.org/2000/svg`:return 1;case`http://www.w3.org/1998/Math/MathML`:return 2;default:return 0}}function Hd(e,t){if(e===0)switch(t){case`svg`:return 1;case`math`:return 2;default:return 0}return e===1&&t===`foreignObject`?0:e}function Ud(e,t){return e===`textarea`||e===`noscript`||typeof t.children==`string`||typeof t.children==`number`||typeof t.children==`bigint`||typeof t.dangerouslySetInnerHTML==`object`&&t.dangerouslySetInnerHTML!==null&&t.dangerouslySetInnerHTML.__html!=null}var Wd=null;function Gd(){var e=window.event;return e&&e.type===`popstate`?e===Wd?!1:(Wd=e,!0):(Wd=null,!1)}var Kd=typeof setTimeout==`function`?setTimeout:void 0,qd=typeof clearTimeout==`function`?clearTimeout:void 0,Jd=typeof Promise==`function`?Promise:void 0,Yd=typeof queueMicrotask==`function`?queueMicrotask:Jd===void 0?Kd:function(e){return Jd.resolve(null).then(e).catch(Xd)};function Xd(e){setTimeout(function(){throw e})}function Zd(e){return e===`head`}function Qd(e,t){var n=t,r=0;do{var i=n.nextSibling;if(e.removeChild(n),i&&i.nodeType===8)if(n=i.data,n===`/$`||n===`/&`){if(r===0){e.removeChild(i),Np(t);return}r--}else if(n===`$`||n===`$?`||n===`$~`||n===`$!`||n===`&`)r++;else if(n===`html`)pf(e.ownerDocument.documentElement);else if(n===`head`){n=e.ownerDocument.head,pf(n);for(var a=n.firstChild;a;){var o=a.nextSibling,s=a.nodeName;a[St]||s===`SCRIPT`||s===`STYLE`||s===`LINK`&&a.rel.toLowerCase()===`stylesheet`||n.removeChild(a),a=o}}else n===`body`&&pf(e.ownerDocument.body);n=i}while(n);Np(t)}function $d(e,t){var n=e;e=0;do{var r=n.nextSibling;if(n.nodeType===1?t?(n._stashedDisplay=n.style.display,n.style.display=`none`):(n.style.display=n._stashedDisplay||``,n.getAttribute(`style`)===``&&n.removeAttribute(`style`)):n.nodeType===3&&(t?(n._stashedText=n.nodeValue,n.nodeValue=``):n.nodeValue=n._stashedText||``),r&&r.nodeType===8)if(n=r.data,n===`/$`){if(e===0)break;e--}else n!==`$`&&n!==`$?`&&n!==`$~`&&n!==`$!`||e++;n=r}while(n)}function ef(e){var t=e.firstChild;for(t&&t.nodeType===10&&(t=t.nextSibling);t;){var n=t;switch(t=t.nextSibling,n.nodeName){case`HTML`:case`HEAD`:case`BODY`:ef(n),Ct(n);continue;case`SCRIPT`:case`STYLE`:continue;case`LINK`:if(n.rel.toLowerCase()===`stylesheet`)continue}e.removeChild(n)}}function tf(e,t,n,r){for(;e.nodeType===1;){var i=n;if(e.nodeName.toLowerCase()!==t.toLowerCase()){if(!r&&(e.nodeName!==`INPUT`||e.type!==`hidden`))break}else if(!r)if(t===`input`&&e.type===`hidden`){var a=i.name==null?null:``+i.name;if(i.type===`hidden`&&e.getAttribute(`name`)===a)return e}else return e;else if(!e[St])switch(t){case`meta`:if(!e.hasAttribute(`itemprop`))break;return e;case`link`:if(a=e.getAttribute(`rel`),a===`stylesheet`&&e.hasAttribute(`data-precedence`)||a!==i.rel||e.getAttribute(`href`)!==(i.href==null||i.href===``?null:i.href)||e.getAttribute(`crossorigin`)!==(i.crossOrigin==null?null:i.crossOrigin)||e.getAttribute(`title`)!==(i.title==null?null:i.title))break;return e;case`style`:if(e.hasAttribute(`data-precedence`))break;return e;case`script`:if(a=e.getAttribute(`src`),(a!==(i.src==null?null:i.src)||e.getAttribute(`type`)!==(i.type==null?null:i.type)||e.getAttribute(`crossorigin`)!==(i.crossOrigin==null?null:i.crossOrigin))&&a&&e.hasAttribute(`async`)&&!e.hasAttribute(`itemprop`))break;return e;default:return e}if(e=cf(e.nextSibling),e===null)break}return null}function nf(e,t,n){if(t===``)return null;for(;e.nodeType!==3;)if((e.nodeType!==1||e.nodeName!==`INPUT`||e.type!==`hidden`)&&!n||(e=cf(e.nextSibling),e===null))return null;return e}function rf(e,t){for(;e.nodeType!==8;)if((e.nodeType!==1||e.nodeName!==`INPUT`||e.type!==`hidden`)&&!t||(e=cf(e.nextSibling),e===null))return null;return e}function af(e){return e.data===`$?`||e.data===`$~`}function of(e){return e.data===`$!`||e.data===`$?`&&e.ownerDocument.readyState!==`loading`}function sf(e,t){var n=e.ownerDocument;if(e.data===`$~`)e._reactRetry=t;else if(e.data!==`$?`||n.readyState!==`loading`)t();else{var r=function(){t(),n.removeEventListener(`DOMContentLoaded`,r)};n.addEventListener(`DOMContentLoaded`,r),e._reactRetry=r}}function cf(e){for(;e!=null;e=e.nextSibling){var t=e.nodeType;if(t===1||t===3)break;if(t===8){if(t=e.data,t===`$`||t===`$!`||t===`$?`||t===`$~`||t===`&`||t===`F!`||t===`F`)break;if(t===`/$`||t===`/&`)return null}}return e}var lf=null;function uf(e){e=e.nextSibling;for(var t=0;e;){if(e.nodeType===8){var n=e.data;if(n===`/$`||n===`/&`){if(t===0)return cf(e.nextSibling);t--}else n!==`$`&&n!==`$!`&&n!==`$?`&&n!==`$~`&&n!==`&`||t++}e=e.nextSibling}return null}function df(e){e=e.previousSibling;for(var t=0;e;){if(e.nodeType===8){var n=e.data;if(n===`$`||n===`$!`||n===`$?`||n===`$~`||n===`&`){if(t===0)return e;t--}else n!==`/$`&&n!==`/&`||t++}e=e.previousSibling}return null}function ff(e,t,n){switch(t=Bd(n),e){case`html`:if(e=t.documentElement,!e)throw Error(i(452));return e;case`head`:if(e=t.head,!e)throw Error(i(453));return e;case`body`:if(e=t.body,!e)throw Error(i(454));return e;default:throw Error(i(451))}}function pf(e){for(var t=e.attributes;t.length;)e.removeAttributeNode(t[0]);Ct(e)}var mf=new Map,hf=new Set;function gf(e){return typeof e.getRootNode==`function`?e.getRootNode():e.nodeType===9?e:e.ownerDocument}var _f=D.d;D.d={f:vf,r:yf,D:Sf,C:Cf,L:wf,m:Tf,X:Df,S:Ef,M:Of};function vf(){var e=_f.f(),t=bu();return e||t}function yf(e){var t=Tt(e);t!==null&&t.tag===5&&t.type===`form`?Es(t):_f.r(e)}var bf=typeof document>`u`?null:document;function xf(e,t,n){var r=bf;if(r&&typeof t==`string`&&t){var i=Gt(t);i=`link[rel="`+e+`"][href="`+i+`"]`,typeof n==`string`&&(i+=`[crossorigin="`+n+`"]`),hf.has(i)||(hf.add(i),e={rel:e,crossOrigin:n,href:t},r.querySelector(i)===null&&(t=r.createElement(`link`),Pd(t,`link`,e),Ot(t),r.head.appendChild(t)))}}function Sf(e){_f.D(e),xf(`dns-prefetch`,e,null)}function Cf(e,t){_f.C(e,t),xf(`preconnect`,e,t)}function wf(e,t,n){_f.L(e,t,n);var r=bf;if(r&&e&&t){var i=`link[rel="preload"][as="`+Gt(t)+`"]`;t===`image`&&n&&n.imageSrcSet?(i+=`[imagesrcset="`+Gt(n.imageSrcSet)+`"]`,typeof n.imageSizes==`string`&&(i+=`[imagesizes="`+Gt(n.imageSizes)+`"]`)):i+=`[href="`+Gt(e)+`"]`;var a=i;switch(t){case`style`:a=Af(e);break;case`script`:a=Pf(e)}mf.has(a)||(e=p({rel:`preload`,href:t===`image`&&n&&n.imageSrcSet?void 0:e,as:t},n),mf.set(a,e),r.querySelector(i)!==null||t===`style`&&r.querySelector(jf(a))||t===`script`&&r.querySelector(Ff(a))||(t=r.createElement(`link`),Pd(t,`link`,e),Ot(t),r.head.appendChild(t)))}}function Tf(e,t){_f.m(e,t);var n=bf;if(n&&e){var r=t&&typeof t.as==`string`?t.as:`script`,i=`link[rel="modulepreload"][as="`+Gt(r)+`"][href="`+Gt(e)+`"]`,a=i;switch(r){case`audioworklet`:case`paintworklet`:case`serviceworker`:case`sharedworker`:case`worker`:case`script`:a=Pf(e)}if(!mf.has(a)&&(e=p({rel:`modulepreload`,href:e},t),mf.set(a,e),n.querySelector(i)===null)){switch(r){case`audioworklet`:case`paintworklet`:case`serviceworker`:case`sharedworker`:case`worker`:case`script`:if(n.querySelector(Ff(a)))return}r=n.createElement(`link`),Pd(r,`link`,e),Ot(r),n.head.appendChild(r)}}}function Ef(e,t,n){_f.S(e,t,n);var r=bf;if(r&&e){var i=Dt(r).hoistableStyles,a=Af(e);t||=`default`;var o=i.get(a);if(!o){var s={loading:0,preload:null};if(o=r.querySelector(jf(a)))s.loading=5;else{e=p({rel:`stylesheet`,href:e,"data-precedence":t},n),(n=mf.get(a))&&Rf(e,n);var c=o=r.createElement(`link`);Ot(c),Pd(c,`link`,e),c._p=new Promise(function(e,t){c.onload=e,c.onerror=t}),c.addEventListener(`load`,function(){s.loading|=1}),c.addEventListener(`error`,function(){s.loading|=2}),s.loading|=4,Lf(o,t,r)}o={type:`stylesheet`,instance:o,count:1,state:s},i.set(a,o)}}}function Df(e,t){_f.X(e,t);var n=bf;if(n&&e){var r=Dt(n).hoistableScripts,i=Pf(e),a=r.get(i);a||(a=n.querySelector(Ff(i)),a||(e=p({src:e,async:!0},t),(t=mf.get(i))&&zf(e,t),a=n.createElement(`script`),Ot(a),Pd(a,`link`,e),n.head.appendChild(a)),a={type:`script`,instance:a,count:1,state:null},r.set(i,a))}}function Of(e,t){_f.M(e,t);var n=bf;if(n&&e){var r=Dt(n).hoistableScripts,i=Pf(e),a=r.get(i);a||(a=n.querySelector(Ff(i)),a||(e=p({src:e,async:!0,type:`module`},t),(t=mf.get(i))&&zf(e,t),a=n.createElement(`script`),Ot(a),Pd(a,`link`,e),n.head.appendChild(a)),a={type:`script`,instance:a,count:1,state:null},r.set(i,a))}}function kf(e,t,n,r){var a=(a=me.current)?gf(a):null;if(!a)throw Error(i(446));switch(e){case`meta`:case`title`:return null;case`style`:return typeof n.precedence==`string`&&typeof n.href==`string`?(t=Af(n.href),n=Dt(a).hoistableStyles,r=n.get(t),r||(r={type:`style`,instance:null,count:0,state:null},n.set(t,r)),r):{type:`void`,instance:null,count:0,state:null};case`link`:if(n.rel===`stylesheet`&&typeof n.href==`string`&&typeof n.precedence==`string`){e=Af(n.href);var o=Dt(a).hoistableStyles,s=o.get(e);if(s||(a=a.ownerDocument||a,s={type:`stylesheet`,instance:null,count:0,state:{loading:0,preload:null}},o.set(e,s),(o=a.querySelector(jf(e)))&&!o._p&&(s.instance=o,s.state.loading=5),mf.has(e)||(n={rel:`preload`,as:`style`,href:n.href,crossOrigin:n.crossOrigin,integrity:n.integrity,media:n.media,hrefLang:n.hrefLang,referrerPolicy:n.referrerPolicy},mf.set(e,n),o||Nf(a,e,n,s.state))),t&&r===null)throw Error(i(528,``));return s}if(t&&r!==null)throw Error(i(529,``));return null;case`script`:return t=n.async,n=n.src,typeof n==`string`&&t&&typeof t!=`function`&&typeof t!=`symbol`?(t=Pf(n),n=Dt(a).hoistableScripts,r=n.get(t),r||(r={type:`script`,instance:null,count:0,state:null},n.set(t,r)),r):{type:`void`,instance:null,count:0,state:null};default:throw Error(i(444,e))}}function Af(e){return`href="`+Gt(e)+`"`}function jf(e){return`link[rel="stylesheet"][`+e+`]`}function Mf(e){return p({},e,{"data-precedence":e.precedence,precedence:null})}function Nf(e,t,n,r){e.querySelector(`link[rel="preload"][as="style"][`+t+`]`)?r.loading=1:(t=e.createElement(`link`),r.preload=t,t.addEventListener(`load`,function(){return r.loading|=1}),t.addEventListener(`error`,function(){return r.loading|=2}),Pd(t,`link`,n),Ot(t),e.head.appendChild(t))}function Pf(e){return`[src="`+Gt(e)+`"]`}function Ff(e){return`script[async]`+e}function If(e,t,n){if(t.count++,t.instance===null)switch(t.type){case`style`:var r=e.querySelector(`style[data-href~="`+Gt(n.href)+`"]`);if(r)return t.instance=r,Ot(r),r;var a=p({},n,{"data-href":n.href,"data-precedence":n.precedence,href:null,precedence:null});return r=(e.ownerDocument||e).createElement(`style`),Ot(r),Pd(r,`style`,a),Lf(r,n.precedence,e),t.instance=r;case`stylesheet`:a=Af(n.href);var o=e.querySelector(jf(a));if(o)return t.state.loading|=4,t.instance=o,Ot(o),o;r=Mf(n),(a=mf.get(a))&&Rf(r,a),o=(e.ownerDocument||e).createElement(`link`),Ot(o);var s=o;return s._p=new Promise(function(e,t){s.onload=e,s.onerror=t}),Pd(o,`link`,r),t.state.loading|=4,Lf(o,n.precedence,e),t.instance=o;case`script`:return o=Pf(n.src),(a=e.querySelector(Ff(o)))?(t.instance=a,Ot(a),a):(r=n,(a=mf.get(o))&&(r=p({},n),zf(r,a)),e=e.ownerDocument||e,a=e.createElement(`script`),Ot(a),Pd(a,`link`,r),e.head.appendChild(a),t.instance=a);case`void`:return null;default:throw Error(i(443,t.type))}else t.type===`stylesheet`&&!(t.state.loading&4)&&(r=t.instance,t.state.loading|=4,Lf(r,n.precedence,e));return t.instance}function Lf(e,t,n){for(var r=n.querySelectorAll(`link[rel="stylesheet"][data-precedence],style[data-precedence]`),i=r.length?r[r.length-1]:null,a=i,o=0;o<r.length;o++){var s=r[o];if(s.dataset.precedence===t)a=s;else if(a!==i)break}a?a.parentNode.insertBefore(e,a.nextSibling):(t=n.nodeType===9?n.head:n,t.insertBefore(e,t.firstChild))}function Rf(e,t){e.crossOrigin??=t.crossOrigin,e.referrerPolicy??=t.referrerPolicy,e.title??=t.title}function zf(e,t){e.crossOrigin??=t.crossOrigin,e.referrerPolicy??=t.referrerPolicy,e.integrity??=t.integrity}var Bf=null;function Vf(e,t,n){if(Bf===null){var r=new Map,i=Bf=new Map;i.set(n,r)}else i=Bf,r=i.get(n),r||(r=new Map,i.set(n,r));if(r.has(e))return r;for(r.set(e,null),n=n.getElementsByTagName(e),i=0;i<n.length;i++){var a=n[i];if(!(a[St]||a[ht]||e===`link`&&a.getAttribute(`rel`)===`stylesheet`)&&a.namespaceURI!==`http://www.w3.org/2000/svg`){var o=a.getAttribute(t)||``;o=e+o;var s=r.get(o);s?s.push(a):r.set(o,[a])}}return r}function Hf(e,t,n){e=e.ownerDocument||e,e.head.insertBefore(n,t===`title`?e.querySelector(`head > title`):null)}function Uf(e,t,n){if(n===1||t.itemProp!=null)return!1;switch(e){case`meta`:case`title`:return!0;case`style`:if(typeof t.precedence!=`string`||typeof t.href!=`string`||t.href===``)break;return!0;case`link`:if(typeof t.rel!=`string`||typeof t.href!=`string`||t.href===``||t.onLoad||t.onError)break;switch(t.rel){case`stylesheet`:return e=t.disabled,typeof t.precedence==`string`&&e==null;default:return!0}case`script`:if(t.async&&typeof t.async!=`function`&&typeof t.async!=`symbol`&&!t.onLoad&&!t.onError&&t.src&&typeof t.src==`string`)return!0}return!1}function Wf(e){return!(e.type===`stylesheet`&&!(e.state.loading&3))}function Gf(e,t,n,r){if(n.type===`stylesheet`&&(typeof r.media!=`string`||!1!==matchMedia(r.media).matches)&&!(n.state.loading&4)){if(n.instance===null){var i=Af(r.href),a=t.querySelector(jf(i));if(a){t=a._p,typeof t==`object`&&t&&typeof t.then==`function`&&(e.count++,e=Jf.bind(e),t.then(e,e)),n.state.loading|=4,n.instance=a,Ot(a);return}a=t.ownerDocument||t,r=Mf(r),(i=mf.get(i))&&Rf(r,i),a=a.createElement(`link`),Ot(a);var o=a;o._p=new Promise(function(e,t){o.onload=e,o.onerror=t}),Pd(a,`link`,r),n.instance=a}e.stylesheets===null&&(e.stylesheets=new Map),e.stylesheets.set(n,t),(t=n.state.preload)&&!(n.state.loading&3)&&(e.count++,n=Jf.bind(e),t.addEventListener(`load`,n),t.addEventListener(`error`,n))}}var Kf=0;function qf(e,t){return e.stylesheets&&e.count===0&&Xf(e,e.stylesheets),0<e.count||0<e.imgCount?function(n){var r=setTimeout(function(){if(e.stylesheets&&Xf(e,e.stylesheets),e.unsuspend){var t=e.unsuspend;e.unsuspend=null,t()}},6e4+t);0<e.imgBytes&&Kf===0&&(Kf=62500*Ld());var i=setTimeout(function(){if(e.waitingForImages=!1,e.count===0&&(e.stylesheets&&Xf(e,e.stylesheets),e.unsuspend)){var t=e.unsuspend;e.unsuspend=null,t()}},(e.imgBytes>Kf?50:800)+t);return e.unsuspend=n,function(){e.unsuspend=null,clearTimeout(r),clearTimeout(i)}}:null}function Jf(){if(this.count--,this.count===0&&(this.imgCount===0||!this.waitingForImages)){if(this.stylesheets)Xf(this,this.stylesheets);else if(this.unsuspend){var e=this.unsuspend;this.unsuspend=null,e()}}}var Yf=null;function Xf(e,t){e.stylesheets=null,e.unsuspend!==null&&(e.count++,Yf=new Map,t.forEach(Zf,e),Yf=null,Jf.call(e))}function Zf(e,t){if(!(t.state.loading&4)){var n=Yf.get(e);if(n)var r=n.get(null);else{n=new Map,Yf.set(e,n);for(var i=e.querySelectorAll(`link[data-precedence],style[data-precedence]`),a=0;a<i.length;a++){var o=i[a];(o.nodeName===`LINK`||o.getAttribute(`media`)!==`not all`)&&(n.set(o.dataset.precedence,o),r=o)}r&&n.set(null,r)}i=t.instance,o=i.getAttribute(`data-precedence`),a=n.get(o)||r,a===r&&n.set(null,i),n.set(o,i),this.count++,r=Jf.bind(this),i.addEventListener(`load`,r),i.addEventListener(`error`,r),a?a.parentNode.insertBefore(i,a.nextSibling):(e=e.nodeType===9?e.head:e,e.insertBefore(i,e.firstChild)),t.state.loading|=4}}var Qf={$$typeof:x,Provider:null,Consumer:null,_currentValue:O,_currentValue2:O,_threadCount:0};function $f(e,t,n,r,i,a,o,s,c){this.tag=1,this.containerInfo=e,this.pingCache=this.current=this.pendingChildren=null,this.timeoutHandle=-1,this.callbackNode=this.next=this.pendingContext=this.context=this.cancelPendingCommit=null,this.callbackPriority=0,this.expirationTimes=it(-1),this.entangledLanes=this.shellSuspendCounter=this.errorRecoveryDisabledLanes=this.expiredLanes=this.warmLanes=this.pingedLanes=this.suspendedLanes=this.pendingLanes=0,this.entanglements=it(0),this.hiddenUpdates=it(null),this.identifierPrefix=r,this.onUncaughtError=i,this.onCaughtError=a,this.onRecoverableError=o,this.pooledCache=null,this.pooledCacheLanes=0,this.formState=c,this.incompleteTransitions=new Map}function ep(e,t,n,r,i,a,o,s,c,l,u,d){return e=new $f(e,t,n,o,c,l,u,d,s),t=1,!0===a&&(t|=24),a=di(3,null,null,t),e.current=a,a.stateNode=e,t=la(),t.refCount++,e.pooledCache=t,t.refCount++,a.memoizedState={element:r,isDehydrated:n,cache:t},Ha(a),e}function tp(e){return e?(e=li,e):li}function np(e,t,n,r,i,a){i=tp(i),r.context===null?r.context=i:r.pendingContext=i,r=Wa(t),r.payload={element:n},a=a===void 0?null:a,a!==null&&(r.callback=a),n=Ga(e,r,t),n!==null&&(hu(n,e,t),Ka(n,e,t))}function rp(e,t){if(e=e.memoizedState,e!==null&&e.dehydrated!==null){var n=e.retryLane;e.retryLane=n!==0&&n<t?n:t}}function ip(e,t){rp(e,t),(e=e.alternate)&&rp(e,t)}function ap(e){if(e.tag===13||e.tag===31){var t=oi(e,67108864);t!==null&&hu(t,e,67108864),ip(e,67108864)}}function op(e){if(e.tag===13||e.tag===31){var t=pu();t=ut(t);var n=oi(e,t);n!==null&&hu(n,e,t),ip(e,t)}}var sp=!0;function cp(e,t,n,r){var i=E.T;E.T=null;var a=D.p;try{D.p=2,up(e,t,n,r)}finally{D.p=a,E.T=i}}function lp(e,t,n,r){var i=E.T;E.T=null;var a=D.p;try{D.p=8,up(e,t,n,r)}finally{D.p=a,E.T=i}}function up(e,t,n,r){if(sp){var i=dp(r);if(i===null)wd(e,t,r,fp,n),Cp(e,r);else if(Tp(i,e,t,n,r))r.stopPropagation();else if(Cp(e,r),t&4&&-1<Sp.indexOf(e)){for(;i!==null;){var a=Tt(i);if(a!==null)switch(a.tag){case 3:if(a=a.stateNode,a.current.memoizedState.isDehydrated){var o=Qe(a.pendingLanes);if(o!==0){var s=a;for(s.pendingLanes|=2,s.entangledLanes|=2;o;){var c=1<<31-We(o);s.entanglements[1]|=c,o&=~c}rd(a),!(K&6)&&(tu=Me()+500,id(0,!1))}}break;case 31:case 13:s=oi(a,2),s!==null&&hu(s,a,2),bu(),ip(a,2)}if(a=dp(r),a===null&&wd(e,t,r,fp,n),a===i)break;i=a}i!==null&&r.stopPropagation()}else wd(e,t,r,null,n)}}function dp(e){return e=ln(e),pp(e)}var fp=null;function pp(e){if(fp=null,e=wt(e),e!==null){var t=o(e);if(t===null)e=null;else{var n=t.tag;if(n===13){if(e=s(t),e!==null)return e;e=null}else if(n===31){if(e=c(t),e!==null)return e;e=null}else if(n===3){if(t.stateNode.current.memoizedState.isDehydrated)return t.tag===3?t.stateNode.containerInfo:null;e=null}else t!==e&&(e=null)}}return fp=e,null}function mp(e){switch(e){case`beforetoggle`:case`cancel`:case`click`:case`close`:case`contextmenu`:case`copy`:case`cut`:case`auxclick`:case`dblclick`:case`dragend`:case`dragstart`:case`drop`:case`focusin`:case`focusout`:case`input`:case`invalid`:case`keydown`:case`keypress`:case`keyup`:case`mousedown`:case`mouseup`:case`paste`:case`pause`:case`play`:case`pointercancel`:case`pointerdown`:case`pointerup`:case`ratechange`:case`reset`:case`resize`:case`seeked`:case`submit`:case`toggle`:case`touchcancel`:case`touchend`:case`touchstart`:case`volumechange`:case`change`:case`selectionchange`:case`textInput`:case`compositionstart`:case`compositionend`:case`compositionupdate`:case`beforeblur`:case`afterblur`:case`beforeinput`:case`blur`:case`fullscreenchange`:case`focus`:case`hashchange`:case`popstate`:case`select`:case`selectstart`:return 2;case`drag`:case`dragenter`:case`dragexit`:case`dragleave`:case`dragover`:case`mousemove`:case`mouseout`:case`mouseover`:case`pointermove`:case`pointerout`:case`pointerover`:case`scroll`:case`touchmove`:case`wheel`:case`mouseenter`:case`mouseleave`:case`pointerenter`:case`pointerleave`:return 8;case`message`:switch(Ne()){case Pe:return 2;case Fe:return 8;case Ie:case Le:return 32;case Re:return 268435456;default:return 32}default:return 32}}var hp=!1,gp=null,_p=null,vp=null,yp=new Map,bp=new Map,xp=[],Sp=`mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset`.split(` `);function Cp(e,t){switch(e){case`focusin`:case`focusout`:gp=null;break;case`dragenter`:case`dragleave`:_p=null;break;case`mouseover`:case`mouseout`:vp=null;break;case`pointerover`:case`pointerout`:yp.delete(t.pointerId);break;case`gotpointercapture`:case`lostpointercapture`:bp.delete(t.pointerId)}}function wp(e,t,n,r,i,a){return e===null||e.nativeEvent!==a?(e={blockedOn:t,domEventName:n,eventSystemFlags:r,nativeEvent:a,targetContainers:[i]},t!==null&&(t=Tt(t),t!==null&&ap(t)),e):(e.eventSystemFlags|=r,t=e.targetContainers,i!==null&&t.indexOf(i)===-1&&t.push(i),e)}function Tp(e,t,n,r,i){switch(t){case`focusin`:return gp=wp(gp,e,t,n,r,i),!0;case`dragenter`:return _p=wp(_p,e,t,n,r,i),!0;case`mouseover`:return vp=wp(vp,e,t,n,r,i),!0;case`pointerover`:var a=i.pointerId;return yp.set(a,wp(yp.get(a)||null,e,t,n,r,i)),!0;case`gotpointercapture`:return a=i.pointerId,bp.set(a,wp(bp.get(a)||null,e,t,n,r,i)),!0}return!1}function Ep(e){var t=wt(e.target);if(t!==null){var n=o(t);if(n!==null){if(t=n.tag,t===13){if(t=s(n),t!==null){e.blockedOn=t,pt(e.priority,function(){op(n)});return}}else if(t===31){if(t=c(n),t!==null){e.blockedOn=t,pt(e.priority,function(){op(n)});return}}else if(t===3&&n.stateNode.current.memoizedState.isDehydrated){e.blockedOn=n.tag===3?n.stateNode.containerInfo:null;return}}}e.blockedOn=null}function Dp(e){if(e.blockedOn!==null)return!1;for(var t=e.targetContainers;0<t.length;){var n=dp(e.nativeEvent);if(n===null){n=e.nativeEvent;var r=new n.constructor(n.type,n);cn=r,n.target.dispatchEvent(r),cn=null}else return t=Tt(n),t!==null&&ap(t),e.blockedOn=n,!1;t.shift()}return!0}function Op(e,t,n){Dp(e)&&n.delete(t)}function kp(){hp=!1,gp!==null&&Dp(gp)&&(gp=null),_p!==null&&Dp(_p)&&(_p=null),vp!==null&&Dp(vp)&&(vp=null),yp.forEach(Op),bp.forEach(Op)}function Ap(e,n){e.blockedOn===n&&(e.blockedOn=null,hp||(hp=!0,t.unstable_scheduleCallback(t.unstable_NormalPriority,kp)))}var jp=null;function Mp(e){jp!==e&&(jp=e,t.unstable_scheduleCallback(t.unstable_NormalPriority,function(){jp===e&&(jp=null);for(var t=0;t<e.length;t+=3){var n=e[t],r=e[t+1],i=e[t+2];if(typeof r!=`function`){if(pp(r||n)===null)continue;break}var a=Tt(n);a!==null&&(e.splice(t,3),t-=3,ws(a,{pending:!0,data:i,method:n.method,action:r},r,i))}}))}function Np(e){function t(t){return Ap(t,e)}gp!==null&&Ap(gp,e),_p!==null&&Ap(_p,e),vp!==null&&Ap(vp,e),yp.forEach(t),bp.forEach(t);for(var n=0;n<xp.length;n++){var r=xp[n];r.blockedOn===e&&(r.blockedOn=null)}for(;0<xp.length&&(n=xp[0],n.blockedOn===null);)Ep(n),n.blockedOn===null&&xp.shift();if(n=(e.ownerDocument||e).$$reactFormReplay,n!=null)for(r=0;r<n.length;r+=3){var i=n[r],a=n[r+1],o=i[gt]||null;if(typeof a==`function`)o||Mp(n);else if(o){var s=null;if(a&&a.hasAttribute(`formAction`)){if(i=a,o=a[gt]||null)s=o.formAction;else if(pp(i)!==null)continue}else s=o.action;typeof s==`function`?n[r+1]=s:(n.splice(r,3),r-=3),Mp(n)}}}function Pp(){function e(e){e.canIntercept&&e.info===`react-transition`&&e.intercept({handler:function(){return new Promise(function(e){return i=e})},focusReset:`manual`,scroll:`manual`})}function t(){i!==null&&(i(),i=null),r||setTimeout(n,20)}function n(){if(!r&&!navigation.transition){var e=navigation.currentEntry;e&&e.url!=null&&navigation.navigate(e.url,{state:e.getState(),info:`react-transition`,history:`replace`})}}if(typeof navigation==`object`){var r=!1,i=null;return navigation.addEventListener(`navigate`,e),navigation.addEventListener(`navigatesuccess`,t),navigation.addEventListener(`navigateerror`,t),setTimeout(n,100),function(){r=!0,navigation.removeEventListener(`navigate`,e),navigation.removeEventListener(`navigatesuccess`,t),navigation.removeEventListener(`navigateerror`,t),i!==null&&(i(),i=null)}}}function Fp(e){this._internalRoot=e}Ip.prototype.render=Fp.prototype.render=function(e){var t=this._internalRoot;if(t===null)throw Error(i(409));var n=t.current;np(n,pu(),e,t,null,null)},Ip.prototype.unmount=Fp.prototype.unmount=function(){var e=this._internalRoot;if(e!==null){this._internalRoot=null;var t=e.containerInfo;np(e.current,2,null,e,null,null),bu(),t[_t]=null}};function Ip(e){this._internalRoot=e}Ip.prototype.unstable_scheduleHydration=function(e){if(e){var t=ft();e={blockedOn:null,target:e,priority:t};for(var n=0;n<xp.length&&t!==0&&t<xp[n].priority;n++);xp.splice(n,0,e),n===0&&Ep(e)}};var Lp=n.version;if(Lp!==`19.2.7`)throw Error(i(527,Lp,`19.2.7`));D.findDOMNode=function(e){var t=e._reactInternals;if(t===void 0)throw typeof e.render==`function`?Error(i(188)):(e=Object.keys(e).join(`,`),Error(i(268,e)));return e=u(t),e=e===null?null:d(e),e=e===null?null:e.stateNode,e};var Rp={bundleType:0,version:`19.2.7`,rendererPackageName:`react-dom`,currentDispatcherRef:E,reconcilerVersion:`19.2.7`};if(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__<`u`){var zp=__REACT_DEVTOOLS_GLOBAL_HOOK__;if(!zp.isDisabled&&zp.supportsFiber)try{Ve=zp.inject(Rp),He=zp}catch{}}e.createRoot=function(e,t){if(!a(e))throw Error(i(299));var n=!1,r=``,o=qs,s=Js,c=Ys;return t!=null&&(!0===t.unstable_strictMode&&(n=!0),t.identifierPrefix!==void 0&&(r=t.identifierPrefix),t.onUncaughtError!==void 0&&(o=t.onUncaughtError),t.onCaughtError!==void 0&&(s=t.onCaughtError),t.onRecoverableError!==void 0&&(c=t.onRecoverableError)),t=ep(e,1,!1,null,null,n,r,null,o,s,c,Pp),e[_t]=t.current,Sd(e),new Fp(t)}})),tt=t(((e,t)=>{function n(){if(!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__>`u`||typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE!=`function`))try{__REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(n)}catch(e){console.error(e)}}n(),t.exports=et()})),nt={},rt=(e={})=>{nt={...nt,...e}},it=e=>e.replace(/\n/g,`<br>`),at=e=>e.response?.statusText?e.response.statusText:e.message?.includes(`Network Error`)?`Network Error`:e.message?.includes(`timeout`)?`Request Timeout`:`An error has occurred`,ot=e=>e.response?.data?.message?e.response.data.message:e.response?.data?.error?e.response.data.error:e.message?e.message:String(e),st=(e,t=5)=>{let n=[],r=e.response?.data?.file,i=e.response?.data?.line;r&&i&&n.push(`${r}:${i}`);let a=e.response?.data?.trace||[];for(let e=0;e<Math.min(t,a.length);e++){let t=a[e];t?.file&&t?.line&&n.push(`${t.file}:${t.line}`)}return e.stack&&n.length===0&&n.push(e.stack),{traces:n,traceAsString:n.map(it).join(`<br>`)}},ct=function(e,t=5){let{traces:n,traceAsString:r}=st(e,t);return{heading:at(e),text:ot(e),trace:r,traceAsString:r,traceAsArray:n}},lt=globalThis.HTMLElement!==void 0&&Object.prototype.hasOwnProperty.call(globalThis.HTMLElement.prototype,`popover`),ut={"pk-spinner":`components/spinner/spinner.js`,"pk-icon":`components/icon/icon.js`,"pk-button":`components/button/button.js`,"pk-checkbox":`components/checkbox/checkbox.js`,"pk-checkbox-select":`components/checkbox-select/checkbox-select.js`,"pk-color-input":`components/color-input/color-input.js`,"pk-code-editor":`components/code-editor/code-editor.js`,"pk-input":`components/input/input.js`,"pk-input-group":`components/input-group/input-group.js`,"pk-input-group-addon":`components/input-group/input-group-addon.js`,"pk-input-group-button":`components/input-group/input-group-button.js`,"pk-input-group-input":`components/input-group/input-group-input.js`,"pk-input-group-text":`components/input-group/input-group-text.js`,"pk-input-group-textarea":`components/input-group/input-group-textarea.js`,"pk-textarea":`components/textarea/textarea.js`,"pk-tiptap-editor":`components/tiptap/tiptap-editor.js`,"pk-tiptap-input":`components/tiptap/tiptap-input.js`,"pk-tiptap-content":`components/tiptap/tiptap-content.js`,"pk-field":`components/field/field.js`,"pk-separator":`components/separator/separator.js`,"pk-status":`components/status/status.js`,"pk-toggle":`components/toggle/toggle.js`,"pk-toggle-group":`components/toggle-group/toggle-group.js`,"pk-lightswitch":`components/lightswitch/lightswitch.js`,"pk-button-group":`components/button-group/button-group.js`,"pk-button-group-separator":`components/button-group/button-group-separator.js`,"pk-button-group-text":`components/button-group/button-group-text.js`,"pk-dialog":`components/dialog/dialog.js`,"pk-popup":`components/popup/popup.js`,"pk-dropdown-label":`components/dropdown-menu/dropdown-label.js`,"pk-dropdown-item":`components/dropdown-menu/dropdown-item.js`,"pk-dropdown-separator":`components/dropdown-menu/dropdown-separator.js`,"pk-dropdown-menu":`components/dropdown-menu/dropdown-menu.js`,"pk-popover":`components/popover/popover.js`,"pk-tooltip":`components/tooltip/tooltip.js`,"pk-radio":`components/radio-group/radio.js`,"pk-radio-group":`components/radio-group/radio-group.js`,"pk-tab":`components/tabs/tab.js`,"pk-tab-heading":`components/tabs/tab-heading.js`,"pk-tab-panel":`components/tabs/tab-panel.js`,"pk-tabs":`components/tabs/tabs.js`,"pk-scroll-area":`components/scroll-area/scroll-area.js`,"pk-option":`components/select/option.js`,"pk-option-group":`components/select/option-group.js`,"pk-select":`components/select/select.js`,"pk-calendar":`components/calendar/calendar.js`,"pk-date-picker":`components/date-picker/date-picker.js`,"pk-combobox":`components/combobox/combobox.js`,"pk-time-picker":`components/time-picker/time-picker.js`,"pk-copy-button":`components/copy-button/copy-button.js`,"pk-editable-table":`components/editable-table/editable-table.js`},dt={"components/spinner/spinner":`src/components/spinner/pk-spinner.ts`,"components/icon/icon":`src/components/icon/pk-icon-loader.ts`,"components/button/button":`src/components/button/pk-button.ts`,"components/checkbox/checkbox":`src/components/checkbox/pk-checkbox.ts`,"components/checkbox-select/checkbox-select":`src/components/checkbox-select/pk-checkbox-select.ts`,"components/color-input/color-input":`src/components/color-input/pk-color-input.ts`,"components/code-editor/code-editor":`src/components/code-editor/pk-code-editor.ts`,"components/input/input":`src/components/input/pk-input.ts`,"components/input-group/input-group":`src/components/input-group/pk-input-group.ts`,"components/input-group/input-group-addon":`src/components/input-group/pk-input-group-addon.ts`,"components/input-group/input-group-button":`src/components/input-group/pk-input-group-button.ts`,"components/input-group/input-group-input":`src/components/input-group/pk-input-group-input.ts`,"components/input-group/input-group-text":`src/components/input-group/pk-input-group-text.ts`,"components/input-group/input-group-textarea":`src/components/input-group/pk-input-group-textarea.ts`,"components/textarea/textarea":`src/components/textarea/pk-textarea.ts`,"components/tiptap/tiptap-editor":`src/components/tiptap/pk-tiptap-editor.ts`,"components/tiptap/tiptap-input":`src/components/tiptap/pk-tiptap-input.ts`,"components/tiptap/tiptap-content":`src/components/tiptap/pk-tiptap-content.ts`,"components/field/field":`src/components/field/pk-field.ts`,"components/separator/separator":`src/components/separator/pk-separator.ts`,"components/status/status":`src/components/status/pk-status.ts`,"components/toggle/toggle":`src/components/toggle/pk-toggle.ts`,"components/toggle-group/toggle-group":`src/components/toggle-group/pk-toggle-group.ts`,"components/lightswitch/lightswitch":`src/components/lightswitch/pk-lightswitch.ts`,"components/button-group/button-group":`src/components/button-group/pk-button-group.ts`,"components/button-group/button-group-separator":`src/components/button-group/pk-button-group-separator.ts`,"components/button-group/button-group-text":`src/components/button-group/pk-button-group-text.ts`,"components/dialog/dialog":`src/components/dialog/pk-dialog.ts`,"components/popup/popup":`src/components/popup/pk-popup.ts`,"components/dropdown-menu/dropdown-label":`src/components/dropdown-menu/pk-dropdown-label.ts`,"components/dropdown-menu/dropdown-item":`src/components/dropdown-menu/pk-dropdown-item.ts`,"components/dropdown-menu/dropdown-separator":`src/components/dropdown-menu/pk-dropdown-separator.ts`,"components/dropdown-menu/dropdown-menu":`src/components/dropdown-menu/pk-dropdown-menu.ts`,"components/popover/popover":`src/components/popover/pk-popover.ts`,"components/tooltip/tooltip":`src/components/tooltip/pk-tooltip.ts`,"components/radio-group/radio":`src/components/radio-group/pk-radio.ts`,"components/radio-group/radio-group":`src/components/radio-group/pk-radio-group.ts`,"components/tabs/tab":`src/components/tabs/pk-tab.ts`,"components/tabs/tab-heading":`src/components/tabs/pk-tab-heading.ts`,"components/tabs/tab-panel":`src/components/tabs/pk-tab-panel.ts`,"components/tabs/tabs":`src/components/tabs/pk-tabs.ts`,"components/scroll-area/scroll-area":`src/components/scroll-area/pk-scroll-area.ts`,"components/select/option":`src/components/select/pk-option.ts`,"components/select/option-group":`src/components/select/pk-option-group.ts`,"components/select/select":`src/components/select/pk-select.ts`,"components/calendar/calendar":`src/components/calendar/pk-calendar.ts`,"components/date-picker/date-picker":`src/components/date-picker/pk-date-picker.ts`,"components/combobox/combobox":`src/components/combobox/pk-combobox.ts`,"components/time-picker/time-picker":`src/components/time-picker/pk-time-picker.ts`,"components/copy-button/copy-button":`src/components/copy-button/pk-copy-button.ts`,"components/editable-table/editable-table":`src/components/editable-table/pk-editable-table.ts`},ft={"src/components/icon/pk-icon-loader.ts":`src/components/icon/pk-icon.ts`},pt=Object.fromEntries(Object.values(dt).map(e=>{let t=ft[e]??e;return[t.replace(/^src\//,``).replace(/\.ts$/,``),t]}));Object.fromEntries(Object.keys(pt).map(e=>[e.split(`/`).pop(),`${e}.js`]));var mt=`modulepreload`,ht=function(e,t){return new URL(e,t).href},gt={},_t=function(e,t,n){let r=Promise.resolve();if(t&&t.length>0){let e=document.getElementsByTagName(`link`),i=document.querySelector(`meta[property=csp-nonce]`),a=i?.nonce||i?.getAttribute(`nonce`);function o(e){return Promise.all(e.map(e=>Promise.resolve(e).then(e=>({status:`fulfilled`,value:e}),e=>({status:`rejected`,reason:e}))))}function s(e){return import.meta.resolve?import.meta.resolve(e):new URL(e,import.meta.url).href}r=o(t.map(t=>{if(t=ht(t,n),t=s(t),t in gt)return;gt[t]=!0;let r=t.endsWith(`.css`);for(let n=e.length-1;n>=0;n--){let i=e[n];if(i.href===t&&(!r||i.rel===`stylesheet`))return}let i=document.createElement(`link`);if(i.rel=r?`stylesheet`:mt,r||(i.as=`script`),i.crossOrigin=``,i.href=t,a&&i.setAttribute(`nonce`,a),document.head.appendChild(i),r)return new Promise((e,n)=>{i.addEventListener(`load`,e),i.addEventListener(`error`,()=>n(Error(`Unable to preload CSS for ${t}`)))})}))}function i(e){let t=new Event(`vite:preloadError`,{cancelable:!0});if(t.payload=e,window.dispatchEvent(t),!t.defaultPrevented)throw e}return r.then(t=>{for(let e of t||[])e.status===`rejected`&&i(e.reason);return e().catch(i)})},vt=`plugin-kit-document-scroll-stability`,yt=()=>{if(typeof document>`u`||document.querySelector(`style[data-plugin-kit-style-id="${vt}"]`))return;let e=document.createElement(`style`);e.setAttribute(`data-plugin-kit-style-id`,vt),e.textContent=`html { scrollbar-gutter: stable; }`,document.head.appendChild(e)},bt=e=>{e.trim()},xt=e=>{(e||[]).map(e=>e.trim()).filter(Boolean).length},St=e=>{e.portalContainer instanceof ShadowRoot&&yt(),e.shadowRootSelectors&&xt(e.shadowRootSelectors)},Ct=``;function wt(e){Ct=e}function Tt(e=``){if(!Ct){let e=document.querySelector(`[data-plugin-kit]`);if(e?.hasAttribute(`data-plugin-kit`)){let t=new URL(e.getAttribute(`data-plugin-kit`)??``,window.location.href).pathname;wt(t)}else{let e=[...document.getElementsByTagName(`script`)].find(e=>e.src.endsWith(`plugin-kit.js`)||e.src.endsWith(`plugin-kit.loader.js`));e&&wt(String(e.getAttribute(`src`)).split(`/`).slice(0,-1).join(`/`))}}return Ct.replace(/\/$/,``)+(e?`/${e.replace(/^\//,``)}`:``)}var Et=`pk-`,Dt=`pk-discovery-complete`;new MutationObserver(e=>{for(let{addedNodes:t}of e)for(let e of t)e.nodeType===Node.ELEMENT_NODE&&Ot(e)});async function Ot(e=document){let t=e instanceof Element?e.tagName.toLowerCase():``,n=t.startsWith(Et),r=[...e.querySelectorAll(`:not(:defined)`)].map(e=>e.tagName.toLowerCase()).filter(e=>e.startsWith(Et));n&&!customElements.get(t)&&r.push(t);let i=e.querySelectorAll(`[data-pk-preload]`),a=e instanceof Element&&e.hasAttribute(`data-pk-preload`)?[e,...i]:i;for(let e of a)r.push(...e.getAttribute(`data-pk-preload`).split(/\s+/).filter(e=>e.startsWith(Et)));let o=[...new Set(r)],s=await Promise.allSettled(o.map(e=>kt(e)));for(let e of s)e.status===`rejected`&&console.warn(e.reason);await new Promise(requestAnimationFrame),e.dispatchEvent(new CustomEvent(Dt,{bubbles:!1,cancelable:!1,composed:!0}))}function kt(e){if(customElements.get(e))return Promise.resolve();let t=ut[e];return t?_t(()=>import(Tt(t)).then(()=>void 0),[],import.meta.url):Promise.reject(Error(`Unable to autoload <${e}> — no module path registered`))}var At=e=>{let t=typeof e==`string`?document.querySelector(e):e;if(!(t instanceof HTMLElement))throw Error(`mountShadowApp: could not resolve host element "${String(e)}".`);return t},jt=(e,t,n)=>{e.querySelectorAll(`[${n}]`).forEach(e=>e.remove()),t.forEach((t,r)=>{if(!t)return;let i=document.createElement(`style`);i.setAttribute(n,String(r)),i.textContent=t,e.append(i)})},Mt=({element:e,styles:t=[],styleAttr:n=`data-pk-shadow-style`,rootAttr:r=`data-pk-shadow-root`,mountClassName:i=`w-full`})=>{let a=At(e);if(!a.attachShadow)throw Error(`mountShadowApp: host element does not support Shadow DOM.`);let o=a.shadowRoot??a.attachShadow({mode:`open`});jt(o,t,n);let s=o.querySelector(`[${r}]`);return s||(s=document.createElement(`div`),s.setAttribute(r,``),i&&(s.className=i),o.append(s)),{host:a,shadowRoot:o,mountNode:s,portalContainer:o}},Nt=()=>{let e=globalThis.Craft;if(typeof e?.t==`function`)return(t,n,r)=>e.t(t,n,r)},Pt=e=>{let{translationCategory:t,translate:n,hostBridge:r,portalClassName:i,portalContainer:a,shadowRootSelectors:o}=e;t&&Pe(t);let s=n??Nt();s&&Me(s),r&&rt(r),i&&bt(i);let c={...a===void 0?{}:{portalContainer:a},...o===void 0?{}:{shadowRootSelectors:o}};(c.portalContainer!==void 0||c.shadowRootSelectors!==void 0)&&St(c)},Ft=Pt,A=e(f(),1),j=fe(),It=(0,A.createContext)({});function Lt({children:e,...t}){return Pt(t),(0,j.jsx)(It.Provider,{value:t,children:e})}var Rt=()=>{if(!window.Craft)throw Error(`Craft host bridge is unavailable because window.Craft is not defined.`);return window.Craft},zt=()=>({request:(e,t,n={})=>Rt().sendActionRequest(e,t,n),openElementSelector:(e,t)=>Rt().createElementSelectorModal(e,t),formatDate:e=>Rt().formatDate(e),getTimepickerOptions:()=>Rt().timepickerOptions||{},getLocale:()=>Rt().locale||`en-US`}),Bt=$e(),Vt=tt();function Ht(e){var t,n,r=``;if(typeof e==`string`||typeof e==`number`)r+=e;else if(typeof e==`object`)if(Array.isArray(e)){var i=e.length;for(t=0;t<i;t++)e[t]&&(n=Ht(e[t]))&&(r&&(r+=` `),r+=n)}else for(n in e)e[n]&&(r&&(r+=` `),r+=n);return r}function Ut(){for(var e,t,n=0,r=``,i=arguments.length;n<i;n++)(e=arguments[n])&&(t=Ht(e))&&(r&&(r+=` `),r+=t);return r}var Wt=(e,t)=>{let n=Array(e.length+t.length);for(let t=0;t<e.length;t++)n[t]=e[t];for(let r=0;r<t.length;r++)n[e.length+r]=t[r];return n},Gt=(e,t)=>({classGroupId:e,validator:t}),Kt=(e=new Map,t=null,n)=>({nextPart:e,validators:t,classGroupId:n}),qt=`-`,Jt=[],Yt=`arbitrary..`,Xt=e=>{let t=$t(e),{conflictingClassGroups:n,conflictingClassGroupModifiers:r}=e;return{getClassGroupId:e=>{if(e.startsWith(`[`)&&e.endsWith(`]`))return Qt(e);let n=e.split(qt);return Zt(n,+(n[0]===``&&n.length>1),t)},getConflictingClassGroupIds:(e,t)=>{if(t){let t=r[e],i=n[e];return t?i?Wt(i,t):t:i||Jt}return n[e]||Jt}}},Zt=(e,t,n)=>{if(e.length-t===0)return n.classGroupId;let r=e[t],i=n.nextPart.get(r);if(i){let n=Zt(e,t+1,i);if(n)return n}let a=n.validators;if(a===null)return;let o=t===0?e.join(qt):e.slice(t).join(qt),s=a.length;for(let e=0;e<s;e++){let t=a[e];if(t.validator(o))return t.classGroupId}},Qt=e=>e.slice(1,-1).indexOf(`:`)===-1?void 0:(()=>{let t=e.slice(1,-1),n=t.indexOf(`:`),r=t.slice(0,n);return r?Yt+r:void 0})(),$t=e=>{let{theme:t,classGroups:n}=e;return en(n,t)},en=(e,t)=>{let n=Kt();for(let r in e){let i=e[r];tn(i,n,r,t)}return n},tn=(e,t,n,r)=>{let i=e.length;for(let a=0;a<i;a++){let i=e[a];nn(i,t,n,r)}},nn=(e,t,n,r)=>{if(typeof e==`string`){rn(e,t,n);return}if(typeof e==`function`){an(e,t,n,r);return}on(e,t,n,r)},rn=(e,t,n)=>{let r=e===``?t:sn(t,e);r.classGroupId=n},an=(e,t,n,r)=>{if(cn(e)){tn(e(r),t,n,r);return}t.validators===null&&(t.validators=[]),t.validators.push(Gt(n,e))},on=(e,t,n,r)=>{let i=Object.entries(e),a=i.length;for(let e=0;e<a;e++){let[a,o]=i[e];tn(o,sn(t,a),n,r)}},sn=(e,t)=>{let n=e,r=t.split(qt),i=r.length;for(let e=0;e<i;e++){let t=r[e],i=n.nextPart.get(t);i||(i=Kt(),n.nextPart.set(t,i)),n=i}return n},cn=e=>`isThemeGetter`in e&&e.isThemeGetter===!0,ln=e=>{if(e<1)return{get:()=>void 0,set:()=>{}};let t=0,n=Object.create(null),r=Object.create(null),i=(i,a)=>{n[i]=a,t++,t>e&&(t=0,r=n,n=Object.create(null))};return{get(e){let t=n[e];if(t!==void 0)return t;if((t=r[e])!==void 0)return i(e,t),t},set(e,t){e in n?n[e]=t:i(e,t)}}},un=`!`,dn=`:`,fn=[],pn=(e,t,n,r,i)=>({modifiers:e,hasImportantModifier:t,baseClassName:n,maybePostfixModifierPosition:r,isExternal:i}),mn=e=>{let{prefix:t,experimentalParseClassName:n}=e,r=e=>{let t=[],n=0,r=0,i=0,a,o=e.length;for(let s=0;s<o;s++){let o=e[s];if(n===0&&r===0){if(o===dn){t.push(e.slice(i,s)),i=s+1;continue}if(o===`/`){a=s;continue}}o===`[`?n++:o===`]`?n--:o===`(`?r++:o===`)`&&r--}let s=t.length===0?e:e.slice(i),c=s,l=!1;s.endsWith(un)?(c=s.slice(0,-1),l=!0):s.startsWith(un)&&(c=s.slice(1),l=!0);let u=a&&a>i?a-i:void 0;return pn(t,l,c,u)};if(t){let e=t+dn,n=r;r=t=>t.startsWith(e)?n(t.slice(e.length)):pn(fn,!1,t,void 0,!0)}if(n){let e=r;r=t=>n({className:t,parseClassName:e})}return r},hn=e=>{let t=new Map;return e.orderSensitiveModifiers.forEach((e,n)=>{t.set(e,1e6+n)}),e=>{let n=[],r=[];for(let i=0;i<e.length;i++){let a=e[i],o=a[0]===`[`,s=t.has(a);o||s?(r.length>0&&(r.sort(),n.push(...r),r=[]),n.push(a)):r.push(a)}return r.length>0&&(r.sort(),n.push(...r)),n}},gn=e=>({cache:ln(e.cacheSize),parseClassName:mn(e),sortModifiers:hn(e),postfixLookupClassGroupIds:_n(e),...Xt(e)}),_n=e=>{let t=Object.create(null),n=e.postfixLookupClassGroups;if(n)for(let e=0;e<n.length;e++)t[n[e]]=!0;return t},vn=/\s+/,yn=(e,t)=>{let{parseClassName:n,getClassGroupId:r,getConflictingClassGroupIds:i,sortModifiers:a,postfixLookupClassGroupIds:o}=t,s=[],c=e.trim().split(vn),l=``;for(let e=c.length-1;e>=0;--e){let t=c[e],{isExternal:u,modifiers:d,hasImportantModifier:f,baseClassName:p,maybePostfixModifierPosition:m}=n(t);if(u){l=t+(l.length>0?` `+l:l);continue}let h=!!m,g;if(h){g=r(p.substring(0,m));let e=g&&o[g]?r(p):void 0;e&&e!==g&&(g=e,h=!1)}else g=r(p);if(!g){if(!h){l=t+(l.length>0?` `+l:l);continue}if(g=r(p),!g){l=t+(l.length>0?` `+l:l);continue}h=!1}let _=d.length===0?``:d.length===1?d[0]:a(d).join(`:`),v=f?_+un:_,y=v+g;if(s.indexOf(y)>-1)continue;s.push(y);let b=i(g,h);for(let e=0;e<b.length;++e){let t=b[e];s.push(v+t)}l=t+(l.length>0?` `+l:l)}return l},bn=(...e)=>{let t=0,n,r,i=``;for(;t<e.length;)(n=e[t++])&&(r=xn(n))&&(i&&(i+=` `),i+=r);return i},xn=e=>{if(typeof e==`string`)return e;let t,n=``;for(let r=0;r<e.length;r++)e[r]&&(t=xn(e[r]))&&(n&&(n+=` `),n+=t);return n},Sn=(e,...t)=>{let n,r,i,a,o=o=>(n=gn(t.reduce((e,t)=>t(e),e())),r=n.cache.get,i=n.cache.set,a=s,s(o)),s=e=>{let t=r(e);if(t)return t;let a=yn(e,n);return i(e,a),a};return a=o,(...e)=>a(bn(...e))},Cn=[],wn=e=>{let t=t=>t[e]||Cn;return t.isThemeGetter=!0,t},Tn=/^\[(?:(\w[\w-]*):)?(.+)\]$/i,En=/^\((?:(\w[\w-]*):)?(.+)\)$/i,Dn=/^\d+(?:\.\d+)?\/\d+(?:\.\d+)?$/,On=/^(\d+(\.\d+)?)?(xs|sm|md|lg|xl)$/,kn=/\d+(%|px|r?em|[sdl]?v([hwib]|min|max)|pt|pc|in|cm|mm|cap|ch|ex|r?lh|cq(w|h|i|b|min|max))|\b(calc|min|max|clamp)\(.+\)|^0$/,An=/^(rgba?|hsla?|hwb|(ok)?(lab|lch)|color-mix)\(.+\)$/,jn=/^(inset_)?-?((\d+)?\.?(\d+)[a-z]+|0)_-?((\d+)?\.?(\d+)[a-z]+|0)/,Mn=/^(url|image|image-set|cross-fade|element|(repeating-)?(linear|radial|conic)-gradient)\(.+\)$/,Nn=e=>Dn.test(e),M=e=>!!e&&!Number.isNaN(Number(e)),Pn=e=>!!e&&Number.isInteger(Number(e)),Fn=e=>e.endsWith(`%`)&&M(e.slice(0,-1)),In=e=>On.test(e),Ln=()=>!0,Rn=e=>kn.test(e)&&!An.test(e),zn=()=>!1,Bn=e=>jn.test(e),Vn=e=>Mn.test(e),Hn=e=>!N(e)&&!P(e),Un=e=>e.startsWith(`@container`)&&(e[10]===`/`&&e[11]!==void 0||e[11]===`s`&&e[16]!==void 0&&e.startsWith(`-size/`,10)||e[11]===`n`&&e[18]!==void 0&&e.startsWith(`-normal/`,10)),Wn=e=>ar(e,lr,zn),N=e=>Tn.test(e),Gn=e=>ar(e,ur,Rn),Kn=e=>ar(e,dr,M),qn=e=>ar(e,pr,Ln),Jn=e=>ar(e,fr,zn),Yn=e=>ar(e,sr,zn),Xn=e=>ar(e,cr,Vn),Zn=e=>ar(e,mr,Bn),P=e=>En.test(e),Qn=e=>or(e,ur),$n=e=>or(e,fr),er=e=>or(e,sr),tr=e=>or(e,lr),nr=e=>or(e,cr),rr=e=>or(e,mr,!0),ir=e=>or(e,pr,!0),ar=(e,t,n)=>{let r=Tn.exec(e);return r?r[1]?t(r[1]):n(r[2]):!1},or=(e,t,n=!1)=>{let r=En.exec(e);return r?r[1]?t(r[1]):n:!1},sr=e=>e===`position`||e===`percentage`,cr=e=>e===`image`||e===`url`,lr=e=>e===`length`||e===`size`||e===`bg-size`,ur=e=>e===`length`,dr=e=>e===`number`,fr=e=>e===`family-name`,pr=e=>e===`number`||e===`weight`,mr=e=>e===`shadow`,hr=Sn(()=>{let e=wn(`color`),t=wn(`font`),n=wn(`text`),r=wn(`font-weight`),i=wn(`tracking`),a=wn(`leading`),o=wn(`breakpoint`),s=wn(`container`),c=wn(`spacing`),l=wn(`radius`),u=wn(`shadow`),d=wn(`inset-shadow`),f=wn(`text-shadow`),p=wn(`drop-shadow`),m=wn(`blur`),h=wn(`perspective`),g=wn(`aspect`),_=wn(`ease`),v=wn(`animate`),y=()=>[`auto`,`avoid`,`all`,`avoid-page`,`page`,`left`,`right`,`column`],b=()=>[`center`,`top`,`bottom`,`left`,`right`,`top-left`,`left-top`,`top-right`,`right-top`,`bottom-right`,`right-bottom`,`bottom-left`,`left-bottom`],x=()=>[...b(),P,N],ee=()=>[`auto`,`hidden`,`clip`,`visible`,`scroll`],S=()=>[`auto`,`contain`,`none`],C=()=>[P,N,c],w=()=>[Nn,`full`,`auto`,...C()],te=()=>[Pn,`none`,`subgrid`,P,N],ne=()=>[`auto`,{span:[`full`,Pn,P,N]},Pn,P,N],re=()=>[Pn,`auto`,P,N],ie=()=>[`auto`,`min`,`max`,`fr`,P,N],ae=()=>[`start`,`end`,`center`,`between`,`around`,`evenly`,`stretch`,`baseline`,`center-safe`,`end-safe`],oe=()=>[`start`,`end`,`center`,`stretch`,`center-safe`,`end-safe`],se=()=>[`auto`,...C()],T=()=>[Nn,`auto`,`full`,`dvw`,`dvh`,`lvw`,`lvh`,`svw`,`svh`,`min`,`max`,`fit`,...C()],E=()=>[Nn,`screen`,`full`,`dvw`,`lvw`,`svw`,`min`,`max`,`fit`,...C()],D=()=>[Nn,`screen`,`full`,`lh`,`dvh`,`lvh`,`svh`,`min`,`max`,`fit`,...C()],O=()=>[e,P,N],ce=()=>[...b(),er,Yn,{position:[P,N]}],le=()=>[`no-repeat`,{repeat:[``,`x`,`y`,`space`,`round`]}],ue=()=>[`auto`,`cover`,`contain`,tr,Wn,{size:[P,N]}],de=()=>[Fn,Qn,Gn],k=()=>[``,`none`,`full`,l,P,N],fe=()=>[``,M,Qn,Gn],pe=()=>[`solid`,`dashed`,`dotted`,`double`],me=()=>[`normal`,`multiply`,`screen`,`overlay`,`darken`,`lighten`,`color-dodge`,`color-burn`,`hard-light`,`soft-light`,`difference`,`exclusion`,`hue`,`saturation`,`color`,`luminosity`],he=()=>[M,Fn,er,Yn],ge=()=>[``,`none`,m,P,N],_e=()=>[`none`,M,P,N],ve=()=>[`none`,M,P,N],ye=()=>[M,P,N],be=()=>[Nn,`full`,...C()];return{cacheSize:500,theme:{animate:[`spin`,`ping`,`pulse`,`bounce`],aspect:[`video`],blur:[In],breakpoint:[In],color:[Ln],container:[In],"drop-shadow":[In],ease:[`in`,`out`,`in-out`],font:[Hn],"font-weight":[`thin`,`extralight`,`light`,`normal`,`medium`,`semibold`,`bold`,`extrabold`,`black`],"inset-shadow":[In],leading:[`none`,`tight`,`snug`,`normal`,`relaxed`,`loose`],perspective:[`dramatic`,`near`,`normal`,`midrange`,`distant`,`none`],radius:[In],shadow:[In],spacing:[`px`,M],text:[In],"text-shadow":[In],tracking:[`tighter`,`tight`,`normal`,`wide`,`wider`,`widest`]},classGroups:{aspect:[{aspect:[`auto`,`square`,Nn,N,P,g]}],container:[`container`],"container-type":[{"@container":[``,`normal`,`size`,P,N]}],"container-named":[Un],columns:[{columns:[M,N,P,s]}],"break-after":[{"break-after":y()}],"break-before":[{"break-before":y()}],"break-inside":[{"break-inside":[`auto`,`avoid`,`avoid-page`,`avoid-column`]}],"box-decoration":[{"box-decoration":[`slice`,`clone`]}],box:[{box:[`border`,`content`]}],display:[`block`,`inline-block`,`inline`,`flex`,`inline-flex`,`table`,`inline-table`,`table-caption`,`table-cell`,`table-column`,`table-column-group`,`table-footer-group`,`table-header-group`,`table-row-group`,`table-row`,`flow-root`,`grid`,`inline-grid`,`contents`,`list-item`,`hidden`],sr:[`sr-only`,`not-sr-only`],float:[{float:[`right`,`left`,`none`,`start`,`end`]}],clear:[{clear:[`left`,`right`,`both`,`none`,`start`,`end`]}],isolation:[`isolate`,`isolation-auto`],"object-fit":[{object:[`contain`,`cover`,`fill`,`none`,`scale-down`]}],"object-position":[{object:x()}],overflow:[{overflow:ee()}],"overflow-x":[{"overflow-x":ee()}],"overflow-y":[{"overflow-y":ee()}],overscroll:[{overscroll:S()}],"overscroll-x":[{"overscroll-x":S()}],"overscroll-y":[{"overscroll-y":S()}],position:[`static`,`fixed`,`absolute`,`relative`,`sticky`],inset:[{inset:w()}],"inset-x":[{"inset-x":w()}],"inset-y":[{"inset-y":w()}],start:[{"inset-s":w(),start:w()}],end:[{"inset-e":w(),end:w()}],"inset-bs":[{"inset-bs":w()}],"inset-be":[{"inset-be":w()}],top:[{top:w()}],right:[{right:w()}],bottom:[{bottom:w()}],left:[{left:w()}],visibility:[`visible`,`invisible`,`collapse`],z:[{z:[Pn,`auto`,P,N]}],basis:[{basis:[Nn,`full`,`auto`,s,...C()]}],"flex-direction":[{flex:[`row`,`row-reverse`,`col`,`col-reverse`]}],"flex-wrap":[{flex:[`nowrap`,`wrap`,`wrap-reverse`]}],flex:[{flex:[M,Nn,`auto`,`initial`,`none`,N]}],grow:[{grow:[``,M,P,N]}],shrink:[{shrink:[``,M,P,N]}],order:[{order:[Pn,`first`,`last`,`none`,P,N]}],"grid-cols":[{"grid-cols":te()}],"col-start-end":[{col:ne()}],"col-start":[{"col-start":re()}],"col-end":[{"col-end":re()}],"grid-rows":[{"grid-rows":te()}],"row-start-end":[{row:ne()}],"row-start":[{"row-start":re()}],"row-end":[{"row-end":re()}],"grid-flow":[{"grid-flow":[`row`,`col`,`dense`,`row-dense`,`col-dense`]}],"auto-cols":[{"auto-cols":ie()}],"auto-rows":[{"auto-rows":ie()}],gap:[{gap:C()}],"gap-x":[{"gap-x":C()}],"gap-y":[{"gap-y":C()}],"justify-content":[{justify:[...ae(),`normal`]}],"justify-items":[{"justify-items":[...oe(),`normal`]}],"justify-self":[{"justify-self":[`auto`,...oe()]}],"align-content":[{content:[`normal`,...ae()]}],"align-items":[{items:[...oe(),{baseline:[``,`last`]}]}],"align-self":[{self:[`auto`,...oe(),{baseline:[``,`last`]}]}],"place-content":[{"place-content":ae()}],"place-items":[{"place-items":[...oe(),`baseline`]}],"place-self":[{"place-self":[`auto`,...oe()]}],p:[{p:C()}],px:[{px:C()}],py:[{py:C()}],ps:[{ps:C()}],pe:[{pe:C()}],pbs:[{pbs:C()}],pbe:[{pbe:C()}],pt:[{pt:C()}],pr:[{pr:C()}],pb:[{pb:C()}],pl:[{pl:C()}],m:[{m:se()}],mx:[{mx:se()}],my:[{my:se()}],ms:[{ms:se()}],me:[{me:se()}],mbs:[{mbs:se()}],mbe:[{mbe:se()}],mt:[{mt:se()}],mr:[{mr:se()}],mb:[{mb:se()}],ml:[{ml:se()}],"space-x":[{"space-x":C()}],"space-x-reverse":[`space-x-reverse`],"space-y":[{"space-y":C()}],"space-y-reverse":[`space-y-reverse`],size:[{size:T()}],"inline-size":[{inline:[`auto`,...E()]}],"min-inline-size":[{"min-inline":[`auto`,...E()]}],"max-inline-size":[{"max-inline":[`none`,...E()]}],"block-size":[{block:[`auto`,...D()]}],"min-block-size":[{"min-block":[`auto`,...D()]}],"max-block-size":[{"max-block":[`none`,...D()]}],w:[{w:[s,`screen`,...T()]}],"min-w":[{"min-w":[s,`screen`,`none`,...T()]}],"max-w":[{"max-w":[s,`screen`,`none`,`prose`,{screen:[o]},...T()]}],h:[{h:[`screen`,`lh`,...T()]}],"min-h":[{"min-h":[`screen`,`lh`,`none`,...T()]}],"max-h":[{"max-h":[`screen`,`lh`,...T()]}],"font-size":[{text:[`base`,n,Qn,Gn]}],"font-smoothing":[`antialiased`,`subpixel-antialiased`],"font-style":[`italic`,`not-italic`],"font-weight":[{font:[r,ir,qn]}],"font-stretch":[{"font-stretch":[`ultra-condensed`,`extra-condensed`,`condensed`,`semi-condensed`,`normal`,`semi-expanded`,`expanded`,`extra-expanded`,`ultra-expanded`,Fn,N]}],"font-family":[{font:[$n,Jn,t]}],"font-features":[{"font-features":[N]}],"fvn-normal":[`normal-nums`],"fvn-ordinal":[`ordinal`],"fvn-slashed-zero":[`slashed-zero`],"fvn-figure":[`lining-nums`,`oldstyle-nums`],"fvn-spacing":[`proportional-nums`,`tabular-nums`],"fvn-fraction":[`diagonal-fractions`,`stacked-fractions`],tracking:[{tracking:[i,P,N]}],"line-clamp":[{"line-clamp":[M,`none`,P,Kn]}],leading:[{leading:[a,...C()]}],"list-image":[{"list-image":[`none`,P,N]}],"list-style-position":[{list:[`inside`,`outside`]}],"list-style-type":[{list:[`disc`,`decimal`,`none`,P,N]}],"text-alignment":[{text:[`left`,`center`,`right`,`justify`,`start`,`end`]}],"placeholder-color":[{placeholder:O()}],"text-color":[{text:O()}],"text-decoration":[`underline`,`overline`,`line-through`,`no-underline`],"text-decoration-style":[{decoration:[...pe(),`wavy`]}],"text-decoration-thickness":[{decoration:[M,`from-font`,`auto`,P,Gn]}],"text-decoration-color":[{decoration:O()}],"underline-offset":[{"underline-offset":[M,`auto`,P,N]}],"text-transform":[`uppercase`,`lowercase`,`capitalize`,`normal-case`],"text-overflow":[`truncate`,`text-ellipsis`,`text-clip`],"text-wrap":[{text:[`wrap`,`nowrap`,`balance`,`pretty`]}],indent:[{indent:C()}],"tab-size":[{tab:[Pn,P,N]}],"vertical-align":[{align:[`baseline`,`top`,`middle`,`bottom`,`text-top`,`text-bottom`,`sub`,`super`,P,N]}],whitespace:[{whitespace:[`normal`,`nowrap`,`pre`,`pre-line`,`pre-wrap`,`break-spaces`]}],break:[{break:[`normal`,`words`,`all`,`keep`]}],wrap:[{wrap:[`break-word`,`anywhere`,`normal`]}],hyphens:[{hyphens:[`none`,`manual`,`auto`]}],content:[{content:[`none`,P,N]}],"bg-attachment":[{bg:[`fixed`,`local`,`scroll`]}],"bg-clip":[{"bg-clip":[`border`,`padding`,`content`,`text`]}],"bg-origin":[{"bg-origin":[`border`,`padding`,`content`]}],"bg-position":[{bg:ce()}],"bg-repeat":[{bg:le()}],"bg-size":[{bg:ue()}],"bg-image":[{bg:[`none`,{linear:[{to:[`t`,`tr`,`r`,`br`,`b`,`bl`,`l`,`tl`]},Pn,P,N],radial:[``,P,N],conic:[Pn,P,N]},nr,Xn]}],"bg-color":[{bg:O()}],"gradient-from-pos":[{from:de()}],"gradient-via-pos":[{via:de()}],"gradient-to-pos":[{to:de()}],"gradient-from":[{from:O()}],"gradient-via":[{via:O()}],"gradient-to":[{to:O()}],rounded:[{rounded:k()}],"rounded-s":[{"rounded-s":k()}],"rounded-e":[{"rounded-e":k()}],"rounded-t":[{"rounded-t":k()}],"rounded-r":[{"rounded-r":k()}],"rounded-b":[{"rounded-b":k()}],"rounded-l":[{"rounded-l":k()}],"rounded-ss":[{"rounded-ss":k()}],"rounded-se":[{"rounded-se":k()}],"rounded-ee":[{"rounded-ee":k()}],"rounded-es":[{"rounded-es":k()}],"rounded-tl":[{"rounded-tl":k()}],"rounded-tr":[{"rounded-tr":k()}],"rounded-br":[{"rounded-br":k()}],"rounded-bl":[{"rounded-bl":k()}],"border-w":[{border:fe()}],"border-w-x":[{"border-x":fe()}],"border-w-y":[{"border-y":fe()}],"border-w-s":[{"border-s":fe()}],"border-w-e":[{"border-e":fe()}],"border-w-bs":[{"border-bs":fe()}],"border-w-be":[{"border-be":fe()}],"border-w-t":[{"border-t":fe()}],"border-w-r":[{"border-r":fe()}],"border-w-b":[{"border-b":fe()}],"border-w-l":[{"border-l":fe()}],"divide-x":[{"divide-x":fe()}],"divide-x-reverse":[`divide-x-reverse`],"divide-y":[{"divide-y":fe()}],"divide-y-reverse":[`divide-y-reverse`],"border-style":[{border:[...pe(),`hidden`,`none`]}],"divide-style":[{divide:[...pe(),`hidden`,`none`]}],"border-color":[{border:O()}],"border-color-x":[{"border-x":O()}],"border-color-y":[{"border-y":O()}],"border-color-s":[{"border-s":O()}],"border-color-e":[{"border-e":O()}],"border-color-bs":[{"border-bs":O()}],"border-color-be":[{"border-be":O()}],"border-color-t":[{"border-t":O()}],"border-color-r":[{"border-r":O()}],"border-color-b":[{"border-b":O()}],"border-color-l":[{"border-l":O()}],"divide-color":[{divide:O()}],"outline-style":[{outline:[...pe(),`none`,`hidden`]}],"outline-offset":[{"outline-offset":[M,P,N]}],"outline-w":[{outline:[``,M,Qn,Gn]}],"outline-color":[{outline:O()}],shadow:[{shadow:[``,`none`,u,rr,Zn]}],"shadow-color":[{shadow:O()}],"inset-shadow":[{"inset-shadow":[`none`,d,rr,Zn]}],"inset-shadow-color":[{"inset-shadow":O()}],"ring-w":[{ring:fe()}],"ring-w-inset":[`ring-inset`],"ring-color":[{ring:O()}],"ring-offset-w":[{"ring-offset":[M,Gn]}],"ring-offset-color":[{"ring-offset":O()}],"inset-ring-w":[{"inset-ring":fe()}],"inset-ring-color":[{"inset-ring":O()}],"text-shadow":[{"text-shadow":[`none`,f,rr,Zn]}],"text-shadow-color":[{"text-shadow":O()}],opacity:[{opacity:[M,P,N]}],"mix-blend":[{"mix-blend":[...me(),`plus-darker`,`plus-lighter`]}],"bg-blend":[{"bg-blend":me()}],"mask-clip":[{"mask-clip":[`border`,`padding`,`content`,`fill`,`stroke`,`view`]},`mask-no-clip`],"mask-composite":[{mask:[`add`,`subtract`,`intersect`,`exclude`]}],"mask-image-linear-pos":[{"mask-linear":[M]}],"mask-image-linear-from-pos":[{"mask-linear-from":he()}],"mask-image-linear-to-pos":[{"mask-linear-to":he()}],"mask-image-linear-from-color":[{"mask-linear-from":O()}],"mask-image-linear-to-color":[{"mask-linear-to":O()}],"mask-image-t-from-pos":[{"mask-t-from":he()}],"mask-image-t-to-pos":[{"mask-t-to":he()}],"mask-image-t-from-color":[{"mask-t-from":O()}],"mask-image-t-to-color":[{"mask-t-to":O()}],"mask-image-r-from-pos":[{"mask-r-from":he()}],"mask-image-r-to-pos":[{"mask-r-to":he()}],"mask-image-r-from-color":[{"mask-r-from":O()}],"mask-image-r-to-color":[{"mask-r-to":O()}],"mask-image-b-from-pos":[{"mask-b-from":he()}],"mask-image-b-to-pos":[{"mask-b-to":he()}],"mask-image-b-from-color":[{"mask-b-from":O()}],"mask-image-b-to-color":[{"mask-b-to":O()}],"mask-image-l-from-pos":[{"mask-l-from":he()}],"mask-image-l-to-pos":[{"mask-l-to":he()}],"mask-image-l-from-color":[{"mask-l-from":O()}],"mask-image-l-to-color":[{"mask-l-to":O()}],"mask-image-x-from-pos":[{"mask-x-from":he()}],"mask-image-x-to-pos":[{"mask-x-to":he()}],"mask-image-x-from-color":[{"mask-x-from":O()}],"mask-image-x-to-color":[{"mask-x-to":O()}],"mask-image-y-from-pos":[{"mask-y-from":he()}],"mask-image-y-to-pos":[{"mask-y-to":he()}],"mask-image-y-from-color":[{"mask-y-from":O()}],"mask-image-y-to-color":[{"mask-y-to":O()}],"mask-image-radial":[{"mask-radial":[P,N]}],"mask-image-radial-from-pos":[{"mask-radial-from":he()}],"mask-image-radial-to-pos":[{"mask-radial-to":he()}],"mask-image-radial-from-color":[{"mask-radial-from":O()}],"mask-image-radial-to-color":[{"mask-radial-to":O()}],"mask-image-radial-shape":[{"mask-radial":[`circle`,`ellipse`]}],"mask-image-radial-size":[{"mask-radial":[{closest:[`side`,`corner`],farthest:[`side`,`corner`]}]}],"mask-image-radial-pos":[{"mask-radial-at":b()}],"mask-image-conic-pos":[{"mask-conic":[M]}],"mask-image-conic-from-pos":[{"mask-conic-from":he()}],"mask-image-conic-to-pos":[{"mask-conic-to":he()}],"mask-image-conic-from-color":[{"mask-conic-from":O()}],"mask-image-conic-to-color":[{"mask-conic-to":O()}],"mask-mode":[{mask:[`alpha`,`luminance`,`match`]}],"mask-origin":[{"mask-origin":[`border`,`padding`,`content`,`fill`,`stroke`,`view`]}],"mask-position":[{mask:ce()}],"mask-repeat":[{mask:le()}],"mask-size":[{mask:ue()}],"mask-type":[{"mask-type":[`alpha`,`luminance`]}],"mask-image":[{mask:[`none`,P,N]}],filter:[{filter:[``,`none`,P,N]}],blur:[{blur:ge()}],brightness:[{brightness:[M,P,N]}],contrast:[{contrast:[M,P,N]}],"drop-shadow":[{"drop-shadow":[``,`none`,p,rr,Zn]}],"drop-shadow-color":[{"drop-shadow":O()}],grayscale:[{grayscale:[``,M,P,N]}],"hue-rotate":[{"hue-rotate":[M,P,N]}],invert:[{invert:[``,M,P,N]}],saturate:[{saturate:[M,P,N]}],sepia:[{sepia:[``,M,P,N]}],"backdrop-filter":[{"backdrop-filter":[``,`none`,P,N]}],"backdrop-blur":[{"backdrop-blur":ge()}],"backdrop-brightness":[{"backdrop-brightness":[M,P,N]}],"backdrop-contrast":[{"backdrop-contrast":[M,P,N]}],"backdrop-grayscale":[{"backdrop-grayscale":[``,M,P,N]}],"backdrop-hue-rotate":[{"backdrop-hue-rotate":[M,P,N]}],"backdrop-invert":[{"backdrop-invert":[``,M,P,N]}],"backdrop-opacity":[{"backdrop-opacity":[M,P,N]}],"backdrop-saturate":[{"backdrop-saturate":[M,P,N]}],"backdrop-sepia":[{"backdrop-sepia":[``,M,P,N]}],"border-collapse":[{border:[`collapse`,`separate`]}],"border-spacing":[{"border-spacing":C()}],"border-spacing-x":[{"border-spacing-x":C()}],"border-spacing-y":[{"border-spacing-y":C()}],"table-layout":[{table:[`auto`,`fixed`]}],caption:[{caption:[`top`,`bottom`]}],transition:[{transition:[``,`all`,`colors`,`opacity`,`shadow`,`transform`,`none`,P,N]}],"transition-behavior":[{transition:[`normal`,`discrete`]}],duration:[{duration:[M,`initial`,P,N]}],ease:[{ease:[`linear`,`initial`,_,P,N]}],delay:[{delay:[M,P,N]}],animate:[{animate:[`none`,v,P,N]}],backface:[{backface:[`hidden`,`visible`]}],perspective:[{perspective:[h,P,N]}],"perspective-origin":[{"perspective-origin":x()}],rotate:[{rotate:_e()}],"rotate-x":[{"rotate-x":_e()}],"rotate-y":[{"rotate-y":_e()}],"rotate-z":[{"rotate-z":_e()}],scale:[{scale:ve()}],"scale-x":[{"scale-x":ve()}],"scale-y":[{"scale-y":ve()}],"scale-z":[{"scale-z":ve()}],"scale-3d":[`scale-3d`],skew:[{skew:ye()}],"skew-x":[{"skew-x":ye()}],"skew-y":[{"skew-y":ye()}],transform:[{transform:[P,N,``,`none`,`gpu`,`cpu`]}],"transform-origin":[{origin:x()}],"transform-style":[{transform:[`3d`,`flat`]}],translate:[{translate:be()}],"translate-x":[{"translate-x":be()}],"translate-y":[{"translate-y":be()}],"translate-z":[{"translate-z":be()}],"translate-none":[`translate-none`],zoom:[{zoom:[Pn,P,N]}],accent:[{accent:O()}],appearance:[{appearance:[`none`,`auto`]}],"caret-color":[{caret:O()}],"color-scheme":[{scheme:[`normal`,`dark`,`light`,`light-dark`,`only-dark`,`only-light`]}],cursor:[{cursor:[`auto`,`default`,`pointer`,`wait`,`text`,`move`,`help`,`not-allowed`,`none`,`context-menu`,`progress`,`cell`,`crosshair`,`vertical-text`,`alias`,`copy`,`no-drop`,`grab`,`grabbing`,`all-scroll`,`col-resize`,`row-resize`,`n-resize`,`e-resize`,`s-resize`,`w-resize`,`ne-resize`,`nw-resize`,`se-resize`,`sw-resize`,`ew-resize`,`ns-resize`,`nesw-resize`,`nwse-resize`,`zoom-in`,`zoom-out`,P,N]}],"field-sizing":[{"field-sizing":[`fixed`,`content`]}],"pointer-events":[{"pointer-events":[`auto`,`none`]}],resize:[{resize:[`none`,``,`y`,`x`]}],"scroll-behavior":[{scroll:[`auto`,`smooth`]}],"scrollbar-thumb-color":[{"scrollbar-thumb":O()}],"scrollbar-track-color":[{"scrollbar-track":O()}],"scrollbar-gutter":[{"scrollbar-gutter":[`auto`,`stable`,`both`]}],"scrollbar-w":[{scrollbar:[`auto`,`thin`,`none`]}],"scroll-m":[{"scroll-m":C()}],"scroll-mx":[{"scroll-mx":C()}],"scroll-my":[{"scroll-my":C()}],"scroll-ms":[{"scroll-ms":C()}],"scroll-me":[{"scroll-me":C()}],"scroll-mbs":[{"scroll-mbs":C()}],"scroll-mbe":[{"scroll-mbe":C()}],"scroll-mt":[{"scroll-mt":C()}],"scroll-mr":[{"scroll-mr":C()}],"scroll-mb":[{"scroll-mb":C()}],"scroll-ml":[{"scroll-ml":C()}],"scroll-p":[{"scroll-p":C()}],"scroll-px":[{"scroll-px":C()}],"scroll-py":[{"scroll-py":C()}],"scroll-ps":[{"scroll-ps":C()}],"scroll-pe":[{"scroll-pe":C()}],"scroll-pbs":[{"scroll-pbs":C()}],"scroll-pbe":[{"scroll-pbe":C()}],"scroll-pt":[{"scroll-pt":C()}],"scroll-pr":[{"scroll-pr":C()}],"scroll-pb":[{"scroll-pb":C()}],"scroll-pl":[{"scroll-pl":C()}],"snap-align":[{snap:[`start`,`end`,`center`,`align-none`]}],"snap-stop":[{snap:[`normal`,`always`]}],"snap-type":[{snap:[`none`,`x`,`y`,`both`]}],"snap-strictness":[{snap:[`mandatory`,`proximity`]}],touch:[{touch:[`auto`,`none`,`manipulation`]}],"touch-x":[{"touch-pan":[`x`,`left`,`right`]}],"touch-y":[{"touch-pan":[`y`,`up`,`down`]}],"touch-pz":[`touch-pinch-zoom`],select:[{select:[`none`,`text`,`all`,`auto`]}],"will-change":[{"will-change":[`auto`,`scroll`,`contents`,`transform`,P,N]}],fill:[{fill:[`none`,...O()]}],"stroke-w":[{stroke:[M,Qn,Gn,Kn]}],stroke:[{stroke:[`none`,...O()]}],"forced-color-adjust":[{"forced-color-adjust":[`auto`,`none`]}]},conflictingClassGroups:{"container-named":[`container-type`],overflow:[`overflow-x`,`overflow-y`],overscroll:[`overscroll-x`,`overscroll-y`],inset:[`inset-x`,`inset-y`,`inset-bs`,`inset-be`,`start`,`end`,`top`,`right`,`bottom`,`left`],"inset-x":[`right`,`left`],"inset-y":[`top`,`bottom`],flex:[`basis`,`grow`,`shrink`],gap:[`gap-x`,`gap-y`],p:[`px`,`py`,`ps`,`pe`,`pbs`,`pbe`,`pt`,`pr`,`pb`,`pl`],px:[`pr`,`pl`],py:[`pt`,`pb`],m:[`mx`,`my`,`ms`,`me`,`mbs`,`mbe`,`mt`,`mr`,`mb`,`ml`],mx:[`mr`,`ml`],my:[`mt`,`mb`],size:[`w`,`h`],"font-size":[`leading`],"fvn-normal":[`fvn-ordinal`,`fvn-slashed-zero`,`fvn-figure`,`fvn-spacing`,`fvn-fraction`],"fvn-ordinal":[`fvn-normal`],"fvn-slashed-zero":[`fvn-normal`],"fvn-figure":[`fvn-normal`],"fvn-spacing":[`fvn-normal`],"fvn-fraction":[`fvn-normal`],"line-clamp":[`display`,`overflow`],rounded:[`rounded-s`,`rounded-e`,`rounded-t`,`rounded-r`,`rounded-b`,`rounded-l`,`rounded-ss`,`rounded-se`,`rounded-ee`,`rounded-es`,`rounded-tl`,`rounded-tr`,`rounded-br`,`rounded-bl`],"rounded-s":[`rounded-ss`,`rounded-es`],"rounded-e":[`rounded-se`,`rounded-ee`],"rounded-t":[`rounded-tl`,`rounded-tr`],"rounded-r":[`rounded-tr`,`rounded-br`],"rounded-b":[`rounded-br`,`rounded-bl`],"rounded-l":[`rounded-tl`,`rounded-bl`],"border-spacing":[`border-spacing-x`,`border-spacing-y`],"border-w":[`border-w-x`,`border-w-y`,`border-w-s`,`border-w-e`,`border-w-bs`,`border-w-be`,`border-w-t`,`border-w-r`,`border-w-b`,`border-w-l`],"border-w-x":[`border-w-r`,`border-w-l`],"border-w-y":[`border-w-t`,`border-w-b`],"border-color":[`border-color-x`,`border-color-y`,`border-color-s`,`border-color-e`,`border-color-bs`,`border-color-be`,`border-color-t`,`border-color-r`,`border-color-b`,`border-color-l`],"border-color-x":[`border-color-r`,`border-color-l`],"border-color-y":[`border-color-t`,`border-color-b`],translate:[`translate-x`,`translate-y`,`translate-none`],"translate-none":[`translate`,`translate-x`,`translate-y`,`translate-z`],"scroll-m":[`scroll-mx`,`scroll-my`,`scroll-ms`,`scroll-me`,`scroll-mbs`,`scroll-mbe`,`scroll-mt`,`scroll-mr`,`scroll-mb`,`scroll-ml`],"scroll-mx":[`scroll-mr`,`scroll-ml`],"scroll-my":[`scroll-mt`,`scroll-mb`],"scroll-p":[`scroll-px`,`scroll-py`,`scroll-ps`,`scroll-pe`,`scroll-pbs`,`scroll-pbe`,`scroll-pt`,`scroll-pr`,`scroll-pb`,`scroll-pl`],"scroll-px":[`scroll-pr`,`scroll-pl`],"scroll-py":[`scroll-pt`,`scroll-pb`],touch:[`touch-x`,`touch-y`,`touch-pz`],"touch-x":[`touch`],"touch-y":[`touch`],"touch-pz":[`touch`]},conflictingClassGroupModifiers:{"font-size":[`leading`]},postfixLookupClassGroups:[`container-type`],orderSensitiveModifiers:[`*`,`**`,`after`,`backdrop`,`before`,`details-content`,`file`,`first-letter`,`first-line`,`marker`,`placeholder`,`selection`]}});function gr(...e){return hr(Ut(e))}var _r=class extends _{constructor(...e){super(...e),this.icon=``,this.name=``}static{this.styles=y`
        :host {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
            width: 1em;
            height: 1em;
            line-height: 1;
            vertical-align: -0.125em;
        }

        svg {
            display: block;
            width: 100%;
            height: 100%;
            fill: currentColor;
        }
    `}render(){let e=w(this.icon||this.name);return e?v`${Te(C(e,{title:this.label}))}`:T}};s([c()],_r.prototype,`icon`,void 0),s([c()],_r.prototype,`name`,void 0),s([c()],_r.prototype,`label`,void 0),_r=s([h(`pk-icon`)],_r);var vr=u({tagName:`pk-icon`,elementClass:_r,react:A.default});function yr(e=`default`){return e===`xxs`||e===`xs`?`xxs`:e===`lg`||e===`xl`?`sm`:`xs`}function br(e=`default`,t){return t||(e===`primary`||e===`secondary`||e===`dashed`||e===`outline`||e===`transparent`?e:`default`)}var xr=[l,he,Re(),Le(`.button`),Ie(),ze(`.button`),y`
        @layer pk-component {
            :host {
                font-family: var(--pk-font-family);
                cursor: pointer;
                --pk-btn-height: var(--pk-btn-height-default);
                --pk-btn-font: var(--pk-btn-font-default);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-default);
                --pk-btn-icon-size: var(--pk-btn-icon-size-default);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-default);
                --pk-btn-caret-size: var(--pk-btn-caret-size-default);
                --pk-btn-radius: var(--pk-btn-radius-default);
                /*
                 * Slotted labels inherit from the host — pin the size-token font
                 * (and button line-height) so Craft CP / Tailwind hosts match.
                 */
                font-size: var(--pk-btn-font);
                line-height: 1.2;
            }

            :host([disabled]) {
                cursor: not-allowed;
                pointer-events: none;
            }

            :host([loading]):not([disabled]) {
                pointer-events: none;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: var(--pk-btn-icon-gap);
                box-sizing: border-box;
                width: auto;
                margin: 0;
                /* Every button carries a 1px border (transparent for fill/plain variants) so the box
                 * model is identical across variants and states. Prevents width shift when swapping a
                 * button between filled and outline/dashed, or toggling states. Matches Bootstrap
                 * (transparent baseline) and  (border always present, only color changes).
                 */
                border: 1px solid transparent;
                border-radius: var(--pk-btn-radius);
                font: inherit;
                font-size: var(--pk-btn-font);
                font-weight: 400;
                line-height: 1.2;
                text-decoration: none;
                white-space: nowrap;
                /* Inherit host cursor so className/style (e.g. cursor-move) pierce shadow. */
                cursor: inherit;
                user-select: none;
                vertical-align: middle;
                appearance: none;
                background: var(--pk-btn-fill, var(--pk-action-fill));
                color: var(--pk-btn-on, var(--pk-action-on));
                height: var(--pk-btn-height);
                min-height: var(--pk-btn-height);
                /* Block padding defaults to 0 (height tokens center content). Override for nav rows. */
                padding-block: var(--pk-btn-padding-block, 0);
                padding-inline: var(--pk-btn-padding-inline);
                transition: background-color 0.12s ease, box-shadow 0.12s ease, color 0.12s ease;
            }

            .button:disabled {
                opacity: 0.5;
            }

            .icon-slot {
                display: none;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                line-height: 0;
            }

            .icon-slot--has-content {
                display: inline-flex;
            }

            /* Fixed token sizes for all icons (labeled or icon-only) — matches plugin-kit-react Button. */
            .icon-slot slot::slotted(svg),
            slot[name='start']::slotted(svg),
            slot[name='end']::slotted(svg) {
                display: block;
                width: var(--pk-btn-icon-size);
                height: var(--pk-btn-icon-size);
                flex-shrink: 0;
                pointer-events: none;
            }

            .icon-slot slot::slotted(img),
            slot[name='start']::slotted(img),
            slot[name='end']::slotted(img) {
                display: block;
                width: var(--pk-btn-icon-size);
                height: var(--pk-btn-icon-size);
                object-fit: contain;
                flex-shrink: 0;
                pointer-events: none;
            }

            /* pk-icon sizes itself from font-size (1em), so scale it to the
             * icon token. This keeps the idiomatic slotted pk-icon usage in
             * sync with raw slotted svg. Set width/height explicitly — %/size-full
             * collapses when the icon-slot has no definite box.
             */
            .icon-slot slot::slotted(pk-icon),
            slot[name='start']::slotted(pk-icon),
            slot[name='end']::slotted(pk-icon) {
                font-size: var(--pk-btn-icon-size);
                width: var(--pk-btn-icon-size);
                height: var(--pk-btn-icon-size);
                /* Kill pk-icon's text-baseline nudge (-0.125em) — flex slots center optically. */
                vertical-align: 0;
                flex-shrink: 0;
                pointer-events: none;
            }

            .label {
                display: inline-flex;
                align-items: center;
                min-width: 0;
                line-height: 1.2;
            }

            /* Trailing slot (status): grow + clip the label so end sits at the far edge
             * and long titles truncate instead of colliding with the indicator.
             */
            .button:has(.icon-slot--end.icon-slot--has-content) .label:not(.is-empty) {
                flex: 1 1 auto;
                overflow: hidden;
            }

            .label.is-empty {
                display: none;
            }

            /* Icon-only (no label): square hit box = size height. Button owns the target;
             * glyph size comes from --pk-btn-icon-size. Do not Tailwind-size the Icon.
             * Opt out with icon (compact), size=none, or group-trigger (narrow disclosure cap).
             */
            :host(:not([icon]):not([size='none']):not([group-trigger])) .button:not(.has-label) {
                width: var(--pk-btn-height);
                min-width: var(--pk-btn-height);
                padding-inline: 0;
            }

            /* Compact density (icon attr): padless box that hugs the glyph.
             * size still drives --pk-btn-icon-size; height/width tiers do not apply.
             * Use for dense x / ellipsis in cells — not for table action rows (prefer square above).
             * line-height: 0 collapses whitespace flex-struts so the glyph sits dead-center.
             */
            :host([icon]) {
                display: inline-flex;
                line-height: 0;
                vertical-align: middle;
            }

            :host([icon]) .button {
                display: flex;
                width: auto;
                min-width: 0;
                height: auto;
                min-height: 0;
                padding-inline: 0.25rem;
                padding-block: 0;
                line-height: 0;
                align-items: center;
                justify-content: center;
            }

            /* Keep label space while loading even before slotchange runs. */
            .button.loading .label.is-empty {
                display: inline-flex;
                visibility: hidden;
            }

            /* Sizes — token-driven scale (see tokens.css) */
            :host([size='xxs']) {
                --pk-btn-height: var(--pk-btn-height-xxs);
                --pk-btn-font: var(--pk-btn-font-xxs);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-xxs);
                --pk-btn-icon-size: var(--pk-btn-icon-size-xxs);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-xxs);
                --pk-btn-caret-size: var(--pk-btn-caret-size-xxs);
                --pk-btn-radius: var(--pk-btn-radius-xxs);
            }

            :host([size='xs']) {
                --pk-btn-height: var(--pk-btn-height-xs);
                --pk-btn-font: var(--pk-btn-font-xs);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-xs);
                --pk-btn-icon-size: var(--pk-btn-icon-size-xs);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-xs);
                --pk-btn-caret-size: var(--pk-btn-caret-size-xs);
                --pk-btn-radius: var(--pk-btn-radius-xs);
            }

            :host([size='sm']) {
                --pk-btn-height: var(--pk-btn-height-sm);
                --pk-btn-font: var(--pk-btn-font-sm);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-sm);
                --pk-btn-icon-size: var(--pk-btn-icon-size-sm);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-sm);
                --pk-btn-caret-size: var(--pk-btn-caret-size-sm);
                --pk-btn-radius: var(--pk-btn-radius-sm);
            }

            :host([size='default']) {
                --pk-btn-height: var(--pk-btn-height-default);
                --pk-btn-font: var(--pk-btn-font-default);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-default);
                --pk-btn-icon-size: var(--pk-btn-icon-size-default);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-default);
                --pk-btn-caret-size: var(--pk-btn-caret-size-default);
                --pk-btn-radius: var(--pk-btn-radius-default);
            }

            :host([size='lg']) {
                --pk-btn-height: var(--pk-btn-height-lg);
                --pk-btn-font: var(--pk-btn-font-lg);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-lg);
                --pk-btn-icon-size: var(--pk-btn-icon-size-lg);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-lg);
                --pk-btn-caret-size: var(--pk-btn-caret-size-lg);
                --pk-btn-radius: var(--pk-btn-radius-lg);
            }

            :host([size='xl']) {
                --pk-btn-height: var(--pk-btn-height-xl);
                --pk-btn-font: var(--pk-btn-font-xl);
                --pk-btn-padding-inline: var(--pk-btn-padding-inline-xl);
                --pk-btn-icon-size: var(--pk-btn-icon-size-xl);
                --pk-btn-icon-gap: var(--pk-btn-icon-gap-xl);
                --pk-btn-caret-size: var(--pk-btn-caret-size-xl);
                --pk-btn-radius: var(--pk-btn-radius-xl);
            }

            /* No preset scale — size to content or set --pk-btn-* on the host for one-off dimensions
             * (height, padding, font, icon, radius) without fighting a named size tier.
             * Pair with icon for a padless glyph host, or set --pk-btn-padding-inline / --pk-btn-height yourself.
             */
            :host([size='none']) {
                --pk-btn-height: auto;
                --pk-btn-font: inherit;
                --pk-btn-padding-inline: 0px;
                --pk-btn-padding-block: 0px;
                --pk-btn-icon-size: 1em;
                --pk-btn-icon-gap: 0px;
                --pk-btn-caret-size: 1em;
                --pk-btn-radius: 0px;
            }

            :host([size='none']) .button {
                height: auto;
                min-height: auto;
                width: 100%;
            }

            /* Variants */
            :host([variant='default']) {
                --pk-btn-fill: var(--pk-action-fill);
                --pk-btn-fill-hover: var(--pk-action-fill-hover);
                --pk-btn-fill-active: var(--pk-action-fill-active);
                --pk-btn-on: var(--pk-action-on);
            }

            :host([variant='primary']) {
                --pk-btn-fill: var(--pk-action-primary-fill);
                --pk-btn-fill-hover: var(--pk-action-primary-fill-hover);
                --pk-btn-fill-active: var(--pk-action-primary-fill-active);
                --pk-btn-on: var(--pk-action-primary-on);
            }

            :host([variant='primary']) .button,
            :host([variant='secondary']) .button {
                -moz-osx-font-smoothing: grayscale;
                -webkit-font-smoothing: antialiased;
            }

            :host([variant='secondary']) {
                --pk-btn-fill: var(--pk-color-gray-500);
                --pk-btn-fill-hover: var(--pk-color-gray-550);
                --pk-btn-fill-active: var(--pk-color-gray-600);
                --pk-btn-on: var(--pk-color-white);
            }

            :host([variant='outline']) .button {
                background: transparent;
                border-color: var(--pk-color-slate-400);
                color: var(--pk-color-gray-700);
            }

            :host([variant='transparent']) .button {
                background: transparent;
                color: var(--pk-color-gray-700);
            }

            /* link/none opt out of the shared transparent 1px border: they never render a border, so
             * carrying one only pads the box by 2px inline (and 2px block at size='none', where height
             * is auto). These are the "inline text" / "no chrome" variants — content-sized is the point,
             * and neither participates in button-group border joins. Other variants keep the stable box.
             */
            /*
             * Craft CP sets --link-color on :root (inherits into shadow). Prefer that,
             * then kit --pk-color-link — not sky-700 (reads as a different “CP blue”).
             * Color on :host so consumer utilities (e.g. text-[var(--link-color)]) can override.
             * Height must be content-sized — default --pk-btn-height (34px) bloated table rows.
             */
            :host([variant='link']) {
                color: var(--link-color, var(--pk-color-link));
                --pk-btn-height: auto;
                --pk-btn-padding-inline: 0;
                --pk-btn-padding-block: 0;
            }

            :host([variant='link']) .button {
                background: transparent;
                border-width: 0;
                border-radius: 0;
                color: inherit;
                width: auto;
                height: auto;
                min-height: 0;
                padding: 0;
                text-underline-offset: 2px;
            }

            :host([variant='dashed']) .button {
                background: transparent;
                border-style: dashed;
                border-color: var(--pk-color-slate-500);
                color: var(--pk-color-gray-700);
            }

            :host([variant='none']) .button {
                border-width: 0;
                border-radius: 0;
                background: transparent;
                color: inherit;
            }

            /* Interaction — pseudo-classes only; playground matrices use dev/pk-button-demo-states.css */
            .button:hover:not(:disabled) {
                background: var(--pk-btn-fill-hover, var(--pk-btn-fill));
            }

            :host([variant='outline']) .button:hover:not(:disabled),
            :host([variant='transparent']) .button:hover:not(:disabled),
            :host([variant='dashed']) .button:hover:not(:disabled) {
                background: var(--pk-color-slate-150);
            }

            :host([variant='link']) .button:hover:not(:disabled) {
                background: transparent;
                text-decoration: underline;
            }

            :host([variant='none']) .button:hover:not(:disabled) {
                background: transparent;
            }

            .button:active:not(:disabled) {
                background: var(--pk-btn-fill-active, var(--pk-btn-fill-hover, var(--pk-btn-fill)));
            }

            :host([variant='outline']) .button:active:not(:disabled),
            :host([variant='transparent']) .button:active:not(:disabled),
            :host([variant='dashed']) .button:active:not(:disabled) {
                background: var(--pk-color-slate-200);
            }

            :host([variant='link']) .button:active:not(:disabled),
            :host([variant='none']) .button:active:not(:disabled) {
                background: transparent;
            }

            .button:focus {
                outline: none;
            }

            .button:focus-visible {
                box-shadow: var(--pk-shadow-focus);
            }

            /* Bordered variants: fold the button's own border into the focus ring by recoloring it to
             * the accent (and solidifying dashed) so focus reads as one cohesive ring instead of a
             * doubled border. The ring is thinned to 1px here because the recolored 1px border already
             * supplies the other half — total 2px, matching the filled variants' ring weight.
             */
            :host([variant='outline']) .button:focus-visible,
            :host([variant='dashed']) .button:focus-visible {
                border-color: var(--pk-color-sky-600);
                box-shadow: 0 0 0 1px var(--pk-color-sky-600), 0 0 5px 1px hsl(from var(--pk-color-sky-600) h s l / 0.7);
            }

            :host([variant='dashed']) .button:focus-visible {
                border-style: solid;
            }

            :host(.pk-dialog__close) .button:focus-visible {
                box-shadow: 0 0 0 2px var(--pk-color-gray-600);
            }

            :host-context(pk-button-group) {
                position: relative;
            }

            :host-context(pk-button-group[orientation='vertical']) {
                display: block;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            :host-context(pk-button-group[orientation='vertical']) .button {
                width: 100%;
                box-sizing: border-box;
            }

            :host-context(pk-button-group:focus-visible) {
                z-index: 2;
            }

            /* Bordered variants — matching border divider (filled uses margin gap via buttonGroupIndentStyles) */

            :host([variant='primary']) .button:focus-visible,
            :host([variant='secondary']) .button:focus-visible {
                box-shadow: var(--pk-shadow-focus-inset);
            }

            :host-context(pk-button-group[exclusive]):host([aria-pressed='true']) .button {
                background: var(--pk-color-gray-500);
                color: var(--pk-color-white);
            }

            :host-context(pk-button-group[exclusive]):host([aria-pressed='true']) .button:hover:not(:disabled) {
                background: var(--pk-color-gray-550);
            }

            :host-context(pk-button-group[exclusive]):host([aria-pressed='true']) .button:active:not(:disabled) {
                background: var(--pk-color-gray-600);
            }

            :host-context(pk-button-group[exclusive]):host([aria-pressed='true']) .button:focus-visible {
                box-shadow: var(--pk-shadow-focus);
            }

            :host([variant='link']) .button:focus-visible {
                box-shadow: none;
                text-decoration: underline;
            }

            .button.loading {
                position: relative;
                cursor: default;
                pointer-events: none;
            }

            .label.loading {
                visibility: hidden;
            }

            .button.loading .icon-slot,
            .button.loading slot[name='start']::slotted(*),
            .button.loading slot[name='end']::slotted(*) {
                visibility: hidden;
            }

            .button.caret .icon-slot--end.icon-slot--has-content {
                display: none;
            }

            /* Scope to the caret span — the button host also gets class caret when
               with-caret is set; an unscoped .caret rule was adding 2px margin
               to the whole button and shifting dropdown anchors left. */
            .button > .caret {
                display: inline-flex;
                align-self: center;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                line-height: 0;
                /* Sits slightly further from the label than the flex gap alone. */
                margin-inline-start: 2px;
            }

            /* Caret has its own per-size token (--pk-btn-caret-size), kept deliberately smaller than
             * --pk-btn-icon-size so it reads as a subordinate dropdown affordance next to real icons.
             */
            .button > .caret svg {
                display: block;
                width: var(--pk-btn-caret-size);
                height: var(--pk-btn-caret-size);
            }

            :host([group-trigger]) .button {
                padding-inline: 6px;
            }

            /* Compact disclosure cap — hide content, keep only the shared SVG caret (centered). */
            :host([group-trigger]) .label,
            :host([group-trigger]) .icon-slot {
                display: none;
            }

            :host([group-trigger]) .button > .caret {
                margin-inline-start: 0;
            }

            :host([size='sm'][group-trigger]) .button,
            :host([size='xs'][group-trigger]) .button,
            :host([size='xxs'][group-trigger]) .button {
                padding-inline: 6px;
            }

            :host([size='lg'][group-trigger]) .button {
                padding-inline: 10px;
            }

            :host([size='xl'][group-trigger]) .button {
                padding-inline: 12px;
            }

            :host-context(pk-button-group[orientation='horizontal']):host([data-pk-group-join]:not([data-pk-group-divider])[variant='outline']) .button,
            :host-context(pk-button-group[orientation='horizontal']):host([data-pk-group-join]:not([data-pk-group-divider])[variant='dashed']) .button,
            :host-context(pk-button-group[orientation='horizontal']):host([data-pk-group-join]:not([data-pk-group-divider])[variant='transparent']) .button {
                border-left-width: 0;
            }

            :host-context(pk-button-group[orientation='vertical']):host([data-pk-group-join]:not([data-pk-group-divider])[variant='outline']) .button,
            :host-context(pk-button-group[orientation='vertical']):host([data-pk-group-join]:not([data-pk-group-divider])[variant='dashed']) .button,
            :host-context(pk-button-group[orientation='vertical']):host([data-pk-group-join]:not([data-pk-group-divider])[variant='transparent']) .button {
                border-top-width: 0;
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]:not([data-pk-group-divider])[variant='outline']) .button,
            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]:not([data-pk-group-divider])[variant='dashed']) .button,
            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]:not([data-pk-group-divider])[variant='transparent']) .button {
                border-left-width: 0;
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-join]:not([data-pk-group-divider])[variant='outline']) .button,
            :host([data-pk-group-orientation='vertical'][data-pk-group-join]:not([data-pk-group-divider])[variant='dashed']) .button,
            :host([data-pk-group-orientation='vertical'][data-pk-group-join]:not([data-pk-group-divider])[variant='transparent']) .button {
                border-top-width: 0;
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-divider][variant='outline']) .button {
                box-shadow: none;
                border-left-width: 1px;
                border-left-style: solid;
                border-left-color: var(--pk-btn-group-divider-color-outline, var(--pk-color-slate-400));
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-divider][variant='outline']) .button {
                box-shadow: none;
                border-top-width: 1px;
                border-top-style: solid;
                border-top-color: var(--pk-btn-group-divider-color-outline, var(--pk-color-slate-400));
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-divider][variant='dashed']) .button {
                box-shadow: none;
                border-left-width: 1px;
                border-left-style: dashed;
                border-left-color: var(--pk-btn-group-divider-color-dashed, var(--pk-color-slate-500));
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-divider][variant='dashed']) .button {
                box-shadow: none;
                border-top-width: 1px;
                border-top-style: dashed;
                border-top-color: var(--pk-btn-group-divider-color-dashed, var(--pk-color-slate-500));
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-divider][variant='outline']) .button:focus-visible,
            :host([data-pk-group-orientation='horizontal'][data-pk-group-divider][variant='dashed']) .button:focus-visible {
                box-shadow: var(--pk-shadow-focus);
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-divider][variant='outline']) .button:focus-visible,
            :host([data-pk-group-orientation='vertical'][data-pk-group-divider][variant='dashed']) .button:focus-visible {
                box-shadow: var(--pk-shadow-focus);
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-internal-trail][variant='outline']) .button,
            :host([data-pk-group-orientation='horizontal'][data-pk-group-internal-trail][variant='dashed']) .button {
                border-right-width: 0;
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-internal-trail][variant='outline']) .button,
            :host([data-pk-group-orientation='vertical'][data-pk-group-internal-trail][variant='dashed']) .button {
                border-bottom-width: 0;
            }
        }
    `],Sr=C(de),Cr=class extends _{constructor(...e){super(...e),this.variant=`default`,this.size=`default`,this.disabled=!1,this.loading=!1,this.withCaret=!1,this.groupTrigger=!1,this.icon=!1,this.title=``,this.type=`button`,this.hasDefaultSlotContent=!1,this.hasStartSlotContent=!1,this.hasEndSlotContent=!1,this.startSlotChanged=e=>{this.iconSlotChanged(e,`start`)},this.endSlotChanged=e=>{this.iconSlotChanged(e,`end`)},this.handleHostClick=e=>{if(this.disabled||this.loading||this.href||this.type!==`submit`&&this.type!==`reset`)return;let t=this.resolveAssociatedForm();if(t){if(e.preventDefault(),e.stopPropagation(),this.type===`reset`){t.reset();return}if(typeof t.requestSubmit==`function`){t.requestSubmit();return}t.dispatchEvent(new Event(`submit`,{bubbles:!0,cancelable:!0}))}}}static{this.shadowRootOptions={mode:`open`,delegatesFocus:!0}}static{this.styles=xr}defaultSlotChanged(e){let t=e.target;this.hasDefaultSlotContent=t.assignedNodes({flatten:!0}).some(e=>e.nodeType===Node.TEXT_NODE?e.textContent?.trim():e.nodeType===Node.ELEMENT_NODE)}iconSlotChanged(e,t){let n=e.target.assignedNodes({flatten:!0}).some(e=>e.nodeType===Node.TEXT_NODE?e.textContent?.trim():e.nodeType===Node.ELEMENT_NODE);t===`start`?this.hasStartSlotContent=n:this.hasEndSlotContent=n}buttonClasses(){return ae({button:!0,"has-label":this.hasDefaultSlotContent,loading:this.loading,caret:this.withCaret,"group-trigger":this.groupTrigger})}connectedCallback(){super.connectedCallback(),this.setAttribute(`data-slot`,`button`),this.addEventListener(`click`,this.handleHostClick)}disconnectedCallback(){this.removeEventListener(`click`,this.handleHostClick),super.disconnectedCallback()}resolveAssociatedForm(){let e=(this.form||this.getAttribute(`form`)||``).trim();if(e){let t=this.ownerDocument?.getElementById(e);if(t instanceof HTMLFormElement&&t.id!==`main`)return t}let t=this.closest(`form`);return t&&t.id!==`main`?t:null}render(){let e=this.spinnerSize||yr(this.size),t=br(this.variant,this.spinnerVariant);return v`
            ${this.href?v`
                    <a
                        part="base"
                        class=${this.buttonClasses()}
                        href=${this.href}
                        target=${this.target??T}
                        rel=${this.rel??T}
                        title=${this.title||T}
                    >
                        ${this.renderInner(e,t)}
                    </a>
                `:v`
                    <button
                        part="base"
                        class=${this.buttonClasses()}
                        type=${this.type}
                        ?disabled=${this.disabled}
                        aria-disabled=${this.disabled?`true`:T}
                        aria-busy=${this.loading?`true`:T}
                        name=${this.name??T}
                        value=${this.value??T}
                        title=${this.title||T}
                    >
                        ${this.renderInner(e,t)}
                    </button>
                `}
        `}renderInner(e,t){return v`
            <span
                class=${ae({"icon-slot":!0,"icon-slot--start":!0,"icon-slot--has-content":this.hasStartSlotContent})}
            >
                <slot name="start" @slotchange=${this.startSlotChanged}></slot>
            </span>
            ${this.loading?v`
                    <pk-spinner
                        variant=${t}
                        size=${e}
                        tone=${this.spinnerTone??T}
                        centered
                    ></pk-spinner>
                `:T}
            <span
                class=${ae({label:!0,"is-empty":!this.hasDefaultSlotContent,loading:this.loading})}
            >
                <slot @slotchange=${this.defaultSlotChanged}></slot>
            </span>
            <span
                class=${ae({"icon-slot":!0,"icon-slot--end":!0,"icon-slot--has-content":this.hasEndSlotContent})}
            >
                <slot name="end" @slotchange=${this.endSlotChanged}></slot>
            </span>
            ${this.withCaret||this.groupTrigger?v`<span part="caret" class="caret">${Te(Sr)}</span>`:T}
        `}};s([c({reflect:!0})],Cr.prototype,`variant`,void 0),s([c({reflect:!0})],Cr.prototype,`size`,void 0),s([c({type:Boolean,reflect:!0})],Cr.prototype,`disabled`,void 0),s([c({type:Boolean,reflect:!0})],Cr.prototype,`loading`,void 0),s([c({reflect:!0,attribute:`spinner-size`})],Cr.prototype,`spinnerSize`,void 0),s([c({reflect:!0,attribute:`spinner-variant`})],Cr.prototype,`spinnerVariant`,void 0),s([c({reflect:!0,attribute:`spinner-tone`})],Cr.prototype,`spinnerTone`,void 0),s([c({type:Boolean,reflect:!0,attribute:`with-caret`})],Cr.prototype,`withCaret`,void 0),s([c({type:Boolean,reflect:!0,attribute:`group-trigger`})],Cr.prototype,`groupTrigger`,void 0),s([c({type:Boolean,reflect:!0})],Cr.prototype,`icon`,void 0),s([c()],Cr.prototype,`href`,void 0),s([c()],Cr.prototype,`target`,void 0),s([c()],Cr.prototype,`rel`,void 0),s([c()],Cr.prototype,`name`,void 0),s([c()],Cr.prototype,`value`,void 0),s([c()],Cr.prototype,`title`,void 0),s([c()],Cr.prototype,`type`,void 0),s([c({reflect:!0})],Cr.prototype,`form`,void 0),s([g()],Cr.prototype,`hasDefaultSlotContent`,void 0),s([g()],Cr.prototype,`hasStartSlotContent`,void 0),s([g()],Cr.prototype,`hasEndSlotContent`,void 0),Cr=s([h(`pk-button`)],Cr);var wr=u({tagName:`pk-button`,elementClass:Cr,react:A.default,events:{onPkClick:`click`}});function Tr({children:e,variant:t=`default`,size:n=`default`,icon:r,groupTrigger:i,withCaret:a,loading:o=!1,disabled:s=!1,type:c=`button`,spinnerSize:l,spinnerVariant:u,spinnerTone:d,"data-state":f,...p}){return(0,j.jsx)(wr,{variant:t,size:n,loading:o,disabled:s,type:c,...ce([`icon`,`groupTrigger`,`withCaret`],{icon:r,groupTrigger:i,withCaret:a}),...l?{"spinner-size":l}:{},...u?{"spinner-variant":u}:{},...d?{"spinner-tone":d}:{},...f?{"data-state":f}:{},...p,children:e})}var Er={empty:{icon:`empty-set`,iconColor:`text-slate-500`,iconContainer:`bg-slate-200/55`,titleClassName:`text-base font-medium text-gray-900`,messageClassName:`text-sm text-gray-500`},error:{icon:`triangle-exclamation`,iconColor:`text-rose-600`,iconContainer:`bg-rose-500/12`,titleClassName:`text-base font-medium text-gray-900`,messageClassName:`text-sm text-gray-500`},success:{icon:`circle-check`,iconColor:`text-emerald-600`,iconContainer:`bg-slate-100`,titleClassName:`text-base font-medium text-gray-900`,messageClassName:`text-sm text-gray-500`},info:{icon:`circle-info`,iconColor:`text-sky-600`,iconContainer:`bg-slate-100`,titleClassName:`text-base font-medium text-gray-900`,messageClassName:`text-sm text-gray-500`}};function Dr({variant:e=`empty`,icon:t=null,title:n=null,message:r=null,primaryAction:i=null,secondaryAction:a=null,children:o=null,containerClassName:s=`flex flex-1 items-center justify-center py-12`,contentClassName:c=`flex w-[90%] max-w-[560px] flex-col items-center text-center`,titleClassName:l=``,messageClassName:u=``,showIcon:d=!0}){let f=Er[e]||Er.empty,p=t||f.icon;return(0,j.jsx)(`div`,{className:s,children:(0,j.jsxs)(`div`,{className:c,children:[d&&p?(0,j.jsx)(`div`,{className:gr(`mb-3 flex size-10 items-center justify-center rounded-[10px]`,f.iconContainer),children:(0,j.jsx)(vr,{icon:p,className:gr(`size-5`,f.iconColor)})}):null,n?(0,j.jsx)(`h2`,{className:gr(`mb-2`,f.titleClassName,l),children:n}):null,r?(0,j.jsx)(`p`,{className:gr(`mb-4 max-w-[560px]`,f.messageClassName,u),children:r}):null,o,i||a?(0,j.jsxs)(`div`,{className:`mt-2 flex items-center justify-center gap-2`,children:[a?.label&&a?.onClick?(0,j.jsx)(Tr,{type:`button`,variant:a.variant||`secondary`,onClick:a.onClick,children:a.label}):null,i?.label&&i?.onClick?(0,j.jsx)(Tr,{type:`button`,variant:i.variant||`primary`,onClick:i.onClick,children:i.label}):null]}):null]})})}function Or({error:e=null,title:t=null,message:n=null,detailsLabel:r=null,actionLabel:i=null,onAction:a=null,showDetails:o=!0,containerClassName:s=`flex flex-1 items-center justify-center py-12`,contentClassName:c=`flex w-[90%] max-w-[560px] flex-col items-center text-center`}){let l=e?ct(e):null,u=t||l?.heading||`Something went wrong`,d=l?.text||n||`An error has occurred.`,f=r||`Show error details`,p=l?.traceAsString||``;return(0,j.jsx)(Dr,{variant:`error`,title:u,message:d,containerClassName:s,contentClassName:c,primaryAction:i&&a?{label:i,onClick:a,variant:`primary`}:null,children:o&&p?(0,j.jsxs)(`details`,{className:`mb-4 w-full text-center text-xs text-rose-600`,children:[(0,j.jsx)(`summary`,{className:`cursor-pointer`,children:f}),(0,j.jsxs)(`div`,{className:`mt-2 whitespace-pre-wrap text-left`,children:[(0,j.jsxs)(`p`,{className:`mb-2`,children:[l?.heading,`: `,l?.text]}),(0,j.jsx)(`div`,{dangerouslySetInnerHTML:{__html:p}})]})]}):null})}var kr=class extends A.Component{state={hasError:!1,error:null};static getDerivedStateFromError(){return{hasError:!0}}componentDidCatch(e,t){this.setState({error:e}),console.error(this.props.consoleLabel||`React app crashed:`,e,t)}render(){if(!this.state.hasError)return this.props.children;let{title:e,message:t,detailsLabel:n,reloadLabel:r,containerClassName:i=`flex flex-1 items-center justify-center py-12`,contentClassName:a=`flex flex-col items-center justify-center text-center`}=this.props;return(0,j.jsx)(Or,{error:this.state.error,title:e,message:t,detailsLabel:n,actionLabel:r,onAction:()=>{window.location.reload()},containerClassName:i,contentClassName:a})}};function Ar({children:e}){return(0,j.jsx)(kr,{consoleLabel:`Navigation builder crashed:`,title:Craft.t(`navigation`,`Something went wrong`),message:Craft.t(`navigation`,`The navigation builder failed to load. Please refresh the page or try again.`),detailsLabel:Craft.t(`navigation`,`Show error details`),reloadLabel:Craft.t(`navigation`,`Reload`),containerClassName:`flex flex-1 items-center justify-center py-12`,children:e})}var jr=e=>{let t,n=new Set,r=(e,r)=>{let i=typeof e==`function`?e(t):e;if(!Object.is(i,t)){let e=t;t=r??(typeof i!=`object`||!i)?i:Object.assign({},t,i),n.forEach(n=>n(t,e))}},i=()=>t,a={setState:r,getState:i,getInitialState:()=>o,subscribe:e=>(n.add(e),()=>n.delete(e))},o=t=e(r,i,a);return a},Mr=(e=>e?jr(e):jr),Nr=e=>e;function Pr(e,t=Nr){let n=A.useSyncExternalStore(e.subscribe,A.useCallback(()=>t(e.getState()),[e,t]),A.useCallback(()=>t(e.getInitialState()),[e,t]));return A.useDebugValue(n),n}var Fr=e=>{let t=Mr(e),n=e=>Pr(t,e);return Object.assign(n,t),n},Ir=(e=>e?Fr(e):Fr);function Lr(){return window.Craft}function Rr(){return window.$}async function zr(e,t){return(await Lr().sendActionRequest(`POST`,`navigation/builder/get-state`,{data:{menuId:e,siteId:t}})).data}async function Br(e,t,n){return(await Lr().sendActionRequest(`POST`,`navigation/builder/save-draft`,{data:{menuId:e,siteId:t,structureMoves:n}})).data}async function Vr(e,t,n,r){return(await Lr().sendActionRequest(`POST`,`navigation/build-sessions/publish`,{data:{menuId:e,siteId:t,applyStructure:n,moves:r}})).data}async function Hr(e,t){return(await Lr().sendActionRequest(`POST`,`navigation/build-sessions/discard`,{data:{menuId:e,siteId:t}})).data}async function Ur(e,t,n,r=!1){return(await Lr().sendActionRequest(`POST`,`navigation/builder/stage-delete`,{data:{menuId:e,siteId:t,nodeId:n,withDescendants:r}})).data}async function Wr(e,t,n,r){return(await Lr().sendActionRequest(`POST`,`navigation/builder/set-node-status`,{data:{menuId:e,siteId:t,nodeIds:n,status:r}})).data}async function Gr(e,t,n,r=!1){return(await Lr().sendActionRequest(`POST`,`navigation/builder/duplicate-nodes`,{data:{menuId:e,siteId:t,nodeIds:n,deep:r}})).data}async function Kr(e,t,n){return(await Lr().sendActionRequest(`POST`,`navigation/build-sessions/unstage-delete`,{data:{menuId:e,siteId:t,nodeId:n}})).data}async function qr(e,t,n,r,i=!1,a=!1){return(await Lr().sendActionRequest(`POST`,`navigation/nodes/copy-to-site`,{data:{menuId:e,sourceSiteId:t,nodeIds:n,siteId:r,deep:i,remapLinkedElements:a}})).data}async function Jr(e){return(await Lr().sendActionRequest(`POST`,`navigation/nodes/add-nodes`,{data:{nodes:e}})).data}function Yr(e){let t=e?.response;t?.data?.message?Lr().cp.displayError(t.data.message):Lr().cp.displayError()}function F(e,t){return Lr().t(`navigation`,e,t)}var Xr=(...e)=>hr(Ut(e));function Zr(e){return Math.max(0,e-1)*20}function Qr(e){return e?`grid grid-cols-[2.5rem_minmax(0,1fr)_8rem_2.25rem]`:`grid grid-cols-[2.5rem_minmax(0,1fr)_2.25rem]`}function $r(e,t){return Xr(Qr(e),t)}var ei=`__navigation_root__`;function ti(e){let t={[ei]:[]};for(let n of e){let e=n.parentId===null?ei:String(n.parentId);t[e]||(t[e]=[]),t[e].push(String(n.id));let r=String(n.id);t[r]||(t[r]=[])}return t}function ni(e,t,n=ei,r=0){let i=t[n]??[],a=[];for(let o of i){let i=Number(o),s=e.get(i);if(!s)continue;let c=r+1,l=n===`__navigation_root__`?null:Number(n);a.push({...s,level:c,parentId:l}),a.push(...ni(e,t,o,c))}return a}function ri(e){let t=new Map;for(let n of e)n.parentId!==null&&t.set(n.parentId,(t.get(n.parentId)??0)+1);return e.map(e=>({...e,hasDescendants:(t.get(e.id)??0)>0}))}function ii(e,t,n){let r=new Map(n.map(e=>[e.id,e])),i=0;return e.map(e=>{if(!t.has(e.id))return e;let a=n[i];return a?.id===e.id?(i+=1,a):r.get(e.id)??e})}function ai(e,t){return e.filter(e=>e.hasDescendants&&!t[e.id]).map(e=>String(e.id))}function oi(e){return Object.fromEntries(Object.entries(e).map(([e,t])=>[e,[...t]]))}function si(e,t){let n=oi(e);for(let e of Object.keys(n))n[e]=n[e].filter(e=>!t.has(e));return n}function ci(e){return ri(ni(new Map(e.map(e=>[e.id,e])),ti(e)))}function li(e,t){switch(t){case`enabled`:return e.enabled&&e.enabledForSite&&!e.pendingDelete;case`disabled`:return!e.pendingDelete&&(!e.enabled||!e.enabledForSite);case`trashed`:return e.pendingDelete;default:return!0}}function ui(e,t){if(t===`all`)return e;let n=new Map(e.map(e=>[e.id,e])),r=new Set;for(let i of e){if(!li(i,t))continue;r.add(i.id);let e=i.parentId;for(;e;)r.add(e),e=n.get(e)?.parentId??null}return e.filter(e=>r.has(e.id))}function di(e){let t=[],n=[];for(let r of e){if(r.pendingDelete)continue;let e=r.level;for(;n.length&&n[n.length-1].level>=e;)n.pop();let i=n.length?n[n.length-1].id:null,a=null;for(let n=t.length-1;n>=0;n--)if(t[n].level===e&&t[n].parentId===i){a=t[n].elementId;break}t.push({elementId:r.id,parentId:i,prevId:a,level:e}),n.push({id:r.id,level:e})}return t.map(({elementId:e,parentId:t,prevId:n})=>({elementId:e,parentId:t,prevId:n}))}function fi(e,t,n){let r=e.parentId;for(;r!==null;){if(r===t)return!0;r=n.get(r)?.parentId??null}return!1}function pi(e,t){let n=e.findIndex(e=>e.id===t);if(n<0)return{start:-1,end:-1};let r=e[n].level,i=new Map(e.map(e=>[e.id,e])),a=n+1;for(;a<e.length;){let n=e[a];if(n.level>r||fi(n,t,i)){a+=1;continue}break}return{start:n,end:a}}function mi(e,t){let n=new Set(e.map(e=>e.id)),r=new Map;r.set(t,0);let i=!0;for(;i;){i=!1;for(let a of e){if(a.id===t)continue;let e=a.parentId;(!e||!n.has(e))&&(e=t);let o=r.get(e);if(o===void 0)continue;let s=o+1;r.get(a.id)!==s&&(r.set(a.id,s),i=!0)}}let a=e[0].level;for(let t of e)r.has(t.id)||r.set(t.id,Math.max(0,t.level-a));return r}function hi(e,t){if(e.length===0)return e;let n=e[0].id,r=mi(e,n);return e.map(e=>({...e,level:t+(r.get(e.id)??0)}))}function gi(e,t){if(e.length===0)return 0;let n=e[0].id,r=mi(e,n);return Math.max(...e.map(e=>r.get(e.id)??0))}function _i(e,t,n,r){let i=t>0?e[t-1]:null,a=t<e.length?e[t]:null,o=a?a.level:1,s=i?i.level+1:1;return r!==null&&(o!==1&&o+n>r&&(o=r-n,o<1&&(o=1)),s+n>r&&(s=r-n,s<o&&(s=o))),{min:o,max:s}}function vi(e,t){return Math.max(t.min,Math.min(t.max,e))}function yi(e,t,n){let{start:r,end:i}=pi(e,t);if(r<0)return null;let a=e.findIndex(e=>e.id===n);if(a<0||a>=r&&a<i)return null;let o=e.slice(r,i),s=o[0].level,c=[...e.slice(0,r),...e.slice(i)],l=c.findIndex(e=>e.id===n);return l<0?null:{remaining:c,insertIndex:l,subtree:o,rootLevel:s,subtreeDepth:gi(o,s)}}function bi(e,t,n,r,i){let a=yi(e,t,n);return a?vi(r,_i(a.remaining,a.insertIndex,a.subtreeDepth,i)):null}function xi(e,t,n,r){let{start:i,end:a}=pi(e,t);if(i<0)return null;let o=e.slice(i,a),s=o[0].level;return vi(n,_i([...e.slice(0,i),...e.slice(a)],i,gi(o,s),r))}function Si(e,t,n,r){let{start:i,end:a}=pi(e,t);if(i<0)return e;let o=e.slice(i,a),s=o[0].level,c=[...e.slice(0,i),...e.slice(a)],l=xi(e,t,n,r);if(l===null||l===s)return e;let u=hi(o,l);return Ei([...c.slice(0,i),...u,...c.slice(i)])}function Ci(e,t,n,r,i){if(t===n)return e;let a=yi(e,t,n);if(!a)return e;let o=bi(e,t,n,r,i);if(o===null)return e;let s=hi(a.subtree,o);return Ei([...a.remaining.slice(0,a.insertIndex),...s,...a.remaining.slice(a.insertIndex)])}function wi(e,t,n){let r=e.findIndex(e=>e.id===t);if(r<=0)return e;let i=e[r-1].level+1;return n!==null&&i>n?e:Si(e,t,i,n)}function Ti(e,t,n){let r=e.findIndex(e=>e.id===t);if(r<=0)return e;let i=e[r];if(i.level<=1)return e;let a=null;for(let t=r-1;t>=0;--t)if(e[t].level===i.level-1){a=e[t];break}if(!a)return e;let{start:o,end:s}=pi(e,t);if(o<0)return e;let c=e.slice(o,s),l=c[0].level,u=a.level,d=pi(e,a.id).end,f=d;o<d&&(f-=s-o);let p=[...e.slice(0,o),...e.slice(s)],m=gi(c,l),h=vi(u,_i(p,f,m,n));if(h!==u)return e;let g=hi(c,h);return Ei([...p.slice(0,f),...g,...p.slice(f)])}function Ei(e){let t=[];return e.map(e=>{for(;t.length&&t[t.length-1].level>=e.level;)t.pop();let n=t.length?t[t.length-1].id:null,r={...e,parentId:n,level:e.level};return t.push({id:e.id,level:e.level}),r})}function Di(e,t){return JSON.stringify(e)===JSON.stringify(t)}function Oi(e){return di(e)}function ki(e,t){if(t.length===0)return e;let n=new Map(e.map(e=>[e.id,{...e}])),r=[];for(let e of t){let t=n.get(e.elementId);t&&(r.push(t),n.delete(e.elementId))}for(let e of n.values())r.push(e);return Ei(r.map(e=>{let n=t.find(t=>t.elementId===e.id);if(!n)return e;let i=n.parentId?r.find(e=>e.id===n.parentId)?.level??0:0;return{...e,parentId:n.parentId,level:i+1}}))}function Ai(e,t,n={}){let r=n.preserveStructure??!0,i=n.duplications??[];if(!r&&i.length===0)return t;if(!r){let e=new Set;return Mi(ji(t,t,i,e),t,e)}let a=new Map(t.map(e=>[e.id,e])),o=new Set,s=[];for(let t of e){let e=a.get(t.id);e&&(s.push({...e,level:t.level,parentId:t.parentId}),o.add(t.id))}return Mi(ji(s,t,i,o),t,o)}function ji(e,t,n,r=new Set(e.map(e=>e.id))){let i=new Map(t.map(e=>[e.id,e])),a=[...e];for(let{sourceId:e,duplicateId:t}of n){if(r.has(t))continue;let n=i.get(t);if(!n)continue;let o=a.findIndex(t=>t.id===e);if(o<0)continue;let s=a[o],{end:c}=pi(a,e);a=[...a.slice(0,c),{...n,level:s.level,parentId:s.parentId},...a.slice(c)],r.add(t)}return a}function Mi(e,t,n){let r=[...e];for(let e of t){if(n.has(e.id))continue;let t=e.parentId;if(!t||!n.has(t))continue;let i=r.findIndex(e=>e.id===t);if(i<0)continue;let a=r[i],{end:o}=pi(r,t);r=[...r.slice(0,o),{...e,level:a.level+1,parentId:a.id},...r.slice(o)],n.add(e.id)}for(let e of t)n.has(e.id)||(r.push(e),n.add(e.id));return ci(Ei(r))}function Ni(e,t,n){let{start:r,end:i}=pi(e,t);if(r<0)return-1;switch(n){case`before`:return r;case`after`:return i;case`inside`:return r+1}}function Pi(e,t,n,r,i,a){let{start:o,end:s}=pi(e,t);if(o<0)return null;let c=e.find(e=>e.id===n);if(!c)return null;let l=e.findIndex(e=>e.id===n);if(n!==t&&l>=o&&l<s)return null;let u=Ni(e,n,r);if(u<0)return null;let d=e.slice(o,s),f=d[0].level,p=[...e.slice(0,o),...e.slice(s)],m=u;u>o&&(m=u-(s-o));let h=gi(d,f),g=_i(p,m,h,a),_=Fi(r===`inside`?c.level+1:i,g,r,c.level);return{insertIndex:m,targetLevel:_,position:r}}function Fi(e,t,n,r){let i=vi(e,t);return n===`inside`&&(i=Math.max(r+1,i),i=vi(i,t)),i}function Ii(e,t,n,r,i,a){if(t===n)return r===`inside`?e:Si(e,t,i,a);let o=Pi(e,t,n,r,i,a);if(!o)return e;let{start:s,end:c}=pi(e,t);if(s<0)return e;let l=e.slice(s,c),u=l[0].level,d=[...e.slice(0,s),...e.slice(c)];if(o.targetLevel===u&&o.insertIndex===s)return e;let f=hi(l,o.targetLevel);return Ei([...d.slice(0,o.insertIndex),...f,...d.slice(o.insertIndex)])}function I(e,t){let n=e.findIndex(e=>e.id===t);if(n<=0)return null;let r=e[n];for(let t=n-1;t>=0&&!(e[t].level<r.level);--t)if(e[t].level===r.level&&e[t].parentId===r.parentId)return e[t];return null}function L(e,t){let n=e.findIndex(e=>e.id===t);if(n<0)return null;let r=e[n],{end:i}=pi(e,t);for(let t=i;t<e.length&&!(e[t].level<r.level);t+=1)if(e[t].level===r.level&&e[t].parentId===r.parentId)return e[t];return null}function Li(e,t,n){let r=e.findIndex(e=>e.id===t);if(r<=0)return!1;let i=e[r],a=e[r-1].level+1;if(n!==null&&a>n)return!1;let{start:o,end:s}=pi(e,t),c=gi(e.slice(o,s),i.level);return!(n!==null&&a+c>n)}function Ri(e,t,n){let r=e.findIndex(e=>e.id===t);if(r<=0)return!1;let i=e[r];if(i.level<=1)return!1;let a=null;for(let t=r-1;t>=0;--t)if(e[t].level===i.level-1){a=e[t];break}if(!a)return!1;let{start:o,end:s}=pi(e,t);if(o<0)return!1;let c=e.slice(o,s),l=c[0].level,u=a.level,d=pi(e,a.id).end,f=d;o<d&&(f-=s-o);let p=[...e.slice(0,o),...e.slice(s)],m=gi(c,l);return vi(u,_i(p,f,m,n))===u}function zi(e,t,n){let r=n===null||n>1;return{canMoveUp:I(e,t)!==null,canMoveDown:L(e,t)!==null,canMoveLeft:r&&Ri(e,t,n),canMoveRight:r&&Li(e,t,n)}}function Bi(e){return e instanceof Element&&!!e.closest(`button, a, input, label, textarea, select, [role="checkbox"], [data-no-row-select]`)}function Vi(e,t,n,r){let{selectedNodeIds:i,lastSelectedNodeId:a}=n,o=r.metaKey||r.ctrlKey;if(r.shiftKey&&a!==null&&t.includes(a)){let n=t.indexOf(a),r=t.indexOf(e);if(n!==-1&&r!==-1){let[i,a]=n<r?[n,r]:[r,n];return{selectedNodeIds:t.slice(i,a+1),lastSelectedNodeId:e}}}if(o||r.toggle){let t=i.includes(e);return{selectedNodeIds:t?i.filter(t=>t!==e):[...i,e],lastSelectedNodeId:t&&i.length===1?null:e}}return i.length===1&&i[0]===e?{selectedNodeIds:[],lastSelectedNodeId:null}:{selectedNodeIds:[e],lastSelectedNodeId:e}}function Hi(e,t){return`navigation.builder.${e}.${t}.collapsedNodes`}function Ui(e){let t=window.Craft;if(t?.getLocalStorage)return t.getLocalStorage(e,{});try{let t=localStorage.getItem(e);return t===null?{}:JSON.parse(t)}catch{return{}}}function Wi(e,t){let n=window.Craft;if(n?.setLocalStorage){n.setLocalStorage(e,t);return}try{localStorage.setItem(e,JSON.stringify(t))}catch{}}function Gi(e,t){if(!e||typeof e!=`object`)return{};let n={};for(let[r,i]of Object.entries(e)){if(!i)continue;let e=Number(r);Number.isFinite(e)&&(t&&!t.has(e)||(n[e]=!0))}return n}function Ki(e,t,n){return!e||!t?{}:Gi(Ui(Hi(e,t)),n)}function qi(e,t,n){let r=Ki(t,n,new Set(e.map(e=>e.id))),i=Hi(t,n);if(Object.keys(Gi(Ui(i))).length>0)return r;if(e.length<=100)return{};let a={};for(let t of e)t.hasDescendants&&(a[t.id]=!0);return a}function Ji(e,t,n){if(!e||!t)return;let r={};for(let[e,t]of Object.entries(n))t&&(r[Number(e)]=!0);Wi(Hi(e,t),r)}var R=Ir((e,t)=>({menuId:0,siteId:0,loading:!0,saving:!1,publishing:!1,discarding:!1,saveFeedbackState:`idle`,state:null,nodes:[],baselineStructureMoves:[],structureDirty:!1,selectedNodeIds:[],lastSelectedNodeId:null,statusFilter:`all`,visibleColumns:{type:!0},collapsedNodeIds:{},activeTabId:null,menuContentOpen:!1,copyToSiteDialog:{open:!1,nodeIds:[],includeDeepOption:!1},error:null,init:async(t,n)=>{e({loading:!0,menuId:t,siteId:n,error:null});try{let r=await zr(t,n),i=r.nodes;r.session?.structureMoves?.length&&(i=ki(i,r.session.structureMoves));let a=Oi(r.nodes),o=qi(i,t,n);e({state:r,nodes:i,baselineStructureMoves:a,structureDirty:r.session?.hasStructureMoves??!1,activeTabId:r.builderTabs[0]?.id??null,collapsedNodeIds:o,loading:!1,lastSelectedNodeId:null})}catch(t){Yr(t),e({loading:!1,error:F(`Couldn’t load menu builder.`)})}},refresh:async()=>{let{menuId:n,siteId:r,nodes:i,structureDirty:a}=t();try{let t=await zr(n,r),o=Oi(t.nodes),s=Ki(n,r,new Set(t.nodes.map(e=>e.id))),c;c=a?Ai(i,t.nodes,{preserveStructure:!0}):t.session?.structureMoves?.length?ki(t.nodes,t.session.structureMoves):t.nodes,e({state:t,nodes:c,baselineStructureMoves:o,structureDirty:a||!!t.session?.hasStructureMoves,collapsedNodeIds:s,selectedNodeIds:[],lastSelectedNodeId:null})}catch(e){Yr(e)}},setNodes:n=>{let r=di(n),{baselineStructureMoves:i}=t();e({nodes:n,structureDirty:!Di(r,i)})},reorder:(e,n,r)=>{let{nodes:i,state:a}=t(),o=a?.menu.maxLevels??null;if(e===n){t().setNodes(ci(Si(i,e,r,o)));return}t().setNodes(ci(Ci(i,e,n,r,o)))},dropNode:(e,n,r,i)=>{let{nodes:a,state:o}=t(),s=o?.menu.maxLevels??null;t().setNodes(ci(Ii(a,e,n,r,i,s)))},indent:e=>{let{nodes:n,state:r}=t(),i=r?.menu.maxLevels??null,a=n.findIndex(t=>t.id===e),o=a>0?n[a-1]:null,s=ci(wi(n,e,i));o&&s.some(t=>t.id===e)&&t().expandNodeCollapsed(o.id),t().setNodes(s)},outdent:e=>{let{nodes:n,state:r}=t();t().setNodes(ci(Ti(n,e,r?.menu.maxLevels??null)))},moveNodeUp:e=>{let{nodes:n}=t(),r=n.find(t=>t.id===e),i=I(n,e);!r||!i||t().dropNode(e,i.id,`before`,r.level)},moveNodeDown:e=>{let{nodes:n}=t(),r=n.find(t=>t.id===e),i=L(n,e);!r||!i||t().dropNode(e,i.id,`after`,r.level)},toggleNodeSelection:(n,r)=>{let{selectedNodeIds:i}=t();e({selectedNodeIds:r?[...new Set([...i,n])]:i.filter(e=>e!==n),lastSelectedNodeId:n})},selectNode:(n,r,i)=>{let{selectedNodeIds:a,lastSelectedNodeId:o}=t();e(Vi(n,i,{selectedNodeIds:a,lastSelectedNodeId:o},r)),document.activeElement instanceof HTMLElement&&document.activeElement.blur()},setSelectedNodeIds:t=>e({selectedNodeIds:t,lastSelectedNodeId:t.length?t[t.length-1]:null}),toggleSelectAll:n=>{let r=t().nodes.filter(e=>!e.pendingDelete).map(e=>e.id);e({selectedNodeIds:n?r:[],lastSelectedNodeId:n?r[r.length-1]??null:null})},clearSelection:()=>e({selectedNodeIds:[],lastSelectedNodeId:null}),setStatusFilter:t=>e({statusFilter:t}),setColumnVisible:(t,n)=>e(e=>({visibleColumns:{...e.visibleColumns,[t]:n}})),toggleNodeCollapsed:n=>{let{collapsedNodeIds:r,menuId:i,siteId:a}=t(),o={...r};o[n]?delete o[n]:o[n]=!0,e({collapsedNodeIds:o}),Ji(i,a,o)},expandNodeCollapsed:n=>{let{collapsedNodeIds:r,menuId:i,siteId:a}=t();if(!r[n])return;let o={...r};delete o[n],e({collapsedNodeIds:o}),Ji(i,a,o)},setActiveTab:t=>e({activeTabId:t}),setMenuContentOpen:t=>e({menuContentOpen:t}),openCopyToSiteDialog:(t,n=!1)=>{e({copyToSiteDialog:{open:!0,nodeIds:t,includeDeepOption:n}})},setCopyToSiteDialogOpen:t=>{e(e=>({copyToSiteDialog:{...e.copyToSiteDialog,open:t,nodeIds:t?e.copyToSiteDialog.nodeIds:[]}}))},getStructureMoves:()=>di(t().nodes),isDirty:()=>{let{structureDirty:e,state:n}=t(),r=n?.session?.changeCount??0;return e||r>0},saveDraft:async()=>{let{menuId:n,siteId:r,structureDirty:i}=t();if(!i){Lr().cp.displayNotice(F(`Nothing to save.`));return}e({saving:!0});try{let e=await Br(n,r,t().getStructureMoves());Lr().cp.displayNotice(e.message??F(`Draft saved.`)),await t().refresh()}catch(e){Yr(e)}finally{e({saving:!1})}},publish:async()=>{let{menuId:n,siteId:r,structureDirty:i}=t();e({publishing:!0,saveFeedbackState:`idle`});try{let a=await Vr(n,r,i,t().getStructureMoves());if(Lr().cp.displayNotice(a.message??F(`Menu saved.`)),e({saveFeedbackState:`success`}),a.reload){window.location.reload();return}await t().refresh()}catch(t){e({saveFeedbackState:`error`}),Yr(t)}finally{e({publishing:!1})}},discard:async()=>{if(!confirm(F(`Discard all unsaved changes to this menu?`)))return;let{menuId:n,siteId:r}=t();e({discarding:!0});try{let e=await Hr(n,r);Lr().cp.displayNotice(e.message??F(`Build session discarded.`)),await t().refresh()}catch(e){Yr(e)}finally{e({discarding:!1})}},deleteNode:async(e,n=!1)=>{let{menuId:r,siteId:i}=t();try{let a=await Ur(r,i,e,n);a.nodes?t().applyServerNodes(a.nodes):await t().refresh()}catch(e){Yr(e)}},deleteSelectedNodes:async(n=!1)=>{let{selectedNodeIds:r,nodes:i}=t(),a=new Set(r),o=i.filter(e=>{if(!a.has(e.id))return!1;if(!n)return!0;let t=e.parentId;for(;t;){if(a.has(t))return!1;t=i.find(e=>e.id===t)?.parentId??null}return!0}).map(e=>e.id),s=null;for(let e of o)try{let{menuId:r,siteId:i}=t(),a=await Ur(r,i,e,n);a.nodes?t().applyServerNodes(a.nodes):await t().refresh()}catch(e){s??=e}e({selectedNodeIds:[],lastSelectedNodeId:null}),s&&Yr(s)},setSelectedNodesStatus:async e=>{let{menuId:n,siteId:r,selectedNodeIds:i}=t();if(i.length)try{let a=await Wr(n,r,i,e);Lr().cp.displayNotice(a.message??F(`Status updated.`)),a.nodes?t().applyServerNodes(a.nodes):await t().refresh()}catch(e){Yr(e)}},duplicateNode:async(e,n=!1)=>{let{menuId:r,siteId:i,nodes:a,structureDirty:o}=t();try{let s=await Gr(r,i,[e],n);if(Lr().cp.displayNotice(s.message??F(`Elements duplicated.`)),s.nodes){t().applyDuplicationResult(a,s.nodes,s.duplications??[],s.session,o);return}await t().refresh()}catch(e){Yr(e)}},duplicateSelectedNodes:async(e=!1)=>{let{menuId:n,siteId:r,selectedNodeIds:i,nodes:a,structureDirty:o}=t();if(i.length)try{let s=await Gr(n,r,i,e);if(Lr().cp.displayNotice(s.message??F(`Elements duplicated.`)),s.nodes){t().applyDuplicationResult(a,s.nodes,s.duplications??[],s.session,o);return}await t().refresh()}catch(e){Yr(e)}},copyNodesToSite:async(e,n,r=!1,i=!1)=>{if(!e.length)return;let{menuId:a,siteId:o}=t();try{let s=await qr(a,o,e,n,r,i);Lr().cp.displayNotice(s.message??(e.length===1?F(`Node copied to site.`):F(`{count} nodes copied to site.`,{count:e.length}))),await t().refresh()}catch(e){Yr(e)}},restoreNode:async e=>{let{menuId:n,siteId:r}=t();try{let i=await Kr(n,r,e);i.nodes?t().applyServerNodes(i.nodes):await t().refresh()}catch(e){Yr(e)}},applyServerNodes:t=>{e({nodes:t,baselineStructureMoves:Oi(t),structureDirty:!1,selectedNodeIds:[],lastSelectedNodeId:null})},applyDuplicationResult:(n,r,i,a,o)=>{let s=Ai(n,r,{preserveStructure:!0,duplications:i}),c=Oi(r),{state:l}=t();e({nodes:s,baselineStructureMoves:c,structureDirty:o||!Di(di(s),c),state:l&&a?{...l,session:a}:l,selectedNodeIds:[],lastSelectedNodeId:null})}}));function Yi(){return R(e=>e.state?.builderTabs??Xi)}var Xi=[];function Zi(){let e=window.Garnish;return e?.uiLayerManager?.registerShortcut?e:null}function Qi(e){(0,A.useEffect)(()=>{if(!e)return;let t=Zi();if(!t)return;let n={keyCode:t.S_KEY,ctrl:!0};return t.uiLayerManager.registerShortcut(n,e=>{let{publishing:t,discarding:n,structureDirty:r,state:i}=R.getState();if(!(r||(i?.session?.changeCount??0)>0)||t||n){e.bubbleShortcut?.();return}e.preventDefault(),R.getState().publish()}),()=>{t.uiLayerManager.unregisterShortcut(n,0)}},[e])}var $i=Object.defineProperty,ea=Object.defineProperties,ta=Object.getOwnPropertyDescriptors,na=Object.getOwnPropertySymbols,ra=Object.prototype.hasOwnProperty,ia=Object.prototype.propertyIsEnumerable,aa=(e,t,n)=>t in e?$i(e,t,{enumerable:!0,configurable:!0,writable:!0,value:n}):e[t]=n,oa=(e,t)=>{for(var n in t||={})ra.call(t,n)&&aa(e,n,t[n]);if(na)for(var n of na(t))ia.call(t,n)&&aa(e,n,t[n]);return e},sa=(e,t)=>ea(e,ta(t)),ca=(e,t,n)=>new Promise((r,i)=>{var a=e=>{try{s(n.next(e))}catch(e){i(e)}},o=e=>{try{s(n.throw(e))}catch(e){i(e)}},s=e=>e.done?r(e.value):Promise.resolve(e.value).then(a,o);s((n=n.apply(e,t)).next())});function la(e,t){return typeof e==`function`?e(t):e}function ua(e,t){return n=>{t.setState(t=>sa(oa({},t),{[e]:la(n,t[e])}))}}var da=(e,t=100,n=1e3)=>new Promise(r=>{let i,a=setInterval(()=>{e()&&(r(),clearInterval(a),clearTimeout(i))},t);i=setTimeout(()=>{clearInterval(a),r()},n)}),fa=`Headless Tree: `,pa=e=>Error(fa+e),ma=e=>console.warn(fa+e),ha={key:`tree`,getInitialState:e=>oa({expandedItems:[],focusedItem:null},e),getDefaultConfig:(e,t)=>oa({setExpandedItems:ua(`expandedItems`,t),setFocusedItem:ua(`focusedItem`,t)},e),stateHandlerNames:{expandedItems:`setExpandedItems`,focusedItem:`setFocusedItem`},treeInstance:{getItemsMeta:({tree:e})=>{let{rootItemId:t}=e.getConfig(),{expandedItems:n}=e.getState(),r=[],i=new Set(n),a=(t,n,o,s,c)=>{if(n.includes(t)){ma(`Circular reference for ${n.join(`.`)}`);return}if(r.push({itemId:t,level:o,index:r.length,parentId:n.at(-1),setSize:s,posInSet:c}),i.has(t)){let r=e.retrieveChildrenIds(t)??[],i=0;for(let e of r)a(e,n.concat(t),o+1,r.length,i++)}},o=e.retrieveChildrenIds(t),s=0;for(let e of o)a(e,[t],0,o.length,s++);return r},getFocusedItem:({tree:e})=>{let t=e.getState().focusedItem;return(t===null?null:e.getItemInstance(t))??e.getItems()[0]},getRootItem:({tree:e})=>{let{rootItemId:t}=e.getConfig();return e.getItemInstance(t)},focusNextItem:({tree:e})=>{var t;let n=e.getFocusedItem().getItemMeta();if(!n)return;let r=Math.min(n.index+1,e.getItems().length-1);(t=e.getItems()[r])==null||t.setFocused()},focusPreviousItem:({tree:e})=>{var t;let n=e.getFocusedItem().getItemMeta();if(!n)return;let r=Math.max(n.index-1,0);(t=e.getItems()[r])==null||t.setFocused()},updateDomFocus:({tree:e})=>{setTimeout(()=>ca(null,null,function*(){var t,n,r,i;let a=e.getFocusedItem();(n=(t=e.getConfig()).scrollToItem)==null||n.call(t,a),yield da(()=>a.getElement()!==null,20,500);let o=a.getElement();if(!o){(r=e.getItems()[0])==null||r.setFocused(),(i=e.getItems()[0]?.getElement())==null||i.focus();return}o.focus()}))},getContainerProps:({prev:e,tree:t},n)=>sa(oa({},e?.()),{role:`tree`,"aria-label":n??``,ref:t.registerElement}),isSearchOpen:()=>!1},itemInstance:{scrollTo:(e,t)=>ca(null,[e,t],function*({tree:e,item:t},n){var r,i,a;(i=(r=e.getConfig()).scrollToItem)==null||i.call(r,t),yield da(()=>t.getElement()!==null,20),(a=t.getElement())==null||a.scrollIntoView(n)}),getId:({itemId:e})=>e,getKey:({itemId:e})=>e,getProps:({item:e,prev:t})=>{let n=e.getItemMeta();return sa(oa({},t?.()),{ref:e.registerElement,role:`treeitem`,"aria-setsize":n.setSize,"aria-posinset":n.posInSet+1,"aria-selected":`false`,"aria-label":e.getItemName(),"aria-level":n.level+1,"aria-expanded":e.isFolder()?e.isExpanded():void 0,tabIndex:e.isFocused()?0:-1,onClick:t=>{e.setFocused(),e.primaryAction(),!(t.ctrlKey||t.shiftKey||t.metaKey)&&e.isFolder()&&(e.isExpanded()?e.collapse():e.expand())}})},expand:({tree:e,item:t,itemId:n})=>{t.isFolder()&&(e.getState().loadingItemChildrens?.includes(n)||(e.applySubStateUpdate(`expandedItems`,e=>[...e,n]),e.rebuildTree()))},collapse:({tree:e,item:t,itemId:n})=>{t.isFolder()&&(e.applySubStateUpdate(`expandedItems`,e=>e.filter(e=>e!==n)),e.rebuildTree())},getItemData:({tree:e,itemId:t})=>e.retrieveItemData(t),equals:({item:e},t)=>e.getId()===t?.getId(),isExpanded:({tree:e,itemId:t})=>e.getState().expandedItems.includes(t),isDescendentOf:({item:e},t)=>{let n=e.getParent();return!!(n?.getId()===t||n?.isDescendentOf(t))},isFocused:({tree:e,item:t,itemId:n})=>e.getState().focusedItem===n||e.getState().focusedItem===null&&t.getItemMeta().index===0,isFolder:({tree:e,item:t,itemId:n})=>n===e.getConfig().rootItemId||e.getConfig().isItemFolder(t),getItemName:({tree:e,item:t})=>e.getConfig().getItemName(t),setFocused:({tree:e,itemId:t})=>{e.applySubStateUpdate(`focusedItem`,t)},primaryAction:({tree:e,item:t})=>{var n;return(n=e.getConfig()).onPrimaryAction?.call(n,t)},getParent:({tree:e,item:t})=>t.getItemMeta().parentId?e.getItemInstance(t.getItemMeta().parentId):void 0,getIndexInParent:({item:e})=>e.getItemMeta().posInSet,getChildren:({tree:e,itemId:t})=>e.retrieveChildrenIds(t).map(t=>e.getItemInstance(t)),getTree:({tree:e})=>e,getItemAbove:({tree:e,item:t})=>e.getItems()[t.getItemMeta().index-1],getItemBelow:({tree:e,item:t})=>e.getItems()[t.getItemMeta().index+1]},hotkeys:{focusNextItem:{hotkey:`ArrowDown`,canRepeat:!0,preventDefault:!0,isEnabled:e=>!(e.isSearchOpen?.call(e)??!1)&&!e.getState().dnd,handler:(e,t)=>{t.focusNextItem(),t.updateDomFocus()}},focusPreviousItem:{hotkey:`ArrowUp`,canRepeat:!0,preventDefault:!0,isEnabled:e=>!(e.isSearchOpen?.call(e)??!1)&&!e.getState().dnd,handler:(e,t)=>{t.focusPreviousItem(),t.updateDomFocus()}},expandOrDown:{hotkey:`ArrowRight`,canRepeat:!0,handler:(e,t)=>{let n=t.getFocusedItem();n.isExpanded()||!n.isFolder()?(t.focusNextItem(),t.updateDomFocus()):n.expand()}},collapseOrUp:{hotkey:`ArrowLeft`,canRepeat:!0,handler:(e,t)=>{var n;let r=t.getFocusedItem();(!r.isExpanded()||!r.isFolder())&&r.getItemMeta().level!==0?((n=r.getParent())==null||n.setFocused(),t.updateDomFocus()):r.collapse()}},focusFirstItem:{hotkey:`Home`,handler:(e,t)=>{var n;(n=t.getItems()[0])==null||n.setFocused(),t.updateDomFocus()}},focusLastItem:{hotkey:`End`,handler:(e,t)=>{var n;(n=t.getItems()[t.getItems().length-1])==null||n.setFocused(),t.updateDomFocus()}}}},ga=(e,t,n)=>{let r={};return[r,()=>{let i=n(r);featureLoop:for(let n=0;n<e.length;n++){let a=e[n][t];if(!a)continue featureLoop;methodLoop:for(let[e,t]of Object.entries(a)){if(!t)continue methodLoop;let n=r[e];r[e]=(...e)=>t(sa(oa({},i),{prev:n}),...e)}}}]},_a=e=>{let t=e?.map(e=>e.key);for(let n of e??[]){let e=n.deps?.find(e=>!t?.includes(e));if(e)throw pa(`${n.key} needs ${e}`)}},va=(e,t)=>{let n=e.length;for(let r=0;r<n;r++)for(let i=r+1;i<n;i++)t(e[i],e[r])<0&&([e[r],e[i]]=[e[i],e[r]]);return e},ya=e=>(t,n)=>n.key&&t.overwrites?.includes(n.key)?1:t.key&&n.overwrites?.includes(t.key)?-1:e.indexOf(t)-e.indexOf(n),ba=(e=[])=>va(e,ya(e)),xa=e=>{let t=e.instanceBuilder??ga,n=[ha,...ba(e.features)];_a(n);let r=[...n],[i,a]=t(r,`treeInstance`,e=>({tree:e})),o=n.reduce((e,t)=>t.getInitialState?.call(t,e,i)??e,e.initialState??e.state??{}),s=n.reduce((e,t)=>t.getDefaultConfig?.call(t,e,i)??e,e),c=n.reduce((e,t)=>oa(oa({},e),t.stateHandlerNames),{}),l,u={current:{}},d=!1,f={},p=[],m={},h={},g={},_={},v=()=>{p=[],g={};let[e,n]=t(r,`itemInstance`,e=>({item:e,tree:i,itemId:s.rootItemId}));n(),f[s.rootItemId]=e,g[s.rootItemId]={itemId:s.rootItemId,index:-1,parentId:null,level:-1,posInSet:0,setSize:1};for(let e of i.getItemsMeta())if(g[e.itemId]=e,f[e.itemId])p.push(f[e.itemId]);else{let[n,a]=t(r,`itemInstance`,t=>({item:t,tree:i,itemId:e.itemId}));a(),f[e.itemId]=n,p.push(n)}d=!1},y=e=>{for(let t of n)e(t)};r.unshift({key:`main`,treeInstance:{getState:()=>o,setState:({},e)=>{var t;(t=s.setState)==null||t.call(s,o)},setMounted:({},e)=>{var t;let n=u.current;n.isMounted=e,e&&((t=n.waitingForMount)==null||t.forEach(e=>e()),n.waitingForMount=[])},applySubStateUpdate:({},e,t)=>{let n=()=>{o[e]=typeof t==`function`?t(o[e]):t;let n=s[c[e]];n?.(o[e])},r=u.current;r.isMounted?n():(r.waitingForMount??=[],r.waitingForMount.push(n))},rebuildTree:()=>{var e;let t=u.current;t.isMounted?(v(),(e=s.setState)==null||e.call(s,o)):(t.waitingForMount??=[],t.waitingForMount.push(()=>{var e;v(),(e=s.setState)==null||e.call(s,o)}))},scheduleRebuildTree:()=>{d=!0},getConfig:()=>s,setConfig:(e,t)=>{var n;let r=typeof t==`function`?t(s):t,i=r.state?.expandedItems&&r.state?.expandedItems!==o.expandedItems;s=r,r.state&&(o=oa(oa({},o),r.state)),i&&(v(),(n=s.setState)==null||n.call(s,o))},getItemInstance:({},e)=>{let n=f[e];if(!n){let[n,a]=t(r,`itemInstance`,t=>({item:t,tree:i,itemId:e}));return a(),n}return n},getItems:()=>(d&&v(),p),registerElement:({},e)=>{l!==e&&(l&&!e?y(e=>e.onTreeUnmount?.call(e,i,l)):!l&&e&&y(t=>t.onTreeMount?.call(t,i,e)),l=e)},getElement:()=>l,getDataRef:()=>u,getHotkeyPresets:()=>_},itemInstance:{registerElement:({itemId:e,item:t},n)=>{if(m[e]===n)return;let r=m[e];r&&!n?y(e=>e.onItemUnmount?.call(e,t,r,i)):!r&&n&&y(e=>e.onItemMount?.call(e,t,n,i)),m[e]=n},getElement:({itemId:e})=>m[e],getDataRef:({itemId:e})=>h[e]??(h[e]={current:{}}),getItemMeta:({itemId:e})=>g[e]??{itemId:e,parentId:null,level:-1,index:-1,posInSet:0,setSize:1}}});for(let e of r)Object.assign(_,e.hotkeys??{});return a(),i},Sa=`sync dataLoader returned undefined`,Ca=`sync dataLoader returned promise`,wa=e=>{if(!e)throw pa(Sa);if(typeof e==`object`&&`then`in e)throw pa(Ca);return e},Ta={key:`sync-data-loader`,getInitialState:e=>oa({loadingItemData:[],loadingItemChildrens:[]},e),getDefaultConfig:(e,t)=>oa({setLoadingItemData:ua(`loadingItemData`,t),setLoadingItemChildrens:ua(`loadingItemChildrens`,t)},e),stateHandlerNames:{loadingItemData:`setLoadingItemData`,loadingItemChildrens:`setLoadingItemChildrens`},treeInstance:{waitForItemDataLoaded:()=>ca(null,null,function*(){}),waitForItemChildrenLoaded:()=>ca(null,null,function*(){}),retrieveItemData:({tree:e},t)=>wa(e.getConfig().dataLoader.getItem(t)),retrieveChildrenIds:({tree:e},t)=>{let{dataLoader:n}=e.getConfig();return`getChildren`in n?wa(n.getChildren(t)):wa(n.getChildrenWithData(t)).map(e=>e.data)},loadItemData:({tree:e},t)=>e.retrieveItemData(t),loadChildrenIds:({tree:e},t)=>e.retrieveChildrenIds(t)},itemInstance:{isLoading:()=>!1,hasLoadedData:()=>!0}},Ea=e=>`childIndex`in e,Da=(e,t,n)=>{let r=n.getState().dnd?.draggedItems,i=n.getConfig();return!(r&&!(i.canDrop?.call(i,r,t)??!0)||r&&r.some(e=>t.item.getId()===e.getId()||t.item.isDescendentOf(e.getId()))||!r&&e&&i.canDropForeignDragObject&&!i.canDropForeignDragObject(e,t))},Oa=e=>e.isExpanded()?1:e.getParent()&&e.getIndexInParent()===e.getItemMeta().setSize-1?2:0,ka=(e,t,n)=>t-(e.slice(0,t).reduce((e,t)=>t&&n?.some(e=>e.getId()===t.getId())?++e:e,0)??0),Aa=(e,t,n,r)=>{let i=n.getConfig();if(!i.canReorder)return r?{type:2}:{type:1};let a=t.getElement()?.getBoundingClientRect(),o=a?(e.clientY-a.top)/a.height:.5,s=a?e.clientX-a.left:0,c=Oa(t),l=r?i.reorderAreaPercentage??.3:.5,u=i.indent??20,d=r?2:1;if(c===1)return o<l?{type:0}:{type:d};if(c===2&&s<t.getItemMeta().level*u){if(o<.5)return{type:0};let e=t.getItemBelow()?.getItemMeta().level??0;return{type:3,reparentLevel:Math.max(e,Math.floor(s/u))}}return o<l?{type:0}:o>1-l?{type:1}:{type:d}},ja=(e,t)=>[e.getId(),t.type,t.type===3?t.reparentLevel:0].join(`__`),Ma=(e,t)=>t===e.getItemMeta().level?e:Ma(e.getParent(),t),Na=(e,t,n)=>{let r=e.getItemMeta(),i=Ma(e,t-1),a=Ma(e,t).getIndexInParent()+1;return{item:i,childIndex:a,insertionIndex:ka(i.getChildren(),a,n),dragLineIndex:r.index+1,dragLineLevel:t}},Pa=(e,t,n,r,i=n.getConfig().canReorder)=>{let a=r?e.dataTransfer:null,o=n.getState().dnd?.draggedItems,s=t.getItemMeta(),c=t.getParent(),l={item:t},u=c?{item:c}:null,d=u&&Da(a,u,n),f=Aa(e,t,n,Da(a,l,n));if(!i&&c&&d&&f.type!==2)return o?.some(e=>e.isDescendentOf(c.getId()))?l:u;if(f.type===2)return l;if(!i&&c&&!d)return Pa(e,c,n,r,!1);if(!c)return l;if(!d)return Pa(e,c,n,r,!1);if(f.type===3)return Na(t,f.reparentLevel,o);let p=f.type===0?0:1,m=t.getIndexInParent()+p;return{item:c,dragLineIndex:s.index+p,dragLineLevel:s.level,childIndex:m,insertionIndex:ka(c.getChildren(),m,o)}},Fa=(e,t,n,r)=>{let{openOnDropDelay:i}=t.getConfig(),a=e.current.lastDragCode;!i||!n.isFolder()||n.isExpanded()||r.type!==2||(clearTimeout(e.current.autoExpandTimeout),e.current.autoExpandTimeout=setTimeout(()=>{a!==e.current.lastDragCode||!e.current.lastAllowDrop||n.expand()},i))},Ia=()=>!1,La={key:`drag-and-drop`,getDefaultConfig:(e,t)=>oa({canDrop:(e,t)=>t.item.isFolder(),canDropForeignDragObject:Ia,canDragForeignDragObjectOver:e.canDropForeignDragObject===Ia?()=>!1:e=>e.effectAllowed!==`none`,setDndState:ua(`dnd`,t),canReorder:!0,openOnDropDelay:800,draggedItemOverwritesSelection:!0},e),stateHandlerNames:{dnd:`setDndState`},onTreeMount:e=>{let t=()=>{e.applySubStateUpdate(`dnd`,null)};e.getDataRef().current.windowDragEndListener=t,window.addEventListener(`dragend`,t)},onTreeUnmount:e=>{let{windowDragEndListener:t}=e.getDataRef().current;t&&window.removeEventListener(`dragend`,t)},treeInstance:{getDragTarget:({tree:e})=>e.getState().dnd?.dragTarget??null,getDragLineData:({tree:e})=>{let t=e.getDragTarget(),n=(t?.item.getItemMeta().level??0)+1,r=e.getElement()?.getBoundingClientRect();if(!t||!r||!Ea(t))return null;let i=t.dragLineLevel*(e.getConfig().indent??1),a=e.getItems()[t.dragLineIndex];if(!a){let a=(e.getItems()[t.dragLineIndex-1]?.getElement())?.getBoundingClientRect();if(a)return{indent:n,top:a.bottom-r.top,left:a.left+i-r.left,width:a.width-i}}let o=(a?.getElement())?.getBoundingClientRect();return o?{indent:n,top:o.top-r.top,left:o.left+i-r.left,width:o.width-i}:null},getDragLineStyle:({tree:e},t=-1,n=-8)=>{let r=e.getDragLineData();return r?{position:`absolute`,top:`${r.top+t}px`,left:`${r.left+n}px`,width:`${r.width-n}px`,pointerEvents:`none`}:{display:`none`}},getContainerProps:({prev:e,tree:t},n)=>{let r=e?.(n);return sa(oa({},r),{onDragOver:e=>{e.preventDefault()},onDrop:e=>ca(null,null,function*(){let n=t.getDataRef(),r={item:t.getRootItem()};if(!Da(e.dataTransfer,r,t))return;e.preventDefault();let i=t.getConfig(),a=t.getState().dnd?.draggedItems;n.current.lastDragCode=void 0,a?yield i.onDrop?.call(i,a,r):e.dataTransfer&&(yield i.onDropForeignDragObject?.call(i,e.dataTransfer,r))}),style:sa(oa({},r?.style),{position:`relative`})})}},itemInstance:{getProps:({tree:e,item:t,prev:n})=>sa(oa(oa({},n?.()),e.getConfig().seperateDragHandle?{}:t.getDragHandleProps()),{onDragEnter:e=>e.preventDefault(),onDragOver:n=>{var r;n.stopPropagation();let i=e.getDataRef(),a=Aa(n,t,e,!0),o=ja(t,a);if(o===i.current.lastDragCode){i.current.lastAllowDrop&&n.preventDefault();return}i.current.lastDragCode=o,i.current.lastDragEnter=Date.now(),Fa(i,e,t,a);let s=Pa(n,t,e,!1);if(!e.getState().dnd?.draggedItems&&(!n.dataTransfer||!(r=e.getConfig()).canDragForeignDragObjectOver?.call(r,n.dataTransfer,s))){i.current.lastAllowDrop=!1;return}if(!Da(null,s,e)){i.current.lastAllowDrop=!1;return}e.applySubStateUpdate(`dnd`,e=>sa(oa({},e),{dragTarget:s,draggingOverItem:t})),i.current.lastAllowDrop=!0,n.preventDefault()},onDragLeave:()=>{setTimeout(()=>{let t=e.getDataRef();(t.current.lastDragEnter??0)+100>=Date.now()||(t.current.lastDragCode=`no-drag`,e.applySubStateUpdate(`dnd`,e=>sa(oa({},e),{draggingOverItem:void 0,dragTarget:void 0})))},100)},onDrop:n=>ca(null,null,function*(){n.stopPropagation();let r=e.getDataRef(),i=Pa(n,t,e,!0),a=e.getState().dnd?.draggedItems,o=Da(n.dataTransfer,i,e);if(e.applySubStateUpdate(`dnd`,{draggedItems:void 0,draggingOverItem:void 0,dragTarget:void 0}),!o)return;n.preventDefault();let s=e.getConfig();r.current.lastDragCode=void 0,a?(yield s.onDrop?.call(s,a,i),a[0].setFocused()):n.dataTransfer&&(yield s.onDropForeignDragObject?.call(s,n.dataTransfer,i)),e.applySubStateUpdate(`dnd`,null),e.updateDomFocus()})}),getDragHandleProps:({tree:e,item:t,prev:n})=>sa(oa({},n?.()),{draggable:!0,onDragStart:n=>{var r,i;let{draggedItemOverwritesSelection:a}=e.getConfig(),o=e.getSelectedItems?e.getSelectedItems():[e.getFocusedItem()],s=!o.includes(t)&&a,c=s?[t]:o,l=e.getConfig();if(s&&((r=e.setSelectedItems)==null||r.call(e,[t.getItemMeta().itemId])),!(l.canDrag?.call(l,c)??!0)){n.preventDefault();return}if(l.setDragImage){let{imgElement:e,xOffset:t,yOffset:r}=l.setDragImage(c);(i=n.dataTransfer)==null||i.setDragImage(e,t??0,r??0)}if(l.createForeignDragObject&&n.dataTransfer){let{format:e,data:t,dropEffect:r,effectAllowed:i}=l.createForeignDragObject(c);n.dataTransfer.setData(e,t),r&&(n.dataTransfer.dropEffect=r),i&&(n.dataTransfer.effectAllowed=i)}e.applySubStateUpdate(`dnd`,{draggedItems:c,draggingOverItem:e.getFocusedItem()})},onDragEnd:n=>{let{onCompleteForeignDrop:r,canDragForeignDragObjectOver:i}=e.getConfig(),a=e.getState().dnd?.draggedItems;if(n.dataTransfer?.dropEffect===`none`||!a)return;let o=Pa(n,t,e,!1);i&&n.dataTransfer&&!i(n.dataTransfer,o)||r?.(a)}}),isDragTarget:({tree:e,item:t})=>{let n=e.getDragTarget();return n?n.item.getId()===t.getId():!1},isUnorderedDragTarget:({tree:e,item:t})=>{let n=e.getDragTarget();return n?!Ea(n)&&n.item.getId()===t.getId():!1},isDragTargetAbove:({tree:e,item:t})=>{let n=e.getDragTarget();return!n||!Ea(n)||n.item!==t.getParent()?!1:n.childIndex===t.getItemMeta().posInSet},isDragTargetBelow:({tree:e,item:t})=>{let n=e.getDragTarget();return!n||!Ea(n)||n.item!==t.getParent()?!1:n.childIndex-1===t.getItemMeta().posInSet},isDraggingOver:({tree:e,item:t})=>(e.getState().dnd?.draggingOverItem)?.getId()===t.getId()}},Ra=Object.defineProperty,za=Object.defineProperties,Ba=Object.getOwnPropertyDescriptors,Va=Object.getOwnPropertySymbols,Ha=Object.prototype.hasOwnProperty,Ua=Object.prototype.propertyIsEnumerable,Wa=(e,t,n)=>t in e?Ra(e,t,{enumerable:!0,configurable:!0,writable:!0,value:n}):e[t]=n,Ga=(e,t)=>{for(var n in t||={})Ha.call(t,n)&&Wa(e,n,t[n]);if(Va)for(var n of Va(t))Ua.call(t,n)&&Wa(e,n,t[n]);return e},Ka=(e,t)=>za(e,Ba(t)),qa=e=>{let[t]=(0,A.useState)(()=>({current:xa(e)})),[n,r]=(0,A.useState)(()=>t.current.getState());return(0,A.useEffect)(()=>(t.current.setMounted(!0),t.current.rebuildTree(),()=>{t.current.setMounted(!1)}),[t]),t.current.setConfig(t=>Ka(Ga(Ga({},t),e),{state:Ga(Ga({},n),e.state),setState:t=>{var n;r(t),(n=e.setState)==null||n.call(e,t)}})),t.current};function Ja(){return v`
        <svg xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" viewBox="0 0 640 640">
            <path
                fill="currentColor"
                d="M557.5 192L534.9 214.6L278.9 470.6C266.4 483.1 246.1 483.1 233.6 470.6L105.6 342.6L83 320L128.3 274.7C129.6 276 172.3 318.7 256.3 402.7L489.7 169.3L512.3 146.7L557.6 192z"
            />
        </svg>
    `}function Ya(){return v`
        <svg xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" viewBox="0 0 640 640">
            <path fill="currentColor" d="M96 352V288H544V352H96z" />
        </svg>
    `}var Xa=[y`
    @layer pk-component {
        .control {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            position: relative;
            box-sizing: border-box;
            width: var(--pk-checkbox-size);
            height: var(--pk-checkbox-size);
            border: 1px solid var(--pk-checkbox-border-color, #c0cbd9);
            border-radius: var(--pk-radius-sm);
            background: var(--pk-color-white);
            cursor: pointer;
            transition: border-color 0.12s ease, box-shadow 0.12s ease;
        }

        :host([disabled]) .control {
            cursor: not-allowed;
        }

        .input:focus-visible + .control {
            border-color: var(--pk-color-sky-600);
            box-shadow: 0 0 0 1px var(--pk-color-sky-600), 0 0 4px 0 hsl(from var(--pk-color-sky-600) h s l / 0.7);
        }

        :host([invalid]) .control,
        .input[aria-invalid='true'] + .control {
            border-color: var(--pk-color-rose-600);
        }

        :host([invalid]) .input:focus-visible + .control,
        .input[aria-invalid='true']:focus-visible + .control {
            border-color: var(--pk-color-rose-600);
            box-shadow: 0 0 0 1px var(--pk-color-rose-600), 0 0 4px 0 hsl(from var(--pk-color-rose-600) h s l / 0.7);
        }

        .indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--pk-color-gray-900);
        }

        .icon-check,
        .icon-indeterminate {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
        }

        .icon-check svg {
            width: 14px;
            height: 14px;
            transform: translateY(1px) scale(1.2);
        }

        .icon-indeterminate svg {
            width: 12px;
            height: 12px;
        }

        :host([checked]) .icon-check,
        .input:checked + .control .icon-check {
            opacity: 1;
        }

        :host([indeterminate]) .icon-check,
        .input:indeterminate + .control .icon-check {
            opacity: 0;
        }

        :host([indeterminate]) .icon-indeterminate,
        .input:indeterminate + .control .icon-indeterminate {
            opacity: 1;
        }
    }
`,y`
    @layer pk-component {
        :host {
            display: inline-flex;
            vertical-align: middle;
            /* Hit target is the content-sized .root label (Craft checkbox-select), not the host. */
            cursor: default;
            font-family: var(--pk-font-family);
            font-size: var(--pk-font-size-base);
            line-height: var(--pk-line-height);
        }

        :host([disabled]) {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .root {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: flex-start;
            gap: var(--pk-control-label-gap);
            /* Content-sized like Craft's <label> beside the checkbox — not full-row. */
            width: fit-content;
            max-width: 100%;
            margin: 0;
            min-height: 0;
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        :host([disabled]) .root {
            cursor: not-allowed;
        }

        .root--with-hint {
            align-items: flex-start;
        }

        .input {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
            opacity: 0;
            appearance: none;
        }

        .text {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
            min-width: 0;
        }

        .label {
            line-height: max(1rem, var(--pk-checkbox-size));
            /* Match form-control / Craft body labels (gray-700), not gray-900. */
            color: var(--pk-color-gray-700);
            cursor: pointer;
        }

        :host([disabled]) .label {
            cursor: not-allowed;
        }

        :host(.all-option) .label {
            font-weight: 700;
        }

        .hint {
            margin: 0;
            color: var(--pk-color-gray-500);
            font-size: var(--pk-font-size-sm);
            line-height: var(--pk-line-height);
        }

        .hint:empty {
            display: none;
        }
    }
`],Za=class extends Ve{constructor(...e){super(...e),this.assumeInteractionOn=[`change`],this.hasSlotController=new S(this,`hint`),this.checked=!1,this.indeterminate=!1,this.disabled=!1,this.invalid=!1,this.checkboxValue=`on`,this.defaultChecked=!1,this.ariaLabel=null,this.hint=``,this.withHint=!1,this.hasDefaultSlotContent=!1}static{this.shadowRootOptions={mode:`open`,delegatesFocus:!0}}static{this.styles=Xa}static get validators(){return[...super.validators,He({validationProperty:`checked`})]}get validationTarget(){return this.input}syncFormValue(){this.setFormValue(this.checked?this.checkboxValue:null,this.checked?`on`:`off`)}resetToDefaultValue(){this.checked=this.defaultChecked,this.indeterminate=!1}restoreFormState(e){e===`on`||e===this.checkboxValue?this.checked=!0:this.checked=!1}updated(e){if(!this.input){super.updated(e);return}(e.has(`indeterminate`)||e.has(`checked`))&&(this.input.indeterminate=this.indeterminate,this.input.checked=this.checked),super.updated(e)}defaultSlotChanged(e){let t=e.target;this.hasDefaultSlotContent=t.assignedNodes({flatten:!0}).some(e=>e.nodeType===Node.TEXT_NODE?e.textContent?.trim():e.nodeType===Node.ELEMENT_NODE)}handleChange(e){let t=e.target;this.checked=t.checked,this.indeterminate=!1,this.dispatchEvent(new CustomEvent(`pk-change`,{detail:{checked:this.checked},bubbles:!0,composed:!0})),this.dispatchEvent(new Event(`input`,{bubbles:!0,composed:!0})),this.dispatchEvent(new Event(`change`,{bubbles:!0,composed:!0}))}render(){let e=this.hasDefaultSlotContent,t=!!this.hint||this.hasSlotController.test(`hint`,this.withHint);return v`
            <label
                part="base"
                class=${ae({root:!0,"root--with-hint":t})}
            >
                <input
                    part="input"
                    class="input"
                    type="checkbox"
                    .checked=${this.checked}
                    ?disabled=${this.disabled}
                    ?required=${this.required}
                    name=${this.name??T}
                    value=${this.checkboxValue}
                    aria-labelledby=${e?`label`:T}
                    aria-describedby=${t?`hint`:T}
                    aria-label=${e?T:this.ariaLabel??T}
                    aria-invalid=${this.invalid?`true`:T}
                    @change=${this.handleChange}
                />
                <span part="control" class="control">
                    <span part="checked-icon" class="icon-check">${Ja()}</span>
                    <span part="indeterminate-icon" class="icon-indeterminate">${Ya()}</span>
                </span>
                ${e||t?v`
                        <span class="text">
                            ${e?v`
                                    <span part="label" class="label" id="label">
                                        <slot @slotchange=${this.defaultSlotChanged}></slot>
                                    </span>
                                `:v`<slot @slotchange=${this.defaultSlotChanged} hidden></slot>`}
                            ${t?v`
                                    <span part="hint" class="hint" id="hint">
                                        <slot name="hint">${this.hint}</slot>
                                    </span>
                                `:T}
                        </span>
                    `:v`<slot @slotchange=${this.defaultSlotChanged} hidden></slot>`}
            </label>
        `}};s([c({type:Boolean,reflect:!0})],Za.prototype,`checked`,void 0),s([c({type:Boolean,reflect:!0})],Za.prototype,`indeterminate`,void 0),s([c({type:Boolean,reflect:!0})],Za.prototype,`disabled`,void 0),s([c({type:Boolean,reflect:!0})],Za.prototype,`invalid`,void 0),s([c()],Za.prototype,`checkboxValue`,void 0),s([c({attribute:`default-checked`,type:Boolean})],Za.prototype,`defaultChecked`,void 0),s([c({attribute:`aria-label`})],Za.prototype,`ariaLabel`,void 0),s([c()],Za.prototype,`hint`,void 0),s([c({type:Boolean,attribute:`with-hint`})],Za.prototype,`withHint`,void 0),s([r(`.input`)],Za.prototype,`input`,void 0),s([g()],Za.prototype,`hasDefaultSlotContent`,void 0),Za=s([h(`pk-checkbox`)],Za);var Qa=u({tagName:`pk-checkbox`,elementClass:Za,react:A.default,events:{onPkChange:`pk-change`,onChange:`change`}});function $a({children:e,checked:t,defaultChecked:n,indeterminate:r=!1,disabled:i=!1,invalid:a=!1,required:o=!1,value:s=`on`,"data-state":c,onCheckedChange:l,onPkChange:u,...d}){let[f,p]=A.useState(!!n),m=t!==void 0,h=m?t:f,g=e=>{let t=Be(e);m||p(t),u?.(e),l?.(t)};return(0,j.jsx)(Qa,{checked:h,defaultChecked:n,indeterminate:r,disabled:i,invalid:a,required:o,checkboxValue:s,...c?{"data-state":c}:{},onPkChange:g,...d,children:e})}var eo=e=>e;function to(e,t,n,r){document.activeElement?.blur?.();let i=window.$(`<div/>`).data({id:t,siteId:n,type:e,editable:!0,savable:!0});Lr().createElementEditor(e,i,{onSaveElement:()=>{r?.()}})}function no(e,t){Lr().createElementSelectorModal(e,{defaultSiteId:t.siteId,sources:t.sources??void 0,criteria:t.criteria??void 0,condition:t.condition??void 0,multiSelect:!0,showSiteMenu:t.showSiteMenu,onSelect:e=>{t.onSelect(e.filter(e=>typeof e.id==`number`&&typeof e.siteId==`number`).map(e=>({id:e.id,siteId:e.siteId,url:e.url})))}})}function ro({node:e}){return(0,j.jsx)(`div`,{className:`cursor-default select-none text-right text-[10px] font-semibold uppercase tracking-wide`,children:(0,j.jsx)(`span`,{className:Xr(`inline-block whitespace-nowrap rounded border border-transparent px-1 py-0.5`,e.typeClass),title:e.url??void 0,style:{color:`rgb(${e.typeTextColorRgb})`,backgroundColor:`rgba(${e.typeColorRgb}, 0.1)`,borderColor:`rgba(${e.typeTextColorRgb}, 0.35)`},children:e.typeLabel})})}var io={default:y`
        --pk-dropdown-item-padding-block: 8px;
        --pk-dropdown-item-padding-inline: 12px;
        --pk-dropdown-item-gap: 0.625rem;
        --pk-dropdown-item-font-size: var(--pk-font-size-base);
        --pk-dropdown-item-line-height: 1.5;
        --pk-dropdown-item-icon-size: 12px;
        --pk-dropdown-label-padding-inline: 12px;
        --pk-dropdown-label-font-size: 13px;
        --pk-dropdown-details-font-size: var(--pk-font-size-sm);
    `,xs:y`
        --pk-dropdown-item-padding-block: 3px;
        --pk-dropdown-item-padding-inline: 8px;
        --pk-dropdown-item-gap: 0.375rem;
        --pk-dropdown-item-font-size: 12px;
        --pk-dropdown-item-line-height: 1.5;
        --pk-dropdown-item-icon-size: 10px;
        --pk-dropdown-label-padding-inline: 8px;
        --pk-dropdown-label-font-size: 11px;
        --pk-dropdown-details-font-size: 11px;
    `,sm:y`
        --pk-dropdown-item-padding-block: 4px;
        --pk-dropdown-item-padding-inline: 10px;
        --pk-dropdown-item-gap: 0.4375rem;
        --pk-dropdown-item-font-size: 13px;
        --pk-dropdown-item-line-height: 1.5;
        --pk-dropdown-item-icon-size: 12px;
        --pk-dropdown-label-padding-inline: 10px;
        --pk-dropdown-label-font-size: 11px;
        --pk-dropdown-details-font-size: 12px;
    `,lg:y`
        --pk-dropdown-item-padding-block: 10px;
        --pk-dropdown-item-padding-inline: 14px;
        --pk-dropdown-item-gap: 0.75rem;
        --pk-dropdown-item-font-size: 16px;
        --pk-dropdown-item-line-height: 1.5;
        --pk-dropdown-item-icon-size: 14px;
        --pk-dropdown-label-padding-inline: 14px;
        --pk-dropdown-label-font-size: 14px;
        --pk-dropdown-details-font-size: var(--pk-font-size-sm);
    `,xl:y`
        --pk-dropdown-item-padding-block: 12px;
        --pk-dropdown-item-padding-inline: 16px;
        --pk-dropdown-item-gap: 0.75rem;
        --pk-dropdown-item-font-size: 18px;
        --pk-dropdown-item-line-height: 1.5;
        --pk-dropdown-item-icon-size: 16px;
        --pk-dropdown-label-padding-inline: 16px;
        --pk-dropdown-label-font-size: 15px;
        --pk-dropdown-details-font-size: var(--pk-font-size-base);
    `},ao=y`
    @layer pk-component {
        :host {
            ${io.default}
        }

        :host([size='xs']) {
            ${io.xs}
        }

        :host([size='sm']) {
            ${io.sm}
        }

        :host([size='lg']) {
            ${io.lg}
        }

        :host([size='xl']) {
            ${io.xl}
        }
    }
`,oo=y`
    @layer pk-component {
        .panel[data-size='default'],
        .submenu-panel[data-size='default'] {
            ${io.default}
        }

        .panel[data-size='xs'],
        .submenu-panel[data-size='xs'] {
            ${io.xs}
        }

        .panel[data-size='sm'],
        .submenu-panel[data-size='sm'] {
            ${io.sm}
        }

        .panel[data-size='lg'],
        .submenu-panel[data-size='lg'] {
            ${io.lg}
        }

        .panel[data-size='xl'],
        .submenu-panel[data-size='xl'] {
            ${io.xl}
        }
    }
`;y`
    ${ao}
    ${oo}
`;var so=[Ce,oo,y`
    @layer pk-component {
        :host {
            display: block;
            position: relative;
            /*
             * Slotted label text inherits from this host (light DOM), not from
             * shadow .item — pin size-token metrics so Craft CP / Tailwind /
             * bare hosts all get the same item rhythm.
             */
            font-size: var(--pk-dropdown-item-font-size, var(--pk-font-size-base));
            line-height: var(--pk-dropdown-item-line-height, 1.5);
            color: var(--text-color, var(--pk-color-gray-700));
        }

        .item {
            display: flex;
            align-items: center;
            gap: var(--pk-dropdown-item-gap, 0.625rem);
            width: 100%;
            margin: 0;
            padding: var(--pk-dropdown-item-padding-block, 8px) var(--pk-dropdown-item-padding-inline, 12px);
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            font-size: var(--pk-dropdown-item-font-size, var(--pk-font-size-base));
            /* Explicit — do not let font:inherit re-leak page line-height. */
            line-height: var(--pk-dropdown-item-line-height, 1.5);
            font-weight: normal;
            text-align: left;
            white-space: nowrap;
            cursor: default;
            user-select: none;
            outline: none;
            box-sizing: border-box;
        }

        .item:hover:not([disabled]):not([aria-disabled='true']),
        :host([data-highlighted]) .item,
        :host([submenu-open]) .item {
            background: var(--pk-color-slate-100);
        }

        .item:focus-visible {
            background: var(--pk-color-slate-100);
        }

        .item[aria-disabled='true'] {
            pointer-events: none;
            opacity: 0.5;
        }

        .label {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .prefix {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: var(--pk-dropdown-item-icon-size, 12px);
            height: var(--pk-dropdown-item-icon-size, 12px);
            line-height: 0;
        }

        .prefix--empty {
            display: none;
        }

        .prefix ::slotted(*) {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: var(--pk-dropdown-item-icon-size, 12px);
            height: var(--pk-dropdown-item-icon-size, 12px);
            /* Kill pk-icon text-baseline nudge inside the padded flex row. */
            vertical-align: 0;
        }

        .prefix ::slotted(svg),
        .prefix ::slotted(*) svg,
        .prefix ::slotted(.pk-dropdown-item__prefix-icon) {
            display: block;
            width: var(--pk-dropdown-item-icon-size, 12px) !important;
            height: var(--pk-dropdown-item-icon-size, 12px) !important;
            max-width: var(--pk-dropdown-item-icon-size, 12px);
            max-height: var(--pk-dropdown-item-icon-size, 12px);
            flex-shrink: 0;
            pointer-events: none;
        }

        .details {
            margin-left: auto;
            color: var(--pk-color-gray-500);
            font-size: var(--pk-dropdown-details-font-size, var(--pk-font-size-sm));
            letter-spacing: 0.04em;
        }

        .details:empty {
            display: none;
        }

        .check {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 12px;
            height: 12px;
            color: var(--pk-color-gray-700);
        }

        .check svg {
            display: block;
            width: 12px;
            height: 12px;
            flex-shrink: 0;
            pointer-events: none;
        }

        .submenu-icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 1rem;
            color: var(--pk-color-gray-700);
        }

        .submenu-icon svg {
            display: block;
            width: 1em;
            height: 1em;
            flex-shrink: 0;
            pointer-events: none;
        }

        .check {
            opacity: 0;
        }

        :host([checked]) .check {
            opacity: 1;
        }

        :host([type='checkbox']) .check,
        :host([type='radio']) .check {
            margin-left: auto;
        }

        :host([type='checkbox'][checked]) .check,
        :host([type='radio'][checked]) .check {
            opacity: 1;
        }

        .submenu-icon:empty {
            display: none;
        }

        :host([destructive]) .item {
            color: var(--pk-color-error);
        }

        :host([destructive]) .item:hover:not([disabled]):not([aria-disabled='true']),
        :host([destructive]) .item:focus-visible {
            color: var(--pk-color-error);
        }

        .submenu-panel {
            width: max-content;
            min-width: 8rem;
            overflow: hidden;
            padding: 4px 0;
            border-radius: var(--pk-radius-md);
            background: var(--pk-color-white);
            box-shadow: var(--pk-shadow-popup);
            /* Match root menu panel — Craft body text, not gray-900. */
            color: var(--text-color, var(--pk-color-gray-700));
        }

        .submenu-panel ::slotted(pk-dropdown-item),
        .submenu-panel ::slotted(pk-dropdown-separator),
        .submenu-panel ::slotted(pk-dropdown-label) {
            display: block;
        }

        .submenu-panel[hidden] {
            display: none !important;
        }
    }
`],co,lo=D(ee),uo=D(p),z=class extends _{static{co=this}constructor(...e){super(...e),this.value=``,this.type=`normal`,this.radioGroup=``,this.disabled=!1,this.destructive=!1,this.checked=!1,this.submenuOpen=!1,this.active=!1,this.submenuAnimated=!1,this.hasSlotController=new S(this,`submenu`,`details`,`start`,`prefix`),this.handleMouseEnter=()=>{!this.hasSubmenu()||this.disabled||(this.notifyParentOfOpening(),this.submenuOpen=!0)},this.handleHostClick=e=>{this.disabled&&(e.preventDefault(),e.stopImmediatePropagation())}}static{this.styles=so}connectedCallback(){super.connectedCallback(),this.syncRole(),this.syncSubmenuAria(),this.addEventListener(`click`,this.handleHostClick),this.addEventListener(`mouseenter`,this.handleMouseEnter)}disconnectedCallback(){this.removeEventListener(`click`,this.handleHostClick),this.removeEventListener(`mouseenter`,this.handleMouseEnter),this.closeSubmenu(),super.disconnectedCallback()}updated(e){(e.has(`type`)||e.has(`checked`))&&this.syncRole(),(e.has(`submenuOpen`)||e.size===0)&&this.syncSubmenuAria(),e.has(`submenuOpen`)&&(this.submenuOpen?this.ensureSubmenuSurface():this.submenuAnimated=!1)}hasSubmenu(){return this.hasSlotController.test(`submenu`)}syncSubmenuAria(){let e=this.hasSubmenu();e?this.setAttribute(`aria-haspopup`,`menu`):this.removeAttribute(`aria-haspopup`),this.setAttribute(`aria-expanded`,e&&this.submenuOpen?`true`:`false`)}focusControl(){this.shadowRoot?.querySelector(`.item`)?.focus({preventScroll:!0})}focus(e){let t=this.shadowRoot?.querySelector(`.item`);if(t){t.focus(e);return}super.focus(e)}get submenuElement(){return this.submenuPanelElement??null}closeSubmenu(){this.submenuAnimated=!1,this.submenuOpen=!1}openSubmenu(){!this.hasSubmenu()||this.disabled||!this.isConnected||(this.notifyParentOfOpening(),this.submenuOpen=!0)}notifyParentOfOpening(){this.dispatchEvent(new CustomEvent(`pk-submenu-open`,{bubbles:!0,composed:!0,detail:{item:this}}));let e=this.parentElement;if(e)for(let t of e.children)t!==this&&t instanceof co&&t.getAttribute(`slot`)===this.getAttribute(`slot`)&&t.submenuOpen&&(t.submenuOpen=!1)}ensureSubmenuSurface(){!this.hasSubmenu()||this.disabled||(this.submenuAnimated=!0,this.updateComplete.then(()=>{!this.submenuOpen||!this.submenuPanelElement||(this.submenuPanelElement.hidden=!1,ye(this.submenuPanelElement,`right-start`),ve(this.submenuPopupElement,`right-start`).then(e=>{ye(this.submenuPanelElement,e)}))}))}syncRole(){if(this.type===`checkbox`){this.setAttribute(`role`,`menuitemcheckbox`),this.setAttribute(`aria-checked`,this.checked?`true`:`false`);return}if(this.type===`radio`){this.setAttribute(`role`,`menuitemradio`),this.setAttribute(`aria-checked`,this.checked?`true`:`false`);return}this.setAttribute(`role`,`menuitem`),this.removeAttribute(`aria-checked`)}handleClick(e){if(this.disabled){e.preventDefault(),e.stopImmediatePropagation();return}this.hasSubmenu()&&(e.preventDefault(),this.openSubmenu())}render(){let e=this.hasSubmenu(),t=this.type===`checkbox`||this.type===`radio`,n=this.hasSlotController.test(`start`)||this.hasSlotController.test(`prefix`);return v`
            <button
                part="item"
                type="button"
                class="item"
                ?disabled=${this.disabled}
                aria-disabled=${this.disabled?`true`:T}
                @click=${this.handleClick}
            >
                <span
                    part="prefix"
                    class=${n?`prefix`:`prefix prefix--empty`}
                >
                    <slot name="start"></slot>
                    <slot name="prefix"></slot>
                </span>
                <span class="label"><slot></slot></span>
                <span class="details"><slot name="details"></slot></span>
                ${t?v`<span class="check" aria-hidden="true">${Te(lo)}</span>`:T}
                ${e?v`<span class="submenu-icon" aria-hidden="true">${Te(uo)}</span>`:T}
            </button>
            ${e?v`
                <pk-popup
                    .active=${this.submenuOpen}
                    .anchor=${this}
                    placement="right-start"
                    .distance=${0}
                    .skidding=${-4}
                    flip
                    shift
                    hover-bridge
                    style="--pk-popup-z-index: 1001"
                >
                    <div
                        part="submenu"
                        class="submenu-panel pk-popup-content"
                        role="menu"
                        data-size=${fo(this)}
                        ?hidden=${!this.submenuOpen}
                        data-open=${this.submenuAnimated?``:T}
                        aria-orientation="vertical"
                    >
                        <slot name="submenu"></slot>
                    </div>
                </pk-popup>
            `:T}
        `}};s([c()],z.prototype,`value`,void 0),s([c({reflect:!0})],z.prototype,`type`,void 0),s([c({attribute:`radio-group`})],z.prototype,`radioGroup`,void 0),s([c({type:Boolean,reflect:!0})],z.prototype,`disabled`,void 0),s([c({type:Boolean,reflect:!0})],z.prototype,`destructive`,void 0),s([c({type:Boolean,reflect:!0})],z.prototype,`checked`,void 0),s([c({attribute:`submenu-open`,type:Boolean,reflect:!0})],z.prototype,`submenuOpen`,void 0),s([c({type:Boolean})],z.prototype,`active`,void 0),s([g()],z.prototype,`submenuAnimated`,void 0),s([r(`.submenu-panel`)],z.prototype,`submenuPanelElement`,void 0),s([r(`pk-popup`)],z.prototype,`submenuPopupElement`,void 0),z=co=s([h(`pk-dropdown-item`)],z);function fo(e){let t=e.parentElement?.getAttribute(`data-size`);if(t===`xs`||t===`sm`||t==="default"||t===`lg`||t===`xl`)return t;let n=e.closest(`pk-dropdown-menu`)?.getAttribute(`size`);return n===`xs`||n===`sm`||n===`lg`||n===`xl`?n:`default`}var po=y`
    @layer pk-component {
        :host {
            display: block;
            /*
             * Slotted label copy inherits through the flat tree from this host
             * when page metrics would otherwise leak via font:inherit chains.
             */
            font-size: var(--pk-dropdown-label-font-size, 13px);
            line-height: 1.3;
            color: var(--pk-color-slate-700, rgba(96, 125, 159, 0.7));
        }

        /* Match v1 DropdownMenuLabel — text-slate-700, regular weight (not medium). */
        .label {
            margin: 0;
            padding-block-start: 6px;
            padding-block-end: 4px;
            padding-inline: var(--pk-dropdown-label-padding-inline, 12px);
            color: inherit;
            font: inherit;
            font-weight: 400;
            user-select: none;
            pointer-events: none;
        }
    }
`,B=class extends _{static{this.styles=po}connectedCallback(){super.connectedCallback(),this.setAttribute(`role`,`presentation`)}render(){return v`
            <div part="label" class="label">
                <slot></slot>
            </div>
        `}};B=s([h(`pk-dropdown-label`)],B);function*V(e=document.activeElement){e!=null&&(yield e,`shadowRoot`in e&&e.shadowRoot&&e.shadowRoot.mode!==`closed`&&(yield*V(e.shadowRoot.activeElement)))}var H=Math.min,mo=Math.max,ho=Math.round,go=Math.floor,_o=e=>({x:e,y:e}),vo={left:`right`,right:`left`,bottom:`top`,top:`bottom`};function yo(e,t,n){return mo(e,H(t,n))}function bo(e,t){return typeof e==`function`?e(t):e}function U(e){return e.split(`-`)[0]}function xo(e){return e.split(`-`)[1]}function So(e){return e===`x`?`y`:`x`}function Co(e){return e===`y`?`height`:`width`}function wo(e){let t=e[0];return t===`t`||t===`b`?`y`:`x`}function To(e){return So(wo(e))}function Eo(e,t,n){n===void 0&&(n=!1);let r=xo(e),i=To(e),a=Co(i),o=i===`x`?r===(n?`end`:`start`)?`right`:`left`:r===`start`?`bottom`:`top`;return t.reference[a]>t.floating[a]&&(o=Fo(o)),[o,Fo(o)]}function Do(e){let t=Fo(e);return[Oo(e),t,Oo(t)]}function Oo(e){return e.includes(`start`)?e.replace(`start`,`end`):e.replace(`end`,`start`)}var ko=[`left`,`right`],Ao=[`right`,`left`],jo=[`top`,`bottom`],Mo=[`bottom`,`top`];function No(e,t,n){switch(e){case`top`:case`bottom`:return n?t?Ao:ko:t?ko:Ao;case`left`:case`right`:return t?jo:Mo;default:return[]}}function Po(e,t,n,r){let i=xo(e),a=No(U(e),n===`start`,r);return i&&(a=a.map(e=>e+`-`+i),t&&(a=a.concat(a.map(Oo)))),a}function Fo(e){let t=U(e);return vo[t]+e.slice(t.length)}function Io(e){return{top:e.top??0,right:e.right??0,bottom:e.bottom??0,left:e.left??0}}function Lo(e){return typeof e==`number`?{top:e,right:e,bottom:e,left:e}:Io(e)}function Ro(e){let{x:t,y:n,width:r,height:i}=e;return{width:r,height:i,top:n,left:t,right:t+r,bottom:n+i,x:t,y:n}}function zo(e,t,n){let{reference:r,floating:i}=e,a=wo(t),o=To(t),s=Co(o),c=U(t),l=a===`y`,u=r.x+r.width/2-i.width/2,d=r.y+r.height/2-i.height/2,f=r[s]/2-i[s]/2,p;switch(c){case`top`:p={x:u,y:r.y-i.height};break;case`bottom`:p={x:u,y:r.y+r.height};break;case`right`:p={x:r.x+r.width,y:d};break;case`left`:p={x:r.x-i.width,y:d};break;default:p={x:r.x,y:r.y}}let m=xo(t);return m&&(p[o]+=f*(m===`end`?1:-1)*(n&&l?-1:1)),p}async function Bo(e,t){t===void 0&&(t={});let{x:n,y:r,platform:i,rects:a,elements:o,strategy:s}=e,{boundary:c=`clippingAncestors`,rootBoundary:l=`viewport`,elementContext:u=`floating`,altBoundary:d=!1,padding:f=0}=bo(t,e),p=Lo(f),m=o[d?u===`floating`?`reference`:`floating`:u],h=Ro(await i.getClippingRect({element:await(i.isElement==null?void 0:i.isElement(m))??!0?m:m.contextElement||await(i.getDocumentElement==null?void 0:i.getDocumentElement(o.floating)),boundary:c,rootBoundary:l,strategy:s})),g=u===`floating`?{x:n,y:r,width:a.floating.width,height:a.floating.height}:a.reference,_=await(i.getOffsetParent==null?void 0:i.getOffsetParent(o.floating)),v=await(i.isElement==null?void 0:i.isElement(_))&&await(i.getScale==null?void 0:i.getScale(_))||{x:1,y:1},y=Ro(i.convertOffsetParentRelativeRectToViewportRelativeRect?await i.convertOffsetParentRelativeRectToViewportRelativeRect({elements:o,rect:g,offsetParent:_,strategy:s}):g);return{top:(h.top-y.top+p.top)/v.y,bottom:(y.bottom-h.bottom+p.bottom)/v.y,left:(h.left-y.left+p.left)/v.x,right:(y.right-h.right+p.right)/v.x}}var Vo=50,Ho=async(e,t,n)=>{let{placement:r=`bottom`,strategy:i=`absolute`,middleware:a=[],platform:o}=n,s=o.detectOverflow?o:{...o,detectOverflow:Bo},c=await(o.isRTL==null?void 0:o.isRTL(t)),l=await o.getElementRects({reference:e,floating:t,strategy:i}),{x:u,y:d}=zo(l,r,c),f=r,p=0,m={};for(let n=0;n<a.length;n++){let h=a[n];if(!h)continue;let{name:g,fn:_}=h,{x:v,y,data:b,reset:x}=await _({x:u,y:d,initialPlacement:r,placement:f,strategy:i,middlewareData:m,rects:l,platform:s,elements:{reference:e,floating:t}});u=v??u,d=y??d,m[g]={...m[g],...b},x&&p<Vo&&(p++,typeof x==`object`&&(x.placement&&(f=x.placement),x.rects&&(l=x.rects===!0?await o.getElementRects({reference:e,floating:t,strategy:i}):x.rects),{x:u,y:d}=zo(l,f,c)),n=-1)}return{x:u,y:d,placement:f,strategy:i,middlewareData:m}},Uo=e=>({name:`arrow`,options:e,async fn(t){let{x:n,y:r,placement:i,rects:a,platform:o,elements:s,middlewareData:c}=t,{element:l,padding:u=0}=bo(e,t)||{};if(l==null)return{};let d=Lo(u),f={x:n,y:r},p=To(i),m=Co(p),h=await o.getDimensions(l),g=p===`y`,_=g?`top`:`left`,v=g?`bottom`:`right`,y=g?`clientHeight`:`clientWidth`,b=a.reference[m]+a.reference[p]-f[p]-a.floating[m],x=f[p]-a.reference[p],ee=await(o.getOffsetParent==null?void 0:o.getOffsetParent(l)),S=ee?ee[y]:0;(!S||!await(o.isElement==null?void 0:o.isElement(ee)))&&(S=s.floating[y]||a.floating[m]);let C=b/2-x/2,w=S/2-h[m]/2-1,te=H(d[_],w),ne=H(d[v],w),re=S-h[m]-ne,ie=S/2-h[m]/2+C,ae=yo(te,ie,re),oe=!c.arrow&&xo(i)!=null&&ie!==ae&&a.reference[m]/2-(ie<te?te:ne)-h[m]/2<0,se=oe?ie<te?ie-te:ie-re:0;return{[p]:f[p]+se,data:{[p]:ae,centerOffset:ie-ae-se,...oe&&{alignmentOffset:se}},reset:oe}}}),Wo=function(e){return e===void 0&&(e={}),{name:`flip`,options:e,async fn(t){var n;let{placement:r,middlewareData:i,rects:a,initialPlacement:o,platform:s,elements:c}=t,{mainAxis:l=!0,crossAxis:u=!0,fallbackPlacements:d,fallbackStrategy:f=`bestFit`,fallbackAxisSideDirection:p=`none`,flipAlignment:m=!0,...h}=bo(e,t);if((n=i.arrow)!=null&&n.alignmentOffset)return{};let g=U(r),_=wo(o),v=U(o)===o,y=await(s.isRTL==null?void 0:s.isRTL(c.floating)),b=d||(v||!m?[Fo(o)]:Do(o)),x=p!==`none`;!d&&x&&b.push(...Po(o,m,p,y));let ee=[o,...b],S=await s.detectOverflow(t,h),C=[],w=i.flip?.overflows||[];if(l&&C.push(S[g]),u){let e=Eo(r,a,y);C.push(S[e[0]],S[e[1]])}if(w=[...w,{placement:r,overflows:C}],!C.every(e=>e<=0)){let e=(i.flip?.index||0)+1,t=ee[e];if(t&&(!(u===`alignment`&&_!==wo(t))||w.every(e=>wo(e.placement)!==_||e.overflows[0]>0)))return{data:{index:e,overflows:w},reset:{placement:t}};let n=w.filter(e=>e.overflows[0]<=0).sort((e,t)=>e.overflows[1]-t.overflows[1])[0]?.placement;if(!n)switch(f){case`bestFit`:{let e=w.filter(e=>{if(x){let t=wo(e.placement);return t===_||t===`y`}return!0}).map(e=>[e.placement,e.overflows.filter(e=>e>0).reduce((e,t)=>e+t,0)]).sort((e,t)=>e[1]-t[1])[0]?.[0];e&&(n=e);break}case`initialPlacement`:n=o;break}if(r!==n)return{reset:{placement:n}}}return{}}}},Go=new Set([`left`,`top`]);async function Ko(e,t){let{placement:n,platform:r,elements:i}=e,a=await(r.isRTL==null?void 0:r.isRTL(i.floating)),o=U(n),s=xo(n),c=wo(n)===`y`,l=Go.has(o)?-1:1,u=a&&c?-1:1,d=bo(t,e),{mainAxis:f,crossAxis:p,alignmentAxis:m}=typeof d==`number`?{mainAxis:d,crossAxis:0,alignmentAxis:null}:{mainAxis:d.mainAxis||0,crossAxis:d.crossAxis||0,alignmentAxis:d.alignmentAxis};return s&&typeof m==`number`&&(p=s===`end`?m*-1:m),c?{x:p*u,y:f*l}:{x:f*l,y:p*u}}var qo=function(e){return e===void 0&&(e=0),{name:`offset`,options:e,async fn(t){var n;let{x:r,y:i,placement:a,middlewareData:o}=t,s=await Ko(t,e);return a===o.offset?.placement&&(n=o.arrow)!=null&&n.alignmentOffset?{}:{x:r+s.x,y:i+s.y,data:{...s,placement:a}}}}},Jo=function(e){return e===void 0&&(e={}),{name:`shift`,options:e,async fn(t){let{x:n,y:r,placement:i,platform:a}=t,{mainAxis:o=!0,crossAxis:s=!1,limiter:c={fn:e=>{let{x:t,y:n}=e;return{x:t,y:n}}},...l}=bo(e,t),u={x:n,y:r},d=await a.detectOverflow(t,l),f=wo(i),p=So(f),m=u[p],h=u[f],g=(e,t)=>yo(t+d[e===`y`?`top`:`left`],t,t-d[e===`y`?`bottom`:`right`]);o&&(m=g(p,m)),s&&(h=g(f,h));let _=c.fn({...t,[p]:m,[f]:h});return{..._,data:{x:_.x-n,y:_.y-r,enabled:{[p]:o,[f]:s}}}}}},Yo=function(e){return e===void 0&&(e={}),{name:`size`,options:e,async fn(t){let{placement:n,rects:r,platform:i,elements:a}=t,{apply:o=()=>{},...s}=bo(e,t),c=await i.detectOverflow(t,s),l=U(n),u=xo(n),d=wo(n)===`y`,{width:f,height:p}=r.floating,m,h;l===`top`||l===`bottom`?(m=l,h=u===(await(i.isRTL==null?void 0:i.isRTL(a.floating))?`start`:`end`)?`left`:`right`):(h=l,m=u===`end`?`top`:`bottom`);let g=p-c.top-c.bottom,_=f-c.left-c.right,v=H(p-c[m],g),y=H(f-c[h],_),b=t.middlewareData.shift,x=!b,ee=v,S=y;b!=null&&b.enabled.x&&(S=_),b!=null&&b.enabled.y&&(ee=g),x&&!u&&(d?S=f-2*mo(c.left,c.right):ee=p-2*mo(c.top,c.bottom)),await o({...t,availableWidth:S,availableHeight:ee});let C=await i.getDimensions(a.floating);return f!==C.width||p!==C.height?{reset:{rects:!0}}:{}}}};function Xo(){return typeof window<`u`}function Zo(e){return es(e)?(e.nodeName||``).toLowerCase():`#document`}function Qo(e){var t;return(e==null||(t=e.ownerDocument)==null?void 0:t.defaultView)||window}function $o(e){return((es(e)?e.ownerDocument:e.document)||window.document)?.documentElement}function es(e){return Xo()?e instanceof Node||e instanceof Qo(e).Node:!1}function ts(e){return Xo()?e instanceof Element||e instanceof Qo(e).Element:!1}function ns(e){return Xo()?e instanceof HTMLElement||e instanceof Qo(e).HTMLElement:!1}function rs(e){return!Xo()||typeof ShadowRoot>`u`?!1:e instanceof ShadowRoot||e instanceof Qo(e).ShadowRoot}function is(e){let{overflow:t,overflowX:n,overflowY:r,display:i}=hs(e);return/auto|scroll|overlay|hidden|clip/.test(t+r+n)&&i!==`inline`&&i!==`contents`}function as(e){return/^(table|td|th)$/.test(Zo(e))}function os(e){try{if(e.matches(`:popover-open`))return!0}catch{}try{return e.matches(`:modal`)}catch{return!1}}var ss=/transform|translate|scale|rotate|perspective|filter/,cs=/paint|layout|strict|content/,ls=e=>!!e&&e!==`none`,us;function ds(e){let t=ts(e)?hs(e):e;return ls(t.transform)||ls(t.translate)||ls(t.scale)||ls(t.rotate)||ls(t.perspective)||!ps()&&(ls(t.backdropFilter)||ls(t.filter))||ss.test(t.willChange||``)||cs.test(t.contain||``)}function fs(e){let t=_s(e);for(;ns(t)&&!ms(t);){if(ds(t))return t;if(os(t))return null;t=_s(t)}return null}function ps(){return us??=typeof CSS<`u`&&CSS.supports&&CSS.supports(`-webkit-backdrop-filter`,`none`),us}function ms(e){return/^(html|body|#document)$/.test(Zo(e))}function hs(e){return Qo(e).getComputedStyle(e)}function gs(e){return ts(e)?{scrollLeft:e.scrollLeft,scrollTop:e.scrollTop}:{scrollLeft:e.scrollX,scrollTop:e.scrollY}}function _s(e){if(Zo(e)===`html`)return e;let t=e.assignedSlot||e.parentNode||rs(e)&&e.host||$o(e);return rs(t)?t.host:t}function vs(e){let t=_s(e);return ms(t)?(e.ownerDocument||e).body:ns(t)&&is(t)?t:vs(t)}function ys(e,t,n){t===void 0&&(t=[]),n===void 0&&(n=!0);let r=vs(e),i=r===e.ownerDocument?.body,a=Qo(r);if(i){let e=bs(a);return t.concat(a,a.visualViewport||[],is(r)?r:[],e&&n?ys(e):[])}else return t.concat(r,ys(r,[],n))}function bs(e){return e.parent&&Object.getPrototypeOf(e.parent)?e.frameElement:null}function xs(e){let t=hs(e),n=parseFloat(t.width)||0,r=parseFloat(t.height)||0,i=ns(e),a=i?e.offsetWidth:n,o=i?e.offsetHeight:r,s=ho(n)!==a||ho(r)!==o;return s&&(n=a,r=o),{width:n,height:r,$:s}}function Ss(e){return ts(e)?e:e.contextElement}function Cs(e){let t=Ss(e);if(!ns(t))return _o(1);let n=t.getBoundingClientRect(),{width:r,height:i,$:a}=xs(t),o=(a?ho(n.width):n.width)/r,s=(a?ho(n.height):n.height)/i;return(!o||!Number.isFinite(o))&&(o=1),(!s||!Number.isFinite(s))&&(s=1),{x:o,y:s}}var ws=_o(0);function Ts(e){let t=Qo(e);return!ps()||!t.visualViewport?ws:{x:t.visualViewport.offsetLeft,y:t.visualViewport.offsetTop}}function Es(e,t,n){return t===void 0&&(t=!1),!!n&&t&&n===Qo(e)}function Ds(e,t,n,r){t===void 0&&(t=!1),n===void 0&&(n=!1);let i=e.getBoundingClientRect(),a=Ss(e),o=_o(1);t&&(r?ts(r)&&(o=Cs(r)):o=Cs(e));let s=Es(a,n,r)?Ts(a):_o(0),c=(i.left+s.x)/o.x,l=(i.top+s.y)/o.y,u=i.width/o.x,d=i.height/o.y;if(a&&r){let e=Qo(a),t=ts(r)?Qo(r):r,n=e,i=bs(n);for(;i&&t!==n;){let e=Cs(i),t=i.getBoundingClientRect(),r=hs(i),a=t.left+(i.clientLeft+parseFloat(r.paddingLeft))*e.x,o=t.top+(i.clientTop+parseFloat(r.paddingTop))*e.y;c*=e.x,l*=e.y,u*=e.x,d*=e.y,c+=a,l+=o,n=Qo(i),i=bs(n)}}return Ro({width:u,height:d,x:c,y:l})}function Os(e,t){let n=gs(e).scrollLeft;return t?t.left+n:Ds($o(e)).left+n}function ks(e,t){let n=e.getBoundingClientRect();return{x:n.left+t.scrollLeft-Os(e,n),y:n.top+t.scrollTop}}function As(e){let{elements:t,rect:n,offsetParent:r,strategy:i}=e,a=i===`fixed`,o=$o(r),s=t?os(t.floating):!1;if(r===o||s&&a)return n;let c={scrollLeft:0,scrollTop:0},l=_o(1),u=_o(0),d=ns(r);if((d||!a)&&((Zo(r)!==`body`||is(o))&&(c=gs(r)),d)){let e=Ds(r);l=Cs(r),u.x=e.x+r.clientLeft,u.y=e.y+r.clientTop}let f=o&&!d&&!a?ks(o,c):_o(0);return{width:n.width*l.x,height:n.height*l.y,x:n.x*l.x-c.scrollLeft*l.x+u.x+f.x,y:n.y*l.y-c.scrollTop*l.y+u.y+f.y}}function js(e){return e.getClientRects?Array.from(e.getClientRects()):[]}function Ms(e){let t=gs(e),n=e.ownerDocument.body,r=mo(e.scrollWidth,e.clientWidth,n.scrollWidth,n.clientWidth),i=mo(e.scrollHeight,e.clientHeight,n.scrollHeight,n.clientHeight),a=-t.scrollLeft+Os(e),o=-t.scrollTop;return hs(n).direction===`rtl`&&(a+=mo(e.clientWidth,n.clientWidth)-r),{width:r,height:i,x:a,y:o}}var Ns=25;function Ps(e,t,n){n===void 0&&(n=`viewport`);let r=n===`layoutViewport`,i=Qo(e),a=$o(e),o=i.visualViewport,s=a.clientWidth,c=a.clientHeight,l=0,u=0;if(o){let e=!ps()||t===`fixed`;r?e||(l=-o.offsetLeft,u=-o.offsetTop):(s=o.width,c=o.height,e&&(l=o.offsetLeft,u=o.offsetTop))}if(Os(a)<=0){let e=a.ownerDocument,t=e.body,n=getComputedStyle(t),r=e.compatMode===`CSS1Compat`&&parseFloat(n.marginLeft)+parseFloat(n.marginRight)||0,i=Math.abs(a.clientWidth-t.clientWidth-r),o=getComputedStyle(a).scrollbarGutter===`stable both-edges`?i/2:i;o<=Ns&&(s-=o)}return{width:s,height:c,x:l,y:u}}function Fs(e,t){let n=Ds(e,!0,t===`fixed`),r=n.top+e.clientTop,i=n.left+e.clientLeft,a=Cs(e);return{width:e.clientWidth*a.x,height:e.clientHeight*a.y,x:i*a.x,y:r*a.y}}function Is(e,t,n){let r;if(t===`viewport`||t===`layoutViewport`)r=Ps(e,n,t);else if(t===`document`)r=Ms($o(e));else if(ts(t))r=Fs(t,n);else{let n=Ts(e);r={x:t.x-n.x,y:t.y-n.y,width:t.width,height:t.height}}return Ro(r)}function Ls(e,t){let n=t.get(e);if(n)return n;let r=ys(e,[],!1).filter(e=>ts(e)&&Zo(e)!==`body`),i=null,a=hs(e).position===`fixed`,o=a?_s(e):e;for(;ts(o)&&!ms(o);){let e=hs(o),t=ds(o),n=i?i.position:a?`fixed`:``;!t&&(n===`fixed`||n===`absolute`&&e.position===`static`)?r=r.filter(e=>e!==o):i=e,o=_s(o)}return t.set(e,r),r}function Rs(e){let{element:t,boundary:n,rootBoundary:r,strategy:i}=e,a=[...n===`clippingAncestors`?os(t)?[]:Ls(t,this._c):[].concat(n),r],o=Is(t,a[0],i),s=o.top,c=o.right,l=o.bottom,u=o.left;for(let e=1;e<a.length;e++){let n=Is(t,a[e],i);s=mo(n.top,s),c=H(n.right,c),l=H(n.bottom,l),u=mo(n.left,u)}return{width:c-u,height:l-s,x:u,y:s}}function zs(e){let{width:t,height:n}=xs(e);return{width:t,height:n}}function Bs(e,t,n){let r=ns(t),i=$o(t),a=n===`fixed`,o=Ds(e,!0,a,t),s={scrollLeft:0,scrollTop:0},c=_o(0);if((r||!a)&&((Zo(t)!==`body`||is(i))&&(s=gs(t)),r)){let e=Ds(t,!0,a,t);c.x=e.x+t.clientLeft,c.y=e.y+t.clientTop}!r&&i&&(c.x=Os(i));let l=i&&!r&&!a?ks(i,s):_o(0);return{x:o.left+s.scrollLeft-c.x-l.x,y:o.top+s.scrollTop-c.y-l.y,width:o.width,height:o.height}}function Vs(e){return hs(e).position===`static`}function Hs(e,t){if(!ns(e)||hs(e).position===`fixed`)return null;if(t)return t(e);let n=e.offsetParent;return $o(e)===n&&(n=n.ownerDocument.body),n}function Us(e,t){let n=Qo(e);if(os(e))return n;if(!ns(e)){let t=_s(e);for(;t&&!ms(t);){if(ts(t)&&!Vs(t))return t;t=_s(t)}return n}let r=Hs(e,t);for(;r&&as(r)&&Vs(r);)r=Hs(r,t);return r&&ms(r)&&Vs(r)&&!ds(r)?n:r||fs(e)||n}var Ws=async function(e){let t=this.getOffsetParent||Us,n=this.getDimensions,r=await n(e.floating);return{reference:Bs(e.reference,await t(e.floating),e.strategy),floating:{x:0,y:0,width:r.width,height:r.height}}};function Gs(e){return hs(e).direction===`rtl`}var Ks={convertOffsetParentRelativeRectToViewportRelativeRect:As,getDocumentElement:$o,getClippingRect:Rs,getOffsetParent:Us,getElementRects:Ws,getClientRects:js,getDimensions:zs,getScale:Cs,isElement:ts,isRTL:Gs};function qs(e,t){return e.x===t.x&&e.y===t.y&&e.width===t.width&&e.height===t.height}function Js(e,t,n){let r=null,i,a=$o(e);function o(){var e;clearTimeout(i),(e=r)==null||e.disconnect(),r=null}function s(n,c){n===void 0&&(n=!1),c===void 0&&(c=1),o();let l=e.getBoundingClientRect(),{left:u,top:d,width:f,height:p}=l;if(n||t(),!f||!p)return;let m=go(d),h=go(a.clientWidth-(u+f)),g=go(a.clientHeight-(d+p)),_=go(u),v={rootMargin:-m+`px `+-h+`px `+-g+`px `+-_+`px`,threshold:mo(0,H(1,c))||1},y=!0;function b(t){let n=t[0].intersectionRatio;if(!qs(l,e.getBoundingClientRect()))return s();if(n!==c){if(!y)return s();n?s(!1,n):i=setTimeout(()=>{s(!1,1e-7)},1e3)}y=!1}try{r=new IntersectionObserver(b,{...v,root:a.ownerDocument})}catch{r=new IntersectionObserver(b,v)}r.observe(e)}let c=Qo(e),l=()=>s(n);return c.addEventListener(`resize`,l),s(!0),()=>{c.removeEventListener(`resize`,l),o()}}function Ys(e,t,n,r){r===void 0&&(r={});let{ancestorScroll:i=!0,ancestorResize:a=!0,elementResize:o=typeof ResizeObserver==`function`,layoutShift:s=typeof IntersectionObserver==`function`,animationFrame:c=!1}=r,l=Ss(e),u=i||a?[...l?ys(l):[],...t?ys(t):[]]:[];u.forEach(e=>{i&&e.addEventListener(`scroll`,n),a&&e.addEventListener(`resize`,n)});let d=l&&s?Js(l,n,a):null,f=-1,p=null;o&&(p=new ResizeObserver(e=>{let[r]=e;r&&r.target===l&&p&&t&&(p.unobserve(t),cancelAnimationFrame(f),f=requestAnimationFrame(()=>{var e;(e=p)==null||e.observe(t)})),n()}),l&&!c&&p.observe(l),t&&p.observe(t));let m,h=c?Ds(e):null;c&&g();function g(){let t=Ds(e);h&&!qs(h,t)&&n(),h=t,m=requestAnimationFrame(g)}return n(),()=>{var e;u.forEach(e=>{i&&e.removeEventListener(`scroll`,n),a&&e.removeEventListener(`resize`,n)}),d?.(),(e=p)==null||e.disconnect(),p=null,c&&cancelAnimationFrame(m)}}var Xs=qo,Zs=Jo,Qs=Wo,$s=Yo,ec=Uo,tc=(e,t,n)=>{let r=new Map,i=n??{},a={...Ks,...i.platform,_c:r};return Ho(e,t,{...i,platform:a})};function nc(e){return ic(e)}function rc(e){return e.assignedSlot?e.assignedSlot:e.parentNode instanceof ShadowRoot?e.parentNode.host:e.parentNode}function ic(e){for(let t=e;t;t=rc(t))if(t instanceof Element&&getComputedStyle(t).display===`none`)return null;for(let t=rc(e);t;t=rc(t)){if(!(t instanceof Element))continue;let e=getComputedStyle(t);if(e.display!==`contents`&&(e.position!==`static`||ds(e)||t.tagName===`BODY`))return t}return null}function ac(e,t){if(!t)return null;let n=e.getRootNode();if(n instanceof Document||n instanceof ShadowRoot){let e=n.getElementById(t);if(e)return e}return e.ownerDocument.getElementById(t)}var oc=class extends Event{constructor(){super(`pk-reposition`,{bubbles:!0,cancelable:!1,composed:!0})}},sc=y`
    @layer pk-component {
        :host {
            display: contents;
        }

        .popup {
            position: absolute;
            isolation: isolate;
            width: max-content;
            z-index: var(--pk-popup-z-index, 1000);
            /* Never transition coordinates — flip would animate the jump. */
            transition: none;

            /* Reset UA styles for [popover] — see  pk-popup. */
            inset: unset;
            padding: unset;
            margin: unset;
            height: unset;
            color: unset;
            background: unset;
            border: unset;
            overflow: unset;
        }

        .popup-fixed {
            position: fixed;
        }

        .popup:not(.active) {
            display: none;
        }

        /* Prefer visibility over opacity so enter animations are not fighting a
         * 0→1 fade. Matches base-ui isPositioned / hide-until-placed.
         */
        .popup.active:not(.positioned) {
            visibility: hidden;
            pointer-events: none;
        }

        .popup.show {
            animation: pk-popup-surface-in 100ms ease-out;
        }

        .popup.hide {
            animation: pk-popup-surface-out 100ms ease-in forwards;
        }

        @keyframes pk-popup-surface-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pk-popup-surface-out {
            from {
                opacity: 1;
                transform: scale(1);
            }

            to {
                opacity: 0;
                transform: scale(0.95);
            }
        }

        .arrow {
            position: absolute;
            width: var(--pk-popup-arrow-size, 6px);
            height: var(--pk-popup-arrow-size, 6px);
            rotate: 45deg;
            background: var(--pk-popup-arrow-color, var(--pk-color-white));
            z-index: 1;
        }

        .hover-bridge {
            position: fixed;
            z-index: calc(var(--pk-popup-z-index, 1000) - 1);
            inset: 0;
            clip-path: polygon(
                var(--pk-hover-bridge-top-left-x, 0) var(--pk-hover-bridge-top-left-y, 0),
                var(--pk-hover-bridge-top-right-x, 0) var(--pk-hover-bridge-top-right-y, 0),
                var(--pk-hover-bridge-bottom-right-x, 0) var(--pk-hover-bridge-bottom-right-y, 0),
                var(--pk-hover-bridge-bottom-left-x, 0) var(--pk-hover-bridge-bottom-left-y, 0)
            );
            pointer-events: auto;
        }

        .hover-bridge:not(.hover-bridge-visible) {
            display: none;
        }
    }
`;function cc(e){return typeof e==`object`&&!!e&&`getBoundingClientRect`in e}function lc(e){return e||(lt?`absolute`:`fixed`)}function uc(e,t){if(!(!lt||cc(e)||t!==`scroll`))return ys(e).filter(e=>e instanceof Element)}var W=class extends _{constructor(...e){super(...e),this.anchor=``,this.active=!1,this.boundary=`viewport`,this.placement=`bottom-start`,this.distance=4,this.skidding=0,this.flip=!0,this.flipFallbackPlacements=``,this.flipFallbackStrategy=`best-fit`,this.flipPadding=8,this.shift=!0,this.shiftPadding=8,this.arrow=!1,this.arrowPlacement=`anchor`,this.arrowPadding=10,this.anchorTracking=!0,this.hoverBridge=!1,this.anchorElement=null,this.settlingInitialPosition=!1,this.settleGeneration=0}static{this.styles=sc}disconnectedCallback(){this.stop(),super.disconnectedCallback()}updated(e){super.updated(e),e.has(`active`)&&(this.active?(this.resolveAnchor(),this.start()):this.stop()),e.has(`anchor`)&&this.handleAnchorChange(),this.active&&!e.has(`active`)&&this.reposition()}reposition(){this.settlingInitialPosition||this.repositionAsync()}async repositionAsync(e=!0){let t=this.popupElement,n=this.arrow?this.arrowElement:null;if(!this.active||!this.anchorElement||!t)return!1;let r=uc(this.anchorElement,this.boundary),i=[Xs({mainAxis:this.distance,crossAxis:this.skidding})];this.sync?i.push($s({apply:({rects:e})=>{let n=this.sync===`width`||this.sync===`both`,r=this.sync===`height`||this.sync===`both`;t.style.width=n?`${e.reference.width}px`:``,t.style.height=r?`${e.reference.height}px`:``}})):(t.style.width=``,t.style.height=``),this.flip&&i.push(Qs({boundary:r,fallbackPlacements:this.flipFallbackPlacements?this.flipFallbackPlacements.split(` `).map(e=>e.trim()).filter(Boolean):void 0,fallbackStrategy:this.flipFallbackStrategy===`best-fit`?`bestFit`:`initialPlacement`,padding:this.flipPadding})),this.shift&&i.push(Zs({boundary:r,padding:this.shiftPadding})),this.arrow&&n&&i.push(ec({element:n,padding:this.arrowPadding}));let a=lc(this.positionMethod),o=a===`fixed`;t.classList.toggle(`popup-fixed`,o);let s=lt?e=>Ks.getOffsetParent(e,nc):Ks.getOffsetParent,{x:c,y:l,middlewareData:u,placement:d}=await tc(this.anchorElement,t,{placement:this.placement,middleware:i,strategy:a,platform:{...Ks,getOffsetParent:s}});if(!this.active||!t.isConnected)return!1;let f={top:`bottom`,right:`left`,bottom:`top`,left:`right`}[d.split(`-`)[0]];if(this.setAttribute(`data-current-placement`,d),Object.assign(t.style,{left:`${c}px`,top:`${l}px`,...o?{position:`fixed`}:{position:``}}),this.anchorElement){let e=this.anchorElement.getBoundingClientRect(),n=t.getBoundingClientRect();t.style.setProperty(`--pk-anchor-width`,`${e.width}px`),t.style.setProperty(`--pk-anchor-height`,`${e.height}px`);let r=Se(d,e,n,this.distance,u.shift);t.style.setProperty(`--pk-transform-origin`,r)}if(this.arrow&&n){let e=u.arrow?.x,t=u.arrow?.y,r=``,i=``,a=``,o=``;if(this.arrowPlacement===`start`){let n=typeof e==`number`?`${this.arrowPadding}px`:``;r=typeof t==`number`?`${this.arrowPadding}px`:``,o=n}else this.arrowPlacement===`end`?(i=typeof e==`number`?`${this.arrowPadding}px`:``,a=typeof t==`number`?`${this.arrowPadding}px`:``):this.arrowPlacement===`center`?(o=typeof e==`number`?`50%`:``,r=typeof t==`number`?`50%`:``):(o=typeof e==`number`?`${e}px`:``,r=typeof t==`number`?`${t}px`:``);Object.assign(n.style,{top:r,right:i,bottom:a,left:o,transform:``,[f]:`calc(-1 * var(--pk-popup-arrow-size, 6px) / 2)`})}return requestAnimationFrame(()=>this.updateHoverBridge()),e&&this.dispatchEvent(new oc),!0}frames(e){return new Promise(t=>{let n=e=>{if(e<=0){t();return}requestAnimationFrame(()=>n(e-1))};n(e)})}async settleInitialPosition(){let e=++this.settleGeneration,t=this.popupElement;if(!t){this.settlingInitialPosition=!1;return}await this.frames(2),!(!this.active||e!==this.settleGeneration)&&(await this.repositionAsync(!1),t.offsetHeight,await this.frames(1),!(!this.active||e!==this.settleGeneration)&&(await this.repositionAsync(!1),!(!this.active||e!==this.settleGeneration)&&(t.classList.add(`positioned`),this.settlingInitialPosition=!1,requestAnimationFrame(()=>this.updateHoverBridge()),this.dispatchEvent(new oc))))}resolveAnchor(){if(typeof this.anchor==`string`&&this.anchor){this.anchorElement=ac(this,this.anchor);return}if(this.anchor instanceof Element||cc(this.anchor)){this.anchorElement=this.anchor;return}let e=this.querySelector(`[slot="anchor"]`);e instanceof HTMLSlotElement&&(e=e.assignedElements({flatten:!0})[0]??null),this.anchorElement=e}async handleAnchorChange(){await this.stop(),this.resolveAnchor(),this.anchorElement&&this.active&&this.start()}usesPopoverTopLayer(){return lt&&this.positionMethod!==`fixed`}stop(){return new Promise(e=>{let t=this.popupElement;this.settleGeneration+=1,this.settlingInitialPosition=!1,t?.classList.remove(`positioned`),this.usesPopoverTopLayer()&&t?.hidePopover?.(),this.cleanup?(this.cleanup(),this.cleanup=void 0,t?.style.removeProperty(`--pk-transform-origin`),requestAnimationFrame(()=>e())):e(),this.removeAttribute(`data-current-placement`)})}releasePositioning(){this.cleanup&&=(this.cleanup(),void 0)}async awaitHidden(){await this.stop()}start(){!this.anchorElement||!this.active||!this.isConnected||!this.popupElement||(this.popupElement.classList.remove(`positioned`),this.settlingInitialPosition=!0,this.usesPopoverTopLayer()&&this.popupElement.showPopover?.(),this.anchorTracking&&(this.cleanup=Ys(this.anchorElement,this.popupElement,()=>{this.settlingInitialPosition||this.reposition()})),this.settleInitialPosition())}getContentElement(){let e=((this.shadowRoot?.querySelector(`slot:not([name])`))?.assignedElements({flatten:!0})??[]).find(e=>e instanceof HTMLElement);if(e)return e;for(let e of this.childNodes)if(e instanceof HTMLElement&&e.getAttribute(`slot`)!==`anchor`)return e;return null}updateHoverBridge(){let e=this.popupElement;if(!this.hoverBridge||!this.anchorElement||!e)return;let t=this.anchorElement.getBoundingClientRect(),n=e.getBoundingClientRect(),r=this.placement.includes(`top`)||this.placement.includes(`bottom`),i=0,a=0,o=0,s=0,c=0,l=0,u=0,d=0;r?t.top<n.top?(i=t.left,a=t.bottom,o=t.right,s=t.bottom,c=n.left,l=n.top,u=n.right,d=n.top):(i=n.left,a=n.bottom,o=n.right,s=n.bottom,c=t.left,l=t.top,u=t.right,d=t.top):t.left<n.left?(i=t.right,a=t.top,o=n.left,s=n.top,c=t.right,l=t.bottom,u=n.left,d=n.bottom):(i=n.right,a=n.top,o=t.left,s=t.top,c=n.right,l=n.bottom,u=t.left,d=t.bottom),this.style.setProperty(`--pk-hover-bridge-top-left-x`,`${i}px`),this.style.setProperty(`--pk-hover-bridge-top-left-y`,`${a}px`),this.style.setProperty(`--pk-hover-bridge-top-right-x`,`${o}px`),this.style.setProperty(`--pk-hover-bridge-top-right-y`,`${s}px`),this.style.setProperty(`--pk-hover-bridge-bottom-left-x`,`${c}px`),this.style.setProperty(`--pk-hover-bridge-bottom-left-y`,`${l}px`),this.style.setProperty(`--pk-hover-bridge-bottom-right-x`,`${u}px`),this.style.setProperty(`--pk-hover-bridge-bottom-right-y`,`${d}px`)}render(){let e=!lt||this.positionMethod===`fixed`,t=this.usesPopoverTopLayer();return v`
            <slot name="anchor" @slotchange=${()=>{this.handleAnchorChange()}}></slot>
            ${this.hoverBridge?v`
                <div
                    part="hover-bridge"
                    class=${ae({"hover-bridge":!0,"hover-bridge-visible":this.active})}
                    aria-hidden="true"
                ></div>
            `:T}
            <div
                popover=${t?`manual`:T}
                part="popup"
                class=${ae({popup:!0,active:this.active,"popup-fixed":e})}
            >
                ${this.arrow?v`<div part="arrow" class="arrow"></div>`:T}
                <slot></slot>
            </div>
        `}};s([c()],W.prototype,`anchor`,void 0),s([c({type:Boolean,reflect:!0})],W.prototype,`active`,void 0),s([c({attribute:`position-method`})],W.prototype,`positionMethod`,void 0),s([c({reflect:!0})],W.prototype,`boundary`,void 0),s([c({reflect:!0})],W.prototype,`placement`,void 0),s([c({type:Number})],W.prototype,`distance`,void 0),s([c({type:Number})],W.prototype,`skidding`,void 0),s([c({type:Boolean})],W.prototype,`flip`,void 0),s([c({attribute:`flip-fallback-placements`})],W.prototype,`flipFallbackPlacements`,void 0),s([c({attribute:`flip-fallback-strategy`})],W.prototype,`flipFallbackStrategy`,void 0),s([c({attribute:`flip-padding`,type:Number})],W.prototype,`flipPadding`,void 0),s([c({type:Boolean})],W.prototype,`shift`,void 0),s([c({attribute:`shift-padding`,type:Number})],W.prototype,`shiftPadding`,void 0),s([c({type:Boolean})],W.prototype,`arrow`,void 0),s([c({attribute:`arrow-placement`})],W.prototype,`arrowPlacement`,void 0),s([c({attribute:`arrow-padding`,type:Number})],W.prototype,`arrowPadding`,void 0),s([c()],W.prototype,`sync`,void 0),s([c({attribute:`anchor-tracking`,type:Boolean})],W.prototype,`anchorTracking`,void 0),s([c({attribute:`hover-bridge`,type:Boolean})],W.prototype,`hoverBridge`,void 0),s([r(`.popup`)],W.prototype,`popupElement`,void 0),s([r(`.arrow`)],W.prototype,`arrowElement`,void 0),W=s([h(`pk-popup`)],W);function dc(e,t,n=500){return new Promise(r=>{let i=new AbortController,{signal:a}=i;if(e.classList.contains(t)){r();return}e.classList.add(t);let o=!1,s=()=>{o||(o=!0,e.classList.remove(t),window.clearTimeout(c),r(),i.abort())};e.addEventListener(`animationend`,s,{once:!0,signal:a}),e.addEventListener(`animationcancel`,s,{once:!0,signal:a});let c=window.setTimeout(s,n);requestAnimationFrame(()=>{!o&&e.getAnimations().length===0&&s()})})}var fc=[Re(),ao,oo,y`
        @layer pk-component {
            /* Standalone: keep a real box so the trigger is not a flex-stretched
               child of the page (display:contents flattened pk-button to full card width).
               Button groups override below — same as legacy + React MenuButton inline-flex wrap. */
            :host {
                display: inline-block;
                position: relative;
                width: fit-content;
                max-width: 100%;
                align-self: flex-start;
                vertical-align: middle;
            }

            :host([data-pk-group-orientation]) {
                display: inline-flex;
                vertical-align: middle;
                flex: 0 0 auto;
                width: auto;
                max-width: none;
                align-self: auto;
            }

            /* Belt-and-suspenders if a parent still flattens layout onto the trigger. */
            ::slotted([slot='trigger']) {
                width: fit-content;
                max-width: 100%;
                flex: 0 0 auto;
                align-self: flex-start;
            }

            :host([data-pk-group-orientation]) ::slotted([slot='trigger']) {
                --pk-bg-start-start-radius: inherit;
                --pk-bg-start-end-radius: inherit;
                --pk-bg-end-start-radius: inherit;
                --pk-bg-end-end-radius: inherit;
                align-self: auto;
                max-width: none;
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]) {
                margin-inline-start: var(--pk-bg-horizontal-indent, 0);
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-join]) {
                margin-block-start: var(--pk-bg-vertical-indent, 0);
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]:has([slot='trigger'][variant='outline'], [slot='trigger'][variant='dashed'])) {
                margin-inline-start: var(--pk-bg-horizontal-indent-outlined, 0);
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-join]:has([slot='trigger'][variant='outline'], [slot='trigger'][variant='dashed'])) {
                margin-block-start: var(--pk-bg-vertical-indent-outlined, 0);
            }

            /* Menu panel — hug content; do not stretch to trigger/anchor width. */
            .panel {
                display: flex;
                flex-direction: column;
                width: max-content;
                min-width: 8rem;
                margin: 0;
                overflow: auto;
                padding: 4px 0;
                border: 0;
                border-radius: var(--pk-radius-md);
                background: var(--pk-color-white);
                box-shadow: var(--pk-shadow-popup);
                /* v1 DropdownMenuItem had no face color — inherited Craft body
                 * (--text-color ≈ gray-700). Do not force gray-900 (too dark). */
                color: var(--text-color, var(--pk-color-gray-700));
                outline: none;
                text-align: start;
                user-select: none;
                /* Match v1 Base UI: popup writes --pk-transform-origin from the
                 * anchor center on the connecting edge (e.g. top-right for
                 * bottom-end). Keyword edge centers made end-aligned menus
                 * scale from the middle of the panel. */
                transform-origin: var(--pk-transform-origin, top);
            }

            .panel.show {
                animation: pk-dropdown-menu-show 100ms ease;
            }

            .panel.hide {
                animation: pk-dropdown-menu-show 100ms ease reverse;
            }

            .panel[hidden] {
                display: none !important;
            }

            ::slotted(pk-dropdown-item),
            ::slotted(pk-dropdown-separator),
            ::slotted(pk-dropdown-label),
            .panel > pk-dropdown-item,
            .panel > pk-dropdown-separator,
            .panel > pk-dropdown-label {
                display: block;
            }

            ::slotted([data-menu-item]) {
                display: flex;
                align-items: center;
                gap: 0.625rem;
                width: 100%;
                margin: 0;
                padding: 8px 12px;
                border: 0;
                background: transparent;
                color: inherit;
                font: inherit;
                font-size: var(--pk-font-size-base);
                text-align: left;
                white-space: nowrap;
                cursor: default;
                user-select: none;
                outline: none;
                box-sizing: border-box;
            }

            ::slotted([data-menu-item]:hover:not([disabled])) {
                background: var(--pk-color-slate-100);
            }

            ::slotted([data-menu-item]:focus-visible) {
                background: var(--pk-color-slate-100);
            }

            ::slotted([data-menu-item][disabled]) {
                pointer-events: none;
                opacity: 0.5;
            }

            ::slotted(pk-dropdown-item[destructive]),
            ::slotted([data-destructive]) {
                color: var(--pk-color-error);
            }

            ::slotted([data-menu-separator]) {
                display: block;
                height: 1px;
                margin: 4px 0;
                background: var(--pk-color-slate-200);
                border: 0;
                padding: 0;
            }
        }

        /* Outside @layer so constructed stylesheets resolve the name reliably. */
        @keyframes pk-dropdown-menu-show {
            from {
                scale: 0.9;
                opacity: 0;
            }

            to {
                scale: 1;
                opacity: 1;
            }
        }
    `],pc=new Set,mc=class extends _{constructor(...e){super(...e),this.open=!1,this.size=`default`,this.placement=`bottom-start`,this.sideOffset=4,this.distance=4,this.skidding=0,this.for=``,this.userTypedQuery=``,this.userTypedTimeout=0,this.openSubmenuStack=[],this.openedByKeyboard=!1,this.triggerElement=null,this.handleMenuClick=e=>{let t=this.resolveMenuItem(e);if(!(!t||t.disabled)){if(t.hasSubmenu()){t.submenuOpen||(this.closeSiblingSubmenus(t),this.addToSubmenuStack(t),t.openSubmenu()),e.stopPropagation();return}this.makeSelection(t)}},this.handleSubmenuOpening=e=>{let t=e.detail?.item;t instanceof z&&(this.closeSiblingSubmenus(t),this.addToSubmenuStack(t))},this.handleGlobalMouseMove=e=>{let t=this.getCurrentSubmenuItem();if(!t?.submenuOpen||!t.submenuElement)return;let n=t.submenuElement,r=e.composedPath(),i=t.matches(`:hover`),a=!!n.matches(`:hover`),o=i||r.some(e=>e===t),s=a||r.some(e=>e instanceof HTMLElement&&e.closest(`[part="submenu"]`)===n);!o&&!s&&window.setTimeout(()=>{!i&&!a&&(t.submenuOpen=!1)},100)},this.handleTriggerClick=e=>{let t=this.getTrigger();!t||!e.composedPath().includes(t)||(e.preventDefault(),e.stopPropagation(),this.openedByKeyboard=!1,this.open=!this.open)},this.handleExternalTriggerClick=e=>{e.preventDefault(),e.stopPropagation(),this.openedByKeyboard=!1,this.open=!this.open},this.handleTriggerKeyDown=e=>{let t=this.getTrigger();!t||!e.composedPath().includes(t)||this.open||(e.key===`ArrowDown`||e.key===`ArrowUp`)&&(e.preventDefault(),e.stopPropagation(),this.openedByKeyboard=!0,this.open=!0)},this.handleDocumentKeyDown=e=>{let t=this.isRtl();if(e.key===`Escape`&&this.open&&me(this)){e.preventDefault(),e.stopPropagation(),this.open=!1,this.getTrigger()?.focus({preventScroll:!0});return}if(!this.open)return;let n=[...V()].find(e=>e.localName===`pk-dropdown-item`),r=n?.localName===`pk-dropdown-item`,i=this.getCurrentSubmenuItem(),a=!!i,o,s,c;a&&i?(o=this.getSubmenuItems(i),s=o.find(e=>e.active||e===n),c=s?o.indexOf(s):-1):(o=this.getItems(),s=o.find(e=>e.active||e===n),c=s?o.indexOf(s):-1);let l;if(e.key===`ArrowUp`&&(e.preventDefault(),e.stopPropagation(),l=c>0?o[c-1]:o[o.length-1]),e.key===`ArrowDown`&&(e.preventDefault(),e.stopPropagation(),l=c!==-1&&c<o.length-1?o[c+1]:o[0]),e.key===(t?`ArrowLeft`:`ArrowRight`)&&r&&s&&s.hasSubmenu()){e.preventDefault(),e.stopPropagation(),this.closeSiblingSubmenus(s),s.openSubmenu(),this.addToSubmenuStack(s),window.setTimeout(()=>{let e=this.getSubmenuItems(s);e.length>0&&this.setActiveItem(e,e[0])},0);return}if(e.key===(t?`ArrowRight`:`ArrowLeft`)&&a){e.preventDefault(),e.stopPropagation();let t=this.removeFromSubmenuStack();t&&(t.submenuOpen=!1,window.setTimeout(()=>{t.focus({preventScroll:!0}),t.active=!0,(t.slot===`submenu`&&t.parentElement instanceof z?this.getSubmenuItems(t.parentElement):this.getItems()).forEach(e=>{e!==t&&(e.active=!1)})},0));return}if((e.key===`Home`||e.key===`End`)&&(e.preventDefault(),e.stopPropagation(),l=e.key===`Home`?o[0]:o[o.length-1]),e.key===`Tab`){this.open=!1;return}if(e.key.length===1&&!(e.metaKey||e.ctrlKey||e.altKey)&&!(e.key===` `&&this.userTypedQuery===``)){window.clearTimeout(this.userTypedTimeout),this.userTypedTimeout=window.setTimeout(()=>{this.userTypedQuery=``},1e3),this.userTypedQuery+=e.key;let t=this.userTypedQuery.trim().toLowerCase();l=o.find(e=>(e.textContent||``).trim().toLowerCase().startsWith(t))}if(l){e.preventDefault(),e.stopPropagation(),this.setActiveItem(o,l);return}(e.key===`Enter`||e.key===` `&&this.userTypedQuery===``)&&r&&s&&(e.preventDefault(),e.stopPropagation(),s.hasSubmenu()?(this.closeSiblingSubmenus(s),s.openSubmenu(),this.addToSubmenuStack(s),window.setTimeout(()=>{let e=this.getSubmenuItems(s);e.length>0&&this.setActiveItem(e,e[0])},0)):this.makeSelection(s))},this.handleDocumentPointerDown=e=>{let t=e.composedPath(),n=this.getTrigger();t.some(e=>e===this||e===n)||(this.open=!1)}}static{this.styles=fc}get panelElement(){return this.menuElement??null}get popup(){return this.popupElement??null}connectedCallback(){super.connectedCallback(),this.addEventListener(`click`,this.handleTriggerClick,!0),this.addEventListener(`keydown`,this.handleTriggerKeyDown)}firstUpdated(){let e=()=>{if(this.for){this.resolveExternalTrigger();return}this.syncSlottedTrigger()};queueMicrotask(e),requestAnimationFrame(e)}disconnectedCallback(){window.clearTimeout(this.userTypedTimeout),this.removeEventListener(`click`,this.handleTriggerClick,!0),this.removeEventListener(`keydown`,this.handleTriggerKeyDown),this.unbindTrigger(this.triggerElement),this.triggerElement=null,this.closeAllSubmenus(),this.popupElement&&(this.popupElement.active=!1),this.menuElement?.classList.remove(`show`,`hide`),document.removeEventListener(`keydown`,this.handleDocumentKeyDown),document.removeEventListener(`pointerdown`,this.handleDocumentPointerDown,!0),document.removeEventListener(`mousemove`,this.handleGlobalMouseMove),Ee(this),pc.delete(this),super.disconnectedCallback()}async updated(e){if(super.updated(e),e.has(`for`)&&this.resolveExternalTrigger(),e.has(`open`)&&this.syncTriggerExpanded(),!e.has(`open`))return;let t=e.get(`open`);t!==this.open&&(t===void 0&&this.open===!1||(this.open?await this.showMenu():(this.closeAllSubmenus(),await this.hideMenu(`unknown`))))}getItems(e=!1){let t=(this.defaultSlot?.assignedElements({flatten:!0})??[]).filter(e=>e.localName===`pk-dropdown-item`);return e?t:t.filter(e=>!e.disabled)}getSubmenuItems(e,t=!1){let n=((e.shadowRoot?.querySelector(`slot[name="submenu"]`))?.assignedElements({flatten:!0})??[...e.children].filter(e=>e.getAttribute(`slot`)===`submenu`)).filter(e=>e.localName===`pk-dropdown-item`);return t?n:n.filter(e=>!e.disabled)}getTrigger(){return this.for?ac(this,this.for)??this.triggerElement:this.querySelector(`[slot="trigger"]`)??this.triggerElement}getAnchor(){return this.getTrigger()??``}resolveExternalTrigger(){this.unbindTrigger(this.triggerElement),this.triggerElement=this.for?ac(this,this.for):null,this.bindTrigger(this.triggerElement),this.requestUpdate()}onTriggerSlotChange(e){if(this.for)return;let[t]=e.target.assignedElements({flatten:!0});this.unbindTrigger(this.triggerElement),this.triggerElement=t??null,this.bindTrigger(this.triggerElement),this.requestUpdate()}syncSlottedTrigger(){let e=this.renderRoot.querySelector(`slot[name="trigger"]`);e&&this.onTriggerSlotChange({target:e})}bindTrigger(e){e&&(e.setAttribute(`aria-haspopup`,`menu`),this.for&&(e.addEventListener(`click`,this.handleExternalTriggerClick),e.addEventListener(`keydown`,this.handleTriggerKeyDown)),this.syncTriggerExpanded())}unbindTrigger(e){e?.removeEventListener(`click`,this.handleExternalTriggerClick),e?.removeEventListener(`keydown`,this.handleTriggerKeyDown)}syncTriggerExpanded(){this.getTrigger()?.setAttribute(`aria-expanded`,this.open?`true`:`false`)}closeAfterSelect(e=`api`){this.open=!1}makeSelection(e){let t=this.getTrigger();if(e.disabled)return;e.type===`checkbox`&&(e.checked=!e.checked),e.type===`radio`&&!e.checked&&(e.checked=!0);let n={value:e.value,type:e.type,checked:e.checked,radioGroup:e.radioGroup};e.dispatchEvent(new CustomEvent(`pk-select`,{detail:n,bubbles:!1,composed:!1,cancelable:!0}));let r=new CustomEvent(`pk-select`,{detail:n,bubbles:!0,composed:!0,cancelable:!0});this.dispatchEvent(r),r.defaultPrevented||(this.open=!1,t?.focus({preventScroll:!0}))}resolveMenuItem(e){let t=e.target;if(t instanceof z)return t;if(t instanceof Element){let e=t.closest(`pk-dropdown-item`);if(e instanceof z)return e}return e.composedPath().find(e=>e instanceof z)??null}whenClosed(){return this.open?new Promise(e=>{this.addEventListener(`pk-after-hide`,()=>{this.popupElement.stop().then(()=>e())},{once:!0})}):this.popupElement?.active?this.popupElement.stop():Promise.resolve()}forceDismissCleanup(){this.open=!1,this.popupElement.active=!1,this.menuElement?.classList.remove(`show`,`hide`),this.closeAllSubmenus(),document.removeEventListener(`keydown`,this.handleDocumentKeyDown),document.removeEventListener(`pointerdown`,this.handleDocumentPointerDown,!0),document.removeEventListener(`mousemove`,this.handleGlobalMouseMove),Ee(this),pc.delete(this)}isRtl(){return getComputedStyle(this).direction===`rtl`}addToSubmenuStack(e){let t=this.openSubmenuStack.indexOf(e);t===-1?this.openSubmenuStack.push(e):this.openSubmenuStack=this.openSubmenuStack.slice(0,t+1)}removeFromSubmenuStack(){return this.openSubmenuStack.pop()}getCurrentSubmenuItem(){return this.openSubmenuStack.length>0?this.openSubmenuStack[this.openSubmenuStack.length-1]:void 0}closeAllSubmenus(){this.getItems(!0).forEach(e=>{e.submenuOpen=!1,e.active=!1}),this.openSubmenuStack=[]}closeSiblingSubmenus(e){let t=e.closest(`pk-dropdown-item:not([slot="submenu"])`);(t instanceof z?this.getSubmenuItems(t,!0):this.getItems(!0)).forEach(t=>{t!==e&&t.submenuOpen&&(t.submenuOpen=!1)}),this.openSubmenuStack.includes(e)||this.openSubmenuStack.push(e)}setActiveItem(e,t){e.forEach(e=>{e.active=e===t,e===t?e.setAttribute(`data-highlighted`,``):e.removeAttribute(`data-highlighted`)}),t.focus({preventScroll:!0}),t.scrollIntoView({block:`nearest`})}async showMenu(){if(!this.popupElement||!this.menuElement)return;this.for&&!this.triggerElement?.isConnected&&this.resolveExternalTrigger();let e=new _e;if(!this.dispatchEvent(e)){this.open=!1;return}if(this.popupElement.active&&(this.popupElement.active=!1,this.menuElement.classList.remove(`show`,`hide`),await this.updateComplete),pc.forEach(e=>{e!==this&&(e.open=!1)}),this.popupElement.active=!0,this.open=!0,pc.add(this),pe(this),document.addEventListener(`keydown`,this.handleDocumentKeyDown),document.addEventListener(`pointerdown`,this.handleDocumentPointerDown,!0),document.addEventListener(`mousemove`,this.handleGlobalMouseMove),await this.updateComplete,await ve(this.popupElement,this.placement,100,{requireEvent:!0}),!this.open){this.popupElement.active=!1,pc.delete(this),Ee(this),document.removeEventListener(`keydown`,this.handleDocumentKeyDown),document.removeEventListener(`pointerdown`,this.handleDocumentPointerDown,!0),document.removeEventListener(`mousemove`,this.handleGlobalMouseMove);return}this.menuElement.classList.remove(`hide`),await dc(this.menuElement,`show`);let t=this.getItems();t.length>0&&(this.openedByKeyboard?this.setActiveItem(t,t[0]):(t.forEach(e=>{e.active=!1,e.removeAttribute(`data-highlighted`)}),this.menuElement.focus({preventScroll:!0}))),this.openedByKeyboard=!1,this.dispatchEvent(new we),this.dispatchEvent(new CustomEvent(`pk-open-change`,{detail:{open:!0},bubbles:!0,composed:!0}))}async hideMenu(e){if(!this.popupElement||!this.menuElement)return;let t=new ge(e);if(!this.dispatchEvent(t)){this.open=!0;return}this.open=!1,pc.delete(this),Ee(this),document.removeEventListener(`keydown`,this.handleDocumentKeyDown),document.removeEventListener(`pointerdown`,this.handleDocumentPointerDown,!0),document.removeEventListener(`mousemove`,this.handleGlobalMouseMove),this.userTypedQuery=``,window.clearTimeout(this.userTypedTimeout),this.getItems(!0).forEach(e=>{e.active=!1,e.removeAttribute(`data-highlighted`)}),this.menuElement.classList.remove(`show`),await dc(this.menuElement,`hide`),this.popupElement.active=!1,this.dispatchEvent(new xe),this.dispatchEvent(new CustomEvent(`pk-open-change`,{detail:{open:!1},bubbles:!0,composed:!0}))}render(){let e=this.hasUpdated?this.popupElement?.active:this.open;return v`
            <pk-popup
                .anchor=${this.for?this.getAnchor():``}
                placement=${this.placement}
                .distance=${this.distance||this.sideOffset}
                .skidding=${this.skidding}
                ?active=${e}
                flip
                shift
                .shiftPadding=${10}
                auto-size="vertical"
                .autoSizePadding=${10}
            >
                <slot
                    name="trigger"
                    slot="anchor"
                    @slotchange=${this.onTriggerSlotChange}
                ></slot>

                <div
                    id="menu"
                    part="panel"
                    class="panel"
                    role="menu"
                    tabindex="-1"
                    aria-orientation="vertical"
                    data-size=${this.size}
                    @click=${this.handleMenuClick}
                    @pk-submenu-open=${this.handleSubmenuOpening}
                >
                    <slot></slot>
                </div>
            </pk-popup>
        `}};s([c({type:Boolean,reflect:!0})],mc.prototype,`open`,void 0),s([c({reflect:!0})],mc.prototype,`size`,void 0),s([c({reflect:!0})],mc.prototype,`placement`,void 0),s([c({attribute:`side-offset`,type:Number})],mc.prototype,`sideOffset`,void 0),s([c({type:Number})],mc.prototype,`distance`,void 0),s([c({type:Number})],mc.prototype,`skidding`,void 0),s([c({reflect:!0})],mc.prototype,`for`,void 0),s([r(`slot:not([name])`)],mc.prototype,`defaultSlot`,void 0),s([r(`#menu`)],mc.prototype,`menuElement`,void 0),s([r(`pk-popup`)],mc.prototype,`popupElement`,void 0),mc=s([h(`pk-dropdown-menu`)],mc);var hc=class extends _{static{this.styles=y`
        @layer pk-component {
            :host {
                display: block;
            }

            hr {
                display: block;
                height: 1px;
                margin: 4px 0;
                border: 0;
                padding: 0;
                background: var(--pk-color-slate-200);
            }
        }
    `}connectedCallback(){super.connectedCallback(),this.setAttribute(`role`,`separator`)}render(){return v`<hr part="base" />`}};hc=s([h(`pk-dropdown-separator`)],hc);var gc=u({tagName:`pk-dropdown-menu`,elementClass:mc,react:A.default,events:{onPkSelect:`pk-select`,onPkShow:`pk-show`,onPkAfterShow:`pk-after-show`,onPkHide:`pk-hide`,onPkAfterHide:`pk-after-hide`,onPkOpenChange:`pk-open-change`}}),_c=u({tagName:`pk-dropdown-item`,elementClass:z,react:A.default,events:{onPkSelect:`pk-select`,onPkSubmenuOpen:`pk-submenu-open`}});u({tagName:`pk-dropdown-label`,elementClass:B,react:A.default});var vc=u({tagName:`pk-dropdown-separator`,elementClass:hc,react:A.default}),yc=gc,bc=_c,xc=vc,Sc=`copy-to-site`;function Cc({nodeIds:e,disabled:t=!1,showIcon:n=!0}){let r=R(e=>e.state?.canCopyToSite??!1),i=R(e=>e.state?.copyToSiteTargets??[]);return!r||i.length===0?null:(0,j.jsxs)(bc,{value:Sc,disabled:t||e.length===0,children:[n&&(0,j.jsx)(vr,{slot:`prefix`,icon:`copy`}),F(`Copy to site…`)]})}function wc({label:e,onAction:t,disabled:n=!1,includeDeepOption:r=!1,deepAsSubmenu:i=!1,shallowLabel:a,destructive:o=!1,icon:s}){let c=s?(0,j.jsx)(vr,{slot:`prefix`,icon:s}):null,l=a??F(`Selected only`),u=e=>{t?.(e)};return r?i?(0,j.jsxs)(bc,{value:`${e}-submenu`,disabled:n,destructive:o,children:[c,e,(0,j.jsxs)(`div`,{slot:`submenu`,children:[(0,j.jsx)(bc,{value:`${e}-shallow`,onPkSelect:()=>u(!1),children:l}),(0,j.jsx)(bc,{value:`${e}-deep`,onPkSelect:()=>u(!0),children:F(`With descendants`)})]})]}):(0,j.jsxs)(j.Fragment,{children:[(0,j.jsxs)(bc,{value:`${e}-shallow`,disabled:n,destructive:o,onPkSelect:()=>u(!1),children:[c,e]}),(0,j.jsxs)(bc,{value:`${e}-deep`,disabled:n,destructive:o,onPkSelect:()=>u(!0),children:[c,F(`{label} (with descendants)`,{label:e})]})]}):(0,j.jsxs)(bc,{value:e,disabled:n,destructive:o,onPkSelect:()=>u(!1),children:[c,e]})}function Tc(e,t){let n=!1,r=()=>{n||(n=!0,window.clearTimeout(a),e?.removeEventListener?.(`pk-after-hide`,i),window.setTimeout(()=>{e?.forceDismissCleanup?.(),t()},0))},i=()=>{r()},a=window.setTimeout(r,450);if(!e){r();return}if(!e.open){r();return}e.addEventListener(`pk-after-hide`,i,{once:!0})}function Ec({node:e,isDragSession:t=!1,className:n}){let r=(0,A.useRef)(null),i=R(e=>e.state?.elementType??``),a=R(e=>e.siteId),o=R(e=>e.nodes),s=R(e=>e.state?.menu.maxLevels??null),c=R(e=>e.moveNodeUp),l=R(e=>e.moveNodeDown),u=R(e=>e.indent),d=R(e=>e.outdent),f=R(e=>e.duplicateNode),p=R(e=>e.deleteNode),m=R(e=>e.refresh),h=R(e=>e.openCopyToSiteDialog),g=zi(o,e.id,s),_=(s===null||s>1)&&e.hasDescendants,v=e.title||F(`Untitled`),y=F(`Duplicate`),b=F(`Delete`),x=e=>{Tc(r.current,e)};return(0,j.jsx)(`div`,{className:Xr(t&&`opacity-0`,n),children:(0,j.jsxs)(yc,{ref:e=>{r.current=e},size:`sm`,placement:`bottom-end`,onPkSelect:t=>{let n=t.detail?.value;if(n)switch(n){case`edit`:x(()=>{to(i,e.id,a,()=>void m())});return;case Sc:x(()=>{h([e.id],_)});return;case`move-up`:x(()=>{c(e.id)});return;case`move-down`:x(()=>{l(e.id)});return;case`move-left`:x(()=>{d(e.id)});return;case`move-right`:x(()=>{u(e.id)});return;case y:case`${y}-shallow`:x(()=>{f(e.id,!1)});return;case`${y}-deep`:x(()=>{f(e.id,!0)});return;case b:case`${b}-shallow`:x(()=>{p(e.id,!1)});return;case`${b}-deep`:x(()=>{p(e.id,!0)});return;default:return}},children:[(0,j.jsx)(Tr,{slot:`trigger`,type:`button`,variant:`transparent`,size:`sm`,"data-no-row-select":!0,style:{"--pk-btn-height":`28px`,"--pk-btn-padding-inline":`7px`,"--pk-btn-icon-size":`14px`},"aria-label":F(`Actions for {title}`,{title:v}),onClick:e=>e.stopPropagation(),children:(0,j.jsx)(vr,{slot:`start`,icon:`ellipsis`})}),(0,j.jsxs)(bc,{value:`edit`,children:[(0,j.jsx)(vr,{slot:`prefix`,icon:`pen`}),F(`Edit`)]}),(0,j.jsx)(Cc,{nodeIds:[e.id],disabled:e.pendingDelete}),(0,j.jsx)(wc,{label:y,icon:`clone`,includeDeepOption:_,deepAsSubmenu:_,shallowLabel:F(`This node`)}),(0,j.jsx)(xc,{}),(0,j.jsxs)(bc,{value:`move-up`,disabled:!g.canMoveUp,children:[(0,j.jsx)(vr,{slot:`prefix`,icon:`arrow-up`}),F(`Move up`)]}),(0,j.jsxs)(bc,{value:`move-down`,disabled:!g.canMoveDown,children:[(0,j.jsx)(vr,{slot:`prefix`,icon:`arrow-down`}),F(`Move down`)]}),(0,j.jsxs)(bc,{value:`move-left`,disabled:!g.canMoveLeft,children:[(0,j.jsx)(vr,{slot:`prefix`,icon:`arrow-left`}),F(`Move left`)]}),(0,j.jsxs)(bc,{value:`move-right`,disabled:!g.canMoveRight,children:[(0,j.jsx)(vr,{slot:`prefix`,icon:`arrow-right`}),F(`Move right`)]}),(0,j.jsx)(xc,{}),(0,j.jsx)(wc,{label:b,icon:`xmark`,includeDeepOption:_,deepAsSubmenu:_,shallowLabel:F(`This node`),destructive:!0})]})})}function Dc({className:e,slot:t}){return(0,j.jsx)(`svg`,{xmlns:`http://www.w3.org/2000/svg`,viewBox:`0 0 448 512`,focusable:`false`,"aria-hidden":`true`,className:e,slot:t,children:(0,j.jsx)(`path`,{fill:`currentColor`,d:`M71.3 295.6c-21.9-21.9-21.9-57.3 0-79.2s57.3-21.9 79.2 0 21.9 57.3 0 79.2s-57.4 21.9-79.2 0zM184.4 182.5c-21.9-21.9-21.9-57.3 0-79.2s57.3-21.9 79.2 0 21.9 57.3 0 79.2-57.3 21.8-79.2 0zm0 147c21.9-21.9 57.3-21.9 79.2 0s21.9 57.3 0 79.2s-57.3 21.9-79.2 0c-21.9-21.8-21.9-57.3 0-79.2zM297.5 216.4c21.9-21.9 57.3-21.9 79.2 0s21.9 57.3 0 79.2s-57.3 21.9-79.2 0c-21.8-21.9-21.8-57.3 0-79.2z`})})}function Oc(e){return e.pendingDelete?`disabled`:e.pendingAdd?`pending`:e.pendingEdit?`warning`:!e.enabled||!e.enabledForSite?`disabled`:e.status===`live`?`enabled`:e.status}function kc(e){return e?.trim()?`.${e.trim().split(/\s+/).join(` .`)}`:null}function Ac(e,t,n){return t?`bg-sky-50`:n?`bg-orange-50/40`:e?`bg-gray-100`:`bg-white group-hover:bg-gray-50`}function jc({node:e,item:t,itemProps:n,showTypeColumn:r=!0,visibleSelectableNodeIds:i,isDragging:a=!1,isDragSession:o=!1,isDropNestTarget:s=!1}){let c=R(e=>e.selectedNodeIds),l=R(e=>e.selectNode),u=R(e=>e.collapsedNodeIds),d=R(e=>e.toggleNodeCollapsed),f=R(e=>e.restoreNode),p=R(e=>e.state?.elementType??``),m=R(e=>e.siteId),h=R(e=>e.refresh),g=(0,A.useRef)(null);(0,A.useEffect)(()=>()=>{g.current&&clearTimeout(g.current)},[]);let{ref:_,onClick:v,role:y,onDragOver:b,...x}=n,{onDragStart:ee,...S}=t.getDragHandleProps(),C=Zr(e.level),w=c.includes(e.id),te=!!u[e.id],ne=!e.pendingDelete,re=kc(e.classes),ie=t=>{ne&&l(e.id,{shiftKey:t.shiftKey,metaKey:t.metaKey,ctrlKey:t.ctrlKey},i)},ae=(0,A.useCallback)(()=>{!ne||!p||to(p,e.id,m,()=>void h())},[p,ne,e.id,h,m]);return(0,j.jsxs)(`div`,{ref:_,role:`row`,...x,onDragOver:e=>{b?.(e),e.dataTransfer&&(e.dataTransfer.dropEffect=`move`)},className:Xr($r(r,`group h-[36px] max-h-[36px] min-h-[36px] overflow-hidden outline-none focus:outline-none focus-visible:outline-none`),ne&&!o&&`cursor-pointer`,!w&&!o&&`hover:bg-gray-50`,w&&!o&&`bg-gray-100 hover:bg-gray-100`,w&&o&&`bg-gray-100`,s&&`bg-sky-50`,e.pendingAdd&&!s&&`bg-orange-50/40`,a&&`opacity-40`),"data-id":e.id,"data-node-id":e.id,"data-tree-row":``,"data-level":e.level,onClick:e=>{!ne||Bi(e.target)||(g.current&&clearTimeout(g.current),g.current=setTimeout(()=>{g.current=null,ie(e)},200))},onDoubleClick:e=>{!ne||Bi(e.target)||(g.current&&=(clearTimeout(g.current),null),e.preventDefault(),ae())},children:[(0,j.jsx)(`div`,{role:`cell`,className:`flex items-center px-3`,children:ne&&(0,j.jsx)($a,{"aria-label":`Select ${e.title}`,checked:w,tabIndex:-1,onClick:e=>e.stopPropagation(),onPkChange:()=>{l(e.id,{shiftKey:!1,metaKey:!1,ctrlKey:!1,toggle:!0},i)}})}),(0,j.jsxs)(`div`,{role:`cell`,className:`flex items-center gap-1.5 pr-3`,style:{paddingLeft:C},children:[(0,j.jsx)(`span`,{className:`relative inline-flex w-3 shrink-0 justify-center`,children:e.hasDescendants?(0,j.jsx)(`button`,{type:`button`,"data-no-row-select":!0,className:`absolute top-1/2 left-1/2 flex size-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer items-center justify-center rounded bg-transparent text-gray-400 hover:bg-transparent hover:text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-600/40`,"aria-expanded":!te,onClick:t=>{t.stopPropagation(),d(e.id)},children:(0,j.jsx)(vr,{icon:`chevron-right`,className:Xr(`size-2.5`,!te&&`rotate-90`)})}):null}),ne&&(0,j.jsxs)(`span`,{className:`relative inline-flex shrink-0`,children:[(0,j.jsx)(Tr,{type:`button`,variant:`none`,size:`xs`,"data-no-row-select":!0,className:`absolute top-1/2 left-1/2 flex size-6 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded bg-transparent p-0 text-gray-400 outline-none hover:bg-transparent hover:text-gray-600 focus:outline-none focus-visible:outline-none focus-visible:ring-0 [&::part(base)]:cursor-move`,title:`Drag to reorder`,onClick:e=>e.stopPropagation(),onDragStart:e=>{e.dataTransfer&&(e.dataTransfer.effectAllowed=`move`),ee?.(e)},...S,children:(0,j.jsx)(Dc,{slot:`start`,className:`size-3.5`})}),(0,j.jsx)(`span`,{className:`invisible inline-flex size-3.5`,"aria-hidden":!0,children:(0,j.jsx)(Dc,{className:`size-3.5`})})]}),(0,j.jsx)(Ue,{status:eo(Oc(e)),className:`size-2.5 shrink-0`}),(0,j.jsx)(`span`,{className:Xr(`min-w-0 truncate font-normal text-gray-900`,e.pendingDelete&&`text-gray-400 line-through`),children:e.title||`(Untitled)`}),e.newWindow&&(0,j.jsx)(vr,{icon:`arrow-up-right-from-square`,className:`size-2.5 shrink-0 text-gray-400/80`,label:`Opens in a new window`}),re&&(0,j.jsx)(`span`,{className:`shrink-0 font-mono text-xs leading-snug text-[#8f98a3]`,children:re}),ne&&(0,j.jsx)(Tr,{type:`button`,variant:`none`,size:`xxs`,"data-no-row-select":!0,style:{"--pk-btn-height":`20px`,"--pk-btn-padding-inline":`6px`,"--pk-btn-font":`11px`,"--pk-btn-radius":`4px`},className:Xr(`node-edit-btn ml-[7px] shrink-0 rounded border border-[rgba(96,125,159,0.25)] bg-transparent font-normal leading-none text-gray-700 opacity-0 transition-opacity hover:bg-transparent focus-visible:opacity-100 group-hover:opacity-100`,w&&`opacity-100`),onClick:e=>{e.stopPropagation(),ae()},children:F(`Edit`)}),e.pendingDelete&&(0,j.jsx)(Tr,{type:`button`,size:`xxs`,variant:`secondary`,onClick:()=>void f(e.id),children:`Restore`})]}),r&&(0,j.jsx)(`div`,{role:`cell`,className:`flex items-center justify-end px-1`,children:(0,j.jsx)(ro,{node:e})}),ne?(0,j.jsx)(`div`,{role:`cell`,className:Xr(`sticky right-0 z-10 flex items-center justify-center pr-1 pl-0.5`,Ac(w,s,e.pendingAdd)),children:(0,j.jsx)(Ec,{node:e,isDragSession:o})}):(0,j.jsx)(`div`,{role:`cell`,"aria-hidden":!0})]})}function Mc({position:e}){return e?(0,j.jsxs)(`div`,{className:`pointer-events-none z-20 flex items-center`,style:{position:`absolute`,top:`${e.top}px`,left:`${e.left}px`,right:`${e.right}px`,transform:`translateY(-50%)`,willChange:`top, left`},children:[(0,j.jsx)(`span`,{className:`size-2.5 shrink-0 rounded-full border-2 border-sky-500 bg-white`}),(0,j.jsx)(`span`,{className:`h-0.5 min-w-0 flex-1 rounded-full bg-sky-500`})]}):null}function Nc(e,t){let n=e.getDragTarget(),r=e.getElement();if(!n||!r)return null;let i=r.getBoundingClientRect(),a=t?128:0;if(!Ea(n)){let e=n.item;if(e.getId()===`__navigation_root__`)return null;let t=e.getElement()?.getBoundingClientRect();if(!t)return null;let r=e.getItemMeta().level+2;return{top:t.bottom-i.top,left:40+Zr(r),right:a}}let o=e.getItems().filter(e=>e.getId()!==ei),s=n.dragLineIndex,c=40+Zr(n.dragLineLevel+1),l=e=>o[e]?.getElement()?.getBoundingClientRect(),u=null;if(o.length===0)return null;if(s>=o.length)u=l(o.length-1)?.bottom??null;else if(s<=0)u=l(0)?.top??null;else{let e=l(s-1),t=l(s);u=e&&t?(e.bottom+t.top)/2:t?.top??e?.bottom??null}return u===null?null:{top:u-i.top,left:c,right:a}}function Pc(e,t){return e.nestTargetId===t.nestTargetId?e.linePosition===t.linePosition?!0:!e.linePosition||!t.linePosition?e.linePosition===t.linePosition:e.linePosition.top===t.linePosition.top&&e.linePosition.left===t.linePosition.left&&e.linePosition.right===t.linePosition.right:!1}function Fc(e,t){let n=e.getDragTarget(),r=n&&!Ea(n)?n.item.getId():null;return{linePosition:r?null:Nc(e,t),nestTargetId:r}}function Ic(e,t,n){let[r,i]=(0,A.useState)({linePosition:null,nestTargetId:null}),a=(0,A.useRef)({linePosition:null,nestTargetId:null});return(0,A.useEffect)(()=>{if(!n){a.current={linePosition:null,nestTargetId:null},i({linePosition:null,nestTargetId:null});return}let r=0,o=()=>{let n=!!e.getDragTarget(),s=Fc(e,t);n?a.current=s:(a.current.linePosition||a.current.nestTargetId)&&(s=a.current),i(e=>Pc(e,s)?e:s),r=window.requestAnimationFrame(o)};return r=window.requestAnimationFrame(o),()=>{window.cancelAnimationFrame(r)}},[n,t,e]),r}function G(){let e=document.activeElement;return e instanceof Element?e instanceof HTMLInputElement||e instanceof HTMLTextAreaElement||e instanceof HTMLSelectElement||e instanceof HTMLElement&&e.isContentEditable?!0:!!e.closest(`[contenteditable="true"], [role="textbox"], [role="menu"], [role="dialog"]`):!1}function Lc(e,t){return e.length===0?null:t!==null&&e.includes(t)?t:e[e.length-1]??null}function Rc(e){let t=R(e=>e.moveNodeUp),n=R(e=>e.moveNodeDown),r=R(e=>e.indent),i=R(e=>e.outdent);(0,A.useEffect)(()=>{if(!e)return;let a=e=>{if(!e.altKey||e.metaKey||e.ctrlKey||e.shiftKey||G())return;let{selectedNodeIds:a,lastSelectedNodeId:o,nodes:s,state:c}=R.getState(),l=Lc(a,o);if(l===null)return;let u=s.find(e=>e.id===l);if(!u||u.pendingDelete)return;let d=zi(s,l,c?.menu.maxLevels??null),f=!1;switch(e.key){case`ArrowUp`:d.canMoveUp&&(t(l),f=!0);break;case`ArrowDown`:d.canMoveDown&&(n(l),f=!0);break;case`ArrowLeft`:d.canMoveLeft&&(i(l),f=!0);break;case`ArrowRight`:d.canMoveRight&&(r(l),f=!0);break}f&&(e.preventDefault(),document.querySelector(`[data-node-id="${l}"]`)?.scrollIntoView({block:`nearest`}))};return window.addEventListener(`keydown`,a),()=>{window.removeEventListener(`keydown`,a)}},[e,r,n,t,i])}var zc={id:0,title:`Root`,type:`root`,typeLabel:``,typeClass:``,typeColorRgb:``,typeTextColorRgb:``,url:null,level:0,parentId:null,status:`live`,newWindow:!1,classes:null,pendingAdd:!1,pendingDelete:!1,pendingEdit:!1,enabled:!0,enabledForSite:!0,hasDescendants:!0,isElementLinked:!1};function Bc(){let e=R(e=>e.nodes),t=R(e=>e.state?.menu.maxLevels??null),n=R(e=>e.collapsedNodeIds),r=R(e=>e.statusFilter),i=R(e=>e.visibleColumns),a=R(e=>e.setNodes),o=R(e=>e.selectedNodeIds),s=R(e=>e.setSelectedNodeIds),c=R(e=>e.clearSelection),l=(0,A.useMemo)(()=>ui(e,r),[e,r]),u=(0,A.useMemo)(()=>new Set(l.map(e=>e.id)),[l]),d=(0,A.useMemo)(()=>new Map(e.map(e=>[e.id,e])),[e]),f=(0,A.useMemo)(()=>ti(l),[l]),p=(0,A.useMemo)(()=>ai(l,n),[l,n]),m=l.filter(e=>!e.pendingDelete),h=m.map(e=>e.id),g=m.length>0&&o.length===m.length,_=i.type,v=t=>{let n=ri(ni(d,t));if(u.size===e.length){a(n);return}a(ii(e,u,n))},y=qa({rootItemId:ei,state:{expandedItems:p},setExpandedItems:e=>{let{expandNodeCollapsed:t,toggleNodeCollapsed:n,collapsedNodeIds:r}=R.getState(),i=ai(l,r),a=typeof e==`function`?e(i):e,o=new Set(a);for(let e of l){if(!e.hasDescendants)continue;let i=e.id,a=o.has(String(i)),s=!!r[i];a&&s?t(i):!a&&!s&&n(i)}},getItemName:e=>e.getItemData()?.title||`(Untitled)`,isItemFolder:e=>{let n=e.getItemMeta().level+1;return t===null||n<t},dataLoader:{getItem:e=>e===`__navigation_root__`?zc:d.get(Number(e))??zc,getChildren:e=>f[e]??[]},indent:20,features:[Ta,La],seperateDragHandle:!0,reorderAreaPercentage:.2,openOnDropDelay:500,canDrop:(e,n)=>{if(`dragLineLevel`in n){let r=n.dragLineLevel+1,i=e[0],a=i?.getItemData();if(!a)return!0;let o=Hc(f,i.getId(),a.level);if(t!==null&&r+o-1>t)return!1}return!0},canDrag:e=>e.every(e=>!e.getItemData()?.pendingDelete),createForeignDragObject:e=>({format:`application/x-navigation-node`,data:e.map(e=>e.getId()).join(`,`),effectAllowed:`move`}),setDragImage:()=>{let e=document.createElement(`div`);return e.style.width=`1px`,e.style.height=`1px`,e.style.opacity=`0`,e.style.position=`fixed`,e.style.top=`-9999px`,document.body.appendChild(e),{imgElement:e,xOffset:0,yOffset:0}},onDrop:(e,t)=>{let n=new Set(e.map(e=>e.getId())),r=si(f,n),i=t.item.getId(),a=[...r[i]??[]],o=e.map(e=>e.getId());`childIndex`in t?r[i]=[...a.slice(0,t.insertionIndex),...o,...a.slice(t.insertionIndex)]:r[i]=[...a,...o],v(r)}});(0,A.useEffect)(()=>{y.rebuildTree()},[y,f,p,l]);let b=(0,A.useMemo)(()=>{let e=y.getState().dnd?.draggedItems;return e?.length?new Set(e.map(e=>e.getId())):new Set},[y,y.getState().dnd]),x=!!y.getState().dnd,{linePosition:ee,nestTargetId:S}=Ic(y,_,x);if(Rc(!x&&l.length>0),!e.length)return(0,j.jsx)(`div`,{className:`flex min-h-[320px] items-center justify-center border border-dashed border-gray-200 p-12 text-sm text-gray-500`,children:(0,j.jsx)(`p`,{children:F(`No nodes yet. Use the sidebar to add your first node.`)})});let C=y.getItems().filter(e=>e.getId()!==ei),{onDragOver:w,...te}=y.getContainerProps(F(`Navigation nodes`));return(0,j.jsx)(`div`,{className:`overflow-x-auto`,children:(0,j.jsxs)(`div`,{role:`table`,className:`min-w-[640px] text-sm`,children:[(0,j.jsxs)(`div`,{role:`rowgroup`,className:$r(_,`bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500`),children:[(0,j.jsx)(`div`,{role:`columnheader`,className:`px-3 py-2.5`,children:(0,j.jsx)($a,{"aria-label":F(`Select all`),checked:g,onPkChange:e=>{e.detail.checked?s(m.map(e=>e.id)):c()}})}),(0,j.jsx)(`div`,{role:`columnheader`,className:`px-3 py-2.5 font-medium normal-case`,children:F(`Node`)}),_&&(0,j.jsx)(`div`,{role:`columnheader`,className:`px-3 py-2.5 text-right font-medium normal-case`,children:F(`Type`)}),(0,j.jsx)(`div`,{role:`columnheader`,className:`sticky right-0 z-10 bg-gray-50 py-2.5`,"aria-hidden":!0})]}),(0,j.jsx)(`div`,{role:`rowgroup`,className:`select-none`,children:C.length===0?(0,j.jsx)(`div`,{className:`py-4 text-center text-sm text-gray-500`,children:F(`No nodes match the selected status.`)}):(0,j.jsxs)(`div`,{...te,className:Xr(`pb-8`,x&&`cursor-grabbing`),onDragOver:e=>{w?.(e),e.dataTransfer&&(e.dataTransfer.dropEffect=`move`)},children:[(0,j.jsx)(Mc,{position:ee}),C.map(e=>(0,j.jsx)(Vc,{item:e,showTypeColumn:_,visibleSelectableNodeIds:h,isDragging:b.has(e.getId()),isDragSession:x,isDropNestTarget:S===e.getId()},e.getId()))]})})]})})}function Vc({item:e,showTypeColumn:t,visibleSelectableNodeIds:n,isDragging:r,isDragSession:i,isDropNestTarget:a}){let o=e.getItemData(),s=e.getProps();return!o||o.id===0?null:(0,j.jsx)(jc,{node:o,item:e,itemProps:s,showTypeColumn:t,visibleSelectableNodeIds:n,isDragging:r,isDragSession:i,isDropNestTarget:a})}function Hc(e,t,n){let r=e[t]??[];return r.length===0?1:1+Math.max(...r.map(t=>Hc(e,t,n+1)))}function Uc({open:e,menuId:t,siteId:n,onOpenChange:r,onSaved:i}){return(0,A.useEffect)(()=>{if(!e)return;let a=new window.Craft.CpScreenSlideout(`navigation/builder/menu-content-slideout`,{params:{menuId:t,siteId:n},onSubmit:()=>{i()}});return a.on(`close`,()=>{r(!1)}),a.open(),()=>{a.destroy()}},[e,t,n,r,i]),null}function Wc({label:e,description:t,className:n,disabled:r,...i}){return(0,j.jsxs)(`label`,{"data-slot":`checkbox-input`,className:n,style:{display:`flex`,alignItems:`flex-start`,gap:`0.5rem`,cursor:r?`not-allowed`:`pointer`},children:[(0,j.jsx)($a,{disabled:r,...i}),(0,j.jsxs)(`span`,{"data-slot":`checkbox-input-body`,style:{minWidth:0,opacity:r?.5:void 0},children:[(0,j.jsx)(`span`,{"data-slot":`checkbox-input-label`,style:{display:`block`,lineHeight:1.25},children:e}),t?(0,j.jsx)(`span`,{"data-slot":`checkbox-input-description`,style:{display:`block`,marginTop:`0.25rem`,color:`var(--pk-color-text-muted, #64748b)`},children:t}):null]})]})}var Gc=e=>e.target===e.currentTarget,Kc=`.modal-shade, .modal`,qc=e=>{let t=getComputedStyle(e);return t.display!==`none`&&t.visibility!==`hidden`&&Number.parseFloat(t.opacity||`1`)>0};function Jc(e=document){let t=e.querySelectorAll(Kc);for(let e of t)if(e instanceof HTMLElement&&!e.closest(`pk-dialog`)&&qc(e))return!0;return!1}function Yc(e,t={}){let n=t.getDocument?.()??document,r=t.root??n.body,i=Jc(n),a=()=>{let t=Jc(n);t!==i&&(i=t,e(t))},o=new MutationObserver(()=>{a()});o.observe(r,{childList:!0,subtree:!0,attributes:!0,attributeFilter:[`class`,`style`,`hidden`]});let s=window.setInterval(a,250);return{disconnect:()=>{o.disconnect(),window.clearInterval(s)}}}var Xc=y`
    @layer pk-component {
        :host {
            /* Not display:contents — that flattens the trigger slot into flex parents
               (e.g. playground cards) and stretches pk-button full width, same class of
               bug as the dropdown host. Dialog host is display none/block, not contents. */
            display: inline-block;
            width: fit-content;
            max-width: 100%;
            align-self: flex-start;
            flex: none;
            vertical-align: middle;
        }

        /*
         * Controlled dialogs (no slot="trigger") — panel is top-layer / fixed while
         * yielding. An inline-block host still sizes to the open <dialog> box in some
         * engines and expands parents (Formie nested field cards grow a blank gap).
         */
        :host(:not([data-has-trigger])) {
            position: absolute;
            width: 0;
            height: 0;
            max-width: none;
            margin: 0;
            padding: 0;
            overflow: visible;
            vertical-align: unset;
        }

        .dialog {
            display: flex;
            flex-direction: column;
            width: min(100%, var(--pk-dialog-width, var(--pk-dialog-max-width, 32rem)));
            min-width: var(--pk-dialog-min-width, 0);
            /* Keep UA :modal inset (0) — that + margin:auto centers the panel. Do not
             * unset inset; it breaks centering (field edit landed top-left). */
            height: var(--pk-dialog-height, fit-content);
            min-height: var(--pk-dialog-min-height, 0);
            max-height: var(--pk-dialog-max-height, calc(100vh - 2rem));
            margin: auto;
            padding: 0;
            /* v1 DialogContent: no CSS border — edge is the 1px ring inside --pk-shadow-modal. */
            border: 0;
            border-radius: var(--pk-radius-lg);
            background: var(--pk-color-white);
            box-shadow: var(--pk-shadow-modal);
            color: var(--pk-color-gray-900);
            overflow: hidden;
            opacity: 1;
            transform: scale(1);
        }

        .dialog:focus,
        .dialog:focus-visible {
            outline: none;
        }

        .dialog:not([open]) {
            display: none;
        }

        .dialog--wide {
            --pk-dialog-max-width: 42rem;
        }

        /* motion only via animateWithClass — never auto-animate on [open] alone. */
        .dialog.show {
            animation: pk-dialog-in 0.15s ease;
        }

        .dialog.hide {
            animation: pk-dialog-out 0.15s ease forwards;
        }

        .dialog.pulse {
            animation: pk-dialog-pulse 0.25s ease;
        }

        @keyframes pk-dialog-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pk-dialog-out {
            from {
                opacity: 1;
                transform: scale(1);
            }

            to {
                opacity: 0;
                transform: scale(0.95);
            }
        }

        @keyframes pk-dialog-pulse {
            0%, 100% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.98);
            }
        }

        .dialog.show::backdrop {
            animation: pk-dialog-backdrop-in 0.15s ease;
        }

        .dialog.hide::backdrop {
            animation: pk-dialog-backdrop-in 0.15s ease reverse;
        }

        .dialog::backdrop {
            background: hsl(from var(--pk-color-gray-900) h s l / 0.2);
            opacity: 1;
        }

        @keyframes pk-dialog-backdrop-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .header {
            position: relative;
            display: flex;
            flex-shrink: 0;
            flex-direction: column;
            gap: 0.2rem;
            padding: 1rem;
            border-bottom: 1px solid var(--pk-color-gray-150);
            border-radius: var(--pk-radius-lg) var(--pk-radius-lg) 0 0;
            background: #f3f7fb;
            text-align: left;
        }

        .title {
            margin: 0;
            padding-inline-end: 2rem;
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.2;
            color: var(--pk-color-gray-900);
        }

        .description {
            margin: 0;
            padding-inline-end: 2rem;
            font-size: 0.75rem;
            font-weight: 400;
            line-height: 1.4;
            color: var(--pk-color-gray-500);
        }

        .close {
            --pk-dialog-close-focus-padding: 0.25rem;
            position: absolute;
            top: calc(1rem - var(--pk-dialog-close-focus-padding));
            right: calc(1rem - var(--pk-dialog-close-focus-padding));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: calc(1.125rem + 2 * var(--pk-dialog-close-focus-padding));
            height: calc(1.125rem + 2 * var(--pk-dialog-close-focus-padding));
            margin: 0;
            padding: var(--pk-dialog-close-focus-padding);
            border: 0;
            border-radius: var(--pk-radius-sm);
            background: transparent;
            color: var(--pk-color-gray-600);
            cursor: pointer;
            line-height: 0;
            opacity: 0.7;
            transition: opacity 0.12s ease;
            box-sizing: border-box;
        }

        .close-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 0;
        }

        .close-icon svg {
            display: block;
            width: 1.125rem;
            height: 1.125rem;
        }

        .close:hover {
            opacity: 1;
            background: transparent;
        }

        .close:focus-visible {
            opacity: 1;
            box-shadow: 0 0 0 2px var(--pk-color-gray-600);
        }

        .body {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            padding: 0;
            /* v1 DialogContent inherited CP text defaults (14px / gray-700) — do not
             * downshift body copy to sm/gray-600 or slotted content reads smaller than v1. */
            font-size: var(--pk-font-size-base);
            line-height: 1.5;
            color: var(--pk-color-gray-700);
        }

        .body--padded {
            padding: 1rem;
        }

        .footer {
            display: flex;
            flex-shrink: 0;
            flex-direction: row;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-top: 1px solid var(--pk-color-gray-150);
            border-radius: 0 0 var(--pk-radius-lg) var(--pk-radius-lg);
            background: #e4edf6;
        }
    }
`,Zc=D(x.xmark),Qc=class extends _{constructor(...e){super(...e),this.open=!1,this.label=``,this.description=``,this.disablePointerDismissal=!1,this.withoutHeader=!1,this.withoutBodyPadding=!1,this.size=`default`,this.triggerElement=null,this.previouslyFocused=null,this.yieldingToHostModal=!1,this.hostModalObserver=null,this.yieldBox=null,this.handleDocumentKeyDown=e=>{this.yieldingToHostModal||e.key===`Escape`&&this.open&&me(this)&&(e.preventDefault(),e.stopPropagation(),this.requestClose(`escape`))},this.onHostModalPresenceChange=e=>{e?this.yieldToHostModal():this.restoreFromHostModal()},this.showing=!1,this.handleDialogCancel=e=>{e.preventDefault(),!this.dialogElement.classList.contains(`hide`)&&me(this)&&this.requestClose(`escape`)},this.handleDialogClick=e=>{e.composedPath().some(e=>e instanceof Element&&e.matches(`[data-dialog="close"], [data-dialog-close]`))&&(e.stopPropagation(),this.requestClose(`close-button`))},this.handleDialogPointerDown=async e=>{if(e.target===this.dialogElement){if(!this.disablePointerDismissal){this.requestClose(`pointer-dismiss`);return}await dc(this.dialogElement,`pulse`)}},this.onTriggerClick=e=>{e.preventDefault(),this.open=!0},this.onFooterSlotChange=()=>{this.requestUpdate()}}static{this.styles=Xc}hasCustomHeaderSlot(){return this.querySelector(`:scope > [slot="header"]`)!==null}applyYieldPosition(e){let t=this.dialogElement;t.style.position=`fixed`,t.style.top=`${e.top}px`,t.style.left=`${e.left}px`,t.style.width=`${e.width}px`,t.style.height=`${e.height}px`,t.style.margin=`0`,t.style.maxHeight=`none`,t.style.zIndex=`99`,t.toggleAttribute(`data-yielding`,!0)}clearYieldPosition(){let e=this.dialogElement;e&&(e.style.position=``,e.style.top=``,e.style.left=``,e.style.width=``,e.style.height=``,e.style.margin=``,e.style.maxHeight=``,e.style.zIndex=``,e.removeAttribute(`data-yielding`),this.yieldBox=null)}yieldToHostModal(){if(this.yieldingToHostModal||!this.open||!this.dialogElement?.open)return;let e=this.dialogElement.getBoundingClientRect();this.yieldBox={top:e.top,left:e.left,width:e.width,height:e.height},this.yieldingToHostModal=!0;try{this.dialogElement.close(),this.dialogElement.show(),this.applyYieldPosition(this.yieldBox)}catch{this.yieldingToHostModal=!1,this.clearYieldPosition()}}restoreFromHostModal(){if(this.yieldingToHostModal&&(this.yieldingToHostModal=!1,this.clearYieldPosition(),!(!this.open||!this.dialogElement)))try{this.dialogElement.open&&this.dialogElement.close(),this.dialogElement.showModal()}catch{}}firstUpdated(){this.open&&this.show()}disconnectedCallback(){De(this),this.removeOpenListeners(),super.disconnectedCallback()}updated(e){super.updated(e),!(!e.has(`open`)||!this.hasUpdated)&&this.handleOpenChange()}handleOpenChange(){this.open&&!this.dialogElement.open?this.show():!this.open&&this.dialogElement.open&&(this.open=!0,this.requestClose(`api`))}async show(e=`api`){if(!(this.showing||this.dialogElement?.open)){this.showing=!0;try{let e=new _e;if(!this.dispatchEvent(e)){this.open=!1;return}this.addOpenListeners(),this.previouslyFocused=document.activeElement,this.open=!0,this.dialogElement.showModal(),Oe(this),requestAnimationFrame(()=>{let e=this.querySelector(`[autofocus]`);if(e){(e.shadowRoot?.querySelector(`input, textarea, select, button`)??e).focus({preventScroll:!0});return}this.dialogElement.focus({preventScroll:!0})}),await dc(this.dialogElement,`show`),this.dispatchEvent(new CustomEvent(`pk-open-change`,{detail:{open:!0},bubbles:!0,composed:!0})),this.dispatchEvent(new we)}finally{this.showing=!1}}}async hide(e=`unknown`){await this.requestClose(e)}closeDialog(){this.requestClose(`close-button`)}async requestClose(e=`unknown`){let t=new ge(typeof e==`string`?e:`close-button`);if(!this.dispatchEvent(t)){this.open=!0,await dc(this.dialogElement,`pulse`);return}this.removeOpenListeners(),await dc(this.dialogElement,`hide`),this.open=!1,this.dialogElement.close(),De(this);let n=this.previouslyFocused;this.previouslyFocused=null,n?.isConnected&&window.setTimeout(()=>{n.focus({preventScroll:!0})},0),this.dispatchEvent(new xe),this.dispatchEvent(new CustomEvent(`pk-open-change`,{detail:{open:!1},bubbles:!0,composed:!0}))}forceOverlayReset(){if(this.open=!1,this.yieldingToHostModal=!1,this.clearYieldPosition(),this.removeOpenListeners(),this.dialogElement?.open)try{this.dialogElement.close()}catch{}this.dialogElement?.classList.remove(`hide`,`show`,`pulse`),De(this)}addOpenListeners(){document.addEventListener(`keydown`,this.handleDocumentKeyDown),pe(this),this.hostModalObserver?.disconnect(),this.hostModalObserver=Yc(this.onHostModalPresenceChange),this.onHostModalPresenceChange(Jc())}removeOpenListeners(){document.removeEventListener(`keydown`,this.handleDocumentKeyDown),Ee(this),this.hostModalObserver?.disconnect(),this.hostModalObserver=null,this.yieldingToHostModal=!1,this.clearYieldPosition()}syncHasTriggerAttribute(){this.toggleAttribute(`data-has-trigger`,!!this.triggerElement)}onTriggerSlotChange(e){let[t]=e.target.assignedElements({flatten:!0});this.triggerElement&&this.triggerElement.removeEventListener(`click`,this.onTriggerClick),this.triggerElement=t??null,this.syncHasTriggerAttribute(),this.triggerElement&&this.triggerElement.addEventListener(`click`,this.onTriggerClick)}render(){let e=!this.hasCustomHeaderSlot()&&!this.withoutHeader&&!!this.label,t=e&&!this.withoutBodyPadding,n=this.querySelector(`:scope > [slot="footer"]`)!==null;return v`
            <slot name="trigger" @slotchange=${this.onTriggerSlotChange}></slot>
            <dialog
                part="panel"
                class=${ae({dialog:!0,open:this.open,"dialog--wide":this.size===`wide`})}
                tabindex="-1"
                @cancel=${this.handleDialogCancel}
                @click=${this.handleDialogClick}
                @pointerdown=${this.handleDialogPointerDown}
            >
                <slot name="header">
                    ${e?v`
                            <header part="header" class="header">
                                <h2 part="title" class="title">
                                    <slot name="label">${this.label}</slot>
                                </h2>
                                ${this.description?v`
                                        <p part="description" class="description">
                                            <slot name="description">${this.description}</slot>
                                        </p>
                                    `:v`<slot name="description" hidden></slot>`}
                                <button type="button" class="close" data-dialog="close" aria-label="Close">
                                    <span class="close-icon" aria-hidden="true">${Te(Zc)}</span>
                                </button>
                            </header>
                        `:T}
                </slot>
                <div
                    part="body"
                    class=${ae({body:!0,"body--padded":t})}
                >
                    <slot></slot>
                </div>
                ${n?v`
                        <footer part="footer" class="footer">
                            <slot name="footer" @slotchange=${this.onFooterSlotChange}></slot>
                        </footer>
                    `:v`<slot name="footer" @slotchange=${this.onFooterSlotChange} hidden></slot>`}
            </dialog>
        `}};s([c({type:Boolean,reflect:!0})],Qc.prototype,`open`,void 0),s([c()],Qc.prototype,`label`,void 0),s([c()],Qc.prototype,`description`,void 0),s([c({attribute:`disable-pointer-dismissal`,type:Boolean,reflect:!0})],Qc.prototype,`disablePointerDismissal`,void 0),s([c({attribute:`without-header`,type:Boolean,reflect:!0})],Qc.prototype,`withoutHeader`,void 0),s([c({attribute:`without-body-padding`,type:Boolean,reflect:!0})],Qc.prototype,`withoutBodyPadding`,void 0),s([c({reflect:!0})],Qc.prototype,`size`,void 0),s([r(`dialog`)],Qc.prototype,`dialogElement`,void 0),s([g()],Qc.prototype,`triggerElement`,void 0),Qc=s([h(`pk-dialog`)],Qc);var $c=u({tagName:`pk-dialog`,elementClass:Qc,react:A.default,events:{onPkShow:`pk-show`,onPkAfterShow:`pk-after-show`,onPkHide:`pk-hide`,onPkAfterHide:`pk-after-hide`,onPkOpenChange:`pk-open-change`}}),el=e=>{if(e)return t=>{Gc(t)&&e(t)}},tl=A.forwardRef(function(e,t){let{open:n,disablePointerDismissal:r,withoutHeader:i,withoutBodyPadding:a,onPkShow:o,onPkAfterShow:s,onPkHide:c,onPkAfterHide:l,onPkOpenChange:u,...d}=e;return(0,j.jsx)($c,{ref:t,...d,open:n,...ce([`disablePointerDismissal`,`withoutHeader`,`withoutBodyPadding`],{disablePointerDismissal:r,withoutHeader:i,withoutBodyPadding:a}),...o?{onPkShow:el(o)}:{},...s?{onPkAfterShow:el(s)}:{},...c?{onPkHide:el(c)}:{},...l?{onPkAfterHide:el(l)}:{},...u?{onPkOpenChange:el(u)}:{}})});tl.displayName=`Dialog`;var nl=!0;function rl({open:e,nodeIds:t,includeDeepOption:n=!1,onOpenChange:r}){let i=(0,A.useRef)(null),a=R(e=>e.state?.copyToSiteTargets??[]),o=R(e=>e.nodes),s=R(e=>e.copyNodesToSite),c=(0,A.useMemo)(()=>o.filter(e=>t.includes(e.id)),[o,t]),l=c.some(e=>e.isElementLinked),u=n&&c.some(e=>e.hasDescendants),d=(0,A.useMemo)(()=>a.map(e=>({value:e.id,label:e.name})),[a]),[f,p]=(0,A.useState)(null),[m,h]=(0,A.useState)(!1),[g,_]=(0,A.useState)(nl),[v,y]=(0,A.useState)(!1);(0,A.useEffect)(()=>{e&&(p(a[0]?.id??null),h(!1),_(nl),y(!1),i.current?.show?.())},[e,a]);let b=async()=>{if(!(!f||!t.length||v)){y(!0);try{await s(t,f,m,g),r(!1)}finally{y(!1)}}};return(0,j.jsxs)(tl,{ref:e=>{i.current=e},open:e,label:F(`Copy to site`),onPkOpenChange:e=>r(e.detail.open),children:[(0,j.jsxs)(`div`,{className:`flex flex-col gap-4`,children:[(0,j.jsxs)(`div`,{className:`flex flex-col gap-1.5`,children:[(0,j.jsx)(`span`,{className:`text-sm font-medium text-gray-800`,children:F(`Target site`)}),(0,j.jsx)(We,{id:`copy-to-site-target`,options:d,value:f??void 0,onChange:e=>p(Number(e)),placeholder:F(`Select a site`),"aria-label":F(`Target site`)})]}),u&&(0,j.jsx)(Wc,{label:F(`Include descendants`),checked:m,onCheckedChange:e=>h(!!e)}),l&&(0,j.jsxs)(`div`,{className:`flex items-start justify-between gap-4 rounded-lg border border-black/10 p-3`,children:[(0,j.jsxs)(`div`,{className:`min-w-0 flex-1`,children:[(0,j.jsx)(`div`,{className:`text-sm font-medium`,children:F(`Use elements from target site`)}),(0,j.jsx)(`p`,{className:`mt-1 text-xs text-gray-500`,children:F(`Element-linked nodes will use each linked element on the selected site, when available.`)})]}),(0,j.jsx)(Ge,{checked:g,onCheckedChange:e=>_(!!e),"aria-label":F(`Use elements from target site`)})]})]}),(0,j.jsx)(Tr,{slot:`footer`,type:`button`,variant:`default`,onClick:()=>r(!1),disabled:v,children:F(`Cancel`)}),(0,j.jsx)(Tr,{slot:`footer`,type:`button`,variant:`primary`,onClick:()=>void b(),disabled:!f||v,children:F(`Copy`)})]})}function il({menuId:e,siteId:t}){let n=R(e=>e.init),r=R(e=>e.loading),i=R(e=>e.error),a=R(e=>e.structureDirty),o=R(e=>e.state?.session?.changeCount??0),s=R(e=>e.state?.stagingEnabled??!0),c=a||o>0,l=R(e=>e.menuContentOpen),u=R(e=>e.setMenuContentOpen),d=R(e=>e.copyToSiteDialog),f=R(e=>e.setCopyToSiteDialogOpen),p=R(e=>e.refresh);return Qi(!r&&!i&&s),(0,A.useEffect)(()=>{n(e,t)},[n,e,t]),(0,A.useEffect)(()=>{let e=e=>{c&&(e.preventDefault(),e.returnValue=``)};return window.addEventListener(`beforeunload`,e),()=>window.removeEventListener(`beforeunload`,e)},[c]),r?null:i?(0,j.jsx)(`div`,{className:`rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700`,children:i}):(0,j.jsxs)(j.Fragment,{children:[(0,j.jsx)(`div`,{className:`-mx-6 -mb-2.5 overflow-hidden rounded-md border border-black/10 bg-white shadow-[0_0_0_1px_rgba(205,216,228,0.25),0_2px_12px_rgba(205,216,228,0.5)]`,children:(0,j.jsx)(Bc,{})}),(0,j.jsx)(Uc,{open:l,menuId:e,siteId:t,onOpenChange:u,onSaved:()=>void p()}),(0,j.jsx)(rl,{open:d.open,nodeIds:d.nodeIds,includeDeepOption:d.includeDeepOption,onOpenChange:f})]})}var al=[Re(),Ce,y`
        @layer pk-component {
            :host {
                /* Flex column parents stretch cross-axis size — pin to content.
                   (inline-block + align-self; same class of fix as dialog / dropdown.) */
                display: inline-block;
                max-width: 100%;
                align-self: flex-start;
                flex: none;
                vertical-align: middle;
            }

            :host([data-pk-group-orientation]) {
                display: inline-flex;
                vertical-align: middle;
                flex: 0 0 auto;
                align-self: auto;
            }

            :host([data-pk-group-orientation]) ::slotted([slot='trigger']) {
                --pk-bg-start-start-radius: inherit;
                --pk-bg-start-end-radius: inherit;
                --pk-bg-end-start-radius: inherit;
                --pk-bg-end-end-radius: inherit;
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]) {
                margin-inline-start: var(--pk-bg-horizontal-indent, 0);
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-join]) {
                margin-block-start: var(--pk-bg-vertical-indent, 0);
            }

            :host([data-pk-group-orientation='horizontal'][data-pk-group-join]:has([slot='trigger'][variant='outline'], [slot='trigger'][variant='dashed'])) {
                margin-inline-start: var(--pk-bg-horizontal-indent-outlined, 0);
            }

            :host([data-pk-group-orientation='vertical'][data-pk-group-join]:has([slot='trigger'][variant='outline'], [slot='trigger'][variant='dashed'])) {
                margin-block-start: var(--pk-bg-vertical-indent-outlined, 0);
            }

            /* Match pk-popup's arrow fill to the panel surface when with-arrow is on. */
            :host([with-arrow]) {
                --pk-popup-arrow-color: var(--pk-color-white);
                --pk-popup-arrow-size: 8px;
            }

            .panel {
                box-sizing: border-box;
                width: 18rem;
                padding: 1rem;
                border-radius: var(--pk-radius-md);
                background: var(--pk-color-white);
                box-shadow: var(--pk-shadow-popover);
                /* Craft CP body text (~gray-700), not gray-900. */
                color: var(--pk-color-gray-700);
            }

            /* Flush panels for command/menu chrome that owns its own inset (variable picker, etc.).
               Match kit v1 PopoverContent min-w 260px / max-w 360px: without min-width,
               width max-content shrinks to short labels and looks narrower than the old picker. */
            :host([flush]) .panel {
                width: max-content;
                min-width: var(--pk-popover-flush-min-width, 16.25rem);
                max-width: min(var(--pk-popover-flush-max-width, 22.5rem), 100vw - 1rem);
                padding: 0;
            }

            .panel[hidden] {
                display: none !important;
            }
        }
    `],ol=class extends _{constructor(...e){super(...e),this.open=!1,this.placement=`bottom`,this.sideOffset=4,this.flush=!1,this.withArrow=!1,this.for=``,this.anchor=null,this.triggerElement=null,this.closing=!1,this.panelAnimated=!1,this.triggerId=E(`pk-popover-trigger`),this.dismissRegistered=!1,this.syncingOpenSideEffects=!1,this.exitAnimationPromise=null,this.handleToggleClick=e=>{e.preventDefault(),e.stopPropagation(),!this.closing&&(this.open=!this.open)},this.onDocumentPointerDown=e=>{this.isPointerInside(e)||this.closing||this.closePopover(`light-dismiss`)},this.onDocumentKeyDown=e=>{e.key!==`Escape`||!me(this)||this.closing||(e.preventDefault(),e.stopPropagation(),this.closePopover(`escape`))}}static{this.styles=al}get panelElement(){return this.popupElement?.getContentElement()??null}disconnectedCallback(){this.closePopover(`api`,!0),super.disconnectedCallback()}willUpdate(e){e.has(`open`)&&this.open===!1&&e.get(`open`)===!0&&!this.syncingOpenSideEffects&&!this.closing&&(this.closing=!0,this.panelAnimated=!1)}async updated(e){if(super.updated(e),!e.has(`open`)||this.syncingOpenSideEffects)return;let t=e.get(`open`);t!==this.open&&(t===void 0&&this.open===!1||(this.open?await this.openPopover():await this.closePopover(`api`)))}onTriggerSlotChange(e){let[t]=e.target.assignedElements({flatten:!0});this.unbindTrigger(this.triggerElement),this.triggerElement=t??null,this.bindTrigger(this.triggerElement)}bindTrigger(e){e&&(e.id||=this.triggerId,e.setAttribute(`aria-haspopup`,`dialog`),e.addEventListener(`click`,this.handleToggleClick),this.syncExpanded())}unbindTrigger(e){e?.removeEventListener(`click`,this.handleToggleClick)}async openPopover(){if(!this.getAnchor())return;if(this.exitAnimationPromise&&await this.exitAnimationPromise,this.dismissRegistered&&this.open){this.panelElement&&(this.panelElement.hidden=!1),this.syncExpanded();return}if(!this.dispatchEvent(new _e)){this.syncingOpenSideEffects=!0,this.open=!1,this.syncingOpenSideEffects=!1;return}this.syncingOpenSideEffects=!0,this.open=!0,this.syncingOpenSideEffects=!1,this.closing=!1,this.panelAnimated=!1,this.panelElement&&(this.panelElement.hidden=!1,ye(this.panelElement,this.placement)),this.syncExpanded(),this.registerDismissHandlers(),await this.updateComplete;let e=await ve(this.popupElement,this.placement);this.panelElement&&ye(this.panelElement,e),this.panelAnimated=!0,this.dispatchEvent(new we),this.dispatchEvent(new CustomEvent(`pk-open-change`,{detail:{open:!0},bubbles:!0,composed:!0}))}async closePopover(e=`unknown`,t=!1){if(this.exitAnimationPromise)return this.exitAnimationPromise;if(!this.dismissRegistered&&!this.closing&&!this.open)return;let n=new ge(e);if(!this.dispatchEvent(n)){this.syncingOpenSideEffects=!0,this.open=!0,this.syncingOpenSideEffects=!1,this.closing=!1,this.panelAnimated=!0;return}this.unregisterDismissHandlers(),this.closing=!0,this.panelAnimated=!1,this.open&&(this.syncingOpenSideEffects=!0,this.open=!1,this.syncingOpenSideEffects=!1);let r=async()=>{t||(await this.updateComplete,await this.waitForExitAnimation()),this.closing=!1,this.panelAnimated=!1,this.panelElement&&(this.panelElement.hidden=!0,this.panelElement.removeAttribute(`data-side`)),this.syncExpanded(),this.dispatchEvent(new xe),this.dispatchEvent(new CustomEvent(`pk-open-change`,{detail:{open:!1},bubbles:!0,composed:!0}))};return this.exitAnimationPromise=r().finally(()=>{this.exitAnimationPromise=null}),this.exitAnimationPromise}waitForExitAnimation(){let e=this.panelElement;return e?new Promise(t=>{let n=!1,r=()=>{n||(n=!0,e.removeEventListener(`animationend`,i),window.clearTimeout(a),e.classList.remove(`closing`),t())},i=t=>{t.target===e&&t.animationName.startsWith(`pk-popup-content-out`)&&r()};e.classList.add(`closing`),e.addEventListener(`animationend`,i);let a=window.setTimeout(r,150)}):Promise.resolve()}getAnchor(){return this.anchor?this.anchor:this.for?ac(this,this.for):this.triggerElement?this.triggerElement:null}registerDismissHandlers(){this.dismissRegistered||(pe(this),this.dismissRegistered=!0,document.addEventListener(`pointerdown`,this.onDocumentPointerDown,!0),document.addEventListener(`keydown`,this.onDocumentKeyDown,!0))}unregisterDismissHandlers(){this.dismissRegistered&&=(Ee(this),!1),document.removeEventListener(`pointerdown`,this.onDocumentPointerDown,!0),document.removeEventListener(`keydown`,this.onDocumentKeyDown,!0)}isPointerInside(e){return be(e,{host:this,anchor:this.getAnchorElement(),panel:this.panelElement})}getAnchorElement(){return this.anchor instanceof HTMLElement?this.anchor:this.triggerElement?this.triggerElement:this.for?ac(this,this.for):null}syncExpanded(){this.triggerElement?.setAttribute(`aria-expanded`,this.open?`true`:`false`)}render(){let e=this.getAnchor();return v`
            <slot name="trigger" @slotchange=${this.onTriggerSlotChange}></slot>
            <pk-popup
                .active=${this.open||this.closing}
                .anchor=${e??``}
                .placement=${this.placement}
                .distance=${this.sideOffset}
                .arrow=${this.withArrow}
                flip
                shift
            >
                <div
                    part="panel"
                    class=${ae({panel:!0,"pk-popup-content":!0,closing:this.closing})}
                    ?hidden=${!this.open&&!this.closing}
                    data-open=${this.panelAnimated&&!this.closing?``:T}
                    tabindex=${this.open?`-1`:T}
                >
                    <slot></slot>
                </div>
            </pk-popup>
        `}};s([c({type:Boolean,reflect:!0})],ol.prototype,`open`,void 0),s([c({reflect:!0})],ol.prototype,`placement`,void 0),s([c({attribute:`side-offset`,type:Number})],ol.prototype,`sideOffset`,void 0),s([c({type:Boolean,reflect:!0})],ol.prototype,`flush`,void 0),s([c({attribute:`with-arrow`,type:Boolean,reflect:!0})],ol.prototype,`withArrow`,void 0),s([c({reflect:!0})],ol.prototype,`for`,void 0),s([c({attribute:!1})],ol.prototype,`anchor`,void 0),s([r(`pk-popup`)],ol.prototype,`popupElement`,void 0),s([g()],ol.prototype,`triggerElement`,void 0),s([g()],ol.prototype,`closing`,void 0),s([g()],ol.prototype,`panelAnimated`,void 0),ol=s([h(`pk-popover`)],ol);var sl=u({tagName:`pk-popover`,elementClass:ol,react:A.default,events:{onPkShow:`pk-show`,onPkAfterShow:`pk-after-show`,onPkHide:`pk-hide`,onPkAfterHide:`pk-after-hide`,onPkOpenChange:`pk-open-change`}}),cl=e=>{if(e)return t=>{Gc(t)&&e(t)}},ll=A.forwardRef(function(e,t){let{open:n,flush:r,withArrow:i,onPkShow:a,onPkAfterShow:o,onPkHide:s,onPkAfterHide:c,onPkOpenChange:l,...u}=e;return(0,j.jsx)(sl,{ref:t,...u,...n===void 0?{}:{open:n},...ce([`flush`,`withArrow`],{flush:r,withArrow:i}),...a?{onPkShow:cl(a)}:{},...o?{onPkAfterShow:cl(o)}:{},...s?{onPkHide:cl(s)}:{},...c?{onPkAfterHide:cl(c)}:{},...l?{onPkOpenChange:cl(l)}:{}})});ll.displayName=`Popover`;var ul=[{id:`type`,label:`Type`}];function dl(){let e=R(e=>e.visibleColumns),t=R(e=>e.setColumnVisible);return(0,j.jsxs)(ll,{placement:`bottom-end`,className:`navigation-node-tree-view-popover`,children:[(0,j.jsxs)(Tr,{slot:`trigger`,type:`button`,variant:`default`,withCaret:!0,children:[(0,j.jsx)(vr,{slot:`start`,icon:`sliders`,className:`size-3.5 text-gray-500`}),(0,j.jsx)(`span`,{children:F(`View`)})]}),(0,j.jsxs)(`div`,{className:`grid w-full grid-cols-[120px_minmax(0,1fr)]`,children:[(0,j.jsx)(`div`,{className:`flex items-center self-stretch border-r border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-snug text-gray-600`,children:F(`Table Columns`)}),(0,j.jsx)(`div`,{className:`flex flex-col justify-center px-4 py-2`,children:ul.map(n=>(0,j.jsxs)(`label`,{className:`flex min-h-9 cursor-pointer items-center gap-2 text-[13px] leading-tight text-gray-900`,children:[(0,j.jsx)($a,{checked:e[n.id],onPkChange:e=>t(n.id,e.detail.checked)}),(0,j.jsx)(`span`,{className:`min-w-0 flex-1 truncate`,children:F(n.label)})]},n.id))})]})]})}var fl=`navigation-status-filter`,pl=[{value:`all`,label:`All`,status:`all`},{value:`enabled`,label:`Enabled`,status:`enabled`},{value:`disabled`,label:`Disabled`,status:`disabled`},{value:`trashed`,label:`Trashed`}];function ml(){let e=(0,A.useRef)(null),t=R(e=>e.state),n=R(e=>e.siteId),r=R(e=>e.nodes),i=R(e=>e.selectedNodeIds),a=R(e=>e.statusFilter),o=R(e=>e.setStatusFilter),s=R(e=>e.setSelectedNodesStatus),c=R(e=>e.duplicateSelectedNodes),l=R(e=>e.deleteSelectedNodes),u=R(e=>e.refresh),d=R(e=>e.openCopyToSiteDialog),f=t?.elementType??``,p=t?.menu.maxLevels??null,m=!p||p>1,h=i.length>0,g=r.filter(e=>i.includes(e.id)),_=g.filter(e=>!e.pendingDelete).map(e=>e.id),v=_.length>0,y=i.length===1&&!g[0]?.pendingDelete,b=pl.find(e=>e.value===a)??pl[0],[x,ee]=(0,A.useState)(!1),S=F(`Duplicate`),C=F(`Delete`);return(0,j.jsxs)(`div`,{id:`navigation-builder-toolbar`,className:`flex min-w-0 flex-1 flex-wrap items-center gap-1.5`,children:[(0,j.jsxs)(yc,{open:x,placement:`bottom-start`,onPkSelect:e=>{let t=e.detail?.value;!t||!pl.some(e=>e.value===t)||(o(t),ee(!1))},onPkOpenChange:e=>ee(e.detail.open),children:[(0,j.jsxs)(Tr,{slot:`trigger`,type:`button`,variant:`default`,withCaret:!0,children:[b.value===`trashed`?(0,j.jsx)(vr,{slot:`start`,icon:`trash`,className:`size-3 text-gray-500`}):b.status?(0,j.jsx)(Ue,{slot:`start`,status:eo(b.status)}):null,(0,j.jsx)(`span`,{children:F(b.label)})]}),pl.map(e=>(0,j.jsxs)(bc,{value:e.value,type:`radio`,radioGroup:fl,checked:a===e.value,children:[e.value===`trashed`?(0,j.jsx)(vr,{slot:`prefix`,icon:`trash`,className:`size-3 text-gray-500`}):e.status?(0,j.jsx)(Ue,{slot:`prefix`,status:eo(e.status)}):null,F(e.label)]},e.value))]}),(0,j.jsx)(dl,{}),h&&(0,j.jsxs)(j.Fragment,{children:[(0,j.jsxs)(yc,{placement:`bottom-end`,onPkSelect:e=>{let t=e.detail?.value;(t===`enabled`||t===`disabled`)&&s(t)},children:[(0,j.jsx)(Tr,{slot:`trigger`,type:`button`,variant:`default`,withCaret:!0,children:(0,j.jsx)(`span`,{children:F(`Set status`)})}),(0,j.jsxs)(bc,{value:`enabled`,disabled:!v,children:[(0,j.jsx)(Ue,{slot:`prefix`,status:`enabled`}),F(`Enabled`)]}),(0,j.jsxs)(bc,{value:`disabled`,disabled:!v,children:[(0,j.jsx)(Ue,{slot:`prefix`,status:`disabled`}),F(`Disabled`)]})]}),(0,j.jsxs)(yc,{ref:t=>{e.current=t},placement:`bottom-end`,onPkSelect:t=>{let r=t.detail?.value;if(!r)return;let a=t=>{Tc(e.current,t)};switch(r){case`edit`:y&&a(()=>{to(f,i[0],n,()=>void u())});return;case Sc:a(()=>{d(_,m)});return;case S:case`${S}-shallow`:a(()=>{c(!1)});return;case`${S}-deep`:a(()=>{c(!0)});return;case C:case`${C}-shallow`:a(()=>{l(!1)});return;case`${C}-deep`:a(()=>{l(!0)});return;default:return}},children:[(0,j.jsxs)(Tr,{slot:`trigger`,type:`button`,variant:`default`,withCaret:!0,children:[(0,j.jsx)(vr,{slot:`start`,icon:`gear`,className:`size-3.5 text-gray-500`}),(0,j.jsx)(`span`,{children:F(`Actions`)})]}),(0,j.jsx)(bc,{value:`edit`,disabled:!y,children:F(`Edit node`)}),(0,j.jsx)(Cc,{nodeIds:_,disabled:!v,showIcon:!1}),(0,j.jsx)(wc,{label:S,disabled:!v,includeDeepOption:m,deepAsSubmenu:!0}),(0,j.jsx)(xc,{}),(0,j.jsx)(wc,{label:C,disabled:!v,includeDeepOption:m,deepAsSubmenu:!0,destructive:!0})]})]})]})}function hl({showToolbar:e=!0}){return e?(0,j.jsx)(ml,{}):null}function gl(){let e=R(e=>e.state?.menu.instructionsHtml??null);return e?(0,j.jsx)(`div`,{className:`navigation-nodes-instructions text-sm text-gray-600 [&_:is(p,ul,ol):last-child]:mb-0`,dangerouslySetInnerHTML:{__html:e}}):null}function _l({initialSettingsUrl:e,initialCanEditSettings:t}){let n=R(e=>e.state),r=R(e=>e.loading),i=R(e=>e.error),a=R(e=>e.publishing),o=R(e=>e.discarding),s=R(e=>e.saveFeedbackState),c=R(e=>e.structureDirty),l=R(e=>e.publish),u=R(e=>e.discard),d=R(e=>e.setMenuContentOpen),[f,p]=(0,A.useState)(!1),m=n?.settingsUrl??e,h=n?.permissions.canEditSettings??t,g=n?.stagingEnabled??!0,_=n?.menuContent.hasFields??!1,v=n?.session?.changeCount??0,y=c||v>0,b=a||o,x=v>0?F(`Save ({count})`,{count:v}):F(`Save`);return(0,A.useEffect)(()=>{if(a||s!==`success`){p(!1);return}p(!0);let e=setTimeout(()=>{p(!1)},2200);return()=>{clearTimeout(e)}},[a,s]),r||i||!g?h?(0,j.jsxs)(Tr,{href:m,target:`_blank`,rel:`noopener noreferrer`,variant:`default`,className:`gap-1.5`,children:[F(`Settings`),(0,j.jsx)(vr,{slot:`end`,icon:`arrow-up-right-from-square`,className:`size-3.5 opacity-70`})]}):null:(0,j.jsxs)(`div`,{className:`flex items-center gap-2`,children:[_&&(0,j.jsx)(Tr,{type:`button`,variant:`default`,onClick:e=>{e.currentTarget.blur(),d(!0)},children:F(`Menu content`)}),h&&(0,j.jsxs)(Tr,{href:m,target:`_blank`,rel:`noopener noreferrer`,variant:`default`,className:`gap-1.5`,children:[F(`Settings`),(0,j.jsx)(vr,{slot:`end`,icon:`arrow-up-right-from-square`,className:`size-3.5 opacity-70`})]}),y&&(0,j.jsx)(Tr,{type:`button`,variant:`default`,loading:o,disabled:b,spinnerVariant:`outline`,onClick:()=>void u(),children:F(`Discard`)}),(0,j.jsx)(Tr,{type:`button`,variant:`primary`,loading:a,disabled:b,onClick:()=>void l(),children:(0,j.jsxs)(`span`,{className:Xr(`inline-flex items-center`,f?`relative`:``),children:[f&&(0,j.jsx)(`span`,{className:`pointer-events-none absolute inset-0 flex items-center justify-center`,children:(0,j.jsx)(vr,{icon:`check`,className:`size-3 transition-opacity duration-300`})}),(0,j.jsx)(`span`,{className:f?`text-transparent`:``,children:x})]})})]})}var vl=(e={})=>{let t={...e};return typeof t.class==`string`&&(t.className=t.class,delete t.class),t},yl=(0,A.createContext)(null),bl=()=>{let e=(0,A.useContext)(yl);if(!e)throw Error(`useSchemaEngineContext must be used within a SchemaEngineProvider`);return e},xl=e=>{if(Array.isArray(e))return e.length>0;if(!e)return!1;if(typeof e==`object`){let t=e;if(Array.isArray(t.errors))return t.errors.length>0}return!!e},Sl=(e,t=new Set)=>{if(Array.isArray(e))return e.forEach(e=>{Sl(e,t)}),t;if(!e||typeof e!=`object`)return t;let n=e;return n.$field&&typeof n.name==`string`&&n.name&&t.add(n.name),n.children&&Sl(n.children,t),n.schema&&Sl(n.schema,t),t},Cl=(e,t)=>{if(!e||!t)return!1;let n=Sl(t);return Array.from(n).some(t=>xl(e[t]))},wl=/^(.+?) (cannot be blank\.|must be .+)$/,Tl=(e,t)=>{if(!t)return e;let n=String(e).match(wl);if(n){let[,,e]=n;return`${t} ${e}`}return`${t} ${e}`},El=Object.assign(({name:e,label:t,instructions:n,required:r,warning:i,children:a,schemaNode:o})=>{let s=bl(),c=e||t||`field`,l=(0,A.useMemo)(()=>Array.from(Sl(o?.children||[])),[o]),u=(0,A.useSyncExternalStore)(s.store.subscribe.bind(s.store),()=>s.getErrorMapFields()||{},()=>({}));return(0,j.jsx)(O,{name:c,label:t,instructions:n,required:r,warning:i,errors:(0,A.useMemo)(()=>{if(e&&typeof s?.getGroupedErrorsForPath==`function`)return s.getGroupedErrorsForPath(e)||[];if(!l.length)return[];let n=[];return l.forEach(r=>{(u[r]||[]).forEach(r=>{n.push(Tl(String(r),t||e||c))}),Object.entries(u).forEach(([i,a])=>{i.startsWith(`${r}.`)&&(a||[]).forEach(r=>{n.push(Tl(String(r),t||e||c))})})}),Array.from(new Set(n))},[u,c,s,t,e,l]),children:(0,j.jsx)(`div`,{"data-pk-field-wrap-controls":``,style:{display:`flex`,alignItems:`center`,flexDirection:`row`,flexWrap:`wrap`,gap:`0.5rem`},children:a})})},{usesSchemaNode:!0}),Dl=class extends _{constructor(...e){super(...e),this.value=``,this.disabled=!1,this.selected=!1,this.focusIndex=-1}focusControl(){this.shadowRoot?.querySelector(`.trigger`)?.focus()}handleClick(){this.disabled||this.dispatchEvent(new CustomEvent(`pk-tab-select`,{detail:{value:this.value},bubbles:!0,composed:!0}))}handleKeyDown(e){this.dispatchEvent(new CustomEvent(`pk-tab-keydown`,{detail:{event:e,value:this.value},bubbles:!0,composed:!0}))}renderTrigger(e){return v`
            <button
                part="trigger"
                type="button"
                class=${e}
                role="tab"
                ?disabled=${this.disabled}
                aria-disabled=${this.disabled?`true`:T}
                aria-selected=${this.selected?`true`:`false`}
                tabindex=${this.focusIndex}
                aria-controls=${this.panelId??T}
                @click=${this.handleClick}
                @keydown=${this.handleKeyDown}
            >
                <span part="icon" class="icon">
                    <slot name="icon"></slot>
                </span>
                <span part="label" class="label">
                    <slot></slot>
                </span>
                <span part="status" class="status">
                    <slot name="status"></slot>
                </span>
            </button>
        `}};s([c()],Dl.prototype,`value`,void 0),s([c({type:Boolean,reflect:!0})],Dl.prototype,`disabled`,void 0),s([c({type:Boolean,reflect:!0})],Dl.prototype,`selected`,void 0),s([c({type:Number,attribute:`focus-index`})],Dl.prototype,`focusIndex`,void 0),s([c()],Dl.prototype,`panelId`,void 0);var Ol=y`
    @layer pk-component {
        :host {
            /* Size to the shadow trigger. Prefer flex-start so a short list line
             * (or host utilities like Tailwind items-center) cannot stretch the
             * host shorter than the trigger and clip the modal active underline. */
            display: inline-flex;
            flex-shrink: 0;
            align-self: flex-start;
            height: auto;
            min-height: auto;
            align-items: stretch;
            /* Pin type metrics for slotted labels — vars cascade from pk-tabs. */
            font-family: var(--pk-font-family);
            font-size: var(--pk-tabs-trigger-font-size, 13px);
            font-weight: var(--pk-tabs-trigger-font-weight, 400);
            line-height: var(--pk-tabs-trigger-line-height, 1.4);
            color: var(--pk-tabs-trigger-color, inherit);
        }

        .trigger {
            position: relative;
            display: var(--pk-tabs-trigger-display, inline-flex);
            align-items: center;
            justify-content: var(--pk-tabs-trigger-justify, center);
            gap: var(--pk-tabs-trigger-gap, 0.5rem);
            width: var(--pk-tabs-trigger-width, auto);
            min-height: var(--pk-tabs-trigger-min-height, 2rem);
            padding: var(--pk-tabs-trigger-padding-block, 0.375rem)
                var(--pk-tabs-trigger-padding-inline, 0.75rem);
            border: 0;
            border-top: var(--pk-tabs-trigger-border-top, 0 solid transparent);
            border-radius: var(--pk-tabs-trigger-radius, var(--pk-radius-sm));
            background: transparent;
            color: inherit;
            font: inherit;
            font-family: var(--pk-font-family);
            font-size: var(--pk-tabs-trigger-font-size, 13px);
            font-weight: var(--pk-tabs-trigger-font-weight, 400);
            line-height: var(--pk-tabs-trigger-line-height, 1.4);
            text-align: var(--pk-tabs-trigger-text-align, center);
            text-transform: var(--pk-tabs-trigger-text-transform, none);
            white-space: nowrap;
            cursor: pointer;
            outline: none;
            box-shadow: none;
            box-sizing: border-box;
            transition: background-color 0.12s ease, color 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
        }

        /* Collapse optional icon/status lanes when nothing is slotted. */
        .icon,
        .status {
            display: none;
            flex: none;
            align-items: center;
            justify-content: center;
            line-height: 0;
        }

        .icon:has(::slotted(*)),
        .status:has(::slotted(*)) {
            display: inline-flex;
        }

        .icon {
            width: var(--pk-tabs-trigger-icon-size, 1.125rem);
            height: var(--pk-tabs-trigger-icon-size, 1.125rem);
            font-size: var(--pk-tabs-trigger-icon-size, 1.125rem);
            color: var(--pk-tabs-trigger-icon-color, inherit);
        }

        .icon ::slotted(*) {
            display: block;
            max-width: 100%;
            max-height: 100%;
            /* Kill pk-icon text-baseline nudge so logos/icons sit on the flex midline. */
            vertical-align: 0;
        }

        .label {
            display: inline-flex;
            flex: var(--pk-tabs-trigger-label-flex, 0 1 auto);
            align-items: center;
            min-width: 0;
            line-height: inherit;
        }

        .status {
            margin-inline-start: var(--pk-tabs-trigger-status-margin, 0);
            color: var(--pk-tabs-trigger-status-color, inherit);
        }

        .trigger:hover:not(:disabled):not([aria-disabled='true']):not([aria-selected='true']) {
            border-top-color: transparent;
            background: var(--pk-tabs-trigger-hover-bg, rgb(255 255 255 / 0.7));
            color: var(--pk-tabs-trigger-hover-color, var(--pk-color-gray-700));
        }

        /*
         * Focus ring is only for :focus-visible on a non-selected tab (manual
         * activation). Selected + focus-visible must NOT draw a ring — active
         * chrome is the underline (mouse) or is replaced by the modal focus box
         * via --pk-tabs-trigger-focus-selected-shadow when set.
         */
        .trigger:focus-visible:not([aria-selected='true']) {
            box-shadow: inset 0 0 0 2px var(--pk-color-sky-600);
        }

        .trigger:disabled,
        .trigger[aria-disabled='true'] {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .trigger[aria-selected='true'],
        :host([selected]) .trigger {
            border-top: var(--pk-tabs-trigger-selected-border-top, var(--pk-tabs-trigger-border-top, 0 solid transparent));
            border-radius: var(--pk-tabs-trigger-selected-radius, var(--pk-radius-sm));
            background: var(--pk-tabs-trigger-selected-bg, var(--pk-color-white));
            color: var(--pk-tabs-trigger-selected-color, var(--pk-color-gray-800));
            box-shadow: var(--pk-tabs-trigger-selected-shadow, 0 1px 2px rgba(31, 41, 51, 0.12));
        }

        .trigger[aria-selected='true']:hover,
        :host([selected]) .trigger:hover {
            background: var(--pk-tabs-trigger-selected-hover-bg, var(--pk-tabs-trigger-selected-bg, var(--pk-color-white)));
            color: var(--pk-tabs-trigger-selected-hover-color, var(--pk-tabs-trigger-selected-color, var(--pk-color-gray-800)));
        }

        /* screen3: keyboard focus on the active tab → full inset box, no underline. */
        .trigger[aria-selected='true']:focus-visible,
        :host([selected]) .trigger:focus-visible {
            box-shadow: var(
                --pk-tabs-trigger-focus-selected-shadow,
                var(--pk-tabs-trigger-selected-shadow, 0 0 #0000)
            );
        }

        .trigger[aria-selected='true']:focus-visible::after,
        :host([selected]) .trigger:focus-visible::after {
            height: var(--pk-tabs-trigger-focus-selected-underline-height, var(--pk-tabs-trigger-underline-height, 0));
        }

        .trigger[aria-selected='true']::after,
        :host([selected]) .trigger::after {
            content: '';
            position: absolute;
            right: var(--pk-tabs-trigger-underline-inset, 15px);
            bottom: 0;
            left: var(--pk-tabs-trigger-underline-inset, 15px);
            height: var(--pk-tabs-trigger-underline-height, 0);
            /* Keep the bar above the list hairline when both meet at the clip edge. */
            z-index: 1;
            background: var(--pk-color-sky-600);
            pointer-events: none;
        }

        /*
         * Validation error chrome (v1 ModalTabs / PaneTabs text-error).
         * Host sets data-has-errors; light-DOM text-* cannot pierce the trigger.
         */
        :host([data-has-errors]) {
            --pk-tabs-trigger-color: var(--pk-color-error, #d81f23);
            --pk-tabs-trigger-hover-color: var(--pk-color-rose-700, #be123c);
            --pk-tabs-trigger-selected-color: var(--pk-color-error, #d81f23);
            --pk-tabs-trigger-selected-hover-color: var(--pk-color-rose-700, #be123c);
            --pk-tabs-trigger-icon-color: inherit;
            --pk-tabs-trigger-status-color: inherit;
        }
    }
`,kl=class extends Dl{static{this.styles=Ol}render(){return this.renderTrigger(`trigger pk-tabs__trigger`)}};kl=s([h(`pk-tab`)],kl);var Al=y`
    @layer pk-component {
        :host {
            display: block;
            flex-shrink: 0;
            width: 100%;
        }

        .heading {
            margin: 0;
            padding: var(--pk-tabs-heading-padding, 0.75rem 0.5rem 0.375rem);
            color: var(--pk-tabs-heading-color, var(--pk-color-gray-400));
            font-family: var(--pk-font-family);
            font-size: var(--pk-tabs-heading-font-size, 11px);
            font-weight: var(--pk-tabs-heading-font-weight, 600);
            line-height: 1.3;
            letter-spacing: var(--pk-tabs-heading-letter-spacing, 0.04em);
            text-transform: var(--pk-tabs-heading-text-transform, uppercase);
            user-select: none;
            pointer-events: none;
        }
    }
`,jl=class extends _{static{this.styles=Al}connectedCallback(){super.connectedCallback(),this.setAttribute(`role`,`presentation`)}render(){return v`
            <div part="heading" class="heading">
                <slot></slot>
            </div>
        `}};jl=s([h(`pk-tab-heading`)],jl);var Ml=class extends _{constructor(...e){super(...e),this.value=``,this.hidden=!0}renderPanel(e){return v`
            <div
                part="content"
                class=${e}
                role="tabpanel"
                id=${this.tabId??T}
                aria-labelledby=${this.tabId??T}
                aria-hidden=${this.hidden?`true`:`false`}
                tabindex=${this.hidden?T:`0`}
            >
                <slot></slot>
            </div>
        `}};s([c()],Ml.prototype,`value`,void 0),s([c({type:Boolean,reflect:!0})],Ml.prototype,`hidden`,void 0),s([c()],Ml.prototype,`tabId`,void 0);var Nl=y`
    @layer pk-component {
        :host {
            /* Flex column so .content can own overflow when the host is height-capped
             * by a modal/pane parent (flex: 1 1 0% + min-height: 0). */
            display: flex;
            flex-direction: column;
            flex: var(--pk-tabs-panel-flex, none);
            min-height: var(--pk-tabs-panel-min-height, 0);
            min-width: 0;
            overflow: hidden;
        }

        :host([hidden]) {
            display: none !important;
        }

        .content {
            flex: 1 1 auto;
            min-height: 0;
            padding: var(--pk-tabs-panel-padding, 0);
            overflow-y: auto;
            border-radius: var(--pk-tabs-panel-radius, 0);
            background: var(--pk-tabs-panel-bg, transparent);
            outline: none;
            font-family: var(--pk-font-family);
            font-size: var(--pk-tabs-panel-font-size, var(--pk-font-size-base));
            line-height: var(--pk-line-height);
        }

        .content:focus-visible {
            box-shadow: inset 0 0 0 2px var(--pk-color-sky-600);
            border-radius: var(--pk-radius-sm);
        }
    }
`,Pl=class extends Ml{static{this.styles=Nl}render(){return this.renderPanel(`content pk-tabs__content`)}};Pl=s([h(`pk-tab-panel`)],Pl);var Fl=y`
    @layer pk-component {
        :host {
            display: block;
            max-width: 100%;
            font-family: var(--pk-font-family);
            font-size: var(--pk-font-size-base);
            line-height: var(--pk-line-height);
            /* Frame chrome on the host (matches v1 PaneTabs root) so consumer
             * overflow utilities on the host do not clip the pane shadow. */
            border-radius: var(--pk-tabs-root-radius);
            box-shadow: var(--pk-tabs-root-shadow);
            overflow: var(--pk-tabs-root-overflow);

            /* Root */
            --pk-tabs-root-gap: 0.75rem;
            --pk-tabs-root-height: auto;
            --pk-tabs-root-radius: 0;
            --pk-tabs-root-shadow: none;
            --pk-tabs-root-overflow: visible;

            /* List */
            --pk-tabs-list-display: inline-flex;
            --pk-tabs-list-width: fit-content;
            --pk-tabs-list-align-self: flex-start;
            --pk-tabs-list-align-items: center;
            --pk-tabs-list-padding: 2px;
            --pk-tabs-list-border-width: 1px;
            --pk-tabs-list-border-color: var(--pk-color-gray-150);
            --pk-tabs-list-border-bottom: var(--pk-tabs-list-border-width) solid var(--pk-tabs-list-border-color);
            --pk-tabs-list-radius: var(--pk-radius-md);
            --pk-tabs-list-bg: color-mix(in oklab, var(--pk-color-gray-100) 90%, transparent);
            --pk-tabs-list-shadow: 0 1px 2px rgba(31, 41, 51, 0.06);
            /* Transparent no-op — keyword none in a multi-shadow list invalidates the whole property. */
            --pk-tabs-list-inset-shadow: 0 0 #0000;
            --pk-tabs-list-color: var(--pk-color-gray-500);
            --pk-tabs-list-overflow-x: auto;
            --pk-tabs-list-overflow-y: visible;

            /* Trigger (inherited by pk-tab) */
            --pk-tabs-trigger-display: inline-flex;
            --pk-tabs-trigger-justify: center;
            --pk-tabs-trigger-width: auto;
            --pk-tabs-trigger-gap: 0.5rem;
            --pk-tabs-trigger-min-height: 2rem;
            --pk-tabs-trigger-padding-block: 0.375rem;
            --pk-tabs-trigger-padding-inline: 0.75rem;
            --pk-tabs-trigger-radius: var(--pk-radius-sm);
            --pk-tabs-trigger-color: inherit;
            --pk-tabs-trigger-font-size: 13px;
            --pk-tabs-trigger-font-weight: 400;
            --pk-tabs-trigger-text-align: center;
            --pk-tabs-trigger-text-transform: none;
            --pk-tabs-trigger-border-top: 0 solid transparent;
            --pk-tabs-trigger-hover-bg: rgb(255 255 255 / 0.7);
            --pk-tabs-trigger-hover-color: var(--pk-color-gray-700);
            --pk-tabs-trigger-selected-hover-bg: var(--pk-tabs-trigger-selected-bg, var(--pk-color-white));
            --pk-tabs-trigger-selected-hover-color: var(--pk-tabs-trigger-selected-color, var(--pk-color-gray-800));
            --pk-tabs-trigger-selected-bg: var(--pk-color-white);
            --pk-tabs-trigger-selected-color: var(--pk-color-gray-800);
            --pk-tabs-trigger-selected-shadow: 0 1px 2px rgba(31, 41, 51, 0.12);
            --pk-tabs-trigger-selected-radius: var(--pk-radius-sm);
            --pk-tabs-trigger-selected-border-top: 0 solid transparent;
            --pk-tabs-trigger-underline-height: 0;
            --pk-tabs-trigger-underline-inset: 15px;
            --pk-tabs-trigger-label-flex: 0 1 auto;
            --pk-tabs-trigger-icon-size: 1.125rem;
            --pk-tabs-trigger-icon-color: inherit;
            --pk-tabs-trigger-status-margin: 0;
            --pk-tabs-trigger-status-color: inherit;

            /* Group headings (pk-tab-heading) */
            --pk-tabs-heading-padding: 0.75rem 0.5rem 0.375rem;
            --pk-tabs-heading-color: var(--pk-color-gray-400);
            --pk-tabs-heading-font-size: 11px;
            --pk-tabs-heading-font-weight: 600;
            --pk-tabs-heading-letter-spacing: 0.04em;
            --pk-tabs-heading-text-transform: uppercase;

            /* Panel (inherited by pk-tab-panel) */
            --pk-tabs-panel-flex: none;
            --pk-tabs-panel-min-height: 0;
            --pk-tabs-panel-padding: 0;
            --pk-tabs-panel-bg: transparent;
            --pk-tabs-panel-radius: 0;
            --pk-tabs-panel-font-size: var(--pk-font-size-base);
        }

        :host([variant='pane']),
        :host([variant='modal']),
        :host([variant='sidebar']) {
            height: 100%;
            min-height: 0;
            --pk-tabs-root-height: 100%;
        }

        .tabs {
            display: flex;
            flex-direction: column;
            gap: var(--pk-tabs-root-gap);
            height: var(--pk-tabs-root-height);
            min-height: 0;
            /* Radius/shadow/overflow live on :host — keep the layout shell fill-only. */
        }

        .tabs[data-placement='bottom'] {
            flex-direction: column-reverse;
        }

        .tabs[data-placement='start'],
        .tabs[data-placement='end'] {
            flex-direction: row;
            align-items: flex-start;
            gap: 1rem;
        }

        .tabs[data-placement='end'] {
            flex-direction: row-reverse;
        }

        .tabs[data-placement='start'] .list,
        .tabs[data-placement='end'] .list {
            flex-direction: column;
            align-self: stretch;
        }

        .list {
            display: var(--pk-tabs-list-display);
            width: var(--pk-tabs-list-width);
            max-width: 100%;
            align-self: var(--pk-tabs-list-align-self);
            align-items: var(--pk-tabs-list-align-items);
            justify-content: flex-start;
            /* Tab strip must not shrink when panels flex-fill the column — otherwise
             * overflow-y clips trigger padding and the modal active underline. */
            flex-shrink: 0;
            position: relative;
            z-index: var(--pk-tabs-list-z-index, auto);
            isolation: isolate;
            padding: var(--pk-tabs-list-padding);
            border: var(--pk-tabs-list-border-width) solid var(--pk-tabs-list-border-color);
            border-bottom: var(--pk-tabs-list-border-bottom, var(--pk-tabs-list-border-width) solid var(--pk-tabs-list-border-color));
            border-radius: var(--pk-tabs-list-radius);
            background: var(--pk-tabs-list-bg);
            box-shadow: var(--pk-tabs-list-shadow), var(--pk-tabs-list-inset-shadow);
            color: var(--pk-tabs-list-color);
            overflow-x: var(--pk-tabs-list-overflow-x, auto);
            overflow-y: var(--pk-tabs-list-overflow-y, visible);
        }

        /* Pane — matches plugin-kit-react PaneTabs */
        :host([variant='pane']) {
            --pk-tabs-root-gap: 0;
            --pk-tabs-root-radius: var(--pk-radius-lg);
            --pk-tabs-root-shadow:
                0 0 0 1px var(--pk-color-gray-200),
                0 2px 12px rgb(205 216 228 / 50%);
            --pk-tabs-root-overflow: visible;

            --pk-tabs-list-display: flex;
            --pk-tabs-list-width: auto;
            --pk-tabs-list-align-self: stretch;
            --pk-tabs-list-align-items: flex-end;
            --pk-tabs-list-padding: 0;
            --pk-tabs-list-border-width: 0;
            --pk-tabs-list-radius: var(--pk-radius-lg) var(--pk-radius-lg) 0 0;
            --pk-tabs-list-bg: var(--pk-color-gray-50);
            /* Must not use keyword none — box-shadow: none, inset … is invalid and drops the hairline. */
            --pk-tabs-list-shadow: inset 0 -1px 0 0 rgb(154 165 177 / 25%);
            --pk-tabs-list-inset-shadow: 0 0 #0000;
            --pk-tabs-list-overflow-x: auto;
            --pk-tabs-list-overflow-y: visible;

            --pk-tabs-trigger-display: flex;
            --pk-tabs-trigger-justify: flex-start;
            --pk-tabs-trigger-min-height: 45px;
            --pk-tabs-trigger-padding-block: 0;
            --pk-tabs-trigger-padding-inline: 24px;
            --pk-tabs-trigger-radius: 0;
            --pk-tabs-trigger-border-top: 0 solid transparent;
            --pk-tabs-trigger-color: var(--pk-color-gray-550);
            --pk-tabs-trigger-font-size: var(--pk-font-size-base);
            --pk-tabs-trigger-font-weight: 400;
            --pk-tabs-trigger-hover-bg: var(--pk-color-slate-100);
            --pk-tabs-trigger-hover-color: var(--pk-color-gray-550);
            --pk-tabs-trigger-selected-bg: var(--pk-color-white);
            --pk-tabs-trigger-selected-color: var(--pk-color-gray-700);
            --pk-tabs-trigger-selected-border-top: 0 solid transparent;
            /* Match Craft .pane-tabs [role=tab].sel — inset top accent + elevation. */
            --pk-tabs-trigger-selected-shadow:
                inset 0 2px 0 var(--pk-color-gray-500),
                0 0 0 1px rgb(51 64 77 / 10%),
                0 2px 12px rgb(205 216 228 / 90%);
            --pk-tabs-trigger-selected-radius: 2px 2px 0 0;

            /* 0% basis — panel fills leftover height and scrolls; auto basis grew with
             * content and clipped under dialog overflow:hidden (Edit Buttons Appearance). */
            --pk-tabs-panel-flex: 1 1 0%;
            --pk-tabs-panel-min-height: 0;
            /*
             * No built-in panel inset — matches v1 PaneTabsContent (padding came from
             * the consumer: ReportTabPanel / FormBuilderTabContent / DefaultsPanel p-6).
             * A non-zero value here double-pads those surfaces.
             */
            --pk-tabs-panel-padding: 0;
            --pk-tabs-panel-bg: var(--pk-color-white);
            --pk-tabs-panel-radius: 0 0 var(--pk-radius-lg) var(--pk-radius-lg);
            --pk-tabs-panel-font-size: var(--pk-font-size-sm, 14px);
        }

        /* Craft bumps the first tab start corner to --radius-lg so the inset
         * accent follows the pane radius instead of reading as clipped at 2px.
         */
        :host([variant='pane']) ::slotted(pk-tab:first-child) {
            --pk-tabs-trigger-selected-radius: var(--pk-radius-lg) 2px 0 0;
        }

        /* Modal — matches plugin-kit-react ModalTabs */
        :host([variant='modal']) {
            --pk-tabs-root-gap: 0;
            --pk-tabs-root-height: 100%;
            /* Dialog already rounds the panel — host radius + overflow clips the
             * first tab’s focus ring into a one-corner “rounded border”. */
            --pk-tabs-root-radius: 0;
            /* Clip to the height chain so panels scroll inside, not through the footer. */
            --pk-tabs-root-overflow: hidden;

            --pk-tabs-list-display: flex;
            --pk-tabs-list-width: 100%;
            --pk-tabs-list-align-self: stretch;
            --pk-tabs-list-align-items: stretch;
            --pk-tabs-list-padding: 0;
            --pk-tabs-list-border-width: 0;
            --pk-tabs-list-border-bottom: 1px solid var(--pk-color-gray-100);
            --pk-tabs-list-radius: 0;
            --pk-tabs-list-bg: var(--pk-color-white);
            --pk-tabs-list-shadow: 0 1px 5px #cdd8e440;
            /* Transparent no-op — keyword none in a multi-shadow list invalidates the whole property. */
            --pk-tabs-list-inset-shadow: 0 0 #0000;
            --pk-tabs-list-color: inherit;
            --pk-tabs-list-overflow-x: auto;
            /* auto (not hidden): overflow-x:auto + overflow-y:hidden clips ~1 device
             * pixel of the bottom active underline, so the 2px sky bar reads as 1px. */
            --pk-tabs-list-overflow-y: auto;
            /* v1 ModalTabsList z-11 — keep the strip above scrolling panel content
             * (editable-table action columns, etc.) when body/panel scrolls. */
            --pk-tabs-list-z-index: 11;

            --pk-tabs-trigger-display: inline-flex;
            --pk-tabs-trigger-justify: center;
            --pk-tabs-trigger-min-height: auto;
            --pk-tabs-trigger-padding-block: 15px;
            --pk-tabs-trigger-padding-inline: 15px;
            --pk-tabs-trigger-radius: 0;
            --pk-tabs-trigger-color: #64788d;
            --pk-tabs-trigger-font-size: 12px;
            --pk-tabs-trigger-font-weight: 500;
            --pk-tabs-trigger-text-transform: uppercase;
            --pk-tabs-trigger-hover-bg: transparent;
            --pk-tabs-trigger-hover-color: var(--pk-color-sky-600);
            --pk-tabs-trigger-selected-bg: transparent;
            --pk-tabs-trigger-selected-color: #64788d;
            --pk-tabs-trigger-selected-hover-bg: transparent;
            --pk-tabs-trigger-selected-hover-color: var(--pk-color-sky-600);
            /* Active (mouse) = 15px-inset underline. Active + :focus-visible = screen3 box. */
            --pk-tabs-trigger-selected-shadow: none;
            --pk-tabs-trigger-selected-radius: 0;
            --pk-tabs-trigger-underline-height: 2px;
            --pk-tabs-trigger-underline-inset: 15px;
            --pk-tabs-trigger-focus-selected-shadow: inset 0 0 0 2px var(--pk-color-sky-600);
            --pk-tabs-trigger-focus-selected-underline-height: 0;

            /* 0% basis — panel fills leftover height and scrolls; auto basis grew with
             * content and clipped under dialog overflow:hidden (Edit Buttons Appearance). */
            --pk-tabs-panel-flex: 1 1 0%;
            --pk-tabs-panel-min-height: 0;
            --pk-tabs-panel-padding: 1rem;
            --pk-tabs-panel-bg: transparent;
            --pk-tabs-panel-radius: 0;
            --pk-tabs-panel-font-size: var(--pk-font-size-sm, 14px);
        }

        /* Sidebar — vertical nav list with optional icons, status, and headings */
        :host([variant='sidebar']) {
            --pk-tabs-root-gap: 0;
            --pk-tabs-root-radius: 0;
            --pk-tabs-root-shadow: none;
            --pk-tabs-root-overflow: visible;

            --pk-tabs-list-display: flex;
            --pk-tabs-list-width: var(--pk-tabs-sidebar-width, 14rem);
            --pk-tabs-list-align-self: stretch;
            --pk-tabs-list-align-items: stretch;
            --pk-tabs-list-padding: 0.5rem;
            --pk-tabs-list-border-width: 0;
            --pk-tabs-list-border-bottom: 0 solid transparent;
            --pk-tabs-list-radius: 0;
            --pk-tabs-list-bg: var(--pk-color-gray-100);
            --pk-tabs-list-shadow: 0 0 #0000;
            --pk-tabs-list-inset-shadow: 0 0 #0000;
            --pk-tabs-list-color: var(--pk-color-gray-600);
            --pk-tabs-list-overflow-x: hidden;
            --pk-tabs-list-overflow-y: auto;

            --pk-tabs-trigger-display: flex;
            --pk-tabs-trigger-justify: flex-start;
            --pk-tabs-trigger-width: 100%;
            /* Match Craft/Formie integrations nav: padding 7px 10px, 16px icons, content-sized height. */
            --pk-tabs-trigger-gap: 10px;
            --pk-tabs-trigger-min-height: 0;
            --pk-tabs-trigger-padding-block: 7px;
            --pk-tabs-trigger-padding-inline: 10px;
            --pk-tabs-trigger-radius: var(--pk-radius-md);
            --pk-tabs-trigger-color: var(--pk-color-gray-700);
            --pk-tabs-trigger-font-size: 13px;
            --pk-tabs-trigger-font-weight: 400;
            --pk-tabs-trigger-line-height: 1.2;
            --pk-tabs-trigger-text-align: start;
            --pk-tabs-trigger-text-transform: none;
            --pk-tabs-trigger-border-top: 0 solid transparent;
            --pk-tabs-trigger-hover-bg: color-mix(in oklab, var(--pk-color-gray-200) 70%, transparent);
            --pk-tabs-trigger-hover-color: var(--pk-color-gray-800);
            --pk-tabs-trigger-selected-bg: var(--pk-color-gray-500);
            --pk-tabs-trigger-selected-color: var(--pk-color-white);
            --pk-tabs-trigger-selected-hover-bg: var(--pk-color-gray-500);
            --pk-tabs-trigger-selected-hover-color: var(--pk-color-white);
            --pk-tabs-trigger-selected-shadow: none;
            --pk-tabs-trigger-selected-radius: var(--pk-radius-md);
            --pk-tabs-trigger-selected-border-top: 0 solid transparent;
            --pk-tabs-trigger-underline-height: 0;
            --pk-tabs-trigger-label-flex: 1 1 auto;
            --pk-tabs-trigger-icon-size: 16px;
            --pk-tabs-trigger-status-margin: auto;
            --pk-tabs-trigger-status-color: var(--pk-color-gray-400);

            --pk-tabs-heading-padding: 14px 10px 5px;
            --pk-tabs-heading-color: var(--pk-color-gray-400);
            --pk-tabs-heading-font-size: 11px;
            --pk-tabs-heading-font-weight: 600;
            --pk-tabs-heading-letter-spacing: 0.04em;
            --pk-tabs-heading-text-transform: uppercase;

            /* 0% basis — panel fills leftover height and scrolls; auto basis grew with
             * content and clipped under dialog overflow:hidden (Edit Buttons Appearance). */
            --pk-tabs-panel-flex: 1 1 0%;
            --pk-tabs-panel-min-height: 0;
            --pk-tabs-panel-padding: 1.25rem;
            --pk-tabs-panel-bg: var(--pk-color-white);
            --pk-tabs-panel-radius: 0;
            --pk-tabs-panel-font-size: var(--pk-font-size-base);
        }

        :host([variant='sidebar']) .tabs {
            flex-direction: row;
            align-items: stretch;
            gap: 0;
        }

        :host([variant='sidebar']) .tabs[data-placement='end'] {
            flex-direction: row-reverse;
        }

        :host([variant='sidebar']) .list {
            flex-direction: column;
            gap: 0.125rem;
            flex: none;
        }

        :host([variant='sidebar']) ::slotted(pk-tab) {
            display: block;
            width: 100%;
        }

        :host([variant='sidebar']) ::slotted(pk-tab-heading) {
            display: block;
            width: 100%;
        }

        /* First group heading sits closer to the list top edge */
        :host([variant='sidebar']) ::slotted(pk-tab-heading:first-child) {
            --pk-tabs-heading-padding: 10px 10px 5px;
        }

        /* Hollow inactive dots read better on the selected dark pill */
        :host([variant='sidebar']) ::slotted(pk-tab[selected]) {
            --pk-tabs-trigger-status-color: var(--pk-color-gray-300);
        }
    }
`,Il=class extends _{constructor(...e){super(...e),this.value=``,this.variant=`default`,this.orientation=`horizontal`,this.placement=`top`,this.activation=`manual`,this.disabled=!1,this.ariaLabel=null,this.baseId=E(`pk-tabs`),this.tabs=[],this.panels=[],this.focusedValue=``,this.syncTabs=()=>{let e=this.shadowRoot?.querySelector(`slot[name="nav"]`);e&&(this.tabs=e.assignedElements({flatten:!0}).filter(e=>e.tagName===`PK-TAB`),this.ensureDefaultValue(),this.applySelection())},this.syncPanels=()=>{let e=this.shadowRoot?.querySelector(`slot:not([name])`);e&&(this.panels=e.assignedElements({flatten:!0}).filter(e=>e.tagName===`PK-TAB-PANEL`),this.applySelection())},this.handleTabSelect=e=>{if(!this.isOwnTabEvent(e)||this.disabled)return;e.stopPropagation();let{value:t}=e.detail;if(t===this.value&&this.activation===`manual`){this.focusedValue=t,this.applySelection();return}t!==this.value&&this.selectTab(t)},this.handleTabKeyDown=e=>{if(!this.isOwnTabEvent(e))return;e.stopPropagation();let t=e.detail.event,n=this.getEnabledTabs();if(n.length===0)return;let r=n.findIndex(t=>t.value===e.detail.value);if(r<0)return;let i=r,a=this.getEffectiveOrientation()===`horizontal`;switch(t.key){case`ArrowDown`:if(a)return;t.preventDefault(),i=r>=n.length-1?0:r+1;break;case`ArrowUp`:if(a)return;t.preventDefault(),i=r<=0?n.length-1:r-1;break;case`ArrowRight`:if(!a)return;t.preventDefault(),i=r>=n.length-1?0:r+1;break;case`ArrowLeft`:if(!a)return;t.preventDefault(),i=r<=0?n.length-1:r-1;break;case`Home`:t.preventDefault(),i=0;break;case`End`:t.preventDefault(),i=n.length-1;break;default:return}let o=n[i];o&&(this.activation===`auto`?o.value===this.value?o.focusControl():this.selectTab(o.value):(this.focusedValue=o.value,this.applySelection(),o.focusControl()))}}static{this.styles=Fl}connectedCallback(){super.connectedCallback(),this.addEventListener(`pk-tab-select`,this.handleTabSelect),this.addEventListener(`pk-tab-keydown`,this.handleTabKeyDown)}disconnectedCallback(){this.removeEventListener(`pk-tab-select`,this.handleTabSelect),this.removeEventListener(`pk-tab-keydown`,this.handleTabKeyDown),super.disconnectedCallback()}updated(e){(e.has(`value`)||e.has(`disabled`)||e.has(`activation`))&&(e.has(`value`)&&(this.focusedValue=this.value),this.applySelection())}ensureDefaultValue(){if(this.value||this.tabs.length===0)return;let e=this.tabs.find(e=>!e.disabled&&!this.disabled);e&&(this.value=e.value,this.focusedValue=this.value)}getEnabledTabs(){return this.tabs.filter(e=>!e.disabled&&!this.disabled)}getEffectiveOrientation(){return this.variant===`sidebar`?`vertical`:this.orientation}getEffectivePlacement(){return this.variant===`sidebar`&&(this.placement===`top`||this.placement===`bottom`)?`start`:this.placement}applySelection(){let e=this.getAttribute(`data-current-value`)??``,t=this.activation===`manual`?this.focusedValue:this.value;for(let e of this.tabs){let n=e.value===this.value,r=`${this.baseId}-tab-${e.value}`,i=`${this.baseId}-panel-${e.value}`;e.selected=n,e.disabled=this.disabled||e.hasAttribute(`disabled`),e.focusIndex=e.value===t?0:-1,e.panelId=i,e.id=r}for(let t of this.panels){let n=t.value===this.value,r=`${this.baseId}-tab-${t.value}`,i=`${this.baseId}-panel-${t.value}`;t.hidden!==!n&&(n?this.dispatchEvent(new CustomEvent(`pk-tab-show`,{detail:{value:t.value},bubbles:!0,composed:!0})):e===t.value&&this.dispatchEvent(new CustomEvent(`pk-tab-hide`,{detail:{value:t.value},bubbles:!0,composed:!0}))),t.hidden=!n,t.tabId=r,t.id=i}this.setAttribute(`data-current-value`,this.value)}isOwnTabEvent(e){let t=e.target;return t instanceof HTMLElement&&t.tagName===`PK-TAB`&&this.tabs.includes(t)}selectTab(e){this.value=e,this.focusedValue=e,this.applySelection(),this.dispatchEvent(new CustomEvent(`pk-change`,{detail:{value:this.value},bubbles:!0,composed:!0}))}render(){let e=this.getEffectiveOrientation();return v`
            <div part="base" class="tabs pk-tabs" data-placement=${this.getEffectivePlacement()}>
                <div
                    part="list"
                    class="list pk-tabs__list"
                    role="tablist"
                    aria-orientation=${e}
                    aria-label=${this.ariaLabel??T}
                    @slotchange=${this.syncTabs}
                >
                    <slot name="nav"></slot>
                </div>
                <slot @slotchange=${this.syncPanels}></slot>
            </div>
        `}};s([c()],Il.prototype,`value`,void 0),s([c({reflect:!0})],Il.prototype,`variant`,void 0),s([c({reflect:!0})],Il.prototype,`orientation`,void 0),s([c({reflect:!0})],Il.prototype,`placement`,void 0),s([c({reflect:!0})],Il.prototype,`activation`,void 0),s([c({type:Boolean,reflect:!0})],Il.prototype,`disabled`,void 0),s([c({attribute:`aria-label`})],Il.prototype,`ariaLabel`,void 0),s([g()],Il.prototype,`tabs`,void 0),s([g()],Il.prototype,`panels`,void 0),s([g()],Il.prototype,`focusedValue`,void 0),Il=s([h(`pk-tabs`)],Il);var Ll=u({tagName:`pk-tabs`,elementClass:Il,react:A.default,events:{onPkChange:`pk-change`,onPkTabShow:`pk-tab-show`,onPkTabHide:`pk-tab-hide`}}),Rl=u({tagName:`pk-tab`,elementClass:kl,react:A.default,events:{onPkTabSelect:`pk-tab-select`,onPkTabKeydown:`pk-tab-keydown`}});u({tagName:`pk-tab-heading`,elementClass:jl,react:A.default});var K=u({tagName:`pk-tab-panel`,elementClass:Pl,react:A.default});function q({onPkChange:e,onPkTabShow:t,onPkTabHide:n,...r}){return(0,j.jsx)(Ll,{...r,...e?{onPkChange:t=>{Gc(t)&&e(t)}}:{},...t?{onPkTabShow:e=>{Gc(e)&&t(e)}}:{},...n?{onPkTabHide:e=>{Gc(e)&&n(e)}}:{}})}var J=Rl,Y=K,X=(0,A.createContext)({}),zl=()=>(0,A.useContext)(X),Bl=e=>typeof e==`object`&&!!e&&!Array.isArray(e),Vl=e=>{if(!e?.children)return[];let t=[],n=e=>{Array.isArray(e)&&e.forEach(e=>{if(!Bl(e))return;let r=e;if(r.$cmp===`ModalTabsTrigger`){let e=typeof r.props?.value==`string`?r.props.value:typeof r.value==`string`?r.value:``;e&&t.push({value:e,if:typeof r.if==`string`?r.if:void 0});return}r.children&&n(r.children)})};return n(e.children),t},Hl=Object.assign(({children:e,schemaNode:t,schema:n,_id:r,_data:i,className:a,value:o,defaultValue:s=``,...c})=>{let l=(0,A.useContext)(yl),u=t??n,[d,f]=(0,A.useState)({}),[p,m]=(0,A.useState)(()=>o??s??``),h=o!=null,g=h?String(o):p,_=(0,A.useMemo)(()=>Vl(u),[u]),v=(0,A.useCallback)(()=>{let e={};if(!u?.children||!l?.getErrorMapFields)return e;let t=l.getErrorMapFields?.()||{};return Object.values(u.children).forEach(n=>{if(typeof n!=`object`||!n||Array.isArray(n))return;let r=n,i=typeof r.props?.value==`string`?r.props.value:``;r.$cmp===`ModalTabsContent`&&i&&(e[i]=Cl(t,r.children||[]))}),e},[l,u]),y=(0,A.useCallback)(e=>{for(let t of _)if(!t.if||Ae(t.if,e))return t.value;return _[0]?.value??``},[_]),b=(0,A.useCallback)((e,t)=>{let n=_.find(t=>t.value===e);return n?!n.if||Ae(n.if,t):!1},[_]);return(0,A.useEffect)(()=>{if(!l?.store?.subscribe){f(v());return}let e=()=>{if(f(v()),h)return;let{values:e}=l.store.state,t=e&&typeof e==`object`?e:{};if(!g||b(g,t))return;let n=y(t);n&&n!==g&&m(n)};return e(),l.store.subscribe(e)},[l,y,v,h,b,g]),(0,j.jsx)(X.Provider,{value:d,children:(0,j.jsx)(q,{variant:`modal`,value:g,className:gr(`h-full min-h-0`,a),...c,onPkChange:e=>{e.stopPropagation();let t=e.detail?.value;typeof t!=`string`||!t||h||m(t)},children:e})})},{usesSchemaNode:!0});function Ul({children:e}){return(0,j.jsx)(A.Fragment,{children:e})}function Wl({children:e,value:t,className:n,...r}){let i=zl(),a=!!(t&&i[t]);return(0,j.jsx)(J,{value:t,"data-has-errors":a?`true`:void 0,className:n,...r,slot:`nav`,children:(0,j.jsxs)(`span`,{className:`inline-flex items-center gap-1`,children:[e,a?(0,j.jsx)(vr,{icon:`triangle-exclamation`,className:`block size-3`}):null]})})}function Gl({children:e,className:t,...n}){return(0,j.jsx)(Y,{className:t,...n,children:(0,j.jsx)(`div`,{className:`grid grid-cols-1 gap-4`,children:e})})}var Kl=y`
    @layer pk-component {
        :host {
            display: block;
            flex-shrink: 0;
            margin: 0;
            padding: 0;
            border: 0;
            box-sizing: border-box;
            background: transparent;
        }

        .line {
            display: block;
            margin: 0;
            padding: 0;
            border: 0;
            box-sizing: border-box;
            background: var(--pk-color-slate-200);
        }

        :host([orientation='horizontal']) .line {
            width: 100%;
            height: 1px;
            margin-block: 0.25rem;
        }

        :host([orientation='vertical']) {
            display: inline-block;
            align-self: stretch;
            width: auto;
            height: 100%;
        }

        :host([orientation='vertical']) .line {
            width: 1px;
            height: 100%;
            margin-inline: 0.25rem;
        }

        /* CE :host { display } otherwise wins over the UA [hidden] rule. */
        :host([hidden]) {
            display: none !important;
        }
    }
`,ql=class extends _{constructor(...e){super(...e),this.orientation=`horizontal`}static{this.styles=Kl}connectedCallback(){super.connectedCallback(),this.setAttribute(`role`,`separator`),this.syncAriaOrientation()}updated(e){super.updated(e),e.has(`orientation`)&&this.syncAriaOrientation()}syncAriaOrientation(){this.setAttribute(`aria-orientation`,this.orientation)}render(){return v`<div class="line" part="base"></div>`}};s([c({reflect:!0})],ql.prototype,`orientation`,void 0),ql=s([h(`pk-separator`)],ql);var Jl=u({tagName:`pk-separator`,elementClass:ql,react:A.default}),Yl={},Xl={},Zl={FieldWrap:El,ModalTabs:Hl,ModalTabsList:Ul,ModalTabsTrigger:Wl,ModalTabsContent:Gl,Separator:Jl},Ql=(e,t)=>{Xl[e]=t},$l=e=>Yl[e]??Xl[e],eu=()=>Zl,tu={text:async()=>(await _t(async()=>{let{TextField:e}=await import(`./TextField-D1bYIEeD.js`);return{TextField:e}},__vite__mapDeps([0,1,2,3,4,5,6,7,8,9,10,11]),import.meta.url)).TextField,textarea:async()=>(await _t(async()=>{let{TextareaField:e}=await import(`./TextareaField-C1Fr1bu6.js`);return{TextareaField:e}},__vite__mapDeps([12,2,1,6,7,8,9,3,11]),import.meta.url)).TextareaField,number:async()=>(await _t(async()=>{let{NumberField:e}=await import(`./NumberField-DHA092Fi.js`);return{NumberField:e}},__vite__mapDeps([13,1,2,3,4,5,6,7,8,9,10,11]),import.meta.url)).NumberField,select:async()=>(await _t(async()=>{let{SelectField:e}=await import(`./SelectField-j6mLG-tP.js`);return{SelectField:e}},__vite__mapDeps([14,2,1,15,16,17,18,6,8,10,3]),import.meta.url)).SelectField,lightswitch:async()=>(await _t(async()=>{let{LightswitchField:e}=await import(`./LightswitchField-B0zNVH7z.js`);return{LightswitchField:e}},__vite__mapDeps([19,1,2,20,21,6,7,8,9,3]),import.meta.url)).LightswitchField,color:async()=>(await _t(async()=>{let{ColorField:e}=await import(`./ColorField-DJ1OZWXe.js`);return{ColorField:e}},__vite__mapDeps([22,2,1,6,7,8,3]),import.meta.url)).ColorField,radioGroup:async()=>(await _t(async()=>{let{RadioGroupField:e}=await import(`./RadioGroupField-Ip5mYdA8.js`);return{RadioGroupField:e}},__vite__mapDeps([23,2,1,15,21,6,7,9,3]),import.meta.url)).RadioGroupField,checkboxSelect:async()=>(await _t(async()=>{let{CheckboxSelectField:e}=await import(`./CheckboxSelectField-B7fYX5Ye.js`);return{CheckboxSelectField:e}},__vite__mapDeps([24,2,1,3]),import.meta.url)).CheckboxSelectField,combobox:async()=>(await _t(async()=>{let{ComboboxField:e}=await import(`./ComboboxField-DmgdN12v.js`);return{ComboboxField:e}},__vite__mapDeps([25,2,1,18,6,8,10,17,3]),import.meta.url)).ComboboxField,group:async()=>(await _t(async()=>{let{GroupField:e}=await import(`./GroupField-DI5yNLXf.js`);return{GroupField:e}},__vite__mapDeps([26,2,1]),import.meta.url)).GroupField,date:async()=>(await _t(async()=>{let{DateTimeField:e}=await import(`./DateTimeField-DgUP9GrO.js`);return{DateTimeField:e}},__vite__mapDeps([27,2,1,18,6,8,10,7,9,3]),import.meta.url)).DateTimeField,codeEditor:async()=>(await _t(async()=>{let{CodeEditorField:e}=await import(`./CodeEditorField-CNF-AR16.js`);return{CodeEditorField:e}},__vite__mapDeps([28,2,1,6,8,3]),import.meta.url)).CodeEditorField},nu=e=>Object.prototype.hasOwnProperty.call(tu,e),ru=async e=>{let t=tu[e];return t?t():null},iu=(0,A.memo)(({fieldType:e,schema:t,field:n,form:r,children:i})=>{let a=$l(e),[o,s]=(0,A.useState)(()=>a??null),[c,l]=(0,A.useState)(!1);return(0,A.useEffect)(()=>{if(a){s(()=>a),l(!1);return}if(!nu(e)){console.warn(`Unknown form field type: ${e}`),s(null),l(!0);return}let t=!1;return l(!1),ru(e).then(n=>{t||!n||(Ql(e,n),s(()=>n))}).catch(n=>{t||(console.error(`Failed to load form field "${e}":`,n),s(null),l(!0))}),()=>{t=!0}},[e,a]),c||!o?null:(0,A.createElement)(o,{schema:t,field:n,form:r},i)});iu.displayName=`SchemaFormFieldNode`;var au=e=>!!e&&typeof e==`object`&&!Array.isArray(e),ou=e=>au(e)&&au(e.target),su=(0,A.createContext)(null),cu=(0,A.createContext)(null),lu=({children:e,...t})=>(0,j.jsx)(cu.Provider,{value:t,children:e}),uu=()=>{let e=(0,A.useContext)(su);if(!e)throw Error(`useSchemaContext must be used within a SchemaProvider`);return e},du=(0,A.memo)(({field:e,children:t})=>{let{form:n}=uu(),r=(0,A.useCallback)(t=>{let r=typeof e._scopePath==`string`?e._scopePath:``,i=r?n?.getFieldValue?.(r):null,a=au(i)?i:{},o=n?.getConditionContext?.(t,e),s=au(o)?o:{},c=au(e._data)?e._data:{};return{...t,...a,...c,...s}},[e,n]),i=(0,A.useCallback)(t=>!e.if||Ae(e.if,r(t)),[r,e.if]),[a,o]=(0,A.useState)(()=>{let t=n?.store?.state?.values;return!t||typeof t!=`object`?!e.if:i(t)});return(0,A.useEffect)(()=>{if(!e.if){o(!0);return}let t=n?.store?.state?.values;if(t&&typeof t==`object`&&o(i(t)),n?.store?.subscribe)return n.store.subscribe(()=>{let{values:e}=n.store.state;o(i(e))})},[e.if,n,i]),a?t:e.hideOnIf?(0,j.jsx)(`div`,{style:{display:`none`},children:t}):null});du.displayName=`SchemaItem`;var fu=(e,t)=>{let{$field:n,...r}=e.$field?{$field:e.$field,...e}:{$field:e.type,...e};return(0,j.jsx)(iu,{fieldType:String(n??``),schema:e,field:{...r},form:t,children:r.children?(0,j.jsx)(hu,{schema:r.children}):null})},pu=e=>{let{$el:t,children:n,attrs:r={}}=e,i=vl(au(r)?r:{});return typeof t!=`string`||!t?null:t===`hr`?(0,A.createElement)(Jl,{...i}):(0,A.createElement)(t,{...i},n?(0,j.jsx)(hu,{schema:n}):null)},mu=e=>{let{$cmp:t,props:n={},children:r,...i}=e,a=eu()[t];if(!a)return console.warn(`Unknown form component: ${t}`),null;let{_id:o,_data:s,_scopePath:c,schema:l,schemaChildPrefix:u,$el:d,$field:f,if:p,hideOnIf:m,attrs:h,type:g,..._}=i,v={...au(n)?n:{},..._};return a.usesSchemaNode?v.schemaNode=e:`schemaNode`in v&&delete v.schemaNode,(0,A.createElement)(a,v,r?(0,j.jsx)(hu,{schema:r}):null)},hu=(0,A.memo)(({schema:e})=>{let{form:t}=uu();return typeof e==`string`?e:Array.isArray(e)?e.map((e,t)=>{if(typeof e==`string`)return e;if(!au(e))return null;let n=typeof e._id==`string`?e._id:`schema-item-${t}`;return(0,j.jsx)(lu,{children:(0,j.jsx)(du,{field:e,children:(0,j.jsx)(hu,{schema:e})})},n)}):e.$field||e.$cmp===`Field`?fu(e,t):e.$el?pu(e):e.$cmp?mu(e):(console.warn(`Unknown schema item:`,e),null)});hu.displayName=`SchemaRenderer`;var gu=({children:e,form:t})=>{let n=(0,A.useMemo)(()=>({form:t,schema:t.schema}),[t]);return(0,j.jsx)(su.Provider,{value:n,children:e})},_u=e=>{let t={};if(!e)return t;let n=(e,n)=>{if(!e)return;let r=n.filter(e=>e!=null&&e!==``).map(e=>String(e));r.length&&(t[e]||(t[e]=[]),t[e].push(...r))},r=(e,t)=>{if(Array.isArray(e)){if(e.length===0)return;if(e.every(e=>typeof e==`string`)){n(t,e);return}if(!t){e.forEach(e=>{r(e,t)});return}e.forEach((e,n)=>{r(e,`${t}.${n}`)});return}!e||typeof e!=`object`||Object.entries(e).forEach(([e,n])=>{r(n,t?`${t}.${e}`:e)})};return Array.isArray(e)?(r(e,``),t):e&&typeof e==`object`&&`errors`in e?(r(e.errors,``),t):(r(e,``),t)},vu=(e,t,n)=>(0,A.useSyncExternalStore)(e?e.subscribe.bind(e):(()=>()=>{}),()=>e?t(e.state):n,()=>e?t(e.state):n),yu=({name:e,children:t})=>{let n=(0,A.useContext)(yl),r=n?.store??null,i=vu(r,()=>n?.getFieldValue(e),void 0),a=vu(r,t=>t?.errors[e]||[],[]);return n?t({state:{value:i},handleChange:t=>{if(ou(t)){let{type:r,checked:i,value:a}=t.target;n.setFieldValue(e,r===`checkbox`?i:a);return}n.setFieldValue(e,t)},handleBlur:()=>{n.store.setTouched(e,!0)},errors:a}):null},bu=({schemaIndex:e=null,defaultValues:t={},errors:n,onChange:r,getConditionContext:i,parentForm:a,parentPath:o})=>{let s=(0,A.useMemo)(()=>{if(!e)throw Error(`SchemaFormEngine requires a compiled schemaIndex.`);let t=ke(e.schema);return{...e,schema:t}},[e]),c=(0,A.useRef)(null);c.current||=new Fe(t);let l=c.current,u=(0,A.useMemo)(()=>Ne(s,{conditionDataResolver:i}),[s,i]),d=(0,A.useMemo)(()=>{let e=new Map;return s.fieldEntries.forEach(t=>{if(t.path){let n=String(t.field?.label||t.field?.name||t.path);e.set(t.path,n)}}),e},[s]),f=(0,A.useRef)(null),p=(0,A.useRef)(null),m=(0,A.useRef)(null),h=(0,A.useRef)(null),g=(0,A.useRef)(null),_=(0,A.useRef)(new Map);(0,A.useEffect)(()=>{if(!r){h.current=null;return}h.current=e=>{g.current&&r(e,g.current)}},[r]),(0,A.useEffect)(()=>l.subscribe(()=>{h.current&&h.current(l.state.values)}),[l]),(0,A.useEffect)(()=>{let e=_u(n);Object.keys(e).length?l.setErrors(e):l.clearErrors()},[n,l]);let v=(e,t)=>{_.current.set(e,t)},y=e=>{_.current.delete(e)},b=()=>{let e={};return _.current.forEach((t,n)=>{let r=t.validate();r?.fields&&Object.entries(r.fields).forEach(([t,r])=>{let i=t?`${n}.${t}`:n;e[i]=r})}),e},x={schema:s.schema,store:l,index:s,Field:yu,getFieldValue:e=>l.getValue(e),setFieldValue:(e,t)=>l.setValue(e,t),getErrorMapFields:()=>l.state.errors||{},SchemaRenderer:hu,handleSubmit:async()=>{let e=u.validate(l.state.values)?.fields||{},t=b(),n={...e,...t};if(Object.keys(n).length>0){l.setErrors(n),p.current&&p.current(n);return}l.clearErrors(),f.current&&await f.current(l.state.values),a&&o&&a.setFieldValue(o,l.state.values),m.current&&m.current(l.state.values)},onChange:e=>{h.current=e},onSubmit:e=>{f.current=e},onError:e=>{p.current=e},onSuccess:e=>{m.current=e},registerNestedForm:v,unregisterNestedForm:y,getGroupedErrorsForPath:e=>{if(!e)return[];let t=l.state.errors||{},n=t[e]||[],r=Object.keys(t).filter(t=>t.startsWith(`${e}.`));if(!r.length)return n;let i=d.get(e),a=[];return r.forEach(e=>{let n=e.replace(/\.\d+(?=\.|$)/g,`.*`),r=d.get(n)||e.split(`.`).slice(-1)[0];(t[e]||[]).forEach(e=>{if(i){a.push(je(String(e),i,r));return}a.push(String(e))})}),Array.from(new Set([...n,...a]))},getConditionContext:(e,t)=>i?.(e,t)||{}};return g.current=x,(0,A.useEffect)(()=>{if(!a||!o)return;let e={path:o,validate:()=>{let e=u.validate(l.state.values),t=e?.fields||{};return Object.keys(t).length?l.setErrors(t):l.clearErrors(),e},getValues:()=>l.state.values};return a.registerNestedForm(o,e),()=>{a.unregisterNestedForm(o)}},[a,o,l,u]),x},xu=`pk-implicit-submit`,Su=(0,A.forwardRef)(({form:e,className:t,withoutForm:n=!1},r)=>{let i=(0,A.useRef)(null);return(0,A.useImperativeHandle)(r,()=>e),(0,A.useEffect)(()=>{if(n)return;let t=i.current;if(!t)return;let r=t=>{t.preventDefault(),t.stopPropagation(),e.handleSubmit()};return t.addEventListener(xu,r),()=>{t.removeEventListener(xu,r)}},[e,n]),n?(0,j.jsx)(yl.Provider,{value:e,children:(0,j.jsx)(gu,{form:e,children:(0,j.jsx)(`div`,{className:t,children:(0,j.jsx)(hu,{schema:e.schema})})})}):(0,j.jsx)(yl.Provider,{value:e,children:(0,j.jsx)(gu,{form:e,children:(0,j.jsxs)(`form`,{ref:i,onSubmit:t=>{t.preventDefault(),t.stopPropagation(),e.handleSubmit()},className:t,children:[(0,j.jsx)(hu,{schema:e.schema}),(0,j.jsx)(`button`,{type:`submit`,tabIndex:-1,"aria-hidden":`true`,className:`sr-only`})]})})})});Su.displayName=`SchemaFormEngine`;function Cu(e,t,n,r){let i=r.parentId;return{menuId:t,siteId:n,type:e.type,parentId:i===``||i===0||i===`0`||i==null?null:i,newWindow:r.newWindow??!1,enabledForPropagatedSites:r.enabledForPropagatedSites??!0,title:r.title??``,url:r.url??``,data:r.data??{}}}function wu({tab:e}){let t=R(e=>e.menuId),n=R(e=>e.siteId),r=R(e=>e.state?.menu.showSiteMenu??!1),i=R(e=>e.refresh),[a,o]=(0,A.useState)(!1),[s,c]=(0,A.useState)([]),l=bu({schemaIndex:e.schemaIndex,defaultValues:e.defaultValues}),u=(0,A.useCallback)(()=>{Object.entries(e.defaultValues).forEach(([e,t])=>{l.setFieldValue(e,t)})},[l,e.defaultValues]),d=(0,A.useCallback)(async r=>{o(!0),c([]);try{await Jr([Cu(e,t,n,r)]),u(),await i()}catch(e){let t=e?.response,n=[];if(t?.data?.errors)for(let e of Object.values(t.data.errors))n.push(...e);c(n.length?n:[F(`Couldn’t add node.`)])}finally{o(!1)}},[t,i,u,n,e]);return(0,A.useEffect)(()=>(l.onSubmit(d),()=>{l.onSubmit(null)}),[l,d]),(0,j.jsxs)(`div`,{className:`space-y-4`,children:[(0,j.jsx)(Su,{form:l,withoutForm:!0,className:`space-y-4`}),s.length>0&&(0,j.jsx)(`ul`,{className:`list-disc space-y-1 pl-5 text-sm text-red-600`,children:s.map(e=>(0,j.jsx)(`li`,{children:e},e))}),e.category===`nodeType`?(0,j.jsxs)(Tr,{type:`button`,variant:`primary`,loading:a,onClick:()=>l.handleSubmit(),children:[(0,j.jsx)(vr,{slot:`start`,icon:`plus`,className:`size-3.5`}),e.button]}):(0,j.jsxs)(Tr,{type:`button`,variant:`primary`,loading:a,onClick:()=>{if(!e.elementType)return;let a=l.store.state.values;no(e.elementType,{siteId:n,sources:e.sources,criteria:e.pickerConfig?.criteria??void 0,condition:e.pickerConfig?.condition??void 0,showSiteMenu:r,onSelect:async r=>{o(!0),c([]);try{await Jr(r.map(r=>({...Cu(e,t,n,a),elementId:r.id,elementSiteId:r.siteId,url:r.url}))),await i()}catch(e){let t=e?.response;c([t?.data?.message??F(`Couldn’t add node.`)])}finally{o(!1)}}})},children:[(0,j.jsx)(vr,{slot:`start`,icon:`plus`,className:`size-3.5`}),e.button]})]})}function Tu(){let e=Yi(),t=R(e=>e.activeTabId),n=R(e=>e.setActiveTab);return R(e=>e.loading)?null:(0,j.jsx)(`aside`,{id:`navigation-builder-sidebar`,className:`w-full`,children:(0,j.jsx)(`nav`,{id:`accordion`,className:`relative z-[1] min-h-10 overflow-hidden rounded-[5px] shadow-[inset_0_-1px_0_#e3e5e8,0_0_0_1px_rgba(205,216,228,0.25),0_2px_12px_rgba(205,216,228,0.5)]`,children:(0,j.jsx)(`ul`,{className:`m-0 block max-w-full list-none p-0`,children:e.map(e=>(0,j.jsx)(Eu,{tab:e,expanded:t===e.id,onToggle:()=>n(t===e.id?null:e.id)},e.id))})})})}function Eu({tab:e,expanded:t,onToggle:n}){return(0,j.jsxs)(`li`,{className:`block`,children:[(0,j.jsxs)(`button`,{type:`button`,className:Xr(`relative flex w-full max-w-full cursor-pointer items-center justify-between gap-2 overflow-hidden border-b border-black/10 bg-[#f3f7fc] px-5 py-2.5 text-left text-[#576575] no-underline hover:bg-[#edf2f8] hover:no-underline`,t&&`pb-2.5 text-[#29323d]`),"data-active":t?`true`:`false`,onClick:n,children:[(0,j.jsx)(`span`,{className:`min-w-0 truncate`,children:e.label}),(0,j.jsx)(vr,{icon:`chevron-down`,className:Xr(`size-3.5 shrink-0`,t&&`rotate-180`)})]}),t&&(0,j.jsx)(`div`,{className:`border-b border-black/10 bg-white p-5`,children:(0,j.jsx)(wu,{tab:e})})]})}var Du=`navigation-builder-ready`;function Ou(){let e=R(e=>e.loading);return(0,A.useEffect)(()=>{if(e){document.body.classList.remove(Du);return}return document.body.classList.add(Du),()=>{document.body.classList.remove(Du)}},[e]),null}function ku(){let e=R(e=>e.loading),t=R(e=>e.error);return(0,j.jsx)(hl,{showToolbar:!e&&!t})}function Au({menuId:e,siteId:t,initialSettingsUrl:n,initialCanEditSettings:r,headerMountNode:i,instructionsMountNode:a,actionsMountNode:o,sidebarMountNode:s}){return(0,j.jsxs)(j.Fragment,{children:[(0,j.jsx)(Ou,{}),i&&(0,Bt.createPortal)((0,j.jsx)(ku,{}),i),a&&(0,Bt.createPortal)((0,j.jsx)(gl,{}),a),o&&(0,Bt.createPortal)((0,j.jsx)(_l,{initialSettingsUrl:n,initialCanEditSettings:r}),o),s&&(0,Bt.createPortal)((0,j.jsx)(Tu,{}),s),(0,j.jsx)(il,{menuId:e,siteId:t})]})}var ju={width:512,height:512,path:`M32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 224zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384z`},Mu=!1;function Nu(){Mu||=(oe({arrowDown:ne,arrowLeft:te,arrowRight:b,arrowUp:le,arrowUpRightFromSquare:k,check:ee,chevronDown:de,chevronRight:p,clone:i,copy:ue,ellipsis:m,gear:o,pen:a,plus:d,trash:ie,triangleExclamation:n,xmark:re}),se(`sliders`,ju),!0)}var Pu=e=>{let t=e.split(`.`).map(e=>e.trim()).filter(Boolean),n=window.Craft;return t.forEach(e=>{n[e]===void 0&&(n[e]={}),n=n[e]}),n},Fu=e=>{let t=e.pluginHandle;return{...e,styleNamespace:e.styleNamespace||t,styleAttr:e.styleAttr||`data-${t}-shadow-style`,rootAttr:e.rootAttr||`data-${t}-shadow-root`,portalClassName:e.portalClassName||`${t}-ui`,translationCategory:e.translationCategory||t}},Iu=(e,t)=>{let n=Fu(t),{mountNode:r,portalContainer:i,shadowRoot:a}=Mt({element:e,styles:n.styleTexts,styleAttr:n.styleAttr,rootAttr:n.rootAttr});return Ft({portalContainer:a,shadowRootSelectors:[`[${n.rootAttr}]`],portalClassName:n.portalClassName,translationCategory:n.translationCategory}),{mountNode:r,portalContainer:i}};function Lu(){Pu(`Navigation`);let e=window.Craft;if(!e||e.Navigation?.ElementSelect)return;let t=window.Garnish,n=Rr();e.Navigation??={},e.Navigation.ElementSelect=t.Base.extend({init(e,t){let r=n(e),i=n(t);r.length&&setTimeout(()=>{let e=r.data(`elementSelect`);e&&e.on(`selectElements`,e=>{e.elements?.length&&i.val(String(e.elements[0].siteId))})},100)}})}try{customElements.define(`pk-spinner`,qe)}catch{}Pu(`Navigation`),Lu(),Nu();var Ru={pluginHandle:`navigation`,styleTexts:[Ye,Je],styleNamespace:`navigation`,styleAttr:`data-navigation-shadow-style`,rootAttr:`data-navigation-shadow-root`,portalClassName:`navigation-ui`,translationCategory:`navigation`},zu=document.querySelector(`#navigation-builder-app`),Bu=document.querySelector(`#navigation-builder-header-root`),Vu=document.querySelector(`#navigation-builder-instructions-root`),Hu=document.querySelector(`#navigation-builder-actions-root`),Uu=document.querySelector(`#navigation-builder-sidebar-root`);if(!zu)console.error(`Navigation builder container not found: #navigation-builder-app`);else{let e=Number(zu.dataset.menuId??window.NavigationBuilderConfig?.menuId),t=Number(zu.dataset.siteId??window.NavigationBuilderConfig?.siteId),n=zu.dataset.settingsUrl??Hu?.dataset.settingsUrl??``,r=(zu.dataset.canEditSettings??Hu?.dataset.canEditSettings)===`1`,{mountNode:i,portalContainer:a}=Mt({element:zu,styles:Ru.styleTexts,styleAttr:Ru.styleAttr,rootAttr:Ru.rootAttr}),o=Bu?Iu(Bu,Ru).mountNode:null,s=Vu?Iu(Vu,Ru).mountNode:null,c=Hu?Iu(Hu,Ru).mountNode:null,l=Uu?Iu(Uu,Ru).mountNode:null;(0,Vt.createRoot)(i).render((0,j.jsx)(A.StrictMode,{children:(0,j.jsx)(Lt,{translationCategory:Ru.translationCategory,portalContainer:a,portalClassName:Ru.portalClassName,shadowRootSelectors:[`[${Ru.rootAttr}]`],hostBridge:zt(),children:(0,j.jsx)(Ar,{children:(0,j.jsx)(Au,{menuId:e,siteId:t,initialSettingsUrl:n,initialCanEditSettings:r,headerMountNode:o,instructionsMountNode:s,actionsMountNode:c,sidebarMountNode:l})})})}))}
//# sourceMappingURL=builder-BKEj-Jsu.js.map