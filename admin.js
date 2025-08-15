const API='../api'; let CSRF=null;
async function getCSRF(){ const r=await fetch(`${API}/common/csrf.php`); const j=await r.json(); CSRF=j.csrf; }
function hdr(){ return {'X-CSRF-Token': CSRF}; }
async function who(){ const r=await fetch(`${API}/auth/whoami.php`); const j=await r.json(); if(!CSRF) CSRF=j.csrf; return j.user; }
async function guardAdmin(){ const u=await who(); if(!u){ location.href='login.html'; } if(u.role!=='admin'){ alert('Admin only'); location.href='dashboard.html'; } }
guardAdmin();

let selected=null, page=1, size=10, total=0;
async function loadAll(){
  const q=document.getElementById('q').value.trim();
  const url=`${API}/admin/requests_all.php?page=${page}&size=${size}${q?`&q=${encodeURIComponent(q)}`:''}`;
  const r=await fetch(url);
  const j=await r.json();
  total=j.total||0;
  const ul=document.getElementById('reqs'); ul.innerHTML='';
  (j.items||[]).forEach(x=>{
    const li=document.createElement('li'); li.className='list-group-item';
    li.innerHTML=`<div class='d-flex justify-content-between'><div><strong>${x.email}</strong> — <em>${x.business_name||'-'}</em></div><span class='badge bg-secondary'>${x.status}</span></div><div>[${x.type}] ${x.priority} — ${x.message}</div>`;
    li.onclick=()=>{ selected=x.id; document.getElementById('replyTxt').focus(); };
    ul.appendChild(li);
  });
  drawPager();
}
function drawPager(){
  const pages=Math.max(1, Math.ceil(total/size));
  const pager=document.getElementById('pager'); pager.innerHTML='';
  const make=(label,p)=>{ const li=document.createElement('li'); li.className='page-item'+(p===page?' active':''); li.innerHTML=`<a class="page-link" href="#">${label}</a>`; li.onclick=(e)=>{e.preventDefault(); page=p; loadAll();}; return li; };
  pager.appendChild(make('«', Math.max(1, page-1)));
  for(let i=1;i<=pages && i<=7;i++){ pager.appendChild(make(i,i)); }
  pager.appendChild(make('»', Math.min(pages, page+1)));
}
async function sendReply(){
  if(!selected){ alert('Select a request from the list'); return; }
  if(!CSRF) await getCSRF();
  const reply=document.getElementById('replyTxt').value.trim();
  const status=document.getElementById('replyStatus').value;
  if(!reply){ alert('Write a reply'); return; }
  const r=await fetch(`${API}/admin/request_reply.php`,{method:'POST', headers: hdr(), body: JSON.stringify({request_id:selected, reply, status})});
  const j=await r.json();
  if(j.ok){ document.getElementById('replyTxt').value=''; loadAll(); alert('Reply sent'); } else alert(j.error||'Error');
}
async function uploadResource(e){
  e.preventDefault();
  const form=document.getElementById('resForm'); const fd=new FormData(form);
  const r=await fetch(`${API}/admin/resource_upload.php`,{method:'POST', body: fd}); const j=await r.json();
  if(j.ok){ form.reset(); alert('Resource saved'); } else alert(j.error||'Error'); 
}
window.addEventListener('load', loadAll);
