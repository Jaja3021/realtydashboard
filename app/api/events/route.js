import { NextResponse } from 'next/server';
import { getSupabase } from '@/lib/supabase';
import { EVENT_TYPES } from '@/lib/constants';

export const dynamic = 'force-dynamic';

export async function GET(request) {
  const supabase = getSupabase();
  const { searchParams } = new URL(request.url);
  const month = searchParams.get('month') || '';
  if (!/^\d{4}-\d{2}$/.test(month)) {
    return NextResponse.json({ error: 'A valid ?month=YYYY-MM is required.' }, { status: 400 });
  }

  const { data: events, error } = await supabase
    .from('events')
    .select('*')
    .gte('event_date', `${month}-01`)
    .lt('event_date', nextMonthStart(month))
    .order('event_date', { ascending: true })
    .order('id', { ascending: true });

  if (error) return NextResponse.json({ error: error.message }, { status: 500 });
  return NextResponse.json({ events });
}

function nextMonthStart(ym) {
  const [y, m] = ym.split('-').map(Number);
  const d = new Date(y, m, 1); // m is 1-based here, so this rolls to the next month
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`;
}

function parseEventPayload(body) {
  const event_date = body.event_date;
  const event_type = (body.event_type || '').trim();
  const title = (body.title || '').trim();
  const notes = (body.notes || '').trim();

  const errors = [];
  if (!event_date || Number.isNaN(Date.parse(event_date))) errors.push('Select a valid date.');
  if (!EVENT_TYPES.includes(event_type)) errors.push('Select a valid event type.');
  if (!title) errors.push('Title is required.');

  const fields = { event_date, event_type, title, notes: notes || null };
  return { fields, errors };
}

export async function POST(request) {
  const supabase = getSupabase();
  const body = await request.json();
  const { fields, errors } = parseEventPayload(body);

  if (errors.length) return NextResponse.json({ errors }, { status: 400 });

  const { data, error } = await supabase.from('events').insert(fields).select().single();

  if (error) return NextResponse.json({ errors: [error.message] }, { status: 500 });
  return NextResponse.json({ event: data }, { status: 201 });
}

export { parseEventPayload };
