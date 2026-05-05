const $ = id => document.getElementById(id);
const val = id => $(id).value;

async function post(data) {
  const fd = new FormData();
  for (const [k,v] of Object.entries(data)) fd.append(k,v);
  return (await fetch('event_admin.php',{method:'POST',body:fd})).json();
}

function toast(msg, ok=true) {
  const t = $('ea-toast');
  t.textContent = msg;
  t.className = 'show '+(ok?'toast-ok':'toast-err');
  clearTimeout(t._t);
  t._t = setTimeout(()=>t.className='', 3200);
}

function toggleSection(id) {
  const b = $('body-'+id);
  b.classList.toggle('hidden');
  b.previousElementSibling.classList.toggle('collapsed');
}

function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtTime(s) {
  if (!s) return '—';
  const n = Number(s);
  if (!isNaN(n) && n > 0) return new Date(n * 1000).toLocaleString([],{dateStyle:'medium',timeStyle:'short'});
  const d = new Date(s.replace(' ','T'));
  return isNaN(d) ? s : d.toLocaleString([],{dateStyle:'medium',timeStyle:'short'});
}

function resetForm() {
  ['ef-id','ef-title','ef-date','ef-start','ef-end','ef-loc','ef-cap','ef-desc'].forEach(id=>$(id).value='');
  $('ef-type').value='practice';
  $('event-form-title').textContent='New Event';
}

function editEvent(id, ev) {
  $('ef-id').value    = id;
  $('ef-title').value = ev.title;
  $('ef-type').value  = ev.event_type;
  $('ef-date').value  = ev.event_date;
  $('ef-start').value = ev.start_time?.slice(0,5) ?? '';
  $('ef-end').value   = ev.end_time?.slice(0,5) ?? '';
  $('ef-loc').value   = ev.location;
  $('ef-cap').value   = ev.max_capacity ?? '';
  $('ef-desc').value  = ev.description ?? '';
  $('event-form-title').textContent = 'Edit Event — ' + ev.title;
  $('body-events').scrollIntoView({behavior:'smooth'});
}

async function submitEvent() {
  const id = val('ef-id'), title = val('ef-title').trim();
  if (!title) { toast('Title is required', false); return; }
  const res = await post({action: id?'edit_event':'create_event', event_id:id, title,
    event_type:val('ef-type'), description:val('ef-desc'), event_date:val('ef-date'),
    start_time:val('ef-start'), end_time:val('ef-end'), location:val('ef-loc'), max_capacity:val('ef-cap')});
  if (res.ok) { toast(id?'Event updated!':'Event created!'); setTimeout(()=>location.reload(), 800); }
  else toast('Error saving event', false);
}

async function deleteEvent(id) {
  if (!confirm('Delete this event and all its data?')) return;
  const res = await post({action:'delete_event', event_id:id});
  if (res.ok) { $('erow-'+id)?.remove(); toast('Event deleted'); }
  else toast('Error deleting', false);
}

async function loadRSVPs() {
  const eid = val('rsvp-event-select');
  if (!eid) { toast('Select an event first', false); return; }
  const res = await post({action:'get_rsvps', event_id:eid});
  const wrap = $('rsvp-results');
  if (!res.ok || !res.rsvps.length) { wrap.innerHTML='<p class="empty-state">No RSVPs found.</p>'; return; }
  wrap.innerHTML = `<div style="overflow-x:auto"><table class="ea-table"><thead><tr><th>Name</th><th>Email</th><th>RSVP</th><th>Responded</th></tr></thead><tbody>`
    + res.rsvps.map(r=>`<tr><td>${escHtml(r.name)}</td><td>${escHtml(r.email)}</td><td><span class="badge badge-${escHtml(r.status)}">${escHtml(r.status)}</span></td><td>${r.responded_at?fmtTime(r.responded_at):'—'}</td></tr>`).join('')
    + `</tbody></table></div>`;
}

async function loadMatches() {
  const eid = val('match-event-select');
  if (!eid) { toast('Select an event first', false); return; }
  const res = await post({action:'get_matches', event_id:eid});
  const wrap = $('matches-results');
  if (!res.ok || !res.matches.length) { wrap.innerHTML='<p class="empty-state">No submitted results found.</p>'; return; }
  wrap.innerHTML = `<div style="overflow-x:auto"><table class="ea-table"><thead><tr><th>ID</th><th>Player</th><th>Opponent</th><th>Score</th><th>Result</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead><tbody>`
    + res.matches.map(m=>`<tr id="mrow-${m.match_id}"><td>${m.match_id}</td><td>${escHtml(m.player_name)}</td><td>${escHtml(m.opponent_school)||'—'}</td><td>${m.user_score}–${m.opponent_score}</td><td><span class="badge badge-${m.result}">${escHtml(m.result)||'—'}</span></td><td>${fmtTime(m.submitted_at)}</td><td><span class="badge badge-${m.approval_status}">${m.approval_status}</span></td><td class="row-actions">${m.approval_status==='pending'?`<button class="ea-btn ea-btn-success ea-btn-sm" onclick="approveMatch(${m.match_id})">Approve</button><button class="ea-btn ea-btn-danger ea-btn-sm" onclick="rejectMatch(${m.match_id})">Reject</button>`:''}</td></tr>`).join('')
    + `</tbody></table></div>`;
}

async function approveMatch(id) {
  const res = await post({action:'approve_match', match_id:id});
  if (res.ok) { toast('Result approved!'); loadMatches(); } else toast('Error', false);
}

async function rejectMatch(id) {
  if (!confirm('Reject this result?')) return;
  const res = await post({action:'reject_match', match_id:id});
  if (res.ok) { toast('Result rejected'); loadMatches(); } else toast('Error', false);
}