import { NextResponse } from 'next/server';
import { getSupabase } from '@/lib/supabase';

export const dynamic = 'force-dynamic';

export async function GET(request) {
  const supabase = getSupabase();
  const { searchParams } = new URL(request.url);
  const requestedMonth = searchParams.get('month') || '';

  const { data: sales, error } = await supabase.from('sales').select('*');
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  const monthSet = new Set(sales.map((s) => s.date_sold.slice(0, 7)));
  const months = [...monthSet].sort((a, b) => b.localeCompare(a));

  const now = new Date();
  const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
  let selectedMonth = /^\d{4}-\d{2}$/.test(requestedMonth) ? requestedMonth : (months[0] || currentMonth);

  const monthSales = sales.filter((s) => s.date_sold.startsWith(selectedMonth));
  const agentTotals = new Map();
  for (const s of monthSales) {
    const cur = agentTotals.get(s.agent_name) || { agent_name: s.agent_name, units: 0, revenue: 0 };
    cur.units += 1;
    cur.revenue += Number(s.price);
    agentTotals.set(s.agent_name, cur);
  }
  const leaderboard = [...agentTotals.values()].sort((a, b) => b.revenue - a.revenue);
  const monthTotal = leaderboard.reduce((sum, a) => sum + a.revenue, 0);

  return NextResponse.json({ months, selectedMonth, leaderboard, monthTotal });
}
