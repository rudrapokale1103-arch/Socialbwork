const API='../api'; let CSRF=null;
async function getCSRF(){ const r=await fetch(`${API}/common/csrf.php`); const j=await r.json(); CSRF=j.csrf; }
function hdr(){ return {'X-CSRF-Token': CSRF}; }
async function who(){ const r=await fetch(`${API}/auth/whoami.php`); const j=await r.json(); if(!CSRF) CSRF=j.csrf; return j.user; }
async function guard(){ const u=await who(); if(!u){ location.href='login.html'; } else { document.getElementById('welcome').classList.remove('d-none'); document.getElementById('welcome').textContent = `Welcome, ${u.email}`; pollNotifs(); } }
guard();

async function saveBusiness(){
  if(!CSRF) await getCSRF();
  const payload={ name:document.getElementById('bizName').value, category:document.getElementById('bizCategory').value, location:document.getElementById('bizLocation').value, description:document.getElementById('bizDesc').value };
  const r=await fetch(`${API}/user/business_save.php`,{method:'POST', headers: hdr(), body: JSON.stringify(payload)}); const j=await r.json();
  alert(j.ok?'Saved':'Error: '+(j.error||''));
}
async function createRequest(){
  if(!CSRF) await getCSRF();
  const payload={ type:document.getElementById('reqType').value, priority:document.getElementById('reqPriority').value, message:document.getElementById('reqMsg').value };
  const r=await fetch(`${API}/user/request_create.php`,{method:'POST', headers: hdr(), body: JSON.stringify(payload)}); const j=await r.json();
  if(j.ok){ document.getElementById('reqMsg').value=''; loadMyReqs(); alert('Request sent'); } else alert(j.error||'Error');
}
async function loadMyReqs(){
  const r=await fetch(`${API}/user/request_my.php`); const items=await r.json();
  const ul=document.getElementById('reqList'); ul.innerHTML='';
  (items||[]).forEach(x=>{ const li=document.createElement('li'); li.className='list-group-item'; li.innerHTML = `<div class='d-flex justify-content-between'><strong>${x.type} (${x.priority})</strong><span class='badge bg-secondary'>${x.status}</span></div><div class='small text-muted'>${x.message}</div><div class='mt-1'><em>Last reply:</em> ${x.last_reply||'-'}</div>`; ul.appendChild(li); });
}
window.addEventListener('load', loadMyReqs);

let chart;
function drawTrendChart(data){
  const ctx = document.getElementById('kwChart');
  if(!ctx) return;
  if(chart) chart.destroy();
  chart = new Chart(ctx, { type:'line', data:{ labels:Array.from({length:data.length},(_,i)=>`W${i+1}`), datasets:[{label:'Keyword Popularity', data}] }, options:{ responsive:true, scales:{y:{beginAtZero:true}} } });
}
async function searchKW(){
  const q = document.getElementById('kw').value.trim();
  if(!q) return;
  const r = await fetch(`${API}/keywords.php?q=${encodeURIComponent(q)}`);
  const j = await r.json();
  const box = document.getElementById('kwSugs');
  box.innerHTML = (j.suggestions||[]).map(s=>`<span class="badge bg-light text-dark border me-1 mb-1">${s}</span>`).join('');
  drawTrendChart(j.trend||[]);
}

async function pollNotifs(){
  const r=await fetch(`${API}/notifications.php`); const items=await r.json();
  if(items && items.length){
    const first=items.find(x=>x.is_read==0);
    const box=document.getElementById('notifBox');
    if(first){ box.classList.remove('d-none'); box.textContent = `🔔 ${first.message}`; }
  }
}
async function logout(){ await fetch('../api/logout.php'); location.href='index.html'; }
