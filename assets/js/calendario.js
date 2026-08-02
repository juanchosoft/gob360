(() => {
  'use strict';
  if (!window.CALENDAR_CONFIG.connected) return;
  const modal = document.querySelector('#eventModal');
  const form = document.querySelector('#eventForm');
  const fields = {
    id: document.querySelector('#eventId'), title: document.querySelector('#eventTitle'),
    start: document.querySelector('#eventStart'), end: document.querySelector('#eventEnd'),
    allDay: document.querySelector('#eventAllDay'), location: document.querySelector('#eventLocation'),
    description: document.querySelector('#eventDescription')
  };
  let calendar;
  const showModal = () => {
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') window.jQuery(modal).modal('show');
    else { modal.style.display = 'block'; modal.classList.add('show'); modal.removeAttribute('aria-hidden'); }
  };
  const hideModal = () => {
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') window.jQuery(modal).modal('hide');
    else { modal.style.display = 'none'; modal.classList.remove('show'); modal.setAttribute('aria-hidden', 'true'); }
  };
  const localValue = value => value ? new Date(value).toLocaleString('sv-SE', { timeZone: 'America/Bogota' }).slice(0, 16) : '';
  const isoValue = value => value ? new Date(value).toISOString() : '';
  const toast = message => { const node = document.querySelector('#toast'); node.textContent = message; node.classList.add('visible'); setTimeout(() => node.classList.remove('visible'), 3200); };
  const setBusy = busy => form.querySelectorAll('button').forEach(button => button.disabled = busy);
  const request = async (method, data = null, query = '') => {
    const response = await fetch(`api/calendar-events.php${query}`, {
      method, headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CALENDAR_CONFIG.csrf },
      body: data ? JSON.stringify(data) : null
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(result.error || 'No fue posible completar la operación');
    return result;
  };
  const openForm = data => {
    form.reset(); document.querySelector('#formError').textContent = '';
    fields.id.value = data.id || ''; fields.title.value = data.title || '';
    fields.start.value = localValue(data.start); fields.end.value = localValue(data.end || data.start);
    fields.allDay.checked = Boolean(data.allDay); fields.location.value = data.location || '';
    fields.description.value = data.description || '';
    document.querySelector('#dialogTitle').textContent = data.id ? 'Editar evento' : 'Nuevo evento';
    document.querySelector('#deleteEvent').hidden = !data.id; showModal();
  };
  const payload = () => {
    const allDay = fields.allDay.checked;
    return { id: fields.id.value, title: fields.title.value.trim(), start: allDay ? fields.start.value.slice(0, 10) : isoValue(fields.start.value), end: allDay ? fields.end.value.slice(0, 10) : isoValue(fields.end.value), allDay, location: fields.location.value.trim(), description: fields.description.value.trim() };
  };
  document.querySelector('#newEventButton')?.addEventListener('click', () => {
    const start = new Date();
    start.setMinutes(Math.ceil(start.getMinutes() / 30) * 30, 0, 0);
    openForm({ start, end: new Date(start.getTime() + 60 * 60 * 1000), allDay: false });
  });
  form.addEventListener('submit', async event => {
    event.preventDefault(); setBusy(true); document.querySelector('#formError').textContent = '';
    try { await request(fields.id.value ? 'PUT' : 'POST', payload()); hideModal(); calendar.refetchEvents(); toast('Evento guardado y sincronizado'); }
    catch (error) { document.querySelector('#formError').textContent = error.message; } finally { setBusy(false); }
  });
  document.querySelector('#deleteEvent').addEventListener('click', async () => {
    if (!confirm('¿Eliminar este evento también de Google Calendar?')) return;
    setBusy(true); try { await request('DELETE', { id: fields.id.value }); hideModal(); calendar.refetchEvents(); toast('Evento eliminado'); }
    catch (error) { document.querySelector('#formError').textContent = error.message; } finally { setBusy(false); }
  });
  calendar = new FullCalendar.Calendar(document.querySelector('#calendar'), {
    locale: 'es', initialView: 'dayGridMonth', height: 'auto', firstDay: 1, nowIndicator: true,
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' },
    buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Agenda' },
    events: async (info, success, failure) => { try { success(await request('GET', null, `?start=${encodeURIComponent(info.startStr)}&end=${encodeURIComponent(info.endStr)}`)); } catch (error) { toast(error.message); failure(error); } },
    dateClick: info => openForm({ start: info.date, end: new Date(info.date.getTime() + 60 * 60 * 1000), allDay: info.allDay }),
    eventClick: info => openForm({ id: info.event.id, title: info.event.title, start: info.event.start, end: info.event.end, allDay: info.event.allDay, location: info.event.extendedProps.location, description: info.event.extendedProps.description }),
    eventDidMount: info => { info.el.title = info.event.title; }
  });
  calendar.render();
})();
