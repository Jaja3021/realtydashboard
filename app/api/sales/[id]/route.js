import { NextResponse } from 'next/server';
import { getSupabase } from '@/lib/supabase';
import { parseSalePayload } from '../route';

export const dynamic = 'force-dynamic';

export async function PATCH(request, { params }) {
  const supabase = getSupabase();
  const id = Number(params.id);
  if (!Number.isInteger(id)) {
    return NextResponse.json({ error: 'Invalid id.' }, { status: 400 });
  }

  const body = await request.json();
  const { fields, errors } = parseSalePayload(body);
  if (errors.length) return NextResponse.json({ errors }, { status: 400 });

  const { data, error } = await supabase.from('sales').update(fields).eq('id', id).select().single();
  if (error) return NextResponse.json({ errors: [error.message] }, { status: 500 });
  return NextResponse.json({ sale: data });
}

export async function DELETE(request, { params }) {
  const supabase = getSupabase();
  const id = Number(params.id);
  if (!Number.isInteger(id)) {
    return NextResponse.json({ error: 'Invalid id.' }, { status: 400 });
  }
  const { error } = await supabase.from('sales').delete().eq('id', id);
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });
  return NextResponse.json({ ok: true });
}
