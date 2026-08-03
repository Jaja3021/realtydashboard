import { NextResponse } from 'next/server';
import { getSupabase } from '@/lib/supabase';
import { PROPERTY_TYPES } from '@/lib/constants';

export const dynamic = 'force-dynamic';

// PostgREST's .or() filter string treats , ( ) as syntax — strip them so a
// search term can't break out of the filter or inject extra conditions.
function sanitizeForFilter(term) {
  return term.replace(/[,()]/g, ' ').trim();
}

export async function GET(request) {
  const supabase = getSupabase();
  const { searchParams } = new URL(request.url);
  const type = searchParams.get('type') || '';
  const q = (searchParams.get('q') || '').trim();
  const month = searchParams.get('month') || '';

  let query = supabase.from('sales').select('*').order('date_sold', { ascending: false }).order('id', { ascending: false });
  if (type && PROPERTY_TYPES.includes(type)) query = query.eq('property_type', type);
  if (/^\d{4}-\d{2}$/.test(month)) query = query.gte('date_sold', `${month}-01`).lt('date_sold', nextMonthStart(month));
  if (q) {
    const safe = sanitizeForFilter(q);
    query = query.or(
      `buyer_family_name.ilike.%${safe}%,buyer_first_name.ilike.%${safe}%,property_name.ilike.%${safe}%,agent_name.ilike.%${safe}%,assisting_name.ilike.%${safe}%`
    );
  }
  const { data: sales, error } = await query;
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  let suggestQuery = supabase.from('sales').select('property_name,buyer_family_name,buyer_first_name,agent_name,assisting_name');
  if (type && PROPERTY_TYPES.includes(type)) suggestQuery = suggestQuery.eq('property_type', type);
  const { data: suggestions, error: suggestError } = await suggestQuery;
  if (suggestError) return NextResponse.json({ error: suggestError.message }, { status: 500 });

  return NextResponse.json({ sales, suggestions });
}

function nextMonthStart(ym) {
  const [y, m] = ym.split('-').map(Number);
  const d = new Date(y, m, 1); // m is 1-based here, so this rolls to the next month
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`;
}

function parseSalePayload(body) {
  const property_name = (body.property_name || '').trim();
  const property_type = (body.property_type || '').trim();
  const location = (body.location || '').trim();
  const phase = (body.phase || '').trim();
  const block = (body.block || '').trim();
  const lot = (body.lot || '').trim();
  const buyer_family_name = (body.buyer_family_name || '').trim();
  const buyer_first_name = (body.buyer_first_name || '').trim();
  const buyer_contact = (body.buyer_contact || '').trim();
  const price = body.price;
  const agent_name = (body.agent_name || '').trim();
  const assisting_name = (body.assisting_name || '').trim();
  const date_sold = body.date_sold;

  const errors = [];
  if (!property_name) errors.push('Property name is required.');
  if (!PROPERTY_TYPES.includes(property_type)) errors.push('Select a valid property type.');
  if (!block) errors.push('Block is required.');
  if (!lot) errors.push('Lot is required.');
  if (!buyer_family_name) errors.push("Buyer's family name is required.");
  if (!buyer_first_name) errors.push("Buyer's first name is required.");
  if (!agent_name) errors.push('Sales agent is required.');
  if (typeof price !== 'number' || Number.isNaN(price) || price <= 0) errors.push('Enter a valid selling price.');
  if (!date_sold || Number.isNaN(Date.parse(date_sold))) errors.push('Select a valid date sold.');

  const fields = {
    property_name,
    property_type,
    location: location || null,
    phase: phase || null,
    block,
    lot,
    buyer_family_name,
    buyer_first_name,
    buyer_contact: buyer_contact || null,
    price,
    agent_name,
    assisting_name: assisting_name || null,
    date_sold,
  };

  return { fields, errors };
}

export async function POST(request) {
  const supabase = getSupabase();
  const body = await request.json();
  const { fields, errors } = parseSalePayload(body);

  if (errors.length) return NextResponse.json({ errors }, { status: 400 });

  const { data, error } = await supabase.from('sales').insert(fields).select().single();

  if (error) return NextResponse.json({ errors: [error.message] }, { status: 500 });
  return NextResponse.json({ sale: data }, { status: 201 });
}

export { parseSalePayload };
