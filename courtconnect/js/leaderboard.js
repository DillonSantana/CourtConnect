const medals = ['🥇', '🥈', '🥉'];

function refreshStandings() {
    fetch('leaderboard.php?fetch=1')
        .then(r => r.json())
        .then(rows => {
            const tbody = document.getElementById('standings-body');
            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7">No approved results yet.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map((r, i) => `
                <tr>
                    <td>${medals[i] ?? i + 1}</td>
                    <td>${r.name}</td>
                    <td>${r.wins}</td>
                    <td>${r.losses}</td>
                    <td>${r.pct}%</td>
                </tr>
            `).join('');
        });
}

setInterval(refreshStandings, 30000);

async function submitScore() {
    const opp = document.getElementById('opp-name').value.trim();
    if (!opp) { showMsg('Opponent name is required.', false); return; }
    const my = +document.getElementById('my-score').value;
    const op = +document.getElementById('opp-score').value;
    if (my === 0 && op === 0) { showMsg('Please enter a score.', false); return; }

    const fd = new FormData();
    fd.append('opponent_name',  opp);
    fd.append('user_score',     my);
    fd.append('opponent_score', op);
    fd.append('event_id',       document.getElementById('event-id').value);

    const res = await (await fetch('leaderboard.php', { method: 'POST', body: fd })).json();
    if (res.ok) {
        showMsg('Submitted! Pending admin approval.', true);
        document.getElementById('opp-name').value  = '';
        document.getElementById('my-score').value  = '0';
        document.getElementById('opp-score').value = '0';
    } else {
        showMsg(res.error || 'Something went wrong.', false);
    }
}

function showMsg(text, ok) {
    const el = document.getElementById('msg');
    el.textContent = text;
    el.className   = ok ? 'text-success' : 'text-danger';
}