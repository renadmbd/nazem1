
const LS_KEY='nazem_inventory';
function getData(){ try{return JSON.parse(localStorage.getItem(LS_KEY))||[]}catch{return[]} }
function setData(rows){ localStorage.setItem(LS_KEY, JSON.stringify(rows)); window.dispatchEvent(new Event('storage')); }
function parseDate(d){ const dt=(d instanceof Date)?d:new Date(d); return isNaN(dt.getTime())?null:dt; }
function daysUntil(s){ const d=parseDate(s); if(!d) return Infinity; const t=new Date(); return Math.ceil((d - new Date(t.toDateString()))/86400000); }
function computeMetrics(rows){ let total=rows.length,out=0,low=0,exp=0; rows.forEach(r=>{ const q=+r.qty||0,m=+r.min_qty||0,dl=daysUntil(r.expiry); if(q<=0) out++; else if(q<m) low++; if(dl<=14) exp++; }); return {totalItems:total,outOfStock:out,lowStock:low,expiringSoon:exp}; }
function sellItem(id,amt=1){ const rows=getData(); const i=rows.findIndex(r=>String(r.id)===String(id)); if(i==-1) return {ok:false,msg:'Item not found'}; const q=+rows[i].qty||0; rows[i].qty=Math.max(0, q-Number(amt)); setData(rows); return {ok:true,msg:'Quantity updated',item:rows[i]}; }
function buildAlerts(rows){ const alerts=[]; rows.forEach(r=>{ const q=+r.qty||0,m=+r.min_qty||0,dl=daysUntil(r.expiry); if(q<=0) alerts.push({type:'out',name:r.name,meta:'Out of stock',item:r}); else if(q<m) alerts.push({type:'low',name:r.name,meta:`${q} / min ${m}`,item:r}); if(dl<=14) alerts.push({type:'expiry',name:r.name,meta:`${dl} days remaining`,item:r}); }); return alerts; }
