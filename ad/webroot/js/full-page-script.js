function adlinkfly_get_url(url){var l=document.createElement("a");l.href=url;return l};function adlinkfly_get_host_name(url){var domain;if(typeof url==='undefined'||url===null||url===''||url.match(/^\#/)){return ""}
url=adlinkfly_get_url(url);if(url.href.search(/^http[s]?:\/\//)!==-1){domain=url.href.split('/')[2]}else{return ""}
domain=domain.split(':')[0];return domain.toLowerCase()}
document.addEventListener("DOMContentLoaded",function(event){if(typeof adlinkfly_url==='undefined'){return}
if(typeof adlinkfly_api_token==='undefined'){return}
var advert_type=1;if(typeof adlinkfly_advert!=='undefined'){if(adlinkfly_advert==2){advert_type=2}
if(adlinkfly_advert==0){advert_type=0}}
var anchors=document.getElementsByTagName("a");if(typeof adlinkfly_domains!=='undefined'){for(var i=0;i<anchors.length;i++){var hostname=adlinkfly_get_host_name(anchors[i].getAttribute("href"));if(hostname.length>0&&adlinkfly_domains.indexOf(hostname)>-1){anchors[i].href=adlinkfly_url+"full/?api="+encodeURIComponent(adlinkfly_api_token)+"&url="+encodeURIComponent(anchors[i].href)+"&type="+encodeURIComponent(advert_type)}else{if(anchors[i].protocol==="magnet:"){anchors[i].href=adlinkfly_url+"full/?api="+encodeURIComponent(adlinkfly_api_token)+"&url="+encodeURIComponent(anchors[i].href)+"&type="+encodeURIComponent(advert_type)}}}
return}
if(typeof adlinkfly_exclude_domains!=='undefined'){for(var i=0;i<anchors.length;i++){var hostname=adlinkfly_get_host_name(anchors[i].getAttribute("href"));if(hostname.length>0&&adlinkfly_exclude_domains.indexOf(hostname)===-1){anchors[i].href=adlinkfly_url+"full/?api="+encodeURIComponent(adlinkfly_api_token)+"&url="+encodeURIComponent(anchors[i].href)+"&type="+encodeURIComponent(advert_type)}else{if(anchors[i].protocol==="magnet:"){anchors[i].href=adlinkfly_url+"full/?api="+encodeURIComponent(adlinkfly_api_token)+"&url="+encodeURIComponent(anchors[i].href)+"&type="+encodeURIComponent(advert_type)}}}
return}})