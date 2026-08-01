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

  let query = supabase.from('sales').select('*').order('date_sold', { ascending: false }).order('id', { ascending: false });
  if (type && PROPERTY_TYPES.includes(type)) query = query.eq('property_type', type);
  if (q) {
    const safe = sanitizeForFilter(q);
    query = query.or(`buyer_name.ilike.%${safe}%,property_name.ilike.%${safe}%,agent_name.ilike.%${safe}%`);
  }
  const { data: sales, error } = await query;
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  let suggestQuery = supabase.from('sales').select('property_name,buyer_name,agent_name');
  if (type && PROPERTY_TYPES.includes(type)) suggestQuery = suggestQuery.eq('property_type', type);
  const { data: suggestions, error: suggestError } = await suggestQuery;
  if (suggestError) return NextResponse.json({ error: suggestError.message }, { status: 500 });

  return NextResponse.json({ sales, suggestions });
}

export async function POST(request) {
  const supabase = getSupabase();
  const body = await request.json();
  const property_name = (body.property_name || '').trim();
  const property_type = (body.property_type || '').trim();
  const location = (body.location || '').trim();
  const buyer_name = (body.buyer_name || '').trim();
  const buyer_contact = (body.buyer_contact || '').trim();
  const price = body.price;
  const agent_name = (body.agent_name || '').trim();
  const date_sold = body.date_sold;

  const errors = [];
  if (!property_name) errors.push('Property name is required.');
  if (!PROPERTY_TYPES.includes(property_type)) errors.push('Select a valid property type.');
  if (!buyer_name) errors.push("Buyer's full name is required.");
  if (!agent_name) errors.push('Sales agent is required.');
  if (typeof price !== 'number' || Number.isNaN(price) || price <= 0) errors.push('Enter a valid selling price.');
  if (!date_sold || Number.isNaN(Date.parse(date_sold))) errors.push('Select a valid date sold.');

  if (errors.length) return NextResponse.json({ errors }, { status: 400 });

  const { data, error } = await supabase
    .from('sales')
    .insert({ property_name, property_type, location, buyer_name, buyer_contact, price, agent_name, date_sold })
    .select()
    .single();

  if (error) return NextResponse.json({ errors: [error.message] }, { status: 500 });
  return NextResponse.json({ sale: data }, { status: 201 });
}
