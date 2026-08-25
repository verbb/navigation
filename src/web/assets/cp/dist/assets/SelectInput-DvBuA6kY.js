import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{$ as t,B as n,G as r,K as i,V as a,W as o,Y as s,Z as c,q as l,z as u}from"./Field-BGncHpxx.js";import{n as d,r as f,t as p}from"./Select-BBao_L6-.js";var m=e(n(),1),h=t`
    @layer pk-component {
        :host {
            display: inline-block;
            flex-shrink: 0;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 9999px;
            vertical-align: middle;
        }

        .status {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: inherit;
        }

        :host([status='all']) .status { background: linear-gradient(60deg, #184cef, #e5422b); }
        :host([status='on']) .status,
        :host([status='live']) .status,
        :host([status='active']) .status,
        :host([status='enabled']) .status,
        :host([status='teal']) .status,
        :host([status='turquoise']) .status { background: var(--pk-color-teal-550); }
        :host([status='off']) .status,
        :host([status='suspended']) .status,
        :host([status='expired']) .status,
        :host([status='red']) .status { background: var(--pk-color-red-600); }
        :host([status='warning']) .status { background: var(--pk-color-amber-100); }
        :host([status='pending']) .status,
        :host([status='orange']) .status { background: var(--pk-color-orange-400); }
        :host([status='amber']) .status { background: var(--pk-color-amber-500); }
        :host([status='yellow']) .status { background: var(--pk-color-yellow-500); }
        :host([status='lime']) .status { background: var(--pk-color-lime-500); }
        :host([status='green']) .status { background: var(--pk-color-green-600); }
        :host([status='emerald']) .status { background: var(--pk-color-emerald-500); }
        :host([status='cyan']) .status { background: var(--pk-color-cyan-500); }
        :host([status='sky']) .status { background: var(--pk-color-sky-500); }
        :host([status='blue']) .status { background: var(--pk-color-blue-600); }
        :host([status='indigo']) .status { background: var(--pk-color-indigo-500); }
        :host([status='violet']) .status { background: var(--pk-color-violet-500); }
        :host([status='purple']) .status { background: var(--pk-color-purple-500); }
        :host([status='fuchsia']) .status { background: var(--pk-color-fuchsia-500); }
        :host([status='pink']) .status { background: var(--pk-color-pink-500); }
        :host([status='rose']) .status { background: var(--pk-color-rose-500); }
        :host([status='light']) .status { background: var(--pk-color-gray-100); }
        :host([status='gray']) .status,
        :host([status='grey']) .status { background: var(--pk-color-gray-300); }
        :host([status='white']) .status { background: var(--pk-color-white); }
        :host([status='black']) .status { background: var(--pk-color-gray-800); }
        :host([status='disabled']) .status,
        :host([status='inactive']) .status {
            /* Ring color is overridable for inverted / selected surfaces. */
            background: transparent;
            box-shadow: inset 0 0 0 2px var(--pk-status-ring, var(--pk-color-gray-500));
        }
    }
`,g=class extends i{constructor(...e){super(...e),this.status=`on`,this.ariaLabel=null}static{this.styles=h}render(){return c`
            <span
                part="base"
                class="status"
                role="status"
                aria-label=${this.ariaLabel??s}
            ></span>
        `}};l([o({reflect:!0})],g.prototype,`status`,void 0),l([o({attribute:`aria-label`})],g.prototype,`ariaLabel`,void 0),g=l([r(`pk-status`)],g);var _=u({tagName:`pk-status`,elementClass:g,react:m.default}),v=a(),y=e=>typeof e==`object`&&!!e&&`group`in e,b=e=>e==null?``:String(e),x=(0,m.forwardRef)(function({options:e,value:t,onChange:n,placeholder:r,disabled:i,isInvalid:a,clearable:o,size:s,width:c,name:l,id:u,onBlur:h,"aria-label":g,"aria-describedby":x,"aria-errormessage":S,"aria-labelledby":C},w){let T=(0,m.useMemo)(()=>e.flatMap(e=>y(e)?e.options:[e]),[e]),E=e=>{if(!n)return;let t=e.detail,r=Array.isArray(t?.value)?t?.value[0]:t?.value,i=T.find(e=>b(e.value)===b(r));n(i?i.value:r??``)},D=e=>(0,v.jsxs)(p,{value:b(e.value),disabled:e.disabled,children:[e.status?(0,v.jsx)(_,{slot:`start`,status:e.status}):null,e.label]},b(e.value));return(0,v.jsx)(f,{ref:w,value:b(t),placeholder:r,disabled:i,invalid:a,clearable:o,size:s,width:c,name:l,id:u,onPkChange:E,onFocusOut:h,"aria-label":g,"aria-describedby":x,"aria-errormessage":S,"aria-labelledby":C,children:e.map(e=>y(e)?(0,v.jsx)(d,{label:e.group,children:e.options.map(D)},e.group):D(e))})});export{_ as n,x as t};
//# sourceMappingURL=SelectInput-DvBuA6kY.js.map