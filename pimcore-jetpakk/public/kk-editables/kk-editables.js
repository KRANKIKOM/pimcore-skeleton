const E="/pimcore_editables_config.json";let c={icons:["arrow-24"],styles:["primary","secondary"],types:["fill","outline","link"]};const m=e=>{e=e.split("/");const s=e[0]||"",i=e[1]||"";return`<svg data-size="${e[2]||24}" data-rotate="${i}"><use xlink:href="/assets/iconset.svg#${s}"></use></svg>`};fetch(E).then(e=>e.json()).then(e=>{"pimcore_button"in e&&Object.keys(e.pimcore_button).forEach(s=>{c[s]=e.pimcore_button[s]})}).catch(e=>{console.error(error)});const C=function(e,s){var i=e.btnType;function n(){return c.styles.map(d=>{let u=["btn"];return i&&i.length>0&&i!=="fill"&&u.push(i),d&&d.length>0&&u.push(d),{boxLabel:d,name:"btnStyle",inputValue:d,checked:e.btnStyle===d,cls:`btn btn-sm ${u.join("-")}`}})}const a={fieldLabel:"Style",defaultType:"radiofield",id:"btnStyle",hidden:e.disable.btnStyle,cls:"input-radio--pickerwall",height:(()=>n().length>15?192:n().length>10?144:(n().length>5,80))(),value:e.btnStyle,defaults:{flex:1},layout:"hbox",items:n()};var o=new Ext.form.FieldContainer(a);function l(){const d=`
      <svg width="24" height="24" viewBox="0 0 24 24">
        <path d="M24 6a6 6 0 0 0-6-6H6a6 6 0 0 0-6 6v12a6 6 0 0 0 6 6h12a6 6 0 0 0 6-6V6Z" fill="#ffffff"/>
        <path d="M21.2 6.5c0-2-1.6-3.7-3.7-3.7h-11c-2 0-3.7 1.6-3.7 3.7v11c0 2 1.6 3.7 3.7 3.7h11c2 0 3.7-1.6 3.7-3.7v-11Z" fill="none" stroke="#ff4343" stroke-width="2"/>
        <path d="M4.3 19.7 19.7 4.3m0 15.4L4.3 4.3" fill="none" stroke="#ff4343" stroke-width="2" stroke-linecap="butt"/>
      </svg>
    `;return c.icons.includes(null)||(c.icons=[null,...c.icons]),c.icons.map(f=>({boxLabel:f?m(f):d,name:"btnIcon",inputValue:f,checked:e.btnIcon===f}))}function r(){return{fieldLabel:"Icon",height:(()=>l().length>30?144:l().length>10?96:64)(),defaultType:"radiofield",id:"btnIcon",hidden:e.disable.btnIcon,cls:"input-radio--pickerwall",value:e.btnIcon,defaults:{flex:1},layout:"hbox",items:l()}}var h=new Ext.form.FieldContainer(r());function x(d,u,f){u&&(i=d.modelValue);const k=b.down("#btnVisualsFieldset"),g=Ext.getCmp("btnStyle"),_=k.items.indexOf(g);b.down("#btnVisualsFieldset").remove(g,!0),a.items=n(),b.down("#btnVisualsFieldset").insert(_,new Ext.form.FieldContainer(a))}function v(){return c.types.map(d=>({boxLabel:d,name:"btnType",inputValue:d,checked:e.btnType===d,listeners:{change:x}}))}var y=new Ext.form.FieldContainer({defaultType:"radiofield",cls:"kk-radio--toggler",value:e.btnType,layout:"hbox",fieldLabel:"Typ",hidden:e.disable.btnType,name:"btnType",items:v()}),w=new Ext.form.FieldContainer({defaultType:"radiofield",fieldLabel:"Icon Position",name:"btnIconPos",cls:"kk-radio--toggler",hidden:e.disable.btnIconPos,id:"btnIconPos",value:e.btnIconPos,layout:"hbox",items:[{boxLabel:"links",name:"btnIconPos",inputValue:"left",checked:e.btnIconPos==="left"},{boxLabel:"rechts",name:"btnIconPos",inputValue:"right",checked:e.btnIconPos==="right"}]}),b=new Ext.FormPanel({itemId:"form",items:[{xtype:"fieldset",id:"btnVisualsFieldset",layout:"vbox",collapsible:!1,defaultType:"textfield",width:"100%",defaults:{width:"100%"},items:[y,o,h,w]}],buttons:[{text:t("cancel"),listeners:{click:s.cancel},iconCls:"pimcore_icon_cancel"},{text:t("save"),listeners:{click:s.save},iconCls:"pimcore_icon_save"}]}),p=new Ext.Window({modal:!1,width:600,height:470,title:"Button Style",items:[b],layout:"fit"});return p.show(),p};pimcore.registerNS("pimcore.document.editables.button");pimcore.document.editables.button=Class.create(pimcore.document.editable,{initialize:function(e,s,i,n,a){this.defaultData={path:"",parameters:"",anchor:"",accesskey:"",rel:"",tabindex:"",target:"",class:"",attributes:"",btnStyle:"primary",btnType:"fill",btnIcon:null,btnIconPos:"right"},this.id=intval(e),i.default&&(this.defaultData=mergeObject(this.defaultData,i.default));const o=(()=>{const l={},r=["text","btnType","btnStyle","btnIcon","btnIconPos"];return i.disable&&Array.isArray(i.disable)&&i.disable.length>0&&r.forEach(h=>{l[h]=i.disable.includes(h)}),{disable:l}})();this.id=e,this.data=mergeObject(this.defaultData,n!=null?n:{}),this.data={...this.data,...o},this.data.disable.text&&(this.data.text=""),c.types.length==0&&(this.data.disable.btnType=!0),c.styles.length==0&&(this.data.disable.btnStyle=!0),c.icons.length==0&&(this.data.disable.btnIcon=!0,this.data.disable.btnPos=!0),i.force&&(this.data={...this.data,...i.force}),this.config=i,this.name=s,this.inherited=a,this.generateIcon=m},render:function(){this.setupWrapper(),this.element=Ext.get(this.id),this.config.required&&(this.required=this.config.required),this.checkValue(),Ext.get(this.id).insertHtml("beforeEnd",'<div class="pimcore_editable_button_wrapper">'+this.getLinkContent()+"</div>");var e=this.data.disable.btnType&&this.data.disable.btnStyle&&this.data.disable.btnIcon&&this.data.disable.btnIconPos,s=new Ext.Button({iconCls:"pimcore_icon_settings",cls:"pimcore_edit_button_settings",listeners:{click:this.openEditor.bind(this)},disabled:e}),i=new Ext.Button({iconCls:"pimcore_icon_link",cls:"pimcore_edit_button_link",listeners:{click:this.openLinkEditor.bind(this)}});s.render(this.id),i.render(this.id)},openLinkEditor:function(){window.dndManager.disable(),this.window=pimcore.helpers.editmode.openLinkEditPanel(this.data,{empty:this.empty.bind(this),cancel:this.cancel.bind(this),save:this.save.bind(this)});const s=this.window.getComponent("form").getForm().findField("text");this.data.disable.text&&(s.setDisabled(!0),s.hide())},openEditor:function(){this.window=C(this.data,{empty:this.empty.bind(this),cancel:this.cancel.bind(this),save:this.save.bind(this)})},getLinkContent:function(){let e="",s="",i=this.data.btnIcon?`<div class="icon">${this.generateIcon(this.data.btnIcon)}</div>`:"",n=this.data.btnIconPos==="right"?i:"",a=this.data.btnIconPos==="left"?i:"";i.length>0||(e="["+t("not_set")+"]");const o=["btn"];this.config.class&&o.push(this.config.class),this.data.class&&o.push(this.data.class),this.data.text?e=this.data.text:this.config.placeholder&&(e=this.config.placeholder);let l=Ext.util.Format.htmlEncode(e);this.config.textPrefix!==void 0&&(l=this.config.textPrefix+l),this.config.textSuffix!==void 0&&(l+=this.config.textSuffix),!this.data.path&&!this.data.anchor&&!this.data.parameters&&o.push("disabled");let r="";return this.data.btnType&&this.data.btnType.length>0&&this.data.btnType!=="fill"&&(r+="-"+this.data.btnType),this.data.btnStyle&&this.data.btnStyle.length>0&&(r+="-"+this.data.btnStyle),r.length>0&&o.push("btn"+r),i.length>0&&o.push("btn--has-icon"),(l||e)&&(s=`<span>${l||e}</span>`),`
      <div class="${o.join(" ")}">
        ${a}
        ${s}
        ${n}
      </div>
    `},save:function(){window.dndManager.enable();const e=this.window.getComponent("form").getForm().getFieldValues();this.data={...this.data,...e,...this.config.force,disable:this.data.disable},this.checkValue(!0),this.window.close(),Ext.get(this.id).query(".pimcore_editable_button_wrapper")[0].innerHTML=this.getLinkContent(),this.reload()},reload:function(){this.config.reload&&(this.reloadDocument(),this.checkValue(!0))},empty:function(){window.dndManager.enable(),this.window.close(),this.data=this.defaultData,this.checkValue(!0),Ext.get(this.id).query(".pimcore_editable_button_wrapper")[0].innerHTML=this.getLinkContent()},cancel:function(){window.dndManager.enable(),this.window.close()},checkValue:function(e){var s="";this.required&&(this.required==="linkonly"?this.data.path&&(s=this.data.path):this.data.text&&this.data.path&&(s=this.data.text+this.data.path),this.validateRequiredValue(s,this.element,this,e))},getValue:function(){return this.data},getType:function(){return"button"}});pimcore.registerNS("pimcore.document.editables.headline");pimcore.document.editables.headline=Class.create(pimcore.document.editable,{initialize:function(e,s,i,n,a){n||(n={}),n.text&&n.text.length>0&&(n.text=str_replace(`
`,"<br>",n.text)),this.config=this.parseConfig(i),this.defaultData={class:"",text:"",headlineSeo:"h2",headlineVisual:null},this.config.default&&(this.defaultData=mergeObject(this.defaultData,this.config.default)),this.data=mergeObject(this.defaultData,n),this.id=e,this.name=s},render:function(){const e=this;this.setupWrapper(),this.element=Ext.get(this.id),this.config.required&&(this.required=this.config.required),this.checkValue(),this.element.on("keyup",this.checkValue.bind(this,!0)),this.element.on("keydown",function(i,n,a){if(in_array(i.getCharCode(),[13])){if(window.getSelection){var o=window.getSelection(),l=o.getRangeAt(0),r=document.createElement("br"),h=document.createTextNode("\xA0");l.deleteContents(),l.insertNode(r),l.collapse(!1),l.insertNode(h),l.selectNodeContents(h),o.removeAllRanges(),o.addRange(l)}i.stopEvent()}}),this.element.dom.addEventListener("paste",function(i){i.preventDefault();var n="";i.clipboardData?n=i.clipboardData.getData("text/plain"):window.clipboardData&&(n=window.clipboardData.getData("Text")),n=this.clearText(n),n=htmlentities(n,"ENT_NOQUOTES",null,!1),n=trim(n);try{pimcore.edithelpers.pasteHtmlAtCaret(n)}catch(a){console.log(a)}}.bind(this)),Ext.get(this.id).insertHtml("afterbegin",this.renderHeadline());var s=new Ext.Button({iconCls:"pimcore_icon_settings",cls:"pimcore_edit_headline_button",listeners:{afterrender:function(i){i.getEl().set({contenteditable:"false"}),i.getEl().insertHtml("afterbegin",`
                <div class="indicators">
                  ${e.renderIndicators()}
                </div>
              `)},click:this.openEditor.bind(this)}});s.render(this.id)},clearText:function(e){return e=str_replace(`\r
`," ",e),e=str_replace(`
`," ",e),e},openEditor:function(){window.dndManager.disable(),this.window=this.openHeadlineEditPanel(this.data,{cancel:this.cancel.bind(this),save:this.save.bind(this)})},renderIndicators:function(){return`
        <div class="indicator--headlineSeo">${this.data.headlineSeo}</div>
        <div class="indicator--headlineVisual">${this.data.headlineVisual?this.data.headlineVisual:"\u2B05"}</div>
      `},openHeadlineEditPanel:function(e,s){var i=new Ext.FormPanel({itemId:"form",items:[{xtype:"tabpanel",deferredRender:!1,defaults:{autoHeight:!0,bodyStyle:"padding:10px"},border:!1,items:[{title:t("basic"),layout:"vbox",border:!1,defaultType:"textfield",items:[{xtype:"fieldset",layout:"vbox",title:"\xDCberschrift",collapsible:!1,defaultType:"textfield",width:"100%",items:[{xtype:"fieldcontainer",fieldLabel:"\xDCberschrift-Gr\xF6\xDFe (SEO)",defaultType:"radiofield",cls:"kk-radio--toggler",value:this.data.headlineSeo,defaults:{flex:1},layout:"hbox",items:[...(()=>["span","h1","h2","h3","h4","h5","h6"].map(a=>({boxLabel:a.toUpperCase(),name:"headlineSeo",inputValue:a,checked:this.data.headlineSeo===a})))()]},{xtype:"fieldcontainer",fieldLabel:"\xDCberschrift-Gr\xF6\xDFe (visuell)",defaultType:"radiofield",value:this.data.headlineVisual,cls:"kk-radio--toggler",defaults:{flex:1},layout:"hbox",items:[{boxLabel:"Keine",name:"headlineVisual",inputValue:null,checked:!this.data.headlineVisual},...(()=>["h1","h2","h3","h4","h5","h6"].map(a=>({boxLabel:a.toUpperCase(),name:"headlineVisual",inputValue:a,checked:this.data.headlineVisual===a})))()]}]}]},{title:t("advanced"),layout:"form",defaultType:"textfield",border:!1,items:[{fieldLabel:t("class"),name:"class",width:300,value:this.data.class},{fieldLabel:t("attributes")+' (key="value")',name:"attributes",width:300,value:this.data.attributes}]}]}],buttons:[{text:t("cancel"),listeners:{click:s.cancel},iconCls:"pimcore_icon_cancel"},{text:t("save"),listeners:{click:s.save},iconCls:"pimcore_icon_save"}]}),n=new Ext.Window({modal:!1,width:600,height:470,title:"\xDCberschrift Bearbeiten",items:[i],layout:"fit"});return n.show(),n},renderHeadline:function(){const e=this.data.headlineSeo;let s=["pimcore_editable_headline_text","pimcore_contenteditable"],i=this.config.placeholder?this.config.placeholder:"Headline";return this.data.class&&s.push(this.data.class),this.data.headlineVisual&&s.push(this.data.headlineVisual),this.config.headlineClass&&s.push(this.config.headlineClass),`
      <${e} data-placeholder="${i}" contenteditable="true" class="${s.join(" ")}">${this.data.text!=="<br>"?this.data.text:""}</${e}>
    `},save:function(){window.dndManager.enable();const e=this.window.getComponent("form").getForm().getFieldValues();this.data={...this.data,...e},this.checkValue(!0),this.window.close(),Ext.get(this.id).query(".pimcore_editable_headline_text")[0].remove(),Ext.get(this.id).insertHtml("afterbegin",this.renderHeadline()),Ext.get(this.id).query(".indicators")[0].innerHTML=`
        ${this.renderIndicators()}
      `,this.reload()},reload:function(){this.config.reload&&(this.reloadDocument(),this.checkValue(!0))},cancel:function(){window.dndManager.enable(),this.window.close()},checkValue:function(e){const s=Ext.get(this.id).query(".pimcore_editable_headline_text")[0];if(s){const i=s.innerHTML;i!=this.data.text&&(trim(strip_tags(i.replace(/&nbsp;/g,""))).length==0?this.data.text="":this.data.text=trim(strip_tags(i,"<br>"))),this.required&&this.validateRequiredValue(value,s,this,e)}},getValue:function(){const e=this.data,s=Ext.get(this.id).query(".pimcore_editable_headline_text")[0];return s&&e.text&&(e.text=s.innerHTML),e.text=strip_tags(e.text,"<br>"),e.text=e.text.replace(/<br>/g,`
`),e.text=trim(e.text),e},getType:function(){return"headline"}});pimcore.registerNS("pimcore.document.editables.toggle");pimcore.document.editables.toggle=Class.create(pimcore.document.editable,{initialize:function(e,s,i,n,a){n||(n={}),this.id=e,this.name=s,this.config=this.parseConfig(i),n.value=n.value||this.config.defaultValue||this.config.choices[0].value||"",n.config=i,this.data=n},render:function(){this.setupWrapper(),this.htmlId=this.id+"_editable";var e=Ext.get(this.id);const s=document.createElement("div");if(s.setAttribute("class","pimcore_editable_toggle__inner"),this.elComponents=[],this.config.label){const n=document.createElement("label");n.innerText=this.config.label,e.appendChild(n)}this.config.stacked&&Ext.get(e.id).addCls("pimcore_editable_toggle--stacked"),this.config.floating&&(Ext.get(e.id).addCls("pimcore_editable_toggle--floating"),this.config.inset&&Ext.get(e.id).setStyle("inset",this.config.inset)),e.appendChild(s),this.config.choices&&this.config.choices.length>0&&this.config.choices[0].value&&this.config.choices.forEach(a=>{if("value"in a&&a.label){const o=this.htmlId+"-"+a.value,l=document.createElement("input"),r=document.createElement("label");l.setAttribute("id",o),l.setAttribute("name",o),l.setAttribute("type","radio"),l.setAttribute("value",a.value),l.setAttribute("name",this.htmlId),a.value==this.data.value&&l.setAttribute("checked","checked"),s.appendChild(l),r.setAttribute("for",o),r.innerHTML=a.label,s.appendChild(r),this.elComponents.push(Ext.get(o))}});const i=this;this.elComponents.forEach(n=>{n.on("change",function(){i.data=i.getValue(),i.reloadDocument()})})},renderInDialogBox:function(){var e;this.config.dialogBoxConfig&&(this.config.dialogBoxConfig.label||this.config.dialogBoxConfig.name)&&(this.config.label=(e=this.config.dialogBoxConfig.label)!=null?e:this.config.dialogBoxConfig.name),this.render()},getValue:function(){const e=this.data;return this.elComponents.forEach(s=>{s.dom.checked&&(this.data.value=s.dom.value)}),e},getType:function(){return"toggle"}});/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PCL
 */pimcore.registerNS("pimcore.document.editables.colorpicker");pimcore.document.editables.colorpicker=Class.create(pimcore.document.editable,{initialize:function(e,s,i,n){this.id=e,this.name=s,i=this.parseConfig(i),i.name=e+"_editable",i.triggerAction="all",i.editable=i.editable?i.editable:!1,(n===null||n.length===0)&&(n={hex:"ffffff",r:255,g:255,b:255,a:1,h:0,s:0,v:1}),n.hex.length!==3&&n.hex.length!==6&&(n.hex="ffffff"),n&&(i.value=n.hex),this.data=n,this.config=i},render:function(){this.setupWrapper(),this.element=Ext.create("Ext.ux.colorpick.Field",this.config),this.element.render(this.id),this.config.reload&&this.element.on("change",this.reloadDocument)},getValue:function(){return this.element&&(this.data={...this.element.color,hex:this.element.value}),this.config.value=this.element.value,this.data},getType:function(){return"colorpicker"}});console.log(`%c

                \u2553\u2588\u2588\u2588\u2584   \u2553\u2588\u2588\u258C
              \u2584\u2588\u2588\u2588\u2580\u2588\u2588\u2588\u2584  \u2514\u2580\u2588\u2588\u2588
            \u2584\u2588\u2588\u2588\u2580   \u2559\u2588\u2588\u2588\u2584   \u2580\u2588\u2588\u2588\u2584
          \u2584\u2588\u2588\u2588\u2559  \u2553\u2588\u2584  \u2559\u2588\u2588\u2588\u2593   \u2580\u2588\u2588\u2588\u2584
       ,\u2593\u2588\u2588\u2588\u2500  \u2584\u2588\u2588\u2588\u2580  \u2553\u2588\u2588\u2588\u2580   \u2584\u2588\u2588\u2588\u2580
     \u2553\u2588\u2588\u2588\u2580\u2500  \u2584\u2588\u2588\u2588\u2580  \u2584\u2588\u2588\u2588\u2580   \u2584\u2588\u2588\u2588\u2559
    \u2590\u2588\u2588\u2588    \u2588\u2588\u2588\u258C   \u2588\u2588\u2588\u2588    \u2588\u2588\u2588\u258C
     \u2559\u2588\u2588\u2588\u2593   \u2559\u2588\u2588\u2588\u2584  \u2559\u2588\u2588\u2588\u2584   \u2580\u2588\u2588\u2588\u2584
       \u2514\u2580\u2588\u2588\u2588,  \u2559\u2588\u2588\u2588\u2584  \u2559\u2588\u2588\u2588\u2593   \u2559\u2588\u2588\u2588\u2584
          \u2580\u2588\u2588\u2588\u2584  \u2559\u2588\u2588\u2588\u2593  \u2514\u2580\u2580\u2500  \u2584\u2588\u2588\u2588\u2580
            \u2559\u2588\u2588\u2588\u2584  \u2514\u2580\u2588\u2588\u2588,   \u2584\u2588\u2588\u2588\u2580
              \u2559\u2588\u2588\u2588\u2584   \u2580\u2588\u2588\u2588\u2593\u2588\u2588\u2588\u2514
                \u2559\u2588\u2588\u2588    \u2559\u2588\u2588\u2580\u2500

%c
        \u2553\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2556
        \u2551  KK Editables [3.0.0]  \u2551
        \u2559\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u255C
           Active Editables:
           \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500
           - Button
           - Headline
           - Toggle
           - Colorpicker

           Docs:
           \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500
           insight.krankikom.de/devdocs/

`,"color: #967DBE","color: #FFDC5A");
