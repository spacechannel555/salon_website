document.addEventListener('DOMContentLoaded', () => {
  const datePicker = document.getElementById('datePicker');
  const calendarGrid = document.getElementById('calendarGrid');
  const myAppointments = document.getElementById('myAppointments');

  const START_HOUR = 11;
  const END_HOUR = 19;
  const SLOT_MIN = 15;
  const SLOT_HEIGHT_PX = 20;

  if (!datePicker || !calendarGrid) {
    console.error('Calendar elements not found in DOM.');
    return;
  }

  fetchAndRender(datePicker.value);

  datePicker.addEventListener('change', () => fetchAndRender(datePicker.value));

  async function fetchAndRender(date) {
    try {
      const res = await fetch(`/appointments.php?action=fetch&date=${encodeURIComponent(date)}`);
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
      }
      const data = await res.json();
      console.log('FETCH result:', data);

      if (!data.success) {
        console.error('API error:', data.error);
        calendarGrid.innerHTML = `<div style="color:red;padding:8px">Error: ${data.error || 'Unable to load schedule'}</div>`;
        return;
      }

      const stylists = data.stylists || [];
      const appointmentsByStylist = data.appointments || {};

      renderGrid(date, stylists, appointmentsByStylist);
      renderMyAppointments(date, appointmentsByStylist);
    } catch (err) {
      console.error('fetchAndRender error:', err);
      calendarGrid.innerHTML = `<div style="color:red;padding:8px">Error loading schedule. Check console for details.</div>`;
    }
  }

  function buildTimeSlots() {
    const slots = [];
    for (let h = START_HOUR; h < END_HOUR; h++) {
      for (let m = 0; m < 60; m += SLOT_MIN) {
        const hh = String(h).padStart(2, '0');
        const mm = String(m).padStart(2, '0');
        slots.push({hour: h, minute: m, label: `${hh}:${mm}:00`});
      }
    }
    return slots;
  }

  function renderGrid(date, stylists, appointmentsByStylist) {
    const slots = buildTimeSlots();

    let html = `<div class="grid-header"> <div class="time-column"></div>`;
    stylists.forEach(s => {
      html += `<div class="col-header">${escapeHtml(s.firstname)} ${escapeHtml(s.lastname || '')}</div>`;
    });
    html += `</div>`;


    html += '<div class="grid-body">';

    html += '<div class="time-column">';
    for (const slot of slots) {
      const display = (slot.minute === 0) ? `${String(slot.hour).padStart(2,'0')}:00` : '';
      html += `<div class="time-slot" style="height:${SLOT_HEIGHT_PX}px">${display}</div>`;
    }
    html += '</div>';

    for (const s of stylists) {
      html += `<div class="stylist-column" data-stylistid="${s.stylistid}" style="position:relative">`;
      for (const slot of slots) {
        html += `<div class="slot-tile" data-dt="${date} ${slot.label}" style="height:${SLOT_HEIGHT_PX}px"></div>`;
      }

      html += `<div class="appts-overlay" style="position:absolute;left:0;right:0;top:0;bottom:0"></div>`;
      html += '</div>';
    }
    html += '</div>'; 

    calendarGrid.innerHTML = html;

    calendarGrid.querySelectorAll('.stylist-column').forEach(col => {
      col.addEventListener('click', onColumnClick);
      const sid = col.dataset.stylistid;
      renderAppointmentsForStylist(col, appointmentsByStylist[sid] || [], slots);
    });
  }

  function renderAppointmentsForStylist(colEl, appts, slots) {
    const overlay = colEl.querySelector('.appts-overlay');
    overlay.innerHTML = '';

    for (const a of appts) {
      const start = new Date(a.appt_datetime.replace(' ', 'T'));
      const minuteOfDay = start.getHours() * 60 + start.getMinutes();
      const startIndex = (minuteOfDay - START_HOUR * 60) / SLOT_MIN;
      if (startIndex < 0) continue;

      const duration = parseInt(a.duration) || 30;
      const span = Math.max(1, Math.ceil(duration / SLOT_MIN));
      const topPx = startIndex * SLOT_HEIGHT_PX;
      const heightPx = span * SLOT_HEIGHT_PX - 2;

      const block = document.createElement('div');
      block.className = 'appt-block';
      block.style.position = 'absolute';
      block.style.left = '6px';
      block.style.right = '6px';
      block.style.top = topPx + 'px';
      block.style.height = heightPx + 'px';
      block.dataset.apptid = a.apptid;

      block.innerHTML = `
        <div class="appt-title">${escapeHtml(a.user_firstname || 'Guest')}</div>
        <div class="appt-meta">${escapeHtml(a.service_name)}</div>
      `;
      overlay.appendChild(block);

      block.addEventListener('click', async (ev) => {
        ev.stopPropagation();
        if (!confirm('Cancel this appointment?')) return;
        const fd = new URLSearchParams();
        fd.append('apptid', a.apptid);
        const res = await fetch('/appointments.php?action=cancel', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: fd.toString()
        });
        const j = await res.json();
        if (j.success) {
          fetchAndRender(datePicker.value);
        } else {
          alert('Unable to cancel: ' + (j.error || JSON.stringify(j)));
        }
      });
    }
  }

  function onColumnClick(e) {
    const col = e.currentTarget;
    const rect = col.getBoundingClientRect();
    const yRel = e.clientY - rect.top;
    const index = Math.floor(yRel / SLOT_HEIGHT_PX);
    const slots = buildTimeSlots();
    const slot = slots[index];
    if (!slot) return;
    const dt = datePicker.value + ' ' + slot.label;
    const stylistid = col.dataset.stylistid;
    openBookingModal(stylistid, dt);
  }

  async function openBookingModal(stylistid, datetime) {
    try {
      const res = await fetch(`/services.php?action=for_stylist&stylistid=${encodeURIComponent(stylistid)}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      if (!data.success) { alert('Unable to load services'); return; }
      const services = data.services || [];

      const modal = document.createElement('div');
      modal.className = 'modal-wrap';
      modal.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal">
          <button class="modal-close">&times;</button>
          <h3>Book with stylist</h3>
          <p><strong>Time:</strong> ${formatDateTime(datetime)}</p>
          <div class="services-list"></div>
        </div>
      `;
      document.body.appendChild(modal);

      const servicesList = modal.querySelector('.services-list');
      services.forEach(s => {
        const btn = document.createElement('button');
        btn.className = 'svc-btn';
        btn.innerHTML = `${escapeHtml(s.service_name)} — ${s.duration} min${s.price ? ' — $' + s.price : ''}`;
        btn.addEventListener('click', () => {
          attemptBooking({stylistid, serviceid: s.serviceid, datetime});
        });
        servicesList.appendChild(btn);
      });

      modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
      modal.querySelector('.modal-backdrop').addEventListener('click', () => modal.remove());
    } catch (err) {
      console.error('openBookingModal error:', err);
      alert('Error loading services for stylist.');
    }
  }

  async function attemptBooking({stylistid, serviceid, datetime}) {
    let bodyParams = new URLSearchParams();
    if (typeof CURRENT_USER_ID !== 'undefined' && CURRENT_USER_ID) {
      bodyParams.append('userid', CURRENT_USER_ID);
    } else {
      const gname = prompt('Your full name for the booking:');
      const gemail = prompt('Your email for confirmation:');
      if (!gname || !gemail) return alert('Name & email required for guest booking.');
      bodyParams.append('guest_name', gname);
      bodyParams.append('guest_email', gemail);
    }
    bodyParams.append('stylistid', stylistid);
    bodyParams.append('serviceid', serviceid);
    bodyParams.append('datetime', datetime);

    const res = await fetch('/appointments.php?action=create', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: bodyParams.toString()
    });
    const j = await res.json();
    if (j.success) {
      alert('Appointment created! A confirmation email will be sent.');
      document.querySelectorAll('.modal-wrap').forEach(m => m.remove());
      fetchAndRender(datePicker.value);
    } else {
      alert('Unable to book: ' + (j.error || JSON.stringify(j)));
    }
  }

  function renderMyAppointments(date, appointmentsByStylist) {
    if (!myAppointments) return;
    const all = Object.values(appointmentsByStylist).flat();
    const mine = all.filter(a => String(a.userid) === String(CURRENT_USER_ID));
    myAppointments.innerHTML = mine.map(m => `
      <div class="my-appt">
        <strong>${escapeHtml(m.service_name)}</strong><br>
        ${escapeHtml(m.appt_datetime)} with ${escapeHtml(m.stylist_firstname)}
        <div><button class="cancel" data-id="${m.apptid}">Cancel</button></div>
      </div>
    `).join('') || '<div>No appointments</div>';

    myAppointments.querySelectorAll('.cancel').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        if (!confirm('Cancel appointment?')) return;
        const fd = new URLSearchParams();
        fd.append('apptid', id);
        const r = await fetch('/appointments.php?action=cancel', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: fd.toString()
        });
        const j = await r.json();
        if (j.success) fetchAndRender(datePicker.value);
        else alert('Unable to cancel');
      });
    });
  }

  function formatDateTime(dt) {
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleString();
  }

  function escapeHtml(s) {
    if (!s) return '';
    return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }
});
