// jQuery is loaded from a CDN

// jQuery Tools is loaded from a CDN


/*
 * flowplayer.js 3.2.2. The Flowplayer API
 *
 * Copyright 2010 Flowplayer Oy
 *
 * This file is part of Flowplayer.
 *
 * Flowplayer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Flowplayer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Flowplayer.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Date: 2010-05-20 17:15:38 +0000 (Thu, 20 May 2010)
 * Revision: 480
 */
(function(){function g(o){console.log("$f.fireEvent",[].slice.call(o))}function k(q){if(!q||typeof q!="object"){return q}var o=new q.constructor();for(var p in q){if(q.hasOwnProperty(p)){o[p]=k(q[p])}}return o}function m(t,q){if(!t){return}var o,p=0,r=t.length;if(r===undefined){for(o in t){if(q.call(t[o],o,t[o])===false){break}}}else{for(var s=t[0];p<r&&q.call(s,p,s)!==false;s=t[++p]){}}return t}function c(o){return document.getElementById(o)}function i(q,p,o){if(typeof p!="object"){return q}if(q&&p){m(p,function(r,s){if(!o||typeof s!="function"){q[r]=s}})}return q}function n(s){var q=s.indexOf(".");if(q!=-1){var p=s.slice(0,q)||"*";var o=s.slice(q+1,s.length);var r=[];m(document.getElementsByTagName(p),function(){if(this.className&&this.className.indexOf(o)!=-1){r.push(this)}});return r}}function f(o){o=o||window.event;if(o.preventDefault){o.stopPropagation();o.preventDefault()}else{o.returnValue=false;o.cancelBubble=true}return false}function j(q,o,p){q[o]=q[o]||[];q[o].push(p)}function e(){return"_"+(""+Math.random()).slice(2,10)}var h=function(t,r,s){var q=this,p={},u={};q.index=r;if(typeof t=="string"){t={url:t}}i(this,t,true);m(("Begin*,Start,Pause*,Resume*,Seek*,Stop*,Finish*,LastSecond,Update,BufferFull,BufferEmpty,BufferStop").split(","),function(){var v="on"+this;if(v.indexOf("*")!=-1){v=v.slice(0,v.length-1);var w="onBefore"+v.slice(2);q[w]=function(x){j(u,w,x);return q}}q[v]=function(x){j(u,v,x);return q};if(r==-1){if(q[w]){s[w]=q[w]}if(q[v]){s[v]=q[v]}}});i(this,{onCuepoint:function(x,w){if(arguments.length==1){p.embedded=[null,x];return q}if(typeof x=="number"){x=[x]}var v=e();p[v]=[x,w];if(s.isLoaded()){s._api().fp_addCuepoints(x,r,v)}return q},update:function(w){i(q,w);if(s.isLoaded()){s._api().fp_updateClip(w,r)}var v=s.getConfig();var x=(r==-1)?v.clip:v.playlist[r];i(x,w,true)},_fireEvent:function(v,y,w,A){if(v=="onLoad"){m(p,function(B,C){if(C[0]){s._api().fp_addCuepoints(C[0],r,B)}});return false}A=A||q;if(v=="onCuepoint"){var z=p[y];if(z){return z[1].call(s,A,w)}}if(y&&"onBeforeBegin,onMetaData,onStart,onUpdate,onResume".indexOf(v)!=-1){i(A,y);if(y.metaData){if(!A.duration){A.duration=y.metaData.duration}else{A.fullDuration=y.metaData.duration}}}var x=true;m(u[v],function(){x=this.call(s,A,y,w)});return x}});if(t.onCuepoint){var o=t.onCuepoint;q.onCuepoint.apply(q,typeof o=="function"?[o]:o);delete t.onCuepoint}m(t,function(v,w){if(typeof w=="function"){j(u,v,w);delete t[v]}});if(r==-1){s.onCuepoint=this.onCuepoint}};var l=function(p,r,q,t){var o=this,s={},u=false;if(t){i(s,t)}m(r,function(v,w){if(typeof w=="function"){s[v]=w;delete r[v]}});i(this,{animate:function(y,z,x){if(!y){return o}if(typeof z=="function"){x=z;z=500}if(typeof y=="string"){var w=y;y={};y[w]=z;z=500}if(x){var v=e();s[v]=x}if(z===undefined){z=500}r=q._api().fp_animate(p,y,z,v);return o},css:function(w,x){if(x!==undefined){var v={};v[w]=x;w=v}r=q._api().fp_css(p,w);i(o,r);return o},show:function(){this.display="block";q._api().fp_showPlugin(p);return o},hide:function(){this.display="none";q._api().fp_hidePlugin(p);return o},toggle:function(){this.display=q._api().fp_togglePlugin(p);return o},fadeTo:function(y,x,w){if(typeof x=="function"){w=x;x=500}if(w){var v=e();s[v]=w}this.display=q._api().fp_fadeTo(p,y,x,v);this.opacity=y;return o},fadeIn:function(w,v){return o.fadeTo(1,w,v)},fadeOut:function(w,v){return o.fadeTo(0,w,v)},getName:function(){return p},getPlayer:function(){return q},_fireEvent:function(w,v,x){if(w=="onUpdate"){var z=q._api().fp_getPlugin(p);if(!z){return}i(o,z);delete o.methods;if(!u){m(z.methods,function(){var B=""+this;o[B]=function(){var C=[].slice.call(arguments);var D=q._api().fp_invoke(p,B,C);return D==="undefined"||D===undefined?o:D}});u=true}}var A=s[w];if(A){var y=A.apply(o,v);if(w.slice(0,1)=="_"){delete s[w]}return y}return o}})};function b(q,G,t){var w=this,v=null,D=false,u,s,F=[],y={},x={},E,r,p,C,o,A;i(w,{id:function(){return E},isLoaded:function(){return(v!==null&&v.fp_play!=undefined&&!D)},getParent:function(){return q},hide:function(H){if(H){q.style.height="0px"}if(w.isLoaded()){v.style.height="0px"}return w},show:function(){q.style.height=A+"px";if(w.isLoaded()){v.style.height=o+"px"}return w},isHidden:function(){return w.isLoaded()&&parseInt(v.style.height,10)===0},load:function(J){if(!w.isLoaded()&&w._fireEvent("onBeforeLoad")!==false){var H=function(){u=q.innerHTML;if(u&&!flashembed.isSupported(G.version)){q.innerHTML=""}flashembed(q,G,{config:t});if(J){J.cached=true;j(x,"onLoad",J)}};var I=0;m(a,function(){this.unload(function(K){if(++I==a.length){H()}})})}return w},unload:function(J){if(this.isFullscreen()&&/WebKit/i.test(navigator.userAgent)){if(J){J(false)}return w}if(u.replace(/\s/g,"")!==""){if(w._fireEvent("onBeforeUnload")===false){if(J){J(false)}return w}D=true;try{if(v){v.fp_close();w._fireEvent("onUnload")}}catch(H){}var I=function(){v=null;q.innerHTML=u;D=false;if(J){J(true)}};setTimeout(I,50)}else{if(J){J(false)}}return w},getClip:function(H){if(H===undefined){H=C}return F[H]},getCommonClip:function(){return s},getPlaylist:function(){return F},getPlugin:function(H){var J=y[H];if(!J&&w.isLoaded()){var I=w._api().fp_getPlugin(H);if(I){J=new l(H,I,w);y[H]=J}}return J},getScreen:function(){return w.getPlugin("screen")},getControls:function(){return w.getPlugin("controls")._fireEvent("onUpdate")},getLogo:function(){try{return w.getPlugin("logo")._fireEvent("onUpdate")}catch(H){}},getPlay:function(){return w.getPlugin("play")._fireEvent("onUpdate")},getConfig:function(H){return H?k(t):t},getFlashParams:function(){return G},loadPlugin:function(K,J,M,L){if(typeof M=="function"){L=M;M={}}var I=L?e():"_";w._api().fp_loadPlugin(K,J,M,I);var H={};H[I]=L;var N=new l(K,null,w,H);y[K]=N;return N},getState:function(){return w.isLoaded()?v.fp_getState():-1},play:function(I,H){var J=function(){if(I!==undefined){w._api().fp_play(I,H)}else{w._api().fp_play()}};if(w.isLoaded()){J()}else{if(D){setTimeout(function(){w.play(I,H)},50)}else{w.load(function(){J()})}}return w},getVersion:function(){var I="flowplayer.js 3.2.2";if(w.isLoaded()){var H=v.fp_getVersion();H.push(I);return H}return I},_api:function(){if(!w.isLoaded()){throw"Flowplayer "+w.id()+" not loaded when calling an API method"}return v},setClip:function(H){w.setPlaylist([H]);return w},getIndex:function(){return p}});m(("Click*,Load*,Unload*,Keypress*,Volume*,Mute*,Unmute*,PlaylistReplace,ClipAdd,Fullscreen*,FullscreenExit,Error,MouseOver,MouseOut").split(","),function(){var H="on"+this;if(H.indexOf("*")!=-1){H=H.slice(0,H.length-1);var I="onBefore"+H.slice(2);w[I]=function(J){j(x,I,J);return w}}w[H]=function(J){j(x,H,J);return w}});m(("pause,resume,mute,unmute,stop,toggle,seek,getStatus,getVolume,setVolume,getTime,isPaused,isPlaying,startBuffering,stopBuffering,isFullscreen,toggleFullscreen,reset,close,setPlaylist,addClip,playFeed,setKeyboardShortcutsEnabled,isKeyboardShortcutsEnabled").split(","),function(){var H=this;w[H]=function(J,I){if(!w.isLoaded()){return w}var K=null;if(J!==undefined&&I!==undefined){K=v["fp_"+H](J,I)}else{K=(J===undefined)?v["fp_"+H]():v["fp_"+H](J)}return K==="undefined"||K===undefined?w:K}});w._fireEvent=function(Q){if(typeof Q=="string"){Q=[Q]}var R=Q[0],O=Q[1],M=Q[2],L=Q[3],K=0;if(t.debug){g(Q)}if(!w.isLoaded()&&R=="onLoad"&&O=="player"){v=v||c(r);o=v.clientHeight;m(F,function(){this._fireEvent("onLoad")});m(y,function(S,T){T._fireEvent("onUpdate")});s._fireEvent("onLoad")}if(R=="onLoad"&&O!="player"){return}if(R=="onError"){if(typeof O=="string"||(typeof O=="number"&&typeof M=="number")){O=M;M=L}}if(R=="onContextMenu"){m(t.contextMenu[O],function(S,T){T.call(w)});return}if(R=="onPluginEvent"||R=="onBeforePluginEvent"){var H=O.name||O;var I=y[H];if(I){I._fireEvent("onUpdate",O);return I._fireEvent(M,Q.slice(3))}return}if(R=="onPlaylistReplace"){F=[];var N=0;m(O,function(){F.push(new h(this,N++,w))})}if(R=="onClipAdd"){if(O.isInStream){return}O=new h(O,M,w);F.splice(M,0,O);for(K=M+1;K<F.length;K++){F[K].index++}}var P=true;if(typeof O=="number"&&O<F.length){C=O;var J=F[O];if(J){P=J._fireEvent(R,M,L)}if(!J||P!==false){P=s._fireEvent(R,M,L,J)}}m(x[R],function(){P=this.call(w,O,M);if(this.cached){x[R].splice(K,1)}if(P===false){return false}K++});return P};function B(){if($f(q)){$f(q).getParent().innerHTML="";p=$f(q).getIndex();a[p]=w}else{a.push(w);p=a.length-1}A=parseInt(q.style.height,10)||q.clientHeight;E=q.id||"fp"+e();r=G.id||E+"_api";G.id=r;t.playerId=E;if(typeof t=="string"){t={clip:{url:t}}}if(typeof t.clip=="string"){t.clip={url:t.clip}}t.clip=t.clip||{};if(q.getAttribute("href",2)&&!t.clip.url){t.clip.url=q.getAttribute("href",2)}s=new h(t.clip,-1,w);t.playlist=t.playlist||[t.clip];var H=0;m(t.playlist,function(){var J=this;if(typeof J=="object"&&J.length){J={url:""+J}}m(t.clip,function(K,L){if(L!==undefined&&J[K]===undefined&&typeof L!="function"){J[K]=L}});t.playlist[H]=J;J=new h(J,H,w);F.push(J);H++});m(t,function(J,K){if(typeof K=="function"){if(s[J]){s[J](K)}else{j(x,J,K)}delete t[J]}});m(t.plugins,function(J,K){if(K){y[J]=new l(J,K,w)}});if(!t.plugins||t.plugins.controls===undefined){y.controls=new l("controls",null,w)}y.canvas=new l("canvas",null,w);function I(J){if(!w.isLoaded()&&w._fireEvent("onBeforeClick")!==false){w.load()}return f(J)}u=q.innerHTML;if(u.replace(/\s/g,"")!==""){if(q.addEventListener){q.addEventListener("click",I,false)}else{if(q.attachEvent){q.attachEvent("onclick",I)}}}else{if(q.addEventListener){q.addEventListener("click",f,false)}w.load()}}if(typeof q=="string"){var z=c(q);if(!z){throw"Flowplayer cannot access element: "+q}else{q=z;B()}}else{B()}}var a=[];function d(o){this.length=o.length;this.each=function(p){m(o,p)};this.size=function(){return o.length}}window.flowplayer=window.$f=function(){var p=null;var o=arguments[0];if(!arguments.length){m(a,function(){if(this.isLoaded()){p=this;return false}});return p||a[0]}if(arguments.length==1){if(typeof o=="number"){return a[o]}else{if(o=="*"){return new d(a)}m(a,function(){if(this.id()==o.id||this.id()==o||this.getParent()==o){p=this;return false}});return p}}if(arguments.length>1){var t=arguments[1],q=(arguments.length==3)?arguments[2]:{};if(typeof t=="string"){t={src:t}}t=i({bgcolor:"#000000",version:[9,0],expressInstall:"http://static.flowplayer.org/swf/expressinstall.swf",cachebusting:true},t);if(typeof o=="string"){if(o.indexOf(".")!=-1){var s=[];m(n(o),function(){s.push(new b(this,k(t),k(q)))});return new d(s)}else{var r=c(o);return new b(r!==null?r:o,t,q)}}else{if(o){return new b(o,t,q)}}}return null};i(window.$f,{fireEvent:function(){var o=[].slice.call(arguments);var q=$f(o[0]);return q?q._fireEvent(o.slice(1)):null},addPlugin:function(o,p){b.prototype[o]=p;return $f},each:m,extend:i});if(typeof jQuery=="function"){jQuery.fn.flowplayer=function(q,p){if(!arguments.length||typeof arguments[0]=="number"){var o=[];this.each(function(){var r=$f(this);if(r){o.push(r)}});return arguments.length?o[arguments[0]]:new d(o)}return this.each(function(){$f(this,k(q),p?k(p):{})})}}})();(function(){var h=document.all,j="http://www.adobe.com/go/getflashplayer",c=typeof jQuery=="function",e=/(\d+)[^\d]+(\d+)[^\d]*(\d*)/,b={width:"100%",height:"100%",id:"_"+(""+Math.random()).slice(9),allowfullscreen:true,allowscriptaccess:"always",quality:"high",version:[3,0],onFail:null,expressInstall:null,w3c:false,cachebusting:false};if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){}})}function i(l,f){if(f){for(key in f){if(f.hasOwnProperty(key)){l[key]=f[key]}}}return l}function a(f,n){var m=[];for(var l in f){if(f.hasOwnProperty(l)){m[l]=n(f[l])}}return m}window.flashembed=function(f,m,l){if(typeof f=="string"){f=document.getElementById(f.replace("#",""))}if(!f){return}if(typeof m=="string"){m={src:m}}return new d(f,i(i({},b),m),l)};var g=i(window.flashembed,{conf:b,getVersion:function(){var f;try{f=navigator.plugins["Shockwave Flash"].description.slice(16)}catch(n){try{var l=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");f=l&&l.GetVariable("$version")}catch(m){}}f=e.exec(f);return f?[f[1],f[3]]:[0,0]},asString:function(l){if(l===null||l===undefined){return null}var f=typeof l;if(f=="object"&&l.push){f="array"}switch(f){case"string":l=l.replace(new RegExp('(["\\\\])',"g"),"\\$1");l=l.replace(/^\s?(\d+\.?\d+)%/,"$1pct");return'"'+l+'"';case"array":return"["+a(l,function(o){return g.asString(o)}).join(",")+"]";case"function":return'"function()"';case"object":var m=[];for(var n in l){if(l.hasOwnProperty(n)){m.push('"'+n+'":'+g.asString(l[n]))}}return"{"+m.join(",")+"}"}return String(l).replace(/\s/g," ").replace(/\'/g,'"')},getHTML:function(o,l){o=i({},o);var n='<object width="'+o.width+'" height="'+o.height+'" id="'+o.id+'" name="'+o.id+'"';if(o.cachebusting){o.src+=((o.src.indexOf("?")!=-1?"&":"?")+Math.random())}if(o.w3c||!h){n+=' data="'+o.src+'" type="application/x-shockwave-flash"'}else{n+=' classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'}n+=">";if(o.w3c||h){n+='<param name="movie" value="'+o.src+'" />'}o.width=o.height=o.id=o.w3c=o.src=null;o.onFail=o.version=o.expressInstall=null;for(var m in o){if(o[m]){n+='<param name="'+m+'" value="'+o[m]+'" />'}}var p="";if(l){for(var f in l){if(l[f]){var q=l[f];p+=f+"="+(/function|object/.test(typeof q)?g.asString(q):q)+"&"}}p=p.slice(0,-1);n+='<param name="flashvars" value=\''+p+"' />"}n+="</object>";return n},isSupported:function(f){return k[0]>f[0]||k[0]==f[0]&&k[1]>=f[1]}});var k=g.getVersion();function d(f,n,m){if(g.isSupported(n.version)){f.innerHTML=g.getHTML(n,m)}else{if(n.expressInstall&&g.isSupported([6,65])){f.innerHTML=g.getHTML(i(n,{src:n.expressInstall}),{MMredirectURL:location.href,MMplayerType:"PlugIn",MMdoctitle:document.title})}else{if(!f.innerHTML.replace(/\s/g,"")){f.innerHTML="<h2>Flash version "+n.version+" or greater is required</h2><h3>"+(k[0]>0?"Your version is "+k:"You have no flash plugin installed")+"</h3>"+(f.tagName=="A"?"<p>Click here to download latest version</p>":"<p>Download latest version from <a href='"+j+"'>here</a></p>");if(f.tagName=="A"){f.onclick=function(){location.href=j}}}if(n.onFail){var l=n.onFail.call(this);if(typeof l=="string"){f.innerHTML=l}}}}if(h){window[n.id]=document.getElementById(n.id)}i(this,{getRoot:function(){return f},getOptions:function(){return n},getConf:function(){return m},getApi:function(){return f.firstChild}})}if(c){jQuery.tools=jQuery.tools||{version:"3.2.2"};jQuery.tools.flashembed={conf:b};jQuery.fn.flashembed=function(l,f){return this.each(function(){$(this).data("flashembed",flashembed(this,l,f))})}}})();


/*
 * flowplayer.playlist 3.0.8. Flowplayer JavaScript plugin.
 *
 * This file is part of Flowplayer, http://flowplayer.org
 *
 * Author: Tero Piirainen, <info@flowplayer.org>
 * Copyright (c) 2008-2010 Flowplayer Ltd
 *
 * Dual licensed under MIT and GPL 2+ licenses
 * SEE: http://www.opensource.org/licenses
 *
 * Date: 2010-05-04 05:33:23 +0000 (Tue, 04 May 2010)
 * Revision: 3405
 */
(function(a){$f.addPlugin("playlist",function(d,q){var o=this;var b={playingClass:"playing",pausedClass:"paused",progressClass:"progress",template:'<a href="${url}">${title}</a>',loop:false,playOnClick:true,manual:false};a.extend(b,q);d=a(d);var j=o.getPlaylist().length<=1||b.manual;var k=null;function e(s){var r=n;a.each(s,function(t,u){if(!a.isFunction(u)){r=r.replace("${"+t+"}",u).replace("$%7B"+t+"%7D",u)}});return r}function i(){k=p().unbind("click.playlist").bind("click.playlist",function(){return h(a(this),k.index(this))})}function c(){d.empty();a.each(o.getPlaylist(),function(){d.append(e(this))});i()}function h(r,s){if(r.hasClass(b.playingClass)||r.hasClass(b.pausedClass)){o.toggle()}else{r.addClass(b.progressClass);o.play(s)}return false}function m(){if(j){k=p()}k.removeClass(b.playingClass);k.removeClass(b.pausedClass);k.removeClass(b.progressClass)}function f(r){return(j)?k.filter("[href="+r.originalUrl+"]"):k.eq(r.index)}function p(){var r=d.find("a");return r.length?r:d.children()}if(!j){var n=d.is(":empty")?b.template:d.html();c()}else{k=p();if(a.isFunction(k.live)){var l=a(d.selector+" a");if(!l.length){l=a(d.selector+" > *")}l.live("click",function(){var r=a(this);return h(r,r.attr("href"))})}else{k.click(function(){var r=a(this);return h(r,r.attr("href"))})}var g=o.getClip(0);if(!g.url&&b.playOnClick){g.update({url:k.eq(0).attr("href")})}}o.onBegin(function(r){m();f(r).addClass(b.playingClass)});o.onPause(function(r){f(r).removeClass(b.playingClass).addClass(b.pausedClass)});o.onResume(function(r){f(r).removeClass(b.pausedClass).addClass(b.playingClass)});if(!b.loop&&!j){o.onBeforeFinish(function(r){if(!r.isInStream&&r.index<k.length-1){return false}})}if(j&&b.loop){o.onBeforeFinish(function(s){var r=f(s);if(r.next().length){r.next().click()}else{k.eq(0).click()}return false})}o.onUnload(function(){m()});if(!j){o.onPlaylistReplace(function(){c()})}o.onClipAdd(function(s,r){k.eq(r).before(e(s));i()});return o})})(jQuery);


/*
 * jQuery Address Plugin v1.2.1
 * http://www.asual.com/jquery/address/
 *
 * Copyright (c) 2009-2010 Rostislav Hristov
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * Date: 2010-05-20 09:39:26 +0300 (Thu, 20 May 2010)
 */
(function(c){c.address=function(){var v=function(a){c(c.address).trigger(c.extend(c.Event(a),function(){for(var b={},g=c.address.parameterNames(),h=0,q=g.length;h<q;h++)b[g[h]]=c.address.parameter(g[h]);return{value:c.address.value(),path:c.address.path(),pathNames:c.address.pathNames(),parameterNames:g,parameters:b,queryString:c.address.queryString()}}.call(c.address)))},w=function(a,b,g){c(c.address).bind(a,b,g);return c.address},p=function(){var a=d.href.indexOf("#");return a!=-1?W(J(t(d.href.substr(a+
1),j))):""},X=function(){return"javascript"},Y=function(a,b){if(i.strict)a=b?a.substr(0,1)!="/"?"/"+a:a:a==""?"/":a;return a},K=function(a,b){return u&&d.protocol=="file:"?b?f.replace(/\?/,"%3F"):f.replace(/%253F/,"?"):a},t=function(a,b){if(i.crawlable&&b)return(a!=""?"!":"")+a;return a.replace(/^\!/,"")},x=function(a,b){return parseInt(a.css(b),10)},Z=function(a){for(var b,g,h=0,q=a.childNodes.length;h<q;h++){if(a.childNodes[h].src)b=String(a.childNodes[h].src);if(g=Z(a.childNodes[h]))b=g}return b},
H=function(){if(!L){var a=p(),b=f!=a;if(y&&m<523){if(D!=F.length){D=F.length;if(typeof z[D-1]!=A)f=z[D-1];G(j)}}else if(u&&m<7&&b)d.reload();else if(b){f=a;G(j)}}},G=function(a){v($);v(a?aa:ba);E(ca,10)},ca=function(){var a=(d.pathname+(/\/$/.test(d.pathname)?"":"/")+(c.address?c.address.value():"")).replace(/\/\//,"/").replace(/^\/$/,""),b=n[i.tracker];if(typeof b==M)b(a);else if(typeof urchinTracker==M)urchinTracker(a);else if(typeof pageTracker!=A&&typeof pageTracker._trackPageview==M)pageTracker._trackPageview(a);
else typeof _gaq!=A&&typeof _gaq.push==M&&_gaq.push(["_trackPageview",a])},da=function(){var a=l.contentWindow.document;a.open();a.write("<html><head><title>"+k.title+"</title><script>var "+o+' = "'+p()+'";<\/script></head></html>');a.close()},fa=function(){if(!ea){ea=e;var a=c("body").ajaxComplete(function(){ja.call(this)}).trigger("ajaxComplete");if(i.wrap){c("body > *").wrapAll('<div style="padding:'+(x(a,"marginTop")+x(a,"paddingTop"))+"px "+(x(a,"marginRight")+x(a,"paddingRight"))+"px "+(x(a,
"marginBottom")+x(a,"paddingBottom"))+"px "+(x(a,"marginLeft")+x(a,"paddingLeft"))+'px;" />').parent().wrap('<div id="'+o+'" style="height:100%; overflow:auto;'+(y?window.statusbar.visible&&!/chrome/i.test(O)?"":" resize:both;":"")+'" />');c("html, body").css({height:"100%",margin:0,padding:0,overflow:"hidden"});y&&c('<style type="text/css" />').appendTo("head").text("#"+o+"::-webkit-resizer { background-color: #fff; }")}if(u&&m<8){a=k.getElementsByTagName("frameset")[0];l=k.createElement((a?"":"i")+
"frame");if(a){a.insertAdjacentElement("beforeEnd",l);a[a.cols?"cols":"rows"]+=",0";l.src=X()+":"+j;l.noResize=e;l.frameBorder=l.frameSpacing=0}else{l.src=X()+":"+j;l.style.display="none";k.body.insertAdjacentElement("afterBegin",l)}E(function(){c(l).bind("load",function(){var b=l.contentWindow;f=typeof b[o]!=A?b[o]:"";if(f!=p()){G(j);d.hash=K(t(f,e),e)}});typeof l.contentWindow[o]==A&&da()},50)}else if(y){if(m<418){c(k.body).append('<form id="'+o+'" style="position:absolute;top:-9999px;" method="get"></form>');
P=k.getElementById(o)}if(typeof d[o]==A)d[o]={};if(typeof d[o][d.pathname]!=A)z=d[o][d.pathname].split(",")}E(function(){v("init");G(j)},1);if(u&&m>7||!u&&"on"+I in n)if(n.addEventListener)n.addEventListener(I,H,false);else n.attachEvent&&n.attachEvent("on"+I,H);else ka(H,50);c("a").filter("[rel*='address:']").address()}},la=function(){if(n.removeEventListener)n.removeEventListener(I,H,false);else n.detachEvent&&n.detachEvent("on"+I,H)},ja=function(){var a=d.pathname.replace(/\/$/,"");c("body").html().indexOf("_escaped_fragment_")!=
-1&&c("a[href]:not([href^=http])",this).each(function(){var b=c(this).attr("href").replace(new RegExp(a+"/?$"),"");if(b==""||b.indexOf("_escaped_fragment_")!=-1)c(this).attr("href","#"+decodeURIComponent(b.replace(/\/(.*)\?_escaped_fragment_=(.*)$/,"!$2")))})},o="jQueryAddress",M="function",A="undefined",I="hashchange",$="change",aa="internalChange",ba="externalChange",e=true,j=false,i={autoUpdate:e,crawlable:j,history:e,strict:e,wrap:j},r=c.browser,m=parseFloat(c.browser.version),ga=r.mozilla,u=
r.msie,s=r.opera,y=r.safari,Q=j,n=function(){try{return top.document!==undefined?top:window}catch(a){return window}}(),k=n.document,F=n.history,d=n.location,ka=setInterval,E=setTimeout,J=decodeURI,W=encodeURI,O=navigator.userAgent,l,P,B=Z(document),ha=B?B.indexOf("?"):-1,R=k.title,D=F.length,L=j,ea=j,S=e,ia=e,N=j,z=[],f=p();if(u){m=parseFloat(O.substr(O.indexOf("MSIE")+4));if(k.documentMode&&k.documentMode!=m)m=k.documentMode!=8?7:8;c(document).bind("propertychange",function(){if(k.title!=R&&k.title.indexOf("#"+
p())!=-1)k.title=R})}if(Q=ga&&m>=1||u&&m>=6||s&&m>=9.5||y&&m>=312){for(r=1;r<D;r++)z.push("");z.push(f);if(u&&d.hash!=f)d.hash="#"+K(t(f,e),e);if(s)history.navigationMode="compatible";if(B&&ha!=-1){B=B.substr(ha+1).split("&");for(r=0;r<B.length;r++){s=B[r].split("=");if(/^(autoUpdate|crawlable|history|strict|wrap)$/.test(s[0]))i[s[0]]=isNaN(s[1])?/^(true|yes)$/i.test(s[1]):parseInt(s[1],10)!==0;if(/^tracker$/.test(s[0]))i[s[0]]=s[1]}}document.readyState=="complete"&&fa();c(fa);c(window).bind("unload",
la)}else if(!Q&&p()!=""||y&&m<418&&p()!=""&&d.search!=""){k.open();k.write('<html><head><meta http-equiv="refresh" content="0;url='+encodeURI(d.href.substr(0,d.href.indexOf("#")))+'" /></head></html>');k.close()}else ca();return{bind:function(a,b,g){return w(a,b,g)},init:function(a){return w("init",a)},change:function(a){return w($,a)},internalChange:function(a){return w(aa,a)},externalChange:function(a){return w(ba,a)},baseURL:function(){var a=d.href;if(a.indexOf("#")!=-1)a=a.substr(0,a.indexOf("#"));
if(/\/$/.test(a))a=a.substr(0,a.length-1);return a},autoUpdate:function(a){if(a!==undefined){i.autoUpdate=a;return this}return i.autoUpdate},crawlable:function(a){if(a!==undefined){i.crawlable=a;return this}return i.crawlable},history:function(a){if(a!==undefined){i.history=a;return this}return i.history},strict:function(a){if(a!==undefined){i.strict=a;return this}return i.strict},tracker:function(a){if(a!==undefined){i.tracker=a;return this}return i.tracker},wrap:function(a){if(a!==undefined){i.wrap=
a;return this}return i.wrap},update:function(){N=e;this.value(f);N=j;return this},title:function(a){if(a!==undefined){a=J(a);E(function(){R=k.title=a;if(ia&&l&&l.contentWindow&&l.contentWindow.document){l.contentWindow.document.title=a;ia=j}if(!S&&ga)d.replace(d.href.indexOf("#")!=-1?d.href:d.href+"#");S=j},50);return this}return k.title},value:function(a){if(a!==undefined){a=W(J(Y(a,e)));if(a=="/")a="";if(f==a&&!N)return;S=e;f=a;if(i.autoUpdate||N){L=e;G(e);z[F.length]=f;if(y)if(i.history){d[o][d.pathname]=
z.toString();D=F.length+1;if(m<418){if(d.search==""){P.action="#"+t(f,e);P.submit()}}else if(m<523||f==""){a=k.createEvent("MouseEvents");a.initEvent("click",e,e);var b=k.createElement("a");b.href="#"+t(f,e);b.dispatchEvent(a)}else d.hash="#"+t(f,e)}else d.replace("#"+t(f,e));else if(f!=p())if(i.history)d.hash="#"+K(t(f,e),e);else d.replace("#"+t(f,e));u&&m<8&&i.history&&E(da,50);if(y)E(function(){L=j},1);else L=j}return this}if(!Q)return null;return J(Y(K(f,j),j))},path:function(a){if(a!==undefined){var b=
this.queryString(),g=this.hash();this.value(a+(b?"?"+b:"")+(g?"#"+g:""));return this}return this.value().split("#")[0].split("?")[0]},queryString:function(a){if(a!==undefined){var b=this.hash();this.value(this.path()+(a?"?"+a:"")+(b?"#"+b:""));return this}a=this.value().split("?");return a.slice(1,a.length).join("?").split("#")[0]},parameter:function(a,b,g){var h,q;if(b!==undefined){var T=this.parameterNames();q=[];for(h=0;h<T.length;h++){var U=T[h],C=this.parameter(U);if(typeof C=="string")C=[C];
if(U==a)C=b===null||b===""?[]:g?C.concat([b]):[b];for(var V=0;V<C.length;V++)q.push(U+"="+C[V])}c.inArray(a,T)==-1&&b!==null&&b!==""&&q.push(a+"="+b);this.queryString(q.join("&"));return this}if(b=this.queryString()){q=b.split("&");b=[];for(h=0;h<q.length;h++){g=q[h].split("=");g[0]==a&&b.push(g[1])}if(b.length!==0)return b.length!=1?b:b[0]}},pathNames:function(){var a=this.path(),b=a.replace(/\/{2,9}/g,"/").split("/");if(a.substr(0,1)=="/"||a.length===0)b.splice(0,1);a.substr(a.length-1,1)=="/"&&
b.splice(b.length-1,1);return b},parameterNames:function(){var a=this.queryString(),b=[];if(a&&a.indexOf("=")!=-1){a=a.split("&");for(var g=0;g<a.length;g++){var h=a[g].split("=")[0];c.inArray(h,b)==-1&&b.push(h)}}return b},hash:function(a){if(a!==undefined){this.value(this.value().split("#")[0]+(a?"#"+a:""));return this}a=this.value().split("#");return a.slice(1,a.length).join("#")}}}();c.fn.address=function(v){var w=function(){if(c(this).is("a")){var p=v?v.call(this):/address:/.test(c(this).attr("rel"))?
c(this).attr("rel").split("address:")[1].split(" ")[0]:c(this).attr("href").replace(/^#\!?/,"");c.address.value(p);return false}};c(this).click(w).live("click",w);c(this).live("submit",function(){if(c(this).is("form")){var p=v?v.call(this):c(this).attr("action")+"?"+c(this).serialize();c.address.value(p);return false}});return this}})(jQuery);

////////////////////////////////////////////////////
// Crockford JS framework v1.0
////////////////////////////////////////////////////

/// Define "create" method on Object type as a static method which allows easy inheritence between objects.
if( typeof Object.create !== "function" )
{
	Object.create = function( instance )
	{
		var F = function() {};
		F.prototype = instance;
		return new F();
	}
}

/// Define "method" method on Function as an instance method which provides syntactic sugar for declaring instance methods on a function
/// (useful for constructor functions to augment types) and also ensures that instance method cannot be overriden.
Function.prototype.method = function( name, func )
{
	if( !this.prototype[ name ] )
	{
		this.prototype[ name ] = func;
		return this;
	}
};

/// Define the "curry" method on Function as an instance method which provides a technique for wrapping a method and specifying default values for parameters.
/// Example: var add1 = add.curry(1);	// will always add 1 + number
/// add1(2);	// this will result in 3
Function.method( "curry", function()
{
	var slice = Array.prototype.slice;
	args = slice.apply( arguments );
	that = this;
	return function()
	{
		return that.apply( null, args.concat( slice.apply( arguments ) ) );
	};
});

/// Determines if the value specified refers to an array.
function isArray( value )
{
	var isValueAnArray = value &&
						 typeof value === "object" &&
						 typeof value.length === "number" &&
						 !value.propertyIsEnumerable( "length" );
	return isValueAnArray;
}

/// Helper method to dispose an array and to optionally call a callback on each item after being removed from the array.
function disposeArray( array, callback )
{
	if( isArray( array ) )
	{
		var item;
		while( array.length !== 0 )
		{
			item = array.pop();
			if( callback && typeof callback === "function" )
			{
				callback( item );
			}
		}
	}
}

/// Ensure that the Array type has an "indexOf" method like other languages.
if( typeof Array.prototype.indexOf !== "function" )
{
	Array.prototype.indexOf = function( item, index )
	{
		var count = this.length;
		index = ( isNaN( index ) || index < 0 || index >= count ) ? 0 : index;
		for( var i = 0; i < count; i++ )
		{
			if( this[ i ] === item )
			{
				return i;
			}
		}
		return -1;
	};
}

/// Ensure that the Array type has an "lastIndexOf" method like other languages.
if( typeof Array.prototype.lastIndexOf !== "function" )
{
	Array.prototype.lastIndexOf = function( item, index )
	{
		var count = this.length;
		index = ( isNaN( index ) || index < 0 || index >= count ) ? count - 1 : index;
		for( var i = 0; i < count; i-- )
		{
			if( this[ i ] === item )
			{
				return i;
			}
		}
		return -1;
	};
}

/// Represents a general purpose dictionary that can be used to associate a key of any kind to a value of any kind.
/// A key can only be associated to a single value.
function Dictionary()
{
	// private fields
	var keys = [];
	var values = [];

	this.get = function( key )
	{
		var index = keys.indexOf( key );
		if( index >= 0 )
		{
			return values[ index ];
		}
		else
		{
			return null;
		}
	};

	this.set = function( key, value )
	{
		var index = keys.indexOf( key );
		if( index >= 0 )
		{
			values[ index ] = value;
		}
		else
		{
			keys.push( key );
			values.push( value );
		}
	};

	this.containsKey = function( key )
	{
		var hasKey = keys.indexOf( key );
		return hasKey;
	};

	this.containsValue = function( value )
	{
		var hasValue = values.indexOf( value );
		return hasValue;
	};

	this.getKeys = function()
	{
		return keys.slice();
	};

	this.getValues = function()
	{
		return values.slice();
	};

	this.getKeysByValue = function( value )
	{
		var match = [];
		var count = values.length;
		var v;
		for( var i = 0; i < count; i++ )
		{
			v = values[ i ];
			if( v === value )
			{
				match.push( keys[ i ] );
			}
		}
		return match;
	};

	this.getCount = function()
	{
		return keys.length;
	};

	this.clear = function()
	{
		disposeArray( keys );
		disposeArray( values );
	};
}

/// Represents a general purpose map that can be used to associate a key of any kind to a value of any kind.
/// The difference between a dictionary and map is that a key can be associated to many values.
function Map()
{
	// private fields
	var keys = [];
	var values = [];

	/// Retrieves the values associated to the given key as an array.
	this.get = function( key )
	{
		var index = keys.indexOf( key );
		if( index >= 0 )
		{
			return values[ index ].slice();
		}
		else
		{
			return [];
		}
	};

	this.set = function( key, value )
	{
		var index = keys.indexOf( key );
		if( index >= 0 )
		{
			var array = values[ index ];
			if( array.indexOf( value ) >= 0 )
			{
				array.push( value );
			}
		}
		else
		{
			keys.push( key );
			values.push( [ value ] );
		}
	};

	this.containsKey = function( key )
	{
		var hasKey = keys.indexOf( key );
		return hasKey;
	};

	this.containsValue = function( value )
	{
		var count = values.length;
		var array;
		for( var i = 0; i < count; i++ )
		{
			array = values[ i ];
			if( array.indexOf( value ) >= 0 )
			{
				return true;
			}
		}
		return false;
	};

	this.getKeys = function()
	{
		return keys.slice();
	};

	this.getKeysByValue = function( value )
	{
		var match = [];
		var count = values.length;
		var array;
		for( var i = 0; i < count; i++ )
		{
			array = values[ i ];
			if( array.indexOf( value ) >= 0 )
			{
				match.push( keys[ i ] );
			}
		}
		return match;
	};

	this.getCount = function()
	{
		return keys.length;
	};

	this.clear = function()
	{
		disposeArray( keys );
		disposeArray( values, disposeArray );
	};
}

var CONSTRUCTORS = function()
{
	var constructors = {};

	var set = function( n, c )
	{
		if( !constructors[ n ] )
		{
			constructors[ n ] = c;
		}
	};

	var get = function( n )
	{
		if( constructors[ n ] )
		{
			return constructors[ n ];
		}

		return error( "Constructor " + n + " not defined" );
	};

	var error = function( message )
	{
		alert( message );
		throw( message );
		return false;
	};

	return {
		set: function( n, c ) { set( n, c ); },
		get: function( n ) { return get( n ); }
	};
};

////////////////////////////////////////////////////
// php.js methods
////////////////////////////////////////////////////

String.method( "urlencode", function()
{
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: urlencode('Kevin van Zonneveld!');
    // *     returns 1: 'Kevin+van+Zonneveld%21'

    var ret = this;

    ret = ret.toString();
    ret = encodeURIComponent(ret);
    ret = ret.replace(/%20/g, '+');

    return ret;
});

String.method( "urldecode", function()
{
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: urldecode('Kevin+van+Zonneveld%21');
    // *     returns 1: 'Kevin van Zonneveld!'

    var ret = this;

    ret = ret.replace(/\+/g, '%20');
    ret = decodeURIComponent(ret);
    ret = ret.toString();

    return ret;
});

String.method( "base64_encode", function()
{
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Bayron Guevara
    // +   improved by: Thunder.m
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: utf8_encode
    // *     example 1: base64_encode('Kevin van Zonneveld');
    // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='

    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof window['atob'] == 'function') {
    //    return atob(data);
    //}

    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = ac = 0, enc="", tmp_arr = [];
    var data = this.utf8_encode();

    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1<<16 | o2<<8 | o3;

        h1 = bits>>18 & 0x3f;
        h2 = bits>>12 & 0x3f;
        h3 = bits>>6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');

    switch( data.length % 3 ){
        case 1:
            enc = enc.slice(0, -2) + '==';
        break;
        case 2:
            enc = enc.slice(0, -1) + '=';
        break;
    }

    return enc;
});

String.method( "utf8_encode", function()
{
    // http://kevin.vanzonneveld.net
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: utf8_encode('Kevin van Zonneveld');
    // *     returns 1: 'Kevin van Zonneveld'

    var str_data = this.replace(/\r\n/g,"\n");
    var tmp_arr = [], ac = 0;

    for (var n = 0; n < str_data.length; n++) {
        var c = str_data.charCodeAt(n);
        if (c < 128) {
            tmp_arr[ac++] = String.fromCharCode(c);
        } else if((c > 127) && (c < 2048)) {
            tmp_arr[ac++] = String.fromCharCode((c >> 6) | 192);
            tmp_arr[ac++] = String.fromCharCode((c & 63) | 128);
        } else {
            tmp_arr[ac++] = String.fromCharCode((c >> 12) | 224);
            tmp_arr[ac++] = String.fromCharCode(((c >> 6) & 63) | 128);
            tmp_arr[ac++] = String.fromCharCode((c & 63) | 128);
        }
    }

    return tmp_arr.join('');
});

String.prototype.unescapeHtml = function () {
    var temp = document.createElement("div");
    temp.innerHTML = this;
    var result = temp.childNodes[0].nodeValue;
    temp.removeChild(temp.firstChild);
    return result;
}

////////////////////////////////////////////////////
// Inner message jQuery plugin
// Written by Mathieu Bouchard (c) 2010
////////////////////////////////////////////////////

var originalVal = jQuery.fn.val;

jQuery.fn.realval = function()
{
	return originalVal.apply( this, arguments );
};

jQuery.fn.val = function()
{
	if( jQuery( this ).length > 0 )
	{
		if( arguments.length == 0 )
		{
			if( jQuery( this ).get( 0 ).message == jQuery( this ).realval() )
			{
				return "";
			}
		}
	}

	return originalVal.apply( this, arguments );
};

jQuery.fn.innerMessage = function()
{
	if( jQuery( this ).length > 0 )
	{
		jQuery( this ).get( 0 ).message = jQuery( this ).realval();

		jQuery( this ).addClass( "inner-messaging" );

		var thisObj = this;

		jQuery( this ).focus( function()
		{
			if( jQuery( this ).realval() == jQuery( thisObj ).get( 0 ).message )
			{
				jQuery( this ).realval( "" );
				jQuery( this ).removeClass( "inner-messaging" );
			}
		});

		jQuery( this ).blur( function( event )
		{
			if( jQuery( this ).realval().length == 0 )
			{
				jQuery( this ).realval( jQuery( thisObj ).get( 0 ).message );
				jQuery( this ).addClass( "inner-messaging" );
			}
		});
	}

	return this;
};

////////////////////////////////////////////////////
// Javascript helpers
// Written by Mathieu Bouchard (c) 2010
////////////////////////////////////////////////////

jQuery( document ).ready( function()
{
	// Add hover class to navigation li's in IE
	if( jQuery.browser.msie )
	{
		jQuery( "ul.navigation li" ).hover(
			function() { jQuery( this ).addClass( "hover" ); },
			function() { jQuery( this ).removeClass( "hover" ); }
		);
	}

	if( window.parent.document != document )
	{
		// Make iframe links that target new windows open in a new window outside the iframe
		jQuery( "a[rel]" ).live( "click", function( e )
		{
			top.frames[ jQuery( this ).attr( "rel" ) ].location.href = jQuery( this ).attr( "href" );
			e.preventDefault();
		});
	}
	else
	{
		// Make rel anchors open in a new window
		jQuery( "a[rel]" ).live( "click", function()
		{
			jQuery( this ).attr( "target", jQuery( this ).attr( "rel" ) );
		});
	}
});

////////////////////////////////////////////////////
// ARIMODAL: auto-resizing iframe modal window
// Written by Mathieu Bouchard (c) 2010
////////////////////////////////////////////////////

var ARIMODAL = function()
{
	var modalCollection = new Dictionary();

	var innerModal = function()
	{
		// private fields
		var initialized = false;
		var myInstance = null;

		// private methods
		var initialize = function()
		{
			if( !initialized )
			{
				initialized = true;

				if( isInnerModal() )
				{
					myInstance = getMyInstance();
					myInstance.show();
					myInstance.resize( jQuery( document ).width(), jQuery( document ).height() );
				}
			}
		};

		var getMyInstance = function()
		{
			return top.ARIMODAL.getModalCollection().get( window.location.pathname );
		};

		var isInnerModal = function()
		{
			return window.parent.document != document;
		};

		var closeMe = function()
		{
			getMyInstance().close();
		};

		// public domain
		return {
			initialize: function()
			{
				initialize();
				return this;
			},
			closeMe: function()
			{
				initialize();
				closeMe();
				return this;
			}
		};
	}();

	var modal = function()
	{
		// private fields
		var initialized = false;
		var modalAPI = null;
		var modalContainer = null;
		var modalHref = null;
		var modalIframe = null;

		// private methods
		var initialize = function( containerID, href, that )
		{
			if( !initialized )
			{
				initialized = true;
				modalHref = href;
				modalCollection.set( href, that );
				initializeModalContainer( containerID );
			}
		};

		var launchModalWindow = function()
		{
			modalAPI = modalContainer.overlay(
			{
				api: true,
				onBeforeLoad: function( e )
				{
					e.stopPropagation();

					modalIframe = jQuery( "<iframe frameborder='0' scrolling='auto' style='width: 100%;' />" );
					modalContainer.append( modalIframe );
					modalIframe.attr( "src", modalHref );

					$( ".close", modalContainer ).css( {
						position: "absolute",
						cursor: "pointer",
						zIndex: 10001
					});
				},
				onClose: function( e )
				{
					modalIframe.remove();
					modalContainer.remove();
				},
				closeOnClick: true,
				expose:
				{
					color: "#000",
					loadSpeed: 200,
					opacity: 0.75
				}
			}).load();
		};

		var initializeModalContainer = function( containerID )
		{
			if( jQuery( "#" + containerID ).length == 0 )
			{
				jQuery( "body" ).append( "<div id='" + containerID + "' class='modal-box' style='display: none; z-index: 10000; padding: 0px; border: 0px;' />" );
			}

			modalContainer = jQuery( "#" + containerID );
		};

		var showIframe = function()
		{
			modalIframe.show();
			return this;
		};

		var resizeIframe = function( width, height )
		{
			var maxHeight = parseInt( modalIframe.css( "max-height" ) );
			var maxWidth = parseInt( modalIframe.css( "max-width" ) );
			var windowHeight = jQuery( window ).height();
			var windowWidth = jQuery( window ).width();

			maxHeight = maxHeight > windowHeight ? windowHeight : maxHeight;
			maxWidth = maxWidth > windowWidth ? windowWidth : maxWidth;

			height = height > maxHeight ? maxHeight : height;
			width = width > maxWidth ? maxWidth : width;

			//width += 17;	// scrollbar fix (except IE6)!

			var left = ( jQuery( window ).width() - width ) / 2;
			var top = ( jQuery( window ).height() - height ) / 2;

			modalIframe.animate( { height: height, width: width }, { duration: 250, queue: false } );
			modalContainer.animate( {  height: height, width: width, left: left, top: top }, { duration: 250, queue: false } );
		};

		var closeModal = function()
		{
			modalAPI.close();
			return this;
		};

		var showCloseIcon = function()
		{
			jQuery( ".close", modalContainer ).show();
		};

		// public domain
		return {
			initialize: function( containerID, href )
			{
				initialize( containerID, href, this );
				return this;
			},
			launch: function()
			{
				launchModalWindow();
				return this;
			},
			close: function()
			{
				closeModal();
				return this;
			},
			show: function()
			{
				showIframe();
				showCloseIcon();
				return this;
			},
			resize: function( width, height )
			{
				resizeIframe( width, height );
				return this;
			}
		};
	};

	return {
		initialize: function()
		{
			innerModal.initialize();
		},
		launch: function( containerID, href )
		{
			modal().initialize( containerID, href ).launch();
		},
		closeMe: function()
		{
			innerModal.closeMe();
		},
		getModalCollection: function()
		{
			return modalCollection;
		}
	};
}();

////////////////////////////////////////////////////
// jQuery plug-in
// Remove all attributes
////////////////////////////////////////////////////

jQuery.fn.removeAttributes = function()
{
	return this.each( function()
	{
		var attributes = jQuery.map( this.attributes, function( item )
		{
			return item.name;
		});

		var obj = $( this );

		jQuery.each( attributes, function( i, item )
		{
			obj.removeAttr( item );
		});
	});
};

////////////////////////////////////////////////////
// jQuery plug-in
// Find element using a selector and remove an
// attribute on it, but without changing scope
// to found element, retaining original scope.
// Written by Mathieu Bouchard (c) 2010
////////////////////////////////////////////////////

jQuery.fn.findAndRemoveAttribute = function( findSelector, attributeToRemove )
{
	this.find( findSelector ).each( function()
	{
		$( this ).removeAttr( attributeToRemove );
	});

	return this;
};

////////////////////////////////////////////////////
// jQuery plug-in
// Compare two DOM nodes for same HTML, which has
// the benefit of being able to compare two DOM
// nodes from different documents.
// Caveat: regexp was simplified to remove ALL
// spaces, therefore changes in spaces only will not
// be detected.
// Written by Mathieu Bouchard (c) 2010
////////////////////////////////////////////////////

jQuery.fn.compare = function( against )
{
	if( $( this ).length == 1 && against.length == 1 )
	{
		var html1 = $( this ).html().replace( /\s/g, "" );
		var html2 = against.html().replace( /\s/g, "" );

		return( html1 === html2 );
	}
}

////////////////////////////////////////////////////
// strips brackets from input names
////////////////////////////////////////////////////

String.method( "stripBrackets", function()
{
    return this.replace( "[", "" ).replace( "]", "" ).replace( "\\[", "" ).replace( "\\]", "" );
});

////////////////////////////////////////////////////
// escapes names from input fields
////////////////////////////////////////////////////

String.method( "escapeName", function()
{
    if( ! jQuery.browser.msie )
	{
		return this.replace( "[", "\\[" ).replace( "]", "\\]" );
	}

	return this;
});