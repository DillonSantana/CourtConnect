function toast(msg, ok=true) {
  const t = document.getElementById('pf-toast');
  t.textContent = msg;
  t.className = 'show '+(ok?'toast-ok':'toast-err');
  clearTimeout(t._t);
  t._t = setTimeout(()=>t.className='', 3200);
}

async function uploadPhoto(input) {
  const file = input.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('action', 'upload_photo');
  fd.append('photo', file);
  toast('Uploading…');
  const res = await fetch('profile.php', {method:'POST', body:fd}).then(r=>r.json());
  if (res.ok) {
    toast('Photo updated!');
    let img = document.getElementById('pf-avatar-img');
    if (!img) {
      const placeholder = document.getElementById('pf-avatar-placeholder');
      img = document.createElement('img');
      img.id = 'pf-avatar-img';
      img.alt = 'Profile picture';
      img.className = 'pf-avatar';
      placeholder.replaceWith(img);
    }
    img.src = res.path + '?t=' + Date.now();
  } else {
    toast(res.error || 'Upload failed', false);
  }
  input.value = '';
}
